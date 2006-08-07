<?php
/**
 * @version  $Revision: 1.13 $
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
// | Author: StarRider starrrider@sourceforge.net
// +----------------------------------------------------------------------+
// $id: data.dropdown.php,v 1.8 2005/07/14 09:03:36 starrider Exp $

/**
 * Initialization
 */
global $gLibertySystem;
define( 'PLUGIN_GUID_DATADROPDOWN', 'datadropdown' );
$pluginParams = array (
	'tag' => 'DD',
	'auto_activate' => TRUE,
	'requires_pair' => TRUE,
	'load_function' => 'data_dropdown',
	'title' => 'DropDown',
	'help_page' => 'DataPluginDropDown',
	'description' => tra("This plugin creates a expandable box of text.. All text should be entered between the ") . "{DD} " . tra("blocks."),
	'help_function' => 'data_dropdown_help',
	'syntax' => "{DD title= width= }" . tra("Text in the Drop-Down box.") . "{DD}",
	'path' => LIBERTY_PKG_PATH.'plugins/data.dropdown.php',
	'security' => 'registered',
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATADROPDOWN, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATADROPDOWN );

/**
 * Help Function
 */
function data_dropdown_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>title</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( 'String used as the link to Expand / Contract the text box. <br />The Default = <strong>"For More Information"</strong></td>')
			.'</tr>'
			.'<tr class="even">'
				.'<td>width</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Controls the width of the title area in pixels. This is a percentage value but the % character should not be added.<br />The Default = ") . '<strong>20</strong></td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . '{DD}' . tra("Text in the Drop-Down box.") . '{DD}<br />'
		. tra("Example: ") . "{DD title='" . tra("Explaining the Lines #1 #3 &amp; #7'} Text in the Drop-Down box") . '{DD}';
	return $help;
}

/**
* Load Function
*/
function data_dropdown($data, $params) {
	extract ($params, EXTR_SKIP);
	$title = (isset($title)) ? $title : tra( 'For More Information, click me.' );
	$id = 'dropdown'.(microtime() * 1000000);
	$ret = 	'<div style="text-align:center;font-weight:bold;">'
				.'<a title="Click to Expand or Contract" href="javascript:flip(\''.$id.'\')">'.$title.'</a>'
			.'</div>'
			.'<div class="help box" style="display:none" id="'.$id.'">'.$data.'</div>';
	return $ret;
}
?>
