<?php
/**
 * @version  $Revision: 1.11 $
 * @package  liberty
 * @subpackage plugins_data
 */

global $gLibertySystem;
define( 'PLUGIN_GUID_DATACOMMENT', 'datacomment' );
global $gLibertySystem;
$pluginParams = array (
	'tag'           => 'COMMENT',
	'auto_activate' => FALSE,
	'requires_pair' => TRUE,
	'load_function' => 'data_comment',
	'title'         => 'Comment',
	'help_page'     => 'DataPluginComment',
	'description'   => tra("This plugin allows Comments (Text that will not be displayed) to be added to a page."),
	'help_function' => 'data__comment_help',
	'syntax'        => "{comment}Data Not Displayed{/comment}",
	'plugin_type'   => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATACOMMENT, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATACOMMENT );

/**
 * data_comment_help 
 * 
 * @access public
 * @return string help
 */
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
		. tra("Example: ") . "{COMMENT}" . tra("Everything in here is not displayed.") . "{/COMMENT}";
	return $help;
}

/**
 * data_comment 
 * 
 * @access public
 * @return string ' '
 */
function data_comment( $pData, $pParams ) {
	return ' ';
}
?>
