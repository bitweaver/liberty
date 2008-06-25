<?php
/**
 * display_content_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.5 $
 * @package  liberty
 * @subpackage functions
 */

	global $gBitSmarty, $gBitSystem, $gContent;

//	vd( $gContent->mInfo );
	$gBitSmarty->assign_by_ref( 'pageInfo', $gContent->mInfo );

	$gBitSystem->display( 'bitpackage:liberty/display_content.tpl' , NULL, array( 'display_mode' => 'display' ));

?>
