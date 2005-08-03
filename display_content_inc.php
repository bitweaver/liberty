<?php
/**
 * display_content_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.1.1.1.2.3 $
 * @package  liberty
 * @subpackage functions
 */

	global $gBitSmarty, $gBitSystem, $gContent;

//	vd( $gContent->mInfo );
	$gBitSmarty->assign_by_ref( 'pageInfo', $gContent->mInfo );

	$gBitSystem->display( 'bitpackage:liberty/display_content.tpl' );

?>
