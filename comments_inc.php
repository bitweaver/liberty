<?php
/**
 * comment_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.18 $
 * @package  liberty
 * @subpackage functions
 */

// $Header: /cvsroot/bitweaver/_bit_liberty/comments_inc.php,v 1.18 2006/07/12 16:47:15 sylvieg Exp $

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
* @param $commentsParentId = the content id of the object where a new comment will be attached - mandatory
* @param $commentsParentIds = the list of content id of object the comments will be displayed - if not defined $commentsParentId
* @param $comments_return_url
**/

/**
 * required setup
 */
require_once (LIBERTY_PKG_PATH.'LibertyComment.php');

global $commentsLib, $gBitSmarty;

$postComment = array();
$formfeedback = array();
$gBitSmarty->assign_by_ref('formfeedback', $formfeedback);

if( @BitBase::verifyId($_REQUEST['delete_comment_id']) && $gBitUser->hasPermission( 'p_liberty_post_comments' )) {
	$deleteComment = new LibertyComment($_REQUEST['delete_comment_id']);
	if( @BitBase::verifyId($deleteComment->mInfo['content_id'] ) ) {
		$deleteComment->deleteComment();
	}
}

if( @BitBase::verifyId($_REQUEST['post_comment_id']) && $gBitUser->hasPermission( 'p_liberty_post_comments' )) {
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
$gBitSmarty->assign('post_comment_id', $post_comment_id);

// Store comment posts
if (!empty($_REQUEST['post_comment_submit']) && $gBitUser->hasPermission( 'p_liberty_post_comments' )) {
	$storeComment = new LibertyComment(@BitBase::verifyId($editComment->mCommentId) ? $editComment->mCommentId : NULL);
	$storeRow = array();
	$storeRow['title'] = $_REQUEST['comment_title'];
	$storeRow['edit'] = $_REQUEST['comment_data'];
	$storeRow['root_id'] = $commentsParentId;
	$storeRow['parent_id'] = (@BitBase::verifyId($storeComment->mInfo['parent_id']) ? $storeComment->mInfo['parent_id'] : (!@BitBase::verifyId($_REQUEST['post_comment_reply_id']) ? $commentsParentId : $_REQUEST['post_comment_reply_id']));
	$storeRow['content_id'] = (@BitBase::verifyId($storeComment->mContentId) ? $storeComment->mContentId : NULL);
	if (!empty( $_REQUEST['format_guid'] ) ) {
		$storeRow['format_guid'] = $_REQUEST['format_guid'];
	}
	$storeComment->storeComment($storeRow);
}

// $post_comment_request is a flag indicating whether or not to display the comment input form
if( empty( $_REQUEST['post_comment_request'] ) && !$gBitSystem->isFeatureActive( 'comments_auto_show_form' ) ) {
	$post_comment_request = NULL;
} elseif( $gBitUser->hasPermission( 'p_liberty_post_comments' ) ) {
	$post_comment_request = TRUE;
}
if( !empty( $_REQUEST['post_comment_request'] ) && $_REQUEST['post_comment_request'] == 'y' && !$gBitUser->hasPermission( 'p_liberty_post_comments' ) ) {
	$gBitSystem->fatalPermission( 'p_liberty_post_comments' );
}
$gBitSmarty->assign_by_ref('post_comment_request', $post_comment_request);

// $post_comment_preview is a flag indicating that the user wants to preview their comment prior to saving it
if( !empty( $_REQUEST['post_comment_preview'] ) ) {
	$postComment['title'] = $_REQUEST['comment_title'];
	$postComment['data'] = $_REQUEST['comment_data'];
	$postComment['format_guid'] = PLUGIN_GUID_TIKIWIKI;
	$postComment['parsed_data'] = LibertyComment::parseData( $postComment );
	$gBitSmarty->assign('post_comment_preview', TRUE);
}

if( !empty( $_REQUEST['post_comment_preview'] ) || $post_comment_request ) {
	include_once( LIBERTY_PKG_PATH.'edit_help_inc.php'); // to set up the format_guid list
}

// $post_comment_reply_id is the content_id which a post is replying to
if (@BitBase::verifyId($_REQUEST['post_comment_reply_id'])) {
	$post_comment_reply_id = $_REQUEST['post_comment_reply_id'];
	$tmpComment = new LibertyComment(NULL, $post_comment_reply_id);
	//$postComment['data'] = $commentsLib->quoteComment($tmpComment->mInfo['data']);  // This is super-ugly, better to just not quote at all, the indented comment indicates what comment it is replying to

	if (preg_match('/^' . tra('Re:') . '/', $tmpComment->mInfo['title'])) {
		$comment_prefix = '';
		}
	else {
		$comment_prefix = tra('Re:') . " ";
		}
	$postComment['title'] = $comment_prefix . $tmpComment->mInfo['title'];

	$gBitSmarty->assign('post_comment_reply_id', $post_comment_reply_id);
}

$maxComments = $gBitSystem->getConfig( 'comments_per_page', 10 );
if (!empty($_REQUEST["comments_maxComments"])) {
	$maxComments = $_REQUEST["comments_maxComments"];
	$comments_at_top_of_page = 'y';
}

$comments_sort_mode = $gBitSystem->getConfig( 'comments_default_ordering', 'commentDate_desc' );
if (!empty($_REQUEST["comments_sort_mode"])) {
	$comments_sort_mode = $_REQUEST["comments_sort_mode"];
	$comments_at_top_of_page = 'y';
}

$comments_display_style = $gBitSystem->getConfig( 'comments_default_display_mode', 'threaded' );
if( !empty( $_REQUEST["comments_style"] ) ) {
	$comments_display_style = $_REQUEST["comments_style"];
	$comments_at_top_of_page = 'y';
}

if( !empty( $_REQUEST['comment_page'] ) || !empty( $_REQUEST['post_comment_request'] ) ) {
	$comments_at_top_of_page = 'y';
}
$commentOffset = !empty( $_REQUEST['comment_page'] ) ? ($_REQUEST['comment_page'] - 1) * $maxComments : 0;

$gComment = new LibertyComment( NULL, $gContent->mContentId );

$currentPage = !empty( $_REQUEST['comment_page'] ) ? $_REQUEST['comment_page'] : 1;

#logic to support displaying a single comment -- used when we need a URL pointing to a comment
if (!empty($_REQUEST['view_comment_id'])) {
        $commentOffset = $gComment->getNumComments_upto($_REQUEST['view_comment_id']);
#       echo "commentOffset =$commentOffset= maxComments=$maxComments=\n";
        $comments_sort_mode = 'commentDate_asc';
        $comments_display_style = 'flat';
        $comments_at_top_of_page = 'y';
        $maxComments = 1;
        $currentPage = ceil( $commentOffset+1 / $maxComments );
        }
else {
        $commentOffset = ($currentPage - 1) * $maxComments;
        }


// $commentsParentId is the content_id which the comment tree is attached to
if( !@BitBase::verifyId( $commentsParentId ) ) {
	$comments = NULL;
	$numComments = 0;
} else {
	if( @BitBase::verifyId( $commentsParentIds ) ) {
		$parents = $commentsParentIds;
	} else {
		$parents = $commentsParentId;
	}
	$comments = $gComment->getComments( $parents, $maxComments, $commentOffset, $comments_sort_mode, $comments_display_style );
	$numComments = $gComment->getNumComments( $commentsParentId );
}

$gBitSmarty->assign_by_ref('comments', $comments);
$gBitSmarty->assign('maxComments', $maxComments);

$numCommentPages = ceil( $numComments / $maxComments );

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
$gBitSmarty->assign( 'commentsPgnHash', $commentsPgnHash );
$gBitSmarty->assign('postComment', $postComment);

$gBitSmarty->assign('currentTimestamp', time());
$gBitSmarty->assign('comments_return_url', $comments_return_url);
$gBitSmarty->assign('comments_at_top_of_page', ( isset( $comments_at_top_of_page ) && $gBitSystem->getConfig( 'comments_reorganise_page_layout', 'n' ) == 'y' ) ? $comments_at_top_of_page : NULL );
$gBitSmarty->assign('comments_style', $comments_display_style);
$gBitSmarty->assign('comments_sort_mode', $comments_sort_mode);
$gBitSmarty->assign('textarea_id', 'commentpost');
?>
