<?php
/**
 * assigned_modules
 *
 * @author    lsces
 * @version    $Revision$
 * @package    liberty
 * @subpackage plugins_data
 * @copyright  Copyright (c) 2015, bitweaver.org
 */

/**
 * Setup Code
 */
define( 'PLUGIN_GUID_DATAtextarea', 'datatextarea' );
global $gLibertySystem;
$pluginParams = array (
	'tag' => 'textarea',
	'auto_activate' => TRUE,
	'requires_pair' => TRUE,
	'load_function' => 'data_textarea',
	'title' => 'Textarea',
	'help_page' => 'DataPlugintextarea',
	'description' => tra( "This plugin allows you to easily create a textarea with a number of optional CSS parameters." ),
	'help_function' => 'data_textarea_help',
	'syntax' => "{textarea}",
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAtextarea, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAtextarea );

function data_textarea_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>' . tra( "CSS rules or class" ) . '</td>'
				.'<td>' . tra( "string") . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "This can be any CSS style rule. e.g.: ") . "font='small-caps 250% serif'" .'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>preset</td>'
				.'<td>' . tra( "string") . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "There are a few presets, which you can use to style with. Presets include: caps, smallcaps, big, small, strikethrough, overline, spaced, nodecor.") .'</td>'
			.'</tr>'
		.'</table>'
		. tra( "Example: " ) . "{span preset=overline font='small-caps 250% serif'}";
	return $help;
}

function  data_textarea( $pData, $pParams, $pCommonObject ) {
	global $gBitSystem;

	$attributes = '';
	$style = '';
	$class = 'form-control ';
	if (empty($pParams['rows'])) {
		$pParams['rows'] = (empty($_COOKIE['rows']) ? $gBitSystem->getConfig('liberty_textarea_height', 20) : $_COOKIE['rows']);
	}
	if (empty($pParams['cols'])) {
		$pParams['cols'] = (empty($_COOKIE['cols']) ? $gBitSystem->getConfig('liberty_textarea_width', 35) : $_COOKIE['rows']);
	}
	if (empty($pParams['id'])) {
		$pParams['id'] = LIBERTY_TEXT_AREA;
	}
	if( empty( $pParams['name'] ) ){
		$pParams['name'] = 'edit';
	}
	if( empty( $pParams['maxchars'] ) ){
		// prevent smarty presistence of vars
		$pParams['maxchars'] = 0;
	}
	foreach ($pParams as $_key=>$_value) {
		switch ($_key) {
		case 'edit':
		case 'help':
		case 'noformat':
		case 'label':
		case 'error':
		case 'required':
		case 'maxchars':
			break;
		case 'class':
			$class .= ' '.$_key;
			break;
		case 'style':
			$style .= $_key;
			break;
		default:
			$attributes .= $_key.'="'.$_value.'" ';
			break;
		}
	}

	// we need to parse the data. we shouldn't cache this to avoid problems with the regular cache file
	$parseHash = $pCommonObject->mInfo;
	$parseHash['no_cache'] = TRUE;
	$parseHash['data'] = $pData;
	$parsedData = $pCommonObject->parseData( $parseHash );
	return( '<textarea '.$attributes.( !empty( $class ) ? 'class="'.$class.'" ' : '' ).'style="'.$style.'">'.$parsedData.'</textarea>' );
}
?>
