<?php
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
// $Id: data.rss.php,v 1.1.1.1.2.2 2005/06/25 09:29:25 squareing Exp $
// Initialization
global $gLibertySystem;

define( 'PLUGIN_GUID_RSS', 'datarss' );

global $gLibertySystem;
$pluginParams = array ( 'tag' => 'RSS',
						'auto_activate' => TRUE,
						'requires_pair' => FALSE,
						'load_function' => 'rss_parse_data',
						'title' => 'RSS Feed',
						'description' => tra("Display RSS Feeds"),
						'help_function' => 'rss_extended_help',
						'syntax' => "{RSS id= max= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_RSS, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_RSS );

function rss_extended_help() {
	return 'NO HELP WRITTEN FOR {RSS}';
}

function rss_parse_data( $data, $params ) {
	$repl = '';
	if( !empty( $params['id'] ) ) {
		global $rsslib;
		require_once( RSS_PKG_PATH.'rss_lib.php' );

		$max = !empty( $params['max'] ) ? $params['max'] : 99;

		$rssdata = $rsslib->get_rss_module_content( $params['id'] );
		$items = $rsslib->parse_rss_data( $rssdata, $params['id'] );

		$repl = '<ul class="rsslist">';

		for ($j = 1; $j < count($items) && $j < $max; $j++) {
			$repl .= '<li><a href="' . $items[$j]["link"] . '">' . $items[$j]["title"] . '</a>';
			if ($items[$j]["pubdate"] <> '') {
				$repl .= ' <small>('.$items[$j]["pubdate"].')</small>'; 
			}
			$repl .= '</li>';
		}

		$repl .= '</ul>';
	}
	return $repl;
}

?>
