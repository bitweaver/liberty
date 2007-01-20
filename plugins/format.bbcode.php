<?php
/**
 * @version  $Revision: 1.3 $
 * @package  liberty
 * @subpackage plugins_format
 */
global $gLibertySystem;


// run 'pear install HTML_BBCodeParser' to install the library,
// you also need to enable the HTML plugin for now to due to dependency on the purge_html function

if( @include_once( 'HTML/BBCodeParser.php' ) ) {

/**
 * definitions
 */
define( 'PLUGIN_GUID_BBCODE', 'bbcode' );

$pluginParams = array (
	'store_function' => 'bbcode_save_data',
	'load_function' => 'bbcode_parse_data',
	'verify_function' => 'bbcode_verify_data',
	'description' => 'BBCode Syntax Format Parser',
	'edit_label' => 'BBCode',
	'edit_field' => PLUGIN_GUID_BBCODE,
	'help_page' => 'BBCodeSyntax',
	'plugin_type' => FORMAT_PLUGIN
);

$gLibertySystem->registerPlugin( PLUGIN_GUID_BBCODE, $pluginParams );

function bbcode_verify_data( &$pParamHash ) {
	$errorMsg = NULL;
	$pParamHash['content_store']['data'] = purge_html( $pParamHash['edit'] );
	return $errorMsg;
}

function bbcode_save_data( &$pParamHash ) {
	static $parser;
	if( empty( $parser ) ) {
		require_once( LIBERTY_PKG_PATH.'plugins/format.tikiwiki.php' );
		$parser = new TikiWikiParser();
	}
	if( $pParamHash['edit'] ) {
		$parser->storeLinks( $pParamHash );
	}
}

function bbcode_parse_data( &$pParseHash, &$pCommonObject ) {
	global $gLibertySystem;
	$ret = $pParseHash['data'];

	$parser = new HTML_BBCodeParser();
	$parser->setText( $ret );
	$parser->parse();
	$ret = $parser->getParsed();

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
	// this function is called manually, since it processes the HTML code
	if( preg_match( "/\{maketoc.*?\}/i", $ret ) && @$gLibertySystem->mPlugins['datamaketoc']['is_active'] == 'y' ) {
		$ret = data_maketoc( $ret );
	}
	return $ret;
}

}

?>