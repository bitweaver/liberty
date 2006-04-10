<?php
/**
 * @version  $Revision: 1.6 $
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
// | Author: xing
// +----------------------------------------------------------------------+
// $Id: data.div.php,v 1.6 2006/04/10 17:18:17 squareing Exp $

/**
 * definitions
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
	'path' => LIBERTY_PKG_PATH.'plugins/data.div.php',
	'security' => 'registered',
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
	global $gContent;
	$style = '';
	foreach( $params as $key => $value ) {
		if( !empty( $value ) ) {
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
				case 'style':
					$style .= $value;
					break;
				default:
					$style .= $key.':'.$value.';';
				break;
			}
		}
	}

	$parse['data']        = &$data;
	$parse['format_guid'] = ( !empty( $gContent->mInfo['format_guid'] ) ? $gContent->mInfo['format_guid'] : NULL );
	$parse['content_id']  = ( !empty( $gContent->mInfo['content_id'] ) ? $gContent->mInfo['content_id'] : NULL );
	$data                 = $gContent->parseData( $parse );

	return( '<div style="'.$style.'">'.$data.'</div>' );
}
?>
