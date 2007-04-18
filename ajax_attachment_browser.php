<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/ajax_attachment_browser.php,v 1.5 2007/04/18 20:52:39 nickpalmer Exp $
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
echo $gBitSmarty->fetch( 'bitpackage:liberty/attachment_browser.tpl' );
?>
