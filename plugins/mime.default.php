<?php
/**
 * @version     $Header$
 *
 * @author      xing  <xing@synapse.plus.com>
 * @version     $Revision$
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
define( 'PLUGIN_MIME_GUID_DEFAULT', 'mimedefault' );

$pluginParams = array (
	// Set of functions and what they are called in this paricular plugin
	// Use the GUID as your namespace
	'verify_function'    => 'mime_default_verify',
	'store_function'     => 'mime_default_store',
	'update_function'    => 'mime_default_update',
	'load_function'      => 'mime_default_load',
	'download_function'  => 'mime_default_download',
	'expunge_function'   => 'mime_default_expunge',
	// Brief description of what the plugin does
	'title'              => 'Default File Handler',
	'description'        => 'This mime handler can handle any file type, creates thumbnails when possible and will make the file available as an attachment.',
	// Templates to display the files
	'upload_tpl'         => 'bitpackage:liberty/mime/default/upload.tpl',
	'view_tpl'           => 'bitpackage:liberty/mime/default/view.tpl',
	'inline_tpl'         => 'bitpackage:liberty/mime/default/inline.tpl',
	'storage_tpl'        => 'bitpackage:liberty/mime/default/storage.tpl',
	'attachment_tpl'     => 'bitpackage:liberty/mime/default/attachment.tpl',
	// This should be the same for all mime plugins
	'plugin_type'        => MIME_PLUGIN,
	// This needs to be specified by plugins that are included by other plugins
	'file_name'          => 'mime.default.php',
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'      => TRUE,
	// Help page on bitweaver.org
	//'help_page'          => 'MimeHelpPage',

	// Here you can use a perl regular expression to pick out file extensions you want to handle
	// e.g.: Some image types: '#^image/(jpe?g|gif|png)#i'
	// This plugin will be picked if nothing matches.
	//'mimetypes'          => array( '/.*/' ),
);
$gLibertySystem->registerPlugin( PLUGIN_MIME_GUID_DEFAULT, $pluginParams );

/**
 * Sanitise and validate data before it's stored
 *
 * @param array $pStoreRow Hash of data that needs to be stored
 * @param array $pStoreRow['upload'] Hash passed in by $_FILES upload
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
if( !function_exists( 'mime_default_verify' )) {
	function mime_default_verify( &$pStoreRow ) {
		global $gBitSystem, $gBitUser;
		$ret = FALSE;

		// if we have a user_id set, we use that.
		if( !empty( $pStoreRow['upload']['user_id'] )) {
			$pStoreRow['user_id'] = $pStoreRow['upload']['user_id'];
		} else {
			// storage is always owned by the user that uploaded it!
			// er... or at least admin if somehow we have a NULL mUserId
			$pStoreRow['user_id'] = @BitBase::verifyId( $gBitUser->mUserId ) ? $gBitUser->mUserId : ROOT_USER_ID;
			if( $pStoreRow['user_id'] < 2 ) {
				bit_error_log( 'The user_id for the upload was not set. Defaulted to user_id = '.$pStoreRow['user_id'].' where 1 = ROOT_USER_ID, -1 = ANONYMOUS_USER_ID, other values = big problem.' );
			}
		}

		if( !empty( $pStoreRow['upload']['tmp_name'] ) && is_file( $pStoreRow['upload']['tmp_name'] )) {
			// attachment_id is only set when we are updating the file
			if( @BitBase::verifyId( $pStoreRow['upload']['attachment_id'] )) {
				// if a new file has been uploaded, we need to get some information from the database for the file update
				$fileInfo = $gBitSystem->mDb->getRow( "
					SELECT la.`attachment_id`, lf.`file_id`, lf.`file_name`
					FROM `".BIT_DB_PREFIX."liberty_attachments` la
					INNER JOIN `".BIT_DB_PREFIX."liberty_files` lf ON ( lf.`file_id` = la.`foreign_id` )
					WHERE la.`attachment_id` = ?", array( $pStoreRow['upload']['attachment_id'] ));
				$pStoreRow = array_merge( $pStoreRow, $fileInfo );
			} else {
				$pStoreRow['attachment_id'] = $gBitSystem->mDb->GenID( 'liberty_attachments_id_seq' );
			}
			// try to generate thumbnails for the upload
			if( isset( $pStoreRow['upload']['thumbnail'] ) ) {
				$pStoreRow['upload']['thumbnail'] = $pStoreRow['upload']['thumbnail'];
			} else {
				$pStoreRow['upload']['thumbnail'] = TRUE;
			}

			// Generic values needed by the storing mechanism
			$pStoreRow['upload']['source_file'] = $pStoreRow['upload']['tmp_name'];

			// Store all uploaded files in the users storage area
			if( empty( $pStoreRow['upload']['dest_branch'] )) {
				$pStoreRow['upload']['dest_branch'] = liberty_mime_get_storage_branch( array( 'sub_dir'=>$pStoreRow['attachment_id'], 'user_id'=>$pStoreRow['user_id'], 'package'=>liberty_mime_get_storage_sub_dir_name( $pStoreRow['upload'] ) ) );
			}

			$ret = TRUE;
		} else {
			$pStoreRow['errors']['upload'] = tra( 'There was a problem verifying the uploaded file.' );
		}

		return $ret;
	}
}

/**
 * When a file is edited
 *
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
if( !function_exists( 'mime_default_update' )) {
	function mime_default_update( &$pStoreRow ) {
		global $gBitSystem;

		// this will reset the uploaded file
		if( BitBase::verifyId( $pStoreRow['attachment_id'] ) && !empty( $pStoreRow['upload'] )) {
			// Store all uploaded files in the users storage area
			if( empty( $pStoreRow['dest_branch'] )) {
				$pStoreRow['dest_branch'] = liberty_mime_get_storage_branch( array( 'sub_dir'=>$pStoreRow['attachment_id'], 'user_id'=>$pStoreRow['user_id'], 'package'=>liberty_mime_get_storage_sub_dir_name( $pStoreRow['upload'] ) ) );
			}

			if( !empty( $pStoreRow['dest_branch'] ) && !empty( $pStoreRow['file_name'] ) ) {
				// First we remove the old file
				$path = STORAGE_PKG_PATH.$pStoreRow['dest_branch'];
				$file = $path.liberty_mime_get_default_file_name( $pStoreRow['file_name'], $pStoreRow['mime_type'] );
				if(( $nuke = LibertyMime::validateStoragePath( $path )) && is_dir( $nuke )) {
					if( !empty( $pStoreRow['unlink_dir'] )) {
						@unlink_r( $path );
						mkdir( $path );
					} else {
						@unlink( $file );
					}
				}

				// make sure we store the new file in the same place as before
				$pStoreRow['upload']['dest_branch'] = $pStoreRow['dest_branch'];

				// if we can create new thumbnails for this file, we remove the old ones first
				$canThumbFunc = liberty_get_function( 'can_thumbnail' );
				if( !empty( $canThumbFunc ) && $canThumbFunc( $pStoreRow['upload']['type'] )) {
					liberty_clear_thumbnails( $pStoreRow['upload'] );
				}

				// Now we process the uploaded file
				if( $storagePath = liberty_process_upload( $pStoreRow['upload'] )) {
					$sql = "UPDATE `".BIT_DB_PREFIX."liberty_files` SET `file_name` = ?, `mime_type` = ?, `file_size` = ?, `user_id` = ? WHERE `file_id` = ?";
					$gBitSystem->mDb->query( $sql, array( $pStoreRow['upload']['name'], $pStoreRow['upload']['type'], $pStoreRow['upload']['size'], $pStoreRow['user_id'], $pStoreRow['file_id'] ));
				}

				// ensure we have the correct guid in the db
				if( empty( $pStoreRow['attachment_plugin_guid'] )) {
					$pStoreRow['attachment_plugin_guid'] = LIBERTY_DEFAULT_MIME_HANDLER;
				}

				$gBitSystem->mDb->associateUpdate(
					BIT_DB_PREFIX."liberty_attachments",
					array( 'attachment_plugin_guid' => $pStoreRow['attachment_plugin_guid'] ),
					array( 'attachment_id' => $pStoreRow['attachment_id'] )
				);
			}
		}
		return TRUE;
	}
}

/**
 * Store the data in the database
 *
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
if( !function_exists( 'mime_default_store' )) {
	function mime_default_store( &$pStoreRow ) {
		global $gBitSystem, $gLibertySystem;
		$ret = FALSE;

		// take care of the uploaded file and insert it into the liberty_files and liberty_attachments tables
		if( $storagePath = liberty_process_upload( $pStoreRow['upload'], empty( $pStoreRow['upload']['copy_file'] ))) {
			// add row to liberty_files
			$storeHash = array(
				"file_name"    => $pStoreRow['upload']['name'],
				"file_id"      => $gBitSystem->mDb->GenID( 'liberty_files_id_seq' ),
				"mime_type"    => $pStoreRow['upload']['type'],
				"file_size"    => (int)$pStoreRow['upload']['size'],
				"user_id"      => $pStoreRow['user_id'],
			);
			$gBitSystem->mDb->associateInsert( BIT_DB_PREFIX."liberty_files", $storeHash );

			// add the data into liberty_attachments to make this file available as attachment
			$storeHash = array(
				"attachment_plugin_guid" => !empty( $pStoreRow['attachment_plugin_guid'] ) ? $pStoreRow['attachment_plugin_guid'] : PLUGIN_MIME_GUID_DEFAULT,
				"attachment_id"          => $pStoreRow['attachment_id'],
				"content_id"             => $pStoreRow['content_id'],
				"foreign_id"             => $storeHash['file_id'],
				"user_id"                => $pStoreRow['user_id'],
			);
			$gBitSystem->mDb->associateInsert( BIT_DB_PREFIX."liberty_attachments", $storeHash );

			$ret = TRUE;
		} else {
			$pStoreRow['errors']['liberty_process'] = "There was a problem processing the file.";
		}
		return $ret;
	}
}

/**
 * Load file data from the database
 *
 * @param array $pFileHash contains all file information
 * @access public
 * @return TRUE on success, FALSE on failure - ['errors'] will contain reason for failure
 */
if( !function_exists( 'mime_default_load' )) {
	function mime_default_load( $pFileHash, &$pPrefs ) {
		global $gBitSystem, $gLibertySystem;
		$ret = FALSE;
		if( @BitBase::verifyId( $pFileHash['attachment_id'] )) {
			$query = "
				SELECT la.`attachment_id`, la.`content_id`, la.`attachment_plugin_guid`, la.`foreign_id`, la.`user_id`, la.`is_primary`, la.`pos`, la.`error_code`, la.`caption`, la.`hits` AS `downloads`,
					lf.`file_id`, lf.`user_id`, lf.`file_name`, lf.`file_size`, lf.`mime_type`
				FROM `".BIT_DB_PREFIX."liberty_attachments` la
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files` lf ON( la.`foreign_id` = lf.`file_id` )
				WHERE la.`attachment_id`=?";
			if( $row = $gBitSystem->mDb->getRow( $query, array( $pFileHash['attachment_id'] ))) {
				$ret = array_merge( $pFileHash, $row );
				$storageName = basename( $row['file_name'] );
				// compatibility with _FILES hash
				$row['name'] = $storageName;
				$defaultFileName = liberty_mime_get_default_file_name( $row['file_name'], $row['mime_type'] );
				$storageBranchPath = liberty_mime_get_storage_branch( array( 'sub_dir' => $row['attachment_id'], 'user_id' =>$row['user_id'], 'package' => liberty_mime_get_storage_sub_dir_name( $row ) ) );
				$storageBranch = $storageBranchPath.$defaultFileName;
				if( !file_exists( STORAGE_PKG_PATH.$storageBranch ) ) {
					$storageBranch = liberty_mime_get_storage_branch( array( 'sub_dir' => $row['attachment_id'], 'user_id' =>$row['user_id'], 'package' => liberty_mime_get_storage_sub_dir_name( $row ) ) ).$storageName;
				}

				// this will fetch the correct thumbnails
				$thumbHash['source_file'] = STORAGE_PKG_PATH.$storageBranch;
				$row['source_file'] = STORAGE_PKG_PATH.$storageBranch;
				$canThumbFunc = liberty_get_function( 'can_thumbnail' );
				if( $canThumbFunc && $canThumbFunc( $row['mime_type'] )) {
					$thumbHash['default_image'] = LIBERTY_PKG_URL.'icons/generating_thumbnails.png';
				}
				$ret['thumbnail_url'] = liberty_fetch_thumbnails( $thumbHash );
				// indicate that this is a mime thumbnail
				if( !empty( $ret['thumbnail_url']['medium'] ) && strpos( $ret['thumbnail_url']['medium'], '/mime/' )) {
					$ret['thumbnail_is_mime'] = TRUE;
				}

				// pretty URLs
				if( $gBitSystem->isFeatureActive( "pretty_urls" ) || $gBitSystem->isFeatureActive( "pretty_urls_extended" )) {
					$ret['display_url'] = LIBERTY_PKG_URL."view/file/".$row['attachment_id'];
				} else {
					$ret['display_url'] = LIBERTY_PKG_URL."view_file.php?attachment_id=".$row['attachment_id'];
				}

				// legacy table data was named storage path and included a partial path. strip out any path just in case
				$ret['file_name']    = $storageName;
				$ret['preferences'] = $pPrefs;

				// some stuff is only available if we have a source file
				//    make sure to check for these when you use them. frequently the original might not be available
				//    e.g.: video files are large and the original might be deleted after conversion
				if( is_file( STORAGE_PKG_PATH.$storageBranch )) {
					$ret['mime_type'] = $row['mime_type'];
					$ret['source_file']   = STORAGE_PKG_PATH.$storageBranch;
					$ret['source_url']    = STORAGE_PKG_URL.$storageBranch;
					$ret['last_modified'] = filemtime( $ret['source_file'] );
					$ret['download_url'] = LibertyMime::getAttachmentDownloadUrl( $row['attachment_id'] );
				}

				// add a description of how to insert this file into a wiki page
				if( $gLibertySystem->isPluginActive( 'dataattachment' )) {
					$ret['wiki_plugin_link'] = "{attachment id=".$row['attachment_id']."}";
				}
			}
		}

		return $ret;
	}
}

/**
 * Takes care of the entire download process. Make sure it doesn't die at the end.
 * in this functioin it would be possible to add download resume possibilites and the like
 *
 * @param array $pFileHash Basically the same has as returned by the load function
 * @access public
 * @return TRUE on success, FALSE on failure - $pParamHash['errors'] will contain reason for failure
 */
if( !function_exists( 'mime_default_download' )) {
	function mime_default_download( &$pFileHash ) {
		global $gBitSystem;
		$ret = FALSE;

		// Check to see if the file actually exists
		if( !empty( $pFileHash['source_file'] ) && is_readable( $pFileHash['source_file'] )) {
			// make sure we close off obzip compression if it's on
			if( $gBitSystem->isFeatureActive( 'site_output_obzip' )) {
				@ob_end_clean();
			}

			// this will get the browser to open the download dialogue - even when the
			// browser could deal with the content type - not perfect, but works
			if( $gBitSystem->isFeatureActive( 'mime_force_download' )) {
				$pFileHash['mime_type'] = "application/force-download";
			}

			// set up header
			header( "Cache-Control: no-cache,must-revalidate" );
			header( "Expires: 0" );
			header( "Accept-Ranges: bytes" );
			header( "Pragma: public" );
			header( "Last-Modified: ".gmdate( "D, d M Y H:i:s T", $pFileHash['last_modified'] ), TRUE, 200 );
			header( 'Content-Disposition: attachment; filename="'.$pFileHash['file_name'].'"' );
			header( "Content-type: ".$pFileHash['mime_type'] );
			header( "Content-Description: File Transfer" );
			header( "Content-Length: ".filesize( $pFileHash['source_file'] ));
			header( "Content-Transfer-Encoding: binary" );
			//header( "Connection: close" );

			@ob_clean();
			flush();
			readfile( $pFileHash['source_file'] );
			$ret = TRUE;
		} else {
			$pFileHash['errors']['no_file'] = tra( 'No matching file found.' );
		}
		return $ret;
	}
}

/**
 * Nuke data in tables when content is removed
 *
 * @param array $pParamHash The contents of LibertyMime->mInfo
 * @access public
 * @return TRUE on success, FALSE on failure - $pParamHash['errors'] will contain reason for failure
 */
if( !function_exists( 'mime_default_expunge' )) {
	function mime_default_expunge( $pAttachmentId ) {
		global $gBitSystem, $gBitUser;
		$ret = FALSE;
		if( @BitBase::verifyId( $pAttachmentId )) {
			if( $fileHash = LibertyMime::loadAttachment( $pAttachmentId )) {
				if( $gBitUser->isAdmin() || $gBitUser->mUserId == $fileHash['user_id'] ) {
					// make sure this is a valid storage directory before removing it
					if( !empty( $fileHash['source_file'] ) && ($nuke = LibertyMime::validateStoragePath( $fileHash['source_file'] )) && is_file( $nuke )) {
						unlink_r( dirname( $nuke ));
					}
					$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_files` WHERE `file_id` = ?";
					$gBitSystem->mDb->query( $query, array( $fileHash['foreign_id'] ));
					$ret = TRUE;
				}
			}
		}
		return $ret;
	}
}
?>
