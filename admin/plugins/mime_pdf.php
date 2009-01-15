<?php
require_once( '../../../bit_setup_inc.php' );
include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

$gBitSystem->verifyPermission( 'p_admin' );

$feedback = array();

$pdfSettings = array(
	'pdf2swf_path' => array(
		'label'   => 'Path to pdf2swf',
		'note'    => 'Path to the pdf2swf executable.',
		'type'    => 'text',
	),
	'swfcombine_path' => array(
		'label'   => 'Path to swfcombine',
		'note'    => 'Path to the swfcombine executable.',
		'type'    => 'text',
	),
);

if( function_exists( 'shell_exec' )) {
	$pdfSettings['pdf2swf_path']['default']    =  shell_exec( 'which pdf2swf' );
	$pdfSettings['swfcombine_path']['default'] =  shell_exec( 'which swfcombine' );
} else {
	$feedback['error'] = "You can not execute binaries on your server. You can not make use of this plugin.";
}

$gBitSmarty->assign( 'pdfSettings', $pdfSettings );

if( !empty( $_REQUEST['plugin_settings'] )) {
	foreach( $pdfSettings as $item => $data ) {
		if( $data['type'] == 'checkbox' ) {
			simple_set_toggle( $item, LIBERTY_PKG_NAME );
		} elseif( $data['type'] == 'numeric' ) {
			simple_set_int( $item, LIBERTY_PKG_NAME );
		} else {
			simple_set_value( $item, LIBERTY_PKG_NAME );
		}
	}

	$feedback['success'] = tra( 'The plugin was successfully updated' );
}

$gBitSmarty->assign( 'feedback', $feedback );
$gBitSystem->display( 'bitpackage:liberty/mime/pdf/admin.tpl', tra( 'PDF Plugin Settings' ), array( 'display_mode' => 'admin' ));
?>
