<?php
require_once( '../../bit_setup_inc.php' );
include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

$gBitSystem->verifyPermission( 'bit_p_admin' );

if( isset( $_REQUEST['pluginsave'] ) && !empty( $_REQUEST['pluginsave'] ) ) {
	if( !empty( $_REQUEST['default_format'] ) && !empty( $_REQUEST['PLUGINS'][$_REQUEST['default_format']][0] ) ) {
		$gLibertySystem->setActivePlugins( $_REQUEST['PLUGINS'] );
		$gBitSystem->storePreference( 'default_format',$_REQUEST['default_format'] );
		$gBitSmarty->assign( 'default_format',$_REQUEST['default_format'] );
	} else {
		$gBitSmarty->assign( 'errorMsg', 'You cannot disable the default format');
	}

	$formToggles = array( 'allow_html' );
	foreach( $formToggles as $item ) {
		simple_set_toggle( $item, LIBERTY_PKG_NAME );
	}
}

// Sort the plugins to avoild splitting tables
foreach( $gLibertySystem->mPlugins as $key => $row ) {
   $types[ucfirst( $row['plugin_type'] )]  = $row['plugin_type'];
   $type[$key]  = $row['plugin_type'];
   $guid[$key] = $row['plugin_guid'];
}
array_multisort( $type, SORT_ASC, $guid, SORT_ASC, $gLibertySystem->mPlugins );
$gBitSmarty->assign_by_ref( 'gLibertySystem', $gLibertySystem );
ksort( $types );
$gBitSmarty->assign_by_ref( 'pluginTypes', $types );

//vd( $gLibertySystem->mPlugins );

$gBitSystem->display( 'bitpackage:liberty/admin_plugins.tpl');
?>
