<?php
/**
 * @version  $Revision: 1.2.2.10 $
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
// | Author (TikiWiki): Mose <mose@sourceforge.net>
// | Reworked & Undoubtedly Screwed-Up for (Bitweaver) 
// | by: StarRider <starrrider@sourceforge.net>
// +----------------------------------------------------------------------+
// $Id: data.sf.php,v 1.2.2.10 2005/11/11 22:04:09 mej Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATASF', 'datasf' );
define('SF_CACHE',48); # in hours
define('DEF_TAG','bugs');
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'SF',
						'auto_activate' => TRUE,
						'requires_pair' => FALSE,
						'load_function' => 'data_sf',
						'title' => 'SourceForge (SF)',
						'help_page' => 'DataPluginSourceForge',
						'description' => tra("Creates a link to SourceForge. Can link to the Bugs / RFEs / Patches / Support Index pages or individual items on those pages."),
						'help_function' => 'data_sf_help',
						'syntax' => "{SF tag= aid= groupid= atid= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATASF, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATASF );

// Help Function
function data_sf_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>tag</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The") . ' <strong>tag</strong> ' . tra("is a short-cut that allows you to use this plugin without having to look up the SourceForge") 
				.' <strong>groupid or adit</strong> ' . tra("numbers for specific projects.") 
				.'<br/>' . tra("Possible values for BitWeaver are:") . '<strong>bugs / rfe / patches / support</strong>'
				.'<br/>' . tra("Possible values for TikiWiki are:") . '<strong>twbugs / twrfe / twpatches / twsupport</strong>'
				.'<br/>' . tra("Possible values for JGraph are:") . '<strong>jgbugs / jgrfe / jgsupport</strong>'
				.'<br/>' . tra("Possible values for PhpBB are:") . '<strong>pbbrfe</strong></td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>aid</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "By supplying an") . ' <strong>aid</strong> ' . tra("value - the link will be to a specific Bug/RFE/Patch/ or /Support item.")
				.tra(" If not given - the link will be to the index page for the project in question.") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>groupid</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Every SourceForge project has an") . ' <strong>groupid</strong> ' . tra(". This number can be aquired by looking at the URLwhen looking at the project.") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>atid</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Only") . ' <strong>Tracker</strong> ' . tra("pages on SourceForge used a") . ' <strong>adit</strong> ' 
				.tra("number. These pages are: Bugs / RFE / Patches / and ") . '<strong>3</strong></td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{SF tag=bugs } - Link to BitWeaver's Bug Index Page<br />"
		. tra("Example: ") . "{SF tag=bugs aid=1226624  } - Link to a BitWeaver Bug<br />"
		. tra("Example: ") . "{SF groupid=141358 atid=749176 aid=1226624 } - Same as Last Example only done the hard way";
	return $help;
}

// Load Function
function data_sf($data, $params) {
	# customize that (or extract it in a db)
	// [tag]     = array(  groupid , atid , ProjectName , TrackerName , IndexName ) 
// BitWeaver ******************************************************
	$sftags['bugs'] = array('141358','749176','BitWeaver',' Bug #',' Bug Index');
	$sftags['rfe'] = array('141358','749179','BitWeaver',' RFE #',' RFE Index');
	$sftags['patches'] = array('141358','749178','BitWeaver',' Patch #',' Patch Index');
	$sftags['support'] = array('141358','749177','BitWeaver',' Support #',' Support Index');
// TikiWiki ******************************************************
	$sftags['twbugs'] = array('64258','506846','TikiWiki',' Bug #',' Bug Index');
	$sftags['twrfe'] = array('64258','506849','TikiWiki',' RFE #',' RFE Index');
	$sftags['twpatches'] = array('64258','506848','TikiWiki',' Patch #',' Patch Index');
	$sftags['twsupport'] = array('64258','506847','TikiWiki',' Support #',' Support Index');
// JGraph ******************************************************
	$sftags['jgbugs'] = array('43118','435210','JGraph',' Bug #',' Bug Index');
	$sftags['jgrfe'] = array('43118','435213','JGraph',' RFE #',' RFE Index');
	$sftags['jgsupport'] = array('43118','435211','JGraph',' Support #',' Support Index');
// JGraph ******************************************************
	$sftags['pbbrfe'] = array('7885','58021','PhpBB',' Bug #',' Bug Index');
	
	extract ($params, EXTR_SKIP);
	$tag = (isset($tag)) ? strtolower($tag) : ' '; // Just to be sure no caps
	if (isset($sftags["$tag"]) and (is_array($sftags["$tag"])) ) { // is $tag in the array 
		list($groupid,$atid,$proj,$tag1,$tag2) = $sftags["$tag"];
		$tag = (isset($aid)) ? $proj . $tag1 . $aid : $proj . $tag2;
	} else { // So their must be doing it the hard way
		if ((!isset($groupid)) or (!isset($atid))) { // If not given set $group_id & $atid to default
			list($groupid,$atid,$proj,$tag1,$tag2) = $sftags[DEF_TAG];
			$tag = (isset($aid)) ? $proj . $tag1 . $aid : $proj . $tag2;
		} else { // Both groupid & atid are present / but project is unknown - so
			$tag = (isset($aid)) ? 'Unknown Project / ID #' . $aid : 'Unknown Project Index Page';
	}	}
	$url = (isset($aid)) ? 'http://sourceforge.net/tracker/index.php?func=detail&aid=' . $aid . '&' : 'http://sourceforge.net/tracker/?'; 
	$url = $url . 'group_id=' . $groupid . '&atid=' . $atid;

	$ret = '<a href="' . $url . '" title="Launch Source Forge.net in a New Window" onkeypress="popUpWin(this.href,\'full\',800,800);" onclick="popUpWin(this.href,\'full\',800,800);return false;">' . $tag . '</a>';

	return $ret;
}
