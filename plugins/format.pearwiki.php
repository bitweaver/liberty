<?php
/**
 * @version  $Revision: 1.1.1.1.2.7 $
 * @package  liberty
 * @subpackage plugins_format
 */

/**
 * definitions
 */
global $gLibertySystem;

define( 'PLUGIN_GUID_PEARWIKI', 'pearwiki' );

$auto_activate = ( @include_once( 'Text/Wiki.php' ) ? TRUE : FALSE );

$pluginParams = array ( 'store_function' => 'pearwiki_save_data',
						'load_function' => 'pearwiki_parse_data',
						'verify_function' => 'pearwiki_verify_data',
						'auto_activate' => $auto_activate,
						'description' => 'Pear Wiki Syntax Format Parser. Requires Text_Wiki Pear extension. If you are running linux you can try running: su -c \'pear install Text_Wiki\'. More info <a href="http://wiki.ciaweb.net/yawiki/index.php?area=Text_Wiki&page=SamplePage">here</a>',
						'edit_label' => 'Pear Text_Wiki Syntax',
						'edit_field' => '<input type="radio" name="format_guid" value="'.PLUGIN_GUID_PEARWIKI.'"',
						'help_page' => 'PearWikiSyntax',
						'plugin_type' => FORMAT_PLUGIN
					  );

$gLibertySystem->registerPlugin( PLUGIN_GUID_PEARWIKI, $pluginParams );

function pearwiki_save_data( &$pParamHash ) {
	static $parser;
	require_once 'Text/Wiki.php';
	if( empty( $parser ) ) {
		$parser =& new Text_Wiki();
	}
}

function pearwiki_verify_data( &$pParamHash ) {
	$errorMsg = NULL;
	$pParamHash['content_store']['data'] = $pParamHash['edit'];
	return( $errorMsg );
}

function pearwiki_parse_data( &$pData, &$pCommonObject ) {
	static $parser;
	require_once 'Text/Wiki.php';
	if( empty( $parser ) ) {
		$parser =& new Text_Wiki();
	}
	$xhtml = $parser->transform($pData, 'Xhtml');

	global $gLibertySystem;
	// create a table of contents for this page
	// this function is called manually, since it processes the HTML code
	if( preg_match( "/\{maketoc.*?\}/i", $xhtml ) && @$gLibertySystem->mPlugins['datamaketoc']['is_active'] == 'y' ) {
		$xhtml= data_maketoc($xhtml);
	}
	return $xhtml;
}
?>
