<?php
/**
 * @version  $Revision: 1.1.1.1.2.8 $
 * @package  liberty
 * @subpackage plugins_data
 */
// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See copyright.txt for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Author (TikiWiki): Damian Parker <damosoft@users.sourceforge.net>
// | Reworked & Undoubtedly Screwed-Up for (Bitweaver) 
// | by: StarRider <starrrider@sourceforge.net>
// | Reworked from: wikiplugin_usercount.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.usercount.php,v 1.1.1.1.2.8 2005/11/11 22:04:09 mej Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATAUSERCOUNT', 'datausercount' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'USERCOUNT',
						'auto_activate' => FALSE,
						'requires_pair' => TRUE,
						'load_function' => 'data_usercount',
						'title' => 'UserCount<strong> - This plugin is not yet functional.</strong>', // Remove this line when the plugin becomes operational
//						'title' => 'UserCount',                                                                                       // and Remove the comment from the start of this line
						'help_page' => 'DataPluginUserCount',
						'description' => tra("Will show the number of users. If a Group Name can be included to filter the Groups."),
						'help_function' => 'data_usercount_help',
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
	extract ($params, EXTR_SKIP);
	$numusers = $gBitUser->count_users($data);
	return $numusers;
}
*/
?>
