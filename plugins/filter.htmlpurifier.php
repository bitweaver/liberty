<?php
/**
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/plugins/filter.htmlpurifier.php,v 1.6 2007/06/11 17:48:50 squareing Exp $
 * @package  liberty
 * @subpackage plugins_filter
 */

/**
 * definitions ( guid character limit is 16 chars )
 */
define( 'PLUGIN_GUID_FILTERHTMLPURIFIER', 'filterhtmlpure' );

global $gLibertySystem;

$pluginParams = array (
	// plugin title
	'title'                    => 'HTML Purification',
	// help page on bitweaver org that explains this plugin
	'help_page'                => 'Html Purifier',
	// brief description of the plugin
	'description'              => "Will try to sanitise any HTML output to make it HTML compliant.",
	// should this plugin be active or not when loaded for the first time
	'auto_activate'            => FALSE,
	// absolute path to this plugin
	'path'                     => LIBERTY_PKG_PATH.'plugins/filter.htmlpurifier.php',
	// type of plugin
	'plugin_type'              => FILTER_PLUGIN,
	// url to page with options for this plugin
	'plugin_settings_url'      => LIBERTY_PKG_URL.'admin/filter_htmlpurifier.php',

	// various filter functions and when they are called
	// called before the data is parsed
	'prefilter_function'       => 'htmlpure_prefilter',
	// called after the data has been parsed
	'postfilter_function'      => 'htmlpure_postfilter',
	// called before the data is parsed if there is a split
	'presplitfilter_function'  => 'htmlpure_presplitfilter',
	// called arter the data has been parsed if there is a split
	'postsplitfilter_function' => 'htmlpure_postsplitfilter',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_FILTERHTMLPURIFIER, $pluginParams );

function htmlpure_prefilter( $pData, $pFilterHash ) {
	//$pData = '... prefilter ... '.$pData;
	return $pData;
}

function htmlpure_postfilter( $pData, $pFilterHash ) {
	global $gLibertySystem;
	//$pData = '... postfilter ... '.$pData;
	return $gLibertySystem->advancedPurifyHtml( $pData );
}

function htmlpure_presplitfilter( $pData, $pFilterHash ) {
	//$pData = '... presplitfilter ... '.$pData;
	return $pData;
}

function htmlpure_postsplitfilter( $pData, $pFilterHash ) {
	global $gLibertySystem;
	//$pData = '... postsplitfilter ... '.$pData;
	return $gLibertySystem->advancedPurifyHtml( $pData );
}

?>
