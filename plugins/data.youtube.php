<?php
/**
 * @version  $Revision: 1.4 $
 * @package  liberty
 * @subpackage plugins_data
 */

/**
 * definitions
 */

global $gLibertySystem;
define( 'PLUGIN_GUID_DATAYOUTUBE', 'datayoutube' );
$pluginParams = array (
	'tag'           => 'YOUTUBE',
	'auto_activate' => FALSE,
	'requires_pair' => FALSE,
	'load_function' => 'data_youtube',
	'title'         => 'Youtube',
	'help_page'     => 'DataPluginYoutube',
	'description'   => tra( "This plugin allows you to simply and safely insert a YouTube video in a page." ),
	'help_function' => 'data_youtube_help',
	'syntax'        => "{youtube id=}",
	'path'          => LIBERTY_PKG_PATH.'plugins/data.youtube.php',
	'plugin_type'   => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAYOUTUBE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAYOUTUBE );

/**
 * data_youtube_help 
 * 
 * @access public
 * @return HTML help in a table
 */
function data_youtube_help() {
	return
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>id</td>'
				.'<td>' . tra( "string" ) . '<br />' . tra("(required)") . '</td>'
				.'<td>' . tra( "ID nr of the Youtube video. You can get this from the URL of the Youtube video you are watching e.g.: pShf2VuAu_Q" ) . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>width</td>'
				.'<td>' . tra( "numeric" ) . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "Alternate width of the youtube box in pixels." ) . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>height</td>'
				.'<td>' . tra( "numeric" ) . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Alternate height of the youtube box in pixels." ) . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>lang</td>'
				.'<td>' . tra( "string" ) . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Alternate language of the Youtube interface, default is 'en'" ).'</td>'
			.'</tr>'
			// currently this is not possible. read up about this issue on:
			// http://blog.jimmyr.com/High_Quality_on_Youtube_11_2008.php
//			.'<tr class="odd">'
//				.'<td>quality</td>'
//				.'<td>' . tra( "string" ) . '<br />' . tra("(optional)") . '</td>'
//				.'<td>' . tra( "Fetch the high resolution version instead of the normal one - not available for all videos." ).'</td>'
//			.'</tr>'
		.'</table>'
		. tra( "Example: " ) . '{youtube id=XXXXX width=425 height=355 lang=en}';
}

/**
 * data_youtube 
 * 
 * @param array $pData 
 * @param array $pParams 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function data_youtube( $pData, $pParams ) {
	extract( $pParams );
	$width   = ( !empty( $width )   ? $width   : "425" );
	$height  = ( !empty( $height )  ? $height  : "355" );
	$hl      = ( !empty( $lang )    ? $lang    : "en" );

	if( !empty( $id )) {
		return '<!--~np~--><object width="'.$width.'" height="'.$height.'"><param name="movie" value="http://www.youtube.com/v/'.$id.'&amp;hl='.$hl.'"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/'.$id.'&amp;hl='.$hl.'" type="application/x-shockwave-flash" wmode="transparent" width="'.$width.'" height="'.$height.'""></embed></object><!--~/np~-->';
	} else {
		return tra( 'No ID given' );
	}
}
?>
