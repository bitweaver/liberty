<?php
/**
 * @version     $Header: /cvsroot/bitweaver/_bit_liberty/plugins/mime.default.php,v 1.6 2008/05/15 19:43:23 squareing Exp $
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
	'view_tpl'           => 'bitpackage:liberty/mime_default_view_inc.tpl',
	'inline_tpl'         => 'bitpackage:liberty/mime_default_inline_inc.tpl',
	// This should be the same for all mime plugins
	'plugin_type'        => MIME_PLUGIN,
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'      => TRUE,
	// Allow for additional processing options - passed in during verify and store
	//'processing_options' => '<label><input type="checkbox" name="liberty[mime][process_archives]" value="true" /> '.tra( 'Process Archives' ).'</label>',

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
		if( @BitBase::verifyId( $pStoreRow['attachment_id'] )) {
			// if a new file has been uploaded, we need to get some information from the database for the file update
			$fileInfo = $gBitSystem->mDb->getRow( "
				SELECT la.`attachment_id`, lf.`file_id`, lf.`storage_path`
				FROM `".BIT_DB_PREFIX."liberty_content` lc
				INNER JOIN `".BIT_DB_PREFIX."liberty_attachments` la ON ( la.`content_id` = lc.`content_id` )
				INNER JOIN `".BIT_DB_PREFIX."liberty_files` lf ON ( lf.`file_id` = la.`foreign_id` )
				WHERE lc.`content_id` = ?", array( $pStoreRow['content_id'] ));
			$pStoreRow = array_merge( $pStoreRow, $fileInfo );
		} else {
			// try to generate thumbnails for the upload
			//$pStoreRow['upload']['thumbnail'] = !$gBitSystem->isFeatureActive( 'liberty_offline_thumbnailer' );
			$pStoreRow['upload']['thumbnail'] = TRUE;

			// Store all uploaded files in the users storage area
			// TODO: allow users to create personal galleries
			$pStoreRow['attachment_id'] = $gBitSystem->mDb->GenID( 'liberty_attachments_id_seq' );
		}

		// Generic values needed by the storing mechanism
		$pStoreRow['upload']['source_file'] = $pStoreRow['upload']['tmp_name'];
		// Store all uploaded files in the users storage area
		$pStoreRow['upload']['dest_path'] = LibertyMime::getStorageBranch( $pStoreRow['attachment_id'], $pStoreRow['user_id'], LibertyMime::getStorageSubDirName( $pStoreRow['upload'] ));

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

	if( !empty( $pStoreRow['upload']['tmp_name'] )) {
		// get the data we need to deal with
		$query = "SELECT `storage_path` FROM `".BIT_DB_PREFIX."liberty_files` lf WHERE `file_id` = ?";
		if( $storage_path = $gBitSystem->mDb->getOne( $query, array( $pStoreRow['file_id'] ))) {
			// First we remove the old file
			@unlink( BIT_ROOT_PATH.$storage_path );
			// make sure we store the new file in the same place as before
			$pStoreRow['upload']['dest_path'] = dirname( $storage_path ).'/';

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
			"file_id"      => defined( 'LINKED_ATTACHMENTS' ) ? $pStoreRow['content_id'] : $gBitSystem->mDb->GenID( 'liberty_files_id_seq' ),
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

		// TODO: deal with primary attachments
		//$this->setPrimaryAttachment(
		//	$pParamHash['liberty_attachments']['primary'],
		//	$pParamHash['content_id'],
		//	empty( $pParamHash['liberty_attachments']['auto_primary'] ) || $pParamHash['liberty_attachments']['auto_primary'] ? TRUE : FALSE
		//);

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

			$canThumbFunc = liberty_get_function( 'can_thumbnail' );
			if( $canThumbFunc( $row['mime_type'] )) {
				$thumbnailerImageUrl = LIBERTY_PKG_URL.'icons/generating_thumbnails.png';
			} else {
				$thumbnailerImageUrl = NULL;
			}

			$ret['thumbnail_url']    = liberty_fetch_thumbnails( $row['storage_path'], $thumbnailerImageUrl, NULL, empty( $pFileHash['no_mime_image'] ));
			$ret['filename']         = basename( $row['storage_path'] );
			$ret['source_file']      = BIT_ROOT_PATH.$row['storage_path'];
			$ret['last_modified']    = filemtime( $ret['source_file'] );
			$ret['source_url']       = str_replace('//', '/', BIT_ROOT_URL.str_replace( '+', '%20', str_replace( '%2F', '/', urlencode( $row['storage_path'] ))));
			$ret['download_url']     = LIBERTY_PKG_URL."mime_download.php?attachment_id=".$row['attachment_id'];
			$ret['display_url']      = LIBERTY_PKG_URL."mime_view.php?attachment_id=".$row['attachment_id'];
			$ret['mime_type']        = $row['mime_type'];
			$ret['file_size']        = $row['file_size'];
			$ret['attachment_id']    = $row['attachment_id'];
			$ret['preferences']      = $pPrefs;

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
	if( is_readable( $pFileHash['source_file'] )) {
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
