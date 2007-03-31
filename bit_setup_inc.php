<?php
/**
 * base package include
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.15 $
 * @package  liberty
 * @subpackage functions
 */

$registerHash = array(
	'package_name' => 'liberty',
	'package_path' => dirname( __FILE__ ).'/',
	'required_package'=> TRUE,
);
$gBitSystem->registerPackage( $registerHash );

require_once( LIBERTY_PKG_PATH.'LibertySystem.php' );

$gLibertySystem->registerService( 'liberty', LIBERTY_PKG_NAME, array(
	'content_edit_mini_tpl'      => 'bitpackage:liberty/service_content_edit_mini_inc.tpl',
	'content_edit_tab_tpl'       => 'bitpackage:liberty/service_content_edit_tab_inc.tpl',
	'content_icon_tpl'           => 'bitpackage:liberty/service_content_icon_inc.tpl',
	'content_display_function'   => 'liberty_content_display',
	'content_load_function'      => 'liberty_content_load',
	//'content_edit_function'      => 'liberty_content_edit',
	//'content_store_function'     => 'liberty_content_store',
	'content_load_sql_function'  => 'liberty_content_load_sql',
	'content_list_sql_function'  => 'liberty_content_list_sql',
	'content_preview_function'   => 'liberty_content_preview',
));

// load only the active plugins unless this is the first run after an install
$current_default_format_guid = $gBitSystem->getConfig( 'default_format' );
$plugin_status = $gBitSystem->getConfig( 'liberty_plugin_status_'.$current_default_format_guid );
if( empty( $current_default_format_guid ) || empty( $plugin_status ) || $plugin_status != 'y' ) {
	$gLibertySystem->scanAllPlugins();
} else {
	$gLibertySystem->loadActivePlugins();
}
$gBitSmarty->assign_by_ref( 'gLibertySystem', $gLibertySystem );

// delete cache file if requested
if( @BitBase::verifyId( $_REQUEST['refresh_liberty_cache'] )) {
	LibertyContent::expungeCacheFile( $_REQUEST['refresh_liberty_cache'] );
}
?>
