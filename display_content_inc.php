<?php
/**
 * display_content_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision$
 * @package  liberty
 * @subpackage functions
 */

	global $gBitSmarty, $gBitSystem, $gContent;

	$gBitSmarty->assign_by_ref( 'pageInfo', $gContent->mInfo );

	$gBitSystem->display( 'bitpackage:liberty/display_content.tpl' , NULL, array( 'display_mode' => 'display' ));

?>
