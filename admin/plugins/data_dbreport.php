<?php
require_once( '../../../bit_setup_inc.php' );
require_once( KERNEL_PKG_PATH.'/simple_form_functions_lib.php' );
$gBitSystem->verifyPermission( 'p_admin' );

$formEnable = array(
	'dbreport_direct' => array(
		'label' => 'Allow Direct DSN Entries',
		'note' => 'Allow direct input of DSN connection data to allow remote report generation.',
		'page' => 'DBReportDirect',
	),
	'dbreport_manage' => array(
		'label' => 'Allow Managed DB Entries',
		'note' => 'Allow DSN information from the managed list of tables and querirs.',
		'page' => 'DBReportManage',
	),
);
$gBitSmarty->assign( 'formEnable',$formEnable );

$formStyle = array(
	'dbreport_group' => array(
		'label' => 'Allow Group entries',
		'note' => 'Allow generation of group footers.',
		'page' => 'DBReportGroup',
	),
	'dbreport_total' => array(
		'label' => 'Allow Total Entries',
		'note' => 'Allow generation of total footer.',
		'page' => 'DBReportTotal',
	),
);
$gBitSmarty->assign( 'formStyle',$formStyle );
$feedback = array();

if( !empty( $_REQUEST['change_prefs'] ) ) {
	$featureToggles = array_merge( $formEnable, $formStyle );
	foreach( $featureToggles as $item => $info ) {
		if( empty( $info['type'] ) || $info['type'] == 'checkbox' ) {
			simple_set_toggle( $item, KERNEL_PKG_NAME );
		} elseif( $info['type'] == 'text' ) {
			simple_set_value( $item, KERNEL_PKG_NAME );
		}
	}
}

$gBitSmarty->assign( 'feedback', $feedback );
$gBitSystem->display( 'bitpackage:liberty/plugins/data_dbreport_admin.tpl', tra( 'Data DBReport Plugin Settings' ), array( 'display_mode' => 'admin' ));
?>
