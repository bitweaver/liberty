<?php
/**
 * @version     $Header: /cvsroot/bitweaver/_bit_liberty/plugins/mime.application.php,v 1.6 2008/09/15 10:32:07 squareing Exp $
 *
 * @author      xing  <xing@synapse.plus.com> 
 * @version     $Revision: 1.6 $
 * created      Thursday May 08, 2008
 * @package     liberty
 * @subpackage  liberty_mime_handler
 *
 * @TODO since plugins can do just about anything here, we might need the<br> 
 * option to create specific tables during install. if required we can scan for<br>
 * files called:<br>
 * table.plugin_guid.php<br>
 * where plugins can insert their own tables<br>
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
define( 'PLUGIN_MIME_GUID_APPLICATION', 'mimeapplication' );

$pluginParams = array (
	// Set of functions and what they are called in this paricular plugin
	// Use the GUID as your namespace
	'verify_function'    => 'mime_default_verify',
	'store_function'     => 'mime_application_store',
	'update_function'    => 'mime_application_update',
	'load_function'      => 'mime_default_load',
	'download_function'  => 'mime_default_download',
	'expunge_function'   => 'mime_default_expunge',
	// Brief description of what the plugin does
	'title'              => 'Default App File Handler',
	'description'        => 'This mime handler can handle any file type, creates thumbnails when possible and will make the file available as an attachment.',
	// Templates to display the files
	'view_tpl'           => 'bitpackage:liberty/mime/application/view.tpl',
	// This should be the same for all mime plugins
	'plugin_type'        => MIME_PLUGIN,
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'      => FALSE,
	// Help page on bitweaver.org
	//'help_page'          => 'MimeHelpPage',

	// Here you can use a perl regular expression to pick out file extensions you want to handle
	// e.g.: Some image types: '#^image/(jpe?g|gif|png)#i'
	// This plugin will be picked if nothing matches.
	'mimetypes'           => array(
		'#application/.*#i',
	),
);
$gLibertySystem->registerPlugin( PLUGIN_MIME_GUID_APPLICATION, $pluginParams );

/**
 * Store the data in the database
 * 
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow[errors] will contain reason
 */
function mime_application_store( &$pStoreRow ) {
	// this will set the correct pluign guid, even if we let default handle the store process
	$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_APPLICATION;
	$pStoreRow['log'] = array();

	// if storing works, we process the image
	if( $ret = mime_default_store( $pStoreRow )) {
		// Dummy for later
	}
	return $ret;
}

/**
 * mime_application_update update file information in the database if there were changes.
 * 
 * @param array $pStoreRow File data needed to update details in the database
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow[errors] will contain reason
 */
function mime_application_update( &$pStoreRow, $pParams = NULL ) {
	$ret = FALSE;

	// this will set the correct pluign guid, even if we let default handle the store process
	$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_APPLICATION;

	// if storing works, we process the image
	if( !empty( $pStoreRow['upload'] ) && $ret = mime_default_update( $pStoreRow )) {
		// Dummy for later
	}
	return $ret;
}

?>
