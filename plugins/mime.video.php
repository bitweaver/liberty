<?php
/**
 * @version		$Header$
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision$
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
 * As a naming convention, the liberty mime handler definition should start with:
 * PLUGIN_MIME_GUID_
 */
define( 'PLUGIN_MIME_GUID_VIDEO', 'mimevideo' );

$pluginParams = array (
	// Set of functions and what they are called in this paricular plugin
	// Use the GUID as your namespace
	'preload_function'    => 'mime_video_preload',
	'verify_function'     => 'mime_default_verify',
	'store_function'      => 'mime_video_store',
	'update_function'     => 'mime_video_update',
	'load_function'       => 'mime_video_load',
	'download_function'   => 'mime_default_download',
	'expunge_function'    => 'mime_default_expunge',
	// Brief description of what the plugin does
	'title'               => 'Convert Video to Flash Video',
	'description'         => 'This plugin will use ffmpeg to convert any compatible uploaded video to flash video. It will also make the video available for viewing if you have flash installed. Please consult the README on how to use this plugin.',
	// Templates to display the files
	'view_tpl'            => 'bitpackage:liberty/mime/video/view.tpl',
	'inline_tpl'          => 'bitpackage:liberty/mime/video/inline.tpl',
	'storage_tpl'         => 'bitpackage:liberty/mime/video/storage.tpl',
	'attachment_tpl'      => 'bitpackage:liberty/mime/video/attachment.tpl',
	'edit_tpl'            => 'bitpackage:liberty/mime/video/edit.tpl',
	// url to page with options for this plugin
	'plugin_settings_url' => LIBERTY_PKG_URL.'admin/plugins/mime_video.php',
	// This should be the same for all mime plugins
	'plugin_type'         => MIME_PLUGIN,
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'       => FALSE,
	// Help page on bitweaver.org
	'help_page'           => 'LibertyMime+Video+Plugin',
	// this should pick up all videos
	'mimetypes'           => array(
		'#video/.*#i',
	),
);
$gLibertySystem->registerPlugin( PLUGIN_MIME_GUID_VIDEO, $pluginParams );

/**
 * mime_video_preload This function is loaded on every page load before anything happens and is used to load required scripts.
 *
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function mime_video_preload() {
	global $gBitThemes;
	$gBitThemes->loadJavascript( UTIL_PKG_PATH."javascript/flv_player/swfobject.js", FALSE, 25 );
}

/**
 * Store the data in the database
 *
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function mime_video_store( &$pStoreRow ) {
	global $gBitSystem;

	// this will set the correct pluign guid, even if we let default handle the store process
	$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_VIDEO;

	// if storing works, we process the video
	if( $ret = mime_default_store( $pStoreRow )) {
		if( !mime_video_converter( $pStoreRow )) {
			$pStoreRow['errors'] = $pStoreRow['log'];
			$ret = FALSE;
		}
	}
	return $ret;
}

/**
 * mime_video_update
 *
 * @param array $pStoreRow
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function mime_video_update( &$pStoreRow, $pParams = NULL ) {
	$ret = FALSE;
	if( BitBase::verifyId( $pStoreRow['attachment_id'] )) {
		$pStoreRow['log'] = array();

		// set the correct pluign guid, even if we let default handle the store process
		$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_VIDEO;
		// remove the entire directory
		$pStoreRow['unlink_dir'] = TRUE;

		// if storing works, we process the video
		if( !empty( $pStoreRow['upload'] ) && $ret = mime_default_update( $pStoreRow )) {
			if( !mime_video_converter( $pStoreRow )) {
				// if it all goes tits up, we'll know why
				$pStoreRow['errors'] = $pStoreRow['log'];
				$ret = FALSE;
			}
		}

		// if there was no upload we'll process the file parameters
		if( empty( $pStoreRow['upload'] ) && isset( $pParams['meta']['aspect'] )) {
			// set aspect NULL that it's removed from the database
			if( empty( $pParams['meta']['aspect'] )) {
				$pParams['meta']['aspect'] = NULL;
			}

			// we store the custom aspect ratio as a preference which we will use to override the original one
			if( !LibertyMime::storeAttachmentPreference( $pStoreRow['attachment_id'], 'aspect', $pParams['meta']['aspect'] )) {
				$log['store_meta'] = "There was a problem storing the preference in the database";
			}

			if( empty( $log )) {
				$ret = TRUE;
			} else {
				$pStoreRow['errors'] = $log;
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
function mime_video_load( $pFileHash, &$pPrefs, $pParams = NULL ) {
	global $gLibertySystem, $gBitThemes;
	if( $ret = mime_default_load( $pFileHash, $pParams )) {
		// check for status of conversion
		if( !empty( $ret['source_file'] )) {
			$source_path = STORAGE_PKG_PATH.dirname( $ret['source_file'] ).'/';
			if( is_file( $source_path.'error' )) {
				$ret['status']['error'] = TRUE;
			} elseif( is_file( $source_path.'processing' )) {
				$ret['status']['processing'] = TRUE;
			} elseif( is_file( $source_path.'flick.flv' )) {
				$ret['media_url'] = storage_path_to_url( dirname( $ret['source_file'] ).'/flick.flv' );
			} elseif( is_file( $source_path.'flick.mp4' )) {
				$ret['media_url'] = storage_path_to_url( dirname( $ret['source_file'] ).'/flick.mp4' );
			}
		}

		// now that we have the original width and height, we can get the displayed values
		$ret['meta'] = array_merge( LibertyMime::getMetaData( $pFileHash['attachment_id'], "Video" ), $pPrefs );
		mime_video_calculate_videosize( $ret['meta'], $pParams );
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
function mime_video_add_process( $pStoreRow ) {
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
			'processor'            => dirname( __FILE__ ).'/mime.video.php',
			'processor_parameters' => mime_video_converter( $pStoreRow, TRUE ),
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
function mime_video_converter( &$pParamHash, $pOnlyGetParameters = FALSE ) {
	global $gBitSystem;

	// video conversion can take a while
	ini_set( "max_execution_time", "1800" );

	$ret = FALSE;

	if( @BitBase::verifyId( $pParamHash['attachment_id'] )) {
		// we might have some attachment preferences set if this is an update
		LibertyMime::expungeAttachmentPreferences( $pParamHash['attachment_id'] );

		// these are set in the liberty plugin admin screen
		$ffmpeg = trim( $gBitSystem->getConfig( 'ffmpeg_path', shell_exec( 'which ffmpeg' )));
		$width  = trim( $gBitSystem->getConfig( 'mime_video_width', 320 ));
		$begin  = date( 'U' );
		$log    = $actionLog = array();

		if( !is_executable( $ffmpeg )) {
			$log['time']     = date( 'Y-M-d - H:i:s O' );
			$log['duration'] = 0;
			$log['message']  = 'ERROR: ffmpeg does not seem to be available on your system at: '.$ffmpeg.' Please set the path to ffmpeg in the liberty plugin administration screen.';
			$actionLog['log_message'] = "ERROR: ffmpeg does not seem to be available on your system at: '$ffmpeg' Please set the path to ffmpeg in the liberty plugin administration screen.";
		} else {
			// this is the codec we'll use - currently this might be: flv, h264, h264-2pass
			$codec = $gBitSystem->getConfig( "mime_video_video_codec", "flv" );
			$source = STORAGE_PKG_PATH.$pParamHash['upload']['dest_branch'].$pParamHash['upload']['name'];
			$destPath = dirname( $source );

			// set some default values if ffpeg-php isn't available or fails
			$default['aspect']     = 4 / 3;
			$default['video_width']  = $width;
			$default['video_height'] = round( $width / 4 * 3 );
			$default['size']       = "{$default['video_width']}x{$default['video_height']}";
			$default['offset']     = '00:00:10';

			if( extension_loaded( 'ffmpeg' )) {
				// we silence these calls since they might spew errors
				$movie = @new ffmpeg_movie( $source );
				$info = array(
					'vcodec'           => @$movie->getVideoCodec(),
					'duration'         => round( @$movie->getDuration() ),
					'width'            => @$movie->getFrameWidth(),
					'height'           => @$movie->getFrameHeight(),
					'video_bitrate'    => @$movie->getVideoBitRate(),
					'acodec'           => @$movie->getAudioCodec(),
					'audio_bitrate'    => @$movie->getAudioBitRate(),
					'audio_samplerate' => @$movie->getAudioSampleRate(),
				);

				// make sure audio sample rate is valid
				if( !empty( $info['audio_samplerate'] ) && !in_array( $info['audio_samplerate'], array( 11025, 22050, 44100 ))) {
					unset( $info['audio_samplerate'] );
				}
			} else {
				// alternative method using ffmpeg to fetch source dimensions
				$command = "$ffmpeg -i ".escapeshellarg( $source ).' 2>&1';
				exec( $command, $output, $status );
				if( !preg_match( '/Stream #(?:[0-9\.]+)(?:.*)\: Video: (?P<videocodec>.*) (?P<width>[0-9]*)x(?P<height>[0-9]*)/', implode( '\n', $output ), $matches )) {
					preg_match( '/Could not find codec parameters \(Video: (?P<videocodec>.*) (?P<width>[0-9]*)x(?P<height>[0-9]*)\)/', implode( '\n', $output ), $matches );
				}

				if( !empty( $matches['width'] ) && !empty( $matches['height'] )) {
					$info['width'] = $matches['width'];
					$info['height'] = $matches['height'];
				}
			}

			// our player supports flv and h264 so we might as well use the default
			if( !$gBitSystem->isFeatureActive( 'mime_video_force_encode' ) && !empty( $info )
				&& (
					// accepted video + audio combinations that can be played by the video player directly
					   ( $info['vcodec'] == 'h264' && ( empty( $info['acodec'] ) || $info['acodec'] == 'mpeg4aac' || $info['acodec'] == 'aac' ))
					|| ( $info['vcodec'] == 'flv'  && ( empty( $info['acodec'] ) || $info['acodec'] == 'mp3' ))
				)
			) {
				// work out what the target filename is
				$extension = (( $info['vcodec'] == "flv" ) ? "flv" : "mp4" );
				$dest_file = $destPath."/flick.$extension";

				// if the video can be processed by ffmpeg-php, width and height are greater than 1
				if( !empty( $info['width'] ) && $info['width'] > 1 ) {
					$info['aspect']   = $info['width'] / $info['height'];
					$info['offset']   = strftime( "%T", round( $info['duration'] / 5 - ( 60 * 60 )));
				} else {
					$info = $default;
				}

				// store prefs and create thumbnails
				LibertyMime::expungeMetaData( $pParamHash['attachment_id'] );
				LibertyMime::storeMetaData( $pParamHash['attachment_id'], 'Video', $info );
				mime_video_create_thumbnail( $source, $info['offset'] );
				if( !is_file( $dest_file ) && !link( $source, $dest_file )) {
					copy( $source, $dest_file );
				}
				mime_video_fix_streaming( $dest_file );

				$log['message'] = 'SUCCESS: Converted to flash video';
				$actionLog['log_message'] = "Video file was successfully uploaded and thumbnails extracted.";
				$ret = TRUE;
			} else {
				// work out what the target filename is
				$extension = (( $codec == "flv" ) ? "flv" : "mp4" );
				$dest_file = $destPath."/flick.$extension";

				// if the video can be processed by ffmpeg-php, width and height are greater than 1
				if( !empty( $info['width'] ) && $info['width'] > 1 ) {
					// reset some values to reduce video size
					if( $info['width'] < $width ) {
						$width = $info['width'];
					}

					// here we calculate the size and aspect ratio of the output video
					$size_ratio         = $width / $info['width'];
					$info['aspect']     = $info['width'] / $info['height'];
					$info['video_width']  = $width;
					$info['video_height'] = round( $size_ratio * $info['height'] );
					// height of video needs to be an even number
					if( $info['video_height'] % 2 ) {
						$info['video_height']++;
					}
					$info['size']       = "{$info['video_width']}x{$info['video_height']}";
				} else {
					$info = $default;
				}

				// transfer settings to vars for easy manipulation for various APIs of ffmpeg
				$audio_bitrate    = ( $gBitSystem->getConfig( 'mime_video_audio_bitrate', 32000 ) / 1000 ).'kb';
				$audio_samplerate = $gBitSystem->getConfig( 'mime_video_audio_samplerate', 22050 );
				$video_bitrate    = ( $gBitSystem->getConfig( 'mime_video_video_bitrate', 160000 ) / 1000 ).'kb';
				$acodec_mp3       = $gBitSystem->getConfig( 'ffmpeg_mp3_lib', 'libmp3lame' );
				$me_param         = $gBitSystem->getConfig( 'ffmpeg_me_method', 'me' );

				if( $codec == "h264" ) {
					$parameters =
						" -i '$source'".
						// audio
						" -acodec libfaac".
						" -ab $audio_bitrate".
						" -ar $audio_samplerate".
						// video
						" -vcodec libx264".
						" -b $video_bitrate".
						" -bt $video_bitrate".
						" -s ".$info['size'].
						" -aspect ".$info['aspect'].
						" -flags +loop -cmp +chroma -refs 1 -coder 0 -me_range 16 -g 300 -keyint_min 25 -sc_threshold 40 -i_qfactor 0.71 -maxrate 10M -bufsize 10M -rc_eq 'blurCplx^(1-qComp)' -qcomp 0.6 -qmin 10 -qmax 51 -qdiff 4 -level 30".
						" -partitions +parti4x4+partp8x8+partb8x8 -$me_param epzs -subq 5 -trellis 1".
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
						" -b $video_bitrate".
						" -bt $video_bitrate".
						" -s ".$info['size'].
						" -aspect ".$info['aspect'].
						" -flags +loop -cmp +chroma -refs 1 -coder 0 -me_range 16 -g 300 -keyint_min 25 -sc_threshold 40 -i_qfactor 0.71 -bf 16 -maxrate 10M -bufsize 10M -rc_eq 'blurCplx^(1-qComp)' -qcomp 0.6 -qmin 10 -qmax 51 -qdiff 4 -level 30".
						" -partitions 0 -$me_param epzs -subq 1 -trellis 0".
						// output
						" -y '$dest_file'";

					// pass 2
					$parameters2 =
						" -i '$source'".
						// audio
						" -acodec libfaac".
						" -ab $audio_bitrate".
						" -ar $audio_samplerate".
						// video
						" -pass 2".
						" -passlogfile $passlogfile".
						" -vcodec libx264".
						" -b $video_bitrate".
						" -bt $video_bitrate".
						" -s ".$info['size'].
						" -aspect ".$info['aspect'].
						" -flags +loop -cmp +chroma -me_range 16 -g 250 -keyint_min 25 -sc_threshold 40 -i_qfactor 0.71 -rc_eq 'blurCplx^(1-qComp)' -qcomp 0.6 -qmin 10 -qmax 51 -qdiff 4".
						" -partitions +parti8x8+parti4x4+partp8x8+partp4x4+partb8x8 -flags2 +brdo+dct8x8+wpred+bpyramid+mixed_refs -$me_param epzs -subq 7 -trellis 1 -refs 6 -bf 16 -directpred 3 -b_strategy 1 -bidir_refine 1 -coder 1".
						// output
						" -y '$dest_file'";

				} else {
					$parameters =
						" -i '$source'".
						// audio
						" -acodec $acodec_mp3".
						" -ab $audio_bitrate".
						" -ar $audio_samplerate".
						// video
						" -f flv".
						" -b $video_bitrate".
						" -bt $video_bitrate".
						" -s ".$info['size'].
						" -aspect ".$info['aspect'].
						// output
						" -y '$dest_file'";
				}

				if( $pOnlyGetParameters ) {
					return $parameters;
				} else {
					// we keep the output of this that we can store it to the error file if we need to do so
					$debug = shell_exec( "$ffmpeg $parameters 2>&1" );
					if( !empty( $parameters2 )) {
						$debug .= shell_exec( "$ffmpeg $parameters2 2>&1" );
						// change back to whence we came
						chdir( $cwd );
					}
				}

				// make sure the conversion was successfull
				if( is_file( $dest_file ) && filesize( $dest_file ) > 48 ) {
					mime_video_fix_streaming( $dest_file );

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
					LibertyMime::expungeMetaData( $pParamHash['attachment_id'] );
					LibertyMime::storeMetaData( $pParamHash['attachment_id'], 'Video', $info );

					// since the flv conversion worked, we will create a preview screenshots to show.
					mime_video_create_thumbnail( $dest_file, $info['offset'] );

					$log['message'] = 'SUCCESS: Converted to flash video';
					$actionLog['log_message'] = "Converted to flashvideo in ".( date( 'U' ) - $begin )." seconds";
					$ret = TRUE;
				} else {
					// remove unsuccessfully converted file
					@unlink( $dest_file );
					$log['message'] = "ERROR: The video you uploaded could not be converted by ffmpeg.\nDEBUG OUTPUT:\n\n".$debug;
					$actionLog['log_message'] = "Video could not be converted to flashvideo. An error dump was saved to: ".$destPath.'/error';

					// write error message to error file
					$h = fopen( $destPath."/error", 'w' );
					fwrite( $h, "$ffmpeg $parameters\n\n$debug" );
					fclose( $h );
				}
				@unlink( $destPath.'/processing' );
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
		LibertyContent::storeActionLogFromHash( array( 'action_log' => $actionLog ));

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
function mime_video_create_thumbnail( $pFile, $pOffset = 60 ) {
	global $gBitSystem;
	$ret = FALSE;
	if( !empty( $pFile ) && is_file( $pFile )) {
		$destPath = dirname( $pFile );

		// try to use an app designed specifically to extract a thumbnail
		if( shell_exec( shell_exec( 'which ffmpegthumbnailer' ).' -h' )) {
			$thumbnailer = trim( shell_exec( 'which ffmpegthumbnailer' ));
		} elseif( shell_exec( shell_exec( 'which ffmpegvideothumbnailer' ).' -h' )) {
			$thumbnailer = trim( shell_exec( 'which ffmpegvideothumbnailer' ));
		}

		if( !empty( $thumbnailer ) && is_executable( $thumbnailer )) {
			shell_exec( "$thumbnailer -i '$pFile' -o '$destPath/thumb.jpg' -s 1024" );
		}

		if( is_file( "$destPath/thumb.jpg" ) && filesize( "$destPath/thumb.jpg" ) > 1 ) {
			$fileHash['type']            = 'image/jpg';
			$fileHash['source_file']     = "$destPath/thumb.jpg";
			$fileHash['dest_branch']       = str_replace( STORAGE_PKG_PATH, '', "$destPath/" );
			liberty_generate_thumbnails( $fileHash );
			$ret = TRUE;

			// remove temp file
			@unlink( "$destPath/thumb.jpg" );
		} else {
			// fall back to using ffmepg
			$ffmpeg = trim( $gBitSystem->getConfig( 'ffmpeg_path', shell_exec( 'which ffmpeg' )));
			shell_exec( "$ffmpeg -i '$pFile' -an -ss $pOffset -t 00:00:01 -r 1 -y '$destPath/preview%d.jpg'" );
			if( is_file( "$destPath/preview1.jpg" )) {
				$fileHash['type']            = 'image/jpg';
				$fileHash['source_file']     = "$destPath/preview1.jpg";
				$fileHash['dest_branch']       = str_replace( STORAGE_PKG_PATH, '', "$destPath/" );
				liberty_generate_thumbnails( $fileHash );
				$ret = TRUE;

				// remove temp file
				@unlink( "$destPath/preview1.jpg" );
			}
		}
	}
	return $ret;
}

/**
 * mime_video_calculate_videosize Calculate the display video size
 *
 * @param array $pFileHash File information including attachment_id
 * @param array $pCommonObject common object - calculations will be stored in $pCommonObject->mStoragePrefs
 * @access public
 * @return void
 */
function mime_video_calculate_videosize( &$pMetaData, $pParams ) {
	global $gBitSystem, $gThumbSizes;

	// fetch default if width is missing
	if( empty( $pMetaData['width'] )) {
		$pMetaData['width']  = $gBitSystem->getConfig( 'mime_video_width', 320 );
	}

	// use aspect to calculate height since it might be different from original
	$pMetaData['height'] = ( $pMetaData['width'] / ( !empty( $pMetaData['aspect'] ) ? $pMetaData['aspect'] : 4 / 3 ));

	// if we want to display a different size
	if( !empty( $pParams['size'] ) && !empty( $gThumbSizes[$pParams['size']]['width'] )) {
		$new_width = $gThumbSizes[$pParams['size']]['width'];
	} elseif( $gBitSystem->isFeatureActive( 'mime_video_default_size' ) && !empty( $gThumbSizes[$gBitSystem->getConfig( 'mime_video_default_size' )]['width'] )) {
		$new_width = $gThumbSizes[$gBitSystem->getConfig( 'mime_video_default_size' )]['width'];
	}

	// if we want to change the video size
	if( !empty( $new_width )) {
		$ratio = $pMetaData['width'] / $new_width;
		$pMetaData['height'] = round( $pMetaData['height'] / $ratio );

		// now that all calculations are done, we apply the width
		$pMetaData['width']  = $new_width;
	}
}

/**
 * mime_video_fix_streaming will make sure the MOOV atom is at the beginning of the MP4 file to enable streaming
 *
 * @param array $pVideoFile
 * @access public
 * @return string shell result on success, FALSE on failure
 */
function mime_video_fix_streaming( $pVideoFile ) {
	global $gBitSystem;
	$ret = FALSE;
	if( preg_match( '#\.mp4$#', $pVideoFile ) && $gBitSystem->isFeatureActive( 'mp4box_path' )) {
		$ret = shell_exec( $gBitSystem->getConfig( 'mp4box_path' )." -add $pVideoFile -new $pVideoFile" );
	}
	return $ret;
}
?>
