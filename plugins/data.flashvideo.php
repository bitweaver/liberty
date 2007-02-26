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
// | Authors: drewslater <andrew@andrewslater.com>
// +----------------------------------------------------------------------+
// $Id: data.flashvideo.php,v 1.6 2007/02/26 16:16:34 squareing Exp $

/**
 * definitions
 */
global $gBitSystem;

define( 'PLUGIN_GUID_DATAFLASHVIDEO', 'dataflashvideo' );
global $gLibertySystem;
$pluginParams = array (
	'tag'           => 'flashvideo',
	'auto_activate' => TRUE,
	'requires_pair' => FALSE,
	'load_function' => 'data_flashvideo',
	'title'         => 'Flash Video',
	'help_page'     => 'DataPluginAttachment',
	'description'   => tra( "Display flashvideo in content. This requires videos" ),
	'help_function' => 'data_flashvideo_help',
	'syntax'        => '{flashvideo id= size= align= }',
	'path'          => LIBERTY_PKG_PATH.'plugins/data.flashvideo.php',
	'security'      => 'registered',
	'plugin_type'   => DATA_PLUGIN,
	'biticon'       => '{biticon iclass= "quicktag icon" ipackage=quicktags iname=flashvideo iexplain="Image"}',
	'taginsert'     => '{flashvideo id= align= description=}',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAFLASHVIDEO, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAFLASHVIDEO );

function data_flashvideo_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>id</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(required)") . '</td>'
				.'<td>' . tra( "Id number of Flashvideo to display inline.") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>size</td>'
				.'<td>' . tra( "key-words") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "You can change the display size of the video here. This will not influence the download size of the video itself. Possible values are:") . ' <strong>small, medium, large, huge</strong></td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>width</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Manually set the width of the video in pixels.").'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>height</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Manually set the height of the video in pixels. The hight will be calculated automatically if not set.").'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>'.tra( "styling" ).'</td>'
				.'<td>'.tra( "string").'<br />'.tra("(optional)").'</td>'
				.'<td>'.tra( "Multiple styling options available: padding, margin, background, border, text-align, color, font, font-size, font-weight, font-family, align. Please view CSS guidelines on what values these settings take.").'</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{flashvideo id='13' text-align='center'}";
	return $help;
}

function data_flashvideo( $pData, $pParams ) { // NOTE: The original plugin had several parameters that have been dropped
	global $gContent, $gBitSmarty;

	// at a minimum, return blank string (not empty) so we still replace the tag
	$ret = ' ';
	if( empty( $pParams['id'] )) {
		return $ret;
	}

	$liba = new LibertyAttachable();
	if( !$att = $liba->getAttachment( $pParams['id'] )) {
		$ret = tra( "The flashvideo id given is not valid" ).": ".$pParams['id'];
		return $ret;
	}

	if( !empty( $att['flv_url'] )) {
		$div = liberty_plugins_div_style( $pParams );

		// mPrefs has been passed to us in $att['prefs']
		$flvPrefs = array_merge( $pParams, $att['prefs'] );
		treasury_flv_calculate_videosize( $flvPrefs );

		$gBitSmarty->assign( 'flvPrefs', $flvPrefs );
		$gBitSmarty->assign( 'flv', $att );
		$ret = $gBitSmarty->fetch( 'bitpackage:treasury/flv_player_inc.tpl' );

		// finally, wrap the output with a div
		$ret = '<div class="'.( !empty( $div['class'] ) ? $div['class'] : "flashvideo-plugin" ).'" style="'.$div['style'].'">'.$ret.( !empty( $div['description'] ) ? '<br />'.$div['description']  : '' ).'</div>';
	} else {
		$ret = tra( "There doesn't seem to be a valid video stream for the id you used" ).": ".$pParams['id'];
	}

	return $ret;
}
?>
