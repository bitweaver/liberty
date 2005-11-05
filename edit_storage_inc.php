<?php
/**
 * edit_storage_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.1.1.1.2.8 $
 * @package  liberty
 * @subpackage functions
 *
 * This file is automatically included by edit_storage.tpl - All you need to do is include edit_storage.tpl
 * from your template file.
 *
 * Calculate a base URL for the attachment deletion/removal icons to use
 */
global $gBitSmarty, $gContent, $gBitUser, $gBitSystem, $gLibertySystem;
$attachmentActionBaseURL = $_SERVER['PHP_SELF'].'?';
$GETArgs = split('&',$_SERVER['QUERY_STRING']);
$firstArg = TRUE;

foreach( $GETArgs as $arg ) {
	$parts = split('=',$arg);
	if( ( $parts[0] != 'deleteAttachment' ) && $parts[0] != 'detachAttachment' ) {
		if( !$firstArg )
			$attachmentActionBaseURL .= "&amp;";
		else
			$firstArg = FALSE;
		$attachmentActionBaseURL .= $arg;
	}
}
$gBitSmarty->assign( 'attachmentActionBaseURL', $attachmentActionBaseURL );

if( !empty( $_REQUEST['deleteAttachment'] ) ) {
	$attachmentId = $_REQUEST['deleteAttachment'];
	
	$siblingAttachments = $gContent->getSiblingAttachments( $attachmentId );
	$attachmentInfo = $gContent->getAttachment( $attachmentId );
	
	if( count( $siblingAttachments ) > 0 || ( !$gBitUser->isAdmin() && $gBitUser->mUserId != $attachmentInfo['user_id'] && $gBitUser->mPerms['bit_p_detach_attachment'] == 'y' ) ) {
		// Other tiki_attachment rows reference the same foreign_id so we should just detach
		$gContent->detachAttachment( $attachmentId );	
	} else {
		$gContent->expungeAttachment( $attachmentId );
	}
} elseif( !empty( $_REQUEST['detachAttachment'] ) ) {
	$attachmentId = $_REQUEST['detachAttachment'];
	$attachmentInfo = $gContent->getAttachment( $attachmentId );
	
	if( $gBitUser->isAdmin() || $gBitUser->mPerms['bit_p_detach_attachment'] == 'y' || $attachmentInfo['user_id'] == $gBitUser->mUserId ) {
		$gContent->detachAttachment( $attachmentId );
	}
}
$gBitSmarty->assign_by_ref( 'gLibertySystem', $gLibertySystem );	
?>
