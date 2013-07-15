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
	'description'         => 'Convert PDFs to flash files that can be browsed online and provides thumbnail images for the galleries and links.',
	// Templates to display the files
	'view_tpl'            => 'bitpackage:liberty/mime/pdf/view.tpl',
	//'attachment_tpl'      => 'bitpackage:liberty/mime/image/attachment.tpl',
	// url to page with options for this plugin
	'plugin_settings_url' => LIBERTY_PKG_URL.'admin/plugins/mime_pdfx.php',
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
	if( $ret = mime_default_store( $pStoreRow )) {
		if( !mime_pdfx_convert_pdf2swf( $pStoreRow )) {
			// if it all goes tits up, we'll know why
			$pStoreRow['errors'] = $pStoreRow['log'];
			$ret = FALSE;
		}
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
	if( !empty( $pStoreRow['upload'] ) && $ret = mime_default_update( $pStoreRow )) {
		if( !mime_pdfx_convert_pdf2swf( $pStoreRow )) {
			// if it all goes tits up, we'll know why
			$pStoreRow['errors'] = $pStoreRow['log'];
			$ret = FALSE;
		}
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
			$source_path = dirname( $ret['source_file'] ).'/';
			// if the swf file exists, we pass it back that it can be viewed.
			if( is_file( $source_path.'pdf.swf' )) {
				$ret['media_url'] = storage_path_to_url( dirname( $ret['source_url'] ).'/pdf.swf' );
			}
		}
	}
	return $ret;
}

/**
 * mime_pdf_convert_pdf2swf Convert a PDF to a SWF video
 *
 * @param array $pFileHash file details.
 * @param array $pFileHash[upload] should contain a complete hash from $_FILES
 * @access public
 * @return TRUE on success, FALSE on failure
 */
function mime_pdfx_convert_pdf2swf( $pFileHash ) {
	global $gBitSystem;
	if( !empty( $pFileHash['upload'] ) && @BitBase::verifyId( $pFileHash['attachment_id'] )) {
		// get file paths

		$pdf2swf    = trim( $gBitSystem->getConfig( 'swf2pdf_path', shell_exec( 'which pdf2swf' )));
		$swfcombine = trim( $gBitSystem->getConfig( 'swfcombine_path', shell_exec( 'which swfcombine' )));

		if( is_executable( $pdf2swf ) && is_executable( $swfcombine )) {
			$source    = STORAGE_PKG_PATH.$pFileHash['upload']['dest_branch'];
			if ( $gBitSystem->isFeatureActive( 'liberty_jpeg_originals' ) ) {
				$source .= 'original.jpg';
			} else {
				$source .= $pFileHash['upload']['name'];
			}
			$dest_branch = dirname( $source );

			$tmp_file  = "$dest_branch/tmp.swf";
			$swf_file  = "$dest_branch/pdf.swf";

			$pdfviewer = UTIL_PKG_PATH."javascript/pdfviewer/fdviewer.swf";
			$swfloader = UTIL_PKG_PATH."javascript/pdfviewer/loader.swf";

			$pdf2swfcommand = "$pdf2swf -s insertstop -s jpegquality=".$gBitSystem->getConfig( 'liberty_thumbnail_quality', 85 )." '$source' -o '$tmp_file'";
			$combinecommand = "$swfcombine '$pdfviewer' loader='$swfloader' '#1'='$tmp_file' -o '$swf_file'";

			shell_exec( $pdf2swfcommand );
			if( is_file( $tmp_file ) && filesize( $tmp_file ) > 0 ) {
				shell_exec( $combinecommand );
				if( !is_file( $swf_file ) || filesize( $swf_file ) == 0 ) {
					// combination went wrong. remove swf file
					$pFileHash['log']['swfcombine'] = "There was a problem combining the PDF SWF with the viewer.";
					@unlink( $swf_file );
				}
			} else {
				$pFileHash['log']['pdf2swf'] = "There was a problem converting the PDF to SWF.";
			}

			// remove temp file
			@unlink( $tmp_file );
		} else {
			$pFileHash['log']['pdf2swf'] = "PDF to SWF functions not installed.";
		}
	}
	return( empty( $pFileHash['log'] ));
}

/**
 * mime_pdf_convert_pdf2swf Convert a PDF to a SWF video
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
			$mwccommand = "$mwconvert '$source' '$thumb_file'";

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
//				$genHash['source_file'] = $genHash['icon_thumb_path'];
//				if( !$panoramaFunc( $genHash )) {
//					$pStoreRow['errors']['panorama'] = $genHash['error'];
//				}
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
