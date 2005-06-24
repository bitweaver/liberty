<?php
// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See copyright.txt for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Author (TikiWiki): Ricardo Gladwell <axonrg@users.sourceforge.net>
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up) 
// | by: StarRider <starrrider@users.sourceforge.net>
// | Reworked from: wikiplugin_copyright.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.copyright.php,v 1.1.1.1.2.1 2005/06/24 07:50:59 starrrider Exp $
// Initialization
global $gBitSystem;
if( ( $gBitSystem->isPackageActive( 'wiki' ) ) && ( $gBitSystem->isFeatureActive( 'wiki_feature_copyrights' ) ) ) { // Do not include this Plugin if this Package and Feature are not active

define( 'PLUGIN_GUID_DATACOPYRIGHT', 'datacopyright' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'COPYRIGHT',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_copyright',
						'title' => 'CopyRight',
						'description' => tra("This plugin is used to insert CopyRight notices."),
						'help_function' => 'data_copyright_help',
						'tp_helppage' => "http://www.bitweaver.org/wiki/index.php", // Update this URL when a page on TP.O exists
						'syntax' => "{COPYRIGHT title= year= authors= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATACOPYRIGHT, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATACOPYRIGHT );

// Help Function
function data_copyright_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>title</td>'
				.'<td>' . tra( "string") . '<br />' . tra( "(optional)") . '</td>'
				.'<td>' . tra( "The Title of the Publication. There is no default.") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>year</td>'
				.'<td>' . tra( "string") . '<br />' . tra( "(optional)") . '</td>'
				.'<td>' . tra( "The Year of the Publication. There is no default.") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>authors</td>'
				.'<td>' . tra( "string") . '<br />' . tra( "(optional)") . '</td>'
				.'<td>' . tra( "The Authors of the Publication. There is no default.") . '</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{COPYRIGHT title='The Tiki Way' year='May 10, 2004' authors='StarRider' }";
	return $help;
}

// Load Function
function data_copyright($data, $params) { // Pre-Clyde Changes
// This plugin did not use any parameters - The keywords ~Title~ ~year~ and ~authors~ were inbeded in $data like this
// {COPYRIGHT()}~title~The Tiki Way~year~May 10, 2004~authors~StarRider{COPYRIGHT}
// Changed this to use Parameters instead
// Added testing to maintain Pre-Clyde compatability
// The next 2 lines allow access to the $pluginParams given above and may be removed when no longer needed
	global $gLibertySystem; 
	$pluginParams = $gLibertySystem->mPlugins[PLUGIN_GUID_DATACOPYRIGHT];
	extract ($params);
	// This maintains Pre-Clyde Parameters
	if ( !empty( $data) ) { // The problem with this is that $authors HAS to be the last key-word
		$pos1 = strpos( strtolower($data), '~title~') + 7;
		$pos2 = strpos( strtolower($data), '~', $pos1 + 1 );
		$title = substr( $data, $pos1, $pos2-$pos1);
		$pos1 = strpos( strtolower($data), '~year~') + 6;
		$pos2 = strpos( strtolower($data), '~', $pos1 + 1 );
		$year = substr( $data, $pos1, $pos2-$pos1);
		$pos1 = strpos( strtolower($data), '~authors~') + 9;
		$pos2 = strlen($data) - $pos1;
		$authors = substr( $data, $pos1, $pos2);
	} else {
		$title = isset( $title) ? $title : ' ';
		$year = isset( $year) ? $year : ' ';
		$authors = isset( $authors) ? $authors : ' ';
	}
	$ret = 'The plugin <strong>"' . $pluginParams['tag'] . '"</strong> has not been completed as yet. ';
	return $ret;
}
}
/******************************************************************************
The code below is from the deprecated COPYRIGHT plugin. All comments and the help routines have been removed. - StarRider

require_once( KERNEL_PKG_PATH.'BitBase.php' );
require_once( WIKI_PKG_PATH.'copyrights_lib.php' );


function wikiplugin_copyright($data, $params) {
	$copyrightslib = new CopyrightsLib();
	if (!isset($_REQUEST['copyrightpage'])) {
		return '';
	}
	//extract($params);
	$result = '';
	$copyrights = $copyrightslib->list_copyrights($_REQUEST['copyrightpage']);
	for ($i = 0; $i < $copyrights['cant']; $i++) {
		$notice = str_replace("~title~", $copyrights['data'][$i]['title'], $data);
		$notice = str_replace("~year~", $copyrights['data'][$i]['year'], $notice);
		$notice = str_replace("~authors~", $copyrights['data'][$i]['authors'], $notice);
		$result = $result . $notice;
	}
	global $bit_p_edit_copyrights;
	if ((isset($bit_p_edit_copyrights)) && ($gBitUser->hasPermission( 'bit_p_edit_copyrights' ))) {
		$result = $result . "\n<a href=\"copyrights.php?page=" . $_REQUEST['copyrightpage'] . "\">Edit copyrights</a> for ((" . $_REQUEST['copyrightpage'] . "))\n";
	}
	return $result;
}
*/
?>
