<?php
/**
 * edit_help_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.5 $
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
			$pinfo["is_active"] =		$gLibertySystem->mPlugins[$pluginGuid]['is_active'];
			$pinfo["guid"] =			preg_replace( "/^data/", "", $pluginGuid );
			$pinfo['auto_activate'] =	!empty( $gLibertySystem->mPlugins[$pluginGuid]['auto_activate'] )		? "TRUE" : "FALSE";
			$pinfo['tag'] =				!empty( $gLibertySystem->mPlugins[$pluginGuid]['tag'] )					? $gLibertySystem->mPlugins[$pluginGuid]['tag'] : tra('Not Defined');
			$pinfo['requires_pair'] =	!empty( $gLibertySystem->mPlugins[$pluginGuid]['requires_pair'] )		? "TRUE" : "FALSE";
			$pinfo['load_function'] =	!empty( $gLibertySystem->mPlugins[$pluginGuid]['load_function'] )		? $gLibertySystem->mPlugins[$pluginGuid]['load_function'] : tra('Not Defined');
			$pinfo['title'] =			!empty( $gLibertySystem->mPlugins[$pluginGuid]['title'] )				? $gLibertySystem->mPlugins[$pluginGuid]['title'] : $pluginGuid;
			$pinfo['help_page'] =		!empty( $gLibertySystem->mPlugins[$pluginGuid]['help_page'] )			? $gLibertySystem->mPlugins[$pluginGuid]['help_page'] : NULL;
			$pinfo['description'] =		!empty( $gLibertySystem->mPlugins[$pluginGuid]['description'] )			? $gLibertySystem->mPlugins[$pluginGuid]['description'] : tra('Not Defined');
			$pinfo['syntax'] =			!empty( $gLibertySystem->mPlugins[$pluginGuid]['syntax'] )				? $gLibertySystem->mPlugins[$pluginGuid]['syntax'] : tra('Not Defined');
			$pinfo['variable_syntax'] =	isset( $gLibertySystem->mPlugins['variable_syntax'] ) &&
										!empty( $gLibertySystem->mPlugins[$pluginGuid]['variable_syntax'] )		? $gLibertySystem->mPlugins[$pluginGuid]['variable_syntax'] : FALSE;
			// NOTE: $gLibertySystem->mPlugins['variable_syntax'] is only used by the Plugin Library {LIB}
			$pinfo['help_function'] =	!empty( $gLibertySystem->mPlugins[$pluginGuid]['help_function'] )		? $gLibertySystem->mPlugins[$pluginGuid]['help_function'] : tra('Not Defined');
			$pinfo["exthelp"] = 		!empty( $gLibertySystem->mPlugins[$pluginGuid]['help_function'] ) &&
										function_exists($gLibertySystem->mPlugins[$pluginGuid]['help_function'] ) ? $gLibertySystem->mPlugins[$pluginGuid]['help_function']() : tra('None Defined');
			$mt = microtime()*1000000;
			$pinfo["windowId"] = $mt;
			$pinfo["extWinId"] = $mt+1;
			$dataplugins[] = $pinfo;
}	}	}
libNatSort2D( $dataplugins,'title' ); // Sort the array - StarRider
$jsWindow = microtime() * 1000000;
$firstPlugin = $dataplugins[1]["windowId"];

$gBitSmarty->assign_by_ref( 'formatplugins', $formatplugins );
$gBitSmarty->assign_by_ref( 'dataplugins', $dataplugins );
$gBitSmarty->assign_by_ref( 'jsWindow', $jsWindow );
$gBitSmarty->assign_by_ref( 'firstPlugin', $firstPlugin );
?>
