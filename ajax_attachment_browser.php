<?php
/**
 * @version $Header$
 * @package liberty
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/includes/setup_inc.php' );
global $gContent, $gBitSmarty;

$gContent = new LibertyMime();
if( isset( $_REQUEST['content_id'] )) {
	$gContent->mContentId = $_REQUEST['content_id'];
}
$gBitSmarty->assign( 'attachmentBrowser', TRUE );
include_once( LIBERTY_PKG_PATH.'attachment_browser.php' );

if( isset( $_REQUEST['json'] )){
	header( 'Content-type:application/json' );
	$gBitSmarty->display( 'bitpackage:liberty/attachment_browser_json.tpl' );
} else {
	echo $gBitSmarty->fetch( 'bitpackage:liberty/attachment_browser.tpl' );
}
?>
