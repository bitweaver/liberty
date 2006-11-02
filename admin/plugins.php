<?php
require_once( '../../bit_setup_inc.php' );
include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

$gBitSystem->verifyPermission( 'p_admin' );

// Since the normal startup only loads the plugins marked active
// We need to load the rest of them here so that we can display them all
$gLibertySystem->scanAllPlugins();

if( isset( $_REQUEST['pluginsave'] ) && !empty( $_REQUEST['pluginsave'] ) ) {
	if( !empty( $_REQUEST['default_format'] ) && !empty( $_REQUEST['PLUGINS'][$_REQUEST['default_format']][0] ) ) {
		$gLibertySystem->setActivePlugins( $_REQUEST['PLUGINS'] );
		$gBitSystem->storeConfig( 'default_format', $_REQUEST['default_format'], LIBERTY_PKG_NAME );
		$gBitSmarty->assign( 'default_format',$_REQUEST['default_format'] );
	} else {
		$gBitSmarty->assign( 'errorMsg', 'You cannot disable the default format');
	}
	$gBitSystem->storeConfig( 'content_allow_html', !empty( $_REQUEST['content_allow_html'] ) );
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
