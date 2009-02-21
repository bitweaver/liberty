<?php
require_once( '../../../bit_setup_inc.php' );
require_once( KERNEL_PKG_PATH.'/simple_form_functions_lib.php' );
$gBitSystem->verifyPermission( 'p_admin' );

$feedback = array();

if( !empty( $_REQUEST['plugin_settings'] )) {
	simple_set_value( 'liberty_plugin_code_default_source', LIBERTY_PKG_NAME );
	$feedback['success'] = tra( 'The plugin was successfully updated' );
}

$gBitSmarty->assign( 'feedback', $feedback );
$gBitSystem->display( 'bitpackage:liberty/plugins/data_dbreport_admin.tpl', tra( 'Data DBReport Plugin Settings' ), array( 'display_mode' => 'admin' ));
?>
