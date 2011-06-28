<?php
/**
 * @version		$Header$
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision$
 * created		Sunday Jul 02, 2006   14:42:13 CEST
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
define( 'PLUGIN_MIME_GUID_FLASH', 'mimeflash' );

$pluginParams = array(
	// simply refer to the default functions - we only want to use a custom view_tpl here
	'verify_function'    => 'mime_default_verify',
	'store_function'     => 'mime_flash_store',
	'update_function'    => 'mime_flash_update',
	'load_function'      => 'mime_default_load',
	'download_function'  => 'mime_default_download',
	'expunge_function'   => 'mime_default_expunge',
	// Brief description of what the plugin does
	'title'              => 'Macromedia Flash',
	'description'        => 'Allow upload and viewing of flash files.',
	// Template used when viewing the item
	'view_tpl'           => 'bitpackage:liberty/mime/flash/view.tpl',
	'inline_tpl'         => 'bitpackage:liberty/mime/flash/inline.tpl',
	'storage_tpl'        => 'bitpackage:liberty/mime/flash/inline.tpl',
	'attachment_tpl'     => 'bitpackage:liberty/mime/flash/inline.tpl',
	// This should be the same for all mime plugins
	'plugin_type'        => MIME_PLUGIN,
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'      => FALSE,
	// Allow for additional processing options - passed in during verify and store
	/* Depricated we should be able to get this automagically from the swf
	'processing_options' =>
		'<label>'.tra( "Width" ).': <input type="text" size="5" name="plugin[swf_width]" />px </label><br />'.
		'<label>'.tra( "Height" ).': <input type="text" size="5" name="plugin[swf_height]" />px </label><br />'.
		tra( 'If this is a flash file please insert the width and hight.' ),
	*/
	// this should pick up all videos
	'mimetypes'          => array(
		'#application/x-shockwave-flash#i',
	),
);

$gLibertySystem->registerPlugin( PLUGIN_MIME_GUID_FLASH, $pluginParams );

/**
 * Update file settings - taken over by mime_default_store appart from the width and height settings
 * 
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function mime_flash_update( &$pStoreRow ) {
	global $gBitSystem;

	// this will set the correct pluign guid, even if we let default handle the store process
	$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_FLASH;
	if( $ret = mime_default_update( $pStoreRow ) ) {
		mime_flash_store_preferences( $pStoreRow );
	}
	return $ret;
}

/**
 * Store file settings - taken over by mime_default_store appart from the width and height settings
 * 
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function mime_flash_store( &$pStoreRow ) {
	global $gBitSystem;

	// this will set the correct pluign guid, even if we let default handle the store process
	$pStoreRow['attachment_plugin_guid'] = PLUGIN_MIME_GUID_FLASH;
	if( $ret = mime_default_store( $pStoreRow ) ) {
		mime_flash_store_preferences( $pStoreRow );
	}
	return $ret;
}

/**
 * mime_flash_store_preferences 
 * 
 * @param array $pFileHash Flash information
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function mime_flash_store_preferences( &$pFileHash ) {
	$ret = FALSE;

	if( @BitBase::verifyId( $pFileHash['attachment_id'] )) {
		list( $pFileHash['preferences']['width'], $pFileHash['preferences']['height'], $type, $attr) = getimagesize( STORAGE_PKG_PATH.$pFileHash['upload']['dest_branch'].$pFileHash['upload']['name'] );

		// store width of video
		if( !empty( $pFileHash['preferences']['width'] )) {
			LibertyMime::storeAttachmentPreference( $pFileHash['attachment_id'], 'width', $pFileHash['preferences']['width'] );
		}
		// store height of video
		if( !empty( $pFileHash['preferences']['height'] )) {
			LibertyMime::storeAttachmentPreference( $pFileHash['attachment_id'], 'height', $pFileHash['preferences']['height'] );
		}
		$ret = TRUE;
	}

	return $ret;
}
?>
