<?php
/**
 * @version  $Revision: 1.6 $
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
		$gBitSystem->fatalError( tra( "No valid content id given." ));
	}

	$gContent = new LibertyContent();
	$gContent = $gContent->getLibertyObject( $_REQUEST['content_id'] );
}
$gBitSmarty->assign_by_ref( 'gContent', $gContent );

// Process the form
// send the user to the content page if he wants to
if( !empty( $_REQUEST['back'] )) {
	bit_redirect( $gContent->getDisplayUrl() );
}

// Update database if needed
if( !empty( $_REQUEST['action'] ) && @BitBase::verifyId( $gContent->mContentId )) {
	if( $_REQUEST["action"] == 'expunge' ) {
		if( $gContent->expungeContentPermissions() ) {
			$feedback['success'] = tra( 'The content permissions were successfully removed.' );
		} else {
			$feedback['error'] = tra( 'The content permissions were not removed.' );
		}
	}

	if( @BitBase::verifyId( $_REQUEST["group_id"] ) && !empty( $_REQUEST["perm"] )) {
		$gBitUser->verifyTicket( TRUE );
		if( $_REQUEST["action"] == 'assign' ) {
			$gContent->storePermission( $_REQUEST["group_id"], $_REQUEST["perm"] );
		} elseif( $_REQUEST["action"] == 'remove' ) {
			$gContent->removePermission( $_REQUEST["group_id"], $_REQUEST["perm"] );
		}
	}
}

// Get a list of groups
$listHash = array( 'sort_mode' => 'group_id_asc' );
$contentPerms['groups'] = $gBitUser->getAllGroups( $listHash );

if( !empty( $gContent->mType['handler_package'] )) {
	$contentPerms['assignable'] = $gBitUser->getGroupPermissions( array( 'package' => $gContent->mType['handler_package'] ));
} else {
	// this is a last resort and will dump all perms a user has
	$contentPerms['assignable'] = $gBitUser->mPerms;
}

// Now we have to get the individual object permissions if any
if( $contentPerms['assigned'] = $gContent->getContentPermissionsList() ) {
	// merge assigned permissions with group permissions
	foreach( array_keys( $contentPerms['groups'] ) as $groupId ) {
		if( !empty( $contentPerms['assigned'][$groupId] )) {
			$contentPerms['groups'][$groupId]['perms'] = array_merge( $contentPerms['groups'][$groupId]['perms'], $contentPerms['assigned'][$groupId] );
		}
	}
}

$gBitSmarty->assign( 'contentPerms', $contentPerms );
$gBitSystem->display( 'bitpackage:liberty/content_permissions.tpl', tra( 'Content Permissions' ));
?>
