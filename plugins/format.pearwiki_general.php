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

if( @include_once('PEAR/Registry.php') ) {
	$genPluginParams = array (
		'store_function' => 'pearwiki_general_save_data',
		'verify_function' => 'pearwiki_general_verify_data',
		'plugin_type' => FORMAT_PLUGIN
	);

	$reg = new PEAR_Registry;
	foreach ($reg->listPackages() as $package) {
		if (substr($package,0,strlen("text_wiki"))=="text_wiki") {
			$inf = $reg->packageInfo($package);
			$package = $inf['package'];
			$p = substr($package,strlen("text_wiki"));
			if (empty($p)) {
				$parser = "Text_Wiki";
				$parser_class = "Default";
			} else {
				$parser = substr($p,1);
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

function pearwiki_general_save_data( &$pParamHash ) {
}

function pearwiki_general_verify_data( &$pParamHash ) {
	$pParamHash['content_store']['data'] = $pParamHash['edit'];
	return( NULL );
}

function pearwiki_general_parse_data( $parser_fmt, &$pParseHash, &$pCommonObject ) {
	if (!defined('PAGE_SEP')) {
		define('PAGE_SEP', 'PAGE MARKER HERE*&^%$#^$%*PAGEMARKERHERE');
	}

	require_once 'Text/Wiki.php';
	$parser = Text_Wiki::singleton($parser_fmt);
	if (PEAR::isError($parser)) {
		vd($parser);
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
