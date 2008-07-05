<?php
/**
 * @version     $Header: /cvsroot/bitweaver/_bit_liberty/plugins/mime.default.php,v 1.32 2008/07/05 06:42:38 lsces Exp $
 *
 * @author      xing  <xing@synapse.plus.com>
 * @version     $Revision: 1.32 $
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
	'upload_tpl'         => 'bitpackage:liberty/mime_default_upload_inc.tpl',
	'view_tpl'           => 'bitpackage:liberty/mime_default_view_inc.tpl',
	'inline_tpl'         => 'bitpackage:liberty/mime_default_inline_inc.tpl',
	// This should be the same for all mime plugins
	'plugin_type'        => MIME_PLUGIN,
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
function mime_default_verify( &$pStoreRow ) {
	global $gBitSystem, $gBitUser;
	$ret = FALSE;

	// storage is always owned by the user that uploaded it!
	// er... or at least admin if somehow we have a NULL mUserId - anon uploads maybe?
	$pStoreRow['user_id'] = @BitBase::verifyId( $gBitUser->mUserId ) ? $gBitUser->mUserId : ROOT_USER_ID;

	if( !empty( $pStoreRow['upload']['tmp_name'] ) && is_file( $pStoreRow['upload']['tmp_name'] )) {
		// attachment_id is only set when we are updating the file
		if( @BitBase::verifyId( $pStoreRow['upload']['attachment_id'] )) {
			// if a new file has been uploaded, we need to get some information from the database for the file update
			$fileInfo = $gBitSystem->mDb->getRow( "
				SELECT la.`attachment_id`, lf.`file_id`, lf.`storage_path`
				FROM `".BIT_DB_PREFIX."liberty_attachments` la
				INNER JOIN `".BIT_DB_PREFIX."liberty_files` lf ON ( lf.`file_id` = la.`foreign_id` )
				WHERE la.`attachment_id` = ?", array( $pStoreRow['upload']['attachment_id'] ));
			$pStoreRow = array_merge( $pStoreRow, $fileInfo );
		} else {
			// try to generate thumbnails for the upload
			//$pStoreRow['upload']['thumbnail'] = !$gBitSystem->isFeatureActive( 'liberty_offline_thumbnailer' );
			$pStoreRow['upload']['thumbnail'] = TRUE;
			if( defined( 'LINKED_ATTACHMENTS' )) {
				// Little cluge - unattached fiels create an empty ['upload']['attachment_id'] attached ones do not
				// Really need core liberty to be consistent on identifying these - ideally adding flag for linked files
				if( $pStoreRow['upload']['attachment_id'] ) {
					$pStoreRow['attachment_id'] = $gBitSystem->mDb->GenID( 'liberty_content_id_seq' );
				} else {
					$pStoreRow['attachment_id'] = $pStoreRow['content_id'];
				}
			} else {
				$pStoreRow['attachment_id'] =  $gBitSystem->mDb->GenID( 'liberty_attachments_id_seq' );
			}
		}

		// Generic values needed by the storing mechanism
		$pStoreRow['upload']['source_file'] = $pStoreRow['upload']['tmp_name'];

		// Store all uploaded files in the users storage area
		if( empty( $pStoreRow['upload']['dest_path'] )) {
			$pStoreRow['upload']['dest_path'] = LibertyMime::getStorageBranch( $pStoreRow['attachment_id'], $pStoreRow['user_id'], LibertyMime::getStorageSubDirName( $pStoreRow['upload'] ));
		}

		$ret = TRUE;
	} else {
		$pStoreRow['errors']['upload'] = tra( 'There was a problem verifying the uploaded file.' );
	}

	return $ret;
}

/**
 * When a file is edited
 * 
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function mime_default_update( &$pStoreRow ) {
	global $gBitSystem;

	// this will reset the uploaded file
	if( BitBase::verifyId( $pStoreRow['attachment_id'] ) && !empty( $pStoreRow['upload'] )) {
		if( !empty( $pStoreRow['storage_path'] )) {
			// First we remove the old file
			$file = BIT_ROOT_PATH.$pStoreRow['storage_path'];
			if(( $nuke = LibertyMime::validateStoragePath( $file )) && is_file( $nuke )) {
				if( !empty( $pStoreRow['unlink_dir'] )) {
					@unlink_r( dirname( BIT_ROOT_PATH.$pStoreRow['storage_path'] ));
					mkdir( dirname( BIT_ROOT_PATH.$pStoreRow['storage_path'] ));
				} else {
					@unlink( BIT_ROOT_PATH.$pStoreRow['storage_path'] );
				}
			}

			// make sure we store the new file in the same place as before
			$pStoreRow['upload']['dest_path'] = dirname( $pStoreRow['storage_path'] ).'/';

			// if we can create new thumbnails for this file, we remove the old ones first
			$canThumbFunc = liberty_get_function( 'can_thumbnail' );
			if( !empty( $canThumbFunc ) && $canThumbFunc( $pStoreRow['upload']['type'] )) {
				liberty_clear_thumbnails( $pStoreRow['upload'] );
			}

			// Now we process the uploaded file
			if( $storagePath = liberty_process_upload( $pStoreRow )) {
				$sql = "UPDATE `".BIT_DB_PREFIX."liberty_files` SET `storage_path` = ?, `mime_type` = ?, `file_size` = ?, `user_id` = ? WHERE `file_id` = ?";
				$gBitSystem->mDb->query( $sql, array( $pStoreRow['upload']['dest_path'].$pStoreRow['upload']['name'], $pStoreRow['upload']['type'], $pStoreRow['upload']['size'], $pStoreRow['user_id'], $pStoreRow['file_id'] ));
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

/**
 * Store the data in the database
 * 
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function mime_default_store( &$pStoreRow ) {
	global $gBitSystem, $gLibertySystem;
	$ret = FALSE;
	// take care of the uploaded file and insert it into the liberty_files and liberty_attachments tables
	if( $storagePath = liberty_process_upload( $pStoreRow )) {
		// add row to liberty_files
		$storeHash = array(
			"storage_path" => $pStoreRow['upload']['dest_path'].$pStoreRow['upload']['name'],
			"file_id"      => defined( 'LINKED_ATTACHMENTS' ) ? $pStoreRow['attachment_id'] : $gBitSystem->mDb->GenID( 'liberty_files_id_seq' ),
			"mime_type"    => $pStoreRow['upload']['type'],
			"file_size"    => $pStoreRow['upload']['size'],
			"user_id"      => $pStoreRow['user_id'],
		);
		$gBitSystem->mDb->associateInsert( BIT_DB_PREFIX."liberty_files", $storeHash );

		// add the data into liberty_attachments to make this file available as attachment
		$storeHash = array(
			"attachment_id"          => $pStoreRow['attachment_id'],
			"content_id"             => $pStoreRow['content_id'],
			"attachment_plugin_guid" => !empty( $pStoreRow['attachment_plugin_guid'] ) ? $pStoreRow['attachment_plugin_guid'] : PLUGIN_MIME_GUID_DEFAULT,
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

/**
 * Load file data from the database
 * 
 * @param array $pFileHash contains all file information
 * @access public
 * @return TRUE on success, FALSE on failure - ['errors'] will contain reason for failure
 */
function mime_default_load( $pFileHash, &$pPrefs ) {
	global $gBitSystem, $gLibertySystem;
	$ret = FALSE;
	if( @BitBase::verifyId( $pFileHash['attachment_id'] )) {
		$query = "
			SELECT *
			FROM `".BIT_DB_PREFIX."liberty_attachments` la
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files` lf ON( la.`foreign_id` = lf.`file_id` )
			WHERE la.`attachment_id`=?";
		if( $row = $gBitSystem->mDb->getRow( $query, array( $pFileHash['attachment_id'] ))) {
			$ret = array_merge( $pFileHash, $row );

			// this will fetch the correct thumbnails
			$thumbHash['storage_path'] = $row['storage_path'];
			$canThumbFunc = liberty_get_function( 'can_thumbnail' );
			if( $canThumbFunc( $row['mime_type'] )) {
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

			$ret['filename']    = basename( $row['storage_path'] );
			$ret['preferences'] = $pPrefs;

			// some stuff is only available if we have a source file
			//    make sure to check for these when you use them. frequently the original might not be available
			//    e.g.: video files are large and the original might be deleted after conversion
			if( is_file( BIT_ROOT_PATH.$row['storage_path'] )) {
				$ret['source_file']   = BIT_ROOT_PATH.$row['storage_path'];
				$ret['source_url']    = storage_path_to_url( $row['storage_path'] );
				$ret['last_modified'] = filemtime( $ret['source_file'] );
				if( $gBitSystem->isFeatureActive( "pretty_urls" ) || $gBitSystem->isFeatureActive( "pretty_urls_extended" )) {
					$ret['download_url'] = LIBERTY_PKG_URL."download/file/".$row['attachment_id'];
				} else {
					$ret['download_url'] = LIBERTY_PKG_URL."download_file.php?attachment_id=".$row['attachment_id'];
				}
			}

			// add a description of how to insert this file into a wiki page
			if( $gLibertySystem->isPluginActive( 'dataattachment' )) {
				$ret['wiki_plugin_link'] = "{attachment id=".$row['attachment_id']."}";
			}

			// additionally we'll add this to distinguish between old plugins and new ones
			// TODO: this should hopefully not be necessary for too long
			$ret['is_mime'] = TRUE;
		}
	}
	return $ret;
}

/**
 * Takes care of the entire download process. Make sure it doesn't die at the end.
 * in this functioin it would be possible to add download resume possibilites and the like
 * 
 * @param array $pFileHash Basically the same has as returned by the load function
 * @access public
 * @return TRUE on success, FALSE on failure - $pParamHash['errors'] will contain reason for failure
 */
function mime_default_download( &$pFileHash ) {
	global $gBitSystem;
	$ret = FALSE;

	// Check to see if the file actually exists
	if( !empty( $pFileHash['source_file'] ) && is_readable( $pFileHash['source_file'] )) {
		// if we have PEAR HTTP/Download installed, we make use of it since it allows download resume and download manager access
		// read the docs if you want to enable download throttling and the like
		if( @include_once( 'HTTP/Download.php' )) {
			$dl = new HTTP_Download();
			$dl->setLastModified( $pFileHash['last_modified'] );
			$dl->setFile( $pFileHash['source_file'] );
			//$dl->setContentDisposition( HTTP_DOWNLOAD_INLINE, $pFileHash['filename'] );
			$dl->setContentDisposition( HTTP_DOWNLOAD_ATTACHMENT, $pFileHash['filename'] );
			$dl->setContentType( $pFileHash['mime_type'] );
			$res = $dl->send();

			if( PEAR::isError( $res )) {
				$gBitSystem->fatalError( $res->getMessage() );
			} else {
				$ret = TRUE;
			}
		} else {
			// make sure we close off obzip compression if it's on
			if( $gBitSystem->isFeatureActive( 'site_output_obzip' )) {
				@ob_end_clean();
			}

			// this will get the browser to open the download dialogue - even when the 
			// browser could deal with the content type - not perfect, but works
			if( $gBitSystem->isFeatureActive( 'mime_force_download' )) {
				$pFileHash['mime_type'] = "application/force-download";
			}

			header( "Cache Control: " );
			header( "Accept-Ranges: bytes" );
			header( "Content-type: ".$pFileHash['mime_type'] );
			header( "Content-Disposition: attachment; filename=".$pFileHash['filename'] );
			header( "Last-Modified: ".gmdate( "D, d M Y H:i:s", $pFileHash['last_modified'] )." GMT", true, 200 );
			header( "Content-Length: ".filesize( $pFileHash['source_file'] ));
			header( "Content-Transfer-Encoding: binary" );
			header( "Connection: close" );

			readfile( $pFileHash['source_file'] );
			$ret = TRUE;
			die;
		}
	} else {
		$pFileHash['errors']['no_file'] = tra( 'No matching file found.' );
	}
	return $ret;
}

/**
 * Nuke data in tables when content is removed
 * 
 * @param array $pParamHash The contents of LibertyMime->mInfo
 * @access public
 * @return TRUE on success, FALSE on failure - $pParamHash['errors'] will contain reason for failure
 */
function mime_default_expunge( $pAttachmentId ) {
	global $gBitSystem, $gBitUser;
	$ret = FALSE;
	if( @BitBase::verifyId( $pAttachmentId )) {
		if( $fileHash = LibertyMime::getAttachment( $pAttachmentId )) {
			if( $gBitUser->isAdmin() || $gBitUser->mUserId == $fileHash['user_id'] && !empty( $fileHash['storage_path'] )) {
				$absolutePath = BIT_ROOT_PATH.'/'.$fileHash['storage_path'];
				if( file_exists( $absolutePath )) {
					// make sure this is a valid storage directory before removing it
					if( preg_match( '!/users/\d+/\d+/\w+/\d+/.+!', $fileHash['storage_path'] )) {
						unlink_r( dirname( $absolutePath ));
					} else {
						unlink( $absolutePath );
					}
				}
				$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_files` WHERE `file_id` = ?";
				$gBitSystem->mDb->query( $query, array( $fileHash['foreign_id'] ));
				$ret = TRUE;
			}
		}
	}
	return $ret;
}
?>
