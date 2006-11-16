<?php
/**
 * $Id: edit_help_inc.php,v 1.11 2006/11/16 16:47:00 squareing Exp $
 * edit_help_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.11 $
 * @package  liberty
 * @subpackage functions
 */

global $gLibertySystem;
$inEditor = TRUE; // Required by PluginHelp to Determin Executed in an Editor

$dataplugins = array();
// Request help string from each plugin module
foreach( array_keys( $gLibertySystem->mPlugins ) as $pluginGuid ) {
	$pinfo = array(); // to make sure it's empty
	if( $gLibertySystem->mPlugins[$pluginGuid]['is_active'] == 'y') {
		if( $gLibertySystem->mPlugins[$pluginGuid]['plugin_type'] == FORMAT_PLUGIN ) {
			$formatplugins[]      = $gLibertySystem->mPlugins[$pluginGuid];
		}
	}

	if( ( $gLibertySystem->mPlugins[$pluginGuid]['plugin_type'] == DATA_PLUGIN ) && ( $gLibertySystem->mPlugins[$pluginGuid]['is_active'] == 'y' ) ) {
		if( isset( $gLibertySystem->mPlugins[$pluginGuid]['description'] )) {
			$pinfo                = $gLibertySystem->mPlugins[$pluginGuid];
			$pinfo["plugin_guid"] = preg_replace( "/^data/", "", $pluginGuid );
			$pinfo["exthelp"]     = !empty( $pinfo['help_function'] ) && function_exists( $pinfo['help_function'] ) ? $pinfo['help_function']() : '';
			$dataplugins[]        = $pinfo;
		}
	}
}

if( !empty( $dataplugins ) ) {
	asort( $formatplugins );
	$gBitSmarty->assign_by_ref( 'formatplugins', $formatplugins );
	asort( $dataplugins );
	$gBitSmarty->assign_by_ref( 'dataplugins', $dataplugins );
}
?>
