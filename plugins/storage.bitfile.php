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
define( 'PLUGIN_GUID_BIT_FILES', 'bitfile' );

$pluginParams = array (
	'store_function'     => 'bit_files_store',
	'verify_function'    => 'bit_files_verify',
	'load_function'      => 'bit_files_load',
	'expunge_function'   => 'bit_files_expunge',
	'description'        => 'Always load by default, disable to prevent Direct File Upload To Server',
	'plugin_type'        => STORAGE_PLUGIN,
	'auto_activate'      => FALSE,
	'edit_label'         => 'Upload File',
	'primary_edit_field' => '<input type="file" name="primary_attachment" size="40" />',
	'edit_field'         => '<input type="file" name="upload" size="40" />',
	'edit_help'          => 'After selecting the file you want to upload, please return to the edit area and click the save button.'
);

if( isset( $gBitSystem )) {
	if ($gBitSystem->getConfig("liberty_attachment_style") == "multiple") {
		$pluginParams['edit_label'] = 'Upload File(s)';
		$pluginParams['edit_help'] =  'The file(s) will be uploaded to your personal storage area.<br />After selecting the file(s) you want to upload, please return to the edit area and click the save button.';
		$pluginParams['edit_field'] = '<div id="upload_div"></div><input type="file" name="upload" size="40" id="uploads" />'.
			'<!-- Multiselect javascript. -->'.
			'<script type="text/javascript" src="'.UTIL_PKG_URL.'javascript/multifile.js"></script>'.
			'<script type="text/javascript">'.
				'var upload_files = document.getElementById( \'upload_div\' );'.
				'var upload_element = document.getElementById( \'uploads\' );'.
				'var multi_selector = new MultiSelector( upload_files, '.$gBitSystem->getConfig('liberty_max_multiple_attachments', 10).' );'.
				'multi_selector.addNamedElement( upload_element , \'uploads\');'.
			'</script>';
		$gBitSmarty->assign( 'loadMultiFile', TRUE );
	} elseif( $gBitSystem->getConfig( 'liberty_attachment_style' ) == "ajax" ) {
		$pluginParams['edit_help_new'] = $pluginParams['edit_help'];
		$pluginParams['edit_field_new'] = $pluginParams['edit_field'];
		$pluginParams['edit_help'] =  'The file(s) will be uploaded to your personal storage area.<br />After selecting the file you want to upload an attachment ID will be displayed for you to use in your content.';
		$pluginParams['edit_field'] = '<input type="file" name="upload" size="40" id="upload" onchange="javascript:LibertyAttachment.uploader(this, \'{$smarty.const.LIBERTY_PKG_URL}attachment_uploader.php\',\'{tr}Please wait for the current upload to finish.{/tr}\', \'liberty_upload_frame\', \'editpageform\');" />'.
			'{include file="bitpackage:liberty/attachment_uploader_inc.tpl"}';
	}
}
//$gLibertySystem->registerPlugin( STORAGE_TYPE_BIT_FILES, $pluginParams );
$gLibertySystem->registerPlugin( PLUGIN_GUID_BIT_FILES, $pluginParams );


function bit_files_verify( &$pStoreRow ) {
	$pStoreRow['plugin_guid'] = PLUGIN_GUID_BIT_FILES;
	$pStoreRow['foreign_id'] = NULL;
	if( strlen( $pStoreRow['upload']['name'] ) > 200 ) {
		$pStoreRow['upload']['name'] = substr( $pStoreRow['upload']['name'], 0, 195 ).'.'.substr( $pStoreRow['upload']['name'], strrpos( $pStoreRow['upload']['name'], '.' ) );
	}
	$pStoreRow['dest_base_name'] = substr( $pStoreRow['upload']['name'], 0, strrpos( $pStoreRow['upload']['name'], '.' ) );
	if( function_exists( 'transliterate' ) ) {
//		$pStoreRow['dest_base_name'] = transliterate( $pStoreRow['dest_base_name'], array('han_transliterate', 'diacritical_remove'), 'utf-8', 'utf-8' );
	}
	$pStoreRow['source_file'] = $pStoreRow['upload']['tmp_name'];

	return( TRUE );
}

function bit_files_store( &$pStoreRow ) {
	global $gBitSystem, $gBitUser;
	$ret = NULL;
	// we have been given an attachment_id but no foreign_id. we will make a last attempt to see if this is an update or an insert
	if( @BitBase::verifyId( $pStoreRow['attachment_id'] ) && !@BitBase::verifyId( $pStoreRow['foreign_id'] )) {
		$pStoreRow['foreign_id'] = $gBitSystem->mDb->getOne( "SELECT `foreign_id` FROM `".BIT_DB_PREFIX."liberty_attachments` WHERE `attachment_id` = ?", array( $pStoreRow['attachment_id'] ));
	}

	if( @BitBase::verifyId( $pStoreRow['foreign_id'] ) ) {
		$sql = "UPDATE `".BIT_DB_PREFIX."liberty_files` SET `storage_path`=?, `mime_type`=?, `file_size`=? WHERE `file_id` = ?";
		$gBitSystem->mDb->query( $sql, array( $pStoreRow['upload']['dest_path'].$pStoreRow['upload']['name'], $pStoreRow['upload']['type'], $pStoreRow['upload']['size'], $pStoreRow['foreign_id'] ) );
	} else {
		$pStoreRow['foreign_id'] = $gBitSystem->mDb->GenID( 'liberty_files_id_seq' );
		$sql = "INSERT INTO `".BIT_DB_PREFIX."liberty_files` ( `storage_path`, `file_id`, `mime_type`, `file_size`, `user_id` ) VALUES ( ?, ?, ?, ?, ? )";
		$userId = !empty( $pStoreRow['upload']['user_id'] ) ? $pStoreRow['upload']['user_id'] : $gBitUser->mUserId;
		$gBitSystem->mDb->query($sql, array( $pStoreRow['upload']['dest_path'].$pStoreRow['upload']['name'], $pStoreRow['foreign_id'],  $pStoreRow['upload']['type'],  $pStoreRow['upload']['size'], $userId ) );
	}
	return $ret;
}

function bit_files_load( $pRow ) {
// this fuction broken, will fix soon - spiderr
// I think its fixed now - no promises though! - drewslater
	global $gBitSystem, $gLibertySystem;
	$ret = NULL;
	if( !empty( $pRow['foreign_id'] ) && is_numeric( $pRow['foreign_id'] )) {
		$query = "
			SELECT *
			FROM `".BIT_DB_PREFIX."liberty_attachments` la
				INNER JOIN `".BIT_DB_PREFIX."liberty_files` lf ON (lf.`file_id` = la.`foreign_id`)
			WHERE la.`foreign_id` = ? AND `attachment_plugin_guid` = ?";
		if( $ret = $gBitSystem->mDb->getRow( $query, array( $pRow['foreign_id'], PLUGIN_GUID_BIT_FILES ))) {
			$thumbHash['storage_path'] = $ret['storage_path'];
			$canThumbFunc = liberty_get_function( 'can_thumbnail' );
			if( $canThumbFunc( $ret['mime_type'] )) {
				$thumbHash['default_image'] = LIBERTY_PKG_URL.'icons/generating_thumbnails.png';
			}
			$ret['thumbnail_url'] = liberty_fetch_thumbnails( $thumbHash );
			$ret['filename'] = str_replace('//', '/', substr( $ret['storage_path'], strrpos($ret['storage_path'], '/')+1) );
			$ret['source_url'] = str_replace('//', '/', BIT_ROOT_URL.str_replace( '+', '%20', str_replace( '%2F', '/', urlencode( $ret['storage_path'] ) ) ) );
			$ret['wiki_plugin_link'] = "{attachment id=".$ret['attachment_id']."}";
		}
	}
	return( $ret );
}

function bit_files_expunge( $pStorageId ) {
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
