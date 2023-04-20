<?php
/**
 * @version  $Revision$
 * $Header$
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
	'plugin_type'   => DATA_PLUGIN,
	'booticon'       => '{booticon iname="fa-image-landscape" iexplain="Web Image"}',
	'taginsert'     => '{img src= width= height= align= description= link=}'
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

function data_img( $pData, $pParams ) {
	$cssStyle = '';
	$cssClass = '';

	foreach( $pParams as $key => $value ) {
		if( !empty( $value ) ) {
			switch( $key ) {
				// rename a couple of parameters
				case 'width':
				case 'height':
					if( preg_match( "/^\d+(em|px|%|pt)$/", trim( $value ) ) ) {
						$cssStyle .= $key.':'.$value.';';
					} elseif( preg_match( "/^\d+$/", $value ) ) {
						$cssStyle .= $key.':'.$value.'px;';
					}
					// remove values from the hash that they don't get used in the div as well
					$pParams[$key] = NULL;
					break;
				case 'class':
					$cssClass .= $value.' ';
					break;
				case 'style':
					$cssStyle .= ';'.$value;
					break;
			}
		}
	}

	$wrapper = liberty_plugins_wrapper_style( $pParams );

	// check if we have a source to load an image from
	if( !empty( $pParams['src'] ) ) {
		// set up image first
		$alt = ( !empty( $wrapper['description'] ) ? $wrapper['description'] : tra( 'Image' ) );
		$ret = '<img alt="'.$alt.'" title="'.$alt.'" src="'.$pParams['src'].'" style="'.$cssStyle.'" class="img-responsive '.$cssClass.' '.( !empty( $wrapper['class'] ) ? $wrapper['class'] : '').'"/>';

		// if this image is linking to something, wrap the image with the <a>
		if( !empty( $wrapper['link'] ) ) {
			$ret = '<a href="'.trim( $wrapper['link'] ).'">'.$ret.'</a>';
		}

		// finally, wrap the image
		if( !empty( $wrapper['style'] ) || !empty( $class ) || !empty( $wrapper['description'] ) ) {
			$ret = '<'.$wrapper['wrapper'].' class="img-plugin" style="'.$wrapper['style'].'">'.$ret.( !empty( $wrapper['description'] ) ? '<br />'.$wrapper['description'] : '' ).'</'.$wrapper['wrapper'].'>';
		}
	} else {
		$ret = '<span class="warning">'.tra( 'When using <strong>{img}</strong> the <strong>src</strong> parameter is required.' ).'</span>';
	}

	return $ret;
}
?>
