<?php
/**
 * Manages liberty Uploads
 *
 * @package  liberty
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/LibertyMime.php,v 1.22 2008/06/27 08:43:42 squareing Exp $
 */

/**
 * required setup
 */
require_once( LIBERTY_PKG_PATH.'LibertyAttachable.php' );
define( 'LIBERTY_DEFAULT_MIME_HANDLER', 'mimedefault' );

/**
 * LibertyMime class
 *
 * @package liberty
 */
class LibertyMime extends LibertyAttachable {
	var $mStoragePrefs = NULL;

	/**
	 * Initiates class
	 *
	 * @access public
	 * @return void
	 */
	function LibertyMime() {
		LibertyAttachable::LibertyAttachable();
	}

	/**
	 * load the attachments for a given content id and then stuff them in mStorage
	 * 
	 * @param array $pContentId 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function load( $pContentId = NULL, $pPluginParams = NULL ) {
		global $gLibertySystem;
		// assume a derived class has joined on the liberty_content table, and loaded it's columns already.
		$contentId = ( @BitBase::verifyId( $pContentId ) ? $pContentId : $this->mContentId );

		if( @BitBase::verifyId( $contentId )) {
			// load up the content
			LibertyContent::load( $contentId );

			// load the storage preferences if there are any
			$this->loadAttachmentPreferences();

			$query = "SELECT * FROM `".BIT_DB_PREFIX."liberty_attachments` la WHERE la.`content_id`=? ORDER BY la.`pos` ASC, la.`attachment_id` ASC";
			if( $result = $this->mDb->query( $query,array( (int)$contentId ))) {
				$this->mStorage = array();
				while( $row = $result->fetchRow() ) {
					if( $func = $gLibertySystem->getPluginFunction( $row['attachment_plugin_guid'], 'load_function', 'mime' )) {
						// we will pass the preferences by reference that the plugin can easily update them
						if( empty( $this->mStoragePrefs[$row['attachment_id']] )) {
							$this->mStoragePrefs[$row['attachment_id']] = array();
						}
						$this->mStorage[$row['attachment_id']] = $func( $row, $this->mStoragePrefs[$row['attachment_id']], $pPluginParams );
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
	function store( &$pStoreHash ) {
		global $gLibertySystem;
		// make sure all the data is in order
		if( LibertyMime::verify( $pStoreHash ) && ( !empty( $pStoreHash['skip_content_store'] ) || LibertyContent::store( $pStoreHash ) ) ) {
			// files have been uploaded
			if( !empty( $pStoreHash['upload_store']['files'] ) && is_array( $pStoreHash['upload_store']['files'] )) {
				$this->mDb->StartTrans();

				foreach( $pStoreHash['upload_store']['files'] as $upload ) {
					// we might be updating attachments and they might have some additional data they need to process
					if( @BitBase::verifyId( $upload['attachment_id'] ) && !empty( $pStoreHash['plugin'][$upload['attachment_id']] ) && is_array( $pStoreHash['plugin'][$upload['attachment_id']] )) {
						foreach( $pStoreHash['plugin'][$upload['attachment_id']] as $guid => $params ) {
							$this->updateAttachmentParams( $upload['attachment_id'], $guid, $params );
						}
					}

					// exit if $upload is empty
					if( empty( $upload['tmp_name'] )) {
						break;
					}

					$storeRow = $pStoreHash['upload_store'];
					unset( $storeRow['files'] );

					// copy by reference that the filetype when changes are made in lookupMimeHandler()
					$storeRow['upload'] = &$upload;

					// when content is created the content_id is only available after LibertyContent::store()
					$storeRow['content_id'] = $pStoreHash['content_id'];

					// let the plugin do the rest
					$guid = $gLibertySystem->lookupMimeHandler( $upload );
					$this->pluginStore( $storeRow, $guid, @BitBase::verifyId( $upload['attachment_id'] ));
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
				$this->mDb->CompleteTrans();
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
	function pluginStore( $pStoreHash, $pGuid, $pUpdate = FALSE ) {
		global $gLibertySystem;
		if( !empty( $pStoreHash ) && $verify_function = $gLibertySystem->getPluginFunction( $pGuid, 'verify_function' )) {
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
	function verify( &$pParamHash ) {
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
			if( !$gBitUser->hasPermission( 'p_liberty_attach_attachments' )) {
				$this->mErrors['permission'] = tra( 'You do not have permission to upload attachments.' );
			}
		}

		// primary attachment. Allow 'none' to clear the primary.
		if( !@BitBase::verifyId( $pParamHash['liberty_attachments']['primary'] ) && ( empty( $pParamHash['liberty_attachments']['primary'] ) || $pParamHash['liberty_attachments']['primary'] != 'none' ) ) {
			$pParamHash['liberty_attachments']['primary'] = NULL;
		}

		return ( count( $this->mErrors ) == 0 );
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
	function updateAttachmentParams( $pAttachmentId, $pPluginGuid, $pParamHash ) {
		global $gLibertySystem;
		$ret = FALSE;

		if( BitBase::verifyId( $pAttachmentId )) {
			if( !empty( $this ) && !empty( $this->mStorage[$pAttachmentId] )) {
				$file = $this->mStorage[$pAttachmentId];
			} else {
				$file = LibertyMime::getAttachment( $pAttachmentId );
			}

			if( @BitBase::verifyId( $file['attachment_id'] ) && !empty( $pPluginGuid ) && !empty( $pParamHash ) && ( $update_function = $gLibertySystem->getPluginFunction( $pPluginGuid, 'update_function', 'mime' ))) {
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
	function verifyAttachment( $pFile ) {
		if( !empty( $pFile['tmp_name'] ) && is_file( $pFile['tmp_name'] ) && empty( $pFile['error'] ) || !empty( $pFile['attachment_id'] )) {
			return $pFile;
		}
	}

	/**
	 * Get the function of the plugin responsible for dealing with a given upload
	 * 
	 * @param string $pGuid GUID of plugin used
	 * @param string $pFunctionName Function type we want to use
	 * @param boolean $pGetDefault Get default function if we can't find the specified one
	 * @access public
	 * @return function name
	 */
	function getPluginFunction( $pGuid, $pFunctionName, $pGetDefault = 'mime' ) {
		deprecated( 'Please call $gLibertySystem->getMimePluginFunction() directly' );
		global $gLibertySystem;
		return $gLibertySystem->getPluginFunction( LIBERTY_DEFAULT_MIME_HANDLER, $pFunctionName, $pGetDefault );
	}

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
			$ret = $gBitSystem->mDb->getAssoc( $sql, array( $pAttachmentId ));
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
			while( $aux = $result->fetchRow() ) {
				$ret[$aux['attachment_id']][$aux['pref_name']] = $aux['pref_value'];
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
}
?>
