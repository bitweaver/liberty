<?php
/**
 * @version		$Header: /cvsroot/bitweaver/_bit_liberty/plugins/mime.pdf.php,v 1.2 2009/04/29 14:29:24 wjames5 Exp $
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
 * As a naming convention, the liberty mime handler definition should start with:
 * PLUGIN_MIME_GUID_
 */
define( 'PLUGIN_MIME_GUID_PDFX', 'mimepdfx' );

$pluginParams = array (
	// Set of functions and what they are called in this paricular plugin
	// Use the GUID as your namespace
	'verify_function'     => 'mime_default_verify',
	'store_function'      => 'mime_pdfx_store',
	'update_function'     => 'mime_pdfx_update',
	'load_function'       => 'mime_pdfx_load',
	'download_function'   => 'mime_default_download',
	'expunge_function'    => 'mime_default_expunge',
	'help_function'       => 'mime_pdfx_help',
	// Brief description of what the plugin does
	'title'               => 'Browsable PDFs with thumbnails',
	'description'         => 'Show uploaded PDFs in the browser\'s built-in PDF viewer and create thumbnail images for galleries and links.',
	// Templates to display the files
	'view_tpl'            => 'bitpackage:liberty/mime/pdf/view.tpl',
	//'attachment_tpl'      => 'bitpackage:liberty/mime/image/attachment.tpl',
	// url to page with options for this plugin
	'plugin_settings_inc' => 'liberty/admin/plugins/mime_pdfx_inc.php',
	// This should be the same for all mime plugins
	'plugin_type'         => MIME_PLUGIN,
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'       => FALSE,
	// Help page on bitweaver.org
	//'help_page'           => 'LibertyMime+Image+Plugin',
	// this should pick up all raw pdf files
	'mimetypes'           => array(
		'#.*/pdf#i',
	),
);
$gLibertySystem->registerPlugin( PLUGIN_MIME_GUID_PDFX, $pluginParams );

/**
 * Store the data in the database
 *
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow[errors] will contain reason
 */
function mime_pdfx_store( &$pStoreRow ) {
	global $gBitSystem;

	// this will set the correct pluign guid, even if we let default handle the store process
	$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_PDFX;
	$pStoreRow['log'] = array();

	// if storing works, we process the image
	$ret = mime_default_store( $pStoreRow );

	if( $gBitSystem->getConfig( 'pdf_thumbnails', 'y' ) == 'y' ) {
		if( !mime_pdfx_thumbnail( $pStoreRow )) {
			// if it all goes tits up, we'll know why
			$pStoreRow['errors'] = $pStoreRow['log'];
			$ret = FALSE;
		}
	}
	return $ret;
}

/**
 * mime_pdf_update update file information in the database if there were changes.
 *
 * @param array $pStoreRow File data needed to update details in the database
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow[errors] will contain reason
 */
function mime_pdfx_update( &$pStoreRow, $pParams = NULL ) {
	global $gThumbSizes, $gBitSystem;

	$ret = TRUE;

	// this will set the correct pluign guid, even if we let default handle the store process
	$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_PDFX;

	// if storing works, we process the image
	if( !empty( $pStoreRow['upload'] )) {
		$ret = mime_default_update( $pStoreRow );
	}

	if( $gBitSystem->getConfig( 'pdf_thumbnails', 'y' ) == 'y' ) {
		if( !mime_pdfx_thumbnail( $pStoreRow )) {
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
 * @param array $pParams Parameters for loading the plugin - e.g.: might contain values such as thumbnail size from the view page
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow[errors] will contain reason
 */
function mime_pdfx_load( &$pFileHash, &$pPrefs, $pParams = NULL ) {
	global $gBitSystem;
	// don't load a mime image if we don't have an image for this file
	if( $ret = mime_default_load( $pFileHash, $pPrefs, $pParams )) {
		if( !empty( $ret['source_file'] )) {
			// browsers display the PDF itself
			if( !empty( $ret['source_url'] )) {
				$ret['media_url'] = $ret['source_url'];
			}
		}
	}
	return $ret;
}

/**
 * mime_pdfx_thumbnail Create gallery thumbnails from the first page of a PDF
 *
 * @param array $pFileHash file details.
 * @param array $pFileHash[upload] should contain a complete hash from $_FILES
 * @access public
 * @return TRUE on success, FALSE on failure
 */
function mime_pdfx_thumbnail( $pFileHash ) {
	global $gBitSystem;
		$mwconvert  = trim( $gBitSystem->getConfig( 'mwconvert_path', shell_exec( 'which convert' )));

		if( is_executable( $mwconvert ) && $gBitSystem->getConfig( 'pdf_thumbnails', 'y' ) == 'y' ) {
			$source    = STORAGE_PKG_PATH.$pFileHash['upload']['dest_branch'];
			if ( $gBitSystem->isFeatureActive( 'liberty_jpeg_originals' ) ) {
				$source .= 'original.jpg';
			} else {
				$source .= $pFileHash['upload']['name'];
			}
			$dest_branch = dirname( $source );

			$thumb_file  = "$dest_branch/thumb.jpg";
			$mwccommand = escapeshellcmd( $mwconvert ).' '.escapeshellarg( $source ).' '.escapeshellarg( $thumb_file );

			shell_exec( $mwccommand );
			if( is_file( $thumb_file ) && filesize( $thumb_file ) > 0 ) {
			}
			else if( is_file( "$dest_branch/thumb-0.jpg" ) ) {
				$thumb_file = "$dest_branch/thumb-0.jpg";
			}
			$genHash = array(
				'attachment_id'	=> $pFileHash['attachment_id'],
				'dest_branch'		=> $pFileHash['upload']['dest_branch'],
				'source_file'		=> $thumb_file,
				'type'				=> 'image/jpeg',
				'thumbnail_sizes'	=> array( 'extra-large', 'large', 'medium', 'small', 'avatar', 'icon' ),
			);
			if( liberty_generate_thumbnails( $genHash )) {
			}
			$mask = "$dest_branch/thumb*.jpg";
   			array_map( "unlink", glob( $mask ) );
		}
	return( empty( $pFileHash['log'] ));
}

/**
 * mime_pdf_help
 *
 * @access public
 * @return string
 */
function mime_pdfx_help() {
	return '';
}
?>
