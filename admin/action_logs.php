<?php
require_once( '../../bit_setup_inc.php' );
include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

$gBitSystem->verifyPermission( 'p_admin' );

$gContent = new LibertyContent();

// logging options
$logSettings = array(
	'liberty_action_log' => array(
		'label' => 'Action Logs',
		'note'  => 'Log all changes made to liberty content.',
		'type'  => 'toggle',
	),
);
$gBitSmarty->assign( 'logSettings', $logSettings );

// form processing
if( !empty( $_REQUEST['apply_settings'] ) ) {
	$settings = array_merge( $logSettings );
	foreach( array_keys( $settings ) as $item ) {
		simple_set_toggle( $item, LIBERTY_PKG_NAME );
	}
	if( !empty( $_REQUEST['log_prune'] ) ) {
		$gContent->expungeActionLog( $_REQUEST['log_prune'] );
	}
}

// get list of log entries
$listHash = $_REQUEST;
// since this is all just text and we don't want to search through hundreds of pages
$listHash['max_records'] = $gBitSystem->getConfig( 'max_records', 10 ) * 5;
$actionLogs = $gContent->getActionLogs( $listHash );
$gBitSmarty->assign( 'listInfo', $listHash['listInfo'] );
$gBitSmarty->assign( 'actionLogs', $actionLogs );

$gBitSystem->display( 'bitpackage:liberty/action_logs.tpl', tra( 'Action Logs' ) );
?>
