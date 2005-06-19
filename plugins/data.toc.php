<?php
global $gLibertySystem;

define( 'PLUGIN_GUID_TOC', 'datatoc' );

global $gLibertySystem;
$pluginParams = array ( 'tag' => 'toc',
						'auto_activate' => TRUE,
						'requires_pair' => FALSE,
						'load_function' => 'toc_parse_data',
						'title' => 'Table Of Contents',
						'description' => tra("Display a Table Of Contents for Structures"),
						'help_function' => 'toc_extended_help',
						'tp_helppage' => "http://www.bitweaver.org/wiki/index.php", // Update this URL when a page on TP.O exists
						'syntax' => '{TOC sturcture_id= }',
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_TOC, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_TOC );

function toc_extended_help() {
	return 'NO HELP WRITTEN FOR {toc}';
}

function toc_parse_data( $data, $params ) {
	$repl = '';
	include_once( LIBERTY_PKG_PATH.'LibertyStructure.php' );
	global $gStructure, $gContent;
	$struct = NULL;
	if( empty( $gStructure ) || !$gStructure->isValid() ) {
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
