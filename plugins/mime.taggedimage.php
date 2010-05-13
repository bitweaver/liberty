<?php
/**
 * @version		$Header: /cvsroot/bitweaver/_bit_liberty/plugins/Attic/mime.taggedimage.php,v 1.1 2010/05/13 11:13:42 lsces Exp $
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision: 1.1 $
 * created		Thursday May 13, 2010 ( from mime.image.php )
 * @package		liberty
 * @subpackage	liberty_mime_handler
 **/

/**
 * setup
 */
global $gLibertySystem;

/**
 *  This is the name of the plugin - max char length is 16
 * As a naming convention, the liberty mime handler definition should start with:
 * PLUGIN_MIME_GUID_
 */
define( 'PLUGIN_MIME_GUID_TAGGEDIMAGE', 'mimetaggedimage' );

$pluginParams = array (
	// Set of functions and what they are called in this paricular plugin
	// Use the GUID as your namespace
	'verify_function'     => 'mime_default_verify',
	'store_function'      => 'mime_taggedimage_store',
	'update_function'     => 'mime_taggedimage_update',
	'load_function'       => 'mime_taggedimage_load',
	'download_function'   => 'mime_default_download',
	'expunge_function'    => 'mime_default_expunge',
	'help_function'       => 'mime_taggedimage_help',
	// Brief description of what the plugin does
	'title'               => 'Advanced Image Processing with image tagging',
	'description'         => 'Extract image meta data and display relevant information to the user and pick individual display options for images. Also allows for area tagging of images.',
	// Templates to display the files
	//'view_tpl'            => 'bitpackage:liberty/mime/image/view.tpl',
	//'attachment_tpl'      => 'bitpackage:liberty/mime/image/attachment.tpl',
	// url to page with options for this plugin
	//'plugin_settings_url' => LIBERTY_PKG_URL.'admin/plugins/mime_image.php',
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
/*
// currently, there's only one option in the image edit file - panorama image setting
if( $gBitSystem->isFeatureActive( 'mime_tag_image_panoramas' )) {
	$pluginParams['edit_tpl'] =  'bitpackage:liberty/mime/image/edit.tpl';
}
*/
$gLibertySystem->registerPlugin( PLUGIN_MIME_GUID_TAGGEDIMAGE, $pluginParams );

/**
 * Store the data in the database
 * 
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow[errors] will contain reason
 */
function mime_taggedimage_store( &$pStoreRow ) {
	// this will set the correct pluign guid, even if we let default handle the store process
	$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_TAGGEDIMAGE;
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
function mime_taggedimage_update( &$pStoreRow, $pParams = NULL ) {
	global $gThumbSizes, $gBitSystem;

	$ret = TRUE;

	// this will set the correct pluign guid, even if we let default handle the store process
	$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_TAGGEDIMAGE;

	// if storing works, we process the image
	if( !empty( $pStoreRow['upload'] ) && $ret = mime_default_update( $pStoreRow )) {
		if( !mime_image_store_exif_data( $pStoreRow )) {
			// if it all goes tits up, we'll know why
			$pStoreRow['errors'] = $pStoreRow['log'];
			$ret = FALSE;
		}
/* Tagging panoramas comes later
	} elseif( $gBitSystem->isFeatureActive( 'mime_image_panoramas' ) && !empty( $pParams['preference']['is_panorama'] ) && empty( $pStoreRow['thumbnail_url']['panorama'] )) {
		if( !mime_image_create_panorama( $pStoreRow )) {
			$ret = FALSE;
		}
	} elseif( empty( $pParams['preference']['is_panorama'] ) && !empty( $pStoreRow['thumbnail_url']['panorama'] )) {
		// we remove the panorama setting in the database and the panorama thumb
		if( LibertyAttachable::validateStoragePath( BIT_ROOT_PATH.$pStoreRow['thumbnail_url']['panorama'] )) {
			@unlink( BIT_ROOT_PATH.$pStoreRow['thumbnail_url']['panorama'] );
		}
*/
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
function mime_taggedimage_load( &$pFileHash, &$pPrefs, $pParams = NULL ) {
	global $gBitSystem;
	// don't load a mime image if we don't have an image for this file
	if( $ret = mime_image_load( $pFileHash, $pPrefs, $pParams )) {
		// Look for image tags
	}
	return $ret;
}

/**
 * mime_taggedimage_help 
 * 
 * @access public
 * @return string
 */
function mime_taggedimage_help() {
	$help =
		tra( "If you have a tagged image and you are using <strong>{attachment}</strong> to insert it, you can use <strong>tagged='1''</strong> to enable tags display on the image." )."<br />"
		.tra( "Example:" ).' '."{attachment id='13' tagged='1'}";
	return $help;
}
?>
