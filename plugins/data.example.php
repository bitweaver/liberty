<?php
// $id:
/**
 * assigned_modules
 *
 * @author   StarRider starrrider@sourceforge.net
 * @version  $Revision: 1.4.2.8 $
 * @package  liberty
 * @subpackage plugins_data
 * @copyright Copyright (c) 2004, bitweaver.org
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * @license Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
 */
/******************
 * Initialization *
 ******************/
define( 'PLUGIN_GUID_DATAEXAMPLE', 'dataexample' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'EXAMPLE',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_example',
						'title' => 'Example',
						'help_page' => 'DataPluginExample',
						'description' => tra("This plugin is an example that does nothing but function as a template for the creation of new plugins."),
						'help_function' => 'data_example_help',
						'syntax' => "{EXAMPLE x1= xp2= }",
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
 		.'</table>'
		. tra("Example: ") . "{EXAMPLE ' }<br />"
		. tra("This will display");
	return $help;
}
/****************
* Load Function *
 ****************/
function data_example($data, $params) {
	extract ($params);
	$ret = ' ''
	
	return $ret;
}
*/
?>
