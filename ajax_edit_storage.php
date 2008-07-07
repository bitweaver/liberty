<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_liberty/ajax_edit_storage.php,v 1.6 2008/07/07 06:08:37 squareing Exp $
 * @version  $Revision: 1.6 $
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
$gBitThemes->setFormatHeader( 'center_only' );
$gBitSystem->display( 'bitpackage:liberty/edit_storage_list.tpl' , NULL, array( 'display_mode' => 'edit' ));
?>
