<?php
/**
 * edit_help_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.1.2.4 $
 * @package  liberty
 * @subpackage functions
 */

global $gLibertySystem;

$dataplugins = array();
// Request help string from each plugin module
foreach( array_keys( $gLibertySystem->mPlugins ) as $pluginGuid ) {
	if( $gLibertySystem->mPlugins[$pluginGuid]['plugin_type'] == FORMAT_PLUGIN ) {
		if( isset( $gLibertySystem->mPlugins[$pluginGuid]['description'] ) ) {
			$pinfo["is_active"] = $gLibertySystem->mPlugins[$pluginGuid]['is_active'];
			$pinfo["guid"] = $pluginGuid;
			$pinfo["name"] = $gLibertySystem->mPlugins[$pluginGuid]['edit_label'];
			$pinfo["description"] = $gLibertySystem->mPlugins[$pluginGuid]['description'];
			$pinfo["help_page"] = ( !empty( $gLibertySystem->mPlugins[$pluginGuid]['help_page'] ) ? $gLibertySystem->mPlugins[$pluginGuid]['help_page'] : NULL );
			$formatplugins[] = $pinfo;
		}
	}

	if( $gLibertySystem->mPlugins[$pluginGuid]['plugin_type'] == DATA_PLUGIN ) {
		if( isset( $gLibertySystem->mPlugins[$pluginGuid]['description'] ) ) {
			$pinfo["is_active"] = $gLibertySystem->mPlugins[$pluginGuid]['is_active'];
			$pinfo["guid"] = preg_replace( "/^data/", "", $pluginGuid );
			$pinfo["name"] = !empty( $gLibertySystem->mPlugins[$pluginGuid]['title'] ) ? $gLibertySystem->mPlugins[$pluginGuid]['title'] : $pluginGuid;
			$pinfo["description"] = $gLibertySystem->mPlugins[$pluginGuid]['description'];
			$pinfo["help_page"] = !empty( $gLibertySystem->mPlugins[$pluginGuid]['help_page'] ) ? $gLibertySystem->mPlugins[$pluginGuid]['help_page'] : NULL;
			$pinfo["syntax"] = $gLibertySystem->mPlugins[$pluginGuid]['syntax'];
			if( !empty( $gLibertySystem->mPlugins[$pluginGuid]['help_function'] ) && function_exists( $gLibertySystem->mPlugins[$pluginGuid]['help_function'] ) ) {
				$pinfo["exthelp"] = $gLibertySystem->mPlugins[$pluginGuid]['help_function']();
			}
			$dataplugins[] = $pinfo;
		}
	}
}
$gBitSmarty->assign_by_ref( 'formatplugins', $formatplugins );
$gBitSmarty->assign_by_ref( 'dataplugins', $dataplugins );
?>
