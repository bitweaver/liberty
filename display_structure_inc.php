<?php
	global $gContent;
	include_once( LIBERTY_PKG_PATH.'lookup_content_inc.php' );
	if( is_object( $gContent ) && $gContent->isValid() ) {
		$gBitSystem->setBrowserTitle( $gStructure->getRootTitle().' : '.$gContent->getTitle() );
		include $gContent->getRenderFile();
	} else {
		$gBitSystem->fatalError( 'Page cannot be found' );
	}
?>
