<?php
/**
 * @version  $Revision: 1.15 $
 * @package  liberty
 * @subpackage plugins_format
 */
global $gLibertySystem;

/**
 * definitions
 */
define( 'PLUGIN_GUID_BITHTML', 'bithtml' );

$pluginParams = array (
	'store_function' => 'bithtml_save_data',
	'load_function' => 'bithtml_parse_data',
	'verify_function' => 'bithtml_verify_data',
	'description' => 'HTML Syntax Format Parser',
	'edit_label' => 'HTML',
	'edit_field' => PLUGIN_GUID_BITHTML,
	'help_page' => 'HTMLSyntax',
	'plugin_type' => FORMAT_PLUGIN,
	'linebreak' => '<br />'
);

$gLibertySystem->registerPlugin( PLUGIN_GUID_BITHTML, $pluginParams );

function bithtml_verify_data( &$pParamHash ) {
    	global $gLibertySystem;
	$errorMsg = NULL;
	$pParamHash['content_store']['data'] = $gLibertySystem->purifyHtml( $pParamHash['edit'] );
	return $errorMsg;
}

function bithtml_save_data( &$pParamHash ) {
	static $parser;
	if( empty( $parser ) ) {
		require_once( LIBERTY_PKG_PATH.'plugins/format.tikiwiki.php' );
		$parser = new TikiWikiParser();
	}
	if( $pParamHash['edit'] ) {
		$parser->storeLinks( $pParamHash );
	}
}

function bithtml_parse_data( &$pParseHash, &$pCommonObject ) {
	global $gLibertySystem;
	$ret = $pParseHash['data'];
	// eventually we should strip tags, maybe tikilink, or other things.
	parse_data_plugins( $ret, $foo, $bar, $empty, $pCommonObject );
	if( preg_match( "/\(\(([^\)][^\)]+)\)\)/", $ret ) ) {
		preg_match_all( "/\(\(([^\)][^\)]+)\)\)/", $ret, $pages );
		foreach (array_unique($pages[1])as $page_parse) {
			// This is a hack for now. page_exists_desc should not be needed here since blogs and articles use this function
			$exists = $pCommonObject->pageExists( $page_parse, $pCommonObject->mContentId, $pCommonObject );
			$repl = $pCommonObject->getDisplayLink( $page_parse, $exists );
			$page_parse_pq = preg_quote($page_parse, "/");
			$ret = preg_replace("/\(\($page_parse_pq\)\)/", "$repl", $ret);
		}
	}
	return $ret;
}

?>
