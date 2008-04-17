<?php
// $Header: /cvsroot/bitweaver/_bit_liberty/Attic/comments_moderation_inc.php,v 1.1 2008/04/17 13:26:29 wjames5 Exp $
// Copyright (c) 2004-2008 bitweaver Group
// All Rights Reserved.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.


/**
 * load up moderation
 * we need to include its bit_setup_inc incase comments gets loaded first
 */
if ( is_file( BIT_ROOT_PATH.'moderation/bit_setup_inc.php' ) ){
	require_once( BIT_ROOT_PATH.'moderation/bit_setup_inc.php' );
}

if( $gBitSystem->isPackageActive('moderation') &&
	!defined('comments_moderation_callback') ) {
	global $gModerationSystem;

	require_once(MODERATION_PKG_PATH.'ModerationSystem.php');

	// What are our transitions
	$commentTransitions = array( "comment_post" =>
							   array (MODERATION_PENDING =>
									  array(MODERATION_APPROVED,
											MODERATION_REJECTED),
									  MODERATION_REJECTED => MODERATION_DELETE,
									  MODERATION_APPROVED => MODERATION_DELETE,
									  ),
							   );

	function comments_moderation_callback(&$pModeration) {
		global $gBitUser, $gBitSystem;

		if ($pModeration['type'] == 'comment_post') {
			$comment = new LibertyComment( NULL, $pModeration['content_id'] );
			$comment->load();
			if ($pModeration['status'] == MODERATION_APPROVED) {
				// change its status
				$comment->storeStatus( 50 );
				// delete the ticket
				$pModeration['status'] = MODERATION_DELETE;
			}else if($pModeration['status'] == MODERATION_REJECTED) {
				// change its status to soft delete
				$comment->storeStatus( -999 );
				// delete the ticket
				$pModeration['status'] = MODERATION_DELETE;
			}
		}

		return TRUE;
	}

	// Register our moderation transitions
	$gModerationSystem->registerModerationListener('liberty',
												   'comments_moderation_callback',
												   $commentTransitions);
}

?>
