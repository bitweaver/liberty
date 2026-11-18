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
	'description'         => 'Show uploaded PDFs in the browser\'s built-in PDF viewer.',
	// Templates to display the files
	'view_tpl'            => 'bitpackage:liberty/mime/pdf/view.tpl',
	//'attachment_tpl'      => 'bitpackage:liberty/mime/image/attachment.tpl',
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
	$ret = mime_default_store( $pStoreRow );
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
			// browsers display the PDF itself
			if( !empty( $ret['source_url'] )) {
				$ret['media_url'] = $ret['source_url'];
			}
		}
	}
	return $ret;
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
