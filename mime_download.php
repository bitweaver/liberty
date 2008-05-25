<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_liberty/Attic/mime_download.php,v 1.1 2008/05/10 21:50:36 squareing Exp $
 *
 * @author       xing  <xing@synapse.plus.com>
 * @package      treasury
 * @copyright    2003-2006 bitweaver
 * @license      LGPL {@link http://www.gnu.org/licenses/lgpl.html}
 **/

/**
 * Setup
 */ 
require_once( '../bit_setup_inc.php' );
require_once( LIBERTY_PKG_PATH.'LibertyMime.php' );

// fetch the attachment details
$attachment = LibertyMime::getAttachment( $_REQUEST['attachment_id'] );
$gBitSmarty->assign( 'attachment', $attachment );

// first we need to check the permissions of the content the attachment belongs to since they inherit them
$gContent = LibertyBase::getLibertyObject( $attachment['content_id'] );
$gContent->verifyViewPermission();
$gBitSmarty->assign( 'gContent', $gContent );

if( $download_function = LibertyMime::getPluginFunction( $attachment['attachment_plugin_guid'], 'download_function' )) {
	if( $download_function( $attachment )) {
		die;
	} else {
		if( !empty( $gContent->mInfo['errors'] )) {
			$msg = '';
			foreach( $gContent->mInfo['errors'] as $error ) {
				$msg .= $error.'<br />';
			}
			$gBitSystem->fatalError( tra( $msg ));
		} else {
			$gBitSystem->fatalError( tra( 'There was an undetermined problem trying to prepare the file for download.' ));
		}
	}
} else {
	$gBitSystem->fatalError( tra( "No suitable download function found." ));
}
?>