<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/ajax_attachment_browser.php,v 1.4 2007/04/17 14:18:21 wjames5 Exp $
 * @package liberty
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
include_once( LIBERTY_PKG_PATH.'attachment_browser.php' );
$gBitSmarty->assign('attachmentBrowser', true);
echo $gBitSmarty->fetch( 'bitpackage:liberty/attachment_browser.tpl' );
?>
