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
// | Author (TikiWiki): dheltzel
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up) 
// | by: StarRider <starrrider@users.sourceforge.net>
// | Reworked from: wikiplugin_article.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.article.php,v 1.1.1.1.2.9 2006/06/11 02:08:33 wolff_borg Exp $

/**
 * definitions
 */
global $gBitSystem, $gBitSmarty;
if( $gBitSystem->isPackageActive( 'articles' ) ) { // Do not include this Plugin if the Package is not active
define( 'PLUGIN_GUID_DATAARTICLE', 'dataarticle' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'ARTICLE',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_article',
						'help_function' => 'data_article_help',
						'title' => 'Article',
						'help_page' => 'DataPluginArticle',
						'description' => tra("This plugin will display the data from a single field in the specified Article."),
						'syntax' => "{ARTICLE id= field= format=}",
						'plugin_type' => DATA_PLUGIN
					);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAARTICLE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAARTICLE );

// Help Routine
function data_article_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>id</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(required)") . '</td>'
				.'<td>' . tra( "The Id number of the Article. This value can easily be found in the URL when the Article is displayed.") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>field</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "This can be any field found in the") . ' tiki_articles ' . tra("table.") . '<br />'
				. tra("This includes (in order of usefulnes):") . '<strong>title, heading, body, author, size, reads, votes, points, type and rating</strong>' . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>format</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Specify format for article display - options: full, list (default)") . '</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{ARTICLE id=14 field='heading'}";
	return $help;
}

// Executable Routine
function data_article($data, $params) { // No change in the parameters with Clyde
	// The next 2 lines allow access to the $pluginParams given above and may be removed when no longer needed
	global $gLibertySystem, $gBitSmarty;
	$pluginParams = $gLibertySystem->mPlugins[PLUGIN_GUID_DATAARTICLE];

	require_once( ARTICLES_PKG_PATH.'BitArticle.php');
	require_once( LIBERTY_PKG_PATH.'lookup_content_inc.php' );

	$module_params = $params;
	$display_result = "<strong>Article ID not found!</strong>";

	$display_format = empty($module_params['format']) ? 'full' : $module_params['format'];

	if( !empty( $module_params['id'] ) ) {
		$articlesObject = new BitArticle($module_params['id']);
		$articlesObject->load();
		$article = $articlesObject->mInfo;

		if ($display_format == "list") {
			$gBitSmarty->assign( 'showDescriptionsOnly', TRUE );
			$getHash['article_id'] = $module_params['id'];
			$articles_results = $articlesObject->getList( $getHash );

			$display_result = '<div class="articles">';
			$gBitSmarty->assign( 'showDescriptionsOnly', TRUE );
			foreach( $articles_results['data'] as $article ) {
				$gBitSmarty->assign( 'article', $article );
				$display_result .= $gBitSmarty->fetch( 'bitpackage:articles/article_display.tpl' );
			}
			$display_result .= '</div>';
			$display_result = eregi_replace( "\n", "", $display_result );
		} else {
			$gBitSmarty->assign( 'article', $article );
			$display_result = $gBitSmarty->fetch( 'bitpackage:articles/article_display.tpl' );
		}
	}
	return $display_result;
}
}
?>
