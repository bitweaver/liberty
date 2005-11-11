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
// | Author (TikiWiki): dheltzel
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up) 
// | by: StarRider <starrrider@users.sourceforge.net>
// | Reworked from: wikiplugin_article.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.article.php,v 1.1.1.1.2.8 2005/11/11 22:04:08 mej Exp $

/**
 * definitions
 */
global $gBitSystem;
if( $gBitSystem->isPackageActive( 'articles' ) ) { // Do not include this Plugin if the Package is not active
define( 'PLUGIN_GUID_DATAARTICLE', 'dataarticle' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'ARTICLE',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_article',
						'title' => 'Article<strong> - This plugin is not yet functional.</strong>', // Remove this line when the plugin
//						'title' => 'Article',                                                                             // and Remove the comment from the start of this line
						'help_page' => 'DataPluginArticle',
						'description' => tra("This plugin will display the data from a single field in the specified Article."),
						'help_function' => 'data_article_help',
						'syntax' => "{ARTICLE id= field=}",
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
		.'</table>'
		. tra("Example: ") . "{ARTICLE id=14 field='heading'}";
	return $help;
}

// Executable Routine
function data_article($data, $params) { // No change in the parameters with Clyde
// The next 2 lines allow access to the $pluginParams given above and may be removed when no longer needed
	global $gLibertySystem; 
	$pluginParams = $gLibertySystem->mPlugins[PLUGIN_GUID_DATAARTICLE];
	$ret = 'The plugin <strong>"' . $pluginParams['tag'] . '"</strong> has not been completed as yet. ';
	return $ret;
}
}
/******************************************************************************
The code below is from the deprecated ARTICLE plugin. All comments and the help routines have been removed. - StarRider

global $gBitSystem;
if( $gBitSystem->isPackageActive( 'articles' ) ) {
	include_once( ARTICLES_PKG_PATH . 'art_lib.php' );

	function wikiplugin_article($data, $params) {
		global $artlib;
		extract ($params, EXTR_SKIP);
		if (!isset($id)) {
			return ("<b>The plugin Article needs an article ID to function.</b><br/>");
		}
		if (!isset($field)) {
			$field = 'heading';
		}
		$article_data = $artlib->get_article($id);
		return $article_data[$field];
	}
}
*/
?>
