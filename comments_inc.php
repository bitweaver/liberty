<?php
/**
 * comment_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.1.1.1.2.7 $
 * @package  Liberty
 * @subpackage functions
 */

// $Header: /cvsroot/bitweaver/_bit_liberty/comments_inc.php,v 1.1.1.1.2.7 2005/06/30 02:56:19 jht001 Exp $

// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// This file sets up the information needed to display
// the comments preferences, post-comment box and the
// list of comments. Finally it displays blog-comments.tpl
// using this information

// Setup URLS for the Comments next and prev buttons and use variables that
// cannot be aliased by normal Bit variables.
// Traverse each _REQUEST data adn put them in an array

// this script may only be included - so its better to die if called directly.

/**
 * required setup
 */
require_once (LIBERTY_PKG_PATH.'LibertyComment.php');

global $commentsLib, $smarty;

$postComment = array();
$formfeedback = array();
$smarty->assign_by_ref('formfeedback', $formfeedback);

if (!empty($_REQUEST['delete_comment_id']) && $gBitUser->hasPermission( 'bit_p_post_comments' )) {
	$deleteComment = new LibertyComment($_REQUEST['delete_comment_id']);
	if (!empty ($deleteComment->mInfo['content_id'])) {
		$deleteComment->deleteComment();
	}
}

if (!empty($_REQUEST['post_comment_id']) && $gBitUser->hasPermission( 'bit_p_post_comments' )) {
	$post_comment_id = $_REQUEST['post_comment_id'];
	$editComment = new LibertyComment($post_comment_id);
	if ($editComment->mInfo['content_id']) {
		if (!$editComment->userCanEdit()) {
			$formfeedback['error'] = "You do not have permission to edit this comment.";
			$editComment = NULL;
			$post_comment_id = NULL;
		} else {
			$postComment['data'] = $editComment->mInfo['data'];
			$postComment['title'] = $editComment->mInfo['title'];
		}
	} else {
		$formfeedback['error'] = "Comment does not exist.";
		$editComment = NULL;
		$post_comment_id = NULL;
	}
} else {
	$post_comment_id = NULL;
	$editComment = NULL;
}
$smarty->assign('post_comment_id', $post_comment_id);

// Store comment posts
if (!empty($_REQUEST['post_comment_submit']) && $gBitUser->hasPermission( 'bit_p_post_comments' )) {
	$storeComment = new LibertyComment(!empty($editComment->mCommentId) ? $editComment->mCommentId : NULL);
	$storeRow = array();
	$storeRow['title'] = $_REQUEST['comment_title'];
	$storeRow['edit'] = $_REQUEST['comment_data'];
	$storeRow['parent_id'] = (!empty($storeComment->mInfo['parent_id']) ? $storeComment->mInfo['parent_id'] : (empty($_REQUEST['post_comment_reply_id']) ? $commentsParentId : $_REQUEST['post_comment_reply_id']));
	$storeRow['content_id'] = (!empty($storeComment->mContentId) ? $storeComment->mContentId : NULL);
	$storeComment->storeComment($storeRow);
}

// $post_comment_request is a flag indicating whether or not to display the comment input form
if (empty($_REQUEST['post_comment_request'])) {
	$post_comment_request = NULL;
} elseif( $gBitUser->hasPermission( 'bit_p_post_comments' ) ) {
	$post_comment_request = TRUE;
}
$smarty->assign_by_ref('post_comment_request', $post_comment_request);

// $post_comment_preview is a flag indicating that the user wants to preview their comment prior to saving it
if( !empty( $_REQUEST['post_comment_preview'] ) ) {
	$postComment['title'] = $_REQUEST['comment_title'];
	$postComment['data'] = $_REQUEST['comment_data'];
	$postComment['parsed_data'] = LibertyComment::parseData( $_REQUEST['comment_data'], 'bitwiki' );
	$smarty->assign('post_comment_preview', TRUE);
}

// $post_comment_reply_id is the content_id which a post is replying to
if (!empty($_REQUEST['post_comment_reply_id'])) {
	$post_comment_reply_id = $_REQUEST['post_comment_reply_id'];
	$tmpComment = new LibertyComment(NULL, $post_comment_reply_id);
	//$postComment['data'] = $commentsLib->quoteComment($tmpComment->mInfo['data']);  // This is super-ugly, better to just not quote at all, the indented comment indicates what comment it is replying to
	$postComment['title'] = tra('Re:')." ".$tmpComment->mInfo['title'];
	$smarty->assign('post_comment_reply_id', $post_comment_reply_id);
}

$maxComments = $gBitSystem->getPreference( 'wiki_comments_per_page' );
if (!empty($_REQUEST["comments_maxComments"])) {
	$maxComments = $_REQUEST["comments_maxComments"];
	$comments_at_top_of_page = 'y';
}

$comments_sort_mode = $gBitSystem->getPreference( 'wiki_comments_default_ordering' );
if (!empty($_REQUEST["comments_sort_mode"])) {
	$comments_sort_mode = $_REQUEST["comments_sort_mode"];
	$comments_at_top_of_page = 'y';
}

$comments_display_style = $gBitSystem->getPreference( 'wiki_comments_default_display_mode' );
if (!empty($_REQUEST["comments_style"])) {
	$comments_display_style = $_REQUEST["comments_style"];
	$comments_at_top_of_page = 'y';
}

if( !empty( $_REQUEST['comment_page'] ) || !empty( $_REQUEST['post_comment_request'] ) ) {
	$comments_at_top_of_page = 'y';
}
$commentOffset = !empty( $_REQUEST['comment_page'] ) ? ($_REQUEST['comment_page'] - 1) * $maxComments : 0;

$gComment = new LibertyComment( NULL, $gContent->mContentId );
// $commentsParentId is the content_id which the comment tree is attached to
if (empty($commentsParentId)) {
	$comments = NULL;
	$numComments = 0;
} else {
	$comments = $gComment->getComments( $commentsParentId, $maxComments, $commentOffset, $comments_sort_mode, $comments_display_style );
	$numComments = $gComment->getNumComments( $commentsParentId );
}
$smarty->assign('comments', $comments);
$smarty->assign('maxComments', $maxComments);

$numCommentPages = ceil( $numComments / $maxComments );
$currentPage = !empty( $_REQUEST['comment_page'] ) ? $_REQUEST['comment_page'] : 1;

$commentsPgnHash = array(
	'numPages' => $numCommentPages,
	'pgnName' => 'comment_page',
	'page' => $currentPage,
	'url' => $comments_return_url,
	'maxComments' => $maxComments,
	'comments_sort_mode' => $comments_sort_mode,
	'comments_style' => $comments_display_style,
	'ianchor' => 'editcomments',
);
$smarty->assign( 'commentsPgnHash', $commentsPgnHash );
$smarty->assign('postComment', $postComment);

$smarty->assign('currentTimestamp', time());
$smarty->assign('comments_return_url', $comments_return_url);
$smarty->assign('comments_at_top_of_page', ( isset( $comments_at_top_of_page ) && $gBitSystem->getPreference( 'comments_reorganise_page_layout', 'n' ) == 'y' ) ? $comments_at_top_of_page : NULL );
$smarty->assign('comments_style', $comments_display_style);
$smarty->assign('comments_sort_mode', $comments_sort_mode);
?>
