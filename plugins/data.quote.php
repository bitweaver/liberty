<?php
/**
 * @version  $Revision: 1.2 $
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
// $Id: data.renderer.php,v 1.2 2006/07/24 17:50:17 sylvieg Exp $

/**
 * definitions
 */
global $gBitSystem, $gBitSmarty;
define( 'PLUGIN_GUID_DATAQUOTE', 'dataquote' );
global $gLibertySystem;
$pluginParams = array (
	'tag' => 'quote',
	'auto_activate' => TRUE,
	'requires_pair' => TRUE,
	'load_function' => 'data_quote',
	'help_function' => 'data_quote_help',
	'title' => 'Quote',
	'help_page' => 'DataPluginQuote',
	'description' => tra( "This plugin will render the given content as discribed by the content_type given" ),
	'syntax' => "{quote format_guid= user= comment_id= }.. content ..{/quote}",
	'path' => LIBERTY_PKG_PATH.'plugins/data.quote.php',
	'security' => 'registered',
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAQUOTE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAQUOTE );

// Help Routine
function data_quote_help() {
	$help ="<table class=\"data help\">
			<tr>
				<th>" . tra( "Key" ) . "</th>
				<th>" . tra( "Type" ) . "</th>
				<th>" . tra( "Comments" ) . "</th>
			</tr>
			<tr class=\"even\">
				<td>comment_id</td>
				<td>" . tra( "comment_id") . '<br />' . tra("(optional)") . "</td>
				<td>" . tra( "specify the comment_id of the comment being quoted") . "</td>
			</tr>
			<tr class=\"odd\">
				<td>user</td>
				<td>" . tra( "user") . '<br />' . tra("(optional)") . "</td>
				<td>" . tra( "specify the user whose comemnt is being quoted") . "</td>
			</tr>
			<tr class=\"even\">
				<td>format_guid</td>
				<td>" . tra( "string") . '<br />' . tra("(required)") . "</td>
				<td>" . tra( "Specify what renderer should be used to render the contents") . "</td>
			</tr>'
		</table>".
	tra("Example: ") . '{quote format_guid="tikiwiki" comment_id="7" user="user"} ... {/quote}<br />';
	return $help;
}

// Executable Routine
function data_quote($data, $params) {
	global $gLibertySystem, $gBitSmarty;

	$rendererHash=array();
	$rendererHash['content_id']=0;
	$rendererHash['format_guid']=$params['format_guid'];
	$rendererHash['data'] = trim($data);
	$formatGuid=$rendererHash['format_guid'];
	$ret = "";
	if( $func = $gLibertySystem->getPluginFunction( $formatGuid, 'load_function' ) ) {
		$ret = $func( $rendererHash, $this );
	}

	$extra = $cite = '';
	$user = $params['user'];

	if (!empty($params['comment_id'])) {
		$extra.="In ";
		if (ACTIVE_PACKAGE == 'bitboards') {
			$c = new BitBoardPost($params['comment_id']);
		} else {
			$c = new LibertyComment($params['comment_id']);
		}
		if (empty($c->mInfo['title'])) {
			$c->mInfo['title']="#".$c->mCommentId;
		}

		$citeurl = $c->getDisplayUrl();
		$cite = ' cite="'.$citeurl.'"';

		$extra.="<a href=\"";
		$extra.=$citeurl;
		$extra.="\" title=\"";
		$extra.=$c->mInfo['title'];
		$extra.="\">";
		$extra.=$c->mInfo['title'];
		$extra.="</a>";
		$extra.=" (";
		$extra.=reltime($c->mInfo['created'],'short');
		$extra.=") ";
		if (empty($user)) {
			$user = $c->mInfo['login'];
		}
	}

	$display_user=$user;
	if (!empty($user)) {
		$u = new BitUser();
		$u->load(true,$user);
		$display_user = "<a href=\"".$u->getDisplayUrl()."\" title=\"".$u->mInfo['display_name']."\">".$u->mInfo['display_name']."</a>";
	}

	$display_result = "<div class=\"quote\">";

	if (!empty($display_user)) {
		$display_result .="<span class=\"quote-title\">{$extra}{$display_user} ".tra( "wrote" ).":</span>";
	}

	$display_result .="<blockquote{$cite}>$ret</blockquote></div>";

	return $display_result;
}
?>
