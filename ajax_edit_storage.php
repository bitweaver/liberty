<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_liberty/ajax_edit_storage.php,v 1.5 2008/06/25 22:21:12 spiderr Exp $
 * @version  $Revision: 1.5 $
 * @package  liberty
 * @subpackage functions
 */

/**
 * required setup
 */
global $gContent;
include_once( '../bit_setup_inc.php' );

// load the content
include_once( LIBERTY_PKG_PATH.'lookup_content_inc.php' );

// process the information
include_once( LIBERTY_PKG_PATH.'edit_storage_inc.php' );

// fetch the content of the page to display
$gBitSystem->setFormatHeader( 'center_only' );
$gBitSystem->display( 'bitpackage:liberty/edit_storage_list.tpl' , NULL, array( 'display_mode' => 'edit' ));
?>
