<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/ajax_comments.php,v 1.1 2007/06/10 15:14:40 wjames5 Exp $
 * @package liberty
 * @subpackage functions
 */
require_once( '../bit_setup_inc.php' );
global $gContent, $gBitSmarty;
 
require_once (LIBERTY_PKG_PATH.'LibertyContent.php');

$staticContent = new LibertyContent();
$gContent = $staticContent->getLibertyObject( $_REQUEST['parent_id'], $_REQUEST['parent_guid']);

if( $gContent->isCommentable() ) {
	$commentsParentId = $_REQUEST['parent_id'];
	$comments_return_url = $_REQUEST['comments_return_url'];
	include_once( LIBERTY_PKG_PATH.'comments_inc.php' );
	if (isset($_REQUEST['post_comment_submit'])){
		$storeComment->loadComment();
		$postComment = $storeComment->mInfo;
		$postComment['parsed_data'] = $storeComment->parseData( $postComment );
	}
	$gBitSmarty->assign('comment', $postComment);
	$gBitSmarty->assign('commentsParentId', $commentsParentId);
	echo $gBitSmarty->fetch( 'bitpackage:liberty/display_comment.tpl' );
}else{
	echo "Sorry, you do not have permission to post comments to this content.";
}
?>