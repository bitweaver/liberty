<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_liberty/view_file.php,v 1.1 2008/06/10 18:43:54 squareing Exp $
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

// first we need to check the permissions of the content the attachment belongs to since they inherit them
$gContent = LibertyBase::getLibertyObject( $attachment['content_id'] );
$gContent->verifyViewPermission();

if( !empty( $_REQUEST['plugin_submit'] )) {
	// now that we have data for a plugin, we'll simply feed it back to the update function of that plugin
	foreach( $_REQUEST['plugin'][$attachment['attachment_id']] as $guid => $data ) {
		if( $gContent->updateAttachmentParams( $_REQUEST['attachment_id'], $guid, $data )) {
			$feedback['success'] = tra( "The data was successfully updated." );
		} else {
			$feedback['error'] = $gContent->mErrors;
		}
	}
	// reload the attachment
	$attachment = LibertyMime::getAttachment( $_REQUEST['attachment_id'] );
}

$gBitSmarty->assign( 'attachment', $attachment );
$gBitSmarty->assign( 'gContent', $gContent );
$gBitSmarty->assign( 'feedback', $feedback );

// what template are we going to use to display this attachment
$gBitSmarty->assign( 'view_template', LibertyMime::getMimeTemplate( 'view', $attachment['attachment_plugin_guid'] ));
$gBitSmarty->assign( 'edit_template', LibertyMime::getMimeTemplate( 'edit', $attachment['attachment_plugin_guid'] ));

$gBitSystem->display( 'bitpackage:liberty/mime_view.tpl', tra( "View File" ));
?>
