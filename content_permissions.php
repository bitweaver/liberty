<?php
/**
 * @version  $Revision: 1.3 $
 * @package  liberty
 * @subpackage functions
 */

/**
 * bit setup
 */
require_once( '../bit_setup_inc.php' );

$gBitSystem->verifyPermission( 'p_liberty_assign_content_perms' );

// If we haven't got any content loaded yet, load it
if( empty( $gContent )) {
	// make sure we have a content_id we can work with
	if( empty( $_REQUEST["content_id"] ) || $_REQUEST["content_id"] < 1 ) {
		$gBitSmarty->assign( 'msg', tra( "No valid content id given." ));
		$gBitSystem->display( 'error.tpl' );
die;
	}

	$gContent = new LibertyContent();
	$gContent = $gContent->getLibertyObject( $_REQUEST['content_id'] );
}
$gBitSmarty->assign_by_ref( 'gContent', $gContent );

// Process the form
// send the user to the content page if he wants to
if( !empty( $_REQUEST['back'] )) {
	header( "Location: ".$gContent->getDisplayUrl() );
	die;
}

// Update database if needed
if( @BitBase::verifyId( $_REQUEST["group_id"] ) && @BitBase::verifyId( $gContent->mContentId ) && !empty( $_REQUEST["perm"] ) && !empty( $_REQUEST['action'] )) {
	$gBitUser->verifyTicket( TRUE );
	if( $_REQUEST["action"] == 'assign' ) {
		$gContent->storePermission( $_REQUEST["group_id"], $_REQUEST["perm"], $gContent->mContentId );
	} elseif( $_REQUEST["action"] == 'remove' ) {
		$gContent->removePermission( $_REQUEST["group_id"], $_REQUEST["perm"] );
	}
}

// Now we have to get the individual object permissions if any
$contentPerms['assigned'] = $gContent->loadAllObjectPermissions( $_REQUEST );

// Get a list of groups
$listHash = array( 'sort_mode' => 'group_name_asc' );
$userGroups = $gBitUser->getAllGroups( $listHash );
$contentPerms['groups'] = $userGroups["data"];

// Get a list of permissions
if( empty( $assignPerms )) {
	if( !empty( $gContent->mType['handler_package'] )) {
		$contentPerms['assignable'] = $gBitUser->getGroupPermissions( NULL, $gContent->mType['handler_package'] );
	} else {
		// this is a last resort and will dump all perms a user has
		$contentPerms['assignable'] = $gBitUser->mPerms;
	}
}
$gBitSmarty->assign( 'contentPerms', $contentPerms );

$gBitSystem->display( 'bitpackage:liberty/content_permissions.tpl', tra( 'Content Permissions' ));
?>
