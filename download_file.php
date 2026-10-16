<?php
/**
 * @version      $Header$
 *
 * @author       xing  <xing@synapse.plus.com>
 * @package      treasury
 * @copyright    2003-2006 bitweaver
 * @license      LGPL {@link http://www.gnu.org/licenses/lgpl.html}
 **/

/**
 * Setup
 */
require_once( '../kernel/includes/setup_inc.php' );
require_once( LIBERTY_PKG_CLASS_PATH.'LibertyMime.php' );

// fetch the attachment details
if( @!BitBase::verifyId( $_REQUEST['attachment_id'] ) || !( $attachment = LibertyMime::loadAttachment( $_REQUEST['attachment_id'], $_REQUEST ))) {
	$gBitSystem->fatalError( tra( "The Attachment ID given is not valid" ));
}

$gBitSmarty->assign( 'attachment', $attachment );

// first we need to check the permissions of the content the attachment belongs to since they inherit them
if( $gContent = LibertyBase::getLibertyObject( $attachment['content_id'] ) ) {
	$gContent->verifyViewPermission();
	// Original images stay with the owner unless the site has chosen to display originals to viewers.
	$mimeType = isset( $attachment['mime_type'] ) ? $attachment['mime_type'] : '';
	if( strncmp( $mimeType, 'image/', 6 ) === 0 && !$gBitSystem->isFeatureActive( 'liberty_original_image' ) && !$gContent->isOwner( $attachment ) && !$gBitUser->isAdmin() && !$gBitUser->hasPermission( $gContent->mAdminContentPerm ) ) {
		$gBitSystem->fatalPermission( $gContent->mViewContentPerm, tra( 'You cannot download the original file.' ) );
	}
	$gBitSmarty->assign( 'gContent', $gContent );

	if( $download_function = $gLibertySystem->getPluginFunction( $attachment['attachment_plugin_guid'], 'download_function', 'mime' )) {
		if( $download_function( $attachment )) {
			LibertyMime::addDownloadHit( $attachment['attachment_id'] );
			die;
		} else {
			if( !empty( $attachment['errors'] )) {
				$msg = '';
				foreach( $attachment['errors'] as $error ) {
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
} else {
	$gBitSystem->fatalError( tra( "Object not found." ), NULL, NULL, 404 );
}
