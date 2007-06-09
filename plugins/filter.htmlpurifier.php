<?php
/**
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/plugins/filter.htmlpurifier.php,v 1.2 2007/06/09 11:43:12 squareing Exp $
 * @package  liberty
 * @subpackage plugins_filter
 */

/**
 * definitions ( guid character limit is 16 chars )
 */
define( 'PLUGIN_GUID_FILTERHTMLPURIFIER', 'filterhtmlpure' );

global $gLibertySystem, $gContent;

$pluginParams = array (
	'title'                => 'HTML Purification',
	'help_page'            => 'Html Purifier',
	'description'          => "Will try to sanitise any HTML output to make it HTML compliant.",
	'auto_activate'        => FALSE,
	'prefilter_function'   => 'prefilter_htmlpure',
	'splitfilter_function' => 'splitfilter_htmlpure',
	'postfilter_function'  => 'postfilter_htmlpure',
	'path'                 => LIBERTY_PKG_PATH.'plugins/filter.htmlpurifier.php',
	'plugin_type'          => FILTER_PLUGIN,
	'plugin_settings_url'  => LIBERTY_PKG_URL.'filter_htmlpurifier.php',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_FILTERHTMLPURIFIER, $pluginParams );

function prefilter_htmlpure( $pFilterHash ) {
	vd( 'prefilter' );
	return $pFilterHash['data'];
}

function splitfilter_htmlpure( $pFilterHash ) {
	vd( 'splitfilter' );
	return $pFilterHash['data'];
}

function postfilter_htmlpure( $pFilterHash ) {
	vd( 'postfilter' );
	return $pFilterHash['data'];
}
?>
