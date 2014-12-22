<?php
/**
 * @version $Header$
 * 
 * @package liberty
 * @subpackage modules
 */
 
/**
 * Initial Setup
 */
global $gStructure, $gContent, $moduleParams, $gBitSmarty;
require_once( LIBERTY_PKG_PATH.'LibertyStructure.php' );

extract( $moduleParams );

$struct = NULL;

if( is_object( $gStructure ) && $gStructure->isValid() && $gStructure->hasViewPermission() ) {
	$struct = &$gStructure;
} elseif( @BitBase::verifyId( $module_params['structure_id'] ) ) {
		$struct = new LibertyStructure( $module_params['structure_id'] );
		$struct->load();
} elseif( is_object( $gContent ) && $gContent->hasViewPermission( FALSE ) ) {
	$structures = $gContent->getStructures();
	// We take the first structure. not good, but works for now - spiderr
	if( !empty( $structures[0] ) ) {
		require_once( LIBERTY_PKG_PATH.'LibertyStructure.php' );
		$struct = new LibertyStructure( $structures[0]['structure_id'] );
		$struct->load();
	}
}

if( is_object( $struct ) && count( $struct->isValid() ) ) {
	$_template->tpl_vars['moduleTitle'] = new Smarty_variable( $moduleParams['title'] );
	$toc = $struct->getToc( $struct->mInfo['root_structure_id'], 'asc', FALSE, 2 );
	$_template->tpl_vars['modStructureTOC'] = new Smarty_variable( $struct->getToc( $struct->mInfo['root_structure_id'], 'asc', FALSE, 2 ) );
}
