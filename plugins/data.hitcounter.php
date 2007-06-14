<?php
/**
 * @version  $Revision: 1.5 $
 * @package  liberty
 * @subpackage plugins_data
 */
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See copyright.txt for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Author: James H. Thompson jht@lj.net
// +----------------------------------------------------------------------+

/**
 * definitions
 */
global $gBitSystem;

// this executes before all packages are registered so can't reliably check isPackageActive here!
if( 1 || $gBitSystem->isPackageActive( 'wiki' ) ) {
define( 'PLUGIN_GUID_DATA_HITCOUNTER', 'datahitcounter' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'HITCOUNTER',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_hitcounter',
						'title' => 'Hit Counter',                                                                             // and Remove the comment from the start of this line
						'help_page' => 'DataPluginHitCounter',
						'description' => tra("This plugin will display the hit count for a page."),
						'help_function' => 'data_hitcounter_help',
						'syntax' => "{HITCOUNTER}",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATA_HITCOUNTER, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATA_HITCOUNTER );

global $gBitSmarty;

// Help Routine
function data_hitcounter_help() {
	$help =
		''
		. tra("Example: ") . "{HITCOUNTER}";
	return $help;
}

// The handler for the plugin
function data_hitcounter($data, $params, &$pCommonObject) {
    $pCommonObject->getHits();
	$display_result = '0';
	if (!empty($pCommonObject->mInfo['hits'])) {
		$display_result = $pCommonObject->mInfo['hits'];    
	}

	return $display_result;
}
}
?>