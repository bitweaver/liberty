<?php
/**
 * @version  $Revision: 1.15 $
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
// +----------------------------------------------------------------------+
// $Id: data.rss.php,v 1.15 2007/08/01 12:01:51 wjames5 Exp $

/**
 * definitions
 */
global $gLibertySystem;
define( 'PLUGIN_GUID_DATARSS', 'datarss' );
global $gLibertySystem;
$pluginParams = array (
	'tag'           => 'rss',
	'auto_activate' => FALSE,
	'requires_pair' => FALSE,
	'load_function' => 'rss_parse_data',
	'title'         => 'RSS Feed',
	'help_page'     => 'DataPluginRSS',
	'description'   => tra("Display RSS Feeds"),
	'help_function' => 'rss_extended_help',
	'syntax'        => "{RSS id= max= }",
	'path'          => LIBERTY_PKG_PATH.'plugins/data.rss.php',
	'security'      => 'registered',
	'plugin_type'   => DATA_PLUGIN,
	'biticon'       => '{biticon ilocation=quicktag ipackage=rss iname=rss-16x16 iexplain="RSS Feed"}',
	'taginsert'     => '{rss}'
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATARSS, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATARSS );

function rss_extended_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>id</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(mandatory)") . '</td>'
				.'<td>' . tra( "IDs of the RSS-feeds to process. Separate multiple ids with \",\"") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>max</td>'
				.'<td>' . tra( "integer") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Number of entries to be displayed from given RSS Feed, prefixed with publication date.") . '</td>'
			.'</tr>'
		.'</table>'
	;
	return $help;
}

function rss_parse_data( $data, $params ) {
	$repl = '';
	if( !empty( $params['id'] ) ) {
		global $rsslib;
		require_once( RSS_PKG_PATH.'rss_lib.php' );

		$max = !empty( $params['max'] ) ? $params['max'] : 99;
		
		if ( $items = $rsslib->parse_feeds( $params ) ){
			//if we want short descriptions get them
			$shortdescs = Array();	
			if ( !empty($module_params['desc_length']) && is_numeric($module_params['desc_length']) && !empty($items)){
				$shortdescs = $rsslib->get_short_descs( $items, $module_params['desc_length'] );
			}
		}		
		
		$repl = '<ul class="rsslist">';

		for ($j = 0; $j < count($items) && $j < $max; $j++) {
			$repl .= '<li><a href="' . $items[$j]->get_permalink() . '">' . $items[$j]->get_title() . '</a>';
			if ($items[$j]->get_date('j M Y | g:i a T') <> '') {
				$repl .= ' <small>('.$items[$j]->get_date('j M Y | g:i a T').')</small>';
			}
			$repl .= '</li>';
		}

		$repl .= '</ul>';
	} else {
		$repl = 'You have not provided an id or feed to process.';
	}

	return $repl;
}

?>
