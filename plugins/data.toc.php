<?php
/**
 * @version  $Revision: 1.6 $
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
// | Author: Christian Fowler <spiderr@users.sourceforge.net>
// +----------------------------------------------------------------------+
// $Id: data.toc.php,v 1.6 2006/04/06 05:06:11 starrrider Exp $

/**
 * definitions
 */
global $gLibertySystem;

define( 'PLUGIN_GUID_TOC', 'datatoc' );

global $gLibertySystem;
$pluginParams = array (
	'tag' => 'toc',
	'auto_activate' => TRUE,
	'requires_pair' => FALSE,
	'load_function' => 'data_toc',
	'title' => 'Table Of Contents (TOC)',
	'help_page' => 'DataPluginTOC',
	'description' => tra("Display a Table Of Contents for Structures"),
	'help_function' => 'data_toc_help',
	'syntax' => '{TOC sturcture_id= }',
	'path' => LIBERTY_PKG_PATH.'plugins/data.toc.php',
	'security' => 'registered',
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_TOC, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_TOC );

function data_toc_help() {
	return '<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>display</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Will create a Tab interface on a page. The name on each tab is the name given to the imported page.The value sent with the TabX parameter is a Numeric Content Id. This allows blog posts, images, wiki pages . . . (and more) to be added.")
			.'</tr>'
		.'</table>'
		. tra("Example: ") . '{toc structure_id=8 display=full_toc}';
}

function data_toc( $data, $params ) {
	include_once( LIBERTY_PKG_PATH.'LibertyStructure.php' );
	global $gStructure, $gContent, $gBitSmarty;
	extract( $params );
	$struct = NULL;
	if( is_object( $gContent ) && ( empty( $gStructure ) || !$gStructure->isValid() ) ) {
		$structures = $gContent->getStructures();
		// We take the first structure. not good, but works for now - spiderr
		if( !empty( $structures[0] ) ) {
			$struct = new LibertyStructure( $structures[0]['structure_id'] );
		}
	} else {
		$struct = &$gStructure;
	}

	$repl = '';
	if( is_object( $struct ) && count( $struct->isValid() ) ) {
		if( @BitBase::verifyId( $structure_id ) ) {
			$get_structure = $structure_id;
		} else {
			$get_structure = $struct->mStructureId;
		}
		$tree = $struct->getSubTree( $get_structure, ( @$display == 'full_toc' ) );
		$gBitSmarty->assign( "subtree", $tree );
		$repl = $gBitSmarty->fetch( "bitpackage:liberty/display_toc_inc.tpl" );
		if( empty( $repl ) ) {
			// return blank, *not* empty, so the {toc} tag gets replaced
			$repl = ' ';
		}
	}

	return $repl;
}
?>
