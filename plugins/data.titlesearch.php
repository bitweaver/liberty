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
// | Author (TikiWiki): Claudio Bustos <cdx@users.sourceforge.net>
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up) 
// | by: StarRider <starrrider@users.sourceforge.net>
// | Reworked from: wikiplugin_titlesearch.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.titlesearch.php,v 1.1.1.1.2.9 2005/11/11 22:04:09 mej Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATATITLESEARCH', 'datatitlesearch' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'TITLESEARCH',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_titlesearch',
						'title' => 'TitleSearch<strong> - This plugin is not yet functional.</strong>', // Remove this line when the plugin becomes operational
//						'title' => 'TitleSearch',                                                                                         // and Remove the comment from the start of this line
						'help_page' => 'DataPluginTitleSearch',
						'description' => tra("This plugin search the titles of all pages in this wiki."),
						'help_function' => 'data_titlesearch_help',
						'syntax' => "{TITLESEARCH search= info= exclude= noheader= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATATITLESEARCH, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATATITLESEARCH );

// Help Function
function data_titlesearch_help() {
	$back = tra("^__Parameter Syntax:__ ") . "~np~{TITLESEARCH" . tra("(key=>value)}~/np~\n");
	$back.= tra("||__::key::__ | __::value::__ | __::Comments::__\n");
	$back.= "::search::" . tra(" | ::string:: | __Required__ - So what do you want to search for?\n");
	$back.= "::info::" . tra(" | ::value:: | defines which fields are to be displayed. Possible values are: ") . "__hits / lastModif / user / ip / len / comment / creator / version /  flag / versions / links / backlinks__" . tra(" Multiple values can be given using the character ~124~. Example: ") . "__user~124~len~124~comment__" . tra(". The default shows __None__ of this information.\n");
	$back.= "::exclude::" . tra(" | ::page names:: | pages to be excluded from the listing. Multiple names can be exclude using the character ~124~. Example: ") . "__HomePage~124~SandBox~124~~np~RecentChanges~/np~__." . tra(" By default - __Every__ page is displayed.\n");
	$back.= "::noheader::" . tra(" | ::boolean:: |  if True (any value = False) a header will be displayed. The default is __False__.||^");
	$back.= tra("^__Example:__ ") . "~np~{TITLESEARCH(search=>Admin,info=>user,exclude=>HomePage|SandBox,noheader=>1)}{TITLESEARCH}~/np~^";
	return $back;
}

// Load Function
function data_titlesearch($data, $params) {
	$ret = "This plugin has not been completed as yet. ";
	return $ret;
}
/******************************************************************************
The code below is from the deprecated TITLESEARCH plugin. All comments and the help routines have been removed. - StarRider
    require_once( WIKI_PKG_PATH.'plugins_lib.php' );
	include_once( WIKI_PKG_PATH.'BitPage.php');
        function run ($data, $params) {
            global $WikiLib;
            $aInfoPreset = array_keys($this->aInfoPresetNames);
            $params = $this->getParams($params, true);
            extract ($params, EXTR_SKIP);
            if (!$search) {
                return $this->error("You have to define a search");
            }
            //
            /////////////////////////////////
            // Create a valid list for $info
            /////////////////////////////////
            //
            if ($info) {
                $info_temp = array();
                foreach($info as $sInfo) {
                    if (in_array(trim($sInfo), $aInfoPreset)) {
                        $info_temp[] = trim($sInfo);
                    }
                    $info = $info_temp?$info_temp:
                    false;
                }
            } else {
                $info = false;
            }
            //
            /////////////////////////////////
            // Process pages
            /////////////////////////////////
            //
            $sOutput = "";
            $aPages = $wikilib->getList(0, -1, 'title_desc', $search);
            foreach($aPages["data"] as $idPage => $aPage) {
                if (in_array($aPage["title"], $exclude)) {
                    unset($aPages["data"][$idPage]);
                    $aPages["cant"]--;
                }
            }
            //
            /////////////////////////////////
            // Start of Output
            /////////////////////////////////
            //
            if (!$noheader) {
                // Create header
                $count = $aPages["cant"];
                if (!$count) {
                    $sOutput  .= tra("No pages found for title search")." '__".$search."__'";
                } elseif ($count == 1) {
                    $sOutput  .= tra("One page found for title search")." '__".$search."__'";
                } else {
                    $sOutput = "$count".tra(" pages found for title search")." '__".$search."__'";
                }
                $sOutput  .= "\n";
            }
            $sOutput.=PluginsLibUtil::createTable($aPages["data"],$info);
            return $sOutput;
        }
    }
*/
?>
