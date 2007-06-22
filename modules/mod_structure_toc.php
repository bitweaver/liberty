<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/modules/mod_structure_toc.php,v 1.6 2007/06/22 10:16:09 lsces Exp $
 * 
 * @package liberty
 * @subpackage modules
 */
 
/**
 * Initial Setup
 */
global $gStructure, $gContent;
$struct = NULL;
if( is_object( $gContent ) && ( empty( $gStructure ) || !$gStructure->isValid() ) ) {
	$structures = $gContent->getStructures();
	// We take the first structure. not good, but works for now - spiderr
	if( !empty( $structures[0] ) ) {
		require_once( LIBERTY_PKG_PATH.'LibertyStructure.php' );
		$struct = new LibertyStructure( $structures[0]['structure_id'] );
		$struct->load();
	}
} else {
	$struct = &$gStructure;
}
if( is_object( $struct ) && count( $struct->isValid() ) ) {
	$gBitSmarty->assign( 'modStructureTOC', $struct->getToc( $struct->mInfo['root_structure_id'] ) );
}
?>



