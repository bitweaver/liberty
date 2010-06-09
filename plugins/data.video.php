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
	'preload_function'    => 'data_video_preload',
	'load_function' => 'data_video',
	'title'         => 'Video',
	'help_page'     => 'DataPluginVideo',
	'description'   => tra( "This plugin allows you to simply and safely insert a video in a page. Currently it only supports Flash Video (.flv) files." ),
	'help_function' => 'data_video_help',
	'syntax'        => "{video video= player=}",
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
				.'<td>' . tra( "URL of the video file. E.g. http://example.com/foo.flv" ) . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>player</td>'
				.'<td>' . tra( "sting" ) . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "URL of the player object. E.g. http://example.com/player.swf" ) . '</td>'
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
		. tra( "Example: " ) . '{video video=http://example.com/foo.flv player=http://example.com/player.swf width=425 height=355}';
}

/**
 * data_video_preload This function is loaded on every page load before anything happens and is used to load required scripts.
 * 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function data_video_preload() {
	global $gBitThemes;
	$gBitThemes->loadJavascript( UTIL_PKG_PATH."javascript/flv_player/swfobject.js", FALSE, 25 );
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
	// static var in case multiple videos on one page
	static $playerCount = 1;
	extract( $pParams );
	$width   = ( !empty( $width )  ? $width  : "425" );
	$height  = ( !empty( $height ) ? $height : "355" );

	if( empty( $player ) ) {
		$player = UTIL_PKG_URL.'javascript/flv_player/mediaplayer.swf';
	}

	$playerId = "flv_player_".$playerCount++;

	$ret = '<div class="video-plugin">
<p id="'.$playerId.'"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this video.</p>
<script type="text/javascript"> var so = new SWFObject(\''.$player.'\',\'player\',\''.$width.'\',\''.$height.'\',\'7\');   so.addVariable("file","'.$video.'");so.addVariable("overstretch","fit"); so.addVariable("frontcolor","0xffffff"); so.addVariable("backcolor","0x193d55"); so.write(\''.$playerId.'\');</script>';

	return $ret;
}
?>
