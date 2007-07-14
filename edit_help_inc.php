<?php
/**
 * $Id: edit_help_inc.php,v 1.15 2007/07/14 08:04:02 squareing Exp $
 * edit_help_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.15 $
 * @package  liberty
 * @subpackage functions
 */

global $gLibertySystem;
$inEditor = TRUE; // Required by PluginHelp to Determin Executed in an Editor

$dataplugins = array_merge( $gLibertySystem->getPluginsOfType( DATA_PLUGIN ), $gLibertySystem->getPluginsOfType( FILTER_PLUGIN ));
$formatplugins = $gLibertySystem->getPluginsOfType( FORMAT_PLUGIN );

// refine data plugins and add help where available
foreach( $dataplugins as $guid => $plugin ) {
	if( !empty( $plugin['description'] ) && !empty( $plugin['syntax'] )) {
		$plugin["plugin_guid"] = preg_replace( "/^(data|filter)/", "", $guid );
		$plugin["exthelp"]     = !empty( $plugin['help_function'] ) && $gLibertySystem->getPluginFunction( $guid, 'help_function' ) ? $plugin['help_function']() : '';
		$dataplugins[$guid]    = $plugin;
	} else {
		unset( $dataplugins[$guid] );
	}
}

if( !empty( $formatplugins ) ) {
	usort( $formatplugins, 'usort_by_title' );
	$gBitSmarty->assign_by_ref( 'formatplugins', $formatplugins );
}

if( !empty( $dataplugins ) ) {
	usort( $dataplugins, 'usort_by_title' );
	$gBitSmarty->assign_by_ref( 'dataplugins', $dataplugins );
}
?>
