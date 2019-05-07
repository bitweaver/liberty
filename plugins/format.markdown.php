<?php
/**
 * @version  $Revision$
 * @package  liberty
 * @subpackage plugins_format
 */

/**
 * Initialization
 */
global $gLibertySystem;

/**
 * definitions
 */
define( 'PLUGIN_GUID_MARKDOWN', 'markdown' );

$pluginParams = array (
	'load_function'   => 'markdown_parse_data',
	'verify_function' => 'markdown_verify_data',
	'auto_activate'   => FALSE,
	'description'     => 'This parser allows you to use plain text, which is then converted to HTML. For the full syntax, please view <a href ="http://daringfireball.net/projects/markdown/syntax">Markdown Syntax</a>',
	'edit_label'      => 'Markdown',
	'edit_field'      => PLUGIN_GUID_MARKDOWN,
	'plugin_type'     => FORMAT_PLUGIN,
	'linebreak'       => "\r\n"
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_MARKDOWN, $pluginParams );

function markdown_verify_data( &$pParamHash ) {
	$pParamHash['content_store']['data'] = $pParamHash['edit'];
}

function markdown_parse_data( &$pParseHash, &$pCommonObject ) {
	require_once( UTIL_PKG_INC.'markdown.php' );
	return Markdown( $pParseHash['data'] );
}
