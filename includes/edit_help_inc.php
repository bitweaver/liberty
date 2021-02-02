<?php
/**
 * $Id$
 * edit_help_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision$
 * @package  liberty
 * @subpackage functions
 */

/**
 * required setup
 */
global $gLibertySystem, $gBitSmarty;

$inEditor = TRUE; // Required by PluginHelp to Determin Executed in an Editor

$dataplugins = array_merge( $gLibertySystem->getPluginsOfType( DATA_PLUGIN ), $gLibertySystem->getPluginsOfType( FILTER_PLUGIN ));
$formatplugins = $gLibertySystem->getPluginsOfType( FORMAT_PLUGIN );
$mimeplugins = $gLibertySystem->getPluginsOfType( MIME_PLUGIN );

// allow mime plugins to append help to the attachment plugin
foreach( $mimeplugins as $guid => $plugin ) {
	if( $func = $gLibertySystem->getPluginFunction( $guid, 'help_function' )) {
		$plugin['exthelp'] = $func();
		$mimeplugins[$guid]= $plugin;
	} else {
		unset( $mimeplugins[$guid] );
	}
}

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

foreach( array_keys( $formatplugins ) as $guid ) {
	// check to see if we have some format syntax help
	if( is_file( LIBERTY_PKG_PATH."templates/help_format_{$guid}_inc.tpl" )) {
		$formatplugins[$guid]['format_help'] = "bitpackage:liberty/help_format_{$guid}_inc.tpl";
		if( is_file( LIBERTY_PKG_INCLUDE_PATH.'help_format_{$guid}_inc.php' )) {
			include_once( LIBERTY_PKG_INCLUDE_PATH.'help_format_{$guid}_inc.php' );
		}
	}
}

if( !empty( $formatplugins ) ) {
	usort( $formatplugins, 'usort_by_title' );
	$gBitSmarty->assignByRef( 'formatplugins', $formatplugins );
}

if( !empty( $mimeplugins ) ) {
	usort( $mimeplugins, 'usort_by_title' );
	$gBitSmarty->assignByRef( 'mimeplugins', $mimeplugins );
}

if( !empty( $dataplugins ) ) {
	usort( $dataplugins, 'usort_by_title' );
	$gBitSmarty->assignByRef( 'dataplugins', $dataplugins );
}
?>
