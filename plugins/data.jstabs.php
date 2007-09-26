<?php
/**
 * @version  $Revision: 1.2 $
 * @package  liberty
 * @subpackage plugins_storage
 */

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATAJSTABS', 'datajstabs' );
global $gLibertySystem;
$pluginParams = array (
	'tag'           => 'jstabs',
	'title'         => 'Javascript Tabs',
	'description'   => tra( "Allow tabbing of content using a simple syntax." ),
	//'help_page'     => 'DataPluginJstabs',

	'auto_activate' => FALSE,
	'requires_pair' => TRUE,
	'syntax'        => '{jstabs}',
	'path'          => LIBERTY_PKG_PATH.'plugins/data.jstabs.php',
	'plugin_type'   => DATA_PLUGIN,

	// display icon in quicktags bar
	'biticon'       => '{biticon ilocation=quicktag iname=application-x-executable iexplain="Javascript Tabs"}',
	'taginsert'     => '{jstabs}text{/jstabs}',

	// functions
	'help_function' => 'data_jstabs_help',
	'load_function' => 'data_jstabs',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAJSTABS, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAJSTABS );


function data_jstabs( $pData, $pParams, $pCommonObject ) {
	global $gBitSmarty;
	require_once $gBitSmarty->_get_plugin_filepath( 'block', 'jstab' );
	require_once $gBitSmarty->_get_plugin_filepath( 'block', 'jstabs' );

	// collect all tabs
	$tabs = preg_split( "!---tab:\s*!i", $pData );
	$html = '';

	foreach( $tabs as $tab ) {
		$tab = trim( $tab );
		if( !empty( $tab )) {
			// first line of every tab is the title
			preg_match( "!(.*?)\n(.*)!s", $tab, $split );

			// we need a valid title and content to work with
			if( !empty( $split[1] ) && !empty( $split[2] )) {
				// prepare data for tabification and parsing
				$params['title'] = trim( $split[1] );

				$parseHash = $pCommonObject->mInfo;
				$parseHash['no_cache'] = TRUE;
				$parseHash['data'] = $split[2];

				$html .= smarty_block_jstab( $params, $pCommonObject->parseData( $parseHash ), $gBitSmarty );
			}
		}
	}

	if( !empty( $html )) {
		return smarty_block_jstabs( array(), $html, $gBitSmarty );
	} else {
		return ' ';
	}
}

function data_jstabs_help() {
	return
		'<p class="data help">'.tra( "This plugin does not take any arguments but you need to use a particular syntax to add tabs. You need to insert something like: <strong>---tab: Title of the tab</strong> on a separate line. This will start a new tab with the title: <em>Title of the tab</em>." ).'</p>'
		. tra( "Example: ") . "<br />{jstabs}<br />---tab:First Tab<br />Some content<br />---tab:Second Tab<br />Some content in the second tab.<br />{/jstabs}";
}
?>
