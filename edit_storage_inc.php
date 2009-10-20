<?php
/**
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/edit_storage_inc.php,v 1.39 2009/10/20 18:01:48 ukgrad89 Exp $
 *
 * edit_storage_inc
 * @author   spider <spider@steelsun.com>
 * @package  liberty
 * @subpackage functions
 */
global $gBitSmarty, $gContent, $gBitUser, $gBitSystem, $gBitThemes, $gLibertySystem;

// if we have active plugins with an upload function, we call them:
foreach( $gLibertySystem->getPluginFunctions( 'upload_function' ) as $guid => $func ) {
	$func( $gContent );
}

// set up base arguments
$getArgs = explode( '&', $_SERVER['QUERY_STRING'] );
$attachmentBaseArgs = '';
foreach( $getArgs as $arg ) {
	$parts = explode( '=', $arg );
	if( $parts[0] != 'deleteAttachment' ) {
		$attachmentBaseArgs .= $arg."&amp;";
	}
}
$gBitSmarty->assign( 'attachmentBaseArgs', $attachmentBaseArgs );

// delete attachment if requested
if( !empty( $_REQUEST['deleteAttachment'] )) {

	// $gContent is empty when we're editing our personal webpage
	// this is a brutish hack but it seems to work without adverse effeects
	if( empty( $gContent )) {
		$gContent = $gBitUser;
	}

	$attachmentId = $_REQUEST['deleteAttachment'];
	$attachmentInfo = $gContent->getAttachment( $attachmentId );

	// the second part of this check seems odd (never used?) to me, but I'll leave it in for now - spiderr 10/17/2007
	if( $gContent->hasAdminPermission() || ( $gContent->isOwner( $attachmentInfo ) && $gBitUser->hasPermission( 'p_liberty_delete_attachment' ))) {
		$gContent->expungeAttachment( $attachmentId );
	}

	// in case we have deleted attachments
	// seems like there should be a better way to do this -- maybe original assign should have been by reference?
	$gBitSmarty->clear_assign( 'gContent' );
	$gBitSmarty->assign( 'gContent', $gContent );
}

// make sure js is being loaded
if( $gBitSystem->getConfig( 'liberty_attachment_style' ) == 'ajax' ) {
	$gBitThemes->loadAjax( 'mochikit', array( 'Iter.js', 'DOM.js' ) );
	$gBitThemes->loadJavascript( LIBERTY_PKG_PATH.'scripts/LibertyAttachment.js', TRUE );
}
?>
