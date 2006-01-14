<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/ajax_attachment_browser.php,v 1.3 2006/01/14 19:54:56 squareing Exp $
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
