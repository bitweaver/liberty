<?php
/**
 * Manages liberty Uploads
 *
 * @package  liberty
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/LibertyMime.php,v 1.5 2008/05/20 18:08:28 squareing Exp $
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
	function load( $pContentId = NULL ) {
		// assume a derived class has joined on the liberty_content table, and loaded it's columns already.
		global $gLibertySystem;
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
					if( $func = $gLibertySystem->getPluginFunction( $row['attachment_plugin_guid'], 'load_function' )) {
						// we will pass the preferences by reference that the plugin can easily update them
						if( empty( $this->mStoragePrefs[$row['attachment_id']] )) {
							$this->mStoragePrefs[$row['attachment_id']] = array();
						}
						$this->mStorage[$row['attachment_id']] = $func( $row, $this->mStoragePrefs[$row['attachment_id']] );
					} else {
						print "No load_function for ".$row['attachment_plugin_guid']." ".$gLibertySystem->mPlugins[$row['attachment_plugin_guid']];
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
		if( LibertyContent::store( $pStoreHash ) && LibertyMime::verify( $pStoreHash ) && !empty( $pStoreHash['upload_store']['files'] )) {
			// short hand
			$this->mDb->StartTrans();

			foreach( $pStoreHash['upload_store']['files'] as $upload ) {
				$storeRow = $pStoreHash['upload_store'];
				unset( $storeRow['files'] );

				// copy by reference that the filetype when changes are made in lookupMimeHandler()
				$storeRow['upload'] = &$upload;

				// when content is created the content_id is only available after LibertyContent::store()
				if( !@BitBase::verifyId( $pStoreHash['content_id'] )) {
					// this probably isn't necessary anymore since every attachment should have a content_id associated with it
					$storeRow['content_id'] = NULL;
				} else {
					$storeRow['content_id'] = $pStoreHash['content_id'];
				}

				// call the appropriate plugin to deal with the upload
				$guid = $gLibertySystem->lookupMimeHandler( $upload );
				if( $verify_function = LibertyMime::getPluginFunction( $guid, 'verify_function' )) {
					// verify the uploaded file using the plugin
					if( $verify_function( $storeRow )) {
						// if we have an attachment id we know it's an update
						if( @BitBase::verifyId( $upload['attachment_id'] )) {
							$function_name = 'update_function';
						} else {
							$function_name = 'store_function';
						}

						if( $store_function = LibertyMime::getPluginFunction( $guid, $function_name )) {
							if( !$store_function( $storeRow, $this )) {
								$this->mErrors = array_merge( $this->mErrors, $upload['errors'] );
							}
						} else {
							$this->mErrors['store_function'] = tra( 'No suitable store function found.' );
						}
					} else {
						$this->mErrors = array_merge( $this->mErrors, $upload['errors'] );
					}
				} else {
					$this->mErrors['verify_function'] = tra( 'No suitable verify function found.' );
				}
			}

			$this->mDb->CompleteTrans();
		}

		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * Verify content that is about to be stored
	 * 
	 * @param array $pStoreHash hash of all data that needs to be stored in the database
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason
	 * @Note: If one of the uploaded files is an update, place the attachment_id with the upload hash in $_FILES or in _files_override
	 */
	function verify( &$pParamHash ) {
		global $gBitUser;

		// check to see if we have any files to upload
		if( isset( $pParamHash['_files_override'] )) {
			// we have been passed in a manually stuffed files attachment, such as a custom uploader would have done.
			// process this, and skip over $_FILES
			$uploads = $pParamHash['_files_override'];
		} elseif( !empty( $_FILES ) ) {
			// we have some _FILES hanging around we will gobble up. This is inherently dagnerous chewing up a _FILES like this as 
			// it can cause premature storing of a _FILE if you are trying to store multiple pieces of content at once.
			foreach( $_FILES as $key => $file ) {
				if( !empty( $file['name'] )) {
					$uploads[$key] = $file;
				}
			}
		}

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

		return ( count( $this->mErrors ) == 0 );
	}

	/**
	 * verifyAttachment will perform a generic check if a file is valid for processing
	 * 
	 * @param array $pFile file array from $_FILES
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function verifyAttachment( $pFile ) {
		if( !empty( $pFile['tmp_name'] ) && is_file( $pFile['tmp_name'] ) && empty( $pFile['error'] )) {
			return $pFile;
		}
	}

	/**
	 * getAttachment will load details of a given attachment
	 * 
	 * @param array $pAttachmentId Attachment ID of the attachment
	 * @access public
	 * @return attachment details
	 */
	function getAttachment( $pAttachmentId, $pParams = NULL ) {
		require_once( LIBERTY_PKG_PATH.'LibertyMime.php' );
		global $gLibertySystem, $gBitSystem;
		$ret = NULL;

		if( @BitBase::verifyId( $pAttachmentId )) {
			$query = "SELECT * FROM `".BIT_DB_PREFIX."liberty_attachments` la WHERE la.`attachment_id`=?";
			if( $result = $gBitSystem->mDb->query( $query, array( (int)$pAttachmentId ))) {
				if( $row = $result->fetchRow() ) {
					if( $func = $gLibertySystem->getPluginFunction( $row['attachment_plugin_guid'], 'load_function' )) {
						$prefs = array();
						// if the object is available, we'll copy the preferences by reference to allow the plugin to update them as needed
						if( !empty( $this )) {
							if( !empty( $this->mStoragePrefs[$pAttachmentId] )) {
								$prefs = &$this->mStoragePrefs[$pAttachmentId];
							}
						} else {
							$prefs = LibertyMime::getAttachmentPreferences( $pAttachmentId );
						}
						$ret = $func( $row, $prefs, $pParams );
					}
				}
			}
		}
		return $ret;
	}

	/**
	 * Get the function of the plugin responsible for dealing with a given upload
	 * 
	 * @param string $pGuid GUID of plugin used
	 * @param string $pFunctionName Function type we want to use
	 * @access public
	 * @return function name
	 * @TODO: Move this to LibertySystem. Currently it's here since LibertyMime is a test project...
	 */
	function getPluginFunction( $pGuid, $pFunctionName ) {
		global $gLibertySystem;
		// if we can't get a function on the first round, we fetch the default
		if( !( $ret = $gLibertySystem->getPluginFunction( $pGuid, $pFunctionName ))) {
			$ret = $gLibertySystem->getPluginFunction( LIBERTY_DEFAULT_MIME_HANDLER, $pFunctionName );
		}

		return $ret;
	}

	/**
	 * getMimeTemplate will fetch an appropriate template to display a given filetype
	 * 
	 * @param array $pTemplate 
	 * @param array $pGuid 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getMimeTemplate( $pTemplate, $pGuid ) {
		global $gLibertySystem;
		if( $plugin = $gLibertySystem->getPluginInfo( $pGuid )) {
			if( !empty( $plugin[$pTemplate.'_tpl'] )) {
				return $plugin[$pTemplate.'_tpl'];
			} else {
				return LibertyMime::getMimeTemplate( $pTemplate, LIBERTY_DEFAULT_MIME_HANDLER );
			}
		}
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
		if( !empty( $this )) {
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

		$return = TRUE;
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
}
?>
