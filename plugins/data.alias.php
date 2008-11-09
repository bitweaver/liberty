<?php
/**
 * assigned_modules
 *
 * @author     xing
 * @version    $Revision: 1.2 $
 * @package    liberty
 * @subpackage plugins_data
 * @copyright  Copyright (c) 2004, bitweaver.org
 */

/**
 * Setup Code
 */
define( 'PLUGIN_GUID_DATAalias', 'dataalias' );
global $gLibertySystem;
$pluginParams = array (
	'tag' => 'alias',
	'auto_activate' => FALSE,
	'requires_pair' => FALSE,
	'load_function' => 'data_alias',
	'title' => 'Alias',
	'help_page' => 'DataPluginAlias',
	'description' => tra( "This plugin allows you to easily create an alias for a page." ),
	'help_function' => 'data_alias_help',
	'syntax' => "{alias page='title'}",
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAalias, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAalias );

function data_alias_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>' . tra( "page" ) . '</td>'
				.'<td>' . tra( "string") . '<br />' . tra( "(required)" ) . '</td>'
				.'<td>' . tra( "The name of any other wiki page." ) .'</td>'
			.'</tr>'
		.'</table>'
		. tra( "Example: " ) . "{alias page='Welcome'}";
	return $help;
}

function data_alias( $pData, $pParams, $pCommonObject ) {
	$page = '';
	require_once(WIKI_PKG_PATH."BitPage.php");

	foreach( $pParams as $key => $value ) {
		if( !empty( $value ) ) {
			switch( $key ) {
				case 'page':
					$page = $value;
					break;
			default:
				break;
			}
		}
	}
	return tra("This page is an alias for:").'&nbsp;'.BitPage::getDisplayLink($page, LibertyContent::pageExists($page));
}
?>
