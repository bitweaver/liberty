<?php
/**
 * assigned_modules
 *
 * @author     xing
 * @version    $Revision: 1.2 $
 * @package    liberty
 * @subpackage plugins_data
 * @copyright  Copyright (c) 2004, bitweaver.org
 */

/**
 * Setup Code
 */
define( 'PLUGIN_GUID_DATAPRE', 'datapre' );
global $gLibertySystem;
$pluginParams = array (
	'tag'           => 'PRE',
	'auto_activate' => TRUE,
	'requires_pair' => TRUE,
	'load_function' => 'data_pre',
	'title'         => 'Pre',
	'help_page'     => 'DataPluginPre',
	'description'   => tra( "This plugin allows you to easily create a preformatted text block with a number of optional CSS parameters." ),
	'help_function' => 'data_pre_help',
	'syntax'        => "{pre border='3px solid blue'}",
	'plugin_type'   => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAPRE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAPRE );

function data_pre_help() {
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
		. tra( "Example: " ) . "{pre preset=centered border='3px solid blue'}";
	return $help;
}

function data_pre( $pData, $pParams, $pCommonObject, $pParseHash ) {
	$style = '';
	foreach( $pParams as $key => $value ) {
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
				case 'class':
					$class = $value;
					break;
				default:
					$style .= $key.':'.$value.';';
				break;
			}
		}
	}
	$parseHash['content_id'] = $pParseHash['content_id'];
	$parseHash['user_id'] = $pParseHash['user_id'];
	$parseHash['no_cache'] = TRUE;
	$parseHash['data'] = $pData;
	$ret = '<pre '.( !empty( $class ) ? 'class="'.$class.'" ' : '' ).'style="'.$style.'">'.$pCommonObject->parseData( $parseHash ).'</pre>';
	return $ret;
}
?>
