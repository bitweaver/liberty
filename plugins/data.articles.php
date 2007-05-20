<?php
/**
 * @version  $Revision: 1.10 $
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
// $Id: data.articles.php,v 1.10 2007/05/20 22:38:03 nickpalmer Exp $

/**
 * definitions
 */
global $gBitSystem, $gBitSmarty;
define( 'PLUGIN_GUID_DATAARTICLES', 'dataarticles' );
global $gLibertySystem;
$pluginParams = array (
	'tag' => 'ARTICLES',
	'auto_activate' => TRUE,
	'requires_pair' => FALSE,
	'load_function' => 'data_articles',
	'help_function' => 'data_articles_help',
	'title' => 'Articles',
	'help_page' => 'DataPluginArticles',
	'description' => tra( "This plugin will display several Articles." ),
	'syntax' => "{ARTICLES max= topic= type= }",
	'path' => LIBERTY_PKG_PATH.'plugins/data.articles.php',
	'security' => 'registered',
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
			.'<tr class="even">'
				.'<td>format</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Specify format for article display - options: full, list (default)") . '</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{ARTICLES max=5 topic='some_topic'}<br />"
		. tra("Example: ") . "{ARTICLES max=5 type='some_type'}";
	return $help;
}

// Executable Routine
function data_articles($data, $params) { // No change in the parameters with Clyde
  global $gLibertySystem, $gBitSmarty, $gBitSystem;
  if( $gBitSystem->isPackageActive( 'articles' ) ) { // Do not include this Plugin if the Package is not active
	// The next 2 lines allow access to the $pluginParams given above and may be removed when no longer needed
	$pluginParams = $gLibertySystem->mPlugins[PLUGIN_GUID_DATAARTICLES];

	require_once( ARTICLES_PKG_PATH.'BitArticle.php');
	require_once( LIBERTY_PKG_PATH.'lookup_content_inc.php' );

	$module_params = $params;
	$articlesObject = new BitArticle();
	$stati = array( 'pending', 'approved' );
	if( !empty( $module_params['status'] ) && in_array( $module_params['status'], $stati ) ) {
		$status_id = constant( 'ARTICLE_STATUS_'.strtoupper( $module_params['status'] ) );
	} else {
		$status_id = ARTICLE_STATUS_APPROVED;
	}

	$sortOptions = array(
		"last_modified_asc",
		"last_modified_desc",
		"created_asc",
		"created_desc",
		"random",
	);
	if( !empty( $module_params['sort_mode'] ) && in_array( $module_params['sort_mode'], $sortOptions ) ) {
		$sort_mode = $module_params['sort_mode'];
	} else {
		$sort_mode = 'last_modified_desc';
	}

	$getHash = Array();
	$getHash['status_id']     = $status_id;
	$getHash['sort_mode']     = $sort_mode;
	$getHash['max_records']   = empty($module_params['max']) ? 1 : $module_params['max'];
	if (isset($module_params['topic'])) {
	  $getHash['topic']         = !empty( $module_params['topic'] ) ? $module_params['topic'] : NULL;
	}
	if (isset($module_params['topic_id'])) {
	  $getHash['topic_id'] = $module_params['topic_id'];
	}
	$articles_results = $articlesObject->getList( $getHash );

	$display_format = empty($module_params['format']) ? 'simple_title_list' : $module_params['format'];
	$display_result = "";

	switch( $display_format ) {
		case 'full':
			$display_result = '<div class="articles">';
			$gBitSmarty->assign( 'showDescriptionsOnly', TRUE );
			foreach( $articles_results['data'] as $article ) {
				$gBitSmarty->assign( 'article', $article );
				$display_result .= $gBitSmarty->fetch( 'bitpackage:articles/article_display.tpl' );
			}
			$display_result .= '</div>';
			$display_result = eregi_replace( "\n", "", $display_result );
			break;
		case 'list':
		default:
			$display_result = "<ul>";
			foreach( $articles_results['data'] as $article ) {
				$link = $articlesObject->getdisplaylink( $article['title'], $article );
				$display_result .= "<li>$link</li>\n";
			}
			$display_result .= "</ul>\n";
			break;
	}
	return $display_result;
  }
  else {
    return "<div class=error>The articles package is not active.</div>";
  }
}
?>
