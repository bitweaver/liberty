<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_liberty/ajax_edit_storage.php,v 1.1 2007/10/31 15:47:58 squareing Exp $
 * @version  $Revision: 1.1 $
 * @package  liberty
 * @subpackage functions
 */
global $gContent;
include_once( '../bit_setup_inc.php' );

// load the content
include_once( LIBERTY_PKG_PATH.'lookup_content_inc.php' );

// process the information
include_once( LIBERTY_PKG_PATH.'edit_storage_inc.php' );

// make sure js is being loaded
$gBitThemes->loadAjax( 'mochikit', array( 'LibertyAttachments' ));
$gBitThemes->loadAjax( 'custom', array( LIBERTY_PKG_URL.'scripts/LibertyAttachments.js' ));

// fetch the content of the page to display
$gBitSystem->setFormatHeader( 'center_only' );
$gBitSystem->display( 'bitpackage:liberty/edit_storage_list.tpl' );
?>
