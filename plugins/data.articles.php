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
// | Author (TikiWiki): Gustavo Muslera <gmuslera@users.sourceforge.net>
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up) 
// | by: StarRider <starrrider@users.sourceforge.net>
// | Reworked from: wikiplugin_articles.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.articles.php,v 1.1.1.1.2.8 2005/08/03 07:43:55 lsces Exp $

/**
 * definitions
 */
global $gBitSystem;
if( $gBitSystem->isPackageActive( 'articles' ) ) { // Do not include this Plugin if the Package is not active
define( 'PLUGIN_GUID_DATAARTICLES', 'dataarticles' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'ARTICLES',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_articles',
						'title' => 'Articles<strong> - This plugin is not yet functional.</strong>', // Remove this line when the plugin becomes operational
//						'title' => 'Articles',                                                                             // and Remove the comment from the start of this line
						'help_page' => 'DataPluginArticles',
						'description' => tra("This plugin will display several Articles."),
						'help_function' => 'data_articles_help',
						'syntax' => "{ARTICLES max= topic= type= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAARTICLES, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAARTICLES );

// Help Routine
function data_articles_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>max</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The number of Articles to be displayed. (Default = 3)") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>topic</td>'
				.'<td>' . tra( "topic name") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Filters the Articles so that only the Topic specified is displayed") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>type</td>'
				.'<td>' . tra( "type name") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Filters the Articles so that only the Type specified is displayed") . '</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{ARTICLES max=5 topic='some_topic'}<br />"
		. tra("Example: ") . "{ARTICLES max=5 type='some_type'}";
	return $help;
}

// Executable Routine
function data_articles($data, $params) { // No change in the parameters with Clyde
// The next 2 lines allow access to the $pluginParams given above and may be removed when no longer needed
	global $gLibertySystem; 
	$pluginParams = $gLibertySystem->mPlugins[PLUGIN_GUID_DATAARTICLES];
	$ret = 'The plugin <strong>"' . $pluginParams['tag'] . '"</strong> has not been completed as yet. ';
	return $ret;
}
}
/******************************************************************************
The code below is from the deprecated ARTICLES plugin. All comments and the help routines have been removed. - StarRider



global $gBitSystem;
if( $gBitSystem->isPackageActive( 'articles' ) ) {
	include_once( ARTICLES_PKG_PATH . 'art_lib.php' );

	function wikiplugin_articles($data,$params) {
		global $gBitSmarty;
		global $artlib;
		global $feature_articles;
		global $bit_p_read_article;
		global $user;
		extract($params);
		if (($feature_articles !=  'y') || (!$gBitUser->hasPermission( 'bit_p_read_article' ))) {
	//		the feature is disabled or the user can't read articles
			return("");
		}
		if(!isset($max)) {$max='3';}
	// Addes filtering by topic if topic is passed
			if(!isset($topic)) {
			$topic='';
			} else {
			$topic = $artlib->get_topic_id($topic);
			}
		$now = date("U");
		$listpages = $artlib->list_articles(0, $max, 'publish_date_desc', '', $now, $user, '', $topic);
		for ($i = 0; $i < count($listpages["data"]); $i++) {
			$listpages["data"][$i]["parsed_heading"] = $artlib->parseData($listpages["data"][$i]["heading"]);
			//print_r($listpages["data"][$i]['title']);
		}
			$topics = $artlib->list_topics();
			$gBitSmarty->assign_by_ref('topics', $topics);
		// If there're more records then assign next_offset
		$gBitSmarty->assign_by_ref('listpages', $listpages["data"]);
		return "~np~ ".$gBitSmarty->fetch('bitpackage:articles/center_list_articles.tpl')." ~/np~";
	}
}
*/
?>
