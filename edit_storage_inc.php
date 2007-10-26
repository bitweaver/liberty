<?php
/**
 * edit_storage_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.23 $
 * @package  liberty
 * @subpackage functions
 *
 * This file is automatically included by edit_storage.tpl - All you need to do is include edit_storage.tpl
 * from your template file.
 *
 * Calculate a base URL for the attachment deletion/removal icons to use
 */
include_once( '../bit_setup_inc.php' );
global $gBitSmarty, $gContent, $gBitUser, $gBitSystem, $gLibertySystem, $gBitThemes;

// we need to load gContent if this is an ajax request
if( BitThemes::isAjaxRequest() ) {
	include_once( LIBERTY_PKG_PATH.'lookup_content_inc.php' );
}

$attachmentActionBaseUrl = $gBitSmarty->get_template_vars( 'attachmentActionBaseUrl' );

if( empty( $attachmentActionBaseUrl )) {
	if( $gBitSystem->getConfig( 'liberty_attachment_style' ) == 'ajax' ) {
		$attachmentActionBaseUrl = LIBERTY_PKG_URL.'edit_storage_inc.php?';
	} else {
		$attachmentActionBaseUrl = $_SERVER['PHP_SELF'].'?';
	}
	$GETArgs = split( '&',$_SERVER['QUERY_STRING'] );
	$firstArg = TRUE;

	foreach( $GETArgs as $arg ) {
		$parts = split( '=', $arg );
		if( $parts[0] != 'deleteAttachment' ) {
			if( !$firstArg ) {
				$attachmentActionBaseUrl .= "&amp;";
			} else {
				$firstArg = FALSE;
			}

			$attachmentActionBaseUrl .= $arg;
		}
	}
	$gBitSmarty->assign( 'attachmentActionBaseUrl', $attachmentActionBaseUrl );
}

if( !empty( $_REQUEST['deleteAttachment'] )) {
	$attachmentId = $_REQUEST['deleteAttachment'];
	$attachmentInfo = $gContent->getAttachment( $attachmentId );

	// the second part of this check seems odd (never used?) to me, but I'll leave it in for now - spiderr 10/17/2007
	if( $gContent->hasAdminPermission() || ( $attachmentInfo['user_id'] == $gBitUser->mUserId && $gBitUser->hasPermission( 'p_liberty_delete_attachment' ))) {
		$gContent->expungeAttachment( $attachmentId );
	}

	// in case we have deleted attachments
	// seems like there should be a better way to do this -- maybe original assign should have been by reference?
	$gBitSmarty->clear_assign( 'gContent' );
	$gBitSmarty->assign( 'gContent', $gContent );
}

if( $gBitSystem->getConfig('liberty_attachment_style') == 'ajax' ) {
	$gBitThemes->loadAjax( 'mochikit' );
	$gBitSmarty->assign( 'attachments_ajax', TRUE );
}

// output some stuff for ajax div
if( BitThemes::isAjaxRequest() ) {
	echo $gBitSmarty->fetch( 'bitpackage:liberty/edit_storage_list.tpl' );
	/* this die is no good here 
	   it screws up all content editing that uses ajax to load forms.
	   I'm taking it out, as it was added recently.
	   If there is a need to have it then we have to target the cases when
	   it is needed.  this comment was added on 10/26/07, if its been a while
	   when you are reading this, then you can probably go ahead and 
	   delete this all -wjames5
	 */
	// die;
}

?>
