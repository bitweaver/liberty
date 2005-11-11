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
// | Author (TikiWiki): Claudio Bustos <cdx@users.sourceforge.net>
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up) 
// | by: StarRider <starrrider@users.sourceforge.net>
// | Reworked from: wikiplugin_backlinks.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.backlinks.php,v 1.1.1.1.2.8 2005/11/11 22:04:08 mej Exp $

/**
 * definitions
 */
global $gBitSystem;
if( ( $gBitSystem->isPackageActive( 'wiki' ) ) && ( $gBitSystem->isFeatureActive('feature_backlinks') ) ) { // Do not include this Plugin if the Package or the Feature is not active
define( 'PLUGIN_GUID_DATABACKLINKS', 'databacklinks' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'BACKLINKS',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_backlinks',
						'title' => 'BackLinks<strong> - This plugin is not yet functional.</strong>', // Remove this line when the plugin becomes operational
//						'title' => 'BackLinks',                                                                             // and Remove the comment from the start of this line
						'help_page' => 'DataPluginBackLinks',
						'description' => tra("This plugin will list all Wiki pages which contains a link to the specified page."),
						'help_function' => 'data_backlinks_help',
						'syntax' => "{BACKLINKS page= info= exclude= self= header= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATABACKLINKS, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATABACKLINKS );

// Help Function
function data_backlinks_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>page</td>'
				.'<td>' . tra( "page name") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( " Can be any wiki page. (Default = the current page)") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>info</td>'
				.'<td>' . tra( "key-words") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Defines what is to be displayed. Multiple columns can be displayed if joined with the character |. Available choices are:") 
				.' <strong>hits, lastmodif, user, ip, len, comment, creator, version, flag, versions, links, backlinks</strong>. ' . tra("(Default = EVERYTHING)") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>exclude</td>'
				.'<td>' . tra( "page-name(s)") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Can be any wiki page. Multiple pagenames will be excluded if joined with the character |. Like this:") 
				.' <strong>HomePage|SandBox</strong> ' . tra("(Default = EVERYTHING is displayed)") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>self</td>'
				.'<td>' . tra( "boolean") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Determins if the page currently viewed is listed. Any value results in TRUE. (Default = FALSE)") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>header</td>'
				.'<td>' . tra( "boolean") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Causes a heading to be displayed above the list. Any value results in TRUE. (Default = FALSE)") . '</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{BACKLINKS page='MyHomePage' info='hits|user' exclude='HomePage|SandBox'}<br />"
		. tra("Example: ") . "{BACKLINKS page='MyHomePage' info='hits|user' exclude='HomePage|SandBox' self='Yes' header='Yes'}";
	return $help;
}

// Load Function
function data_backlinks($data, $params) { // Pre-Clyde Changes
// Renamed Parameters $include_self to $self & $noheader to $header
// Changed $header so that any value passed to it makes it True
 // Added testing to Maintain Pre-Clyde compatability
	extract ($params, EXTR_SKIP);
	if (isset ($include_self) && ($include_self) ) // Maintain Pre-Clyde compatability
		$self = TRUE;
    $self = isset($self) ? TRUE : FALSE; // Any value passed in this parameter makes it True
	if (isset ($noheader) && ($noheader) )  // Maintain Pre-Clyde compatability
		$header = TRUE;
    $header = isset($header) ? TRUE : FALSE; // Any value passed in this parameter makes it True

// The next 2 lines allow access to the $pluginParams given above and may be removed when no longer needed
	global $gLibertySystem; 
	$pluginParams = $gLibertySystem->mPlugins[PLUGIN_GUID_DATABACKLINKS];
	$ret = 'The plugin <strong>"' . $pluginParams['tag'] . '"</strong> has not been completed as yet. ';
	return $ret;
}
}
/******************************************************************************
The code below is from the deprecated BACKLINKS plugin. All comments and the help routines have been removed. Also - this code was originally in object oriented format - StarRider
    require_once( WIKI_PKG_PATH.'plugins_lib.php' );
    function run ($data, $params) {
        global $wikilib;
        $params = $this->getParams($params, true);
        $aInfoPreset = array_keys($this->aInfoPresetNames);
        extract ($params, EXTR_SKIP);
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
        }
        $sOutput = "";
        // Verify if the page exists
        if (!$wikilib->pageExists($page)) {
            return $this->error(tra("Page cannot be found")." : <b>$page</b>");
        }
        //
        /////////////////////////////////
        // Process backlinks
        /////////////////////////////////
        //
        $aBackRequest = array();
        $aBackLinks = $wikilib->get_backlinks($page);
        foreach($aBackLinks as $backlink) {
            if (!in_array($backlink["from_page"], $exclude)) {
                $aBackRequest[] = $backlink["from_page"];
            }
        }
        if ($self) {
            $aBackRequest[] = $page;
        }
        if (!$aBackRequest) {
            return tra("No pages link to")." (($page))";
        } else {
            $aPages = $this->getList(0, -1, 'title_desc', $aBackRequest);
        }
        //
        /////////////////////////////////
        // Start of Output
        /////////////////////////////////
        //
        if (!$noheader) {
            // Create header
            $count = $aPages["cant"];
            if ($count == 1) {
                $sOutput  .= tra("One page links to")." (($page))";
            } else {
                $sOutput = "$count ".tra("pages link to")." (($page))";
            }
            $sOutput  .= "\n";
        }
        $sOutput  .= PluginsLibUtil::createTable($aPages["data"], $info);
        return $sOutput;
    }
 */
?>
