<?php
/**
 * @version  $Revision: 1.1 $
 * @package  liberty
 * @subpackage plugins_storage
 */
global $gLibertySystem, $gBitSystem, $gBitSmarty;

/**
 * definitions
 */
define( 'PLUGIN_GUID_BIT_IMAGE', 'bitimage' );

$pluginParams = array (
	'store_function' => 'bit_image_store',
	'verify_function' => 'bit_image_verify',
	'load_function' => 'bit_image_load',
	'expunge_function' => 'bit_image_expunge',
	'description' => 'Allow access to fisheye images as managed attachment content',
	'plugin_type' => STORAGE_PLUGIN,
	'auto_activate' => TRUE,
	'edit_label' => 'Fisheye Image',
);


global $gLibertySystem;
$gLibertySystem->registerPlugin( PLUGIN_GUID_BIT_IMAGE, $pluginParams );


function bit_image_verify( &$pStoreRow ) {
	$pStoreRow['plugin_guid'] = PLUGIN_GUID_BIT_IMAGE;
	$pStoreRow['foreign_id'] = NULL;
	$pStoreRow['dest_base_name'] = substr( $pStoreRow['upload']['name'], 0, strrpos( $pStoreRow['upload']['name'], '.' ) );
	if( function_exists( 'transliterate' ) ) {
//		$pStoreRow['dest_base_name'] = transliterate( $pStoreRow['dest_base_name'], array('han_transliterate', 'diacritical_remove'), 'utf-8', 'utf-8' );
	}
	$pStoreRow['source_file'] = $pStoreRow['upload']['tmp_name'];

	return( TRUE );
}

function bit_image_store( &$pStoreRow ) {
	global $gBitSystem, $gBitUser;
	$ret = NULL;
	if( !empty( $pStoreRow['foreign_id'] ) ) {
		$sql = "UPDATE `".BIT_DB_PREFIX."liberty_files SET `storage_path`=?, `mime_type`=?, `file_size`=? WHERE `file_id` = ?";
		$gBitSystem->mDb->query( $sql, array( $pStoreRow['dest_file_path'], $pStoreRow['type'], $pStoreRow['size'], $pStoreRow['foreign_id'] ) );
	} else {
		$pStoreRow['foreign_id'] = $gBitSystem->mDb->GenID( 'liberty_files_id_seq' );
		$sql = "INSERT INTO `".BIT_DB_PREFIX."liberty_files` ( `storage_path`, `file_id`, `mime_type`, `file_size`, `user_id` ) VALUES ( ?, ?, ?, ?, ? )";
		$userId = !empty( $pStoreRow['upload']['user_id'] ) ? $pStoreRow['upload']['user_id'] : $gBitUser->mUserId;
		$gBitSystem->mDb->query($sql, array( $pStoreRow['upload']['dest_path'].$pStoreRow['upload']['name'], $pStoreRow['foreign_id'],  $pStoreRow['upload']['type'],  $pStoreRow['upload']['size'], $userId ) );
	}
	return $ret;
}

function bit_image_load( $pRow ) {
	global $gBitSystem, $gLibertySystem;
	$ret = NULL;
	if( !empty( $pRow['foreign_id'] ) && is_numeric( $pRow['foreign_id'] )) {
		$query = "
			SELECT la.*, lf.*, img.`content_id`
			FROM `".BIT_DB_PREFIX."liberty_attachments` la
				INNER JOIN `".BIT_DB_PREFIX."liberty_attachments_map` lam ON (la.`attachment_id` = lam.`attachment_id`)
				INNER JOIN `".BIT_DB_PREFIX."fisheye_image` img ON( img.`content_id` = lam.`content_id` )
				INNER JOIN `".BIT_DB_PREFIX."liberty_files` lf ON (lf.`file_id` = la.`foreign_id`)
			WHERE la.`foreign_id` = ? AND `attachment_plugin_guid` = ?";
		if( $ret = $gBitSystem->mDb->getRow( $query, array( $pRow['foreign_id'], PLUGIN_GUID_BIT_IMAGE ))) {
			$canThumbFunc = liberty_get_function( 'can_thumbnail' );
			if( $canThumbFunc( $ret['mime_type'] )) {
				$thumbnailerImageUrl = LIBERTY_PKG_URL.'icons/generating_thumbnails.png';
			} else {
				$thumbnailerImageUrl = NULL;
			}
			$ret['thumbnail_url'] = liberty_fetch_thumbnails( $ret['storage_path'], $thumbnailerImageUrl );
			$ret['filename'] = substr( $ret['storage_path'], strrpos($ret['storage_path'], '/')+1);
			$ret['source_url'] = BIT_ROOT_URL.str_replace( '+', '%20', str_replace( '%2F', '/', urlencode( $ret['storage_path'] ) ) );
			$ret['wiki_plugin_link'] = "{attachment id=".$ret['attachment_id']."}";
		}
	}
	return( $ret );
}

function bit_image_expunge( $pStorageId ) {
	global $gBitUser, $gBitSystem;
	$ret = FALSE;

	if (is_numeric($pStorageId)) {
		$sql = "SELECT * FROM `".BIT_DB_PREFIX."liberty_attachments` WHERE `attachment_id` = ?";
		if( $row = $gBitSystem->mDb->getRow( $sql, array( $pStorageId ))) {
			$sql = "SELECT * FROM `".BIT_DB_PREFIX."liberty_files` WHERE `file_id` = ?";
			if( $fileRow = $gBitSystem->mDb->getRow( $sql, array( $row['foreign_id'] ))) {
				$absolutePath = BIT_ROOT_PATH.'/'.$fileRow['storage_path'];

				if( $gBitUser->isAdmin() || $gBitUser->mUserId == $row['user_id'] ) {
					if( file_exists( $absolutePath )) {
						// make sure this is a valid storage directory before removing it
					   if( preg_match( '!/users/\d+/\d+/\w+/\d+/.+!', $fileRow['storage_path'] )) {
						   unlink_r( dirname( $absolutePath ));
					   } else {
						   unlink( $absolutePath );
					   }
					}
					$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_files` WHERE `file_id` = ?";
					$gBitSystem->mDb->query($query, array($row['foreign_id']) );
					$ret = TRUE;
				}
			}
		}
	}
	return $ret;
}

?>
