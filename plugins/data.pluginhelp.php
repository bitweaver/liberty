<?php
// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See copyright.txt for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Author: StarRider <starrrider@sbcglobal.net>
// | Reworked from: wikiplugin_pluginhelp.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.pluginhelp.php,v 1.1 2005/06/19 04:55:48 bitweaver Exp $
// Initialization
define( 'PLUGIN_GUID_DATAPLUGINHELP', 'datapluginhelp' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'PLUGINHELP',
						'auto_activate' => TRUE,
						'requires_pair' => FALSE,
						'load_function' => 'data_pluginhelp',
						'title' => 'PluginHelp<strong> - This plugin is not yet functional.</strong>', // Remove this line when the plugin becomes operational
//						'title' => 'PluginHelp',                                                                             // and Remove the comment from the start of this line
						'description' => tra("This plugin will display the plugin's Help."),
						'help_function' => 'data__pluginhelp_help',
						'tp_helppage' => "http://www.bitweaver.org/wiki/index.php", // Update this URL when a page on TP.O exists
						'syntax' => "{PLUGINHELP plugin= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAPLUGINHELP, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAPLUGINHELP );

// Help Function
function data_pluginhelp_help() {
	$back = tra("^__Parameter Syntax:__ ") . "~np~{PLUGINHELP(" . tra("key=> )}~/np~\n");
	$back.= tra("||__::key: ::__ | __::value::__ | __::Comments::__\n");
	$back.= "::plugin::" . tra(" | ::plugin name:: | the name of a plugin. Will display the Help and Extended Help - fairly much as they are seen here.||^");
	$back.= tra("^__Example:__ ") . "~np~{PLUGINHELP(plugin=>pluginhelp)}{PLUGINHELP}~/np~^";
	return $back;
}

// Load Function
function data_pluginhelp($data, $params) {
	$ret = "This plugin has not been completed as yet. ";
	return $ret;
}
/******************************************************************************
The code below is from the deprecated PLUGINHELP plugin. All comments and the help routines have been removed. - StarRider
include_once( WIKI_PKG_PATH.'BitPage.php');
function wikiplugin_pluginhelp($data, $params) {
	global $wikilib;
	extract ($params);
	if (!isset($plugin)) {
		return tra("The plugin <b>PluginHelp</b> needs the name of a plugin to function. Please seek Help.<br/>");
	}
	$file = "wikiplugin_" . $plugin . ".php";
	$func_name = "wikiplugin_" . $plugin . "_help";
	
	if (file_exists( PLUGINS_DIR . '/' . $file)) {
		include_once( PLUGINS_DIR . '/' . $file );
		$back = '<b>' . strtoupper($plugin) . ' - </b>';
		if (function_exists($func_name)) { $back.= $func_name(); }
		$func_name = "wikiplugin_" . $plugin . "_extended_help";
		if (function_exists($func_name)) { $back.= $func_name(); }
	} else {
		$back = tra("Unable to locate the file named <b>") . $file . '</b> in the <b>' . PLUGINS_DIR . '/</b> ' . tra("directory");
	}
	return $back;
}
*/
?>
