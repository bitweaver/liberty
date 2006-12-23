<?php
require_once( '../../bit_setup_inc.php' );
include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

$gBitSystem->verifyPermission( 'p_admin' );

// this will clear out all plugin settings in the database. scanAllPlugins 
// below will then reload all plugins and set them to their default setup.
if( !empty( $_REQUEST['reset_all_plugins'] ) ) {
	//$gBitSystem->storeConfig( 'default_format', PLUGIN_GUID_TIKIWIKI, LIBERTY_PKG_NAME );
	$gLibertySystem->resetAllPluginSettings();

	// this scanAllPlugins is required. who knows why this stuff is so resilient
	$gLibertySystem->scanAllPlugins();
	header( "Location: ".LIBERTY_PKG_URL."admin/plugins.php?another_reload=1" );
}

// this stuff is pretty insane. calling scanAllPlugins() multiple times
// won't do the trick. we actually need to reload the page twice!
// (perhaps someone else can find the reason for this and come up with a 
// cleaner solution)
if( !empty( $_REQUEST['another_reload'] ) ) {
	header( "Location: ".LIBERTY_PKG_URL."admin/plugins.php" );
}

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
	$gBitSystem->storeConfig( 'content_allow_html', !empty( $_REQUEST['content_allow_html'] ) ? $_REQUEST['content_allow_html'] : NULL );
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

$gBitSystem->display( 'bitpackage:liberty/admin_plugins.tpl');
?>
