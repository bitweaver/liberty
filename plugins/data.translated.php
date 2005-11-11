<?php
/**
 * @version  $Revision: 1.1.1.1.2.9 $
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
// | Author (TikiWiki): Mose <mose@users.sourceforge.net>
// | Reworked & Undoubtedly Screwed-Up for (Bitweaver) 
// | by: StarRider <starrrider@sourceforge.net>
// | Reworked from: wikiplugin_translated.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.translated.php,v 1.1.1.1.2.9 2005/11/11 22:04:09 mej Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATATRANSLATED', 'datatranslated' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'TRANSLATED',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_translated',
						'title' => 'Translated<strong> - This plugin is not yet functional.</strong>', // Remove this line when the plugin becomes operational
//						'title' => 'Translated',                                                                                       // and Remove the comment from the start of this line
						'help_page' => 'DataPluginTranslated',
						'description' => tra("This plugin is used to create a link to a page that contains a translation. The link can be shown as an Icon for the country or as an abreviation for the language."),
						'help_function' => 'data_translated_help',
						'syntax' => "{TRANSLATED page= lang= flag= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATATRANSLATED, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATATRANSLATED );

// Help Function
function data_translated_help() {
	$back = tra("^__Parameter Syntax:__ ") . "~np~{TRANSLATED" . tra("(key=>value)}~/np~\n");
	$back.= tra("||__::key::__ | __::value::__ | __::Comments::__\n");
	$back.= "::page::" . tra(" | ::page name:: |  __Manditory__ Is a valid url for the page which contains the translation. The page name can be in the formats [url] or ((wikiname)) or ((inter:interwiki))\n^");
	$back.= "::lang::" . tra(" | ::abreviation:: | __Manditory__ Is a 2 letter code that specifies the language to use. See the Abreviations given below.\n");
	$back.= "::flag::" . tra(" | ::flag name:: | __Optional__ Is an image file that can be used as the link. See the Flag Names given below\n");
	$back.= "^__Flag Names:__ ~pp~American_Samoa / Angola / Antigua / Argentina / Armenia / Australia / Austria / Bahamas / Bahrain / Bangladesh / Barbados / Belgium / Bermuda / Bolivia / Brazil / Brunei / Canada / Cayman_Islands / Chile / China / Colombia / Cook_Islands / Costa_Rica / Croatia / Cuba / Cyprus / Czech_Republic / Denmark / Dominican_Republic / Ecuador / Egypt / El_Salvador / Estonia / Federated_States_of_Micronesia / Fiji / Finland / France / French_Polynesia / Germany / Greece / Guam / Guatemala / Haiti / Honduras / Hungary / Iceland / India / Indonesia / Iran / Iraq / Ireland / Israel / Italy / Jamaica / Japan / Jordan / Kazakstan / Kenya / Kiribati / Kuwait / Latvia / Lebanon / Lithuania / Malawi / Malaysia / Malta / Marshall_Islands / Mauritius / Mexico / Morocco / Mozambique / Nauru / Nepal / Netherlands / New_Caledonia / New_Zealand / Nicaragua / Nigeria / Niue / Norway / Pakistan / Panama / Papua_New_Guinea / Paraguay / Peru / Phillippines / Poland / Portugal / Puerto_Rico / Quatar / Romania / Russia / Samoa / Saudi_Arabia / Singapore / Slovakia / Slovenia / Solomon_Islands / Somalia / South_Africa / South_Korea / Spain / Sri_Lanka / St_Vincent_Grenadines / Surinam / Sweden / Switzerland / Taiwan / Thailand / Tonga / Trinidad_Tobago / Turkey / Tuvalu / Ukraine / United_Arab_Emirates / United_Kingdom / United_States / Uruguay / Vanuatu / Venezuela / Wales / Yugoslavia / Zambia / Zimbabwe~/pp~\n^";
	$back.= tra("^ __Language Abreviations:__ Chinese (Simplified)=") . "__cn__" . tra(" /  Chinese Traditional=") . "__tw__" . tra(" / Czech=") . "__cs__" . tra(" / Danish=") . "__da__" . tra(" / English=") . "__en__" . tra(" / French=") . "__fr__" . tra(" / German=") . "__de__" . tra(" / Hebrew=") . "__he__" . tra(" / Italian=") . "__it__" . tra(" / Japanese=") . "__ja__" . tra(" / Norwegian=") . "__no__" . tra(" / Polish=") . "__po__" . tra(" / Russian=") . "__ru__" . tra(" / Serbian=") . "__sr__" . tra(" / Slovak=") . "__sk__" . tra(" Spanish=") . "__es__" . tra(" Swedish=") . "__sv__.||^";
	$back.= tra("^__Example:__ ") . "~np~{TRANSLATED(page=>Home Page,lang=>fr,flag=>France)}~/np~^";
	return $back;
}

// Load Function
function data_translated($data, $params) {
    extract ($params, EXTR_SKIP);
    if (!isset($page) ) {  // A Manditory Parameter is missing
        $ret = 'The __page__ parameter was missing from the __~np~{TRANSLATED}~/np~__ plugin.';
		$ret.= data_translated_help();
	    return $ret;
	}
    if (!isset($lang) ) {  // A Manditory Parameter is missing
        $ret = 'The __lang__ parameter was missing from the __~np~{TRANSLATED}~/np~__ plugin.';
		$ret.= data_translated_help();
	    return $ret;
	}

	$img = '';
	$h = opendir(USERS_PKG_PATH . "icons/flags/");
	while ($file = readdir($h)) { // Open the directory and read each filename
		if (substr($file,0,1) != '.' and substr($file,-4,4) == '.gif') { // Operate only on gif files
			$avflags[] = substr($file,0,strlen($file)-4);
		}
	}
	if (isset($flag)) {
		if (in_array($flag,$avflags)) {
		    $file = USERS_PKG_URL . "icons/flags/" . $flag . ".gif";
			$img = "<img src='$file' width='18' height='13' vspace='0' hspace='3' alt='($lang)' align='baseline' />";
		}
	} else {
		$img = "($lang) ";
	}
	$ret = $img . $page;
	return $ret;
}
/******************************************************************************
The code below is from the deprecated TRANSLATED plugin. All comments and the help routines have been removed. - StarRider
function wikiplugin_translated($data, $params) {
	extract ($params, EXTR_SKIP);
	$img = '';
	$h = opendir(USERS_PKG_URL . "icons/flags/");
	while ($file = readdir($h)) {
		if (substr($file,0,1) != '.' and substr($file,-4,4) == '.gif') {
			$avflags[] = substr($file,0,strlen($file)-4);
		}
	}
	if (isset($flag)) {
		if (in_array($flag,$avflags)) {
			$img = "<img src=IMG_PKG_URL.'flags/$flag.gif' width='18' height='13' vspace='0' hspace='3' alt='$lang' align='baseline' /> ";
		}
	}
	if (!$img) {
		$img = "( $lang ) ";
	}
	if (isset($data)) {
		$back = $img.$data;
	} else {
		$back = "''no data''";
	}
	return $back;
}
*/
?>
