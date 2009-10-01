<?php
/**
 * @version  $Revision: 1.19 $
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
// | Author: Christian Fowler <spiderr@users.sourceforge.net>
// +----------------------------------------------------------------------+
// $Id: data.toc.php,v 1.19 2009/10/01 14:17:01 wjames5 Exp $

/**
 * definitions
 */
global $gLibertySystem;

define( 'PLUGIN_GUID_DATATOC', 'datatoc' );

global $gLibertySystem;
$pluginParams = array (
	'tag'           => 'toc',
	'auto_activate' => TRUE,
	'requires_pair' => FALSE,
	'load_function' => 'data_toc',
	'title'         => 'Structure Table Of Contents',
	'help_page'     => 'DataPluginTOC',
	'description'   => tra("Display a Table Of Contents for Structures"),
	'help_function' => 'data_toc_help',
	'syntax'        => '{toc structure_id= }',
	'plugin_type'   => DATA_PLUGIN,
	'biticon'       => '{biticon ilocation=quicktag ipackage=quicktags iname=toc iexplain="Structure Table of Contents"}',
	'taginsert'     => '{toc}',
	'structure_id'  => 'id of the structure to display'
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATATOC, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATATOC );

/**
 * Help Function
 */
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
				.'<td>' . tra( "Will display a Table Of Contents for Structures, such as Wiki-books. Works only if the page where the tag is used is a part of some structure. If the page belongs to several wiki-books, use structure_id attribute.") . '</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . '{toc structure_id=8 display=full_toc}';
}

/**
 * Load Function
 */
function data_toc( $pData, $pParams ) {
	include_once( LIBERTY_PKG_PATH.'LibertyStructure.php' );
	global $gStructure, $gContent, $gBitSmarty;

	if( is_object( $gStructure ) && $gStructure->isValid() ) {
		$struct = &$gStructure;
	} elseif( @BitBase::verifyId( $pParams['structure_id'] ) ) {
			$struct = new LibertyStructure( $pParams['structure_id'] );
			$struct->load();
	} elseif( is_object( $gContent ) ) {
		$structures = $gContent->getStructures();
		// We take the first structure. not good, but works for now - spiderr
		if( !empty( $structures[0] ) ) {
			require_once( LIBERTY_PKG_PATH.'LibertyStructure.php' );
			$struct = new LibertyStructure( $structures[0]['structure_id'] );
			$struct->load();
		}
	}

	$repl = ' ';
	if( !empty( $struct) && is_object( $struct ) && $struct->isValid()) {
		if( @BitBase::verifyId( $structure_id ) ) {
			$get_structure = $structure_id;
		} else {
			$get_structure = $struct->mStructureId;
		}

		$tree = $struct->getSubTree( $get_structure, ( !empty( $pParams['display'] ) && $pParams['display'] == 'full_toc' ));
		$gBitSmarty->assign( "subtree", $tree );
		$repl = $gBitSmarty->fetch( "bitpackage:liberty/plugins/data_toc.tpl" );
	}

	return $repl;
}
?>
