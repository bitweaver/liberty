<?php
/**
 * edit_storage_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.25 $
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

// set up base URL
if( $gBitSystem->getConfig( 'liberty_attachment_style' ) == 'ajax' ) {
	$attachmentBaseUrl = LIBERTY_PKG_URL.'edit_storage_inc.php';
} else {
	$attachmentBaseUrl = $_SERVER['PHP_SELF'];
}
$gBitSmarty->assign( 'attachmentBaseUrl', $attachmentBaseUrl );

// set up base arguments
$getArgs = split( '&', $_SERVER['QUERY_STRING'] );
$attachmentBaseArgs = '';
foreach( $getArgs as $arg ) {
	$parts = split( '=', $arg );
	if( $parts[0] != 'deleteAttachment' ) {
		$attachmentBaseArgs .= $arg."&amp;";
	}
}
$gBitSmarty->assign( 'attachmentBaseArgs', $attachmentBaseArgs );

// delete attachment if requested
if( !empty( $_REQUEST['deleteAttachment'] )) {
	$attachmentId = $_REQUEST['deleteAttachment'];
	$attachmentInfo = $gContent->getAttachment( $attachmentId );

	// the second part of this check seems odd (never used?) to me, but I'll leave it in for now - spiderr 10/17/2007
	if( $gContent->hasAdminPermission() || ( $gContent->isOwner( $attachmentInfo ) && $gBitUser->hasPermission( 'p_liberty_delete_attachment' ))) {
		//$gContent->expungeAttachment( $attachmentId );
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

// output some stuff for ajax div if requested
/* This in general is no good here.
 * it creates problems for packages loading up entire editing forms using ajax.
 * If there is some need to display the list tpl, then it should probably 
 * happen in an another tpl or via another php file - presumably the one which 
 * is including this file. This is a bad shortcut.  I'm commenting it out for 
 * now, as I've not been able to find a part of bw that needs it. If this 
 * causes something of yours to break come get me so we can sort it out.  
 * -wjames5
 *
 *
 * Since this is called as a service, it has to happen automagically - this is 
 * the reason why we check for isAjaxRequest and don't really have an option to 
 * check for anything else.
 *
 * If you are calling this page via an XMLHttpRequest and don't want to display 
 * the content, are you using a specific format header like 'xml' or 
 * 'center_only'? any other distinguishing features? perhaps we could use a
 * $_REQUEST parameter like $_REQUEST['no_attachment_content'] 
 * - xing - Wednesday Oct 31, 2007   10:43:17 CET
 */
if( BitThemes::isAjaxRequest() && $gBitSystem->mFormatHeader != 'xml' ) {
	$gBitSystem->setFormatHeader( 'center_only' );
	$gBitSystem->display( 'bitpackage:liberty/edit_storage_list.tpl' );
}
?>
