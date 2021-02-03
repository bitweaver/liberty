<?php
/**
 * @version  $Header$
 * lookup_content_inc
 *
 * @author   spider <spider@steelsun.com>
 * @package  liberty
 * @subpackage functions
 */

/**
 * Required setup
 */
require_once( '../kernel/includes/setup_inc.php' );
if( !empty( $_REQUEST['q'] )) {
	bit_redirect( $_REQUEST['q'] );
} else {
	$_REQUEST['error'] = tra( 'The redirect did not include a url.' );
	include( KERNEL_PKG_PATH . 'error.php' );
}
?>
