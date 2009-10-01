<?php
/**
 * @version  $Revision: 1.4 $
 * @package  liberty
 * @subpackage plugins_data
 */
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See copyright.txt for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Author: James H Thompson (jht@lj.net)
// +----------------------------------------------------------------------+

/**
 * definitions
 */
global $gBitSystem;
// this executes before all packages are registered so can't reliably check isPackageActive here!
define( 'PLUGIN_GUID_DATA_CREATIONTIME', 'datacreationtime' );
global $gLibertySystem;
$pluginParams = array (
	'tag'           => 'CREATIONTIME',
	'auto_activate' => TRUE,
	'requires_pair' => FALSE,
	'load_function' => 'data_creationtime',
	'title'         => 'Creation Time',
	'help_page'     => 'DataPluginCreationTime',
	'description'   => tra("This plugin will display the creation time of a page."),
	'help_function' => 'data_creationtime_help',
	'syntax'        => "{creationtime}",
	'plugin_type'   => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATA_CREATIONTIME, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATA_CREATIONTIME );

global $gBitSmarty;
require_once $gBitSmarty->_get_plugin_filepath('modifier','bit_short_datetime');

// Help Routine
function data_creationtime_help() {
	return tra( "Example: " )."{creationtime}<br />";
}

//The actual handler for the plugin 
function data_creationtime( $data, $params, &$pCommonObject ) {
	return smarty_modifier_bit_short_datetime( $pCommonObject->mInfo['created'] );
}
?>
