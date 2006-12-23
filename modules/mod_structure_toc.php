<?php
// $Header: /cvsroot/bitweaver/_bit_liberty/modules/mod_structure_toc.php,v 1.4 2006/12/23 18:55:44 squareing Exp $
/**
 * Params:
 * @package liberty
 * @subpackage modules
 */
global $gStructure, $gContent;
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
if( is_object( $struct ) && count( $struct->isValid() ) ) {
	$gBitSmarty->assign( 'modStructureTOC', $struct->getToc( $struct->mInfo['root_structure_id'] ) );
}
?>
