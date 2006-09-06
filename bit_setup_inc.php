<?php
/**
 * base package include
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.8 $
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

	global $gLibertySystem;

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
