<?php
/**
 * @version  $Revision: 1.14 $
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
// $Id: data.blog.php,v 1.14 2007/08/22 22:43:38 wjames5 Exp $

/**
 * definitions
 */
global $gBitSystem, $gBitSmarty;
//it seems this is loaded before the package is activated.
//if( $gBitSystem->isPackageActive( 'blogs' ) ) { // Do not include this Plugin if the Package is not active
define( 'PLUGIN_GUID_DATABLOG', 'datablog' );
global $gLibertySystem;
$pluginParams = array (
	'tag' => 'BLOG',
	'auto_activate' => FALSE,
	'requires_pair' => FALSE,
	'load_function' => 'data_blog',
	'help_function' => 'data_blog_help',
	'title' => 'Blog',
	'help_page' => 'DataPluginBlog',
	'description' => tra( "This plugin will display several posts from a blog." ),
	'syntax' => "{BLOG id= user= max= format= }",
	'path' => LIBERTY_PKG_PATH.'plugins/data.blog.php',
	'security' => 'registered',
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
				.'<td>' . tra( "numeric") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Filters for the specified Blog by id") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>user</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The login name of the user who's posts are to be displayed. (Default = 3)") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>max</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The number of posts to be displayed. (Default = 3)") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
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
	global $gLibertySystem, $gBitSmarty, $gBitSystem, $gBitUser;
	$display_result = "";

	if ($gBitSystem->isPackageActive('blogs') && $gBitUser->hasPermission( 'p_blogs_view')) {
	// The next 2 lines allow access to the $pluginParams given above and may be removed when no longer needed
		$pluginParams = $gLibertySystem->mPlugins[PLUGIN_GUID_DATABLOG];
		
		require_once( BLOGS_PKG_PATH.'BitBlog.php');
		require_once( LIBERTY_PKG_PATH.'lookup_content_inc.php' );
		
		$module_params = $params;
		
		if (isset($module_params['id'])) {
			$gBitSmarty->assign('blog_id', $module_params['id']);
		}
		
		$blogPost = new BitBlogPost();
		
		$sortOptions = array(
							 "publish_date_desc",
							 "publish_date_asc",
							 "last_modified_asc",
							 "last_modified_desc",
							 "created_asc",
							 "created_desc",
							 "random",
							 );
		if( !empty( $module_params['sort_mode'] ) && in_array( $module_params['sort_mode'], $sortOptions ) ) {
			$sort_mode = $module_params['sort_mode'];
		} else {
			$sort_mode = 'publish_date_desc';
		}
		
		$getHash = Array();
		
		if ( isset($module_params['user']) ){ $getHash['user'] = $module_params['user']; }
		if ( isset($module_params['id']) ){ $getHash['blog_id'] = $module_params['id'];}
		if ( isset($module_params['group_id']) ){ $getHash['group_id'] = $module_params['group_id'];}
		
		$getHash['sort_mode']   = $sort_mode;
		$getHash['parse_data'] = TRUE;
		$getHash['max_records'] = empty($module_params['max']) ? 1 : $module_params['max'];
		$getHash['load_num_comments'] = TRUE;
		$getHash['page'] = (!empty($module_params['page']) ? $module_params['page'] : 1);
		$getHash['offset'] = (!empty($module_params['offset']) ? $module_params['offset'] : 0);
		$blogPosts = $blogPost->getList( $getHash );
		
		$display_format = empty($module_params['format']) ? 'simple_title_list' : $module_params['format'];
		
		switch( $display_format ) {
			case 'full':
				$display_result = '<div class="blogs">';				
				if ( $gBitSystem->isPackageActive( 'rss' ) ){
					if ( isset($module_params['user']) ){
						$rssUser = new BitUser();
						$rssUser->load(false, $module_params['user']);
						$rssUserId = $rssUser->getField('user_id');
					}
					$rssPath = BLOGS_PKG_URL.'blogs_rss.php?'
						.( isset($module_params['id']) ? 'blog_id='.$module_params['id'] : "" )
						.( (isset($module_params['id']) && isset($rssUserId))? "&": "")
						.( isset($rssUserId) ? 'user_id='.$rssUserId : "" )
						.( (isset($rssUserId) && isset($module_params['group_id']))? "&": "")
						.( isset($module_params['group_id']) ? 'group_id='.$module_params['group_id'] : "" );
					// something like this would be better, calling smarty directly so translation can also be called -wjames5
					// $rssIcon = smarty_function_biticon( array('ipackage'=>"rss", 'iname'="rss-16x16", 'iexplain'=>"RSS feed"), &$gBitSmarty );
					$display_result .= '<div class="floaticon"><a title="RSS feed" href="'.$rssPath.'"><img src="'.BIT_ROOT_URL.'rss/icons/rss-16x16.png" alt="RSS feed" title="RSS feed" class="icon" /></a></div>';
				}

				$gBitSmarty->assign( 'showDescriptionsOnly', TRUE );
				
				foreach( $blogPosts['data'] as $aPost ) {
					$gBitSmarty->assign('aPost', $aPost);
					$display_result .= $gBitSmarty->fetch( 'bitpackage:blogs/blog_list_post.tpl' );
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
	}
	else {
		$display_result = '<div class=error>'.tra('Blogs Package Deactivated.'). '</div>';
	}
	return $display_result;
}
//}
?>
