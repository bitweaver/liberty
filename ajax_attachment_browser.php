<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/ajax_attachment_browser.php,v 1.1.2.2 2006/01/11 12:20:51 lsces Exp $
 * @package liberty
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
include_once( LIBERTY_PKG_PATH.'attachment_browser.php' );
echo $gBitSmarty->fetch( 'bitpackage:liberty/attachment_browser.tpl' );
?>
