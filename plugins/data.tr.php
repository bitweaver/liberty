<?php
/**
 * tr translation plugin
 *
 * @author     wjames5 will@tekimaki.com 
 * @version    $Revision: 1.2 $
 * @package    liberty
 * @subpackage plugins_data
 * @copyright  Copyright (c) 2008, bitweaver.org
 */

/**
 * Setup Code
 */
define( 'PLUGIN_GUID_DATATR', 'datatr' );
global $gLibertySystem;
$pluginParams = array (
	'tag'           => 'TR',
	'auto_activate' => TRUE,
	'requires_pair' => TRUE,
	'load_function' => 'data_tr',
	'title'         => 'Translate',
	'help_page'     => 'DataPluginTR',
	'description'   => tra( "Use this plugin to mark strings for translation. You should only use this for common short strings, and not entire pages." ),
	'help_function' => 'data_tr_help',
	'syntax'        => "{tr}",
	'path'          => LIBERTY_PKG_PATH.'plugins/data.tr.php',
	'security'      => 'registered',
	'plugin_type'   => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATATR, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATATR );

function data_tr_help() {
	$help = tra( "Example: " ) . "{tr}string of text to be translated{/tr}";
	return $help;
}

function data_tr( $pData, $pParams, $pCommonObject ) {
	$transString = tra( $pData );
	$parseHash = $pCommonObject->mInfo;
	$parseHash['no_cache'] = TRUE;
	$parseHash['data'] = $transString;
	$parsedData = $pCommonObject->parseData( $parseHash );
	$parsedData = preg_replace( '|<br\s*/?>$|', '', $parsedData );
	return $parsedData;
}
?>
