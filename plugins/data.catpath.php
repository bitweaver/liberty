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
// | Author (TikiWiki): TeeDog <teedog@users.sourceforge.net>
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up) 
// | by: StarRider <starrrider@users.sourceforge.net>
// | Reworked from: wikiplugin_catpath.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.catpath.php,v 1.1.1.1.2.9 2005/11/11 22:04:09 mej Exp $

/**
 * definitions
 */
global $gBitSystem;
if( $gBitSystem->isPackageActive( 'categories' ) ) { // Do not include this Plugin if the Package is not active
define( 'PLUGIN_GUID_DATACATPATH', 'datacatpath' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'CATPATH',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_catpath',
						'title' => 'CatPath<strong> - This plugin is not yet functional.</strong>', // Remove this line when the plugin becomes operational
//						'title' => 'CatPath',																						  // and Remove the comment from the start of this line
						'help_page' => 'DataPluginCatPath',
						'description' => tra("This plugin insert the full category path for each category that the page belongs to."),
						'help_function' => 'data_catpath_help',
						'syntax' => "{CATPATH divider= top= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATACATPATH, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATACATPATH );

// Help Function
function data_catpath_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>divider</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra("A character or string used to separate the categories. The default is <strong>></strong>.") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>top</td>'
				.'<td>' . tra( "boolean") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Determins if the TOP category is displayed or not. Passing any value in this parameter will make it <strong>TRUE</strong>. The Default = <strong>FALSE</strong> so the TOP category will not be displayed") . '</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{CATPATH divider='->' top='yes' }";
	return $help;
}

// Load Function
function data_catpath($data, $params) { // Pre-Clyde Changes
// requires_pair was TRUE / No other changes were made to the Help
// The next 2 lines allow access to the $pluginParams given above and may be removed when no longer needed
	global $gLibertySystem; 
	$pluginParams = $gLibertySystem->mPlugins[PLUGIN_GUID_DATACATPATH];
	$ret = 'The plugin <strong>"' . $pluginParams['tag'] . '"</strong> has not been completed as yet. ';
	return $ret;
}
}
/******************************************************************************
The code below is from the deprecated CATPATH plugin. All comments and the help routines have been removed. - StarRider
function wikiplugin_catpath($data, $params) {
	global $gBitSmarty;
	global $gBitSystem;
	global $package_categories;
	global $categlib;
	if (!is_object($categlib)) {
		require_once (CATEGORIES_PKG_PATH."categlib.php");
	}
	if ($package_categories != 'y') {
		return "<span class='warn'>" . tra("Categories are disabled"). "</span>";
	}
	extract ($params, EXTR_SKIP);
	// default divider is '>'
	if (!(isset($divider))) {
		$divider = '>';
	}
	// default setting for top is 'no'
	if (!(isset($top))) {
		$top = 'no';
	} elseif ($top != 'y' and $top != 'yes' and $top != 'n' and $top != 'no') {
		$top = 'no';
	}
	$obj_id = urldecode($_REQUEST['page']);
	$cats = $categlib->get_object_categories('wiki page', $obj_id);
	$catpath = '';
	foreach ($cats as $category_id) {
		$catpath .= '<span class="categpath">';
		// Display TOP on each line if wanted
		if ($top == 'yes' or $top == 'y') {
			$catpath .= '<a class="categpath" href="'.CATEGORIES_PKG_URL.'index.php?parent_id=0">TOP</a> ' . $divider . ' ';
		}
		$path = '';
		$info = $categlib->get_category($category_id);
		$path
			= '<a class="categpath" href="'.CATEGORIES_PKG_URL.'index.php?parent_id=' . $info["category_id"] . '">' . $info["name"] . '</a>';
		while ($info["parent_id"] != 0) {
			$info = $categlib->get_category($info["parent_id"]);
			$path = '<a class="categpath" href="'.CATEGORIES_PKG_URL.'.php?parent_id=' . $info["category_id"] . '">' . $info["name"] . '</a> ' . $divider . ' ' . $path;
		}
		$catpath .= $path . '</span><br/>';
	}
	return $catpath;
}
*/
?>
