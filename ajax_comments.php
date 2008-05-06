<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/ajax_comments.php,v 1.8 2008/05/06 20:00:48 wjames5 Exp $
 * @package liberty
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
 
$staticContent = new LibertyContent();
$gContent = $staticContent->getLibertyObject( $_REQUEST['parent_id'], (!empty($_REQUEST['parent_guid'])?$_REQUEST['parent_guid']:NULL) );
$XMLContent = "";

if( !$gContent->hasUserPermission( 'p_liberty_post_comments', TRUE, TRUE)) {
	$statusCode = 401;
	$XMLContent = tra( "You do not have the required permissions to post new comments" );
} elseif( $gContent->isCommentable() ) {
	$commentsParentId = $_REQUEST['parent_id'];
	$comments_return_url = $_REQUEST['comments_return_url'];
	/* If we are receiving ajax comments request make sure our results also know 
	   we are using ajax comments. This is an insurance measure that if the originating content 
	   forced on ajax comments (even if off system wide) that the return results 
	   continue to use ajax comments. Don't take this out under penalty of death.
	*/
	$gBitSystem->setConfig( 'comments_ajax', 'y' );
	include_once( LIBERTY_PKG_PATH.'comments_inc.php' );
	if( isset( $_REQUEST['post_comment_submit'] )) {
		if ($storeComment->loadComment()){
			$statusCode = 200;
			$postComment = $storeComment->mInfo;
			$postComment['parsed_data'] = $storeComment->parseData( $postComment );
		}else{
			//if store is requested but it fails for some reason - like captcha mismatch
			$statusCode = 400;
		}
	}else{
		//we assume preview request which we return as ok - our js callback knows what to do when preview is requested
		$statusCode = 200;
	}
	$gBitSmarty->assign('comment', $postComment);
	$gBitSmarty->assign('commentsParentId', $commentsParentId);
	if( !empty($formfeedback) ){
		$statusCode = 400;
		require_once $gBitSmarty->_get_plugin_filepath( 'function', 'formfeedback' );
		$XMLContent = smarty_function_formfeedback( $formfeedback, $gBitSmarty );
	}
	$XMLContent .= $gBitSmarty->fetch( 'bitpackage:liberty/display_comment.tpl' );
} else {
	$statusCode = 405;
	$XMLContent = tra( "Sorry, you can not post a comment here." );
}

//We return XML with a status code
$mRet = "<req><status><code>".$statusCode."</code></status>"
	."<content><![CDATA[".$XMLContent."]]></content></req>";

//since we are returning xml we must report so in the header
//we also need to tell the browser not to cache the page
//see: http://mapki.com/index.php?title=Dynamic_XML
// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
// always modified
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
// HTTP/1.0
header("Pragma: no-cache");
//XML Header
header("content-type:text/xml");

print_r('<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>');
print_r($mRet);					
die;
?>
