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
require_once( '../kernel/setup_inc.php' );
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
	$data = !empty( $_REQUEST['plugin'][$attachment['attachment_id']][$attachment['attachment_plugin_guid']] ) ? $_REQUEST['plugin'][$attachment['attachment_id']][$attachment['attachment_plugin_guid']] : array();
	if( $gContent->updateAttachmentParams( $attachment['attachment_id'], $attachment['attachment_plugin_guid'], $data )) {
		$feedback['success'] = tra( "The data was successfully updated." );
	} else {
		$feedback['error'] = $gContent->mErrors;
	}
	// reload the attachment
	$attachment = LibertyMime::getAttachment( $_REQUEST['attachment_id'] );
}

// attachment usage filter stuff is hardcoded for now - ugly as hell but we'll survive it as long as there's no demand for more of these.
if( $gLibertySystem->isPluginActive( 'filterattachment' )) {
	$gBitSmarty->assign( 'usage', attachment_filter_get_usage( $attachment['attachment_id'] ));
}

if( empty( $_REQUEST['size'] )) {
	$_REQUEST['size'] = $gBitSystem->getConfig( 'liberty_thumbnail_size', 'medium' );
}

$attachment['original'] = ( $gContent->isOwner( $attachment ) || $gBitSystem->isFeatureActive( 'liberty_original_image' ));

$gBitSmarty->assign( 'attachment', $attachment );
$gBitSmarty->assign( 'gContent', $gContent );
$gBitSmarty->assign( 'feedback', $feedback );

// what template are we going to use to display this attachment
$gBitSmarty->assign( 'view_template', $gLibertySystem->getMimeTemplate( 'view', $attachment['attachment_plugin_guid'] ));
$gBitSmarty->assign( 'edit_template', $gLibertySystem->getMimeTemplate( 'edit', $attachment['attachment_plugin_guid'] ));

$gBitSystem->display( 'bitpackage:liberty/mime_view.tpl', tra( "View File" ), array( 'display_mode' => 'display' ));
?>
