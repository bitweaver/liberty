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
	$id     = trim( (string)( $pParams['id'] ?? '' ));
	$width  = ( !empty( $pParams['width'] )  && is_numeric( $pParams['width'] )  ? (int)$pParams['width']  : 425 );
	$height = ( !empty( $pParams['height'] ) && is_numeric( $pParams['height'] ) ? (int)$pParams['height'] : 355 );
	$lang   = ( !empty( $pParams['lang'] ) && preg_match( '/^[a-z]{2}(-[A-Za-z]{2})?$/', $pParams['lang'] ) ? $pParams['lang'] : 'en' );

	if( !preg_match( '/^[A-Za-z0-9_-]{6,20}$/', $id )) {
		return tra( 'No ID given' );
	}
	return '<!--~np~--><iframe class="youtube-plugin" width="'.$width.'" height="'.$height.'" style="max-width:100%" src="https://www.youtube-nocookie.com/embed/'.$id.'?hl='.$lang.'" title="YouTube video" loading="lazy" frameborder="0" allow="encrypted-media; picture-in-picture; fullscreen"></iframe><!--~/np~-->';
}
?>
