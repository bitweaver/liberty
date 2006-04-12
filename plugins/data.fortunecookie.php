<?php
global $gBitSystem;
if( $gBitSystem->isPackageActive( 'tidbits' ) ) {
	define( 'PLUGIN_GUID_DATACOOKIE', 'datacookie' );
	global $gLibertySystem;

	$pluginParams = array (
		'tag' => 'COOKIE',
		'auto_activate' => FALSE,
		'requires_pair' => FALSE,
		'title' => 'Fortune Cookies',
		'description' => tra( "Display a random sentence in the page." ),
		'help_page' => 'DataPluginFortuneCookie',
		'load_function' => 'data_cookie',
		//'help_function' => 'data_cookie_help',
		'syntax' => "{cookie}",
		'path' => LIBERTY_PKG_PATH.'plugins/data.cookie.php',
		//'security' => 'registered',
		'plugin_type' => DATA_PLUGIN
	);
	$gLibertySystem->registerPlugin( PLUGIN_GUID_DATACOOKIE, $pluginParams );
	$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATACOOKIE );

	function data_cookie( $pData, $pParams ) {
		global $taglinelib;
		require_once( TIDBITS_PKG_PATH.'BitFortuneCookies.php' );
		return( $taglinelib->pick_cookie() );
	}
}
?>
