<?php
/**
 * @version  $Revision$
 * @package  liberty
 * @subpackage plugins_storage
 */
global $gLibertySystem, $gBitSystem, $gBitSmarty, $gBitThemes;

/**
 * definitions
 */
define( 'PLUGIN_GUID_EXISTING_FILES', 'existing' );

$pluginParams = array (
	'store_function' => 'existing_files_store',
	'verify_function' => 'existing_files_verify',
	'description' => 'Always load for handling ajax attachments',
	'edit_label' => 'Ajax Upload Files',
	'plugin_type' => STORAGE_PLUGIN,
	'auto_activate' => FALSE,
);

if( isset( $gBitSystem ) ) {
	$gLibertySystem->registerPlugin( PLUGIN_GUID_EXISTING_FILES, $pluginParams );
}

function existing_files_verify( &$pStoreRow ) {
	global $gBitUser, $gContent;

	if( @BitBase::verifyId( $gContent->mContentId ) ) {

		// Pull all the data on the attachment in question
		$query = "SELECT * FROM `".BIT_DB_PREFIX."liberty_attachments` a ".
			"WHERE a.`attachment_id`=?";

		if( $result = $gContent->mDb->query( $query, array( $pStoreRow ))) {
			$pStoreRow = $result->fetchRow();
			//vd($pStoreRow);
			// Tell LA not to do the insert.
			$pStoreRow['skip_insert'] = TRUE;
			$pStoreRow['plugin_guid'] = PLUGIN_GUID_EXISTING_FILES;

			// Verify the user owns this attachment
			if( $gBitUser->isAdmin() || $pStoreRow['user_id'] == $gBitUser->mUserId ) {

				// Verify that it isn't attached already
				if( empty( $pStoreRow['content_id'] )) {
					return( TRUE );
				}
			}
		}
	}

	return( FALSE );
}

function existing_files_store( &$pStoreRow ) {
	global $gBitSystem, $gContent;

	if ( @BitBase::verifyId( $gContent->mContentId ) ) {
		// Update the attachments content_id
		$query = "UPDATE `".BIT_DB_PREFIX."liberty_attachments` SET ".
			"content_id = ? WHERE attachment_id = ?";

		$result = $gContent->mDb->query( $query, array( $gContent->mContentId, $pStoreRow['attachment_id'] ) );
	}

	return( TRUE );
}

?>
