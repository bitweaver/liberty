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
// | Author: StarRider <starrrider@sourceforge.net>
// | Wrote it but didn't think of it :-)
// +----------------------------------------------------------------------+
// $Id: data.comment.php,v 1.1.2.1 2005/06/28 13:42:43 starrrider Exp $
// Initialization
define( 'PLUGIN_GUID_COMMENT', 'comment' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'COMMENT',
						'auto_activate' => TRUE,
						'requires_pair' => TRUE,
						'load_function' => 'data_comment',
						'title' => 'Comment',
						'help_page' => 'DataPluginComment',
						'description' => tra("This plugin allows comments (Text that is not displayed) to be stored."),
						'help_function' => 'data__comment_help',
						'syntax' => "{COMMENT}",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_COMMENT, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_COMMENT );

// Help Function
function data_comment_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>' . tra("This plugin uses no parameters. All data located between the") . ' <strong>{COMMENT}</strong> ' 
				.tra("is simply not displayed.") . '</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{COMMENT}" . tra("Anything a user wants included but not displayed.") . "{COMMENT}";
	return $help;
}

// Load Function
function data_comment($data, $params) {
	return ' ';
}
?>
