<?php
/**
 * @version  $Revision: 1.1 $
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
	'syntax'        => '{biticon ipackage= iname= iexplain}',
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
				.'<td>' . tra( "key-words") . '<br />' . tra( "(required)" ) . '</td>'
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
	require_once $gBitSmarty->_get_plugin_filepath( 'function', 'biticon' );
	return smarty_function_biticon( $pParams, $gBitSmarty );
}
?>
