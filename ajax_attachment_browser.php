<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/ajax_attachment_browser.php,v 1.7 2007/05/11 17:10:42 wjames5 Exp $
 * @package liberty
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
global $gContent, $gBitSmarty;

$gContent = new LibertyAttachable();
if (isset($_REQUEST['content_id'])) {
	$gContent->mContentId = $_REQUEST['content_id'];
}
$gBitSmarty->assign('attachmentBrowser', true);
include_once( LIBERTY_PKG_PATH.'attachment_browser.php' );

if (isset($_REQUEST['json'])){
	header('Content-type:application/json');
	$gBitSmarty->display( 'bitpackage:liberty/attachment_browser_json.tpl' );
}else{
	echo $gBitSmarty->fetch( 'bitpackage:liberty/attachment_browser.tpl' );
}
?>
