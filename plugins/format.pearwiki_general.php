<?php
/**
 * @version  $Revision: 1.9 $
 * @package  liberty
 * @subpackage plugins_format
 */

/**
 * definitions
 */
global $gLibertySystem;
if( @include_once( 'PEAR/Registry.php' )) {
	if( @include_once( 'Text/Wiki.php' )) {

$genPluginParams = array (
	'store_function' => 'pearwiki_general_save_data',
	'verify_function' => 'pearwiki_general_verify_data',
	'plugin_type' => FORMAT_PLUGIN,
	'linebreak' => "\r\n"
);

$reg = new PEAR_Registry();

foreach( $reg->listPackages() as $package ) {
	if( preg_match( '!^text_wiki!', $package )) {
		// get package information
		$inf = $reg->packageInfo( $package );

		// package information is all over the place. this should clean it up a bit
		if( !empty( $inf['name'] )) {
			$package = $inf['name'];
		} elseif( !empty( $inf['name'] )) {
			$package = $inf['package'];
		} else {
			continue;
		}

		// fetch parser name
		$p = substr( $package, strlen( "text_wiki" ));
		if( empty( $p )) {
			$parser = "Text_Wiki";
			$parser_class = "Default";
		} else {
			$parser = substr( $p,1 );
			$parser_class = $parser;
		}

		$f = create_function('&$pParseHash, &$pCommonObject','return pearwiki_general_parse_data("'.$parser_class.'",$pParseHash, $pCommonObject);');
		$guid = "pearwiki_$parser";
		if (strlen($guid)>16) {
			$guid = "pw_$parser";
		}
		if (strlen($guid)>16) {
			$guid = substr($guid,0,16);
		}
		$insPluginParams =  array(
			'load_function' => $f,
			'edit_field' => "<input type=\"radio\" name=\"format_guid\" value=\"$guid\"",
			'description' => "Pear Wiki Parser for $parser Syntax.",
			'edit_label' => "$parser Syntax, parsed by Pear::Text_Wiki$p",
			'help_page' => "{$parser}Syntax",
			'auto_activate' => true,
		);
		$gLibertySystem->registerPlugin( $guid, array_merge($genPluginParams,$insPluginParams) );

	}
}

	}
}

function pearwiki_general_save_data( &$pParamHash ) {
}

function pearwiki_general_verify_data( &$pParamHash ) {
	$pParamHash['content_store']['data'] = $pParamHash['edit'];
	return( NULL );
}

function pearwiki_general_parse_data( $parser_fmt, &$pParseHash, &$pCommonObject ) {
    global $gBitSystem;

	if (!defined('PAGE_SEP')) {
		define('PAGE_SEP', 'PAGE MARKER HERE*&^%$#^$%*PAGEMARKERHERE');
	}
	$parser = Text_Wiki::singleton($parser_fmt);
	if (PEAR::isError($parser)) {
        $gBitSystem->fatalError("PEAR Wiki Parser Error", "There was an unknown error while constructing the parser.");
		die();
	}
	global $gBitSystem;
	if ($gBitSystem->isPackageActive('wiki')) {
		$parser->setRenderConf('xhtml', 'wikilink', 'exists_callback', array( &$pCommonObject, 'pageExists' ) );
		$parser->setRenderConf('xhtml', 'wikilink', 'view_url', WIKI_PKG_URL.'index.php?page=');
		$parser->setRenderConf('xhtml', 'wikilink', 'new_url', WIKI_PKG_URL.'edit.php?page=');
	}
	$parser->setRenderConf('xhtml', 'table', 'css_table', 'wikitable');
	$xhtml = $parser->transform( $pParseHash['data'], 'Xhtml' );

	return $xhtml;
}
?>
