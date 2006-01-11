<?php
// $id: data.example.php,v 1.4.2.9 2005/07/14 09:03:36 starrider Exp $
/**
 * assigned_modules
 *
 * @author     xing
 * @version    $Revision: 1.1.2.2 $
 * @package    liberty
 * @subpackage plugins_data
 * @copyright  Copyright (c) 2004, bitweaver.org
 */

/**
 * Setup Code
 */
define( 'PLUGIN_GUID_DATADIV', 'datadiv' );
global $gLibertySystem;
$pluginParams = array ( 
	'tag' => 'DIV',
	'auto_activate' => TRUE,
	'requires_pair' => TRUE,
	'load_function' => 'data_div',
	'title' => 'Div (DIV)',
	'help_page' => 'DataPluginDiv',
	'description' => tra( "This plugin allows you to easily create a div with a number of optional CSS parameters." ),
	'help_function' => 'data_div_help',
	'syntax' => "{div border='3px solid blue'}",
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATADIV, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATADIV );

function data_div_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>' . tra( "CSS rules" ) . '</td>'
				.'<td>' . tra( "string") . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "This can be any CSS style rule. e.g.: ") . "border='3px solid blue'" .'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>preset</td>'
				.'<td>' . tra( "string") . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "There are a few presets, which you can use to style with. Presets include: dark, orange, red, blue, centered.") .'</td>'
			.'</tr>'
 		.'</table>'
		. tra( "Example: " ) . "{div preset=centered border='3px solid blue'}";
	return $help;
}

function data_div( $data, $params ) {
	$style = '';
	foreach( $params as $key => $value ) {
		switch( $key ) {
			case 'preset':
				if( $value == 'dark' ) {
					$style .= 'background:#333;color:#ccc;border:2px solid #000;padding:0.5em 1em;margin:0.5em;';
				} elseif( $value == "orange" ) {
					$style .= 'background:#f60;color:#fff;border:2px solid #900;padding:0.5em 1em;margin:0.5em;';
				} elseif( $value == "red" ) {
					$style .= 'background:#eee;color:#900;border:2px solid #900;padding:0.5em 1em;margin:0.5em;';
				} elseif( $value == "blue" ) {
					$style .= 'background:#def;color:#009;border:2px solid #acf;padding:0.5em 1em;margin:0.5em;';
				} elseif( $value == "centered" ) {
					$style .= 'background:#eee;color:#333;border:2px solid #ddd;padding:0.5em 1em;margin:0.5em auto;width:50%;text-align:center;';
				}
				break;
			default:
				$style .= $key.':'.$value.';';
				break;
		}
	}
	return( '<div style="'.$style.'">'.$data.'</div>' );
}
?>
