<?php
/**
 * @version  $Revision: 1.10 $
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
// $Id: data.attachment.php,v 1.10 2006/12/13 20:10:36 squareing Exp $

/**
 * definitions
 */
global $gBitSystem;

define( 'PLUGIN_GUID_DATAATTACHMENT', 'dataattachment' );
global $gLibertySystem;
$pluginParams = array (
	'tag' => 'ATTACHMENT',
	'auto_activate' => TRUE,
	'requires_pair' => FALSE,
	'load_function' => 'data_attachment',
	'title' => 'Attachment',
	'help_page' => 'DataPluginAttachment',
	'description' => tra("Display attachment in content"),
	'help_function' => 'data_attachment_help',
	'syntax' => '{ATTACHMENT id= size= align= }',
	'path' => LIBERTY_PKG_PATH.'plugins/data.attachment.php',
	'security' => 'registered',
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAATTACHMENT, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAATTACHMENT );


function data_attachment_help() {
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
				.'<td>' . tra( "Id number of Attachment to display inline.") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>size</td>'
				.'<td>' . tra( "key-words") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "If the Attachment is an image, you can specify the size of the thumbnail displayed. Possible values are:") . ' <strong>avatar, small, medium, large, original</strong> '
				. tra( "(Default = " ) . '<strong>medium</strong>)</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>link</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Allows you to specify a relative or absolute URL the image will link to if clicked. If set to false, no link is inserted.")
				. tra("(Default = ") . '<strong>'.tra( 'link to source image' ).'</strong>)</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>'.tra( "styling" ).'</td>'
				.'<td>'.tra( "string").'<br />'.tra("(optional)").'</td>'
				.'<td>'.tra( "Multiple styling options available: padding, margin, background, border, text-align, color, font, font-size, font-weight, font-family, align. Please view CSS guidelines on what values these settings take.").'</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{ATTACHMENT id='13' size='small' text-align='center' link='http://www.google.com'}";
	return $help;
}

function data_attachment( $pData, $pParams ) { // NOTE: The original plugin had several parameters that have been dropped
	// at a minimum, return blank string (not empty) so we still replace the tag
	$ret = ' ';
	if( empty( $pParams['id'] ) ) {
		// The Manditory Parameter is missing. we are not gonna trow an error, and just return empty since
		// many sites use the old style required second "closing" empty tag
		return $ret;
	}

	$liba = new LibertyAttachable();
	if( !$att = $liba->getAttachment( $pParams['id'] ) ) {
		$ret = tra( "The attachment id given is not valid." );
		return $ret;
	}

	// insert source url if we need the original file
	if( !empty( $pParams['size'] ) && $pParams['size'] == 'original' ) {
		$thumburl = $att['source_url'];
	} else {
		$thumburl = ( !empty( $pParams['size'] ) && !empty( $att['thumbnail_url'][$pParams['size']] ) ? $att['thumbnail_url'][$pParams['size']] : $att['thumbnail_url']['medium'] );
	}

	// check if we have a valid thumbnail
	if( !empty( $thumburl ) ) {
		$div = liberty_plugins_div_style( $pParams );

		// set up image first
		$ret = '<img'.
				' alt="'.  ( !empty( $div['description'] ) ? $div['description'] : tra( 'Image' ) ).'"'.
				' title="'.( !empty( $div['description'] ) ? $div['description'] : tra( 'Image' ) ).'"'.
				' src="'  .$thumburl.'"'.
			' />';

		// use specified link as href. insert default link to source only when source not already displayed
		if( !empty( $pParams['link'] ) && $pParams['link'] == 'false' ) {
		} elseif( !empty( $pParams['link'] ) ) {
			$ret = '<a href="'.trim( $pParams['link'] ).'">'.$ret.'</a>';
		} elseif( empty( $pParams['size'] ) || $pParams['size'] != 'original' ) {
			$ret = '<a href="'.trim( $att['source_url'] ).'">'.$ret.'</a>';
		}

		// finally, wrap the image with a div
		if( !empty( $div['style'] ) || !empty( $class ) || !empty( $div['description'] ) ) {
			$ret = '<div class="'.( !empty( $div['class'] ) ? $div['class'] : "att-plugin" ).'" style="'.$div['style'].'">'.$ret.'<br />'.( !empty( $div['description'] ) ? $div['description'] : '' ).'</div>';
		}
	} else {
	    $ret = tra( "The attachment id given is not valid." );
	}

	return $ret;
}
?>
