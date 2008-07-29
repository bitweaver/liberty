<?php
/**
 * @version  $Revision: 1.7 $
 * @package  liberty
 * @subpackage plugins_data
 */

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATABITICON', 'databiticon' );
global $gLibertySystem;
$pluginParams = array (
	'tag'           => 'biticon',
	'auto_activate' => TRUE,
	'requires_pair' => FALSE,
	'load_function' => 'data_biticon',
	'title'         => 'bitweaver Icon',
	'help_page'     => 'DataPluginBiticon',
	'description'   => tra( "Display any bitweaver icon" ),
	'help_function' => 'data_biticon_help',
	'syntax'        => '{biticon ipackage= iname= iexplain=}',
	'path'          => LIBERTY_PKG_PATH.'plugins/data.biticon.php',
	'plugin_type'   => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATABITICON, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATABITICON );

/**
 * data_biticon_help 
 * 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function data_biticon_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>ipackage</td>'
				.'<td>' . tra( "key-words") . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "Package the icon is taken from. The icon style icons take the value 'icons'.") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>iname</td>'
				.'<td>' . tra( "key-words") . '<br />' . tra("(required)") . '</td>'
				.'<td>' . tra( "Name of the icon to be displayed" ) . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>ixplain</td>'
				.'<td>' . tra( "string" ) . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Explanation of the icon - visible when hovering over the icon.").'</td>'
			.'</tr>'
		.'</table>'
		. tra( "Example: " ) . '{biticon ipackage="icons" iname="large/accessories-text-editor" iexplain="edit"}';
	return $help;
}

function data_biticon( $pData, $pParams ) {
	global $gBitSmarty;
	$ret = tra( 'Please provide an icon name as iname parameter. You can <a href="'.THEMES_PKG_URL.'icon_browser.php">select icons here</a>.' );

	if( !empty( $pParams['iname'] )) {
		require_once $gBitSmarty->_get_plugin_filepath( 'function', 'biticon' );

		// sanitise biticon parameters before they are passed to the function
		$biticon['iname']    = $pParams['iname'];
		$biticon['ipackage'] = !empty( $pParams['ipackage'] ) ? $pParams['ipackage'] : 'icons';
		$biticon['iexplain'] = !empty( $pParams['iexplain'] ) ? $pParams['iexplain'] : 'icon';
		$ret = smarty_function_biticon( $biticon, $gBitSmarty );
		$wrapper = liberty_plugins_wrapper_style( $pParams, FALSE );
		if( !empty( $wrapper['style'] )) {
			$ret ='<'.$wrapper['wrapper'].' class="'.( !empty( $wrapper['class'] ) ? $wrapper['class'] : "biticon-plugin" ).'" style="'.$wrapper['style'].'">'.$ret.'</'.$wrapper['wrapper'].'>';
		}
	}
	return $ret;
}
?>
