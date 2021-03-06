<?php
/**
 * @version  $Revision$
 * @package  liberty
 * @subpackage functions
 */

/**
 * bit setup
 */
require_once( '../kernel/includes/setup_inc.php' );

$gBitSystem->verifyPermission( 'p_liberty_assign_content_perms' );

require_once( LIBERTY_PKG_INCLUDE_PATH.'lookup_content_inc.php' );

if( $gContent == null ) {
	$gBitSystem->fatalError('Could not find the requested content.', NULL, NULL, HttpStatusCodes::HTTP_GONE );
}

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
		$gBitUser->verifyTicket();
		if( $_REQUEST["action"] == 'assign' ) {
			$gContent->storePermission( $_REQUEST["group_id"], $_REQUEST["perm"] );
		} elseif( $_REQUEST["action"] == 'negate' ) {
			$gContent->storePermission( $_REQUEST["group_id"], $_REQUEST["perm"], TRUE );
		} elseif( $_REQUEST["action"] == 'remove' ) {
			$gContent->removePermission( $_REQUEST["group_id"], $_REQUEST["perm"] );
		}
	}
}

// Get a list of groups
$listHash = array( 'sort_mode' => 'group_id_asc', 'visible' => 1 );
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

// if we've called this page as part of an ajax update, we output the appropriate data
if( $gBitThemes->isAjaxRequest() ) {
	if( count( $contentPerms['groups'] <= 10 )) {
		$size = 'large/';
	} else {
		$size = 'small/';
	}

	$gid = $_REQUEST['group_id'];
	$perm = $_REQUEST['perm'];

	// we're applying the same logic as in the template. if you fix / change anything here, please update the template as well.
	$biticon = array(
		'ipackage' => 'icons',
		'iname'    => $size.'media-playback-stop',
		'iexplain' => '',
		'iforce'   => 'icon',
	);
	$action = 'assign';
	if( !empty( $contentPerms['groups'][$gid]['perms'][$perm] )) {
		$biticon['iname'] = $size.'dialog-ok';
		if( !empty( $contentPerms['assigned'][$gid][$perm] )) {
			$assigned = $contentPerms['assigned'][$gid][$perm];
			$biticon['iname'] = $size.'list-add';
			$action = 'negate';
		}
		if( !empty( $assigned['is_revoked'] )) {
			$biticon['iname'] = $size.'list-remove';
			$action = 'remove';
		}
	}

	$gBitSmarty->loadPlugin( 'smarty_function_biticon' );
	$ret = '<a title="'.$contentPerms['groups'][$gid]['group_name']." :: ".$perm.'" '.
			'href="javascript:void(0);" onclick="BitAjax.updater('.
			"'{$perm}{$gid}', ".
			"'".LIBERTY_PKG_URL."content_permissions.php', ".
			"'action={$action}&amp;content_id={$gContent->mContentId}&amp;perm={$perm}&amp;group_id={$gid}'".
		')">'.smarty_function_biticon( $biticon, $gBitSmarty ).'</a>';
	echo $ret;
	die;
}

$gBitSystem->display( 'bitpackage:liberty/content_permissions.tpl', tra( 'Content Permissions' ), array( 'display_mode' => 'display' ));
?>
