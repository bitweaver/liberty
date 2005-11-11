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
// | Author (TikiWiki): Unknown
// | Reworked & Undoubtedly Screwed-Up for (Bitweaver) 
// | by: StarRider <starrrider@sourceforge.net>
// | Reworked from: wikiplugin_wikilist.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.wikilist.php,v 1.1.1.1.2.8 2005/11/11 22:04:09 mej Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATAWIKILIST', 'datawikilist' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'WIKILIST',
						'auto_activate' => FALSE,
						'requires_pair' => TRUE,
						'load_function' => 'data_wikilist',
						'title' => 'WikiList<strong> - This plugin is not yet functional.</strong>', // Remove this line when the plugin becomes operational
//						'title' => 'WikiList',                                                                             // and Remove the comment from the start of this line
						'help_page' => 'DataPluginWikiList',
						'description' => tra("Displays an alphabetically sorted list of WikiPages"),
						'help_function' => 'data_wikilist_help',
						'syntax' => "{WIKILIST num= alpha= total= list= }Group Name{WIKILIST} ",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAWIKILIST, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAWIKILIST );

// Help Function
function data_wikilist_help() {
	$back = tra("^__Parameter Syntax:__ ") . "~np~{WIKILIST" . tra("(key=>value)}~/np~\n");
	$back.= tra("||__::key::__ | __::value::__ | __::Comments::__\n");
	$back.= "::num::" . tra(" | ::0 or 1:: |  adds numbering to the list. Default = None (0).\n");
	$back.= "::alpha::" . tra(" | ::0 or 1:: |  sorts names aplhabetically and groups them by the beginning letter. Default = On (1).\n");
	$back.= "::lists:: | ::all / userpages / wiki:: |  " . tra("defines the type of pages to be shown. wiki & userpages will only show those type of pages. Default = all.\n");
	$back.= "::total::" . tra(" | ::0 or 1:: |  shows total number of users in list at the end. Default = On (1).\n");
	$back.= "::GroupName::" . tra(" | ::Not a Parameter:: |  Given between ~np~{WIKILIST}~np~ blocks. If no GroupName is given then All Users is assumed.\n");
	return $back;
}

// Load Function
function data_wikilist($data, $params) {
	$ret = "This plugin has not been completed as yet. ";
	return $ret;
}
/******************************************************************************
The code below is from the deprecated WIKILIST plugin. All comments and the help routines have been removed. - StarRider

// Displays an alphabetically sorted list of WikiPages
// Use:
// {WIKILIST(num=>1,alpha=>1,total=>1,list=>all)}{WIKILIST}
// num=>1		--> writes a number in front of every name							default = 0
// alpha=>1		--> shows names in groups of beginning letters						default = 1
// list=>																			default = all
//		all			--> shows the wiki list and appends the userPage list
//		userpages	--> shows all userPages without showing the wiki list
//		wiki		--> shows the wiki list without showing the userpages
// total=>1		--> shows total number of users in list at the end					default = 1
//
// If no groupname is given, plugin returns all users


// function used to sort an array - NOT case-sensitive
function wikiplugin_compare_wikipages($a, $b) {
	return strcmp(strtolower($a), strtolower($b));
}

function wikiplugin_wikilist($data, $params) {
	global $gBitSystem;
	global $feature_hotwords;
	// turn off $feature_hotwords to avoid conflicts
	$feature_hotwords = 'n';

	extract ($params, EXTR_SKIP);
	if(!isset($alpha))		{ $alpha = 1; }
	if(!isset($userpages))	{ $userpages = "all"; }
	if(!isset($num))		{ $num = 0; }
	if(!isset($total))		{ $total = 1; }

	$ret = "";
	$pagedata = $gBitSystem->list_pages(0);

	foreach ($pagedata['data'] as $pagedata_temp) {
		$wiki_pages_all[] = $pagedata_temp['pageName'];
	}

	// sort the pages
	usort($wiki_pages_all, "wikiplugin_compare_wikipages");

	// sort the userpages from the rest of the wikipages
	foreach ($wiki_pages_all as $pagename) {
		if(strstr($pagename,"userPage") == false){
			$wiki_pages[] = $pagename;
		}
		else {
			$user_pages[] = $pagename;
		}
	}

	if ($list != "userpages") {
		$wp_list_count = 0;
		foreach ($wiki_pages as $pagename) {
			if ($wp_list_count >= 1) {
				$prev_pagename = $wiki_pages[$wp_list_count-1];
			}
			else {
				$prev_pagename = 0;
			}
			$wp_list_count++;

			if ($alpha != 0) {
				if (strtolower($prev_pagename[0]) != strtolower($pagename[0])) {
					$ret .= ("-=".strtoupper($pagename[0])."=-\n");
				}
			}

			if ($num != 0) {
				$ret .= ($wp_list_count." ");
			}

			$ret .= ("((".$pagename."))\n");
		}

		if ($total != 0) {
			$ret .= ("<br />".tra("Total").": ".$wp_list_count."\n");
		}
	}

	if ($list != "wiki") {
		$ret .= ("-=userPages=-\n");

		$wp_list_count = 0;
		foreach ($user_pages as $user_title) {
			$wp_list_count++;
			if ($num != 0) {
				$ret .= ($wp_list_count." ");
			}

			$ret .= ("((".$user_title."))\n");
		}

		if ($total != 0) {
			$ret .= ("<br />".tra("Total").": ".$wp_list_count."\n");
		}
	}

	return $ret;
}
*/
?>
