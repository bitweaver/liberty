<?php
/**
 * @version		$Header: /cvsroot/bitweaver/_bit_liberty/plugins/mime.audio.php,v 1.6 2008/05/28 17:58:06 wjames5 Exp $
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision: 1.6 $
 * created		Thursday May 08, 2008
 * @package		liberty
 * @subpackage	liberty_mime_handler
 **/

/**
 * setup
 */
global $gLibertySystem;

/**
 *  This is the name of the plugin - max char length is 16
 * As a naming convention, the treasury mime handler definition should start with:
 * PLUGIN_MIME_GUID_
 */
define( 'PLUGIN_MIME_GUID_AUDIO', 'mimeaudio' );

$pluginParams = array (
	// Set of functions and what they are called in this paricular plugin
	// Use the GUID as your namespace
	'verify_function'     => 'mime_default_verify',
	'store_function'      => 'mime_audio_store',
	'update_function'     => 'mime_audio_update',
	'load_function'       => 'mime_audio_load',
	'download_function'   => 'mime_default_download',
	'expunge_function'    => 'mime_default_expunge',
	// Brief description of what the plugin does
	'title'               => 'Extract Audio File Information',
	'description'         => 'This plugin will extract as much information about an uploaded audio file as possible and allow you to listen to it on the website using a streaming player.',
	// Templates to display the files
	'view_tpl'            => 'bitpackage:liberty/mime_audio_view_inc.tpl',
	'inline_tpl'          => 'bitpackage:liberty/mime_audio_inline_inc.tpl',
	//'edit_tpl'            => 'bitpackage:liberty/mime_audio_edit_inc.tpl',
	// url to page with options for this plugin
	'plugin_settings_url' => LIBERTY_PKG_URL.'admin/mime_audio.php',
	// This should be the same for all mime plugins
	'plugin_type'         => MIME_PLUGIN,
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'       => FALSE,
	'processing_options'  => '',
	// this should pick up all audio
	'mimetypes'           => array(
		'#audio/.*#i',
	),
);
$gLibertySystem->registerPlugin( PLUGIN_MIME_GUID_AUDIO, $pluginParams );

// depending on the scan the default file might not be included yet. we need to get it manually - simply use the relative path
require_once( 'mime.default.php' );
require_once( UTIL_PKG_PATH.'getid3/getid3/getid3.php' );

/**
 * Store the data in the database
 * 
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function mime_audio_store( &$pStoreRow ) {
	// this will set the correct pluign guid, even if we let default handle the store process
	$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_AUDIO;
	$pStoreRow['log'] = array();

	// if storing works, we process the audio
	if( $ret = mime_default_store( $pStoreRow )) {
		if( !mime_audio_converter( $pStoreRow )) {
			// if it all goes tits up, we'll know why
			$pStoreRow['errors'] = $pStoreRow['log'];
			$ret = FALSE;
		}
	}
	return $ret;
}

/**
 * mime_audio_update 
 * 
 * @param array $pStoreRow 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function mime_audio_update( &$pStoreRow ) {
	// this will set the correct pluign guid, even if we let default handle the store process
	$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_AUDIO;
	$pStoreRow['log'] = array();

	// if storing works, we process the audio
	if( $ret = mime_default_update( $pStoreRow )) {
		if( !mime_audio_converter( $pStoreRow )) {
			// if it all goes tits up, we'll know why
			$pStoreRow['errors'] = $pStoreRow['log'];
			$ret = FALSE;
		}
	}
	return $ret;
}

/**
 * Load file data from the database
 * 
 * @param array $pFileHash Contains all file information
 * @param array $pPrefs Attachment preferences taken liberty_attachment_prefs
 * @param array $pParams Parameters for loading the plugin - e.g.: might contain values from the view page
 * @access public
 * @return TRUE on success, FALSE on failure - ['errors'] will contain reason for failure
 */
function mime_audio_load( &$pFileHash, &$pPrefs, $pParams = NULL ) {
	global $gLibertySystem, $gBitThemes;

	// don't load a mime image if we don't have an image for this file
	$pFileHash['no_mime_image'] = TRUE;
	if( $ret = mime_default_load( $pFileHash, $pParams )) {
		// fetch meta data from the db
		$ret['meta'] = LibertyMime::getMetaData( $pFileHash['attachment_id'], "ID3" );

		if( !empty( $ret['source_file'] ) && is_file( dirname( $ret['source_file'] ).'/bitverted.mp3' )) {
			$ret['audio_url'] = dirname( $ret['source_url'] ).'/bitverted.mp3';
			// we need some javascript for the flv player:
			$gBitThemes->loadJavascript( UTIL_PKG_PATH."javascript/flv_player/swfobject.js", FALSE, 25 );
		}
	}
	return $ret;
}

/**
 * mime_audio_converter 
 * 
 * @param array $pParamHash 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function mime_audio_converter( &$pParamHash ) {
	global $gBitSystem;

	// audio conversion can take a while
	ini_set( "max_execution_time", "1800" );

	$ret = FALSE;
	$log = array();

	$source = BIT_ROOT_PATH.$pParamHash['upload']['dest_path'].$pParamHash['upload']['name'];
	$dest_path = dirname( $source );
	$dest_file = $dest_path.'/bitverted.mp3';

	if( @BitBase::verifyId( $pParamHash['attachment_id'] )) {
		if( !$gBitSystem->isFeatureActive( 'mime_audio_force_encode' ) && preg_match( "!.mp3$!i", $pParamHash['upload']['name'] )) {
			// make a copy of the original
			if( !link( $source, $dest_file )) {
				copy( $source, $dest_file );
			}
			$ret = TRUE;
		} else {
			// TODO: have a better mechanism of converting audio to mp3. ffmpeg works well as long as the source is 'perfect'
			//       there are many audiofiles that can't be read by ffmpeg but by other tools like flac, faac, oggenc
			//       mplayer is very good, but has a lot of dependencies and not many servers have it installed

			if( !( $ret = mime_audio_conver_ffmpeg( $pParamHash, $source, $dest_file ))) {
				// fall back to using slower mplayer / lame combo
				$ret = mime_audio_conver_mplayer_lame( $pParamHash, $source, $dest_file );
			}
		}

		// if the conversion was successful, we'll copy the tags to the new mp3 file and import data to meta tables
		if( $ret == TRUE ) {
			$log['success'] = 'SUCCESS: Converted to mp3 audio';

			// now that we have a new mp3 file, we might as well copy the tags accross in case someone downloads it
			$getID3 = new getID3;
			// we silence this since this will spew lots of ugly errors when using UTF-8 and some odd character in the file ID
			$meta = @$getID3->analyze( $source );
			getid3_lib::CopyTagsToComments( $meta );

			require_once( UTIL_PKG_PATH.'getid3/getid3/write.php' );
			// Initialize getID3 tag-writing module
			$tagwriter = new getid3_writetags;
			$tagwriter->filename       = $dest_file;
			$tagwriter->tagformats     = array( 'id3v1', 'id3v2.3' );

			// set various options
			$tagwriter->overwrite_tags = TRUE;
			$tagwriter->tag_encoding   = "UTF-8";

			// store some stuff
			$tagwriter->tag_data       = $meta['comments'];

			// write tags
			if( !$tagwriter->WriteTags() ) {
				$log['tagging'] = "There was a proglem writing the tags to the mp3 file.".implode( "\n\n", $tagwriter->errors );
			}

			// getID3 returns everything in subarrays - we want to store everything in [0]
			foreach( $meta['comments'] as $key => $comment ) {
				$store[$key] = $comment[0];
			}
			$store['playtimeseconds'] = $meta['playtime_seconds'];
			$store['playtimestring']  = $meta['playtime_string'];

			if( !LibertyMime::storeMetaData( $pParamHash['attachment_id'], 'ID3', $store )) {
				$log['store_meta'] = "There was a problem storing the meta data in the database";
			}

			// if we have an image in the id3v2 tag, we might as well do something with it
			// we'll simply use the first image we can find in the file
			if( !empty( $meta['id3v2']['APIC'][0]['data'] )) {
				$image = $meta['id3v2']['APIC'][0];
			}elseif( !empty( $meta['id3v2']['PIC'][0]['data'] )) {
				$image = $meta['id3v2']['PIC'][0];
			}

			if ( isset( $image ) ){
				// write the image to temp file for us to process
				$tmpfile = str_replace( "//", "/", tempnam( TEMP_PKG_PATH, LIBERTY_PKG_NAME ) );

				if( $fp = fopen( $tmpfile, 'w' )) {
					fwrite( $fp, $image['data'] );
					fclose( $fp );

					$fileHash['type']            = $image['mime'];
					$fileHash['source_file']     = $tmpfile;
					$fileHash['dest_path']       = $pParamHash['upload']['dest_path'];
					liberty_generate_thumbnails( $fileHash );

					// remove temp file
					if( !empty( $tmpfile ) && is_file( $tmpfile )) {
						unlink( $tmpfile );
					}
				}
			}

			// TODO: when tags package is enabled add an option to add tags
			//       recommended tags might be artist and album

			// TODO: fetch album cover from amazon.com or musicbrainz.org
			//       fetch lyrics from lyricwiki.org

			//$item->mLogs['audio_converter'] = "Audio file was successfully converted to MP3.";
		}
	}

	// update log
	$pParamHash['log'] = array_merge( $pParamHash['log'], $log );

	return $ret;
}

/**
 * mime_audio_conver_mplayer_lame will decode the audio to wav using mplayer and then encode to mp3 using lame
 * 
 * @param array $pParamHash file information
 * @param array $pSource source file
 * @param array $pDest destination file
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function mime_audio_conver_mplayer_lame( &$pParamHash, $pSource, $pDest ) {
	global $gBitSystem;
	$ret = FALSE;
	$log = array();

	if( !empty( $pParamHash ) && !empty( $pSource ) && is_file( $pSource ) && !empty( $pDest )) {
		$mplayer = trim( $gBitSystem->getConfig( 'mime_audio_mplayer_path', shell_exec( 'which mplayer' )));
		$lame    = trim( $gBitSystem->getConfig( 'mime_audio_lame_path', shell_exec( 'which lame' )));

		// confirm that both applications are available
		if( $mm = shell_exec( "$mplayer 2>&1" ) && $ll = shell_exec( "$lame 2>&1" )) {
			// we will decode the audio file using mplayer and encode using lame
			$mplayer_params = " -quiet -vo null -vc dummy -af volume=0,resample=44100:0:1 -ao pcm:waveheader:file='$pSource.wav' '$pSource' ";
			$lame_params    = $gBitSystem->getConfig( "mime_audio_lame_options", " -b ".( $gBitSystem->getConfig( 'mime_audio_bitrate', 64000 ) / 1000 ))." '$pSource.wav' '$pDest' ";
			$command        = "$mplayer $mplayer_params && $lame $lame_params";
			$debug          = shell_exec( "$command 2>&1" );

			// remove the temporary wav file again
			@unlink( "$pSource.wav" );

			// make sure the conversion was successfull
			if( is_file( $pDest ) && filesize( $pDest ) > 1 ) {
				$ret = TRUE;
			} else {
				// remove unsuccessfully converted file
				@unlink( $pDest );
				$log['message'] = 'ERROR: The audio you uploaded could not be converted by mplayer and lame. DEBUG OUTPUT: '.nl2br( $debug );

				// write error message to error file
				$h = fopen( dirname( $pDest )."/error", 'w' );
				fwrite( $h, "$command\n\n$mm\n\n$ll\n\n$debug" );
				fclose( $h );
			}
		}
	}

	// update log
	$pParamHash['log'] = array_merge( $pParamHash['log'], $log );

	return $ret;
}

/**
 * mime_audio_conver_ffmpeg 
 * 
 * @param array $pParamHash file information
 * @param array $pSource source file
 * @param array $pDest destination file
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function mime_audio_conver_ffmpeg( &$pParamHash, $pSource, $pDest ) {
	global $gBitSystem;
	$ret = FALSE;
	$log = array();

	if( !empty( $pParamHash ) && !empty( $pSource ) && is_file( $pSource ) && !empty( $pDest )) {
		// these are set in the liberty plugin admin screen
		$ffmpeg = trim( $gBitSystem->getConfig( 'mime_audio_ffmpeg_path', shell_exec( 'which ffmpeg' )));

		if( $ff = shell_exec( "$ffmpeg 2>&1" )) {
			// set up parameters to convert audio
			$params =
				" -i '$pSource'".
				" -acodec libmp3lame".
				" -ab ".trim( $gBitSystem->getConfig( 'mime_audio_bitrate', 64000 ).'b' ).
				" -ar ".trim( $gBitSystem->getConfig( 'mime_audio_samplerate', 22050 )).
				" -y '$pDest'";
			$debug = shell_exec( "$ffmpeg $params 2>&1" );

			// make sure the conversion was successfull
			if( is_file( $pDest ) && filesize( $pDest ) > 1 ) {
				$ret = TRUE;
			} else {
				// remove unsuccessfully converted file
				@unlink( $pDest );
				$log['message'] = 'ERROR: The audio you uploaded could not be converted by ffmpeg. DEBUG OUTPUT: '.nl2br( $debug );

				// write error message to error file
				$h = fopen( dirname( $pDest )."/error", 'w' );
				fwrite( $h, "$ffmpeg $params\n\n$ff\n\n$debug" );
				fclose( $h );
			}
		}
	}

	// update log
	$pParamHash['log'] = array_merge( $pParamHash['log'], $log );

	return $ret;
}
?>
