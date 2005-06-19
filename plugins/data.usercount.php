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
// | Reworked from: wikiplugin_usercount.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.usercount.php,v 1.1 2005/06/19 04:55:47 bitweaver Exp $
// Initialization
define( 'PLUGIN_GUID_DATAUSERCOUNT', 'datausercount' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'USERCOUNT',
						'auto_activate' => FALSE,
						'requires_pair' => TRUE,
						'load_function' => 'data_usercount',
						'title' => 'UserCount<strong> - This plugin is not yet functional.</strong>', // Remove this line when the plugin becomes operational
//						'title' => 'UserCount',                                                                                       // and Remove the comment from the start of this line
						'description' => tra("Will show the number of users. If a Group Name can be included to filter the Groups."),
						'help_function' => 'data__usercount_help',
						'tp_helppage' => "http://www.bitweaver.org/wiki/index.php", // Update this URL when a page on TP.O exists
						'syntax' => "{USERCOUNT}Group Name{USERCOUNT}",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAUSERCOUNT, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAUSERCOUNT );

// Help Function
function data_usercount_help() {
	$back = tra("^__Parameter Syntax:__ ") . "~np~{USERCOUNT(key=>value)}~/np~\n";
	$back.= tra("||__::key: ::__ | __::value::__ | __::Comments::__\n");
	$back.= tra("::NONE:: | ::NONE:: | this plugin has no parameters.||^");
	$back.= tra("^__Example:__ ") . "~np~{USERCOUNT()}Registered{USERCOUNT}~/np~^";
	return $back;
}

// Load Function
function data_usercount($data, $params) {
	$ret = "This plugin has not been completed as yet. ";
	return $ret;
}
/******************************************************************************
The code below is from the deprecated USERCOUNT plugin. All comments and the help routines have been removed. - StarRider

function wikiplugin_usercount($data, $params) {
	global $gBitUser;
	extract ($params);
	$numusers = $gBitUser->count_users($data);
	return $numusers;
}
*/
?>
