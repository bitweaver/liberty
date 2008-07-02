<?php
/**
 * @version		$Header: /cvsroot/bitweaver/_bit_liberty/plugins/mime.image.php,v 1.6 2008/07/02 09:14:00 squareing Exp $
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision: 1.6 $
 * created		Thursday May 08, 2008
 * @package		liberty
 * @subpackage	liberty_mime_handler
 **/

/**
 * setup
 */
global $gLibertySystem;

/**
 *  This is the name of the plugin - max char length is 16
 * As a naming convention, the treasury mime handler definition should start with:
 * PLUGIN_MIME_GUID_
 */
define( 'PLUGIN_MIME_GUID_IMAGE', 'mimeimage' );

$pluginParams = array (
	// Set of functions and what they are called in this paricular plugin
	// Use the GUID as your namespace
	'verify_function'     => 'mime_default_verify',
	'store_function'      => 'mime_image_store',
	'update_function'     => 'mime_image_update',
	'load_function'       => 'mime_image_load',
	'download_function'   => 'mime_default_download',
	'expunge_function'    => 'mime_default_expunge',
	// Brief description of what the plugin does
	'title'               => 'Advanced Image Processing',
	'description'         => 'Extract image meta data and display relevant information to the user and pick individual display options for images.',
	// Templates to display the files
	'view_tpl'            => 'bitpackage:liberty/mime_image_view_inc.tpl',
	//'inline_tpl'          => 'bitpackage:liberty/mime_image_inline_inc.tpl',
	// url to page with options for this plugin
	'plugin_settings_url' => LIBERTY_PKG_URL.'admin/mime_image.php',
	// This should be the same for all mime plugins
	'plugin_type'         => MIME_PLUGIN,
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'       => TRUE,
	// Help page on bitweaver.org
	//'help_page'           => 'LibertyMime+Image+Plugin',
	// this should pick up all image
	'mimetypes'           => array(
		'#image/.*#i',
	),
);
// currently, there's only one option in the image edit file - panorama image setting
if( $gBitSystem->isFeatureActive( 'mime_image_panoramas' )) {
	$pluginParams['edit_tpl'] =  'bitpackage:liberty/mime_image_edit_inc.tpl';
}
$gLibertySystem->registerPlugin( PLUGIN_MIME_GUID_IMAGE, $pluginParams );

// depending on the scan the default file might not be included yet. we need to get it manually - simply use the relative path
require_once( 'mime.default.php' );

/**
 * Store the data in the database
 * 
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow[errors] will contain reason
 */
function mime_image_store( &$pStoreRow ) {
	// this will set the correct pluign guid, even if we let default handle the store process
	$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_IMAGE;
	$pStoreRow['log'] = array();

	// if storing works, we process the image
	if( $ret = mime_default_store( $pStoreRow )) {
		if( !mime_image_store_exif_data( $pStoreRow )) {
			// if it all goes tits up, we'll know why
			$pStoreRow['errors'] = $pStoreRow['log'];
			$ret = FALSE;
		}
	}
	return $ret;
}

/**
 * mime_image_update update file information in the database if there were changes.
 * 
 * @param array $pStoreRow File data needed to update details in the database
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow[errors] will contain reason
 */
function mime_image_update( &$pStoreRow, $pParams = NULL ) {
	global $gThumbSizes, $gBitSystem;

	$ret = TRUE;

	// this will set the correct pluign guid, even if we let default handle the store process
	$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_IMAGE;

	// if storing works, we process the image
	if( !empty( $pStoreRow['upload'] ) && $ret = mime_default_update( $pStoreRow )) {
		if( !mime_image_store_exif_data( $pStoreRow )) {
			// if it all goes tits up, we'll know why
			$pStoreRow['errors'] = $pStoreRow['log'];
			$ret = FALSE;
		}
	} elseif( $gBitSystem->isFeatureActive( 'mime_image_panoramas' ) && !empty( $pParams['preference']['is_panorama'] ) && empty( $pStoreRow['thumbnail_url']['panorama'] )) {
		if( !mime_image_create_panorama( $pStoreRow )) {
			$ret = FALSE;
		}
	} elseif( empty( $pParams['preference']['is_panorama'] ) && !empty( $pStoreRow['thumbnail_url']['panorama'] )) {
		// we remove the panorama setting in the database and the panorama thumb
		if( LibertyAttachable::validateStoragePath( BIT_ROOT_PATH.$pStoreRow['thumbnail_url']['panorama'] )) {
			@unlink( BIT_ROOT_PATH.$pStoreRow['thumbnail_url']['panorama'] );
		}
	}

	return $ret;
}

/**
 * Load file data from the database
 * 
 * @param array $pFileHash Contains all file information
 * @param array $pPrefs Attachment preferences taken liberty_attachment_prefs
 * @param array $pParams Parameters for loading the plugin - e.g.: might contain values such as thumbnail size from the view page
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow[errors] will contain reason
 */
function mime_image_load( &$pFileHash, &$pPrefs, $pParams = NULL ) {
	// don't load a mime image if we don't have an image for this file
	if( $ret = mime_default_load( $pFileHash, $pPrefs, $pParams )) {
		// fetch meta data from the db
		$ret['meta'] = LibertyMime::getMetaData( $ret['attachment_id'], "EXIF" );
		// check for panorama image
		if( is_file( BIT_ROOT_PATH.dirname( $ret['storage_path'] )."/panorama.jpg" )) {
			$ret['thumbnail_url']['panorama'] = storage_path_to_url( dirname( $ret['storage_path'] )."/panorama.jpg" );
		}
	}
	return $ret;
}

/**
 * mime_image_store_exif_data Process a JPEG and store its EXIF data as meta data.
 * 
 * @param array $pFileHash file details.
 * @param array $pFileHash[upload] should contain a complete hash from $_FILES
 * @access public
 * @return TRUE on success, FALSE on failure
 */
function mime_image_store_exif_data( $pFileHash ) {
	if( !empty( $pFileHash['upload'] )) {
		$upload = &$pFileHash['upload'];
	}

	if( @BitBase::verifyId( $pFileHash['attachment_id'] ) && function_exists( 'exif_read_data' ) && !empty( $upload['source_file'] ) && is_file( $upload['source_file'] ) && preg_match( "#/(jpeg|tiff)#i", $upload['type'] )) {
		$exifHash = exif_read_data( $upload['source_file'], 0, TRUE );

		// extract more information if we can find it
		if( ini_get( 'short_open_tag' )) {
			require_once UTIL_PKG_PATH.'jpeg_metadata_tk/JPEG.php';
			require_once UTIL_PKG_PATH.'jpeg_metadata_tk/JFIF.php';
			require_once UTIL_PKG_PATH.'jpeg_metadata_tk/PictureInfo.php';
			require_once UTIL_PKG_PATH.'jpeg_metadata_tk/XMP.php';
			require_once UTIL_PKG_PATH.'jpeg_metadata_tk/EXIF.php';

			// Retrieve the header information from the JPEG file
			$jpeg_header_data = get_jpeg_header_data( $upload['source_file'] );

			// Retrieve EXIF information from the JPEG file
			$Exif_array = get_EXIF_JPEG( $upload['source_file'] );

			// Retrieve XMP information from the JPEG file
			$XMP_array = read_XMP_array_from_text( get_XMP_text( $jpeg_header_data ) );

			// Retrieve Photoshop IRB information from the JPEG file
			$IRB_array = get_Photoshop_IRB( $jpeg_header_data );
			if( !empty( $exifHash['IFD0']['Software'] ) && preg_match( '/photoshop/i', $exifHash['IFD0']['Software'] ) ) {
				require_once UTIL_PKG_PATH.'jpeg_metadata_tk/Photoshop_File_Info.php';
				// Retrieve Photoshop File Info from the three previous arrays
				$psFileInfo = get_photoshop_file_info( $Exif_array, $XMP_array, $IRB_array );

				if( !empty( $psFileInfo['headline'] ) ) {
					$exifHash['headline'] = $psFileInfo['headline'];
				}

				if( !empty( $psFileInfo['caption'] ) ) {
					$exifHash['caption'] = $psFileInfo['caption'];
				}
			}
		}

		if( !empty( $exifHash['EXIF'] )) {
			LibertyMime::storeMetaData( $pFileHash['attachment_id'], 'EXIF', $exifHash['EXIF'] );
		}
	}

	return TRUE;
}

/**
 * mime_image_create_panorama 
 * 
 * @param array $pStoreRow 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function mime_image_create_panorama( &$pStoreRow ) {
	global $gBitSystem, $gThumbSizes;
	// we know the panorama image will be a jpeg, so we don't need the canThumbFunc check here
	if(( $panoramaFunc = liberty_get_function( 'panorama' )) && !empty( $pStoreRow['storage_path'] ) && !empty( $pStoreRow['source_file'] ) && is_file( $pStoreRow['source_file'] )) {
		// the panorama has to be a jpg
		$gBitSystem->setConfig( 'liberty_thumbnail_format', 'jpg' );
		$gThumbSizes['panorama'] = array( 'width' => 3000, 'height' => 1500 );
		$genHash = array(
			'dest_path'       => dirname( $pStoreRow['storage_path'] )."/",
			'source_file'     => $pStoreRow['source_file'],
			'type'            => $pStoreRow['mime_type'],
			'thumbnail_sizes' => array( 'panorama' ),
		);
		if( liberty_generate_thumbnails( $genHash )) {
			// we want to modify the panorama
			$genHash['source_file'] = $genHash['icon_thumb_path'];
			if( !$panoramaFunc( $genHash )) {
				$pStoreRow['errors']['panorama'] = $genHash['error'];
			}
		}

		return( empty( $pStoreRow['errors'] ));
	}
}

/**
 * liberty_magickwand_panorama_image - strictly speaking, this belongs in one of the image processing plugin files, but we'll leave it here for the moment
 * 
 * @param array $pFileHash File hash - souce_file is required
 * @param array $pOptions 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_magickwand_panorama_image( &$pFileHash, $pOptions = array() ) {
	$magickWand = NewMagickWand();
	$pFileHash['error'] = NULL;
	if( !empty( $pFileHash['source_file'] ) && is_file( $pFileHash['source_file'] )) {
		if( !$pFileHash['error'] = liberty_magickwand_check_error( MagickReadImage( $magickWand, $pFileHash['source_file'] ), $magickWand )) {
			// annoyingly, the panorama viewer flips the image horizontally - this isn't doing anything for some reason...
			if( !$pFileHash['error'] = liberty_magickwand_check_error( MagickFlopImage( $magickWand ), $magickWand )) {
				// calculate border width
				$iwidth  = round( MagickGetImageWidth( $magickWand ));
				$iheight = round( MagickGetImageHeight( $magickWand ));
				$aspect  = $iwidth / $iheight;
				// we need to pad the image if the aspect ratio is not 2:1 (give it a wee bit of leeway that we don't add annoying borders if not really needed)
				if( $aspect > 2.1 || $aspect < 1.9 ) {
					$bwidth = $bheight = 0;
					if( $aspect > 2 ) {
						$bheight = round((( $iwidth / 2 ) - $iheight ) / 2 );
					} else {
						$bwidth = round((( $iheight / 2 ) - $iwidth ) / 2 );
					}
					$pixelWand = NewPixelWand();
					PixelSetColor( $pixelWand, ( !empty( $pOptions['background'] ) ? $pOptions['background'] : 'black' ));
					if( !$pFileHash['error'] = liberty_magickwand_check_error( MagickBorderImage( $magickWand, $pixelWand, $bwidth, $bheight ), $magickWand )) {
						if( !$pFileHash['error'] = liberty_magickwand_check_error( MagickWriteImage( $magickWand, $pFileHash['source_file'] ), $magickWand )) {
							// yay!
						}
					}
					DestroyPixelWand( $pixelWand );
				}
			}
		}
	}
	DestroyMagickWand( $magickWand );
	return( empty( $pFileHash['error'] ));
}
?>
