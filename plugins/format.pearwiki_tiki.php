<?php
/**
 * @version  $Revision: 1.1.1.1.2.9 $
 * @package  liberty
 * @subpackage plugins_format
 */


/**
 * definitions
 */
global $gLibertySystem;

define( 'PLUGIN_GUID_PEARWIKI_TIKI', 'pearwiki_tiki' );

$auto_activate = ( @include_once( 'Text/Wiki.php' ) ? TRUE : FALSE );

$pluginParams = array ( 'store_function' => 'pearwiki_tiki_save_data',
						'load_function' => 'pearwiki_tiki_parse_data',
						'verify_function' => 'pearwiki_tiki_verify_data',
						'auto_activate' => $auto_activate,
						'description' => 'Pear Wiki Parser for TikiWiki Syntax. Requires Text_Wiki Pear extension. More info <a href="http://wiki.ciaweb.net/yawiki/index.php?area=Text_Wiki&page=SamplePage">here</a>',
						'edit_label' => 'Tiki Wiki Syntax, parsed by Pear::Text_Wiki',
						'edit_field' => '<input type="radio" name="format_guid" value="'.PLUGIN_GUID_PEARWIKI_TIKI.'"',
						'help_page' => 'TikiWikiSyntax',
						'plugin_type' => FORMAT_PLUGIN
					  );

$gLibertySystem->registerPlugin( PLUGIN_GUID_PEARWIKI_TIKI, $pluginParams );

function pearwiki_tiki_save_data( &$pParamHash ) {
}

function pearwiki_tiki_verify_data( &$pParamHash ) {
	$errorMsg = NULL;
	$pParamHash['content_store']['data'] = $pParamHash['edit'];
	return( $errorMsg );
}

function pearwiki_tiki_parse_data( &$pData, &$pCommonObject ) {
	static $parser;
	if( empty( $parser ) ) {
		define('PAGE_SEP', 'PAGE MARKER HERE*&^%$#^$%*PAGEMARKERHERE');

		require_once(dirname(__FILE__).'/../../util/pear/Text/Wiki/Tiki.php');
		$parser =& new Text_Wiki_Tiki();
		$parser->setRenderConf('xhtml', 'wikilink', 'exists_callback', array(&$pCommonObject, 'pageExists'));
		$parser->setRenderConf('xhtml', 'wikilink', 'view_url', 'index.php?page=');
		$parser->setRenderConf('xhtml', 'wikilink', 'new_url', 'edit.php?page=');
		$parser->setRenderConf('xhtml', 'table', 'css_table', 'wikitable');
		$parser->setRenderConf('xhtml', 'table', 'css_td', 'wikicell');
		//$parser->setFormatConf('Xhtml', 'translate', false);
		/*$extwiki = array();
		$extwikiSth = $this->mDb->query('SELECT `extwiki`, `name` FROM `tiki_extwiki`');
		while ($rec = $extwikiSth->fetchRow()) {
			$extwiki[$rec['name']] = str_replace('$page', '%s', $rec['extwiki']);
		}
		$parser->setRenderConf('xhtml', 'interwiki', 'sites', $extwiki);*/
	}
	$xhtml = $parser->transform($pData, 'Xhtml');

	global $gLibertySystem;
	// create a table of contents for this page
	// this function is called manually, since it processes the HTML code
	/*if( preg_match( "/\{maketoc.*?\}/i", $xhtml ) && @$gLibertySystem->mPlugins['datamaketoc']['is_active'] == 'y' ) {
		$xhtml= data_maketoc($xhtml);
	}*/
	return $xhtml;
}
?>
