<?php
/**
 * Manages liberty Uploads
 *
 * @package  liberty
 */

/**
 * required setup
 */
require_once( LIBERTY_PKG_CLASS_PATH.'LibertyContent.php' );

// load the image processor plugin, check for loaded 'gd' since that is the default processor, and config might not be set.
if( $gBitSystem->isFeatureActive( 'image_processor' ) || extension_loaded( 'gd' ) ) {
	require_once( LIBERTY_PKG_PATH."plugins/processor.".$gBitSystem->getConfig( 'image_processor','gd' ).".php" );
}

// maximum size of the 'original' image when converted to jpg
define( 'MAX_THUMBNAIL_DIMENSION', 20000 );

/**
 * LibertyMime class
 *
 * @package liberty
 */
class LibertyMime extends LibertyContent {
	public $mStoragePrefs = NULL;

	/**
	 * load the attachments for a given content id and then stuff them in mStorage
	 *
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	public function load() {
		global $gLibertySystem;
		if( @BitBase::verifyId( $this->mContentId )) {
			// load up the content
			LibertyContent::load();

			// don't loadAttachmentPreferences() when we are forcing the installer since it breaks the login process before 2.1.0-beta
			if( !defined( 'INSTALLER_FORCE' ) && !defined( 'LOGIN_VALIDATE' )) {
				$this->loadAttachmentPreferences();
			}

			$query = "SELECT * FROM `".BIT_DB_PREFIX."liberty_attachments` la WHERE la.`content_id`=? ORDER BY la.`pos` ASC, la.`attachment_id` ASC";
			if( $result = $this->mDb->query( $query,array( $this->mContentId ))) {
				$this->mStorage = array();
				while( $row = $result->fetchRow() ) {
					if( !empty( $row['is_primary'] ) ) {
						// used by edit tpl's among other things
						$this->mInfo['primary_attachment_id'] = $row['attachment_id'];
					} elseif( !$this->getField( 'primary_attachment_id' ) && !empty( $row['attachment_id'] ) ) {
						// primary was not set by the above, default to first row. might be reset by later iterations via if is_primary above
						$this->mInfo['primary_attachment_id'] = $row['attachment_id'];
					}
					if( $func = $gLibertySystem->getPluginFunction( $row['attachment_plugin_guid'], 'load_function', 'mime' )) {
						// we will pass the preferences by reference that the plugin can easily update them
						if( empty( $this->mStoragePrefs[$row['attachment_id']] )) {
							$this->mStoragePrefs[$row['attachment_id']] = array();
						}
						$this->mStorage[$row['attachment_id']] = $func( $row, $this->mStoragePrefs[$row['attachment_id']], NULL );
					} else {
						print "No load_function for ".$row['attachment_plugin_guid'];
					}
				}
			}
		}
		return( TRUE );
	}

	/**
	 * Store a new upload
	 *
	 * @param array $pStoreHash contains all data to store the gallery
	 * @return bool TRUE on success, FALSE if store could not occur. If FALSE, $this->mErrors will have reason why
	 * @access public
	 **/
	public function store( &$pStoreHash ) {
		global $gLibertySystem;
		// make sure all the data is in order
		if( LibertyMime::verify( $pStoreHash ) && ( !empty( $pStoreHash['skip_content_store'] ) || parent::store( $pStoreHash ) ) ) {
			$this->StartTrans();
			// files have been uploaded
			if( !empty( $pStoreHash['upload_store']['files'] ) && is_array( $pStoreHash['upload_store']['files'] )) {

				foreach( $pStoreHash['upload_store']['files'] as $key => $upload ) {
					// if we don't have an upload, we'll simply update the file settings using the mime plugins
					if( empty( $upload['tmp_name'] )) {
						if( @BitBase::verifyId( $upload['attachment_id'] )) {
							// since the form might have all options unchecked, we need to call the update function regardless
							// currently i can't think of a better way to get the plugin guid back when $pStoreHash[plugin] is
							// empty. - xing - Friday Jul 11, 2008   20:21:18 CEST
							if( !empty( $this->mStorage[$upload['attachment_id']] )) {
								$attachment = $this->mStorage[$upload['attachment_id']];
								$data = array();
								if( !empty( $pStoreHash['plugin'][$upload['attachment_id']][$attachment['attachment_plugin_guid']] )) {
									$data = $pStoreHash['plugin'][$upload['attachment_id']][$attachment['attachment_plugin_guid']];
								}
								if( !$this->updateAttachmentParams( $upload['attachment_id'], $attachment['attachment_plugin_guid'], $data )) {
									$this->mErrors['attachment_update'] = "There was a problem updating the file settings.";
								}
							}
						}
						// skip rest of process
						continue;
					}

					$storeRow = $pStoreHash['upload_store'];
					unset( $storeRow['files'] );

					// copy by reference that filetype changes are made in lookupMimeHandler()
					$storeRow['upload'] = &$upload;
					if( isset( $pStoreHash['thumbnail'] ) ) {
						$storeRow['upload']['thumbnail'] = $pStoreHash['thumbnail'];
					}

					// when content is created the content_id is only available after LibertyContent::store()
					$storeRow['content_id'] = $pStoreHash['content_id'];

					// let the plugin do the rest
					$guid = $gLibertySystem->lookupMimeHandler( $upload );
					$this->pluginStore( $storeRow, $guid, @BitBase::verifyId( $upload['attachment_id'] ));

					// finally, we need to update the original hash with the new values
					$pStoreHash['upload_store']['files'][$key] = $storeRow;
				}
			}

			// some mime plugins might not have file uploads - these plugins will tell us what mime handlers they are using
			if( !empty( $pStoreHash['mimeplugin'] ) && is_array( $pStoreHash['mimeplugin'] )) {
				foreach( $pStoreHash['mimeplugin'] as $guid => $storeRow ) {
					// check to see if we have anything worth storing in the array
					$plugin_store = FALSE;
					foreach( array_values( $storeRow ) as $value ) {
						if( !empty( $value )) {
							$plugin_store = TRUE;
						}
					}

					if( !empty( $plugin_store )) {
						// when content is created the content_id is only available after LibertyContent::store()
						$storeRow['content_id'] = $pStoreHash['content_id'];
						$this->pluginStore( $storeRow, $guid, @BitBase::verifyId( $upload['attachment_id'] ));
					}
				}
			}

			// deal with the primary attachment after we've dealt with all the files
			$this->setPrimaryAttachment(
				$pStoreHash['liberty_attachments']['primary'],
				$pStoreHash['content_id'],
				empty( $pStoreHash['liberty_attachments']['auto_primary'] ) || $pStoreHash['liberty_attachments']['auto_primary'] ? TRUE : FALSE
			);

			// Roll back if something went wrong
			if( empty( $this->mErrors )) {
				$this->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
			}
		}

		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * pluginStore will use a given plugin to store uploaded file data
	 *
	 * @param string $pGuid GUID of plugin
	 * @param array $pStoreHash Data to be prcessed and stored by the plugin
	 * @param boolean $pUpdate set to TRUE if this is just an update
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	public function pluginStore( &$pStoreHash, $pGuid, $pUpdate = FALSE ) {
		global $gLibertySystem;
		if( !empty( $pStoreHash ) && $verify_function = $gLibertySystem->getPluginFunction( $pGuid, 'verify_function' )) {
			// pass along a pointer to the content object
			$pStoreHash['this'] = &$this;
			// verify the uploaded file using the plugin
			if( $verify_function( $pStoreHash )) {
				if( $process_function = $gLibertySystem->getPluginFunction( $pGuid, (( $pUpdate ) ? 'update_function' : 'store_function' ))) {
					if( !$process_function( $pStoreHash )) {
						$this->mErrors = array_merge( $this->mErrors, $pStoreHash['errors'] );
					}
				} else {
					$this->mErrors['store_function'] = tra( 'No suitable store function found.' );
				}
			} else {
				$this->mErrors = array_merge( $this->mErrors, $pStoreHash['errors'] );
			}
		} else {
			$this->mErrors['verify_function'] = tra( 'No suitable verify function found.' );
		}

		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * Verify content that is about to be stored
	 *
	 * @param array $pStoreHash hash of all data that needs to be stored in the database
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason
	 * @todo If one of the uploaded files is an update, place the attachment_id with the upload hash in $_FILES or in _files_override
	 */
	public function verify( &$pParamHash ) {
		global $gBitUser, $gLibertySystem;

		// check to see if we have any files to upload
		if( isset( $pParamHash['_files_override'] )) {
			// we have been passed in a manually stuffed files attachment, such as a custom uploader would have done.
			// process this, and skip over $_FILES
			$uploads = $pParamHash['_files_override'];
		} elseif( !empty( $_FILES )) {
			// we have some _FILES hanging around we will gobble up. This is inherently dagnerous chewing up a _FILES like this as
			// it can cause premature storing of a _FILE if you are trying to store multiple pieces of content at once.
			foreach( $_FILES as $key => $file ) {
				if( !empty( $file['name'] ) || !empty( $file['attachment_id'] )) {
					$uploads[$key] = $file;
				}
			}
		}

		// verify uploads
		if( !empty( $uploads ) ) {
			foreach( array_keys( $uploads ) as $file ) {
				$pParamHash['upload_store']['files'][$file] = LibertyMime::verifyAttachment( $uploads[$file] );
			}
		}

		// don't check for p_liberty_attach_attachments permission on bitpermuser class so registration with avatar upload works
		if( strtolower( get_class( $this )) == 'bitpermuser' ) {
			$pParamHash['upload_store']['no_perm_check'] = TRUE;
		}

		// check for the required permissions to upload a file to the liberty attachments area
		if( !empty( $uploads ) && empty( $pParamHash['no_perm_check'] )) {
			if( !$this->hasUserPermission( 'p_liberty_attach_attachments' )) {
				$this->mErrors['permission'] = tra( 'You do not have permission to upload attachments.' );
			}
		}

		// primary attachment. Allow 'none' to clear the primary.
		if( !@BitBase::verifyId( $pParamHash['liberty_attachments']['primary'] ) && ( empty( $pParamHash['liberty_attachments']['primary'] ) || $pParamHash['liberty_attachments']['primary'] != 'none' ) ) {
			$pParamHash['liberty_attachments']['primary'] = NULL;
		}

		// if we have an error we get them all by checking parent classes for additional errors
		if( count( $this->mErrors ) > 0 ){
			// check errors of LibertyContent since LibertyMime means to override the parent verify
			LibertyContent::verify( $pParamHash );
		}

		return ( count( $this->mErrors ) == 0 );
	}

	/**
	 * getThumbnailUrl will fetch the primary thumbnail for a given content. If nothing has been set, it will fetch the last thumbnail it can find.
	 *
	 * @param string $pSize
	 * @param array $pInfoHash
	 * @access public
	 * @return boolean TRUE on success, FALSE on failure - $this->mErrors will contain reason for failure
	 */
	public function getThumbnailUrl( $pSize='small', $pInfoHash=NULL, $pSecondary=NULL, $pDefault=TRUE ) {
		$ret = NULL;
		if( !empty( $pInfoHash ) ) {
			// do some stuff if we are given a hash of stuff
		} elseif( $this->isValid() && !empty( $this->mStorage ) ) {
			foreach( array_keys( $this->mStorage ) as $attachmentId ) {
				if( !empty( $this->mStorage[$attachmentId]['is_primary'] ) ) {
					break;
				}
			}
			if( !empty( $this->mStorage[$attachmentId]['thumbnail_url'][$pSize] )) {
				$ret = $this->mStorage[$attachmentId]['thumbnail_url'][$pSize];
			}
		}
		if( $pDefault && empty( $ret ) ) {
			$ret = parent::getThumbnailUrl( $pSize, $pInfoHash, $pSecondary );
		}
		return $ret;
	}

	/**
	 * updateAttachmentParams will update attachment parameters
	 *
	 * @param numeric $pAttachmentId attachment_id of the item we want the prefs from (optional)
	 * @param string $pPluginGuid GUID of the plugin that should process the data
	 * @param array $pParamHash Data to be processed by the plugin
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	public function updateAttachmentParams( $pAttachmentId, $pPluginGuid, $pParamHash = array() ) {
		global $gLibertySystem;
		$ret = FALSE;

		if( BitBase::verifyId( $pAttachmentId )) {
			if( !empty( $this ) && !empty( $this->mStorage[$pAttachmentId] )) {
				$file = $this->mStorage[$pAttachmentId];
			} else {
				$file = $this->getAttachment( $pAttachmentId );
			}

			if( @BitBase::verifyId( $file['attachment_id'] ) && !empty( $pPluginGuid ) && ( $update_function = $gLibertySystem->getPluginFunction( $pPluginGuid, 'update_function', 'mime' ))) {
				if( $update_function( $file, $pParamHash )) {
					$ret = TRUE;
				} else {
					if( !empty( $file['errors'] )) {
						$this->mErrors['param_update'] = $file['errors'];
					} else {
						$this->mErrors['param_update'] = tra( 'There was an unspecified error while updating the file.' );
					}
				}
			}
		}
		return $ret;
	}

	/**
	 * verifyAttachment will perform a generic check if a file is valid for processing
	 *
	 * @param array $pFile file array from $_FILES
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	public function verifyAttachment( $pFile ) {
		if( !empty( $pFile['tmp_name'] ) && is_file( $pFile['tmp_name'] ) && empty( $pFile['error'] ) || !empty( $pFile['attachment_id'] )) {
			return $pFile;
		}
	}

	/**
	 * Increment the item hit flag by 1
	 *
	 * @access public
	 * @param numeric $pAttachmentId Attachment ID
	 * @return adodb query result or FALSE
	 * @note we're abusing the hits column for download count.
	 */
	public static function addDownloadHit( $pAttachmentId = NULL ) {
		global $gBitUser, $gBitSystem;
		if( @BitBase::verifyId( $pAttachmentId ) && $attachment = static::loadAttachment( $pAttachmentId )) {
			if( !$gBitUser->isRegistered() || ( $gBitUser->isRegistered() && $gBitUser->mUserId != $attachment['user_id'] )) {
				$bindVars = array( $pAttachmentId );
				if( $gBitSystem->mDb->getOne( "SELECT `attachment_id` FROM `".BIT_DB_PREFIX."liberty_attachments` WHERE `attachment_id` = ? AND `hits` IS NULL", $bindVars )) {
					$query = "UPDATE `".BIT_DB_PREFIX."liberty_attachments` SET `hits` = 1 WHERE `attachment_id` = ?";
				} else {
					$query = "UPDATE `".BIT_DB_PREFIX."liberty_attachments` SET `hits` = `hits`+1 WHERE `attachment_id` = ?";
				}
				return $gBitSystem->mDb->query( $query, $bindVars );
			}
		}
		return FALSE;
	}

	// {{{ =================== Storage Directory Methods ====================
	function getSourceUrl( $pParamHash=array() ) {
		$ret = NULL;
		if( empty( $pParamHash ) && !empty( $this ) ) {
			$pParamHash = $this->mInfo;
		}
		if( $fileName = $this->getParameter( $pParamHash, 'file_name', $this->getField( 'file_name' ) ) ) {
			$defaultFileName = liberty_mime_get_default_file_name( $fileName, BitBase::getParameter( $pParamHash, 'mime_type' ) );
			if( file_exists( $this->getStoragePath( $pParamHash ).$defaultFileName ) ) {
				$ret = $this->getStorageUrl( $pParamHash ).$defaultFileName;
			} else {
				$ret = $this->getStorageUrl( $pParamHash ).basename( $fileName );
			}
		}
		return $ret;
	}

	function getSourceFile( $pParamHash=array() ) {
		$ret = NULL;
		if( empty( $pParamHash ) && !empty( $this ) ) {
			$pParamHash = $this->mInfo;
		}
		if( $fileName = $this->getParameter( $pParamHash, 'file_name', $this->getField( 'file_name' ) ) ) {
			$defaultFileName = liberty_mime_get_default_file_name( $fileName, BitBase::getParameter( $pParamHash, 'mime_type' ) );
			$ret = $this->getStoragePath( $pParamHash ).$defaultFileName;
			if( !file_exists( $ret ) ) {
				$ret = $this->getStoragePath( $pParamHash ).basename( $fileName );
			}
		}
		return $ret;
	}


	/**
	 * getStoragePath - get path to store files for the feature site_upload_dir. It creates a calculable hierarchy of directories
	 *
	 * @access public
	 * @author Christian Fowler<spider@steelsun.com>
	 * @param $pSubDir any desired directory below the StoragePath. this will be created if it doesn't exist
	 * @param $pCommon indicates not to use the 'common' branch, and not the 'users/.../<user_id>' branch
	 * @param $pRootDir override BIT_ROOT_DIR with a custom absolute path - useful for areas where no we access should be allowed
	 * @return string full path on local filsystem to store files.
	 */
	function getStoragePath( $pParamHash, $pRootDir=NULL ) {
		$ret = null;

		if( $branch = liberty_mime_get_storage_branch( $pParamHash ) ) {
			$ret = ( !empty( $pRootDir ) ? $pRootDir : STORAGE_PKG_PATH ).$branch;
			mkdir_p($ret);
		}
		return $ret;
	}


	function getStorageUrl( $pParamHash ) {
		return STORAGE_PKG_URL.liberty_mime_get_storage_branch( $pParamHash );
	}

	/**
	 * getStorageBranch - get url to store files for the feature site_upload_dir. It creates a calculable hierarchy of directories
	 *
	 * @access public
	 * @author Christian Fowler<spider@steelsun.com>
	 * @param $pSubDir any desired directory below the StoragePath. this will be created if it doesn't exist
	 * @param $pUserId indicates the 'users/.../<user_id>' branch or use the 'common' branch if null
	 * @param $pRootDir **deprecated, unused, will be removed in future relase**.
	 * @return string full path on local filsystem to store files.
	 */
	function getStorageBranch( $pParamHash ) {
		return liberty_mime_get_storage_branch( $pParamHash );
	}

	/**
	 * getStorageSubDirName get a filename based on the uploaded file
	 *
	 * @param array $pFileHash File information provided in $_FILES
	 * @access public
	 * @return appropriate sub dir name
	 */
	function getStorageSubDirName( $pFileHash = NULL ) {
		if( !empty( $pFileHash['mime_type'] ) && strstr( $pFileHash['mime_type'], "/" )) {
			$ret = strtolower( preg_replace( "!/.*$!", "", $pFileHash['mime_type'] ));
			// if we only got 'application' we will use the file extenstion
			if( $ret == 'application' && !empty( $pFileHash['name'] ) && ( $pos = strrpos( $pFileHash['name'], "." )) !== FALSE ) {
				$ret = strtolower( substr( $pFileHash['name'], $pos + 1 ));
			}
		}

		// append an 's' to not create an image and images dir side by side (legacy reasons)
		if( empty( $ret ) || $ret == 'image' ) {
			$ret = 'images';
		}

		return $ret;
	}

	/**
	 * validateStoragePath make sure that the file/dir you are trying to delete is valid
	 *
	 * @param array $pPath absolute path to the file/dir we want to validate
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	public static function validateStoragePath( $pPath ) {
		// file_exists checks for file or directory
		if( !empty( $pPath ) && $pPath = realpath( $pPath )) {
			// ensure path sanity
			if( preg_match( "#^".realpath( STORAGE_PKG_PATH )."/(users|common)/\d+/\d+/\w+/\d+#", $pPath )) {
				return $pPath;
			}
		}
	}

	// }}}


	// {{{ =================== Attachment Methods ====================
	/**
	 * Get a list of all available attachments
	 *
	 * @param array $pListHash
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getAttachmentList( &$pListHash ) {
		global $gLibertySystem, $gBitUser, $gBitSystem;

		LibertyContent::prepGetList( $pListHash );

		// initialise some variables
		$attachments = $ret = $bindVars = array();
		$whereSql = $joinSql = $selectSql = '';

		// only admin may view attachments from other users
		if( !$gBitUser->isAdmin() ) {
			$pListHash['user_id'] = $gBitUser->mUserId;
		}

		if( !empty( $pListHash['user_id'] ) ) {
			$whereSql .= empty( $whereSql ) ? ' WHERE ' : ' AND ';
			$whereSql .= " la.user_id = ? ";
			$bindVars[] = $pListHash['user_id'];
		}

		if( !empty( $pListHash['content_id'] ) ) {
			$whereSql .= empty( $whereSql ) ? ' WHERE ' : ' AND ';
			$whereSql .= " la.`content_id` = ? ";
			$selectSql .= " , la.`content_id` ";
			$bindVars[] = $pListHash['content_id'];
		}
		$query = "SELECT la.* $selectSql FROM `".BIT_DB_PREFIX."liberty_attachments` la INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON(la.`user_id` = uu.`user_id`) $joinSql $whereSql";
		$result = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] );
		while( $res = $result->fetchRow() ) {
			$attachments[] = $res;
		}

		foreach( $attachments as $attachment ) {
			if( $loadFunc = $gLibertySystem->getPluginFunction( $attachment['attachment_plugin_guid'], 'load_function', 'mime' )) {
				/* @$prefs - quick hack to stop LibertyMime plugins from breaking until migration to LibertyMime is complete
				 * see expected arguments of liberty/plugins/mime.default.php::mime_default_load -wjames5
				 */
				$prefs = array();
				$ret[$attachment['attachment_id']] = $loadFunc( $attachment, $prefs );
			}
		}

		// count all entries
		$query = "SELECT COUNT(*)
			FROM `".BIT_DB_PREFIX."liberty_attachments` la
			INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON(la.`user_id` = uu.`user_id`)
			$joinSql $whereSql
		";

		$pListHash['cant'] = $this->mDb->getOne( $query, $bindVars );
		$this->postGetList( $pListHash );

		return $ret;
	}

	/**
	 * Expunges the content deleting attached attachments
	 */
	function expunge() {
		if( !empty( $this->mStorage ) && count( $this->mStorage )) {
			foreach( array_keys( $this->mStorage ) as $i ) {
				$this->expungeAttachment( $this->mStorage[$i]['attachment_id'] );
			}
		}
		return LibertyContent::expunge();
	}

	/**
	 * expunge attachment from the database (and file system via the plugin if required)
	 *
	 * @param numeric $pAttachmentId attachment id of the item that should be deleted
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function expungeAttachment( $pAttachmentId ) {
		global $gLibertySystem, $gBitUser;
		$ret = NULL;
		if( @$this->verifyId( $pAttachmentId ) ) {
			$sql = "SELECT `attachment_plugin_guid`, `user_id` FROM `".BIT_DB_PREFIX."liberty_attachments` WHERE `attachment_id` = ?";
			if(( $row = $this->mDb->getRow( $sql, array( $pAttachmentId ))) && ( $this->isOwner( $row ) || $gBitUser->isAdmin() )) {
				// check if we have the means available to remove this attachment
				if(( $guid = $row['attachment_plugin_guid'] ) && $expungeFunc = $gLibertySystem->getPluginFunction( $guid, 'expunge_function', 'mime' )) {
					// --- Do the final cleanup of liberty related tables ---

					// there might be situations where we remove user images including portrait, avatar or logo
					// This needs to happen before the plugin can do it's work due to constraints
					$types = array( 'portrait', 'avatar', 'logo' );
					foreach( $types as $type ) {
						$sql = "UPDATE `".BIT_DB_PREFIX."users_users` SET `{$type}_attachment_id` = NULL WHERE `{$type}_attachment_id` = ?";
						$this->mDb->query( $sql, array( $pAttachmentId ));
					}

					if( $expungeFunc( $pAttachmentId )) {
						// Delete the attachment meta data, prefs and record.
						$sql = "DELETE FROM `".BIT_DB_PREFIX."liberty_attachment_meta_data` WHERE `attachment_id` = ?";
						$this->mDb->query( $sql, array( $pAttachmentId ));
						$sql = "DELETE FROM `".BIT_DB_PREFIX."liberty_attachment_prefs` WHERE `attachment_id` = ?";
						$this->mDb->query( $sql, array( $pAttachmentId ));
						$sql = "DELETE FROM `".BIT_DB_PREFIX."liberty_attachments` WHERE `attachment_id`=?";
						$this->mDb->query( $sql, array( $pAttachmentId ));

						// Remove attachment from memory
						unset( $this->mStorage[$pAttachmentId] );
						$ret = TRUE;
					}
				} else {
					print( "Expunge function not found for this content!" );
				}
			}
		}

		return $ret;
	}

	/**
	 * loadAttachment will load details of a given attachment
	 *
	 * @param numeric $pAttachmentId Attachment ID of the attachment
	 * @param array $pParams optional parameters that might contain information like display thumbnail size
	 * @access public
	 * @return attachment details
	 */
	public static function loadAttachment( $pAttachmentId, $pParams = NULL ) {
		global $gLibertySystem, $gBitSystem;
		$ret = NULL;

		if( @BitBase::verifyId( $pAttachmentId )) {
			$query = "SELECT * FROM `".BIT_DB_PREFIX."liberty_attachments` la WHERE la.`attachment_id`=?";
			if( $result = $gBitSystem->mDb->query( $query, array( (int)$pAttachmentId ))) {
				if( $row = $result->fetchRow() ) {
					if( $func = $gLibertySystem->getPluginFunction( $row['attachment_plugin_guid'], 'load_function', 'mime' )) {
						$sql = "SELECT `pref_name`, `pref_value` FROM `".BIT_DB_PREFIX."liberty_attachment_prefs` WHERE `attachment_id` = ?";
						$prefs = $gBitSystem->mDb->getAssoc( $sql, array( $pAttachmentId ));
						$ret = $func( $row, $prefs, $pParams );
					}
				}
			}
		}
		return $ret;
	}

	/**
	 * getAttachment will load details of a given attachment
	 *
	 * @param numeric $pAttachmentId Attachment ID of the attachment
	 * @param array $pParams optional parameters that might contain information like display thumbnail size
	 * @access public
	 * @return attachment details
	 */
	public static function getAttachment( $pAttachmentId, $pParams = NULL ) {
		global $gLibertySystem, $gBitSystem;
		$ret = NULL;

		if( @BitBase::verifyId( $pAttachmentId )) {
			$query = "SELECT * FROM `".BIT_DB_PREFIX."liberty_attachments` la WHERE la.`attachment_id`=?";
			if( $result = $gBitSystem->mDb->query( $query, array( (int)$pAttachmentId ))) {
				if( $row = $result->fetchRow() ) {
					if( $func = $gLibertySystem->getPluginFunction( $row['attachment_plugin_guid'], 'load_function', 'mime' )) {
						$prefs = static::getAttachmentPreferences( $pAttachmentId );
						$ret = $func( $row, $prefs, $pParams );
					}
				}
			}
		}
		return $ret;
	}

	/**
	 * setPrimaryAttachment will set is_primary 'y' for the specified
	 * attachment and will ensure that all others are set to 'n'
	 *
	 * @param mixed   $pAttachmentId attachment id of the item we want to
	 *				  set as the primary attachment. Use 'none' to clear.
	 * @param numeric $pContentId content id we are working with.
	 * @param boolean $pAutoPrimary automatically set primary if there is only
	 *				  one attachment. Defaults to true.
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	public function setPrimaryAttachment( $pAttachmentId = NULL, $pContentId = NULL, $pAutoPrimary = TRUE ) {
		global $gBitSystem;

		$ret = FALSE;

		// If we are not given an attachment id but we where told the
		// content_id and we are supposed to auto set the primary then
		// figure out which one it is
		if( !@BitBase::verifyId( $pAttachmentId ) && ( empty( $pAttachmentId ) || $pAttachmentId != 'none' ) && @BitBase::verifyId( $pContentId ) && $pAutoPrimary ) {
			$query = "
				SELECT `attachment_id`
				FROM `".BIT_DB_PREFIX."liberty_content` lc
				INNER JOIN `".BIT_DB_PREFIX."liberty_attachments` la ON( lc.`content_id` = la.`content_id` )
				WHERE lc.`content_id` = ?";
			$pAttachmentId = $this->mDb->getOne( $query, array( $pContentId ));
		}

		// If we have an attachment_id we'll set it to this
		if( @BitBase::verifyId( $pAttachmentId )) {
			// get attachment we want to set primary
			$attachment = $this->getAttachment( $pAttachmentId );

			// Clear old primary. There can only be one!
			$this->clearPrimaryAttachment( $attachment['content_id'] );

			// now update the attachment to is_primary
			$query = "
				UPDATE `".BIT_DB_PREFIX."liberty_attachments`
				SET `is_primary` = ? WHERE `attachment_id` = ?";
			$this->mDb->query( $query, array( 'y', $pAttachmentId ));

			$ret = TRUE;
		// Otherwise, are we supposed to clear the primary entirely?
		} elseif( @BitBase::verifyId( $pContentId ) && !empty( $pAttachmentId ) && $pAttachmentId == 'none' ) {
			// Okay then do the job
			$this->clearPrimaryAttachment( $pContentId );
		}

		return $ret;
	}

	/**
	 * clearPrimaryAttachment will remove the primary flag for all attachments
	 * with the given content_id
	 *
	 * @param numeric the content_id for which primary should be unset.
	 * @return TRUE on succes
	 */
	function clearPrimaryAttachment( $pContentId ) {
		$ret = FALSE;

		if( @BitBase::verifyId( $pContentId )) {
			$query = "
				UPDATE `".BIT_DB_PREFIX."liberty_attachments`
				SET `is_primary` = ? WHERE `content_id` = ?";
			$this->mDb->query( $query, array( NULL, $pContentId ));
			$ret = TRUE;
		}

		return $ret;
	}
	// }}}


	/**
	 * === Attachment Preferences ===
	 */

	/**
	 * Returns the attachment preference value for the passed in key.
	 *
	 * @param string Hash key for the mPrefs value
	 * @param string Default value to return if the preference is empty
	 * @param int Optional content_id for arbitrary content preference
	 */
	function getAttachmentPreference( $pAttachmentId, $pPrefName, $pPrefDefault = NULL ) {
		if( is_null( $this->mStoragePrefs ) ) {
			$this->loadAttachmentPreferences();
		}

		$ret = NULL;
		if( @BitBase::verifyId( $pAttachmentId ) && !empty( $pPrefName )) {
			if( isset( $this->mStoragePrefs ) && isset( $this->mStoragePrefs[$pAttachmentId][$pPrefName] )) {
				$ret = $this->mStoragePrefs[$pAttachmentId][$pPrefName];
			} else {
				$ret = $pPrefDefault;
			}
		}

		return $ret;
	}

	/**
	 * Returns the attachment preferences for a given attachment id
	 *
	 * @param string Hash key for the mPrefs value
	 * @param string Default value to return if the preference is empty
	 * @param int Optional content_id for arbitrary content preference
	 */
	function getAttachmentPreferences( $pAttachmentId ) {
		global $gBitSystem;

		$ret = array();
		if( BitBase::verifyId( $pAttachmentId ) ) {
			if( !empty( $this ) && is_subclass_of( $this, "LibertyMime" ) ) {
				// we're loading from within object
				if( is_null( $this->mStoragePrefs )) {
					$this->loadAttachmentPreferences();
				}

				if( @BitBase::verifyId( $pAttachmentId ) && isset( $this->mStoragePrefs[$pAttachmentId] )) {
					$ret = $this->mStoragePrefs[$pAttachmentId];
				}
			} else {
				// if the object isn't loaded, we need to get the prefs from the database
				$sql = "SELECT `pref_name`, `pref_value` FROM `".BIT_DB_PREFIX."liberty_attachment_prefs` WHERE `attachment_id` = ?";
				$ret = $gBitSystem->mDb->getAssoc( $sql, array( (int)$pAttachmentId ));
			}
		}

		return $ret;
	}

	/**
	 * setAttachmentPreference will set an attachment preferences without storing it in the database
	 *
	 * @param array $pAttachmentId
	 * @param array $pPrefName
	 * @param array $pPrefValue
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function setAttachmentPreference( $pAttachmentId, $pPrefName, $pPrefValue ) {
		$this->mStoragePrefs[$pAttachmentId][$pPrefName] = $pPrefValue;
	}

	/**
	 * Saves a preference to the liberty_content_prefs database table with the
	 * given pref name and value. If the value is NULL, the existing value will
	 * be delete and the value will not be saved. However, a zero will be
	 * stored.
	 *
	 * @param string Hash key for the prefs value
	 * @param string Value for the prefs hash key
	 */
	function storeAttachmentPreference( $pAttachmentId, $pPrefName, $pPrefValue = NULL ) {
		global $gBitSystem;
		$ret = FALSE;
		if( @BitBase::verifyId( $pAttachmentId ) && !empty( $pPrefName )) {
			$query    = "DELETE FROM `".BIT_DB_PREFIX."liberty_attachment_prefs` WHERE `attachment_id` = ? AND `pref_name` = ?";
			$bindvars = array( $pAttachmentId, $pPrefName );
			$result   = $gBitSystem->mDb->query( $query, $bindvars );
			if( !is_null( $pPrefValue )) {
				$query      = "INSERT INTO `".BIT_DB_PREFIX."liberty_attachment_prefs` (`attachment_id`,`pref_name`,`pref_value`) VALUES(?, ?, ?)";
				$bindvars[] = substr( $pPrefValue, 0, 250 );
				$result     = $gBitSystem->mDb->query( $query, $bindvars );
			}

			// this function might be called statically
			if( !empty( $this ) && $this->isValid() ) {
				$this->mStoragePrefs[$pAttachmentId][$pPrefName] = $pPrefValue;
			}

			$ret = TRUE;
		}
		return $ret;
	}

	/**
	 * loadPreferences of the currently loaded object or pass in to get preferences of a specific content_id
	 *
	 * @param numeric $pContentId content_id of the item we want the prefs from (optional)
	 * @param numeric $pAttachmentId attachment_id of the item we want the prefs from (optional)
	 * @access public
	 * @return array of preferences if $pContentId or $pAttachmentId is set or pass preferences on to $this->mStoragePrefs
	 */
	function loadAttachmentPreferences( $pContentId = NULL ) {
		global $gBitSystem;

		if( !@BitBase::verifyId( $pContentId ) && $this->isValid() && @BitBase::verifyId( $this->mContentId )) {
			$pContentId = $this->mContentId;
			$store_prefs = TRUE;
		}

		$ret = array();
		if( !empty( $this ) && !is_null( $this->mStoragePrefs )) {
			$ret = $this->mStoragePrefs;
		} elseif( @BitBase::verifyId( $pContentId )) {
			$sql = "
				SELECT lap.`attachment_id`, lap.`pref_name`, lap.`pref_value`
				FROM `".BIT_DB_PREFIX."liberty_attachment_prefs` lap
					INNER JOIN `".BIT_DB_PREFIX."liberty_attachments` la ON (la.`attachment_id` = lap.`attachment_id`)
				WHERE la.`content_id` = ?";
			$result = $gBitSystem->mDb->query( $sql, array( $pContentId ));
			if( !empty( $result )) {
				while( $aux = $result->fetchRow() ) {
					$ret[$aux['attachment_id']][$aux['pref_name']] = $aux['pref_value'];
				}
			}
		}

		// if neither a content id nor an attachment id are given, we will place the results in mStoragePrefs
		if( !empty( $store_prefs )) {
			$this->mStoragePrefs = $ret;
		} else {
			return $ret;
		}
	}

	/**
	 * expungeAttachmentPreferences will remove all attachment preferences of a given attachmtent
	 *
	 * @param array $pAttachmentId attachemnt we want to remove the prefs for
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function expungeAttachmentPreferences( $pAttachmentId ) {
		global $gBitSystem;
		$ret = FALSE;
		if( @BitBase::verifyId( $pAttachmentId ) ) {
			$sql = "DELETE FROM `".BIT_DB_PREFIX."liberty_attachment_prefs` WHERE `attachment_id` = ?";
			$gBitSystem->mDb->query( $sql, array( $pAttachmentId ));
			$ret = TRUE;
		}
		return $ret;
	}

	public static function getAttachmentDownloadUrl( $pAttachmentId ) {
		global $gBitSystem;
		$ret = NULL;
		if( BitBase::verifyId( $pAttachmentId ) ) {
			if( $gBitSystem->isFeatureActive( "pretty_urls" ) || $gBitSystem->isFeatureActive( "pretty_urls_extended" )) {
				$ret = LIBERTY_PKG_URL."download/file/".$pAttachmentId;
			} else {
				$ret = LIBERTY_PKG_URL."download_file.php?attachment_id=".$pAttachmentId;
			}
		}
		return $ret;
	}

	public function getDownloadUrl() {
		$ret = "";
		if( $this->isValid() && $this->getField( 'attachment_id' ) ) {
			$ret = LibertyMime::getAttachmentDownloadUrl( $this->getField( 'attachment_id' ) );
		}
		return $ret;
	}

	// {{{ =================== Meta Methods ====================
	/**
	 * storeMetaData
	 *
	 * @param numeric $pAttachmentId AttachmentID the data belongs to
	 * @param string $pType Type of data. e.g.: EXIF, ID3. This will default to "Meta Data"
	 * @param array $pStoreHash Data that needs to be stored in the database in an array. The key will be used as the meta_title.
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	public static function storeMetaData( $pAttachmentId, $pType = "Meta Data", $pStoreHash ) {
		global $gBitSystem;
		$ret = FALSE;
		if( @BitBase::verifyId( $pAttachmentId ) && !empty( $pType ) && !empty( $pStoreHash )) {
			if( is_array( $pStoreHash )) {
				foreach( $pStoreHash as $key => $data ) {
					if( !is_array( $data )) {
						// store the data in the meta table
						$meta = array(
							'attachment_id' => $pAttachmentId,
							'meta_type_id'  => LibertyMime::storeMetaId( $pType, 'type' ),
							'meta_title_id' => LibertyMime::storeMetaId( $key, 'title' ),
						);

						// remove this entry from the database if it already exists
						$gBitSystem->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."liberty_attachment_meta_data` WHERE `attachment_id` = ? AND `meta_type_id` = ? AND `meta_title_id` = ?", $meta );

						// don't insert empty lines
						if( !empty( $data )) {
							$meta['meta_value'] = $data;
							$gBitSystem->mDb->associateInsert( BIT_DB_PREFIX."liberty_attachment_meta_data", $meta );
						}

						$ret = TRUE;
					} else {
						// should we recurse?
					}
				}
			}
		}
		return $ret;
	}

	/**
	 * storeMetaId
	 *
	 * @param string $pDescription Description of meta key. e.g.: Exif, ID3, Album, Artist
	 * @param string $pTable Table data is stored in - either 'type' or 'title'
	 * @access public
	 * @return newly stored ID on success, FALSE on failure
	 */
	private static function storeMetaId( $pDescription, $pTable = 'type' ) {
		global $gBitSystem;
		$ret = FALSE;
		if( !empty( $pDescription )) {
			if( !( $ret = LibertyMime::getMetaId( $pDescription, $pTable ))) {
				$store = array(
					"meta_{$pTable}_id" => $gBitSystem->mDb->GenID( "liberty_meta_{$pTable}s_id_seq" ),
					"meta_{$pTable}"    => LibertyMime::normalizeMetaDescription( $pDescription ),
				);
				$gBitSystem->mDb->associateInsert( BIT_DB_PREFIX."liberty_meta_{$pTable}s", $store );
				$ret = $store["meta_{$pTable}_id"];
			}
		}
		return $ret;
	}

	/**
	 * getMetaData
	 *
	 * @param numeric $pAttachmentId AttachmentID the data belongs to
	 * @param string $pType Type of data. e.g.: EXIF, ID3.
	 * @param string $pTitle Title of data. e.g.: Artist, Album.
	 * @access public
	 * @return array with meta data on success, FALSE on failure
	 * $note: Output format varies depending on requested data
	 */
	public static function getMetaData( $pAttachmentId, $pType = NULL, $pTitle = NULL ) {
		global $gBitSystem;
		$ret = array();
		if( @BitBase::verifyId( $pAttachmentId )) {
			$bindVars = array( $pAttachmentId );
			$whereSql = "";
			if( !empty( $pType ) && !empty( $pTitle )) {

				// we have a type and title - only one entry will be returned
				$bindVars[] = LibertyMime::normalizeMetaDescription( $pType );
				$bindVars[] = LibertyMime::normalizeMetaDescription( $pTitle );

				$sql = "
					SELECT lmd.`meta_value`
					FROM `".BIT_DB_PREFIX."liberty_attachment_meta_data` lmd
						INNER JOIN `".BIT_DB_PREFIX."liberty_meta_types` lmtype ON( lmd.`meta_type_id` = lmtype.`meta_type_id` )
						INNER JOIN `".BIT_DB_PREFIX."liberty_meta_titles` lmtitle ON( lmd.`meta_title_id` = lmtitle.`meta_title_id` )
					WHERE lmd.`attachment_id` = ? AND lmtype.`meta_type` = ? AND lmtitle.`meta_title` = ?";
				$ret = $gBitSystem->mDb->getOne( $sql, $bindVars );

			} elseif( !empty( $pType )) {

				// only type given - return array with all vlues of this type
				$bindVars[] = LibertyMime::normalizeMetaDescription( $pType );

				$sql = "
					SELECT lmtitle.`meta_title`, lmd.`meta_value`
					FROM `".BIT_DB_PREFIX."liberty_attachment_meta_data` lmd
						INNER JOIN `".BIT_DB_PREFIX."liberty_meta_types` lmtype ON( lmd.`meta_type_id` = lmtype.`meta_type_id` )
						INNER JOIN `".BIT_DB_PREFIX."liberty_meta_titles` lmtitle ON( lmd.`meta_title_id` = lmtitle.`meta_title_id` )
					WHERE lmd.`attachment_id` = ? AND lmtype.`meta_type` = ?";
				$ret = $gBitSystem->mDb->getAssoc( $sql, $bindVars );

			} elseif( !empty( $pTitle )) {

				// only title given - return array with all vlues with this title
				$bindVars[] = LibertyMime::normalizeMetaDescription( $pTitle );

				$sql = "
					SELECT lmtype.`meta_type`, lmd.`meta_value`
					FROM `".BIT_DB_PREFIX."liberty_attachment_meta_data` lmd
						INNER JOIN `".BIT_DB_PREFIX."liberty_meta_types` lmtype ON( lmd.`meta_type_id` = lmtype.`meta_type_id` )
						INNER JOIN `".BIT_DB_PREFIX."liberty_meta_titles` lmtitle ON( lmd.`meta_title_id` = lmtitle.`meta_title_id` )
					WHERE lmd.`attachment_id` = ? AND lmtitle.`meta_title` = ?";
				$ret = $gBitSystem->mDb->getAssoc( $sql, $bindVars );

			} else {

				// nothing given - return nested array based on type and title
				$sql = "
					SELECT lmd.`attachment_id`, lmd.`meta_value`, lmtype.`meta_type`, lmtitle.`meta_title`
					FROM `".BIT_DB_PREFIX."liberty_attachment_meta_data` lmd
						INNER JOIN `".BIT_DB_PREFIX."liberty_meta_types` lmtype ON( lmd.`meta_type_id` = lmtype.`meta_type_id` )
						INNER JOIN `".BIT_DB_PREFIX."liberty_meta_titles` lmtitle ON( lmd.`meta_title_id` = lmtitle.`meta_title_id` )
					WHERE lmd.`attachment_id` = ?";

				$result = $gBitSystem->mDb->query( $sql, $bindVars );
				while( $aux = $result->fetchRow() ) {
					$ret[$aux['meta_type']][$aux['meta_title']] = $aux['meta_value'];
				}
			}
		}
		return $ret;
	}

	/**
	 * expungeMetaData will remove the meta data for a given attachment
	 *
	 * @param array $pAttachmentId Attachment ID of attachment
	 * @access public
	 * @return query result
	 */
	function expungeMetaData( $pAttachmentId ) {
		global $gBitSystem;
		if( @BitBase::verifyId( $pAttachmentId )) {
			return $gBitSystem->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."liberty_attachment_meta_data` WHERE `attachment_id` = ?", array( $pAttachmentId ));
		}
	}

	/**
	 * getMetaId
	 *
	 * @param string $pDescription Description of meta key. e.g.: Exif, ID3, Album, Artist
	 * @param string $pTable Table data is stored in - either 'type' or 'title'
	 * @access public
	 * @return meta type or title id on sucess, FALSE on failure
	 */
	private static function getMetaId( $pDescription, $pTable = 'type' ) {
		global $gBitSystem;
		$ret = FALSE;
		if( !empty( $pDescription ) && ( $pTable == 'type' || $pTable == 'title' )) {
			$ret = $gBitSystem->mDb->getOne( "SELECT `meta_{$pTable}_id` FROM `".BIT_DB_PREFIX."liberty_meta_{$pTable}s` WHERE `meta_{$pTable}` = ?", array( LibertyMime::normalizeMetaDescription( $pDescription )));
		}
		return $ret;
	}

	/**
	 * getMetaDescription
	 *
	 * @param string $pId ID of type or title we want the description for
	 * @param string $pTable Table data is stored in - either 'type' or 'title'
	 * @access public
	 * @return description on sucess, FALSE on failure
	 */
	function getMetaDescription( $pId, $pTable = 'type' ) {
		global $gBitSystem;
		$ret = FALSE;
		if( @BitBase::verifyId( $pId )) {
			$ret = $gBitSystem->mDb->getOne( "SELECT `meta_{$pTable}` FROM `".BIT_DB_PREFIX."liberty_meta_{$pTable}s` WHERE `meta_{$pTable}_id` = ?", array( $pId ));
		}
		return $ret;
	}

	/**
	 * normalizeMetaDescription
	 *
	 * @param string $pDescription Description of meta key. e.g.: Exif, ID3, Album, Artist
	 * @access public
	 * @return normalized meta description that can be used as a guid
	 */
	public static function normalizeMetaDescription( $pDescription ) {
		return strtolower( substr( preg_replace( "![^a-zA-Z0-9]!", "", trim( $pDescription )), 0, 250 ));
	}
	// }}}
}

/**
 * mime_get_storage_sub_dir_name get a filename based on the uploaded file
 *
 * @param array $pFileHash File information provided in $_FILES
 * @access public
 * @return appropriate sub dir name
 */
if( !function_exists( 'liberty_mime_get_storage_sub_dir_name' )) {
	function liberty_mime_get_storage_sub_dir_name( $pFileHash = NULL ) {
		if( !empty( $pFileHash['type'] ) ) {
			// type is from upload file hash
			$mimeType = $pFileHash['type'];
		} elseif( !empty( $pFileHash['mime_type'] ) ) {
			// mime_type is from liberty_files
			$mimeType = $pFileHash['mime_type'];
		}

		if( !empty( $mimeType ) && strstr( $mimeType, "/" )) {
			$ret = strtolower( preg_replace( "!/.*$!", "", $mimeType ));
			// if we only got 'application' we will use the file extenstion
			if( $ret == 'application' && !empty( $pFileHash['name'] ) && ( $pos = strrpos( $pFileHash['name'], "." )) !== FALSE ) {
				$ret = strtolower( substr( $pFileHash['name'], $pos + 1 ));
			}
		}

		// append an 's' to not create an image and images dir side by side (legacy reasons)
		if( empty( $ret ) || $ret == 'image' ) {
			$ret = 'images';
		}
		return $ret;
	}
}

/**
 * liberty_mime_get_storage_branch - get url to store files for the feature site_upload_dir. It creates a calculable hierarchy of directories
 *
 * @access public
 * @author Christian Fowler<spider@steelsun.com>
 * @param $pParamHash key=>value pairs to determine path. Possible keys in descending directory depth are: 'user_id' indicates the 'users/.../<user_id>' branch or use the 'common' branch if null, 'package' - any desired directory below the StoragePath. this will be created if it doesn't exist, 'sub_dir' -  the sub-directory in the package organization directory, this is often a primary id such as attachment_id
 * @return string full path on local filsystem to store files.
 */
if( !function_exists( 'liberty_mime_get_storage_branch' )) {
	function liberty_mime_get_storage_branch( $pParamHash ) {
		// *PRIVATE FUNCTION. GO AWAY! DO NOT CALL DIRECTLY!!!
		global $gBitSystem;
		$pathParts = array();


		if( $pUserId = BitBase::getParameter( $pParamHash, 'user_id' ) ) {
			$pathParts[] = 'users';
			$pathParts[] = (int)($pUserId % 1000);
			$pathParts[] = $pUserId;
		} elseif( $pAttachmentId = BitBase::getParameter( $pParamHash, 'attachment_id' ) ) {
			$pathParts[] = 'attachments';
			$pathParts[] = (int)($pAttachmentId % 1000);
			$pathParts[] = $pAttachmentId;
		} else {
			$pathParts[] = 'common';
		}

		if( $pPackage = BitBase::getParameter( $pParamHash, 'package' ) ) {
			$pathParts[] = $pPackage;
		}
		// In case $pSubDir is multiple levels deep we'll need to mkdir each directory if they don't exist
		if( $pSubDir = BitBase::getParameter( $pParamHash, 'sub_dir' ) ){
			$pSubDirParts = preg_split('#/#',$pSubDir);
			foreach ($pSubDirParts as $subDir) {
				$pathParts[] = $subDir;
			}
		} else {
			$pSubDir = liberty_mime_get_storage_sub_dir_name( $pParamHash );
		}

		$fullPath = implode( '/', $pathParts ).'/';
		if( BitBase::getParameter( $pParamHash, 'create_dir', TRUE ) ){
			if( !file_exists( STORAGE_PKG_PATH.$fullPath ) ) {
				mkdir_p( STORAGE_PKG_PATH.$fullPath );
			}
		}

		return $fullPath;
	}
}

if( !function_exists( 'liberty_mime_get_storage_url' )) {
	function liberty_mime_get_storage_url( $pParamHash ) {
		return STORAGE_PKG_URL.liberty_mime_get_storage_branch( $pParamHash );
	}
}

if( !function_exists( 'liberty_mime_get_storage_path' )) {
	function liberty_mime_get_storage_path( $pParamHash ) {
		return STORAGE_PKG_PATH.liberty_mime_get_storage_branch( $pParamHash );
	}
}

if( !function_exists( 'liberty_mime_get_source_url' )) {
	function liberty_mime_get_source_url( $pParamHash ) {
		$fileName = BitBase::getParameter( $pParamHash, 'file_name' );
		if( empty( $pParamHash['package'] ) ) {
			$pParamHash['package'] = liberty_mime_get_storage_sub_dir_name( array( 'mime_type' => BitBase::getParameter( $pParamHash, 'mime_type' ), 'name' => $fileName ) );
		}
		if( empty( $pParamHash['sub_dir'] ) ) {
			$pParamHash['sub_dir'] = BitBase::getParameter( $pParamHash, 'attachment_id' );
		}
		$defaultFileName = liberty_mime_get_default_file_name( $fileName, BitBase::getParameter( $pParamHash, 'mime_type' ) );
		$fileBranch = liberty_mime_get_storage_branch( $pParamHash );
		if( file_exists( STORAGE_PKG_PATH.$fileBranch.$defaultFileName ) ) {
			$ret = STORAGE_PKG_URL.$fileBranch.$defaultFileName;
		} else {
			$ret = STORAGE_PKG_URL.$fileBranch.basename( BitBase::getParameter( $pParamHash, 'file_name' ) );
		}
		return $ret;
	}
}

if( !function_exists( 'liberty_mime_get_source_file' )) {
	function liberty_mime_get_source_file( $pParamHash ) {
		$fileName = BitBase::getParameter( $pParamHash, 'file_name' );
		if( empty( $pParamHash['package'] ) ) {
			$pParamHash['package'] = liberty_mime_get_storage_sub_dir_name( array( 'mime_type' => BitBase::getParameter( $pParamHash, 'mime_type' ), 'name' => $fileName ) );
		}
		if( empty( $pParamHash['sub_dir'] ) ) {
			$pParamHash['sub_dir'] = BitBase::getParameter( $pParamHash, 'attachment_id' );
		}
		$defaultFileName = liberty_mime_get_default_file_name( $fileName, BitBase::getParameter( $pParamHash, 'mime_type' ) );
		$ret = STORAGE_PKG_PATH.liberty_mime_get_storage_branch( $pParamHash ).$defaultFileName;
		if( !file_exists( $ret ) ) {
			$ret = STORAGE_PKG_PATH.liberty_mime_get_storage_branch( $pParamHash ).basename( BitBase::getParameter( $pParamHash, 'file_name' ) );
		}
		return $ret;
	}
}

if( !function_exists( 'liberty_mime_get_default_file_name' )) {
	function liberty_mime_get_default_file_name( $pFileName, $pMimeType ) {
		global $gBitSystem;

		if( empty( $pMimeType ) ) {
			$pMimeType = $gBitSystem->lookupMimeType( substr( $pFileName, strrpos( $pFileName, '.' ) + 1 ) );
		}

		if( $gBitSystem->isFeatureActive( 'liberty_originalize_file_names' ) ) {
			$ret = 'original.'.$gBitSystem->getMimeExtension( $pMimeType );
		} else {
			$ret = $pFileName;
		}
		return $ret;
	}
}

/* vim: :set fdm=marker : */

?>
