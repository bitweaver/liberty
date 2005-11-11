<?php
/**
 * @version  $Revision: 1.1.1.1.2.10 $
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
// | Author (TikiWiki): Oliver Hertel <ohertel@users.sourceforge.net>
// | Reworked & Undoubtedly Screwed-Up for (Bitweaver) 
// | by: StarRider <starrrider@sourceforge.net>
// +----------------------------------------------------------------------+
// $Id: data.sort.php,v 1.1.1.1.2.10 2005/11/11 22:04:09 mej Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATASORT', 'datasort' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'SORT',
						'auto_activate' => FALSE,
						'requires_pair' => TRUE,
						'load_function' => 'data_sort',
						'title' => 'Sort',
						'help_page' => 'DataPluginSort',
						'description' => tra("This plugin sorts operates on lines of text - not the text in the lines. Every line between the ") . "~np~{SORT}~/np~" . tra(" blocks -  including the lines the blocks are on - is sorted."),
						'help_function' => 'data_sort_help',
						'syntax' => "{SORT sort= }" . tra("Lines to be sorted") . "{SORT}",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATASORT, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATASORT );

/**
 * Help Function
 */
function data_sort_help() {
	$back = tra("^__Parameter Syntax:__ ") . "~np~{SORT" . tra("(key=>value)}~/np~\n");
	$back.= tra("||__::key::__ | __::value::__ | __::Comments::__\n");
	$back.= "::sort::" . tra(" | ::direction:: | will sort the lines in the desired direction. Choices are: ") . "__asc / desc / reverse / shuffle__" . tra(" The default is ") . "__asc__.||^";
	$back.= tra("^__Example:__ ") . "~np~{SORT(sort=>shuffle)}".tra("Line 1 / Line 2 / Line 3")."{SORT}~/np~^";
	$back.= tra("^__Note:__ Plugin's are __case sensitive__. The Name of the plugin __MUST__ be UPPERCASE. The Key(s) are __always__ lowercase. Some Values are mixed-case but most require lowercase. When in doubt - look at the Example.^");
	return $back;
}

/**
 * Load Function
 */
function data_sort($data, $params) {
	extract ($params, EXTR_SKIP);
	$sort = (isset($sort)) ? $sort : "asc";
	$lines = explode("\n", $data); // separate lines into array
	// $lines = array_filter( $lines, "chop" ); // remove \n
	srand ((float)microtime() * 1000000); // needed for shuffle;
	if ($sort == "asc") {
		sort ($lines);
	} else if ($sort == "desc") {
		rsort ($lines);
	} else if ($sort == "reverse") {
		$lines = array_reverse($lines);
	} else if ($sort == "shuffle") {
		shuffle ($lines);
	}
	reset ($lines);
	if (is_array($lines)) {
		$data = implode("\n", $lines);
	}
	$data = trim($data);
	return $data;
}
?>
