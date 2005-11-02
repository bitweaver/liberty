<?php
/**
 * @version  $Revision: 1.2.2.10 $
 * @package  liberty
 * @subpackage plugins_storage
 */
global $gLibertySystem;

/**
 * definitions
 */
define( 'PLUGIN_GUID_BIT_FILES', 'bitfile' );

$pluginParams = array (
	'store_function' => 'bit_files_store',
	'verify_function' => 'bit_files_verify',
	'load_function' => 'bit_files_load',
	'expunge_function' => 'bit_files_expunge',
	'description' => 'Upload File To Server',
	'plugin_type' => STORAGE_PLUGIN,
	'auto_activate' => TRUE,
	'edit_label' => 'Upload File',
	'edit_field' => '<input type="file" name="upload" size="40" />',
	'edit_help' => 'This file will be uploaded to your personal storage area.<br />After selecting the file you want to upload, please return to the edit area and click the save button.'
);

//$gLibertySystem->registerPlugin( STORAGE_TYPE_BIT_FILES, $pluginParams );
$gLibertySystem->registerPlugin( PLUGIN_GUID_BIT_FILES, $pluginParams );


function bit_files_verify( &$pStoreRow ) {
	$pStoreRow['plugin_guid'] = PLUGIN_GUID_BIT_FILES;
	$pStoreRow['foreign_id'] = NULL;
	$pStoreRow['dest_base_name'] = substr( $pStoreRow['upload']['name'], 0, strrpos( $pStoreRow['upload']['name'], '.' ) );
	$pStoreRow['source_file'] = $pStoreRow['upload']['tmp_name'];

	return( TRUE );
}

function bit_files_store( &$pStoreRow ) {
	global $gBitSystem, $gBitUser;
	$ret = NULL;
	$pref = $gBitSystem->getPreference('centralized_upload_dir');
	if( isset( $pref ) ) {
		if( !empty( $pStoreRow['foreign_id'] ) ) {
			//$sql = "UPDATE tiki_attachment SET `binary_id`=NULL, `storage_path`=? WHERE `user_id`=? AND storage_id=?";
			$sql = "UPDATE `".BIT_DB_PREFIX."tiki_files SET `storage_path`=?, `mime_type`=?, `size`=? WHERE `file_id` = ?";
			$gBitSystem->mDb->query( $sql, array( $pStoreRow['dest_file_path'], $pStoreRow['type'], $pStoreRow['size'], $pStoreRow['foreign_id'] ) );
		} else {
			$pStoreRow['file_id'] = $gBitSystem->mDb->GenID( 'tiki_files_file_id_seq' );
			$sql = "INSERT INTO `".BIT_DB_PREFIX."tiki_files` ( `storage_path`, `file_id`, `mime_type`, `size`, `user_id` ) VALUES ( ?, ?, ?, ?, ? )";
			$userId = !empty( $pStoreRow['upload']['user_id'] ) ? $pStoreRow['upload']['user_id'] : $gBitUser->mUserId;
			$gBitSystem->mDb->query($sql, array( $pStoreRow['upload']['dest_path'].$pStoreRow['upload']['name'], $pStoreRow['file_id'],  $pStoreRow['upload']['type'],  $pStoreRow['upload']['size'], $userId ) );
		}
		$sql = "UPDATE `".BIT_DB_PREFIX."tiki_attachments` SET `foreign_id`=? WHERE `attachment_id` = ?";
		$gBitSystem->mDb->query( $sql, array( $pStoreRow['file_id'], $pStoreRow['attachment_id'] ) );
	}
	return $ret;
}

function bit_files_load( $pRow ) {
// this fuction broken, will fix soon - spiderr
// I think its fixed now - no promises though! - drewslater
	global $gBitSystem, $gLibertySystem;
	$ret = NULL;
	if( !empty( $pRow['foreign_id'] ) && is_numeric( $pRow['foreign_id'] )) {
		$query = "SELECT *
				  FROM `".BIT_DB_PREFIX."tiki_attachments` ta INNER JOIN `".BIT_DB_PREFIX."tiki_files` tf ON (tf.`file_id` = ta.`foreign_id`)
				  WHERE ta.`foreign_id` = ? AND ta.`content_id` = ?";
		if( $rs = $gBitSystem->mDb->query($query, array( $pRow['foreign_id'], $pRow['content_id'] )) ) {
			$ret = $rs->fields;
			$canThumbFunc = liberty_get_function( 'can_thumbnail' );
			if ( file_exists( BIT_ROOT_PATH.dirname( $ret['storage_path'] ).'/medium.jpg' ) ) {
				$ret['thumbnail_url']['avatar'] = BIT_ROOT_URL.dirname( $ret['storage_path'] ).'/avatar.jpg';
				$ret['thumbnail_url']['small'] = BIT_ROOT_URL.dirname( $ret['storage_path'] ).'/small.jpg';
				$ret['thumbnail_url']['medium'] = BIT_ROOT_URL.dirname( $ret['storage_path'] ).'/medium.jpg';
				$ret['thumbnail_url']['large'] = BIT_ROOT_URL.dirname( $ret['storage_path'] ).'/large.jpg';
			} elseif( $canThumbFunc( $ret['mime_type'] ) ) {
				$ret['thumbnail_url']['avatar'] = LIBERTY_PKG_URL.'icons/generating_thumbnails.png';
				$ret['thumbnail_url']['small'] = LIBERTY_PKG_URL.'icons/generating_thumbnails.png';
				$ret['thumbnail_url']['medium'] = LIBERTY_PKG_URL.'icons/generating_thumbnails.png';
				$ret['thumbnail_url']['large'] = LIBERTY_PKG_URL.'icons/generating_thumbnails.png';
			} else {
				$mime_thumbnail = $gLibertySystem->getMimeThumbnailURL($ret['mime_type']);
				$ret['thumbnail_url']['avatar'] = $mime_thumbnail;
				$ret['thumbnail_url']['small'] = $mime_thumbnail;
				$ret['thumbnail_url']['medium'] = $mime_thumbnail;
				$ret['thumbnail_url']['large'] = $mime_thumbnail;
			}
//			if ( file_exists( BIT_ROOT_PATH.dirname( $ret['storage_path'] ).'/original.jpg' ) ) {
//				$ret['thumbnail_url']['original'] = BIT_ROOT_URL.dirname( $ret['storage_path'] ).'/original.jpg';
//			}
			$ret['filename'] = substr( $ret['storage_path'], strrpos($ret['storage_path'], '/')+1);
			$ret['source_url'] = BIT_ROOT_URL.str_replace( '+', '%20', str_replace( '%2F', '/', urlencode( $ret['storage_path'] ) ) );
			$ret['wiki_plugin_link'] = "{attachment id=".$ret['attachment_id']."}";
		}
	}
	return( $ret );
}

function bit_files_expunge( $pStorageId ) {
	global $gBitUser, $gBitSystem;
	$ret = FALSE;

	if (is_numeric($pStorageId)) {
		$sql = "SELECT * FROM `".BIT_DB_PREFIX."tiki_attachments` WHERE `attachment_id` = ?";
		$rs = $gBitSystem->mDb->query($sql, array($pStorageId));
		$row = &$rs->fields;
		if ($row) {
			$sql = "SELECT * FROM `".BIT_DB_PREFIX."tiki_files` WHERE `file_id` = ?";
			$fileRs = $gBitSystem->mDb->query($sql, array($row['foreign_id']) );
			$fileRow = &$fileRs->fields;
			if ($fileRow) {
				$absolutePath = BIT_ROOT_PATH.'/'.$fileRow['storage_path'];

				if ($gBitUser->isAdmin() || $gBitUser->mUserId == $row['user_id']) {
					if (file_exists($absolutePath)) {
						unlink($absolutePath);
					}
					$query = "DELETE FROM `".BIT_DB_PREFIX."tiki_attachments` WHERE `attachment_id` = ?";
					$gBitSystem->mDb->query($query, array($pStorageId));
					$query = "DELETE FROM `".BIT_DB_PREFIX."tiki_files` WHERE `file_id` = ?";
					$gBitSystem->mDb->query($query, array($row['foreign_id']) );
					$ret = TRUE;
				}
			}
		}
	}
	return $ret;
}

?>
