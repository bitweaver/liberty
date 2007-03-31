<?php
/**
 * @version  $Revision: 1.6 $
 * @package  liberty
 * @subpackage functions
 */
$gBitSystem->verifyPermission( 'p_liberty_assign_content_perms' );

// If we haven't got any content loaded yet, load it
if( empty( $gContent ) ) {
	// make sure we have a content_id we can work with
	if( empty( $_REQUEST["perm_content_id"] ) || $_REQUEST["perm_content_id"] < 1 ) {
		$gBitSmarty->assign( 'msg', tra( "No valid content id given." ) );
		$gBitSystem->display( 'error.tpl' );
die;
	}

	$gContent = new LibertyContent();
	$gContent = $gContent->getLibertyObject( $_REQUEST['perm_content_id'] );
}
$gBitSmarty->assign_by_ref( 'gContent', $gContent );

// Process the form
// send the user to the content page if he wants to
if( !empty( $_REQUEST['back'] ) ) {
	header( "Location: ".$gContent->getDisplayUrl() );
	die;
}

// Update database if needed
if( !empty( $_REQUEST["perm_group_id"] ) && !empty( $gContent->mContentId ) && !empty( $_REQUEST["perm"] ) ) {
	if( isset( $_REQUEST["assign"] ) ) {
		$gContent->storePermission( $_REQUEST["perm_group_id"], $_REQUEST["perm"], $gContent->mContentId );
	}

	if( isset( $_REQUEST["action"] ) ) {
		if( $_REQUEST["action"] == 'remove' ) {
			$gContent->removePermission( $_REQUEST["perm_group_id"], $_REQUEST["perm"] );
		}
	}
}

// Now we have to get the individual object permissions if any
$assignedPerms = $gContent->loadAllObjectPermissions( $_REQUEST );
$gBitSmarty->assign( 'assignedPerms', $assignedPerms );

// Get a list of groups
$listHash = array( 'sort_mode' => 'group_name_asc' );
$userGroups = $gBitUser->getAllGroups( $listHash );
$gBitSmarty->assign_by_ref( 'userGroups', $userGroups["data"] );

// Get a list of permissions
if( empty( $assignPerms ) ) {
	if( !empty( $gContent->mType['handler_package'] ) ) {
		$assignPerms = $gBitUser->getGroupPermissions( NULL, $gContent->mType['handler_package'] );
	} else {
		$assignPerms = $gBitUser->mPerms;
	}
}
$gBitSmarty->assign_by_ref( 'assignPerms', $assignPerms );
?>
