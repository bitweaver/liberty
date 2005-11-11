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
// | Reworked from: wikiplugin_catorphans.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.catorphans.php,v 1.1.1.1.2.9 2005/11/11 22:04:08 mej Exp $

/**
 * definitions
 */
global $gBitSystem;
if( $gBitSystem->isPackageActive( 'categories' ) ) { // Do not include this Plugin if the Package is not active
define( 'PLUGIN_GUID_DATACATORPHANS', 'datacatorphans' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'CATORPHANS',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_catorphans',
						'title' => 'CatOrphans<strong> - This plugin is not yet functional.</strong>', // Remove this line when the plugin becomes operational
//						'title' => 'CatOrphans',                                                                             // and Remove the comment from the start of this line
						'help_page' => 'DataPluginCatOrphans',
						'description' => tra("Creates a listing of bitweaver objects that have not been categorized."),
						'help_function' => 'data_catorphans_help',
						'syntax' => "{CATORPHANS objects= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATACATORPHANS, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATACATORPHANS );

// Help Function
function data_catorphans_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>object</td>' 
				.'<td>' . tra( "object(s)") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra("Most bitweaver Objects can be selected, including") . " <strong>article, blog, faq, fgal, igal, newsletter, poll, quiz, survey, tracker, & wiki</strong>. " . tra("Multiple objects can be entered bu using the character + between object names, like this") . " <strong>blog+faq</strong>. " . tra(". The default is <strong>wiki</strong> objects.") . '</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{CATORPHANS objects='wiki+blog+faq'}";
	return $help;
}

// Load Function
function data_catorphans($data, $params) { // Pre-Clyde Changes
// requires_pair was TRUE / No other changes were made to the Help
// The next 2 lines allow access to the $pluginParams given above and may be removed when no longer needed
	global $gLibertySystem; 
	$pluginParams = $gLibertySystem->mPlugins[PLUGIN_GUID_DATACATORPHANS];
	$ret = 'The plugin <strong>"' . $pluginParams['tag'] . '"</strong> has not been completed as yet. ';
	return $ret;
}
}
/******************************************************************************
The code below is from the deprecated CATORPHANS plugin. All comments and the help routines have been removed. - StarRider

Note: I changed the seperator from | to +

require_once( WIKI_PKG_PATH.'BitPage.php' );
function wikiplugin_catorphans($data, $params) {
	global $gBitSmarty;
	global $wikilib;
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
	// default object is 'wiki'
	if (!isset($objects)or $objects != 'wiki') {
		$objects = 'wiki';
	}
	$orphans = '';
	// currently only supports display of wiki pages
	if ($objects == 'wiki') {
		$listpages = $wikilib->getList(0, -1, 'title_asc', '');
		foreach ($listpages['data'] as $page) {
			if (!$categlib->is_categorized('wiki page', $page['title'])) {
				//				$orphans .= '<a href="'.WIKI_PKG_URL.'index.php?page='.$page['title'].'">'.$page['title'].'</a><br />';
				$orphans .= '((' . $page['title'] . '))<br />';
			}
		}
	}
	return $orphans;
}
*/
?>
