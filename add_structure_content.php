<?php

$gLiteweightScan = TRUE;
require_once( '../bit_setup_inc.php' );

if( !empty( $_REQUEST['modal'] ) ) {
	$gBitSystem->mConfig['site_top_bar'] = FALSE;
	$gBitSystem->mConfig['site_left_column'] = FALSE;
	$gBitSystem->mConfig['site_right_column'] = FALSE;
	$gBitSmarty->assign( 'popupPage', '1' );
}

require_once( LIBERTY_PKG_PATH.'lookup_content_inc.php' );
require_once( LIBERTY_PKG_PATH.'edit_structure_inc.php' );

error_log( print_r( $_REQUEST, TRUE ) );

if( $gBitSystem->isAjaxRequest() ) {
	header( 'Content-Type: text/html; charset=utf-8' );
	print $gBitSmarty->fetch( "bitpackage:liberty/add_structure_feedback_inc.tpl" ); 
	exit;
} else {
	if( !$gBitSystem->loadAjax( 'mochikit', array( 'Iter.js', 'DOM.js', 'Format.js', 'Style.js', 'Signal.js', 'Logging.js', 'ThickBox.js' ) ) ) {
		// do something....
	}
	$gBitSystem->display( 'bitpackage:liberty/add_structure_content.tpl', "Add Content" );
}

?>
