<?php
/**
 * @version  $Revision: 1.1.2.3 $
 * @package  liberty
 * @subpackage plugins_data
 */
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See copyright.txt for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Author (TikiWiki): Gustavo Muslera <gmuslera@users.sourceforge.net>
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up) 
// | by: wjames5
// | Reworked from: data.articles.php from wikiplugin_articles.php
// +----------------------------------------------------------------------+
// $Id: data.blog.php,v 1.1.2.3 2006/06/14 14:48:10 mej Exp $

/**
 * definitions
 */
global $gBitSystem, $gBitSmarty;
if( $gBitSystem->isPackageActive( 'blogs' ) ) { // Do not include this Plugin if the Package is not active
define( 'PLUGIN_GUID_DATABLOG', 'datablog' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'BLOG',
						'auto_activate' => TRUE,
						'requires_pair' => FALSE,
						'load_function' => 'data_blog',
						'help_function' => 'data_blog_help',
						'title' => 'Blog',
						'help_page' => 'DataPluginBlog',
						'description' => tra( "This plugin will display several posts from a blog." ),
						'syntax' => "{BLOG id= max= format= }",
						'plugin_type' => DATA_PLUGIN
					);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATABLOG, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATABLOG );

// Help Routine
function data_blog_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>id</td>'
				.'<td>' . tra( "topic name") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Filters for the specified Blog by id") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>max</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The number of posts to be displayed. (Default = 3)") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>format</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Specify format for posts display - options: full, list (default)") . '</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{BLOG id=2 max=5 format='full'}<br />"
		. tra("Example: ") . "{BLOG id=5 format='list'}";
	return $help;
}

// Executable Routine
function data_blog($data, $params) { // No change in the parameters with Clyde
	// The next 2 lines allow access to the $pluginParams given above and may be removed when no longer needed
	global $gLibertySystem, $gBitSmarty;
	$pluginParams = $gLibertySystem->mPlugins[PLUGIN_GUID_DATABLOG];

	require_once( BLOGS_PKG_PATH.'BitBlog.php');
	require_once( LIBERTY_PKG_PATH.'lookup_content_inc.php' );

	$module_params = $params;
	
/*	$gBitSystem->verifyPermission( 'bit_p_read_blog' ); */

	$gBitSmarty->assign('blog_id', $module_params['id']);

  $blogPost = new BitBlogPost();
	
	$sortOptions = array(
		"last_modified_asc",
		"last_modified_desc",
		"created_asc",
		"created_desc",
	);
	if( !empty( $module_params['sort_mode'] ) && in_array( $module_params['sort_mode'], $sortOptions ) ) {
		$sort_mode = $module_params['sort_mode'];
	} else {
		$sort_mode = 'last_modified_desc';
	}	
		
	$getHash = Array();

	$getHash['blog_id'] = empty($module_params['id']) ? 1 : $module_params['id'];
	$getHash['sort_mode']   = $sort_mode;
	$getHash['parse_data'] = TRUE;
	$getHash['max_records'] = empty($module_params['max']) ? 1 : $module_params['max'];
	$getHash['load_num_comments'] = TRUE;
	$getHash['page'] = (!empty($module_params['page']) ? $module_params['page'] : 1);
	$getHash['offset'] = (!empty($module_params['offset']) ? $module_params['offset'] : 0);
	$blogPosts = $blogPost->getList( $getHash );
		
	$display_format = empty($module_params['format']) ? 'simple_title_list' : $module_params['format'];

	$display_result = "";	
	switch( $display_format ) {
		case 'full':
			$display_result = '<div class="blogs">';
			$gBitSmarty->assign( 'showDescriptionsOnly', TRUE );

			foreach( $blogPosts['data'] as $aPost ) {
				$gBitSmarty->assign('aPost', $aPost);
				$display_result .= $gBitSmarty->fetch( 'bitpackage:blogs/blog_list_post.tpl' );
/*
				$gBitSmarty->assign( 'article', $article );
				$display_result .= $gBitSmarty->fetch( 'bitpackage:articles/article_display.tpl' );
*/
			}
			

			$display_result .= '</div>';
			$display_result = eregi_replace( "\n", "", $display_result );
			break;
		case 'list':
		default:
			$display_result = "<ul>";
			foreach( $blogPosts['data'] as $post ) {
				$link = $blogPost->getDisplayLink( $post['title'], $post );
				$display_result .= "<li>$link</li>\n";
			}
			$display_result .= "</ul>\n";
			break;
	}
	return $display_result;
}
}
?>
