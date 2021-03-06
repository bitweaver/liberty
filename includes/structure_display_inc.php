<?php
/**
 * structure_display_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision$
 * @package  liberty
 * @subpackage functions
 */

/**
 * required setup
 */
global $gContent;
include_once( LIBERTY_PKG_INCLUDE_PATH.'lookup_content_inc.php' );
if( is_object( $gContent ) && $gContent->isValid() ) {
	$gBitSystem->setBrowserTitle( $gStructure->getRootTitle().' : '.$gContent->getTitle() );
	$gBitSystem->setCanonicalLink( $gContent->getDisplayUrl() );
	include $gContent->getRenderFile();
} else {
	$gBitSystem->fatalError( tra( 'Page cannot be found' ), NULL, NULL, HttpStatusCodes::HTTP_GONE );
}
