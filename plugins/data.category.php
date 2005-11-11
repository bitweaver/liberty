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
// | Author (TikiWiki): Oliver Hertel <ohertel@users.sourceforge.net>
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up) 
// | by: StarRider <starrrider@users.sourceforge.net>
// | Reworked from: wikiplugin_category.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.category.php,v 1.1.1.1.2.9 2005/11/11 22:04:08 mej Exp $

/**
 * definitions
 */
global $gBitSystem;
if( $gBitSystem->isPackageActive( 'categories' ) ) { // Do not include this Plugin if the Package is not active
define( 'PLUGIN_GUID_DATACATEGORY', 'datacategory' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'CATEGORY',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_category',
						'title' => 'Category<strong> - This plugin is not yet functional.</strong>', // Remove this line when the plugin becomes operational
//						'title' => 'Category',                                                                             // and Remove the comment from the start of this line
						'help_page' => 'DataPluginCategory',
						'description' => tra("This plugin insert a list of items for the current category or a given category."),
						'help_function' => 'data_category_help',
						'syntax' => "{CATEGORY id= types= sort= sub= split= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATACATEGORY, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATACATEGORY );

// Help Function
function data_category_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>id</td>' 
				.'<td>' . tra( "number(s)") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "A Category Id number or list of Id numbers. The easiest way to see a Category Id number is to open 'View Categories' and placing the mouse over the Category in question. The URL will be displayed by most browsers. The end of the URL contains an Id number like this: <strong>parent_id=9</strong>. Multiple Id numbers can be entered by joining them with the + character like this: <strong>1+2+3</strong>. Default = <strong>the Current Category Id Number </strong> if not defined.") . '</td>'
			.'</tr>'
				.'<tr class="even">'
				.'<td>type</td>'
				.'<td>' . tra( "key-word") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "A filter to limit which <strong>bitweaver</strong> Object will be displayed on the page. The key-words are:") . ' <strong>blog faq fgal igal newsletter poll quiz survey tracker & wiki</strong>. ' . tra("Multiple key-words can be entered by joining them with the + character like this:") . ' <strong>blog+wiki</strong>. ' . tra("The Default = ") . '<strong>ALL OBJECTS</strong></td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>sort</td>'
				.'<td>' . tra( "key-word") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Specifies a Sort Order. The Sort Order may be by:") . '<strong> type, created, name, or hits. </strong>' . tra("A direction can be defined by adding either") . '<strong> _asc</strong> or <strong>_dec. </strong>' . tra("(The Default = ") . '<strong>name_asc</strong>)</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>sub</td>'
				.'<td>' . tra( "boolean") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Determins if any subcategory items are to be displayed. Passing any value in this parameter will make it <strong>FALSE</strong>. The Default = <strong>TRUE</strong> so subcategories are displayed.") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>split</td>'
				.'<td>' . tra( "boolean") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Primarilly used when displaying multiple categories. Determins if the display will be split into more than one column. Passing any value in this parameter will make it <strong>FALSE</strong>. The Default = <strong>TRUE</strong> so multiple columns are displayed.") . '</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{CATEGORY id='1+2+3' types='blog+faq' sort='type_asc'}<br />"
		. tra("Example: ") . "{CATEGORY id='1+2+3' types='blog+faq+wiki' sort='name_asc' sub='No' split='No'}";
	return $help;
}

// Load Function
function data_category($data, $params) {  // Pre-Clyde Changes
// requires_pair was TRUE / No other changes were made to the Help
// The next 2 lines allow access to the $pluginParams given above and may be removed when no longer needed
	global $gLibertySystem; 
	$pluginParams = $gLibertySystem->mPlugins[PLUGIN_GUID_DATACATEGORY];
	$ret = 'The plugin <strong>"' . $pluginParams['tag'] . '"</strong> has not been completed as yet. ';
	return $ret;
}
}
/******************************************************************************
The code below is from the deprecated CATEGORY plugin. All comments and the help routines have been removed. - StarRider
function in_multi_array($needle, $haystack) {
	$in_multi_array = false;
	if (in_array($needle, $haystack)) {
		$in_multi_array = true;
	} else {
		while (list($tmpkey, $tmpval) = each($haystack)) {
			if (is_array($haystack[$tmpkey])) {
				if (in_multi_array($needle, $haystack[$tmpkey])) {
					$in_multi_array = true;
					break;
				}
			}
		}
	}
	return $in_multi_array;
}
function wikiplugin_category($data, $params) {
	global $gBitSmarty;
	global $package_categories;
	global $categlib;
	if (!is_object($categlib)) {
		require_once (CATEGORIES_PKG_PATH."categlib.php");
	}
	if ($package_categories != 'y') {
		return "<span class='warn'>" . tra("Categories are disabled"). "</span>";
	}
	extract ($params, EXTR_SKIP);
	// array for converting long type names (as in database) to short names (as used in plugin)
	$typetokens = array(
		"article" => "article",
		"blog" => "blog",
		"faq" => "faq",
		"file gallery" => "fgal",
		"image gallery" => "igal",
		"newsletter" => "newsletter",
		"poll" => "poll",
		"quiz" => "quiz",
		"survey" => "survey",
		"tracker" => "tracker",
		"wiki page" => "wiki"
	);
	// TODO: move this array to a lib
	// array for converting long type names to translatable headers (same strings as in application menu)
	$typetitles = array(
		"article" => "Articles",
		"blog" => "Blogs",
		"directory" => "Directory",
		"faq" => "FAQs",
		"file gallery" => "File Galleries",
		"forum" => "Forums",
		"image gallery" => "Image Gals",
		"newsletter" => "Newsletters",
		"poll" => "Polls",
		"quiz" => "Quizzes",
		"survey" => "Surveys",
		"tracker" => "Trackers",
		"wiki page" => "Wiki"
	);
	// string given back to caller
	$out = "";
	// TODO: use categ name instead of id (alternative)
	$id = (isset($id)) ? $id : 'current'; // use current category if none is given
	if ($id == 'current') {
		$obj_id = urldecode($_REQUEST['page']);
		$catids = $categlib->get_object_categories('wiki page', $obj_id);
	} else {
		$catids = explode("+", $id);      // create array of category ids to be displayed
	}
	// default setting for $split is 'yes'
	if (!(isset($split))) {
		$split = 'yes';
	} elseif ($split != 'y' and $split != 'yes' and $split != 'n' and $split != 'no' and $split != 'true' and $split != 'false') {
		$split = 'yes';
	}
	// array with items to be displayed
	$listcat = array();
	// title of categories
	$title = '';
	// TODO: allow 'find' and 'maxRecords'
	$find = "";
	$offset = 0;
	$maxRecords = 500;
	$count = 0;
	$sort = (isset($sort)) ? $sort : "name_asc";
	$types = (isset($types)) ? "+" . strtolower($types) : "*";
	$typesallowed = split("\+", $types); // create array of types the user allowed to be displayed
	foreach ($catids as $id) {
		// get data of category
		$cat = $categlib->get_category($id);
		// store name of category
		if ($count != 0) {
			$title .= "| <a href=\"".CATEGORIES_PKG_URL."index.php?parent_id=" . $id . "\">" . $cat['name'] . "</a> ";
		} else {
			$title .= "<a href=\"".CATEGORIES_PKG_URL."index.php?parent_id=" . $id . "\">" . $cat['name'] . "</a> ";
		}
		// keep track of how many categories there are for split mode off
		$count++;
		// check if sub=>true and get sub category data
		if (!(isset($sub))) {
			$sub = true;
		} elseif ($sub == 'no' or $sub == 'n' or $sub == 'false') {
			$sub = false;
		} else {
			$sub = true;
		}
		$subcategs = array();
		if ($sub) {
			$subcategs = $categlib->get_category_descendants($id);
		}
		// array with objects in category
		$objectcat = array();
		if ($sub) {
			// get all items for category and sub category
			$objectcat = $categlib->list_category_objects_deep($id, $offset, $maxRecords, $sort, $find);
		} else {
			// get all items for category
			$objectcat = $categlib->list_category_objects($id, $offset, $maxRecords, $sort, $find);
		}
		foreach ($objectcat["data"] as $obj) {
			$type = $obj["type"];
			// check if current type is in allowed type list: * = everything allowed
			if (($types == '*') || array_search($typetokens[strtolower($type)], $typesallowed)) {
				// remove duplicates in non-split mode
				if ($split == 'n' or $split == 'no' or $split == 'false') {
					if (!(in_multi_array($obj['name'], $listcat))) // TODO: check for name+type
						{
						$listcat[$typetitles["$type"]][] = $obj;
					}
				} else {
					$listcat[$typetitles["$type"]][] = $obj;
				}
			}
		}
		// split mode: appending onto $out each time
		if ($split == 'y' or $split == 'yes' or $split == 'true') {
			$gBitSmarty->assign("title", $title);
			$gBitSmarty->assign("listcat", $listcat);
			$out .= $gBitSmarty->fetch("bitpackage:wiki/simple_plugin.tpl");
			// reset array for next loop
			$listcat = array();
			// reset title
			$title = '';
			$count = 0;
		}
	}
	// non-split mode
	if ($split == 'n' or $split == 'no' or $split == 'false') {
		$gBitSmarty->assign("title", $title);
		$gBitSmarty->assign("listcat", $listcat);
		$out = $gBitSmarty->fetch("bitpackage:wiki/simple_plugin.tpl");
	}
	return '~np~'.$out.'~/np~';
}
*/
?>
