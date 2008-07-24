<?php
/**
 * @version		$Header: /cvsroot/bitweaver/_bit_liberty/plugins/mime.pbase.php,v 1.5 2008/07/24 08:33:08 squareing Exp $
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision: 1.5 $
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
define( 'PLUGIN_MIME_GUID_PBASE', 'mimepbase' );

$pluginParams = array (
	// Set of functions and what they are called in this paricular plugin
	// Use the GUID as your namespace
	'verify_function'     => 'mime_pbase_verify',
	'store_function'      => 'mime_pbase_store',
	//'update_function'     => 'mime_pbase_update',
	'load_function'       => 'mime_pbase_load',
	//'download_function'   => 'mime_pbase_download',
	//'expunge_function'    => 'mime_pbase_expunge',
	// Brief description of what the plugin does
	'title'               => 'Display image from PBase',
	'description'         => 'Use a PBase image ID to display it on your website.',
	// Templates to display the files
	'upload_tpl'          => 'bitpackage:liberty/mime_pbase_upload_inc.tpl',
	// url to page with options for this plugin
	//'plugin_settings_url' => LIBERTY_PKG_URL.'admin/mime_pbase.php',
	// This should be the same for all mime plugins
	'plugin_type'         => MIME_PLUGIN,
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'       => FALSE,
	// Help page on bitweaver.org
	//'help_page'           => 'LibertyMime+PBase+Plugin',
);
$gLibertySystem->registerPlugin( PLUGIN_MIME_GUID_PBASE, $pluginParams );

function mime_pbase_verify( &$pStoreRow ) {
	global $gBitSystem, $gBitUser;
	$ret = FALSE;
	if( @BitBase::verifyId( $pStoreRow['pbase_id'] )) {
		$pStoreRow['user_id']       = @BitBase::verifyId( $gBitUser->mUserId ) ? $gBitUser->mUserId : ROOT_USER_ID;
		$pStoreRow['attachment_id'] = $gBitSystem->mDb->GenID( 'liberty_attachments_id_seq' );
		$ret = TRUE;
	} else {
		$pStoreRow['errors']['pbase_id'] = "No valid PBase ID given.";
	}
	return $ret;
}

/**
 * Store the data in the database
 * 
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow[errors] will contain reason
 */
function mime_pbase_store( &$pStoreRow ) {
	global $gBitSystem;
	$ret = FALSE;
	if( @BitBase::verifyId( $pStoreRow['pbase_id'] )) {
		// add the data into liberty_attachments to make this file available as attachment
		$storeHash = array(
			"attachment_id"          => $pStoreRow['attachment_id'],
			"content_id"             => $pStoreRow['content_id'],
			"attachment_plugin_guid" => PLUGIN_MIME_GUID_PBASE,
			"foreign_id"             => $pStoreRow['pbase_id'],
			"user_id"                => $pStoreRow['user_id'],
		);
		$gBitSystem->mDb->associateInsert( BIT_DB_PREFIX."liberty_attachments", $storeHash );
		$ret = TRUE;
	} else {
		$pStoreRow['errors']['pbase_id'] = "No valid PBase ID given.";
	}
	return $ret;
}

/**
 * mime_pbase_update update file information in the database if there were changes.
 * 
 * @param array $pStoreRow File data needed to update details in the database
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow[errors] will contain reason
 */
function mime_pbase_update( &$pStoreRow, $pParams = NULL ) {
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
function mime_pbase_load( &$pFileHash, &$pPrefs, $pParams = NULL ) {
	$ret = array();
	if( $ret = mime_default_load( $pFileHash, $pPrefs, $pParams )) {
		$ret['display_url']             = 'http://www.pbase.com/image/'.$pFileHash['foreign_id'];
		$ret['thumbnail_url']['small']  = 'http://www.pbase.com/image/'.$pFileHash['foreign_id'].'/small.jpg';
		$ret['thumbnail_url']['medium'] = 'http://www.pbase.com/image/'.$pFileHash['foreign_id'].'/medium.jpg';
		$ret['thumbnail_url']['large']  = 'http://www.pbase.com/image/'.$pFileHash['foreign_id'].'/large.jpg';
		$ret['is_mime'] = TRUE;
	}
	return $ret;
}
?>
