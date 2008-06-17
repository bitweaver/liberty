<?php
/**
 * @version  $Revision: 1.9 $
 * $Header: /cvsroot/bitweaver/_bit_liberty/plugins/Attic/data.image.php,v 1.9 2008/06/17 17:07:50 lsces Exp $
 * @package  liberty
 * @subpackage plugins_storage
 */

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATAIMAGE', 'dataimage' );
global $gLibertySystem;
$pluginParams = array (
	'tag'           => 'image',
	'title'         => 'Fisheye Image',
	'description'   => tra( "Display an image in other content. This plugin only works with files that have been uploaded using fisheye." ),
	'help_page'     => 'DataPluginImage',

	'auto_activate' => TRUE,
	'requires_pair' => FALSE,
	'syntax'        => '{image id= }',
	'path'          => LIBERTY_PKG_PATH.'plugins/data.image.php',
	'plugin_type'   => DATA_PLUGIN,

	// display icon in quicktags bar
	'biticon'       => '{biticon ilocation=quicktag iname=image-x-generic iexplain="Image"}',
	'taginsert'     => '{image id= size= nolink=}',

	// functions
	'help_function' => 'data_image_help',
	'load_function' => 'data_image',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAIMAGE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAIMAGE );


function data_image( $pData, $pParams ) {
	global $gBitSystem, $gBitSmarty;
	$ret = ' ';

	$imgStyle = '';

	$wrapper = liberty_plugins_wrapper_style( $pParams );

	$description = !isset( $wrapper['description'] ) ? $wrapper['description'] : NULL;
	foreach( $pParams as $key => $value ) {
		if( !empty( $value ) ) {
			switch( $key ) {
				// rename a couple of parameters
				case 'width':
				case 'height':
					if( preg_match( "/^\d+(em|px|%|pt)$/", trim( $value ) ) ) {
						$imgStyle .= $key.':'.$value.';';
					} elseif( preg_match( "/^\d+$/", $value ) ) {
						$imgStyle .= $key.':'.$value.'px;';
					}
					// remove values from the hash that they don't get used in the div as well
					$pParams[$key] = NULL;
					break;
			}
		}
	}

	$wrapper = liberty_plugins_wrapper_style( $pParams );

	if( !empty( $pParams['src'] ) ) {
		$thumbUrl = $pParams['src'];
	} elseif( @BitBase::verifyId( $pParams['id'] ) && $gBitSystem->isPackageActive( 'fisheye' )) {
		require_once( FISHEYE_PKG_PATH.'FisheyeImage.php' );
		require_once $gBitSmarty->_get_plugin_filepath( 'modifier', 'display_bytes' );

		$item = new FisheyeImage( $pParams['id'], NULL );

		if( $item->load() ) {
			// insert source url if we need the original file
			if( !empty( $pParams['size'] ) && $pParams['size'] == 'original' ) {
				$thumbUrl = $item->mInfo['source_url'];
			} elseif( $item->mInfo['thumbnail_url'] ) {
				$thumbUrl = ( !empty( $pParams['size'] ) && !empty( $item->mInfo['thumbnail_url'][$pParams['size']] ) ? $item->mInfo['thumbnail_url'][$pParams['size']] : $item->mInfo['thumbnail_url']['medium'] );
			}

			if( empty( $description ) ) {
				$description = !isset( $wrapper['description'] ) ? $wrapper['description'] : $item->getField( 'title', tra( 'Image' ) );
			}
		}
	}

	// check if we have a valid thumbnail
	if( !empty( $thumbUrl )) {
		// set up image first
		$ret = '<img'.
			' alt="'.  $description.'"'.
			' title="'.$description.'"'.
			' src="'  .$thumbUrl.'"'.
			' style="'.$imgStyle.'"'.
			' />';

		if( !empty( $pParams['nolink'] ) ) {
		} elseif( !empty( $wrapper['link'] ) ) {
			// if this image is linking to something, wrap the image with the <a>
			$ret = '<a href="'.trim( $wrapper['link'] ).'">'.$ret.'</a>';
		} elseif ( empty( $pParams['size'] ) || $pParams['size'] != 'original' ) {
			$ret = '<a href="'.trim( $item->mInfo['source_url'] ).'">'.$ret.'</a>';
		}

		if( !empty( $wrapper['style'] ) || !empty( $class ) || !empty( $wrapper['description'] ) ) {
			$ret = '<'.$wrapper['wrapper'].' class="'.( !empty( $wrapper['class'] ) ? $wrapper['class'] : "img-plugin" ).'" style="'.$wrapper['style'].'">'.$ret.( !empty( $wrapper['description'] ) ? '<br />'.$wrapper['description'] : '' ).'</'.$wrapper['wrapper'].'>';
		}
	} else {
		$ret = tra( "Unknown Image" );
	}

	return $ret;
}

function data_image_help() {
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
				.'<td>' . tra( "Image id number of Image to display inline.") . tra( "You can use either content_id or id." ).'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>size</td>'
				.'<td>' . tra( "key-words") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "If the File is an image, you can specify the size of the thumbnail displayed. Possible values are:") . ' <strong>avatar, small, medium, large, original</strong> '
				. tra( "(Default = " ) . '<strong>medium</strong>)</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>nolink</td>'
				.'<td>' . tra( "key-words") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Remove hotlink from element. Used to display fixed copies of an image item.") . '</td>'
			.'</tr>'
		.'</table>'
		. tra( "Example: ") . "{image id='13' size='small'}";
	return $help;
}
?>
