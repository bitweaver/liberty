<?php
// $Id: data.comment.php,v 1.1.2.5 2005/08/03 07:43:55 lsces Exp $
/**
 * assigned_modules
 *
 * @author   StarRider <starrrider@sourceforge.net>
 * @version  $Revision: 1.1.2.5 $
 * @package  liberty
 * @subpackage plugins_data
 * @copyright Copyright (c) 2004, bitweaver.org
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * @license Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
 */
/******************
 * Initialization *
 ******************/
global $gLibertySystem;
define( 'PLUGIN_GUID_COMMENT', 'comment' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'COMMENT',
						'auto_activate' => TRUE,
						'requires_pair' => TRUE,
						'load_function' => 'data_comment',
						'title' => 'Comment',
						'help_page' => 'DataPluginComment',
						'description' => tra("This plugin allows Comments (Text that will not be displayed) to be added to a page."),
						'help_function' => 'data__comment_help',
						'syntax' => "{COMMENT}Data Not Displayed{COMMENT}",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_COMMENT, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_COMMENT );
/*****************
 * Help Function *
 *****************/
function data_comment_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>' . tra("This plugin uses no parameters. Anything located between the two")
				. ' <strong>{COMMENT}</strong> ' . tra("Blocks is not displayed.") . '</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{COMMENT}" . tra("Everything in here is not displayed.") . "{COMMENT}";
	return $help;
}
/****************
* Load Function *
 ****************/
function data_comment($data, $params) {
	return ' ';
}
?>
