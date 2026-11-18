<?php
/**
 * @version  $Revision$
 * @package  liberty
 * @subpackage plugins_data
 */

/**
 * definitions
 */

global $gLibertySystem;
define( 'PLUGIN_GUID_DATAVIDEO', 'datavideo' );
$pluginParams = array (
	'tag'           => 'VIDEO',
	'auto_activate' => FALSE,
	'requires_pair' => FALSE,
	'load_function' => 'data_video',
	'title'         => 'Video',
	'help_page'     => 'DataPluginVideo',
	'description'   => tra( "Insert a video file (MP4, WebM or Ogg) into a page with the browser's built-in player." ),
	'help_function' => 'data_video_help',
	'syntax'        => "{video video= width= height=}",
	'plugin_type'   => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAVIDEO, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAVIDEO );

/**
 * data_video_help 
 * 
 * @access public
 * @return HTML help in a table
 */
function data_video_help() {
	return
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>video</td>'
				.'<td>' . tra( "string" ) . '<br />' . tra("(required)") . '</td>'
				.'<td>' . tra( "URL of the video file. E.g. http://example.com/foo.mp4" ) . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>height</td>'
				.'<td>' . tra( "numeric" ) . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Alternate height of the video box in pixels." ) . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>width</td>'
				.'<td>' . tra( "numeric" ) . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "Alternate width of the video box in pixels." ) . '</td>'
			.'</tr>'
		.'</table>'
		. tra( "Example: " ) . '{video video=http://example.com/foo.mp4 width=640 height=360}';
}

/**
 * data_video 
 * 
 * @param array $pData 
 * @param array $pParams 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function data_video( $pData, $pParams ) {
	$video  = trim( (string)( $pParams['video'] ?? '' ));
	$width  = ( !empty( $pParams['width'] )  && is_numeric( $pParams['width'] )  ? (int)$pParams['width']  : 425 );
	$height = ( !empty( $pParams['height'] ) && is_numeric( $pParams['height'] ) ? (int)$pParams['height'] : 355 );

	// only plain web paths: no javascript: or data: URLs
	if( $video === '' || !preg_match( '#^(https?://|/)#i', $video )) {
		return tra( 'No video given' );
	}
	$src = htmlspecialchars( $video, ENT_QUOTES, 'UTF-8' );

	if( preg_match( '#\.flv(\?|$)#i', $video )) {
		// Flash video cannot be played by any current browser
		return '<div class="video-plugin"><a href="'.$src.'">'.tra( 'Download video' ).'</a></div>';
	}

	return '<div class="video-plugin"><video controls preload="metadata" playsinline width="'.$width.'" height="'.$height.'" style="max-width:100%;height:auto" src="'.$src.'">'
		.'<a href="'.$src.'">'.tra( 'Download video' ).'</a></video></div>';
}
?>
