<?php
/**
 * comment_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.40 $
 * @package  liberty
 * @subpackage functions
 */

// $Header: /cvsroot/bitweaver/_bit_liberty/comments_inc.php,v 1.40 2007/10/25 08:53:41 jht001 Exp $

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

global $commentsLib, $gBitSmarty, $gBitSystem;

if ($gBitSystem->isPackageActive('bitboards')) {
	require_once(BITBOARDS_PKG_PATH.'BitBoardTopic.php');
}

$postComment = array();
$formfeedback = array();
$gBitSmarty->assign_by_ref('formfeedback', $formfeedback);

// make sure that we don't feed ajax comments if we don't have javascript enabled
if( !BitThemes::isJavascriptEnabled() ) {
	$gBitSystem->setConfig( 'comments_ajax', 'n' );
}

if( $gBitSystem->isFeatureActive( 'comments_ajax' ) && !empty( $gContent ) && is_object( $gContent ) && $gContent->isCommentable() ) {
	$gBitSmarty->assign( 'comments_ajax', TRUE );
	$gBitThemes->loadAjax( 'mochikit', array( 'Iter.js', 'DOM.js', 'Style.js', 'Color.js', 'Position.js', 'Visual.js' ));
}

if( @BitBase::verifyId($_REQUEST['delete_comment_id']) ) {
	$deleteComment = new LibertyComment($_REQUEST['delete_comment_id']);
	if( $deleteComment->isValid() && $gBitUser->hasPermission('p_liberty_admin_comments') ) {
		$deleteComment->deleteComment();
	}
}

if( @BitBase::verifyId($_REQUEST['post_comment_id']) && $gBitUser->hasPermission( 'p_liberty_post_comments' )) {
	$post_comment_id = $_REQUEST['post_comment_id'];
	$editComment = new LibertyComment($post_comment_id);
	//if we are passed a comment id but not going to store it then turn off ajax
	if (!isset($_REQUEST['post_comment_submit']) && !isset($_REQUEST['post_comment_cancel'])){
		$gBitSmarty->assign('comments_ajax', FALSE);  //even if ajax is on - we force it off in this case
	}
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
	if (empty($_REQUEST['post_comment_id'])&&$gBitSystem->isPackageActive('bitboards')) {
		$content_type = $gBitUser->getPreference('signiture_content_type');
		$content_data = $gBitUser->getPreference('signiture_content_data');
		if (!empty($content_type) && !empty($content_data)) {
			$storeRow['edit'] .= "\n{renderer format_guid=$content_type class=mb-signature}$content_data{/renderer}";
		}
	}
	$storeRow['root_id'] = $commentsParentId;
	$storeRow['parent_id'] = (@BitBase::verifyId($storeComment->mInfo['parent_id']) ? $storeComment->mInfo['parent_id'] : (!@BitBase::verifyId($_REQUEST['post_comment_reply_id']) ? $commentsParentId : $_REQUEST['post_comment_reply_id']));
	$storeRow['content_id'] = (@BitBase::verifyId($storeComment->mContentId) ? $storeComment->mContentId : NULL);
	if( !empty( $_REQUEST['captcha'] ) ) {
		$storeRow['captcha'] = $_REQUEST['captcha'];
	}
	if (!empty($_REQUEST['comment_name'])) {
		$storeRow['anon_name'] = $_REQUEST['comment_name'];
	}
	if (!empty( $_REQUEST['format_guid'] ) ) {
		$storeRow['format_guid'] = $_REQUEST['format_guid'];
	}
	if(!($gBitSystem->isPackageActive('bitboards') && BitBoardTopic::isLockedMsg($storeRow['parent_id']))) {
		if( $storeComment->storeComment($storeRow) ) {
			if($gBitSystem->isPackageActive('bitboards') && $gBitSystem->isFeatureActive('bitboards_thread_track')) {
				$topic_id = substr($storeComment->mInfo['thread_forward_sequence'],0,10);
				$data = BitBoardTopic::getNotificationData($topic_id);
				foreach ($data['users'] as $login => $user) {
					if($data['topic']->mInfo['llc_last_modified']>$user['track_date'] && $data['topic']->mInfo['llc_last_modified']>$user['track_notify_date']) {
						$data['topic']->sendNotification($user);
					}
				}
			}
			$postComment = NULL;
		} else {
			$formfeedback['error']=$storeComment->mErrors;
			$postComment['data'] = $_REQUEST['comment_data'];
			$postComment['title'] = $_REQUEST['comment_title'];
			if( !empty( $_REQUEST['comment_name'] ) ) {
				$postComment['anon_name'] = $_REQUEST['comment_name'];
			}
			$_REQUEST['post_comment_request'] = TRUE;
			$_REQUEST['post_comment_preview'] = TRUE;  //this is critical and triggers other settings if store fails - do not remove without looking at what preview effects
		}
	} else {
		$formfeedback['warning']="The selected Topic is Locked posting is disabled";
	}
} elseif(!empty($_REQUEST['post_comment_request']) && !$gBitUser->hasPermission( 'p_liberty_post_comments' )) {
	$formfeedback['warning']="You don't have p_liberty_post_comments";
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

if( !empty( $_REQUEST['post_comment_cancel'] ) ) {
	$postComment = NULL;
}

// $post_comment_preview is a flag indicating that the user wants to preview their comment prior to saving it
if( !empty( $_REQUEST['post_comment_preview'] ) ) {
	if ( isset($_REQUEST['no_js_preview']) && $_REQUEST['no_js_preview']=="y" ){
		$no_js_preview = $_REQUEST['no_js_preview'];
		$gBitSmarty->assign('comments_ajax', FALSE);  //even if ajax is on - we force it off in this case		
	}else{
		$no_js_preview = "n";
	}
	
	$gBitSmarty->assign_by_ref('no_js_preview', $no_js_preview);

	$postComment['user_id'] = $gBitUser->mUserId;
	$postComment['title'] = $_REQUEST['comment_title'];
	if (!empty($_REQUEST['comment_name'])) {
		$postComment['anon_name'] = $_REQUEST['comment_name'];
	}
	$postComment['data'] = $_REQUEST['comment_data'];
	$postComment['format_guid'] = empty( $_REQUEST['format_guid'])? $gBitSystem->getConfig( 'default_format' ) : $_REQUEST['format_guid'];
	$postComment['parsed_data'] = LibertyComment::parseData( $postComment );
	$postComment['created'] = time();
	$postComment['last_modified'] = time();
	$gBitSmarty->assign('post_comment_preview', TRUE);
}

// $post_comment_reply_id is the content_id which a post is replying to
if (@BitBase::verifyId($_REQUEST['post_comment_reply_id'])) {
	$post_comment_reply_id = $_REQUEST['post_comment_reply_id'];
	$tmpComment = new LibertyComment(NULL, $post_comment_reply_id);
	if (!empty($_REQUEST['quote'])) {
		$postComment['data'] = $tmpComment->getQuoted();
	}
	if (preg_match('/^' . tra('Re:') . '/', $tmpComment->mInfo['title'])) {
		$comment_prefix = '';
	}
	else {
		$comment_prefix = tra('Re:') . " ";
	}
	//this always overrides the title with "Re: Parent Title" -- not sure what it really should do so I put in this conditional for previews
	if (!isset($_REQUEST['comment_title'])){
		$postComment['title'] = $comment_prefix . $tmpComment->mInfo['title'];
	}

	$gBitSmarty->assign('post_comment_reply_id', $post_comment_reply_id);
}

if (!empty($_SESSION['liberty_comments_per_page'])) {
	$maxComments = $_SESSION['liberty_comments_per_page'];
	}
else {
	$maxComments = $gBitSystem->getConfig( 'comments_per_page', 10 );
	}
if (!empty($_REQUEST["comments_maxComments"])) {
	$maxComments = $_REQUEST["comments_maxComments"];
	$comments_at_top_of_page = 'y';
	$_SESSION['liberty_comments_per_page'] = $maxComments;
}


if (!empty($_SESSION['liberty_comments_ordering'])) {
	$comments_sort_mode = $_SESSION['liberty_comments_ordering'];
	}
else {
	$comments_sort_mode = $gBitSystem->getConfig( 'comments_default_ordering', 'commentDate_desc' );
	}
if (!empty($_REQUEST["comments_sort_mode"])) {
	$comments_sort_mode = $_REQUEST["comments_sort_mode"];
	$comments_at_top_of_page = 'y';
	$_SESSION['liberty_comments_ordering'] = $comments_sort_mode;
}

if (!empty($_SESSION['liberty_comments_display_mode'])) {
	$comments_display_style = $_SESSION['liberty_comments_display_mode'];
	}
else {	
	$comments_display_style = $gBitSystem->getConfig( 'comments_default_display_mode', 'threaded' );
	}
if( !empty( $_REQUEST["comments_style"] ) ) {
	$comments_display_style = $_REQUEST["comments_style"];
	$comments_at_top_of_page = 'y';
	$_SESSION['liberty_comments_display_mode'] = $comments_display_style;
}

if( !empty( $_REQUEST['comment_page'] ) || !empty( $_REQUEST['post_comment_request'] ) ) {
	$comments_at_top_of_page = 'y';
}
$commentOffset = !empty( $_REQUEST['comment_page'] ) ? ($_REQUEST['comment_page'] - 1) * $maxComments : 0;

if (empty($gComment)) {
	$gComment = new LibertyComment( NULL, $gContent->mContentId );
}

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
	$comments = array();
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

if ($comments_display_style == 'flat') {
	$commentsTree = $comments;
}
else {	
	$commentsTree = array();
	foreach ($comments as $id => $node){
		if (!empty( $comments[ $node['parent_id'] ])) {
			$comments[ $node['parent_id'] ]['children'][$id] = &$comments[$id];
		}
		if ($node['parent_id'] == $node['root_id'] or empty( $comments[ $node['parent_id'] ])){
			$comments[$id]['level'] = 0;	
			$commentsTree[$id] = &$comments[$id];
		}
	}
}

$gBitSmarty->assign_by_ref('comments', $commentsTree);
$gBitSmarty->assign('maxComments', $maxComments);

$numCommentPages = ceil( $numComments / $maxComments );

$commentsPgnHash = array(
	'numPages' => $numCommentPages,
	'pgnName' => 'comment_page',
	'page' => $currentPage,
	'comment_page' => $currentPage,
	'url' => $comments_return_url,
	'comments_maxComments' => $maxComments,
	'comments_sort_mode' => $comments_sort_mode,
	'comments_style' => $comments_display_style,
	'ianchor' => 'editcomments',
);
$gBitSmarty->assign_by_ref( 'commentsPgnHash', $commentsPgnHash );
$gBitSmarty->assign_by_ref('postComment', $postComment);
$gBitSmarty->assign_by_ref('gComment', $gComment);

$gBitSmarty->assign('currentTimestamp', time());
$gBitSmarty->assign('comments_return_url', $comments_return_url);
$gBitSmarty->assign('comments_at_top_of_page', ( isset( $comments_at_top_of_page ) && $gBitSystem->getConfig( 'comments_reorganise_page_layout', 'n' ) == 'y' ) ? $comments_at_top_of_page : NULL );
$gBitSmarty->assign('comments_style', $comments_display_style);
$gBitSmarty->assign('comments_sort_mode', $comments_sort_mode);
$gBitSmarty->assign('textarea_id', 'commentpost');

if (!empty($_REQUEST['post_comment_request'])) {
	if ($gBitSystem->isPackageActive('bitboards') && BitBoardTopic::isLockedMsg( (@BitBase::verifyId($storeComment->mInfo['parent_id']) ? $storeComment->mInfo['parent_id'] : (!@BitBase::verifyId($_REQUEST['post_comment_reply_id']) ? $commentsParentId : $_REQUEST['post_comment_reply_id'])))) {
		unset($_REQUEST['post_comment_request']);
		unset($_GET['post_comment_request']);
		unset($_POST['post_comment_request']);
		$formfeedback['warning']="The selected Topic is Locked posting is disabled";
	}
}

?>
