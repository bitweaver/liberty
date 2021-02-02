<?php
/**
 * $Header$
 * @version  $Revision$
 * @package  liberty
 * @subpackage functions
 */

/**
 * required setup
 */
global $gContent;
include_once( '../kernel/setup_inc.php' );

// load the content
include_once( LIBERTY_PKG_INCLUDE_PATH.'lookup_content_inc.php' );

// process the information
include_once( LIBERTY_PKG_INCLUDE_PATH.'edit_storage_inc.php' );

// fetch the content of the page to display
$gBitThemes->setFormatHeader( 'center_only' );

$gBitSmarty->assign( 'uploadTab', TRUE );

if( isset( $_REQUEST['form_id'] ) ){
	$gBitSmarty->assign( 'form_id', $_REQUEST['form_id'] );
}

$gBitSystem->display( 'bitpackage:liberty/edit_storage_list.tpl' , NULL, array( 'display_mode' => 'edit' ));
?>
