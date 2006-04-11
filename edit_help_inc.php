<?php
/**
 * $Id: edit_help_inc.php,v 1.8 2006/04/11 19:20:17 starrrider Exp $
 * edit_help_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.8 $
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
			$pinfo["guid"] = $pluginGuid;
			$pinfo["title"]	=		!empty( $gLibertySystem->mPlugins[$pluginGuid]['edit_label'] ) ?	tra('Format Plugin').' "'.$gLibertySystem->mPlugins[$pluginGuid]['edit_label'].'"' : tra('Undefined Plugin Name');
			$pinfo["description"] =	!empty( $gLibertySystem->mPlugins[$pluginGuid]['description'] ) ?	$gLibertySystem->mPlugins[$pluginGuid]['description'] : tra('Not Defined');
			$pinfo["is_active"] =	$gLibertySystem->mPlugins[$pluginGuid]['is_active'];
			$pinfo["help_page"] =	!empty( $gLibertySystem->mPlugins[$pluginGuid]['help_page'] ) ?		$gLibertySystem->mPlugins[$pluginGuid]['help_page'] : NULL ;
			$formatplugins[] = $pinfo;
	}	}
	if( ($gLibertySystem->mPlugins[$pluginGuid]['plugin_type'] == DATA_PLUGIN) && ($gLibertySystem->mPlugins[$pluginGuid]['is_active'] == 'y') ) {
		if( isset( $gLibertySystem->mPlugins[$pluginGuid]['description'] )) {
			$pinfo["guid"] =			preg_replace( "/^data/", "", $pluginGuid );
			$pinfo['title'] =			!empty( $gLibertySystem->mPlugins[$pluginGuid]['title'] )				? tra('Data Plugin').' "'.$gLibertySystem->mPlugins[$pluginGuid]['title'].'"' : tra('Undefined Plugin Name');
			$pinfo['description'] =		!empty( $gLibertySystem->mPlugins[$pluginGuid]['description'] )			? $gLibertySystem->mPlugins[$pluginGuid]['description'] : tra('Not Defined');
			$pinfo["is_active"] =		$gLibertySystem->mPlugins[$pluginGuid]['is_active'];
			$pinfo['auto_activate'] =	!empty( $gLibertySystem->mPlugins[$pluginGuid]['auto_activate'] )		? "TRUE" : "FALSE";
			$pinfo['tag'] =				!empty( $gLibertySystem->mPlugins[$pluginGuid]['tag'] )					? $gLibertySystem->mPlugins[$pluginGuid]['tag'] : tra('Not Defined');
			$pinfo['requires_pair'] =	!empty( $gLibertySystem->mPlugins[$pluginGuid]['requires_pair'] )		? "TRUE" : "FALSE";
			$pinfo['load_function'] =	!empty( $gLibertySystem->mPlugins[$pluginGuid]['load_function'] )		? $gLibertySystem->mPlugins[$pluginGuid]['load_function'] : tra('Not Defined');
			$pinfo['help_page'] =		!empty( $gLibertySystem->mPlugins[$pluginGuid]['help_page'] )			? $gLibertySystem->mPlugins[$pluginGuid]['help_page'] : NULL;
			$pinfo['syntax'] =			!empty( $gLibertySystem->mPlugins[$pluginGuid]['syntax'] )				? $gLibertySystem->mPlugins[$pluginGuid]['syntax'] : tra('Not Defined');
			$pinfo['help_function'] =	!empty( $gLibertySystem->mPlugins[$pluginGuid]['help_function'] )		? $gLibertySystem->mPlugins[$pluginGuid]['help_function'] : tra('Not Defined');
			$pinfo["exthelp"] = 		!empty( $gLibertySystem->mPlugins[$pluginGuid]['help_function'] ) &&
				function_exists($gLibertySystem->mPlugins[$pluginGuid]['help_function'] ) 						? $gLibertySystem->mPlugins[$pluginGuid]['help_function']() : '';
			$dataplugins[] = $pinfo;
}	}	}
$FirstPluginWinId = $dataplugins[0]["guid"];
$helpWinId  = 'HelpWindow';

$gBitSmarty->assign_by_ref( 'formatplugins', $formatplugins );
$gBitSmarty->assign_by_ref( 'dataplugins', $dataplugins );
$gBitSmarty->assign_by_ref( 'helpWinId', $helpWinId );
$gBitSmarty->assign_by_ref( 'FirstPluginWinId', $FirstPluginWinId );
?>
