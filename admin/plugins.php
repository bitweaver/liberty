<?php
require_once( '../../kernel/includes/setup_inc.php' );
include_once( KERNEL_PKG_INCLUDE_PATH.'simple_form_functions_lib.php' );

$gBitSystem->verifyPermission( 'p_admin' );

// this will clear out all plugin settings in the database. scanAllPlugins 
// below will then reload all plugins and set them to their default setup.
if( !empty( $_REQUEST['reset_all_plugins'] ) ) {
	// because of include_once scenarios in scanAllPlugins, you need two complete page refreshes afternuking the plugin settings
	if( $_REQUEST['reset_all_plugins'] == 'refresh' ) {
		bit_redirect( LIBERTY_PKG_URL."admin/plugins.php" );
	} else {
		$gBitUser->verifyTicket();
		//$gBitSystem->storeConfig( 'default_format', PLUGIN_GUID_TIKIWIKI, LIBERTY_PKG_NAME );
		$gLibertySystem->resetAllPluginSettings();

		// this scanAllPlugins is required. who knows why this stuff is so resilient
		$gLibertySystem->scanAllPlugins();
		bit_redirect( LIBERTY_PKG_URL."admin/plugins.php?reset_all_plugins=refresh" );
	}
}

// Since the normal startup only loads the plugins marked active
// We need to load the rest of them here so that we can display them all
$gLibertySystem->scanAllPlugins();

// Plugin settings pages. A plugin declares
//   'plugin_settings_inc' => '<package>/admin/<path>_inc.php'
// and its settings live at admin/plugins.php?settings=<guid>. The file handles
// its form and sets $pluginSettingsTemplate (and $pluginSettingsTitle). It only
// saves when 'plugin_settings' is submitted, and that needs a valid ticket.
if( !empty( $_REQUEST['settings'] ) && is_string( $_REQUEST['settings'] )) {
	$settingsGuid = $_REQUEST['settings'];
	$settingsFile = NULL;
	if( !empty( $gLibertySystem->mPlugins[$settingsGuid]['plugin_settings_inc'] )
		&& preg_match( '#^([a-z0-9_]+)/(admin/(?:[a-z0-9_]+/)*[a-z0-9_]+_inc\.php)$#', $gLibertySystem->mPlugins[$settingsGuid]['plugin_settings_inc'], $incParts )
		&& defined( strtoupper( $incParts[1] ).'_PKG_PATH' )
	) {
		$pkgPath = constant( strtoupper( $incParts[1] ).'_PKG_PATH' );
		$adminPath = realpath( $pkgPath.'admin' );
		$incPath = realpath( $pkgPath.$incParts[2] );
		if( $adminPath && $incPath && strpos( $incPath, $adminPath.'/' ) === 0 ) {
			$settingsFile = $incPath;
		}
	}
	if( empty( $settingsFile )) {
		$gBitSystem->fatalError( tra( 'This plugin has no settings page.' ), NULL, NULL, 404 );
	}

	if( !empty( $_REQUEST['plugin_settings'] )) {
		$gBitUser->verifyTicket();
	}

	$pluginSettingsTemplate = NULL;
	$pluginSettingsTitle = $gLibertySystem->mPlugins[$settingsGuid]['title'] ?? tra( 'Plugin Settings' );
	include( $settingsFile );
	if( empty( $pluginSettingsTemplate )) {
		$gBitSystem->fatalError( tra( 'This plugin has no settings page.' ), NULL, NULL, 404 );
	}
	$gBitSmarty->assign( 'pluginSettingsGuid', $settingsGuid );
	$gBitSystem->display( $pluginSettingsTemplate, $pluginSettingsTitle, array( 'display_mode' => 'admin' ));
	exit;
}

if( isset( $_REQUEST['pluginsave'] ) && !empty( $_REQUEST['pluginsave'] ) ) {
	$gBitUser->verifyTicket();
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
	if( !empty( $plugin['plugin_settings_inc'] ) && empty( $plugin['plugin_settings_url'] )) {
		$gLibertySystem->mPlugins[$guid]['plugin_settings_url'] = LIBERTY_PKG_URL.'admin/plugins.php?settings='.urlencode( $guid );
	// since plugins can be in other packages, they can't use package constants. here we re-interpret URLs
	} elseif( !empty( $plugin['plugin_settings_url'] ) && strpos( $plugin['plugin_settings_url'], "/" ) !== 0 ) {
		$parts = explode( "/", $plugin['plugin_settings_url'] );
		$gLibertySystem->mPlugins[$guid]['plugin_settings_url'] = constant( strtoupper( $parts[0] )."_PKG_URL" ).str_replace( $parts[0]."/", "", $plugin['plugin_settings_url'] );
	}

	$types[ucfirst( $plugin['plugin_type'] )] = $plugin['plugin_type'];
	$typeSort[$guid] = $plugin['plugin_type'];
	$guidSort[$guid] = $plugin['plugin_guid'];
}
array_multisort( $typeSort, SORT_ASC, $guidSort, SORT_ASC, $gLibertySystem->mPlugins );
$gBitSmarty->assignByRef( 'gLibertySystem', $gLibertySystem );
ksort( $types );
$gBitSmarty->assignByRef( 'pluginTypes', $types );

$gBitSystem->display( 'bitpackage:liberty/admin_plugins.tpl', tra( 'Liberty Plugins' ), array( 'display_mode' => 'admin' ));
?>
