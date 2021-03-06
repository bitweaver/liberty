<?php
/**
 * @version  $Revision$
 * @package  liberty
 * @subpackage plugins_data
 */
// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See below for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Author: StarRider starrrider@sourceforge.net
// +----------------------------------------------------------------------+
// $id: data.example.php,v 1.4.2.9 2005/07/14 09:03:36 starrider Exp $

/******************
 * Initialization *
 ******************/
define( 'PLUGIN_GUID_DATAEXAMPLE', 'dataexample' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'EXAM',
	'auto_activate' => FALSE,
	'requires_pair' => FALSE,
	'load_function' => 'data_example',
	'title' => 'Example',
	'help_page' => 'DataPluginExample',
	'description' => tra("This Plugin is an Example that does nothing. It functions as a template for the creation of new plugins."),
	'help_function' => 'data_example_help',
	'syntax' => "{EXAM x1= x2= }",
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAEXAMPLE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAEXAMPLE );
/*****************
 * Help Function *
 *****************/
function data_example_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>x1</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Specifies something / probably to be displayed.")
					.'<br />' . tra( "The Default = <strong>Sorry About That</strong>")
				.'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>XXX</td>'
				.'<td>' . tra( "number") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Specifies something / probably to be displayed.")
					.'<br />' . tra( "The Default =") . ' <strong>3</strong> ' . tra( "Which means - What")
				.'</td>'
			.'</tr>'
 		.'</table>'
		. tra("Example: ") . "{EXAM x1=' ' x2=5 }<br />"
		. tra("This will display");
	return $help;
}
/****************
* Load Function *
 ****************/
function data_example($data, $params) {
	extract ($params, EXTR_SKIP);
	$ret = ' ';

	return $ret;
}
?>
