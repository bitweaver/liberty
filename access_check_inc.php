<?php

if( !$gContent->hasUserAccess( $accessPermission ) ) {
	if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
		if ( !empty($_REQUEST['submit_answer'])) {	// User is attempting to authenticate themseleves to view this gallery
			if( !$gContent->validateUserAccess( $_REQUEST['try_access_answer']) ) {
				$gBitSmarty->assign("failedLogin", "Incorrect Answer");
				$gBitSystem->display("bitpackage:gatekeeper/authenticate.tpl", "Password Required to view: ".$gContent->getTitle() );
				die;
			}
		} else {
			if( !empty( $gContent->mInfo['access_answer'] ) ) {
				$gBitSystem->display("bitpackage:gatekeeper/authenticate.tpl", "Password Required to view: ".$gContent->getTitle() );
				die;
			}
			$gBitSystem->fatalError( tra( "You cannot view this image gallery" ) );
		}
	}
}

?>
