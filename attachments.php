<?php
/**
 * attachment_browser
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision$
 * @package  liberty
 * @subpackage functions
 */

/**
 * bit setup
 */
require_once("../kernel/includes/setup_inc.php");

$gContent = new LibertyMime();

if( !$gBitUser->isRegistered() ) {
	$gBitSystem->fatalError( tra( 'You need to be logged in to view this page.' ));
}

$feedback = array();
$listHash = &$_REQUEST;
if( $gBitUser->isAdmin() ) {
	if( !empty( $listHash['login'] ) && $listHash['login'] == 'all' ) {
		$listHash['user_id'] = NULL;
	} elseif( !empty( $listHash['login'] ) ) {
		if( $userInfo = $gBitUser->getUserInfo( array( 'login' => $listHash['login'] ) ) ) {
			$listHash['user_id'] = $userInfo['user_id'];
		} else {
			$feedback['error'] = tra( 'That user does not exist.' );
		}
	} else {
		$listHash['user_id'] = $gBitUser->mUserId;
	}
} else {
	$listHash['user_id'] = $gBitUser->mUserId;
}
$attachments = $gContent->getAttachmentList( $listHash );

$gBitSmarty->assign( 'listInfo', $listHash['listInfo'] );
$gBitSmarty->assign( 'attachments', $attachments );
$gBitSmarty->assign( 'feedback', $feedback );
$gBitSystem->display( 'bitpackage:liberty/attachments.tpl', tra( 'Attachments' ), array( 'display_mode' => 'display' ));
?>
