<?php
/**
 * base package include
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision$
 * @package  liberty
 * @subpackage functions
 */

$registerHash = array(
	'package_name' => 'liberty',
	'package_path' => dirname( dirname( __FILE__ ) ).'/',
	'required_package'=> TRUE,
);
$gBitSystem->registerPackage( $registerHash );

// initiate LibertySystem
require_once( LIBERTY_PKG_CLASS_PATH.'LibertySystem.php' );
LibertySystem::loadSingleton();
$gBitSmarty->assignByRef( 'gLibertySystem', $gLibertySystem );

// We can't load this in liberty/bit_setup_inc.php becuase it's too soon in the process.
// packages haven't been scanned yet making things like <pkg>_PKG_URL and similar
// unavailable to the plugins that are kept in <pkg>/liberty_plugins/
$current_default_format_guid = $gBitSystem->getConfig( 'default_format' );
if( $gLibertySystem->mDb->isValid() ) { // install condition check
	$plugin_status = $gBitSystem->getConfig( 'liberty_plugin_status_'.$current_default_format_guid );
	if( empty( $current_default_format_guid ) || empty( $plugin_status ) || $plugin_status != 'y' ) {
		$gLibertySystem->scanAllPlugins();
	} else {
		$gLibertySystem->loadActivePlugins();
	}
}

$gLibertySystem->registerService( 'liberty', 
	LIBERTY_PKG_NAME, 
	array(
		'content_edit_mini_tpl'      => 'bitpackage:liberty/service_content_edit_mini_inc.tpl',
		'content_edit_tab_tpl'       => 'bitpackage:liberty/service_content_edit_tab_inc.tpl',
		'content_icon_tpl'           => 'bitpackage:liberty/service_content_icon_inc.tpl',
		'content_body_tpl'           => 'bitpackage:liberty/service_content_body_inc.tpl',
		'content_display_function'   => 'liberty_content_display',
		//'content_load_function'      => 'liberty_content_load',
		'content_edit_function'      => 'liberty_content_edit',
		//'content_store_function'     => 'liberty_content_store',
		'content_load_sql_function'  => 'liberty_content_load_sql',
		'content_list_sql_function'  => 'liberty_content_list_sql',
		'content_preview_function'   => 'liberty_content_preview',
	),
	array( 
		'description' => tra( 'Provides core functionality, including enforcing some access control and dynamic layout components.' ),
		'required' => TRUE,
	)
);

// delete cache file if requested
if( !empty( $_REQUEST['refresh_liberty_cache'] ) && BitBase::verifyId( $_REQUEST['refresh_liberty_cache'] )) {
	require_once( LIBERTY_PKG_CLASS_PATH.'LibertyContent.php' );
	LibertyContent::expungeCacheFile( $_REQUEST['refresh_liberty_cache'] );
}

// make thumbnail sizes available to smarty
global $gThumbSizes;
$gBitSmarty->assignByRef( 'gThumbSizes', $gThumbSizes );
?>
