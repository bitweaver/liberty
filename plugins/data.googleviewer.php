<?php
/**
 * @version  $Revision: 1.1 $
 * @package  liberty
 * @subpackage plugins_data
 */

/**
 * definitions
 */

global $gLibertySystem;
define( 'PLUGIN_GUID_DATAGOOGLEVIEWER', 'datagoogleviewer' );
$pluginParams = array (
	'tag'           => 'GOOGLEVIEWER',
	'auto_activate' => FALSE,
	'requires_pair' => FALSE,
	'load_function' => 'data_googleviewer',
	'title'         => 'Google Viewer',
	'help_page'     => 'DataPluginGoogleviewer',
	'description'   => tra( "This plugin allows you to simply embed a PDF document in a page using the embeddable Google Viewer." ),
	'help_function' => 'data_googleviewer_help',
	'syntax'        => "{googleviewer url=}",
	'plugin_type'   => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAGOOGLEVIEWER, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAGOOGLEVIEWER );

/**
 * data_googleviewer_help 
 * 
 * @access public
 * @return HTML help in a table
 */
function data_googleviewer_help() {
	return
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>url</td>'
				.'<td>' . tra( "string" ) . '<br />' . tra("(required)") . '</td>'
				.'<td>' . tra( "URL of the PDF online" ) . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>width</td>'
				.'<td>' . tra( "numeric" ) . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "Alternate width of the Google Viewer box in pixels.  Default is 100%." ) . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>height</td>'
				.'<td>' . tra( "numeric" ) . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Alternate height of the Google Viewer box in pixels. Default is 650." ) . '</td>'
			.'</tr>'
		.'</table>'
		. tra( "Example: " ) . '{googleviewer url=XXXXX width=425 height=355}';
}

/**
 * data_googleviewer 
 * 
 * @param array $pData 
 * @param array $pParams 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function data_googleviewer( $pData, $pParams ) {
	extract( $pParams );
	$width   = ( !empty( $width )  ? $width  : "100%" );
	$height  = ( !empty( $height ) ? $height : "650" );

	if( !empty( $url )) {
		return '<!--~np~--><iframe width="'.$width.'" height="'.$height.'" style="border:none;" src="http://docs.google.com/viewer?embedded=true&url='.urlencode($url).'"></iframe><!--~/np~-->';
	} else {
		return tra( 'No URL given' );
	}
}
?>
