<?php
/**
 * Management of Liberty Content
 *
 * @package  liberty
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/LibertyAttachable.php,v 1.141 2008/05/09 10:13:14 squareing Exp $
 * @author   spider <spider@steelsun.com>
 */
// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See copyright.txt for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Authors: spider <spider@steelsun.com>
// +----------------------------------------------------------------------+

/**
 * required setup
 */
require_once( LIBERTY_PKG_PATH.'LibertyContent.php' );
require_once( LIBERTY_PKG_PATH.'LibertySystem.php' );

// load the image processor plugin, check for loaded 'gd' since that is the default processor, and config might not be set.
if( $gBitSystem->isFeatureActive( 'image_processor' ) || extension_loaded( 'gd' ) ) {
	require_once( LIBERTY_PKG_PATH."plugins/processor.".$gBitSystem->getConfig( 'image_processor','gd' ).".php" );
}

// maximum size of the 'original' image when converted to jpg
define( 'MAX_THUMBNAIL_DIMENSION', 99999 );

/**
 * LibertyAttachable class
 *
 * @package liberty
 */
class LibertyAttachable extends LibertyContent {
	var $mContentId;
	var $mStorage;

	function LibertyAttachable() {
		LibertyContent::LibertyContent();
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
	function getStoragePath( $pSubDir = NULL, $pUserId = NULL, $pPackage = ACTIVE_PACKAGE, $pPermissions = 0755, $pRootDir = NULL ) {
		$ret = null;
		if( $storageUrl = LibertyAttachable::getStorageBranch( $pSubDir, $pUserId, $pPackage, $pPermissions, $pRootDir ) ) {
			//$ret = BIT_ROOT_PATH.$storageUrl;
			$ret = ( !empty( $pRootDir ) ? $pRootDir : BIT_ROOT_PATH ).$storageUrl;
			//$ret = $storageUrl;
		}
		return $ret;
	}


	function getStorageUrl( $pSubDir = NULL, $pUserId = NULL, $pPackage = ACTIVE_PACKAGE, $pPermissions = 0755, $pRootDir = NULL ) {
		return BIT_ROOT_URL.LibertyAttachable::getStorageBranch( $pSubDir, $pUserId, $pPackage, $pPermissions, $pRootDir );
	}

	function getStorageSubDirName() {
		return 'images';
	}

	/**
	* getStorageBranch - get url to store files for the feature site_upload_dir. It creates a calculable hierarchy of directories
	*
	* @access public
	* @author Christian Fowler<spider@steelsun.com>
	* @param $pSubDir any desired directory below the StoragePath. this will be created if it doesn't exist
	* @param $pUserId indicates the 'users/.../<user_id>' branch or use the 'common' branch if null
	* @param $pRootDir override BIT_ROOT_DIR with a custom absolute path - useful for areas where no we access should be allowed
	* @return string full path on local filsystem to store files.
	*/
	function getStorageBranch( $pSubDir = NULL, $pUserId = NULL, $pPackage = ACTIVE_PACKAGE, $pPermissions = 0755, $pRootDir = NULL ) {
		// *PRIVATE FUNCTION. GO AWAY! DO NOT CALL DIRECTLY!!!
		global $gBitSystem;
		$pathParts = array();
		$pathParts = split( '/', trim( STORAGE_PKG_PATH, '/\\' ) );

		if( !$pUserId ) {
			$pathParts[] = 'common';
		} else {
			$pathParts[] = 'users';
			$pathParts[] = (int)($pUserId % 1000);
			$pathParts[] = $pUserId;
		}

		if( $pPackage ) {
			$pathParts[] = $pPackage;
		}
		// In case $pSubDir is multiple levels deep we'll need to mkdir each directory if they don't exist
		$pSubDirParts = split('/',$pSubDir);
		foreach ($pSubDirParts as $subDir) {
			$pathParts[] = $subDir;
		}

		$fullPath = implode( $pathParts, '/' ).'/';

		mkdir_p( $fullPath );
		$ret = substr( $fullPath, strlen( dirname( STORAGE_PKG_PATH ) ) );
		return $ret;
	}

	// Private Method used during verify to extract 
	function extractMetaData( &$pParamHash, &$pFile ) {

		// Process a JPEG , jpeg_metadata_tk REQUIRES short_tags because that is the way it was written. feel free to fix something. XOXO spiderr
		if( ini_get( 'short_open_tag' ) && function_exists( 'exif_read_data' ) && !empty( $pFile['tmp_name'] ) && strpos( strtolower($pFile['type']), 'jpeg' ) !== FALSE ) {
			$exifHash = @exif_read_data( $pFile['tmp_name'], 0, true);
			//vd( $exifHash );

			// Change: Allow this example file to be easily relocatable - as of version 1.11
			require_once UTIL_PKG_PATH.'jpeg_metadata_tk/JPEG.php';
			require_once UTIL_PKG_PATH.'jpeg_metadata_tk/JFIF.php';
			require_once UTIL_PKG_PATH.'jpeg_metadata_tk/PictureInfo.php';
			require_once UTIL_PKG_PATH.'jpeg_metadata_tk/XMP.php';
			require_once UTIL_PKG_PATH.'jpeg_metadata_tk/EXIF.php';

			// Retrieve the header information from the JPEG file
			$jpeg_header_data = get_jpeg_header_data( $pFile['tmp_name'] );

			// Retrieve EXIF information from the JPEG file
			$Exif_array = get_EXIF_JPEG( $pFile['tmp_name'] );

			// Retrieve XMP information from the JPEG file
			$XMP_array = read_XMP_array_from_text( get_XMP_text( $jpeg_header_data ) );

			// Retrieve Photoshop IRB information from the JPEG file
			$IRB_array = get_Photoshop_IRB( $jpeg_header_data );
			if( !empty( $exifHash['IFD0']['Software'] ) && preg_match( '/photoshop/i', $exifHash['IFD0']['Software'] ) ) {
				require_once UTIL_PKG_PATH.'jpeg_metadata_tk/Photoshop_File_Info.php';
				// Retrieve Photoshop File Info from the three previous arrays
				$psFileInfo = get_photoshop_file_info( $Exif_array, $XMP_array, $IRB_array );

				if( !empty( $psFileInfo['headline'] ) ) {
					if( empty( $pParamHash['title'] ) ) {
						$pParamHash['title'] = $psFileInfo['headline'];
					} elseif( empty( $pParamHash['edit'] ) && !$this->getField( 'data' ) && $pParamHash['title'] != $psFileInfo['headline'] ) {
						$pParamHash['edit'] = $psFileInfo['headline'];
					}
				}
				if( !empty( $psFileInfo['caption'] ) ) {
					if( empty( $pParamHash['title'] ) ) {
						$pParamHash['title'] = $psFileInfo['caption'];
					} elseif( empty( $pParamHash['edit'] ) && !$this->getField( 'data' ) && $pParamHash['title'] != $psFileInfo['caption'] ) {
						$pParamHash['edit'] = $psFileInfo['caption'];
					}
				}
			}

			if( !empty( $exifHash['EXIF']['DateTimeOriginal'] ) ) {
				$pParamHash['event_time'] = strtotime( $exifHash['EXIF']['DateTimeOriginal'] );
			}

			if( !empty( $exifHash['IFD0']['ImageDescription'] ) ) {
				if( empty( $pParamHash['title'] ) ) {
					$pParamHash['title'] = $exifHash['IFD0']['ImageDescription'];
				} elseif( empty( $pParamHash['edit'] ) && !$this->getField( 'data' ) && $pParamHash['title'] != $exifHash['IFD0']['ImageDescription'] ) {
					$pParamHash['edit'] = $exifHash['IFD0']['ImageDescription'];
				}
			}
		}


	}

	function verifyAttachment( &$pParamHash, $pFile, $pKey ) {
		global $gBitSystem, $gBitUser, $gLibertySystem;

		if( !empty( $pFile ) && !empty( $pFile['size'] ) ) {
			if( empty( $pParamHash['storage_guid'] )) {
				// only file format storage available at present 
				$pParamHash['storage_guid'] = $storageGuid = PLUGIN_GUID_BIT_FILES;
			} else {
				$storageGuid = $pParamHash['storage_guid'];
			}

			if( !empty( $pFile['size'] ) ) {
				$this->extractMetaData( $pParamHash, $pFile );
				// meta data may be stupid and have stuffed title with all spaces
				if( !empty( $pParamHash['title'] ) ) {
					$pParamHash['title'] = trim( $pParamHash['title'] );
				}

				// let's add a default title
				if( empty( $pParamHash['title'] ) && !empty( $pFile['name'] ) ) {
					if( preg_match( '/^[A-Z]:\\\/', $pFile['name'] ) ) {
						// MSIE shit file names if passthrough via gigaupload, etc.
						// basename will not work - see http://us3.php.net/manual/en/function.basename.php
						$tmp = preg_split("[\\\]",$pFile['name']);
						$defaultName = $tmp[count($tmp) - 1];
					} elseif( strpos( '.', $pFile['name'] ) ) {
						list( $defaultName, $ext ) = explode( '.', $pFile['name'] );
					} else {
						$defaultName = $pFile['name'];
					}
					$pParamHash['title'] = str_replace( '_', ' ', substr( $defaultName, 0, strrpos( $defaultName, '.' ) ) );
				}


				if ( !is_windows() ) {
					list( $pFile['name'], $pFile['type'] ) = $gBitSystem->verifyFileExtension( $pFile['tmp_name'], $pFile['name'] );
				} else {
//						$pFile['type'] = $gBitSystem->verifyMimeType( $pFile['tmp_name'] );
				}
				// clean out crap that can make life difficult in server maintenance
				$cleanedBaseName = preg_replace( '/[&\%:\/\\\]/', '', substr( $pFile['name'], 0, strrpos( $pFile['name'], '.' ) ) );
				$pFile['dest_base_name'] = $cleanedBaseName;
				$pFile['source_file'] = $pFile['tmp_name'];
				// lowercase all file extensions
				$pFile['name'] = $cleanedBaseName.strtolower( substr( $pFile['name'], strrpos( $pFile['name'], '.' ) ) );
				if (!isset($pParamHash['STORAGE'][$storageGuid])) {
					$pParamHash['STORAGE'][$storageGuid] = array();
				}
				$pParamHash['STORAGE'][$storageGuid][$pKey] = array('upload' => &$pFile);
			}
		}
	}

	
	/**
	* verify - standard API method, with a twist. It will gobble up anything in $_FILES if available, unless an array of arrays is passed in to  $pParamHash['_files_override']
	*
	* @access private
	* @author Christian Fowler<spider@steelsun.com>
	* @param $pParamHash
	* @return FALSE if errors were present, TRUE meaning object is ready to store
	*/
	function verify( &$pParamHash ) {
		global $gBitSystem, $gBitUser;
		// check to see if we have any files to upload
		if( isset( $pParamHash['_files_override'] ) ) {
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

		// don't check for p_liberty_attach_attachments permission on bitpermuser class so registration with avatar upload works
		if( strtolower( get_class( $this )) == 'bitpermuser' ) {
			$pParamHash['no_perm_check'] = TRUE;
		}

		// check for the required permissions to upload a file to the liberty attachments area
		if( !empty( $uploads ) && empty( $pParamHash['no_perm_check'] )) {
			if( !$gBitUser->hasPermission( 'p_liberty_attach_attachments' )) {
				$this->mErrors['permission'] = tra( 'You do not have permission to upload attachments.' );
			}
		}

		if( !empty( $pParamHash['attachment_id'] ) && !$this->verifyId( $pParamHash['attachment_id'] ) ) {
			$this->mErrors['file'] = tra('System Error: Non-numeric storage_id.');
		}

		if( empty( $pParamHash['user_id'] ) ) {
			// storage is always owned by the user that uploaded it!
			// er... or at least admin if somehow we have a NULL mUserId - anon uploads maybe?
			$pParamHash['user_id'] = @$this->verifyId( $gBitUser->mUserId ) ? $gBitUser->mUserId : ROOT_USER_ID;
		}
		if( empty( $pParamHash['process_storage'] ) ) {
			$pParamHash['process_storage'] = NULL;
		}

		if( empty( $pParamHash['subdir'] ) ) {
			$pParamHash['subdir'] = 'files';
		}

		if( !empty( $uploads ) ) {
			foreach( array_keys( $uploads ) as $f ) {
				$this->verifyAttachment( $pParamHash, $uploads[$f], $f );
			}
		}

		// primary attachment. Allow 'none' to clear the primary.
		if( !@BitBase::verifyId( $pParamHash['liberty_attachments']['primary'] ) && ( empty( $pParamHash['liberty_attachments']['primary'] ) || $pParamHash['liberty_attachments']['primary'] != 'none' ) ) {
			$pParamHash['liberty_attachments']['primary'] = NULL;
		}

		return ( count( $this->mErrors ) == 0 );
	}

	/* store -- Stores any attachments
	 *
	 * pass $pParamHash['liberty_attachable']['skip_content_store'] == TRUE
	 * to avoid the underlying content store and simply store the attachments.
	 *
	 * verify() will shove things to store into $pParamHash['STORAGE'] to be
	 * gobbled up in this function.
	 *
	 * pass $pParamHash['liberty_attachable']['auto_primary'] == FALSE to turn off the auto
	 * primary on first attachment feature for a content type.
	 *
	 * @param hash $pParamHash The hash of arguments
	 *
	 */
	function store( &$pParamHash ) {
		global $gLibertySystem, $gBitSystem, $gBitUser;
		$this->mDb->StartTrans();
		if( LibertyAttachable::verify( $pParamHash ) && ( isset($pParamHash['skip_content_store']) ||  LibertyContent::store( $pParamHash ) ) ) {

			if(!empty( $pParamHash['STORAGE'] ) && count( $pParamHash['STORAGE'] ) ) {
				foreach( array_keys( $pParamHash['STORAGE'] ) as $guid ) {
					$storeRows = &$pParamHash['STORAGE'][$guid]; // short hand variable assignment
					// If it is empty then nothing more to do. Avoid error in foreach.
					if (empty($storeRows)) {
						continue;
					}
					foreach( $storeRows as $key => $value ) {
						$storeRow = &$pParamHash['STORAGE'][$guid][$key];
						$storeRow['plugin_guid'] = $guid;

						if (!@BitBase::verifyId($pParamHash['content_id'])) {
							$storeRow['content_id'] = NULL;
						} else {
							$storeRow['content_id'] = $pParamHash['content_id']; // copy in content_id
						}

						if (!empty($pParamHash['user_id'])) {
							$storeRow['user_id'] = $pParamHash['user_id']; // copy in the user_id
						} else {
							$storeRow['user_id'] = $gBitUser->mUserId;
						}

						// do we have a verify function for this storage type, and do things verify?
						$verifyFunc = $gLibertySystem->getPluginFunction( $guid, 'verify_function' );
						if( $verifyFunc && $verifyFunc( $storeRow ) ) {
							// For backwards compatibility with a single upload.
							if( @BitBase::verifyId( $pParamHash['attachment_id'] )) {
								$storeRow['upload']['attachment_id'] = $storeRow['attachment_id'] = $pParamHash['attachment_id'];
							} else if ( !isset($storeRow['skip_insert'] ) ) {
								if ( defined( 'LINKED_ATTACHMENTS' ) && @BitBase::verifyId( $pParamHash['content_id'] ) ) {
									$storeRow['upload']['attachment_id'] = $storeRow['attachment_id'] = $pParamHash['content_id'];
								} else {
									$storeRow['upload']['attachment_id'] = $storeRow['attachment_id'] = 
										defined( 'LINKED_ATTACHMENTS' ) ? $this->mDb->GenID( 'liberty_content_id_seq') : $this->mDb->GenID( 'liberty_attachments_id_seq' );
								}	
							}

							// if we have uploaded a file, we can take care of that generically
							if( !empty( $storeRow['upload'] ) && is_array( $storeRow['upload'] ) && !empty( $storeRow['upload']['size'] ) ) {
								if( empty( $storeRow['upload']['type'] ) ) {
									$ext = substr( $storeRow['upload']['name'], strrpos( $storeRow['upload']['name'], '.' ) + 1 );
									$storeRow['upload']['type'] = $gBitSystem->lookupMimeType( $ext );
								}
								$storeRow['upload']['dest_path'] = $this->getStorageBranch( $storeRow['attachment_id'], $storeRow['user_id'], $this->getStorageSubDirName() );
								if (!empty( $pParamHash['thumbnail_sizes'] ) ) {
									$storeRow['upload']['thumbnail_sizes'] = $pParamHash['thumbnail_sizes'];
								}
								$storagePath = liberty_process_upload( $storeRow );
								// We're gonna store to local file system & liberty_files table
								if( empty( $storagePath ) ) {
									$this->mErrors['file'] = tra( "Could not store file" ).": ".$storeRow['upload']['name'].'.';
									$storeRow['attachment_id'] = NULL;
									$storeRow['upload']['attachment_id'] = NULL;
								} else {
									$storeRow['upload']['dest_file_path'] = $storagePath;
								}
							}

							if( @BitBase::verifyId( $storeRow['attachment_id'] ) && $storeFunc = $gLibertySystem->getPluginFunction( $storeRow['plugin_guid'], 'store_function' )) {
								$this->mStorage = $storeFunc( $storeRow );
							}

							// don't insert if we already have an entry with this attachment_id
							if( @BitBase::verifyId( $storeRow['attachment_id'] ) && !isset( $storeRow['skip_insert'] ) && !$this->getAttachment( $storeRow['attachment_id'] )) {
								$sql = "INSERT INTO `".BIT_DB_PREFIX."liberty_attachments` ( `content_id`, `attachment_id`, `attachment_plugin_guid`, `foreign_id`, `user_id` ) VALUES ( ?, ?, ?, ?, ? )";
								$rs = $this->mDb->query( $sql, array( $storeRow['content_id'], $storeRow['attachment_id'], $storeRow['plugin_guid'], (int)$storeRow['foreign_id'], $storeRow['user_id'] ) );
							}
						}
					}
				}
			}

			// set the primary attachment id
			$this->setPrimaryAttachment(
				$pParamHash['liberty_attachments']['primary'],
				$pParamHash['content_id'],
				empty( $pParamHash['liberty_attachments']['auto_primary'] ) || $pParamHash['liberty_attachments']['auto_primary'] ? TRUE : FALSE
				);
		}
		$this->mDb->CompleteTrans();

		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * Get a list of all available attachments
	 *
	 * @param array $pListHash
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getAttachmentList( &$pListHash ) {
		global $gLibertySystem, $gBitUser, $gBitSystem;

		$this->prepGetList( $pListHash );

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
			if( $loadFunc = $gLibertySystem->getPluginFunction( $attachment['attachment_plugin_guid'], 'load_function' )) {
				$ret[$attachment['attachment_id']] = $loadFunc( $attachment );
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
	 * Expunges the content deleting attachments if asked to do so, otherwise just detaching them
	 * TODO: this hasn't been updated yet since the liberty_attachments update
	 */
	function expunge() {
		if( !empty( $this->mStorage ) && count( $this->mStorage )) {
			foreach( array_keys( $this->mStorage ) as $i ) {
				$this->expungeAttachment(  $this->mStorage[$i]['attachment_id'] );
			}
		}
		return LibertyContent::expunge();
	}

	/**
	 * expunge attachment from the database (and file system via the plugin if required)
	 *
	 * @param numeric $pAttachmentId attachment id of the item that should be deleted
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function expungeAttachment( $pAttachmentId ) {
		global $gLibertySystem, $gBitUser;
		$ret = NULL;
		if( @$this->verifyId( $pAttachmentId ) ) {
			$sql = "SELECT `attachment_plugin_guid`, `user_id` FROM `".BIT_DB_PREFIX."liberty_attachments` WHERE `attachment_id`=?";
			$row = $this->mDb->getRow( $sql, array( $pAttachmentId ) );
			$guid = $row['attachment_plugin_guid'];
			if( $guid && ( $this->isOwner( $row ) || $gBitUser->isAdmin() )) {
				// check if we have the means available to remove this attachment
				if( $expungeFunc = $gLibertySystem->getPluginFunction( $guid,'expunge_function' )) {
					// --- Do the final cleanup of liberty related tables ---
					if( $expungeFunc( $pAttachmentId )) {
						// Delete the attachment record.
						$sql = "DELETE FROM `".BIT_DB_PREFIX."liberty_attachments` WHERE `attachment_id`=?";
						$this->mDb->query( $sql, array( $pAttachmentId ) );

						// Remove attachment from memory
						unset( $this->mStorage[$pAttachmentId] );
					}
				} else {
					print( "Expunge function not found for this content!" );
					$ret = NULL;
				}
			}
		}

		return $ret;
	}

	/**
	 * fully load content and insert any attachments in $this->mStorage
	 * allow an optional content_id to be passed in to ease legacy lib style objects (like blogs, articles, etc.)
	 *
	 * @param array $pContentId
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function load( $pContentId=NULL ) {
		// assume a derived class has joined on the liberty_content table, and loaded it's columns already.
		global $gLibertySystem;
		$conId = ( @$this->verifyId( $pContentId ) ? $pContentId : $this->mContentId );

		if( @$this->verifyId( $conId ) ) {
			LibertyContent::load( $conId );
			$query = "
				SELECT *
				FROM `".BIT_DB_PREFIX."liberty_attachments` la
				WHERE la.`content_id`=? ORDER BY la.`pos` ASC, la.`attachment_id` ASC";
			if( $result = $this->mDb->query( $query,array( (int)$conId ))) {
				$this->mStorage = array();
				while( $row = $result->fetchRow() ) {
					if( $func = $gLibertySystem->getPluginFunction( $row['attachment_plugin_guid'], 'load_function' )) {
						$this->mStorage[$row['attachment_id']] = $func( $row );
						//$this->mStorage[$row['attachment_id']]['is_primary'] = !empty( $row['primary_attachment_id'] );
					} else {
						print "No load_function for ".$row['attachment_plugin_guid']." ".$gLibertySystem->mPlugins[$row['attachment_plugin_guid']];
					}
				}
			}
		}
		return( TRUE );
	}

	/**
	 * load details of a given attachment
	 * allow an optional content_id to be passed in to ease legacy lib style objects (like blogs, articles, etc.)
	 *
	 * @param array $pAttachmentId
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getAttachment( $pAttachmentId ) {
		global $gLibertySystem, $gBitSystem;
		$ret = NULL;

		if( @BitBase::verifyId( $pAttachmentId ) ) {
			$query = "SELECT * FROM `".BIT_DB_PREFIX."liberty_attachments` la WHERE la.`attachment_id`=?";
			if( $result = $gBitSystem->mDb->query( $query, array( (int)$pAttachmentId ))) {
				if( $row = $result->fetchRow() ) {
					if( $func = $gLibertySystem->getPluginFunction( $row['attachment_plugin_guid'], 'load_function'  ) ) {
						$ret = $func( $row );
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
	function setPrimaryAttachment( $pAttachmentId = NULL, $pContentId = NULL, $pAutoPrimary = TRUE ) {
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

	/*
	 * clearPrimaryAttachment will remove the primary flag for all attachments
	 * with the given content_id
	 *
	 * @param numeric the content_id for which primary should be unset.
	 * @return TRUE on succes
	 */
	function clearPrimaryAttachment( $pContentId ) {
		$ret = FALSE;

		if (@BitBase::verifyId($pContentId)) {
			$query = "
				UPDATE `".BIT_DB_PREFIX."liberty_attachments`
				SET `is_primary` = ? WHERE `content_id` = ?";
			$this->mDb->query( $query, array( NULL, $pContentId ));
			$ret = TRUE;
		}

		return $ret;
	}

	/**
	 * scanForAttchmentUse generates a list of all content associated with a given attachment
	 * 
	 * @param numeric $pAttachmentId attachment id of the item to check
	 * @access public
	 * @return FALSE on failure, or an array on success
	 */
	function scanForAttchmentUse( $pAttachmentId = NULL ) {
		$ret = FALSE;
		if(  !@BitBase::verifyId( $pAttachmentId )) {
			return $ret;
		}

		$bindVars[] = $pAttachmentId;
		
		$query = "
			SELECT *
			FROM `".BIT_DB_PREFIX."liberty_content` lc
			INNER JOIN `".BIT_DB_PREFIX."liberty_attachments` la ON( lc.`content_id` = la.`content_id` )
			WHERE la.`attachment_id` = ?";

		$result = $this->mDb->query( $query, $bindVars);
		while( $res = $result->fetchRow() ) {
			$content_id = $res['content_id'];
			$liberty_content = new LibertyContent();
			$res['display_link'] = $liberty_content->getDisplayLink(NULL,$res);
			$attached_to[] = $res;
		}

		return $attached_to;


	}

}
?>
