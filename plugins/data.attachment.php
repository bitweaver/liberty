<?php
/**
 * @version  $Revision: 1.7 $
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
// $Id: data.attachment.php,v 1.7 2006/11/16 16:45:46 squareing Exp $

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

function data_attachment($data, $params) { // NOTE: The original plugin had several parameters that have been dropped
	// at a minimum, return blank string (not empty) so we still replace the tag
	$ret = ' ';
	if( empty( $params['id'] ) ) {
		// The Manditory Parameter is missing. we are not gonna trow an error, and just return empty since
		// many sites use the old style required second "closing" empty tag
		return $ret;
	}

	$liba = new LibertyAttachable();
	if( !$att = $liba->getAttachment( $params['id'] ) ) {
	    $ret = tra( "The attachment id given is not valid." );
   	    return $ret;
   	}

	// insert source url if we need the original file
	if( !empty( $params['size'] ) && $params['size'] == 'original' ) {
		$thumburl = $att['source_url'];
	} else {
		$thumburl = ( !empty( $params['size'] ) && !empty( $att['thumbnail_url'][$params['size']] ) ? $att['thumbnail_url'][$params['size']] : $att['thumbnail_url']['medium'] );
	}

	$attstring = array();
	$attstring['div_style'] = '';

	foreach( $params as $key => $value ) {
		if( !empty( $value ) ) {
			switch( $key ) {
				// rename a couple of parameters
				case 'background-color':
					$key = 'background';
				case 'description':
					$key = 'desc';
				case 'class':
					$class = $value;
					break;
				case 'float':
				case 'padding':
				case 'margin':
				case 'background':
				case 'border':
				case 'text-align':
				case 'color':
				case 'font':
				case 'font-size':
				case 'font-weight':
				case 'font-family':
					$attstring['div_style'] .= $key.':'.$value.';';
					break;
				case 'align':
					if( $value == 'center' || $value == 'middle' ) {
						$attstring['div_style'] .= 'text-align:center;';
					} else {
						$attstring['div_style'] .= 'float:'.$value.';';
					}
					break;
				default:
					$attstring[$key] = $value;
					break;
			}
		}
	}

	// check if we have a valid thumbnail
	if( !empty( $thumburl ) ) {
		// set up image first
		$ret = '<img'.
				' alt="'.  ( !empty( $attstring['desc'] ) ? $attstring['desc'] : tra( 'Image' ) ).'"'.
				' title="'.( !empty( $attstring['desc'] ) ? $attstring['desc'] : tra( 'Image' ) ).'"'.
				' src="'  .$thumburl.'"'.
			' />';

		// use specified link as href. insert default link to source only when source not already displayed
		if( !empty( $params['link'] ) && $params['link'] == 'false' ) {
		} elseif( !empty( $params['link'] ) ) {
			$ret = '<a href="'.trim( $attstring['link'] ).'">'.$ret.'</a>';
		} elseif( empty( $params['size'] ) || $params['size'] != 'original' ) {
			$ret = '<a href="'.trim( $att['source_url'] ).'">'.$ret.'</a>';
		}

		// finally, wrap the image with a div
		if( !empty( $attstring['div_style'] ) || !empty( $class ) || !empty( $attstring['desc'] ) ) {
			$ret = '<div class="'.( !empty( $class ) ? $class : "img-plugin" ).'" style="'.$attstring['div_style'].'">'.$ret.'<br />'.( !empty( $attstring['desc'] ) ? $attstring['desc'] : '' ).'</div>';
		}
	} else {
	    $ret = tra( "The attachment id given is not valid." );
	}

	return $ret;
}
?>
