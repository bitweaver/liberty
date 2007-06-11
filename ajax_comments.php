<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/ajax_comments.php,v 1.2 2007/06/11 20:15:48 squareing Exp $
 * @package liberty
 * @subpackage functions
 */
require_once( '../bit_setup_inc.php' );
 
$staticContent = new LibertyContent();
$gContent = $staticContent->getLibertyObject( $_REQUEST['parent_id'], $_REQUEST['parent_guid'] );

if( !$gBitUser->hasPermission( 'p_liberty_post_comments' )) {
	echo tra( "You do not have the required permissions to post new comments" );
} elseif( $gContent->isCommentable() ) {
	$commentsParentId = $_REQUEST['parent_id'];
	$comments_return_url = $_REQUEST['comments_return_url'];
	include_once( LIBERTY_PKG_PATH.'comments_inc.php' );
	if( isset( $_REQUEST['post_comment_submit'] )) {
		$storeComment->loadComment();
		$postComment = $storeComment->mInfo;
		$postComment['parsed_data'] = $storeComment->parseData( $postComment );
	}
	$gBitSmarty->assign('comment', $postComment);
	$gBitSmarty->assign('commentsParentId', $commentsParentId);
	echo $gBitSmarty->fetch( 'bitpackage:liberty/display_comment.tpl' );
} else {
	echo tra( "Sorry, you can not post a comment here." );
}
?>
