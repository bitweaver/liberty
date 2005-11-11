<?php
/**
 * @version  $Revision: 1.1.1.1.2.9 $
 * @package  liberty
 * @subpackage plugins_data
 */
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
// +----------------------------------------------------------------------+
// $Id: data.copyright.php,v 1.1.1.1.2.9 2005/11/11 22:04:09 mej Exp $

/**
 * definitions
 */
global $gBitSystem;
if( ( $gBitSystem->isPackageActive( 'wiki' ) ) && ( $gBitSystem->isFeatureActive( 'wiki_feature_copyrights' ) ) ) { // Do not include this Plugin if this Package and Feature are not active

define( 'PLUGIN_GUID_DATACOPYRIGHT', 'datacopyright' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'COPYRIGHT',
						'auto_activate' => TRUE,
						'requires_pair' => FALSE,
						'load_function' => 'data_copyright',
						'title' => 'CopyRight',
						'help_page' => 'DataPluginCopyRight',
						'description' => tra("This plugin is used to insert CopyRight notices."),
						'help_function' => 'data_copyright_help',
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
	extract ($params, EXTR_SKIP);
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
?>
