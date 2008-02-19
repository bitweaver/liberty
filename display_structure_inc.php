<?php
/**
 * display_structure_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.5 $
 * @package  liberty
 * @subpackage functions
 */

/**
 * required setup
 */
	global $gContent;
	include_once( LIBERTY_PKG_PATH.'lookup_content_inc.php' );
	if( is_object( $gContent ) && $gContent->isValid() ) {
		$gBitSystem->setBrowserTitle( $gStructure->getRootTitle().' : '.$gContent->getTitle() );
		include $gContent->getRenderFile();
	} else {
		$gBitSystem->setHttpStatus( 404 );
		$gBitSystem->fatalError( tra( 'Page cannot be found' ));
	}
?>
