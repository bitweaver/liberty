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
require_once( LIBERTY_PKG_CLASS_PATH.'LibertyStructure.php' );

extract( $moduleParams );

$struct = NULL;

if( is_object( $gStructure ) && $gStructure->isValid() && $gStructure->hasViewPermission() ) {
	$struct = &$gStructure;
} elseif( @BitBase::verifyId( $module_params['structure_id'] ) ) {
	$struct = new LibertyStructure( $module_params['structure_id'] );
} elseif( is_object( $gContent ) && is_a( $gContent, 'LibertyBase' ) && $gContent->hasViewPermission( FALSE ) ) {
	if( $structures = $gContent->getStructures() ) {
		// We take the first structure by default, perhaps there is a better choice
		$structureId = $structures[0]['structure_id'];
		if( count( $structures ) > 1 ) {
			foreach( $structures as $structureHash ) {
				if( $gContent->getTitle() == $structureHash['root_title'] ) {
					$structureId = $structureHash['root_structure_id'];
					break;
				}
			}
		}
		if( !empty( $structures[0] ) ) {
			require_once( LIBERTY_PKG_CLASS_PATH.'LibertyStructure.php' );
			$struct = new LibertyStructure( $structureId );
		}
	}
}

if( is_object( $struct ) && $struct->isValid() ) {
	if( !empty( $moduleParams['title'] ) ) {
		$_template->tpl_vars['moduleTitle'] = new Smarty_variable( $moduleParams['title'] );
	}
	$toc = $struct->getToc( $struct->mInfo['root_structure_id'], 'asc', FALSE, 2 );
	$root = $struct->getRootObject( $struct->mInfo['root_structure_id'] );
	$_template->tpl_vars['rootTitle'] = new Smarty_variable( $root->getDisplayLink() );
	$_template->tpl_vars['modStructureTOC'] = new Smarty_variable( $struct->getToc( $struct->mInfo['root_structure_id'], 'asc', FALSE, 2 ) );
}
