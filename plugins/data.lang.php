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
// | Author (TikiWiki): Sylvie Greverend <sylvieg@users.sourceforge.net>
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up) 
// | by: StarRider <starrrider@users.sourceforge.net>
// | Reworked from: wikiplugin_lang.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.lang.php,v 1.1.1.1.2.8 2005/11/11 22:04:09 mej Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATALANG', 'datalang' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'LANG',
						'auto_activate' => FALSE,
						'requires_pair' => TRUE,
						'load_function' => 'data_lang',
						'title' => 'Lang<strong> - This plugin is not yet functional.</strong>', // Remove this line when the plugin becomes operational
//						'title' => 'Lang',                                                                             // and Remove the comment from the start of this line
						'help_page' => 'DataPluginLang',
						'description' => tra("This plugin will attempt to translate the text between the ") . "{LANG}" . tra(" blocks to the current language. If the translation fails - nothing is displayed."),
						'help_function' => 'data__lang_help',
						'syntax' => "{LANG lang= }" . tra("Text to be translated") . "{LANG}",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATALANG, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATALANG );

// Help Function
function data_lang_help() {
	$back = tra("^__Parameter Syntax:__ ") . "~np~{LANG" . tra("(key=>value)}~/np~\n");
	$back.= tra("||__::key::__ | __::value::__ | __::Comments::__\n");
	$back.= "::lang::" . tra(" | ::abreviation:: | the language of the text between the ") . "~np~{LANG}~/np~" . tra(" blocks. __Required__.\n");
	$back.= tra("abreviations: Chinese (Simplified)=") . "__cn__" . tra(" /  Chinese Traditional=") . "__tw__" . tra(" / Czech=") . "__cs__" . tra(" / Danish=") . "__da__" . tra(" / English=") . "__en__" . tra(" / French=") . "__fr__" . tra(" / German=") . "__de__" . tra(" / Hebrew=") . "__he__" . tra(" / Italian=") . "__it__" . tra(" / Japanese=") . "__ja__" . tra(" / Norwegian=") . "__no__" . tra(" / Polish=") . "__po__" . tra(" / Russian=") . "__ru__" . tra(" / Serbian=") . "__sr__" . tra(" / Slovak=") . "__sk__" . tra(" Spanish=") . "__es__" . tra(" Swedish=") . "__sv__.||^";
	$back.= tra("^__Example:__ ") . "~np~{LANG(lang=>fr)}Bon appétit{LANG}~/np~^";
	return $back;
}

// Load Function
function data_lang($data, $params) {
	global $gBitLanguage;
	extract ($params, EXTR_SKIP);
	if (!isset($lang) || $lang == $gBitLanguage->mLanguage)
		return $data;
	else
		return "";
}
/******************************************************************************
The code below is from the deprecated LANG plugin. All comments and the help routines have been removed. - StarRider

function wikiplugin_lang($data, $params) {
	global $gBitLanguage;
	extract ($params, EXTR_SKIP);
	if (!isset($lang) || $lang == $gBitLanguage->mLanguage)
		return $data;
	else
		return "";
}
*/
?>
