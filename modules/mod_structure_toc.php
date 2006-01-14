<?php
// $Header: /cvsroot/bitweaver/_bit_liberty/modules/mod_structure_toc.php,v 1.3 2006/01/14 19:54:56 squareing Exp $
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
	$gBitSmarty->assign( 'modStructureTOC', $struct->get_toc( $struct->mInfo['root_structure_id'] ) );
}
?>
