<?php
/**
 * @version		$Header$
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision$
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
define( 'PLUGIN_MIME_GUID_PDF', 'mimepdf' );

$pluginParams = array (
	// Set of functions and what they are called in this paricular plugin
	// Use the GUID as your namespace
	'verify_function'     => 'mime_default_verify',
	'store_function'      => 'mime_pdf_store',
	'update_function'     => 'mime_pdf_update',
	'load_function'       => 'mime_pdf_load',
	'download_function'   => 'mime_default_download',
	'expunge_function'    => 'mime_default_expunge',
	//'help_function'       => 'mime_pdf_help',
	// Brief description of what the plugin does
	'title'               => 'Browsable PDFs',
	'description'         => 'Convert PDFs to flash files that can be browsed online.',
	// Templates to display the files
	'view_tpl'            => 'bitpackage:liberty/mime/pdf/view.tpl',
	//'attachment_tpl'      => 'bitpackage:liberty/mime/image/attachment.tpl',
	// url to page with options for this plugin
	'plugin_settings_url' => LIBERTY_PKG_URL.'admin/plugins/mime_pdf.php',
	// This should be the same for all mime plugins
	'plugin_type'         => MIME_PLUGIN,
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'       => FALSE,
	// Help page on bitweaver.org
	//'help_page'           => 'LibertyMime+Image+Plugin',
	// this should pick up all image
	'mimetypes'           => array(
		'#.*/pdf#i',
	),
);
$gLibertySystem->registerPlugin( PLUGIN_MIME_GUID_PDF, $pluginParams );

/**
 * Store the data in the database
 *
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow[errors] will contain reason
 */
function mime_pdf_store( &$pStoreRow ) {
	// this will set the correct pluign guid, even if we let default handle the store process
	$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_PDF;
	$pStoreRow['log'] = array();

	// if storing works, we process the image
	if( $ret = mime_default_store( $pStoreRow )) {
		if( !mime_pdf_convert_pdf2swf( $pStoreRow )) {
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
function mime_pdf_update( &$pStoreRow, $pParams = NULL ) {
	global $gThumbSizes, $gBitSystem;

	$ret = TRUE;

	// this will set the correct pluign guid, even if we let default handle the store process
	$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_PDF;

	// if storing works, we process the image
	if( !empty( $pStoreRow['upload'] ) && $ret = mime_default_update( $pStoreRow )) {
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
function mime_pdf_load( &$pFileHash, &$pPrefs, $pParams = NULL ) {
	global $gBitSystem;
	// don't load a mime image if we don't have an image for this file
	if( $ret = mime_default_load( $pFileHash, $pPrefs, $pParams )) {
		if( !empty( $ret['source_file'] )) {
			$source_path = STORAGE_PKG_PATH.dirname( $ret['source_file'] ).'/';
			// if the swf file exists, we pass it back that it can be viewed.
			if( is_file( $source_path.'pdf.swf' )) {
				$ret['media_url'] = storage_path_to_url( dirname( $ret['source_file'] ).'/pdf.swf' );
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
function mime_pdf_convert_pdf2swf( $pFileHash ) {
	global $gBitSystem;
	if( !empty( $pFileHash['upload'] ) && @BitBase::verifyId( $pFileHash['attachment_id'] )) {
		// get file paths
		$pdf2swf    = trim( $gBitSystem->getConfig( 'swf2pdf_path', shell_exec( 'which pdf2swf' )));
		$swfcombine = trim( $gBitSystem->getConfig( 'swfcombine_path', shell_exec( 'which swfcombine' )));

		if( is_executable( $pdf2swf ) && is_executable( $swfcombine )) {
			$source    = STORAGE_PKG_PATH.$pFileHash['upload']['dest_branch'].$pFileHash['upload']['name'];
			$destPath = dirname( $source );

			$tmp_file  = "$destPath/tmp.swf";
			$swf_file  = "$destPath/pdf.swf";

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
		}
	}

	return( empty( $pFileHash['log'] ));
}

/**
 * mime_pdf_help
 *
 * @access public
 * @return string
 */
function mime_pdf_help() {
	return '';
}
?>
