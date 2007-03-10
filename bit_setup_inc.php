<?php
/**
 * base package include
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.12 $
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
	'content_edit_mini_tpl' => 'bitpackage:liberty/edit_content_inc.tpl',
	'content_load_sql_function' => 'liberty_content_load_sql',
	'content_list_sql_function' => 'liberty_content_list_sql',
	//	'content_store_function'  => 'liberty_content_store',
	//	'content_edit_function' => 'liberty_content_edit',
	'content_preview_function' => 'liberty_content_preview',
	//	'content_expunge_function'  => 'liberty_content_expunge',
) );

// load only the active plugins unless this is the first run after an install
$current_default_format_guid = $gBitSystem->getConfig( 'default_format' );
$plugin_status = $gBitSystem->getConfig( 'liberty_plugin_status_'.$current_default_format_guid );
if( empty( $current_default_format_guid ) || empty( $plugin_status ) || $plugin_status != 'y' ) {
	$gLibertySystem->scanAllPlugins();
} else {
	$gLibertySystem->loadActivePlugins();
}

$gBitSmarty->assign_by_ref( 'gLibertySystem', $gLibertySystem );
?>
