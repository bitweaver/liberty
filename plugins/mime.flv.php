<?php
/**
 * @version		$Header: /cvsroot/bitweaver/_bit_liberty/plugins/Attic/mime.flv.php,v 1.8 2008/05/29 09:55:41 squareing Exp $
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision: 1.8 $
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
define( 'PLUGIN_MIME_GUID_FLV', 'mimeflv' );

$pluginParams = array (
	// Set of functions and what they are called in this paricular plugin
	// Use the GUID as your namespace
	'verify_function'     => 'mime_default_verify',
	'store_function'      => 'mime_flv_store',
	'update_function'     => 'mime_flv_update',
	'load_function'       => 'mime_flv_load',
	'download_function'   => 'mime_default_download',
	'expunge_function'    => 'mime_default_expunge',
	// Brief description of what the plugin does
	'title'               => 'Convert Video to Flash Video',
	'description'         => 'This plugin will use ffmpeg to convert any compatible uploaded video to flash video. It will also make the video available for viewing if you have flash installed. Please consult the README on how to use this plugin.',
	// Templates to display the files
	'view_tpl'            => 'bitpackage:liberty/mime_flv_view_inc.tpl',
	'inline_tpl'          => 'bitpackage:liberty/mime_flv_inline_inc.tpl',
	//'edit_tpl'            => 'bitpackage:liberty/mime_flv_edit_inc.tpl',
	// url to page with options for this plugin
	'plugin_settings_url' => LIBERTY_PKG_URL.'admin/mime_flv.php',
	// This should be the same for all mime plugins
	'plugin_type'         => MIME_PLUGIN,
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'       => FALSE,
	// Help page on bitweaver.org
	'help_page'           => 'LibertyMime+FLV+Plugin',
	// this should pick up all videos
	'mimetypes'           => array(
		'#video/.*#i',
	),
);
$gLibertySystem->registerPlugin( PLUGIN_MIME_GUID_FLV, $pluginParams );

// depending on the scan the default file might not be included yet. we need to get it manually - simply use the relative path
require_once( 'mime.default.php' );

/**
 * Store the data in the database
 * 
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function mime_flv_store( &$pStoreRow ) {
	global $gBitSystem;

	// this will set the correct pluign guid, even if we let default handle the store process
	$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_FLV;

	// if storing works, we process the video
	if( $ret = mime_default_store( $pStoreRow )) {
		if( $gBitSystem->isFeatureActive( 'mime_use_cron' )) {
			// if we want to use cron, we add a process, otherwise we convert video right away
			if( mime_flv_add_process( $pStoreRow )) {
				// add an indication that this file is being processed
				touch( BIT_ROOT_PATH.$pStoreRow['upload']['dest_path']."processing" );
			}
		} else {
			if( !mime_flv_converter( $pStoreRow )) {
				$pStoreRow['errors'] = $pStoreRow['log'];
				$ret = FALSE;
			}
		}
	}
	return $ret;
}

/**
 * mime_flv_update 
 * 
 * @param array $pStoreRow 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function mime_flv_update( &$pStoreRow ) {
	global $gBitSystem;
	// if storing works, we process the video
	if( $ret = mime_default_update( $pStoreRow )) {
		// we only need to add a new process when we are actually uploading a new file
		if( !empty( $pStoreRow['upload']['tmp_name'] )) {
			// add an indication that this file is being processed
			touch( BIT_ROOT_PATH.$pStoreRow['upload']['dest_path']."processing" );
			// remove any error file since this is a new video file
			@unlink( BIT_ROOT_PATH.$pStoreRow['upload']['dest_path']."error" );
			// since this user is uploading a new video, we will remove the old flick.flv file
			@unlink( BIT_ROOT_PATH.$pStoreRow['upload']['dest_path']."flick.flv" );

			// if we want to use cron, we add a process, otherwise we convert video right away
			if( $gBitSystem->isFeatureActive( 'mime_use_cron' )) {
				mime_flv_add_process( $pStoreRow );
			} else {
				if( !mime_flv_converter( $pStoreRow )) {
					$pStoreRow['errors'] = $pStoreRow['log']['message'];
				}
			}
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
function mime_flv_load( $pFileHash, &$pPrefs, $pParams = NULL ) {
	global $gLibertySystem, $gBitThemes;
	if( $ret = mime_default_load( $pFileHash, $pParams )) {
		// check for status of conversion
		if( !empty( $ret['source_file'] )) {
			if( is_file( dirname( $ret['source_file'] ).'/error' )) {
				$ret['status']['error'] = TRUE;
			} elseif( is_file( dirname( $ret['source_file'] ).'/processing' )) {
				$ret['status']['processing'] = TRUE;
			} elseif( is_file( dirname( $ret['source_file'] ).'/flick.flv' )) {
				$ret['flv_url'] = dirname( $ret['source_url'] ).'/flick.flv';
				// we need some javascript for the flv player:
				$gBitThemes->loadJavascript( UTIL_PKG_PATH."javascript/flv_player/swfobject.js", FALSE, 25 );
			} elseif( is_file( dirname( $ret['source_file'] ).'/flick.mp4' )) {
				$ret['flv_url'] = dirname( $ret['source_url'] ).'/flick.mp4';
				// we need some javascript for the flv player:
				$gBitThemes->loadJavascript( UTIL_PKG_PATH."javascript/flv_player/swfobject.js", FALSE, 25 );
			}
		}

		// now that we have the original width and height, we can get the displayed values
		$ret['preferences'] = mime_flv_calculate_videosize( $ret, $pPrefs, $pParams );

		// we can use a special plugin if active to include flvs in wiki pages
		if( $gLibertySystem->isPluginActive( 'dataflashvideo' )) {
			$ret['wiki_plugin_link'] = "{flashvideo id={$ret['attachment_id']}}";
		}
	}
	return $ret;
}

/**
 * This function will add an entry to the process queue for the cron job to take care of
 * 
 * @param array $pContentId 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function mime_flv_add_process( $pStoreRow ) {
	global $gBitSystem;
	$ret = FALSE;
	if( @BitBase::verifyId( $pStoreRow['content_id'] )) {
		$query = "
			UPDATE `".BIT_DB_PREFIX."liberty_process_queue`
			SET `process_status`=?
			WHERE `content_id`=? AND `process_status`=?";
		$gBitSystem->mDb->query( $query, array( 'defunkt', $pStoreRow['content_id'], 'pending' ));

		$storeHash = array (
			'content_id'           => $pStoreRow['content_id'],
			'queue_date'           => $gBitSystem->getUTCTime(),
			'process_status'       => 'pending',
			'processor'            => dirname( __FILE__ ).'/mime.flv.php',
			'processor_parameters' => mime_flv_converter( $pStoreRow, TRUE ),
		);
		$gBitSystem->mDb->associateInsert( BIT_DB_PREFIX."liberty_process_queue", $storeHash );
		$ret = TRUE;
	}
	return $ret;
}

/**
 * Convert a stored video file to flashvideo
 * 
 * @param array $pParamHash 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function mime_flv_converter( &$pParamHash, $pOnlyGetParameters = FALSE ) {
	global $gBitSystem;

	// video conversion can take a while
	ini_set( "max_execution_time", "1800" );

	$ret = FALSE;

	if( @BitBase::verifyId( $pParamHash['attachment_id'] )) {
		// these are set in the liberty plugin admin screen
		$ffmpeg = trim( $gBitSystem->getConfig( 'mime_flv_ffmpeg_path', shell_exec( 'which ffmpeg' )));
		$width  = trim( $gBitSystem->getConfig( 'mime_flv_width', 320 ));

		$begin = date( 'U' );
		$log   = $actionLog = array();

		if( !( $ff = shell_exec( "$ffmpeg 2>&1" ))) {
			$log['time']     = date( 'Y-M-d - H:i:s O' );
			$log['duration'] = 0;
			$log['message']  = 'ERROR: ffmpeg does not seem to be available on your system at: '.$ffmpeg.' Please set the path to ffmpeg in the liberty plugin administration screen.';
		} else {
			// this is the codec we'll use - currently this might be: flv, h264, h264-2pass
			$codec = $gBitSystem->getConfig( "mime_flv_video_codec", "flv" );

			$source = BIT_ROOT_PATH.$pParamHash['upload']['dest_path'].$pParamHash['upload']['name'];
			$dest_path = dirname( $source );
			$extension = (( $codec == "flv" ) ? "flv" : "mp4" );
			$dest_file = $dest_path."/flick.$extension";

			// set some default values if ffpeg-php isn't available or fails
			$default['aspect']     = 4 / 3;
			$default['flv_width']  = $width;
			$default['flv_height'] = round( $width / 4 * 3 );
			$default['size']       = "{$default['flv_width']}x{$default['flv_height']}";
			$default['offset']     = '00:00:10';

			if( extension_loaded( 'ffmpeg' )) {
				// we silence these calls since they might spew errors
				$movie = @new ffmpeg_movie( $source );
				$info['vcodec']           = @$movie->getVideoCodec();
				$info['acodec']           = @$movie->getAudioCodec();
				$info['duration']         = round( @$movie->getDuration() );
				$info['width']            = @$movie->getFrameWidth();
				$info['height']           = @$movie->getFrameHeight();
				$info['video_bitrate']    = @$movie->getVideoBitRate();
				$info['audio_bitrate']    = @$movie->getAudioBitRate();
				$info['audio_samplerate'] = @$movie->getAudioSampleRate();
			}

			// our player supports flv and h264 so we might as well use the default
			if( !$gBitSystem->isFeatureActive( 'mime_flv_force_encode' ) && !empty( $info ) && ( $info['vcodec'] == 'h264' || $info['vcodec'] == 'flv' ) && $info['acodec'] == 'mp3' ) {
				// if the video can be processed by ffmpeg-php, width and height are greater than 1
				if( !empty( $info['width'] ) && $info['width'] > 1 ) {
					$info['aspect']   = $info['width'] / $info['height'];
					$info['offset']   = strftime( "%T", round( $info['duration'] / 5 - ( 60 * 60 )));
				} else {
					$info = $default;
				}

				// store prefs and create thumbnails
				mime_flv_store_preferences( $pParamHash, $info );
				mime_flv_create_thumbnail( $source, $info['offset'] );
				rename( $source, $dest_file );
				$log['message'] = 'SUCCESS: Converted to flash video';
				$actionLog['log_message'] = "Flv video file was successfully uploaded and thumbnails extracted.";
				$ret = TRUE;
			} else {
				// if the video can be processed by ffmpeg-php, width and height are greater than 1
				if( !empty( $info['width'] ) && $info['width'] > 1 ) {
					// reset some values to reduce video size
					if( $info['width'] < $width ) {
						$width = $info['width'];
					}

					// make sure audio sample rate is valid
					if( !in_array( $info['audio_samplerate'], array( 11025, 22050, 44100 ))) {
						unset( $info['audio_samplerate'] );
					}

					/* not sure why this is here.
					$compare = array(
						'video_bitrate'    => 160000,
						'audio_bitrate'    => 32000,
						'audio_samplerate' => 22050,
					);

					foreach( $compare as $comp => $default ) {
						if( !empty( $info[$comp] ) && $info[$comp] < $gBitSystem->getConfig( 'mime_flv_'.$comp, $default )) {
							$gBitSystem->setConfig( 'mime_flv_'.$comp, $info[$comp] );
						}
					}
					 */

					// here we calculate the size and aspect ratio of the output video
					$size_ratio         = $width / $info['width'];
					$info['aspect']     = $info['width'] / $info['height'];
					$info['flv_width']  = $width;
					$info['flv_height'] = round( $size_ratio * $info['height'] );
					// height of video needs to be an even number
					if( $info['flv_height'] % 2 ) {
						$info['flv_height']++;
					}
					$info['size']       = "{$info['flv_width']}x{$info['flv_height']}";
				} else {
					$info = $default;
				}

				if( $codec == "h264" ) {
					$parameters =
						" -i '$source'".
						// audio
						" -acodec mp3".
						" -ab ".trim( $gBitSystem->getConfig( 'mime_flv_audio_bitrate', 32000 ).'b' ).
						" -ar ".trim( $gBitSystem->getConfig( 'mime_flv_audio_samplerate', 22050 )).
						// video
						" -vcodec libx264".
						" -b ".trim( $gBitSystem->getConfig( 'mime_flv_video_bitrate', 160000 ).'b' ).
						" -bt ".trim( $gBitSystem->getConfig( 'mime_flv_video_bitrate', 160000 ).'b' ).
						" -s ".$info['size'].
						" -aspect ".$info['aspect'].
						" -flags +loop -cmp +chroma -refs 1 -coder 0 -me_range 16 -g 300 -keyint_min 25 -sc_threshold 40 -i_qfactor 0.71 -maxrate 10M -bufsize 10M -rc_eq 'blurCplx^(1-qComp)' -qcomp 0.6 -qmin 10 -qmax 51 -qdiff 4 -level 30".
						" -partitions +parti4x4+partp8x8+partb8x8 -me umh -subq 5 -trellis 1".
						// output
						" -y '$dest_file'";

				} elseif( $codec == "h264-2pass" ) {
					// it is not possible to pass in the path for the x264 log file and it is always generated in the working dir.
					$cwd = getcwd();
					chdir( dirname( $dest_file ));

					$passlogfile = dirname( $dest_file )."/ffmpeg2pass";

					// pass 1
					$parameters =
						" -i '$source'".
						// audio
						" -an".
						// video
						" -pass 1".
						" -passlogfile $passlogfile".
						" -vcodec libx264".
						" -b ".trim( $gBitSystem->getConfig( 'mime_flv_video_bitrate', 160000 ).'b' ).
						" -bt ".trim( $gBitSystem->getConfig( 'mime_flv_video_bitrate', 160000 ).'b' ).
						" -s ".$info['size'].
						" -aspect ".$info['aspect'].
						" -flags +loop -cmp +chroma -refs 1 -coder 0 -me_range 16 -g 300 -keyint_min 25 -sc_threshold 40 -i_qfactor 0.71 -bf 16 -maxrate 10M -bufsize 10M -rc_eq 'blurCplx^(1-qComp)' -qcomp 0.6 -qmin 10 -qmax 51 -qdiff 4 -level 30".
						" -partitions 0 -me epzs -subq 1 -trellis 0".
						// output
						" -y '$dest_file'";

					// pass 2
					$parameters2 =
						" -i '$source'".
						// audio
						" -acodec mp3".
						" -ab ".trim( $gBitSystem->getConfig( 'mime_flv_audio_bitrate', 32000 ).'b' ).
						" -ar ".trim( $gBitSystem->getConfig( 'mime_flv_audio_samplerate', 22050 )).
						// video
						" -pass 2".
						" -passlogfile $passlogfile".
						" -vcodec libx264".
						" -b ".trim( $gBitSystem->getConfig( 'mime_flv_video_bitrate', 160000 ).'b' ).
						" -bt ".trim( $gBitSystem->getConfig( 'mime_flv_video_bitrate', 160000 ).'b' ).
						" -s ".$info['size'].
						" -aspect ".$info['aspect'].
						" -flags +loop -cmp +chroma -me_range 16 -g 250 -keyint_min 25 -sc_threshold 40 -i_qfactor 0.71 -rc_eq 'blurCplx^(1-qComp)' -qcomp 0.6 -qmin 10 -qmax 51 -qdiff 4".
						" -partitions +parti8x8+parti4x4+partp8x8+partp4x4+partb8x8 -flags2 +brdo+dct8x8+wpred+bpyramid+mixed_refs -me umh -subq 7 -trellis 1 -refs 6 -bf 16 -directpred 3 -b_strategy 1 -bidir_refine 1 -coder 1".
						// output
						" -y '$dest_file'";

				} else {
					$parameters =
						" -i '$source'".
						// audio
						" -acodec mp3".
						" -ab ".trim( $gBitSystem->getConfig( 'mime_flv_audio_bitrate', 32000 ).'b' ).
						" -ar ".trim( $gBitSystem->getConfig( 'mime_flv_audio_samplerate', 22050 )).
						// video
						" -f flv".
						" -b ".trim( $gBitSystem->getConfig( 'mime_flv_video_bitrate', 160000 ).'b' ).
						" -bt ".trim( $gBitSystem->getConfig( 'mime_flv_video_bitrate', 160000 ).'b' ).
						" -s ".$info['size'].
						" -aspect ".$info['aspect'].
						// output
						" -y '$dest_file'";
				}

				if( $pOnlyGetParameters ) {
					return $parameters;
				} else {
					// we keep the output of this that we can store it to the error file if we need to do so
					if( $debug = shell_exec( "$ffmpeg $parameters 2>&1" ) && !empty( $parameters2 )) {
						$debug .= shell_exec( "$ffmpeg $parameters2 2>&1" );
						// change back to whence we came
						chdir( $cwd );
					}
				}

				// make sure the conversion was successfull
				if( is_file( $dest_file ) && filesize( $dest_file ) > 1 ) {
					// try to work out a reasonable timepoint where to extract a screenshot
					if( preg_match( '!Duration: ([\d:\.]*)!', $debug, $time )) {
						list( $h, $m, $s ) = explode( ':', $time[1] );
						$seconds = round( 60 * 60 * (int)$h + 60 * (int)$m + (float)$s );
						// we need to subract one hour from our time for strftime to return the correct value
						$info['offset'] = strftime( "%T", round( $seconds / 5 - ( 60 * 60 )));
					} else {
						$info['offset'] = "00:00:10";
					}
					// store some video specific settings
					mime_flv_store_preferences( $pParamHash, $info );

					// since the flv conversion worked, we will create a preview screenshots to show.
					mime_flv_create_thumbnail( $dest_file, $info['offset'] );

					$log['message'] = 'SUCCESS: Converted to flash video';
					$actionLog['log_message'] = "Converted to flashvideo in ".( date( 'U' ) - $begin )." seconds";
					$ret = TRUE;
				} else {
					// remove unsuccessfully converted file
					@unlink( $dest_file );
					$log['message'] = 'ERROR: The video you uploaded could not be converted by ffmpeg. DEBUG OUTPUT: '.nl2br( $debug );
					$actionLog['log_message'] = "Video could not be converted to flashvideo. An error dump was saved to: ".$dest_path.'/error';

					// write error message to error file
					$h = fopen( $dest_path."/error", 'w' );
					fwrite( $h, "$ffmpeg $parameters\n\n$debug" );
					fclose( $h );
				}
				@unlink( $dest_path.'/processing' );
			}
		}

		$log['time']     = date( 'd/M/Y:H:i:s O' );
		$log['duration'] = date( 'U' ) - $begin;

		// we'll insert some info into the database for reference
		$actionLog['content_id'] = $pParamHash['content_id'];
		$actionLog['title'] = "Uploaded file: {$pParamHash['upload']['name']} [Attchment ID: {$pParamHash['attachment_id']}]";

		// if this all goes tits up, we'll know why
		$pParamHash['log'] = $log;

		// we'll add an entry in the action logs
		LibertyContent::storeActionLog( array( 'action_log' => $actionLog ));

		// return the log
		$pParamHash['log'] = $log;
	}
	return $ret;
}

/**
 * This function will create a thumbnail for a given video
 * 
 * @param string $pFile path to video file
 * @param numric $pOffset Offset in seconds to use to create thumbnail from
 * @access public
 * @return TRUE on success, FALSE on failure
 */
function mime_flv_create_thumbnail( $pFile, $pOffset = 60 ) {
	global $gBitSystem;
	$ret = FALSE;
	if( !empty( $pFile )) {
		$dest_path = dirname( $pFile );

		// try to use an app designed specifically to extract a thumbnail
		if( shell_exec( shell_exec( 'which ffmpegthumbnailer' ).' -h' )) {
			$thumbnailer = trim( shell_exec( 'which ffmpegthumbnailer' ));
		} elseif( shell_exec( shell_exec( 'which ffmpegvideothumbnailer' ).' -h' )) {
			$thumbnailer = trim( shell_exec( 'which ffmpegvideothumbnailer' ));
		}

		if( !empty( $thumbnailer )) {
			shell_exec( "$thumbnailer -i '$pFile' -o '$dest_path/medium.jpg' -s 600" );
			if( is_file( "$dest_path/medium.jpg" )) {
				$fileHash['type']            = 'image/jpg';
				$fileHash['thumbnail_sizes'] = array( 'icon', 'avatar', 'small' );
				$fileHash['source_file']     = "$dest_path/medium.jpg";
				$fileHash['dest_path']       = str_replace( BIT_ROOT_PATH, '', "$dest_path/" );
				liberty_generate_thumbnails( $fileHash );
				$ret = TRUE;
			}
		} else {
			// fall back to using ffmepg
			$ffmpeg    = trim( $gBitSystem->getConfig( 'mime_flv_ffmpeg_path', shell_exec( 'which ffmpeg' )));
			shell_exec( "$ffmpeg -i '$pFile' -an -ss $pOffset -t 00:00:01 -r 1 -y '$dest_path/preview%d.jpg'" );
			if( is_file( "$dest_path/preview1.jpg" )) {
				$fileHash['type']            = 'image/jpg';
				$fileHash['thumbnail_sizes'] = array( 'icon', 'avatar', 'small', 'medium' );
				$fileHash['source_file']     = "$dest_path/preview1.jpg";
				$fileHash['dest_path']       = str_replace( BIT_ROOT_PATH, '', "$dest_path/" );
				liberty_generate_thumbnails( $fileHash );
				$ret = TRUE;
			}
		}
	}
	return $ret;
}

/**
 * mime_flv_store_preferences 
 * 
 * @param array $pVideoInfo Video information
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function mime_flv_store_preferences( $pFileHash, $pVideoInfo ) {
	$ret = FALSE;

	if( @BitBase::verifyId( $pFileHash['attachment_id'] )) {
		// store duration of video
		if( !empty( $pVideoInfo['duration'] )) {
			LibertyMime::storeAttachmentPreference( $pFileHash['attachment_id'], 'duration', $pVideoInfo['duration'] );
		}

		// only store aspect if aspect is different to 4:3
		$default = 4 / 3;
		if( !empty( $pVideoInfo['aspect'] ) && $pVideoInfo['aspect'] != $default ) {
			LibertyMime::storeAttachmentPreference( $pFileHash['attachment_id'], 'aspect', $pVideoInfo['aspect'] );
		}
		$ret = TRUE;
	}

	return $ret;
}

/**
 * mime_flv_calculate_videosize Calculate the display video size
 * 
 * @param array $pFileHash File information including attachment_id
 * @param array $pCommonObject common object - calculations will be stored in $pCommonObject->mStoragePrefs
 * @access public
 * @return void
 */
function mime_flv_calculate_videosize( $pFileHash, &$pPrefs, $pParams ) {
	global $gBitSystem;

	$pPrefs['flv_width']  = $gBitSystem->getConfig( 'mime_flv_width', 320 );
	$pPrefs['flv_height'] = ( $gBitSystem->getConfig( 'mime_flv_width', 320 ) / ( !empty( $pPrefs['aspect'] ) ? $pPrefs['aspect'] : 4 / 3 ));

	// if we want to display a different size
	if( !empty( $pParams['size'] )) {
		if( $pParams['size'] == 'small' ) {
			$new_width = 160;
		} elseif( $pParams['size'] == 'medium' ) {
			$new_width = 320;
		} elseif( $pParams['size'] == 'large' ) {
			$new_width = 480;
		} elseif( $pParams['size'] == 'huge' ) {
			$new_width = 600;
		}
	} else {
		$new_width = $gBitSystem->getConfig( 'mime_flv_default_size' );
	}

	// if we want to change the video size
	if( !empty( $new_width )) {
		$ratio = $pPrefs['flv_width'] / $new_width;
		$pPrefs['flv_height'] = round( $pPrefs['flv_height'] / $ratio );

		// now that all calculations are done, we apply the width
		$pPrefs['flv_width']  = $new_width;
	}

	return $pPrefs;
}
?>
