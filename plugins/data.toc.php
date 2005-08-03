<?php
/**
 * @version  $Revision: 1.1.1.1.2.10 $
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
// $Id: data.toc.php,v 1.1.1.1.2.10 2005/08/03 07:43:55 lsces Exp $

/**
 * definitions
 */
global $gLibertySystem;

define( 'PLUGIN_GUID_TOC', 'datatoc' );

global $gLibertySystem;
$pluginParams = array ( 'tag' => 'toc',
						'auto_activate' => TRUE,
						'requires_pair' => FALSE,
						'load_function' => 'data_toc',
						'title' => 'Table Of Contents (TOC)',
						'help_page' => 'DataPluginTOC',
						'description' => tra("Display a Table Of Contents for Structures"),
						'help_function' => 'data_toc_help',
						'syntax' => '{TOC sturcture_id= }',
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_TOC, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_TOC );

function data_toc_help() {
	return 'NO HELP WRITTEN FOR {TOC}';
}

function data_toc( $data, $params ) {
	$repl = '';
	include_once( LIBERTY_PKG_PATH.'LibertyStructure.php' );
	global $gStructure, $gContent;
	$struct = NULL;
	if( is_object( $gContent ) && (empty( $gStructure ) || !$gStructure->isValid()) ) {
		$structures = $gContent->getStructures();
		// We take the first structure. not good, but works for now - spiderr
		if( !empty( $structures[0] ) ) {
			$struct = new LibertyStructure( $structures[0]['structure_id'] );
		}
	} else {
		$struct = &$gStructure;
	}
	if( is_object( $struct ) && count( $struct->isValid() ) ) {
		// maybe there is not toc to render?
		if( !$repl = $struct->get_toc( $struct->mStructureId ) ) {
			// return blank, *not* empty, so the {toc} tag gets replaced
			$repl = ' ';
		}
	}

	return $repl;
}
?>
