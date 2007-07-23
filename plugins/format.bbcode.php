<?php
/**
 * @version  $Revision: 1.14 $
 * @package  liberty
 * @subpackage plugins_format
 */
global $gLibertySystem;

/**
 * run 'pear install Text_Wiki_BBCode-alpha' to install the library,
 */ 
if( @include_once( 'doc/Text_Wiki_BBCode/doc/BBCodeParser.php' ) ) {

/**
 * definitions
 */
define( 'PLUGIN_GUID_BBCODE', 'bbcode' );

$pluginParams = array (
	'load_function'   => 'bbcode_parse_data',
	'verify_function' => 'bbcode_verify_data',
	'description'     => 'BBCode Syntax Format Parser',
	'edit_label'      => 'BBCode',
	'edit_field'      => PLUGIN_GUID_BBCODE,
	'help_page'       => 'BBCodeSyntax',
	'plugin_type'     => FORMAT_PLUGIN,
	'linebreak'       => "\r\n"
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_BBCODE, $pluginParams );

function bbcode_verify_data( &$pParamHash ) {
	$pParamHash['content_store']['data'] = $pParamHash['edit'];
}

function bbcode_parse_data( &$pParseHash, &$pCommonObject ) {
	global $gLibertySystem;
	$data = $pParseHash['data'];
	$data = preg_replace( '/\[(quote|code):[0-9a-f]+=/', '[\1=', $data );
	$data = preg_replace( '/:[0-9a-f]+\]/', ']', $data );

	/* get options from the ini file 
	// $config = parse_ini_file('BBCodeParser.ini', true);
	$config = parse_ini_file('doc/Text_Wiki_BBCode/doc/BBCodeParser_V2.ini', true);
	$options = &PEAR::getStaticProperty('HTML_BBCodeParser', '_options');
	$options = $config['HTML_BBCodeParser'];
	unset($options);
	 */

	$parser = new HTML_BBCodeParser('BBCodeParser_V2.ini');
	$parser->setText( $data );
	$parser->parse();
	$ret = $parser->getParsed();

	return $ret;
}

} // PEAR check

?>
