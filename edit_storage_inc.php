<?php
/**
 * edit_storage_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.19 $
 * @package  liberty
 * @subpackage functions
 *
 * This file is automatically included by edit_storage.tpl - All you need to do is include edit_storage.tpl
 * from your template file.
 *
 * Calculate a base URL for the attachment deletion/removal icons to use
 */
global $gBitSmarty, $gContent, $gBitUser, $gBitSystem, $gLibertySystem, $gBitThemes;

$attachmentActionBaseUrl = $gBitSmarty->get_template_vars( 'attachmentActionBaseURL' );
if( empty( $attachmentActionBaseUrl )) {
	$attachmentActionBaseURL = $_SERVER['PHP_SELF'].'?';
	$GETArgs = split( '&',$_SERVER['QUERY_STRING'] );
	$firstArg = TRUE;

	foreach( $GETArgs as $arg ) {
		$parts = split( '=', $arg );
		if( $parts[0] != 'deleteAttachment' ) {
			if( !$firstArg ) {
				$attachmentActionBaseURL .= "&amp;";
			} else {
				$firstArg = FALSE;
			}

			$attachmentActionBaseURL .= $arg;
		}
	}
	$gBitSmarty->assign( 'attachmentActionBaseURL', $attachmentActionBaseURL );
}

if( !empty( $_REQUEST['deleteAttachment'] )) {
	$attachmentId = $_REQUEST['deleteAttachment'];
	$attachmentInfo = $gContent->getAttachment( $attachmentId );

	// TODO: Should we have a permission for deleting attachments?
	if( $gBitUser->isAdmin() || ( $attachmentInfo['user_id'] == $gBitUser->mUserId && $gBitUser->hasPermission( 'p_liberty_delete_attachment' ))) {
		$gContent->expungeAttachment( $attachmentId );
	}
}
$gBitSmarty->assign_by_ref( 'gLibertySystem', $gLibertySystem );

// in case we have deleted attachments
// seems like there should be a better way to do this -- maybe original assign should have been by reference?
$gBitSmarty->clear_assign( 'gContent' );
$gBitSmarty->assign( 'gContent', $gContent );
$gBitThemes->loadAjax( 'prototype' );
?>
