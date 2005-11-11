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
// | Author (TikiWiki): Luis Argerich <lrargerich@users.sourceforge.net>
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up) 
// | by: StarRider <starrrider@users.sourceforge.net>
// | Reworked from: wikiplugin_split.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.split.php,v 1.1.1.1.2.9 2005/11/11 22:04:09 mej Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATASPLIT', 'datasplit' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'SPLIT',
						'auto_activate' => FALSE,
						'requires_pair' => TRUE,
						'load_function' => 'data_split',
						'title' => 'Split<strong> - This plugin is not yet functional.</strong>', // Remove this line when the plugin becomes operational
//						'title' => 'Split',                                                                             // and Remove the comment from the start of this line
						'help_page' => 'DataPluginSplit',
						'description' => tra("This plugin is used to split a page in two or more columns using __-~045~-__ as a seperator."),
						'help_function' => 'data_split_help',
						'syntax' => "{SPLIT joincols= fixedsize= }{SPLIT}",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATASPLIT, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATASPLIT );

// Help Function
function data_split_help() {
	$back = tra("^__Parameter Syntax:__ ") . "~np~{SPLIT" . tra("(key=>value)}~/np~\n");
	$back.= tra("||__::key::__ | __::value::__ | __::Comments::__\n");
	$back.= "::joincols::" . tra(" | ::boolean:: |  if True (any value = False) a colspan will be generated if the column is missed. The default is __True__.\n");
	$back.= "::fixedsize::" . tra(" | ::boolean:: | if True (any value = False) a width attribute will be generated for Tables Row (~060~TD~062~). The default is __True__.||^");
	$back.= tra("^__Example:__ ") . "~np~{SPLIT()}" . tra("::-=Hot Dogs=-:: 2 for a Dollar --- ::-=Corn Dogs=-:: 3 for a Dollar") . "{SPLIT}~/np~\n";
	$back.= tra("This will display 2 boxes side by side with a Title Bar and text in each.^");
	return $back;
}

// Load Function
function data_split($data, $params) {
	$ret = "This plugin has not been completed as yet. ";
	return $ret;
}
/******************************************************************************
The code below is from the deprecated SPLIT plugin. All comments and the help routines have been removed. - StarRider
function wikiplugin_split($data, $params) {
	global $gBitSystem;
	global $replacement;
    // Remove first <ENTER> if exists...
    // it may be here if present after {SPLIT()} in original text
	if (substr($data, 0, 1) == "\n") $data = substr($data, 1);
	extract ($params, EXTR_SKIP);
    $fixedsize = (!isset($fixedsize) ? true : false);
    $joincols  = (!isset($joincols) ? true : false);
    // Split data by rows and cells
//	$sections = preg_split("/\@{3,}+/", $data);
	$sections = preg_split("/@@@+/", $data);
    $rows = array();
    $maxcols = 0;
    foreach ($sections as $i)
    {
//        $rows[] = preg_split("/-{3,}+/", $i);
	$rows[] = preg_split("/---+/", $i);
        $maxcols = max($maxcols, count(end($rows)));
    }
    // Is there split sections present?
    // Do not touch anything if no... even don't generate <table>
    if (count($rows) <= 1 && count($rows[0]) <= 1)
        return $data;
	$columnSize = floor(100 / $maxcols);
	$result = '<table border="0"'.($fixedsize ? ' width="100%"' : '').'>';
    // Attention: Dont forget to remove leading empty line in section ...
    //            it should remain from previous '---' line...
    // Attention: origianl text must be placed between \n's!!!
    foreach ($rows as $r)
    {
        $result .= "<tr>";
        $idx = 1;
	    foreach ($r as $i)
        {
            // Generate colspan for last element if needed
            $colspan = ((count($r) == $idx) && (($maxcols - $idx) > 0) ? ' colspan="'.($maxcols - $idx + 1).'"' : '');
            $idx++;
            // Add cell to table
    		$result .= '<td valign="top"'.($fixedsize ? ' width="'.$columnSize.'%"' : '').$colspan.'>'
			.preg_replace("/\\n/", "<br />", $i)
//                     . ((substr($i, 0, 1) == "\n") || (substr($i, 0, 1) == "\r") ? $i : "\n".$i)
//                     . ((substr($i, -1) == "\n") || (substr($i, -1) == "\r") ? '' : "\n")
                     . '</td>';
        }
        $result .= "</tr>";
    }
    // Close HTML table (no \n at end!)
	$result .= "</table>";
	return $result;
}
*/
?>
