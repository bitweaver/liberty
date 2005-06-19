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
// | Reworked from: wikiplugin_sf.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.sf.php,v 1.1 2005/06/19 04:55:47 bitweaver Exp $
// Initialization
define( 'PLUGIN_GUID_DATASF', 'datasf' );
define('SF_CACHE',48); # in hours
define('DEFAULT_TAG','bugs');
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'SF',
						'auto_activate' => TRUE,
						'requires_pair' => FALSE,
						'load_function' => 'data_sf',
						'title' => 'Sf',
						'description' => tra("This plugin automatically creates a link to the appropriate ))SourceForge(( object for ))bitweaver((."),
						'help_function' => 'data__sf_help',
						'tp_helppage' => "http://www.bitweaver.org/wiki/index.php", // Update this URL when a page on TP.O exists
						'syntax' => "{SF groupid= adit= aid= tag= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATASF, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATASF );

// Help Function
function data_sf_help() {
	$back = tra("^__Parameter Syntax:__ ") . "~np~{SF" . tra("(key=>value)}~/np~\n");
	$back.= tra("||__::key::__ | __::value::__ | __::Comments::__\n");
	$back.= "::groupid::" . tra(" | ::number:: | every ))SourceForge(( project has one. This identifies the project and can be found in the URL.\n");
	$back.= "::adit::" . tra(" | ::number:: | is number identifies the section to be looked at. The choices are Bugs, Support, Patches & RFE's and are unique for each project.\n");
	$back.= "::aid::" . tra(" | ::number:: | is the Tracker in question. For a bug report labled ~034~__~091~ 123456 ~093~ I'm a bug!__~034~ - the ") . "aid" . tra(" number is 123456\n");
	$back.= "::tag::" . tra(" | ::name:: | is a short cut that allows the plugin to work automatically. The ") . "groupid & adit" . tra(" are supplied if these ") . "tags" . tra(" are used: ") . "__bugs / rfe / patches / support__" . tra(" are for ))bitweaver(( - ") . "__twbugs / twrfe / twpatches / twsupport__" . tra(" are for ))TikiWiki(( - ") . "__jgbugs / jgsupport / jgrfe__" . tra(" are for JGraph.||^");
	$back.= tra("^__Example:__ ") . "~np~{SF(groupid=>101599,adit=>630083,aid=>928215)}~/np~\n";
	$back.= tra("This is a ))bitweaver(( bug report named ~034~Lost in Space - errrr - bitweaver~034~^");
	return $back;
}

function get_artifact_label($gid,$atid,$aid,$reload=false) {
	$agent = $_SERVER['HTTP_USER_AGENT'];
	$cachefile = TEMP_PKG_PATH."cache/".$bitdomain."sftrackers.cache.$gid.$atid.$aid";
	$cachelimit = time() - 60*60*SF_CACHE;
	$url = "http://sourceforge.net/tracker/index.php?func=detail&amp;aid=$aid&amp;group_id=$gid&amp;atid=$atid";
	if (!is_file($cachefile)) $reload = true;
	$back = false;
	if ($reload or (filemtime($cachefile) < $cachelimit)) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_REFERER, $url);
		$buffer = curl_exec ($ch);
		curl_close ($ch);
		if (preg_match("/<title>[^-]*-([^<]*)<\/title>/i",$buffer,$match)) {
			$fp = fopen($cachefile,"wb");
			fputs($fp,$match[1]);
			fclose($fp);
		} elseif (is_file($cachefile)) {
			$fp = fopen($cachefile,"rb");
			$back = fgets($fp);
			fclose($fp);
		}
	} else {
		$fp = fopen($cachefile,"rb");
		$back = fgets($fp,4096);
		fclose($fp);
	}
	return $back;
}

// Load Function
function data_sf($data, $params) {
	# customize that (or extract it in a db)
	$sftags['bugs'] = array('101599','630083');
	$sftags['rfe'] = array('101599','630086');
	$sftags['patches'] = array('101599','630085');
	$sftags['support'] = array('101599','630084');
	$sftags['twbugs'] = array('64258','506846');
	$sftags['twrfe'] = array('64258','506849');
	$sftags['twpatches'] = array('64258','506848');
	$sftags['twsupport'] = array('64258','506847');
	$sftags['jgbugs'] = array('43118','435210');
	$sftags['jgsupport'] = array('43118','435211');
	$sftags['jgrfe'] = array('43118','435213');
	extract ($params);
	if (isset($tag) and isset($sftags["$tag"]) and is_array($sftags["$tag"])) {
		list($sf_group_id,$sf_atid) = $sftags["$tag"];
	} else {
		$sf_group_id = (isset($groupid)) ? "$groupid" : $sftags[DEFAULT_TAG][0];
		$sf_atid = (isset($atid)) ? "$atid" : $sftags[DEFAULT_TAG][1];
		$tag = DEFAULT_TAG;
	}
	if (!isset($aid)) {
		//return "__please use (aid=>xxx) as parameters__";
		return "<b>please use (aid=>xxx) as parameters</b>";
	}
	$label = get_artifact_label($sf_group_id,$sf_atid,$aid);
	//$back = "[http://sf.net/tracker/index.php?func=detail&amp;aid=$aid&amp;group_id=$sf_group_id&amp;atid=$sf_atid|$tag:#$aid: $label|nocache]";
	$back = "<a href='http://sf.net/tracker/index.php?func=detail&amp;aid=$aid&amp;group_id=$sf_group_id&amp;atid=$sf_atid' target='_blank' title='$tag:#$aid' class='wiki'>$label</a>";
	return $back;
}
/******************************************************************************
The code below is from the deprecated SF plugin. All comments and the help routines have been removed. - StarRider
define('SF_CACHE',48); # in hours
define('DEFAULT_TAG','bugs');

function get_artifact_label($gid,$atid,$aid,$reload=false) {
	$agent = $_SERVER['HTTP_USER_AGENT'];
	$cachefile = TEMP_PKG_PATH."cache/".$bitdomain."sftrackers.cache.$gid.$atid.$aid";
	$cachelimit = time() - 60*60*SF_CACHE;
	$url = "http://sourceforge.net/tracker/index.php?func=detail&amp;aid=$aid&amp;group_id=$gid&amp;atid=$atid";
	if (!is_file($cachefile)) $reload = true;
	$back = false;
	if ($reload or (filemtime($cachefile) < $cachelimit)) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_REFERER, $url);
		$buffer = curl_exec ($ch);
		curl_close ($ch);
		if (preg_match("/<title>[^-]*-([^<]*)<\/title>/i",$buffer,$match)) {
			$fp = fopen($cachefile,"wb");
			fputs($fp,$match[1]);
			fclose($fp);
		} elseif (is_file($cachefile)) {
			$fp = fopen($cachefile,"rb");
			$back = fgets($fp);
			fclose($fp);
		}
	} else {
		$fp = fopen($cachefile,"rb");
		$back = fgets($fp,4096);
		fclose($fp);
	}
	return $back;
}
function wikiplugin_sf($data, $params) {
	# customize that (or extract it in a db)
	$sftags['bugs'] = array('101599','630083');
	$sftags['rfe'] = array('101599','630086');
	$sftags['patches'] = array('101599','630085');
	$sftags['support'] = array('101599','630084');
	$sftags['twbugs'] = array('64258','506846');
	$sftags['twrfe'] = array('64258','506849');
	$sftags['twpatches'] = array('64258','506848');
	$sftags['twsupport'] = array('64258','506847');
	$sftags['jgbugs'] = array('43118','435210');
	$sftags['jgsupport'] = array('43118','435211');
	$sftags['jgrfe'] = array('43118','435213');
	extract ($params);
	if (isset($tag) and isset($sftags["$tag"]) and is_array($sftags["$tag"])) {
		list($sf_group_id,$sf_atid) = $sftags["$tag"];
	} else {
		$sf_group_id = (isset($groupid)) ? "$groupid" : $sftags[DEFAULT_TAG][0];
		$sf_atid = (isset($atid)) ? "$atid" : $sftags[DEFAULT_TAG][1];
		$tag = DEFAULT_TAG;
	}
	if (!isset($aid)) {
		//return "__please use (aid=>xxx) as parameters__";
		return "<b>please use (aid=>xxx) as parameters</b>";
	}
	$label = get_artifact_label($sf_group_id,$sf_atid,$aid);
	//$back = "[http://sf.net/tracker/index.php?func=detail&amp;aid=$aid&amp;group_id=$sf_group_id&amp;atid=$sf_atid|$tag:#$aid: $label|nocache]";
	$back = "<a href='http://sf.net/tracker/index.php?func=detail&amp;aid=$aid&amp;group_id=$sf_group_id&amp;atid=$sf_atid' target='_blank' title='$tag:#$aid' class='wiki'>$label</a>";
	return $back;
}
*/
?>
