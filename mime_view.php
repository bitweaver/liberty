<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_liberty/Attic/mime_view.php,v 1.3 2008/05/28 18:55:00 squareing Exp $
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

$feedback = array();

// fetch the attachment details
if( @!BitBase::verifyId( $_REQUEST['attachment_id'] ) || !( $attachment = LibertyMime::getAttachment( $_REQUEST['attachment_id'], $_REQUEST ))) {
	$gBitSystem->fatalError( tra( "The Attachment ID given is not valid" ));
}

if( !empty( $_REQUEST['plugin_submit'] )) {
	// now that we have data for a plugin, we'll simply feed it back to the update function of that plugin
	foreach( $_REQUEST['plugin'] as $guid => $data ) {
		if( $update_function = LibertyMime::getPluginFunction( $guid, 'update_function' )) {
			// verify the uploaded file using the plugin
			if( !$update_function( $attachment, $data )) {
				if( !empty( $attachment['errors'] )) {
					$feedback['error'] = $attachment['errors'];
				} else {
					$feedback['error'] = tra( 'There was an unspecified error while updating the file.' );
				}
			} else {
				$feedback['success'] = tra( "The data was successfully updated." );
			}
		}
	}
	// reload the attachment
	$attachment = LibertyMime::getAttachment( $_REQUEST['attachment_id'] );
}

// first we need to check the permissions of the content the attachment belongs to since they inherit them
$gContent = LibertyBase::getLibertyObject( $attachment['content_id'] );
$gContent->verifyViewPermission();
$gBitSmarty->assign( 'gContent', $gContent );
$gBitSmarty->assign( 'attachment', $attachment );
$gBitSmarty->assign( 'feedback', $feedback );

// what template are we going to use to display this attachment
$gBitSmarty->assign( 'view_template', LibertyMime::getMimeTemplate( 'view', $attachment['attachment_plugin_guid'] ));
$gBitSmarty->assign( 'edit_template', LibertyMime::getMimeTemplate( 'edit', $attachment['attachment_plugin_guid'] ));

$gBitSystem->display( 'bitpackage:liberty/mime_view.tpl', tra( "View File" ));
?>
