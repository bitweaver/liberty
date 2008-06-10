<?php
/**
 * @version		$Header: /cvsroot/bitweaver/_bit_liberty/plugins/mime.image.php,v 1.2 2008/06/10 19:34:31 squareing Exp $
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision: 1.2 $
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
	'title'               => 'Extract image meta data',
	'description'         => 'Extract image meta data and display relevant information to the user.',
	// Templates to display the files
	//'view_tpl'            => 'bitpackage:liberty/mime_image_view_inc.tpl',
	//'inline_tpl'          => 'bitpackage:liberty/mime_image_inline_inc.tpl',
	//'edit_tpl'            => 'bitpackage:liberty/mime_image_edit_inc.tpl',
	// url to page with options for this plugin
	//'plugin_settings_url' => LIBERTY_PKG_URL.'admin/mime_image.php',
	// This should be the same for all mime plugins
	'plugin_type'         => MIME_PLUGIN,
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'       => FALSE,
	// Help page on bitweaver.org
	//'help_page'           => 'LibertyMime+Image+Plugin',
	// this should pick up all image
	'mimetypes'           => array(
		'#image/.*#i',
	),
);
$gLibertySystem->registerPlugin( PLUGIN_MIME_GUID_IMAGE, $pluginParams );

// depending on the scan the default file might not be included yet. we need to get it manually - simply use the relative path
require_once( 'mime.default.php' );

/**
 * Store the data in the database
 * 
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
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
 * mime_image_update 
 * 
 * @param array $pStoreRow 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function mime_image_update( &$pStoreRow, $pParams = NULL ) {
	$ret = FALSE;

	// this will set the correct pluign guid, even if we let default handle the store process
	$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_IMAGE;

	// if storing works, we process the image
	if( !empty( $pStoreRow['upload'] ) && $ret = mime_default_update( $pStoreRow )) {
		if( !mime_image_store_exif_data( $pStoreRow )) {
			// if it all goes tits up, we'll know why
			$pStoreRow['errors'] = $pStoreRow['log'];
			$ret = FALSE;
		}
	}
	return $ret;
}

/**
 * Load file data from the database
 * 
 * @param array $pFileHash Contains all file information
 * @param array $pPrefs Attachment preferences taken liberty_attachment_prefs
 * @param array $pParams Parameters for loading the plugin - e.g.: might contain values from the view page
 * @access public
 * @return TRUE on success, FALSE on failure - ['errors'] will contain reason for failure
 */
function mime_image_load( &$pFileHash, &$pPrefs, $pParams = NULL ) {
	global $gLibertySystem, $gBitThemes;

	// don't load a mime image if we don't have an image for this file
	if( $ret = mime_default_load( $pFileHash, $pPrefs, $pParams )) {
		// fetch meta data from the db
		$ret['meta'] = LibertyMime::getMetaData( $pFileHash['attachment_id'], "EXIF" );
	}
	return $ret;
}

/**
 * mime_image_store_exif_data Process a JPEG and store its EXIF data as meta data.
 * 
 * @param array $pFileHash file details.
 * @param array $pFileHash[upload] should contain a complete hash from $_FILES
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
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

		LibertyMime::storeMetaData( $pFileHash['attachment_id'], 'EXIF', $exifHash['EXIF'] );
	}

	return TRUE;
}
?>
