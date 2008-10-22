<?php
/**
 * @version  $Revision: 1.1 $
 * @package  liberty
 * @subpackage plugins_format
 */
global $gLibertySystem;

/**
 * definitions
 */
define( 'PLUGIN_GUID_SIMPLETEXT', 'simpletext' );

$pluginParams = array (
	'load_function'   => 'simpletext_parse_data',
	'verify_function' => 'simpletext_verify_data',
	'description'     => 'Simple Syntax Format Parser',
	'edit_label'      => 'TEXT',
	'edit_field'      => PLUGIN_GUID_SIMPLETEXT,
	'help_page'       => 'SimpleTextSyntax',
	'plugin_type'     => FORMAT_PLUGIN,
	'linebreak'       => '<br />'
);

$gLibertySystem->registerPlugin( PLUGIN_GUID_SIMPLETEXT, $pluginParams );

function simpletext_verify_data( &$pParamHash ) {
	$pParamHash['content_store']['data'] = $pParamHash['edit'];
}

function simpletext_parse_data( &$pParseHash, &$pCommonObject ) {
	return nl2br( htmlentities( $pParseHash['data'] ) );
}
?>
