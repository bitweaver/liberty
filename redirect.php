<?php
/**
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/redirect.php,v 1.5 2010/02/08 21:27:23 wjames5 Exp $
 * lookup_content_inc
 *
 * @author   spider <spider@steelsun.com>
 * @package  liberty
 * @subpackage functions
 */

/**
 * Required setup
 */
require_once( '../kernel/setup_inc.php' );
if( !empty( $_REQUEST['q'] )) {
	bit_redirect( $_REQUEST['q'] );
} else {
	$_REQUEST['error'] = tra( 'The redirect did not include a url.' );
	include( KERNEL_PKG_PATH . 'error.php' );
}
?>
