<?php
/**
 * @version  $Revision$
 * @package  liberty
 * @subpackage plugins_data
 */
// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See below for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Author (TikiWiki): Marc Laporte <marclaporte@users.sourceforge.net>
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up)
// | by: StarRider <starrrider@users.sourceforge.net>
// +----------------------------------------------------------------------+
// $Id$

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATAINCLUDE', 'datainclude' );
global $gLibertySystem;
$pluginParams = array (
	'tag' => 'INCLUDE',
	'auto_activate' => TRUE,
	'requires_pair' => FALSE,
	'load_function' => 'data_include',
	'title' => 'Include',
	'help_page' => 'DataPluginInclude',
	'description' => tra("This plugin is used to include the contents of one Wiki page in another Wiki page."),
	'help_function' => 'data_include_help',
	'syntax' => "{INCLUDE content_id= }",
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAINCLUDE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAINCLUDE );

// Help Function
function data_include_help() {
	$help = '
		<table class="plugin help">
			<tr>
				<th>'.tra( 'key' ).'</th>
				<th>'.tra( 'type' ).'</th>
				<th>'.tra( 'comments' ).'</th>
			</tr>
			<tr class="odd">
				<td>page_name</td>
				<td>'.tra( 'string (optional)' ).'</td>
				<td>'.tra( 'To include any wiki page you can use it\'s page name (this has to be a unique name. if it\'s not unique, use the page_id instead) (this method is deprecated).' ).'</td>
			</tr>
			<tr class="even">
				<td>page_id</td>
				<td>'.tra( 'numeric (optional)' ).'</td>
				<td>'.tra( 'To include any wiki page you can use it\'s page_id number.' ).'</td>
			</tr>
			<tr class="odd">
				<td>content_id</td>
				<td>'.tra( 'numeric (optional)' ).'</td>
				<td>'.tra( 'To include any content from bitweaver insert the appropriate numeric content id. This can include blog posts, images, wiki texts...<br />
					Avaliable content can be viewed <a href="'.LIBERTY_PKG_URL.'list_content.php">here</a>' ).'</td>
			</tr>
		</table>
		Example: {INCLUDE page_name=Welcome}
		Example: {INCLUDE page_id=15}
		Example: {INCLUDE content_id=15}';
	return $help;
}

function data_include($data, $params) {
	$ret = "<p>Please enter a valid 'page_name', 'page_id' or 'content_id' to include in this page.</p>";
	// load page by page_id
	if( isset( $params['page_id'] ) && is_numeric( $params['page_id'] ) ) {
		require_once( WIKI_PKG_PATH.'BitPage.php');
		$wp = new BitPage( $params['page_id'] );
		if( $wp->load() ) {
			$ret = $wp->parseData( $wp->mInfo );
		}
	// load page by content_id
	} elseif( isset( $params['content_id'] ) && is_numeric( $params['content_id'] ) ) {
		if( $obj = LibertyBase::getLibertyObject( $params['content_id'] ) ) {
			$ret = $obj->parseData();
		}
	// load page by page_name
	} elseif( isset( $params['page_name'] ) ) {
		$ret = "page_name isn't working yet, please use page_id or content_id";
	}

	// if $ret is empty, we need to make sure there is at least a space that we get rid of the {}
	if( empty( $ret )) {
		$ret = ' ';
	}

	return $ret;
}
?>
