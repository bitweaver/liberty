<?php
require_once( '../../bit_setup_inc.php' );
include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

$gBitSystem->verifyPermission( 'p_admin' );

// this will clear out all plugin settings in the database. scanAllPlugins 
// below will then reload all plugins and set them to their default setup.
if( !empty( $_REQUEST['reset_all_plugins'] ) ) {
	$gBitUser->verifyTicket();
	//$gBitSystem->storeConfig( 'default_format', PLUGIN_GUID_TIKIWIKI, LIBERTY_PKG_NAME );
	$gLibertySystem->resetAllPluginSettings();

	// this scanAllPlugins is required. who knows why this stuff is so resilient
	$gLibertySystem->scanAllPlugins();
	bit_redirect( LIBERTY_PKG_URL."admin/plugins.php" );
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
	$gBitSystem->storeConfig( 'content_allow_html', !empty( $_REQUEST['content_allow_html'] ) ? $_REQUEST['content_allow_html'] : NULL, LIBERTY_PKG_NAME );
	$gBitSystem->storeConfig( 'content_force_allow_html', !empty( $_REQUEST['content_force_allow_html'] ) ? $_REQUEST['content_force_allow_html'] : NULL, LIBERTY_PKG_NAME );
}


// Sort the plugins to avoild splitting tables
foreach( $gLibertySystem->mPlugins as $guid => $plugin ) {
// since plugins can be in other packages, they can't use package constants. here we re-interpret URLs
	if( !empty( $plugin['plugin_settings_url'] ) && strpos( $plugin['plugin_settings_url'], "/" ) !== 0 ) {
		$parts = explode( "/", $plugin['plugin_settings_url'] );
		$gLibertySystem->mPlugins[$guid]['plugin_settings_url'] = constant( strtoupper( $parts[0] )."_PKG_URL" ).str_replace( $parts[0]."/", "", $plugin['plugin_settings_url'] );
	}

	$types[ucfirst( $plugin['plugin_type'] )] = $plugin['plugin_type'];
	$typeSort[$guid] = $plugin['plugin_type'];
	$guidSort[$guid] = $plugin['plugin_guid'];
}
array_multisort( $typeSort, SORT_ASC, $guidSort, SORT_ASC, $gLibertySystem->mPlugins );
$gBitSmarty->assign_by_ref( 'gLibertySystem', $gLibertySystem );
ksort( $types );
$gBitSmarty->assign_by_ref( 'pluginTypes', $types );

$gBitSystem->display( 'bitpackage:liberty/admin_plugins.tpl', tra( 'Liberty Plugins' ), array( 'display_mode' => 'admin' ));
?>
