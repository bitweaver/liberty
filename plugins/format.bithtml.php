<?php
/**
 * @version  $Revision$
 * @package  liberty
 * @subpackage plugins_format
 */
global $gLibertySystem;

/**
 * definitions
 */
define( 'PLUGIN_GUID_BITHTML', 'bithtml' );

$pluginParams = array (
	'load_function'   => 'bithtml_parse_data',
	'verify_function' => 'bithtml_verify_data',
	'description'     => 'HTML Syntax Format Parser',
	'edit_label'      => 'HTML',
	'edit_field'      => PLUGIN_GUID_BITHTML,
	'help_page'       => 'HTMLSyntax',
	'plugin_type'     => FORMAT_PLUGIN,
	'linebreak'       => '<br />'
);

$gLibertySystem->registerPlugin( PLUGIN_GUID_BITHTML, $pluginParams );

function bithtml_verify_data( &$pParamHash ) {
	$pParamHash['content_store']['data'] = $pParamHash['edit'];
}

function bithtml_parse_data( &$pParseHash, &$pCommonObject ) {
	return $pParseHash['data'];
}
?>
