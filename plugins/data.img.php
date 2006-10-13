<?php
/**
 * @version  $Revision: 1.9 $
 * $Header: /cvsroot/bitweaver/_bit_liberty/plugins/data.img.php,v 1.9 2006/10/13 09:23:08 lsces Exp $
 * @package  liberty
 * @subpackage plugins_data
 */

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATAIMG', 'dataimg' );
global $gLibertySystem;
$pluginParams = array (
	'tag'           => 'img',
	'auto_activate' => TRUE,
	'requires_pair' => FALSE,
	'load_function' => 'data_img',
	'title'         => 'Image',
	'help_page'     => 'DataPluginImg',
	'description'   => tra( "Allows you to insert an image into your page with little effort and a multitude of styling options." ),
	'help_function' => 'data_img_help',
	'syntax'        => "{img src=http://www.google.at/logos/olympics06_ski_jump.gif}",
	'path'          => LIBERTY_PKG_PATH.'plugins/data.img.php',
	'security'      => 'registered',
	'plugin_type'   => DATA_PLUGIN,
	'biticon'       => '{biticon class="quicktag icon" ipackage=quicktags iname=image iexplain="Image"}',
	'taginsert'     => '{img src= width= height= align= desc= link=}'
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAIMG, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAIMG );

function data_img_help() {
	return
		'<table class="data help">'
			.'<tr>'
				.'<th>'.tra( "Key" ).'</th>'
				.'<th>'.tra( "Type" ).'</th>'
				.'<th>'.tra( "Comments" ).'</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>src</td>'
				.'<td>'.tra( "string").'<br />'.tra("(required)").'</td>'
				.'<td>'.tra( "Specify where the path to the image.").'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>link</td>'
				.'<td>'.tra( "string").'<br />'.tra("(optional)").'</td>'
				.'<td>'.tra( "If you want your image to link to a web address, use link='link/to/page'." ).'</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>'.tra( "styling" ).'</td>'
				.'<td>'.tra( "string").'<br />'.tra("(optional)").'</td>'
				.'<td>'.tra( "Multiple styling options available: padding, margin, background, border, text-align, color, font, font-size, font-weight, font-family, align. Please view CSS guidelines on what values these settings take.").'</td>'
			.'</tr>'
		.'</table>'
		. tra( "Example: ")."{img src=http://www.google.at/logos/olympics06_ski_jump.gif float=right border=\"3px solid blue\"}";
}

function data_img($data, $params) {
	$imgdata = array();
	$imgdata['img_style'] = '';
	$imgdata['div_style'] = '';

	foreach( $params as $key => $value ) {
		if( !empty( $value ) ) {
			switch( $key ) {
				// rename a couple of parameters
				case 'background-color':
					$key = 'background';
				case 'description':
					$key = 'desc';
				case 'width':
				case 'height':
					if( preg_match( "/^\d/", $value ) ) {
						$imgdata['img_style'] .= $key.':'.$value.';';
					}
					break;
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
					$imgdata['div_style'] .= $key.':'.$value.';';
					break;
				case 'align':
					if( $value == 'center' || $value == 'middle' ) {
						$imgdata['div_style'] .= 'text-align:center;';
					} else {
						$imgdata['div_style'] .= 'float:'.$value.';';
					}
					break;
				default:
					$imgdata[$key] = $value;
					break;
			}
		}
	}

	// check if we have a source to load an image from
	if( !empty( $imgdata['src'] ) ) {
		// set up image first
		$ret = '<img'.
				' alt="'.  ( !empty( $imgdata['desc'] ) ? $imgdata['desc'] : tra( 'Image' ) ).'"'.
				' title="'.( !empty( $imgdata['desc'] ) ? $imgdata['desc'] : tra( 'Image' ) ).'"'.
				' src="'  .$imgdata['src'].'"'.
				' style="'.$imgdata['img_style'].'"'.
			' />';

		// if this image is linking to something, wrap the image with the <a>
		if( !empty( $imgdata['link'] ) ) {
			$ret = '<a href="'.trim( $imgdata['link'] ).'">'.$ret.'</a>';
		}

		// finally, wrap the image with a div
		if( !empty( $imgdata['div_style'] ) || !empty( $class ) || !empty( $imgdata['desc'] ) ) {
			$ret = '<div class="'.( !empty( $class ) ? $class : "img-plugin" ).'" style="'.$imgdata['div_style'].'">'.$ret.'<br />'.( !empty( $imgdata['desc'] ) ? $imgdata['desc'] : '' ).'</div>';
		}
	} else {
		$ret = '<span class="warning">'.tra( 'When using <strong>{img}</strong> the <strong>src</strong> parameter is required.' ).'</span>';
	}

	return $ret;
}
?>
