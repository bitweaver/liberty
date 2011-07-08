<?php
/**
 * comment_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision$
 * @package  liberty
 * @subpackage functions
 */

// $Header$

// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See below for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.

// This file sets up the information needed to display
// the comments preferences, post-comment box and the
// list of comments. Finally it displays blog-comments.tpl
// using this information

// Setup URLS for the Comments next and prev buttons and use variables that
// cannot be aliased by normal Bit variables.
// Traverse each _REQUEST data adn put them in an array

// this script may only be included - so its better to die if called directly.


/**
 * Parameters that need to be set when calling this file
 * @param numeric $commentsParentId    The content id of the object where a new comment will be attached                                  (required)
 * @param array   $commentsParentIds   The list of content id of object the comments will be displayed - if not defined $commentsParentId (required if $commentsParentId is not set)
 * @param string  $comments_return_url The URL the user should be sent to after posting the comment                                       (required)
**/


/**
 * required setup
 */
require_once( LIBERTY_PKG_PATH.'LibertyComment.php' );

global $commentsLib, $gBitSmarty, $gBitSystem;

$postComment = array();
$formfeedback = array( 'error' => array() );
$gBitSmarty->assign_by_ref( 'formfeedback', $formfeedback );

// make sure that we don't feed ajax comments if we don't have javascript enabled
if( !BitThemes::isJavascriptEnabled() ) {
	$gBitSystem->setConfig( 'comments_ajax', 'n' );
}

if( $gBitSystem->isFeatureActive( 'comments_ajax' ) && !empty( $gContent ) && is_object( $gContent ) && $gContent->isCommentable() ) {
	$gBitSmarty->assign( 'comments_ajax', TRUE );
	$gBitThemes->loadAjax( 'mochikit', array( 'Iter.js', 'DOM.js', 'Style.js', 'Color.js', 'Position.js', 'Visual.js' ));
}

if( @BitBase::verifyId( $_REQUEST['delete_comment_id'] )) {
	$deleteComment = new LibertyComment($_REQUEST['delete_comment_id']);
	// make sure we're loaded up before we delete
	$deleteComment->loadComment();
	if( $deleteComment->isValid() && $gContent->hasUserPermission( 'p_liberty_admin_comments' )) {
		// delete entire thread
		$deleteComment->expunge();
	}
}

if( @BitBase::verifyId( $_REQUEST['post_comment_id'] ) && $gContent->hasUserPermission( 'p_liberty_post_comments' )) {
	$post_comment_id = $_REQUEST['post_comment_id'];
	$editComment = new LibertyComment( $post_comment_id );
	//if we are passed a comment id but not going to store it then turn off ajax
	if( !isset( $_REQUEST['post_comment_submit'] ) && !isset( $_REQUEST['post_comment_cancel'] )){
		//even if ajax is on - we force it off in this case
		$gBitSmarty->assign( 'comments_ajax', FALSE );
	}

	if( $editComment->mInfo['content_id'] ) {
		if( $editComment->userCanUpdate( $gContent )) {
			$postComment['data'] = $editComment->mInfo['data'];
			$postComment['title'] = $editComment->mInfo['title'];
		} else {
			$formfeedback['error'] = "You do not have permission to edit this comment.";
			$editComment = NULL;
			$post_comment_id = NULL;
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
if( !empty( $_REQUEST['post_comment_submit'] ) && $gContent->hasUserPermission( 'p_liberty_post_comments' )) {

	// check for !anon_post before logging in (auto-fill can hork things up)
	if( empty( $_REQUEST['anon_post'] ) && !empty( $_REQUEST['login_email'] ) && !empty( $_REQUEST['login_password'] ) ) {
		$gBitUser->login( $_REQUEST['login_email'], $_REQUEST['login_password'] );
		if( !empty( $gBitUser->mErrors['login'] ) ) {
			$formfeedback['error'][] = $gBitUser->mErrors['login']; 
		}
	} else {
		if( !empty($_REQUEST['comment_name'] )) {
			$_REQUEST['anon_name'] = $_REQUEST['comment_name'];
		}
	}
	
	// this commentsParentId is some crazy ass business - lets prepare for the day when this can be removed
	// there are references to it in LibertyComments::verifyComments as well
	$_REQUEST['comments_parent_id'] = $commentsParentId;

	$storeComment = new LibertyComment( @BitBase::verifyId( $editComment->mCommentId ) ? $editComment->mCommentId : NULL );

	if( empty( $formfeedback['error'] ) && $storeComment->storeComment( $_REQUEST )) {
		// store successful
		$storeComment->loadComment();
		if( empty( $_REQUEST['post_comment_id'] ) && $gBitSystem->isPackageActive( 'switchboard' ) ) {
			// A new comment, and we have switchboard to send notifications
			global $gSwitchboardSystem;
			// Draft the message:
			$message['subject'] = tra( 'New comment on:' ).' '.$gContent->getTitle().' @ '.$gBitSystem->getConfig( 'site_title' );
			$message['message'] = tra('A new message was posted to ').' '.$gContent->getTitle()."<br/>\n".$gContent->getDisplayUri()."<br/>\n"
					.'/----- '.tra('Here is the message')." -----/<br/>\n<br/>\n".'<h2>'.$storeComment->getTitle()."</h2>\n".tra('By').' '.$gBitUser->getDisplayName()."\n<p>".$storeComment->parseData().'</p>';
			$gSwitchboardSystem->sendEvent('My Content', 'new comment', $gContent->mContentId, $message );
		}
		$postComment = NULL;
	} else {
		// store fails handle errors and preview
		$formfeedback['error']=array_merge( $formfeedback['error'], $storeComment->mErrors );
		$postComment['data'] = !empty( $_REQUEST['comment_data'] ) ? $_REQUEST['comment_data'] : '';
		$postComment['title'] = !empty( $_REQUEST['comment_title'] ) ? $_REQUEST['comment_title'] : '';
		if( !empty( $_REQUEST['comment_name'] ) ) {
			$postComment['anon_name'] = $_REQUEST['comment_name'];
		}

		$_REQUEST['post_comment_request'] = TRUE;
		//this is critical and triggers other settings if store fails - do not remove without looking at what preview effects
		$_REQUEST['post_comment_preview'] = TRUE;
	}
} elseif(!empty($_REQUEST['post_comment_request']) && !$gContent->hasUserPermission( 'p_liberty_post_comments' )) {
	$formfeedback['warning']="You don't have permission to post comments.";
}

// $post_comment_request is a flag indicating whether or not to display the comment input form
if( empty( $_REQUEST['post_comment_request'] ) && !$gBitSystem->isFeatureActive( 'comments_auto_show_form' ) ) {
	$post_comment_request = NULL;
} elseif( $gContent->hasUserPermission( 'p_liberty_post_comments' ) ) {
	$post_comment_request = TRUE;
	// force off ajax attachments which does not work for comments attachments
	if( $gBitSystem->isFeatureActive( 'comments_allow_attachments' ) && $gBitSystem->getConfig( 'liberty_attachment_style') == 'ajax' ){
		$gBitSystem->setConfig( 'liberty_attachment_style', 'standard' );
	}
}

// in anticipation of mainlining LCConfig package - enable comment format configuration
// hack because comments does not have edit service -wjames5
if( $gBitSystem->isPackageActive( 'lcconfig' ) ){
	$spoofHash = array();
	lcconfig_content_edit( new LibertyComment(), $spoofHash  );
}

if( !empty( $_REQUEST['post_comment_request'] ) && $_REQUEST['post_comment_request'] == 'y' && !$gContent->hasUserPermission( 'p_liberty_post_comments' ) ) {
	$gBitSystem->fatalPermission( 'p_liberty_post_comments' );
}
$gBitSmarty->assign_by_ref('post_comment_request', $post_comment_request);

if( !empty( $_REQUEST['post_comment_cancel'] ) ) {
	$postComment = NULL;
}

// $post_comment_preview is a flag indicating that the user wants to preview their comment prior to saving it
if( !empty( $_REQUEST['post_comment_preview'] )) {
	if( isset( $_REQUEST['no_js_preview'] ) && $_REQUEST['no_js_preview']=="y" ) {
		$no_js_preview = $_REQUEST['no_js_preview'];

		//even if ajax is on - we force it off in this case
		$gBitSmarty->assign( 'comments_ajax', FALSE );
	} else {
		$no_js_preview = "n";
	}

	$gBitSmarty->assign_by_ref( 'no_js_preview', $no_js_preview );

	$postComment['user_id'] = $gBitUser->mUserId;
	$postComment['title'] = $_REQUEST['comment_title'];
	if( !empty( $_REQUEST['comment_name'] )) {
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
if( @BitBase::verifyId( $_REQUEST['post_comment_reply_id'] )) {
	$post_comment_reply_id = $_REQUEST['post_comment_reply_id'];
	$tmpComment = new LibertyComment( NULL, $post_comment_reply_id );
	if( !empty( $_REQUEST['quote'] )) {
		$postComment['data'] = $tmpComment->getQuoted();
	}
	if( preg_match( '/^' . tra( 'Re:' ) . '/', $tmpComment->mInfo['title'] )) {
		$comment_prefix = '';
	} else {
		$comment_prefix = tra( 'Re:' ) . " ";
	}

	//this always overrides the title with "Re: Parent Title" -- not sure what it really should do so I put in this conditional for previews
	if( !isset( $_REQUEST['comment_title'] )) {
		$postComment['title'] = $comment_prefix.$tmpComment->mInfo['title'];
	}

	$gBitSmarty->assign( 'post_comment_reply_id', $post_comment_reply_id );
}

if( $gContent->hasUserPermission( 'p_liberty_read_comments' )) {

	if( !empty( $_SESSION['liberty_comments_per_page'] )) {
		$maxComments = $_SESSION['liberty_comments_per_page'];
	} else {
		$maxComments = $gBitSystem->getConfig( 'comments_per_page', 10 );
	}

	if( !empty( $_REQUEST["comments_maxComments"] )) {
		$maxComments = $_REQUEST["comments_maxComments"];
		$comments_at_top_of_page = 'y';
		$_SESSION['liberty_comments_per_page'] = $maxComments;
	}

	if( !empty( $_SESSION['liberty_comments_ordering'] )) {
		$comments_sort_mode = $_SESSION['liberty_comments_ordering'];
	} else {
		$comments_sort_mode = $gBitSystem->getConfig( 'comments_default_ordering', 'commentDate_desc' );
	}

	if( !empty( $_REQUEST["comments_sort_mode"] )) {
		$comments_sort_mode = $_REQUEST["comments_sort_mode"];
		$comments_at_top_of_page = 'y';
		$_SESSION['liberty_comments_ordering'] = $comments_sort_mode;
	}

	if( !empty( $_SESSION['liberty_comments_display_mode'] )) {
		$comments_display_style = $_SESSION['liberty_comments_display_mode'];
	} else {
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

	if( empty( $gComment )) {
		$gComment = new LibertyComment();
	}

	$currentPage = !empty( $_REQUEST['comment_page'] ) ? $_REQUEST['comment_page'] : 1;
	if( $currentPage < 1 ) {
		$currentPage = 1;
	}

	# logic to support displaying a single comment -- used when we need a URL pointing to a comment
	if( !empty( $_REQUEST['view_comment_id'] )) {
		$commentOffset = $gComment->getNumComments_upto( $_REQUEST['view_comment_id'] );
#       echo "commentOffset =$commentOffset= maxComments=$maxComments=\n";
		$comments_sort_mode = 'commentDate_asc';
		$comments_display_style = 'flat';
		$comments_at_top_of_page = 'y';
		$maxComments = 1;
		$currentPage = ceil( $commentOffset + 1 / $maxComments );
	} else {
		$commentOffset = ( $currentPage - 1 ) * $maxComments;
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
		// pass in a reference to the root object so that we can do proper permissions checks
		if ( is_object( $gContent )) {
			$gComment->mRootObj = $gContent;
		}
		$numComments = $gComment->getNumComments( $commentsParentId );
		if ($commentOffset > $numComments) {
			$commentOffset = $numComments / $maxComments;
			$currentPage = ceil( $commentOffset+1 / $maxComments );
		}
		$comments = $gComment->getComments( $parents, $maxComments, $commentOffset, $comments_sort_mode, $comments_display_style );
	}

	if( $comments_display_style == 'flat' ) {
		$commentsTree = $comments;
	} else {
		$commentsTree = array();
		foreach( $comments as $id => $node ){
			if( !empty( $comments[ $node['parent_id'] ] )) {
				$comments[ $node['parent_id'] ]['children'][$id] = &$comments[$id];
			}
			if( $node['parent_id'] == $node['root_id'] || empty( $comments[ $node['parent_id'] ] )) {
				$comments[$id]['level'] = 0;
				$commentsTree[$id] = &$comments[$id];
			}
		}
	}

	$gBitSmarty->assign_by_ref( 'comments', $commentsTree );
	$gBitSmarty->assign( 'maxComments', $maxComments );

	$numCommentPages = ceil( $numComments / $maxComments );
	$comments_return_url = $comments_return_url.( !strpos( $comments_return_url, '?' ) ? '?' : '' );

	// libertypagination smarty function setup
	$commentsPgnHash = array(
			'numPages'      => $numCommentPages,
			'pgnName'       => 'comment_page',
			'page'          => $currentPage,
			'comment_page'  => $currentPage,
			'url'           => $comments_return_url,
			'comments_page' => ( empty( $comments_on_separate_page ) ? FALSE : $comments_on_separate_page ),
			'ianchor'       => 'editcomments',
			);
	$gBitSmarty->assign_by_ref( 'commentsPgnHash', $commentsPgnHash );
	$gBitSmarty->assign_by_ref( 'postComment', $postComment );
	$gBitSmarty->assign_by_ref( 'gComment', $gComment );

	$gBitSmarty->assign( 'currentTimestamp', time() );
	$gBitSmarty->assign( 'comments_return_url', $comments_return_url );
	$gBitSmarty->assign( 'comments_at_top_of_page', ( isset( $comments_at_top_of_page ) && $gBitSystem->getConfig( 'comments_reorganise_page_layout', 'n' ) == 'y' ) ? $comments_at_top_of_page : NULL );
	$gBitSmarty->assign( 'comments_style', $comments_display_style );
	$gBitSmarty->assign( 'comments_sort_mode', $comments_sort_mode );
	$gBitSmarty->assign( 'textarea_id', 'commentpost' );
	$gBitSmarty->assign( 'comments_count', $numComments );

	// @TODO get this shit out of here - boards and any other package ridding on comments should make use of services
	if( $gBitSystem->isPackageActive( 'boards' )) {
		require_once(BOARDS_PKG_PATH.'BitBoardTopic.php');
	}

	// @TODO get this shit out of here - boards and any other package ridding on comments should make use of services
	// this clearly can go in an edit service, but need to be careful since comments currently does not call edit service - have to check what doing so might trigger.
	if( !empty( $_REQUEST['post_comment_request'] )) {
		if( $gBitSystem->isPackageActive( 'boards' )
				&& (
					BitBoardTopic::isLockedMsg( @BitBase::verifyId( $storeComment->mInfo['parent_id'] )
						? $storeComment->mInfo['parent_id'] : ( !@BitBase::verifyId( $_REQUEST['post_comment_reply_id'] )
							? $commentsParentId : $_REQUEST['post_comment_reply_id'] ))
				   )
		  ) {
			unset( $_REQUEST['post_comment_request'] );
			unset( $_GET['post_comment_request'] );
			unset( $_POST['post_comment_request'] );
			$formfeedback['warning']="The selected Topic is Locked posting is disabled";
		}
	}

}
