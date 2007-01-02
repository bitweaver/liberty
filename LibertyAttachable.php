<?php
/**
 * Management of Liberty Content
 *
 * @package  liberty
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/LibertyAttachable.php,v 1.54 2007/01/02 22:26:41 spiderr Exp $
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

// load the image processor plugin
if( $gBitSystem->isFeatureActive( 'image_processor' ) ) {
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
		$baseUrl = null;
		$pathParts = array();
		$pathParts = split( '/', $gBitSystem->getConfig( 'site_upload_dir', 'storage/' ) );

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

		$pRootDir = !empty( $pRootDir ) ? $pRootDir : BIT_ROOT_PATH;

		foreach( $pathParts as $p ) {
			if( !empty( $p ) ) {
				$baseUrl .= $p.'/';
				if( !file_exists( $pRootDir.$baseUrl ) ) {
					$oldu = umask( 0 );
					if( mkdir( $pRootDir.$baseUrl, $pPermissions ) ) {
						umask( $oldu );
					} else {
						// ACK, something went very wrong.
						$baseUrl = FALSE;
						break;
					}
				}
			}
		}
		return $baseUrl;
	}

	/**
	* verifyStorageFile - verify if a file exists
	*
	* @access public
	* @author Christian Fowler<spider@steelsun.com>
	* @param $pFileName name of the file that needs to be checked for
	* @param $pSubDir any desired directory below the StoragePath.
	* @param $pCommon indicates usage of the 'common' branch, and not the 'users/.../<user_id>' branch
	* @param $pPackage indicates what package the data is from. defaults to the currently active package
	* @return on success return full path to file, if it fails to find the file, returns false
	*/
	//	------------------------- i think this function is not used - xing
//	function verifyStorageFile( $pFileName, $pSubDir = NULL, $pUserId = NULL, $pPackage = ACTIVE_PACKAGE ) {
//		// don't worry about double slashes '//' for now. we'll remove them later
//		$path = $this->getConfig( 'site_upload_dir', 'storage' ).'/';
//		if( !$pUserId ) {
//			$path .= 'common/';
//		} else {
//			$path .= 'users/'.(int)($pUserId % 1000).'/'.$pUserId.'/';
//		}
//		$path .= $pPackage.'/'.$pSubDir.'/'.$pFileName;
//		$path = BIT_ROOT_PATH.ereg_replace( '//','/',$path );
//		if( file_exists( $path ) ) {
//			return $path;
//		} else {
//			return FALSE;
//		}
//	}

	function verify( &$pParamHash ) {
		global $gBitSystem, $gBitUser;
		if( !empty( $pParamHash['attachment_id'] ) && !$this->verifyId( $pParamHash['attachment_id'] ) ) {
			$this->mErrors['file'] = 'System Error: Non-numeric storage_id.';
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

		if( !empty( $_FILES['upload'] ) ) {
			// tiki files upload
			if( !empty( $_FILES['upload']['size'] ) ) {
				$pParamHash['upload'] = $_FILES['upload'];
			} elseif( !empty( $_FILES['upload']['name'] ) ) {
				$this->mErrors['upload'] = tra( 'Empty file' ).': '.$_FILES['upload']['name'];
			}
		}

		if( !empty( $pParamHash['upload']['size'] ) && !empty( $pParamHash['upload'] ) && is_array( $pParamHash['upload'] ) ) {

			$save = TRUE;
/*
Disable for now - instead fend off new uploads once quota is exceeded. Need a nice upload mechanism that can cancel uploads once the upload has begun, ala megaupload
			if( $gBitSystem->isPackageActive( 'quota' ) && !$gBitUser->isAdmin() ) {
				require_once( QUOTA_PKG_PATH.'LibertyQuota.php' );
				$quota = new LibertyQuota();
				// Prevent people from uploading more than there quota
				$q = $quota->getUserQuota( $pParamHash['user_id'] );
				$u = (int)$quota->getUserUsage( $pParamHash['user_id'] );
				if( $u + $pParamHash['upload']['size'] > $q ) {
					$save = FALSE;
					$this->mErrors['upload'] = $pParamHash['upload']['name'].' '.tra( 'could not be stored because you do not have enough disk quota.' ).' '.round(($u + $pParamHash['upload']['size'] - $q)/1000).'KB Needed' ;
				}
			}
*/
			if( $save ) {
				// - TODO: get common preferences page with this as an option, but right now files are only option cuz no blobs - SPIDERR
				$storageGuid = !empty( $pParamHash['storage_guid'] ) ? $pParamHash['storage_guid'] : $gBitSystem->getConfig( 'common_storage_plugin', PLUGIN_GUID_BIT_FILES );
				if( !empty( $pParamHash['upload']['size'] ) ) {
					$pParamHash['upload']['dest_base_name'] = substr( $pParamHash['upload']['name'], 0, strrpos( $pParamHash['upload']['name'], '.' )  );
					$pParamHash['upload']['source_file'] = $pParamHash['upload']['tmp_name'];
					// lowercase all file extensions
					$pParamHash['upload']['name'] = $pParamHash['upload']['dest_base_name'].strtolower( substr( $pParamHash['upload']['name'], strrpos( $pParamHash['upload']['name'], '.' ) )  );
					$pParamHash['STORAGE'][$storageGuid] = $pParamHash['upload'];
				}
			}
		}

		if( isset( $pParamHash['STORAGE'] ) && is_array( $pParamHash['STORAGE'] ) ) {
			foreach( array_keys( $pParamHash['STORAGE'] ) as $guid ) {
				if( empty( $pParamHash['STORAGE'][$guid] ) ) {
					unset( $pParamHash['STORAGE'][$guid]  );
				} else {
					// reassign uploaded guid value to array element hashed under the guid. we are going to add more stuff to the guid hash
					$inputValue = $pParamHash['STORAGE'][$guid];
					$pParamHash['STORAGE'][$guid] = array();
					$pParamHash['STORAGE'][$guid]['upload'] = $inputValue;
				}
			}
		}
		return( count( $this->mErrors ) == 0 );
	}

	// Things to be stored should be shoved in the array $pParamHash['STORAGE']
	function store ( &$pParamHash ) {
		global $gLibertySystem, $gBitSystem;
		$this->mDb->StartTrans();
		if( LibertyAttachable::verify( $pParamHash ) && LibertyContent::store( $pParamHash ) && !empty( $pParamHash['STORAGE'] ) && count( $pParamHash['STORAGE'] ) ) {
			foreach( array_keys( $pParamHash['STORAGE'] ) as $guid ) {
				$storeRow = &$pParamHash['STORAGE'][$guid]; // short hand variable assignment
				$storeRow['plugin_guid'] = $guid;
				$storeRow['content_id'] = $pParamHash['content_id']; // copy in content_id
				$storeRow['user_id'] = $pParamHash['user_id']; // copy in the user_id
				// do we have a verify function for this storage type, and do things verify?
				if( function_exists( $gLibertySystem->mPlugins[$guid]['verify_function'] )
					&& $gLibertySystem->mPlugins[$guid]['verify_function']( $storeRow ) ) {
					if( empty( $pParamHash['attachment_id'] ) ) {
						$sql = "SELECT `attachment_id` FROM `".BIT_DB_PREFIX."liberty_attachments`
								WHERE `attachment_plugin_guid` = ? AND `content_id` = ? AND `foreign_id`=?";
						$rs = $this->mDb->query( $sql, array( $storeRow['plugin_guid'], (int)$storeRow['content_id'], (int)$storeRow['foreign_id'] ) );
						if( empty( $rs ) || !$rs->NumRows() ) {
							$pParamHash['attachment_id'] = $this->mDb->GenID( 'liberty_attachments_id_seq' );
							$sql = "INSERT INTO `".BIT_DB_PREFIX."liberty_attachments` ( `attachment_id`, `attachment_plugin_guid`, `content_id`, `foreign_id`, `user_id` ) VALUES ( ?, ?, ?, ?, ? )";
							$rs = $this->mDb->query( $sql, array( $pParamHash['attachment_id'], $storeRow['plugin_guid'], $pParamHash['content_id'], (int)$storeRow['foreign_id'], $storeRow['user_id'] ) );
						} else {
							$this->mErrors['storage'] = $guid.' '.$storeRow['foreign_id'].' has already been added to this content.';
							$pParamHash['attachment_id'] = NULL;
						}
					}
					$storeRow['attachment_id'] = $pParamHash['attachment_id'];

					// if we have uploaded a file, we can take care of that generically
					if( is_array( $storeRow['upload'] ) && !empty( $storeRow['upload']['size'] ) ) {
						if( empty( $storeRow['upload']['type'] ) ) {
							$ext = substr( $storeRow['upload']['name'], strrpos( $storeRow['upload']['name'], '.' ) + 1 );
							$storeRow['upload']['type'] = $gBitSystem->lookupMimeType( $ext );
						}
						$storeRow['upload']['dest_path'] = $this->getStorageBranch( $storeRow['attachment_id'], $pParamHash['user_id'], $this->getStorageSubDirName() );
						if (!empty( $pParamHash['thumbnail_sizes'] ) ) {
							$storeRow['upload']['thumbnail_sizes'] = $pParamHash['thumbnail_sizes'];
						}
						$storagePath = liberty_process_upload( $storeRow );
						// We're gonna store to local file system & liberty_files table
						if( empty( $storagePath ) ) {
							$this->mErrors['file'] = tra( "Could not store file" ).": ".$storeRow['upload']['name'].'.';
							$pParamHash['attachment_id'] = NULL;
						} else {
							$storeRow['upload']['dest_file_path'] = $storagePath;
						}
					}

					if( $pParamHash['attachment_id'] && function_exists( $gLibertySystem->mPlugins[$storeRow['plugin_guid']]['store_function'] ) ) {
						$storeFunc = $gLibertySystem->mPlugins[$storeRow['plugin_guid']]['store_function'];
						$this->mStorage = $storeFunc( $storeRow );
					}
				} else {
				}
			}
		}
		$this->mDb->CompleteTrans();

		if( @$this->verifyId( $pParamHash['existing_attachment_id'] ) ) {
			foreach( $pParamHash['existing_attachment_id'] as $existingAttachmentId ) {
				// allow for multiple values seperated by any non numeric character
				$ids = preg_split( '/\D/', $existingAttachmentId );
				foreach( $ids as $id ) {
					$id = ( int )$id;
					if( @$this->verifyId( $id ) ) {
						$this->cloneAttachment( $id, $pParamHash['content_id'] );
					}
				}
			}
		}
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
		global $gLibertySystem, $gBitUser;

		$this->prepGetList( $pListHash );

		// initialise some variables
		$attachments = $ret = $bindVars = array();
		$whereSql = '';

		// only admin may view attachments from other users
		if( !$gBitUser->isAdmin() ) {
			$pListHash['user_id'] = $gBitUser->mUserId;
		}

		if( !empty( $pListHash['user_id'] ) ) {
			$whereSql .= empty( $whereSql ) ? ' WHERE ' : ' AND ';
			$whereSql .= " la.user_id = ? ";
			$bindVars[] = $pListHash['user_id'];
		}

		$query = "
			SELECT DISTINCT( la.`foreign_id` ) AS `hash_key`, la.*
			FROM `".BIT_DB_PREFIX."liberty_attachments` la
				INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON( la.`user_id` = uu.`user_id` )
			$whereSql
		";

		$result = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] );
		while( $res = $result->fetchRow() ) {
			$attachments[] = $res;
		}

		foreach( $attachments as $attachment ) {
			$loadFunc = $gLibertySystem->getPluginFunction( $attachment['attachment_plugin_guid'], 'load_function' );
			$ret[] = $loadFunc( $attachment );
		}

		// count all entries
		$query = "
			SELECT COUNT( DISTINCT( la.`foreign_id` ) )
			FROM `".BIT_DB_PREFIX."liberty_attachments` la
			$whereSql
		";

		$pListHash['cant'] = $this->mDb->getOne( $query, $bindVars );
		$this->postGetList( $pListHash );

		return $ret;
	}

	// Clone an existing attachment but have it reference another content_id
	function cloneAttachment($pAttachmentId, $pNewContentId) {
		global $gLibertySystem;
		global $gBitUser;

		$sql = "SELECT * FROM `".BIT_DB_PREFIX."liberty_attachments` WHERE `attachment_id` = ?";
		$rs = $this->mDb->query($sql, array( $pAttachmentId ));
		$tmpAttachment = $rs->fetchRow();

		if ( @$this->verifyId($tmpAttachment['attachment_id']) ) {
			$newAttachmentId = $this->mDb->GenID( 'liberty_attachments_id_seq' );
			$sql = "INSERT INTO `".BIT_DB_PREFIX."liberty_attachments` ( `attachment_id`, `attachment_plugin_guid`, `content_id`, `foreign_id`, `user_id` ) VALUES ( ?, ?, ?, ?, ? )";
			$rs = $this->mDb->query( $sql, array( $newAttachmentId, $tmpAttachment['attachment_plugin_guid'], $pNewContentId, $tmpAttachment['foreign_id'], $gBitUser->mUserId ) );
		}
	}


	function expunge () {
		if( !empty( $this->mStorage ) && count( $this->mStorage ) ) {
			foreach( array_keys( $this->mStorage ) as $i ) {
				$this->expungeAttachment(  $this->mStorage[$i]['attachment_id'] );
			}
		}
		return LibertyContent::expunge();
	}

	function expungeAttachment( $pAttachmentId ) {
		global $gLibertySystem;
		global $gBitUser;
		$ret = NULL;

		if( @$this->verifyId( $pAttachmentId ) ) {
			$sql = "SELECT `attachment_plugin_guid`, `user_id` FROM `".BIT_DB_PREFIX."liberty_attachments` WHERE `attachment_id`=?";
			$row = $this->mDb->getRow( $sql, array( $pAttachmentId ) );
			$guid = $row['attachment_plugin_guid'];
			$user_id = $row['user_id'];

			if( $guid && ($user_id == $gBitUser->mUserId || $gBitUser->isAdmin()) ) {
				if ( function_exists( $gLibertySystem->mPlugins[$guid]['expunge_function'])) {
					$expungeFunc = $gLibertySystem->mPlugins[$guid]['expunge_function'];
					if( $expungeFunc( $pAttachmentId ) ) {
						$delDir = dirname( $this->mStorage[$pAttachmentId]['storage_path'] );
						// add a safety precation to verify that images/123 is in the delete directory in case / got
						// shoved into $this->mStorage[$pAttachmentId]['storage_path'] for some reason, which would nuke the entire storage directory
						if( preg_match ( '/image\//', $this->mStorage[$pAttachmentId]['mime_type'] ) && preg_match( "/images\/$pAttachmentId/", $delDir ) ) {
							unlink_r( BIT_ROOT_PATH.dirname( $this->mStorage[$pAttachmentId]['storage_path'] ) );
						}
						$sql = "DELETE FROM `".BIT_DB_PREFIX."liberty_attachments` WHERE `attachment_id`=?";
						$this->mDb->query( $sql, array( $pAttachmentId ) );

						unset($this->mStorage[$pAttachmentId]);
					}
				} else {
					print("Expunge function not found for this content!");
					$ret = NULL;
				}
			}
		}

		return $ret;
	}

	function detachAttachment( $pAttachmentId ) {
		if( @$this->verifyId( $pAttachmentId ) ) {
			$attachmentInfo = $this->getAttachment($pAttachmentId);
			if (@$this->verifyId($attachmentInfo['user_id'] ) ) {
				$attachmentOwner = new BitUser($attachmentInfo['user_id']);
				$attachmentOwner->load();
				if ($attachmentOwner->mContentId) {
					$query = "UPDATE `".BIT_DB_PREFIX."liberty_attachments` SET `content_id` = ? WHERE `attachment_id` = ?";
					$result = $this->mDb->query($query, array($attachmentOwner->mContentId, $pAttachmentId));
				} else {
					$this->mErrors[] = "Unable to detach this attachment because the owner does not have a content row";
				}
			} else {
				$this->mErrors[] = "An attachment with this id does not exist: $pAttachmentId";
			}
		}
		return TRUE;
	}

	// allow an optional content_id to be passed in to ease legacy lib style objects (like blogs, articles, etc.)
	function load( $pContentId=NULL ) {
		// assume a derived class has joined on the liberty_content table, and loaded it's columns already.
		global $gLibertySystem;
		$conId = ( @$this->verifyId( $pContentId ) ? $pContentId : $this->mContentId );

		if( @$this->verifyId( $conId ) ) {
			LibertyContent::load($pContentId);
			$query = "SELECT * FROM `".BIT_DB_PREFIX."liberty_attachments` a
					  WHERE a.`content_id`=?";
			if( $result = $this->mDb->query($query,array((int) $conId)) ) {
				$this->mStorage = array();
				while( $row = $result->fetchRow() ) {
					if( $func = $gLibertySystem->getPluginFunction( $row['attachment_plugin_guid'], 'load_function'  ) ) {
						$this->mStorage[$row['attachment_id']] = $func( $row );
					} else {
						print "NO load_function for ".$row['attachment_plugin_guid']." ".$gLibertySystem->mPlugins[$row['attachment_plugin_guid']];
					}
				}
			}
		}
		return( TRUE );
	}

	// allow an optional content_id to be passed in to ease legacy lib style objects (like blogs, articles, etc.)
	function getAttachment( $pAttachmentId ) {
		// assume a derived class has joined on the liberty_content table, and loaded it's columns already.
		global $gLibertySystem;
		$ret = NULL;

		if( @$this->verifyId( $pAttachmentId ) ) {
			$query = "SELECT * FROM `".BIT_DB_PREFIX."liberty_attachments` a
					  WHERE a.`attachment_id`=?";
			if( $result = $this->mDb->query($query,array((int) $pAttachmentId)) ) {
				$ret = array();
				if( $row = $result->fetchRow() ) {
					if( $func = $gLibertySystem->getPluginFunction( $row['attachment_plugin_guid'], 'load_function'  ) ) {
						$ret = $func( $row );
					}
				}
			}
		}
		return $ret;
	}

	// Get a list of attachments which also reference the foreign_id of the given attachment
	function getSiblingAttachments( $pAttachmentId ) {
		$ret = NULL;

		$attachmentInfo = $this->getAttachment( $pAttachmentId );

		if( @$this->verifyId( $attachmentInfo['attachment_id'] ) && @$this->verifyId( $attachmentInfo['foreign_id'] ) && @$this->verifyId( $attachmentInfo['attachment_plugin_guid'] ) ) {
			$query = "SELECT  * FROM `".BIT_DB_PREFIX."liberty_attachments` WHERE `foreign_id` = ? AND `attachment_plugin_guid` = ? AND `attachment_id` <> ?";
			$result = $this->mDb->query( $query, array ($attachmentInfo['foreign_id'], $attachmentInfo['attachment_plugin_guid'], $attachment['attachment_id'] ) );
			$ret = $result->getRows();
		}

		return $ret;
	}
}



/* -=-=-=-=-=-=-=-=-=-=-=- Liberty File Processing Functions -=-=-=-=-=-=-=-=-=-=-=- */


/**
 * Process uploaded files. Will automagically generate thumbnails for images
 * 
 * @param array $pFileHash Data require to process the files
 * @param array $pFileHash['upload']['name'] (required) Name of the uploaded file
 * @param array $pFileHash['upload']['type'] (required) Mime type of the file uploaded
 * @param array $pFileHash['upload']['dest_path'] (required) Relative path where you want to store the file
 * @param array $pFileHash['upload']['source_file'] (required) Absolute path to file including file name
 * @param boolean $pFileHash['upload']['thumbnail'] (optional) Set to FALSE if you don't want to generate thumbnails
 * @param array $pFileHash['upload']['thumbnail_sizes'] (optional) Decide what sizes thumbnails you want to create: icon, avatar, small, medium, large
 * @param boolean $pMoveFile (optional) specify if you want to move or copy the original file
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_process_upload( &$pFileHash ) {
	// Check for evil file extensions that could be execed on the server
	if( preg_match( '/(.pl|.php|.php3|.php4|.phtml|.cgi|.asp|.sh|.shtml)$/', $pFileHash['upload']['name'] ) ) {
		$pFileHash['upload']['type'] = 'text/plain';
		$pFileHash['upload']['name'] = $pFileHash['upload']['name'].'.txt';
	}
	// Thumbs.db is a windows My Photos/ folder file, and seems to really piss off imagick
	if( (preg_match( '/^image\/*/', $pFileHash['upload']['type'] ) || preg_match( '/pdf/i', $pFileHash['upload']['type'] ) ) && $pFileHash['upload']['name'] != 'Thumbs.db' ) {
		$ret = liberty_process_image( $pFileHash['upload'] );
	} else {
		$ret = liberty_process_generic( $pFileHash['upload'] );
	}
	return $ret;
}

/**
 * liberty_process_archive 
 * 
 * @param array $pFileHash 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_process_archive( &$pFileHash ) {
	// sanity check: make sure tmp_name isn't empty. will scan / if it is
	if( empty( $pFileHash['tmp_name'] ) || empty( $pFileHash['name'] ) ) {
		return FALSE;
	}

	$cwd = getcwd();
	// if the file has been uploaded using a form, we'll process the uploaded
	// file directly. if it's been ftp uploaded or some other method used,
	// we'll copy the file. in the case of xuploaded files, the files have been
	// processed but don't have to be copied
	if( empty( $pFileHash['preprocessed'] ) && !is_uploaded_file( $pFileHash['tmp_name'] ) && is_file( $pFileHash['tmp_name'] ) ) {
		$tmpDir = ini_get('upload_tmp_dir');
		$copyFile = tempnam( !empty( $tmpDir ) ? $tmpDir : '/tmp', $pFileHash['name'] );
		copy( $pFileHash['tmp_name'], $copyFile );
		$pFileHash['tmp_name'] = $copyFile;
	}

	$dir = dirname( $pFileHash['tmp_name'] );
	$upExt = strtolower( substr( $pFileHash['name'], ( strrpos( $pFileHash['name'], '.' ) + 1 ) ) );
	$baseDir = $dir.'/';
	if( is_file( $pFileHash['tmp_name'] ) ) {
		global $gBitUser;
		$baseDir .= $gBitUser->mUserId;
	}

	$destDir = $baseDir.'/'.basename( $pFileHash['tmp_name'] );
	// this if is very important logic back so subdirs get processed properly
	if( ( is_dir( $baseDir ) || mkdir( $baseDir ) ) && @mkdir( $destDir ) ) {
		// Some commands don't nicely support extracting to other directories
		chdir( $destDir );
		list( $mimeType, $mimeExt ) = split( '/', strtolower( $pFileHash['type'] ) );
		switch( $mimeExt ) {
			case 'x-rar-compressed':
			case 'x-rar':
				$shellResult = shell_exec( "unrar x \"{$pFileHash['tmp_name']}\" \"$destDir\"" );
				break;
			case 'x-bzip2':
			case 'bzip2':
			case 'x-gzip':
			case 'gzip':
			case 'x-tgz':
			case 'x-tar':
			case 'tar':
				switch( $upExt ) {
					case 'gz':
					case 'tgz': $compressFlag = '-z'; break;
					case 'bz2': $compressFlag = '-j'; break;
					default: $compressFlag = ''; break;
				}
				$shellResult = shell_exec( "tar -x $compressFlag -f \"{$pFileHash['tmp_name']}\"  -C \"$destDir\"" );
				break;
			case 'x-zip-compressed':
			case 'x-zip':
			case 'zip':
				$shellResult = shell_exec( "unzip \"{$pFileHash['tmp_name']}\" -d \"$destDir\"" );
				break;
			case 'x-stuffit':
			case 'stuffit':
				$shellResult = shell_exec( "unstuff -d=\"$destDir\" \"{$pFileHash['tmp_name']}\" " );
				break;
			default:
				if( $upExt == 'zip' ) {
					$shellResult = shell_exec( "unzip \"{$pFileHash['tmp_name']}\" -d \"$destDir\"" );
				} elseif( $upExt == 'rar' ) {
					$shellResult = shell_exec( "unrar x \"{$pFileHash['tmp_name']}\" \"$destDir\"" );
				} elseif( $upExt == 'sit' || $upExt == 'sitx' ) {
					print( "unstuff -d=\"$destDir\" \"{$pFileHash['tmp_name']}\" " );
					$shellResult = shell_exec( "unstuff -d=\"$destDir\" \"{$pFileHash['tmp_name']}\" " );
				} else {
					$destDir = NULL;
				}
				break;
		}
	}
	//vd($shellResult);
	chdir( $cwd );

	// if we created a copy of the original, we remove it
	if( !empty( $copyFile ) ) {
		@unlink( $copyFile );
	}

	return $destDir;
}

/**
 * liberty_process_generic 
 * 
 * @param array $pFileHash 
 * @param array $pMoveFile 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_process_generic( &$pFileHash, $pMoveFile=TRUE ) {
	$ret = NULL;
	$destBase = $pFileHash['dest_path'].$pFileHash['name'];
	$actualPath = BIT_ROOT_PATH.$destBase;
	if( is_file( $pFileHash['source_file']) ) {
		if( $pMoveFile && rename( $pFileHash['source_file'], $actualPath ) ) {
			$ret = $destBase;
		} else {
			copy( $pFileHash['source_file'], $actualPath );
		}
		$ret = $destBase;
	}
	$pFileHash['size'] = filesize( $actualPath );

	return $ret;
}


/**
 * liberty_process_image 
 * 
 * @param array $pFileHash 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_process_image( &$pFileHash ) {
	global $gBitSystem;
	$ret = NULL;

	list($type, $ext) = split( '/', strtolower( $pFileHash['type'] ) );
	mkdir_p( BIT_ROOT_PATH.$pFileHash['dest_path'] );
	if( $resizePath = liberty_process_generic( $pFileHash ) ) {
		$pFileHash['source_file'] = BIT_ROOT_PATH.$resizePath;
		//set permissions if possible - necessary for some wonky shared hosting environments
		if(chmod($pFileHash['source_file'], 0644)){
			//does nothing, but fails elegantly
		}
		$nameHold = $pFileHash['name'];
		$sizeHold = $pFileHash['size'];
		$ret = $pFileHash['source_file'];
		// do not thumbnail only if intentionally set to FALSE
		if( !isset( $pFileHash['thumbnail'] ) || $pFileHash['thumbnail']==TRUE ) {
			liberty_generate_thumbnails( $pFileHash );
		}
		$pFileHash['name'] = $nameHold;
		$pFileHash['size'] = $sizeHold;
	}
	return $ret;
}


/**
 * liberty_clear_thumbnails 
 * 
 * @param array $pFileHash 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_clear_thumbnails( &$pFileHash ) {
	$thumbnail_sizes = array( 'icon', 'avatar', 'small', 'medium', 'large' );
	foreach( $thumbnail_sizes as $size ) {
		$fullPath =  BIT_ROOT_PATH.$pFileHash['dest_path']."$size.jpg";
		if( file_exists( $fullPath ) ) {
			unlink( $fullPath );
		}
	}
}

/**
 * liberty_get_function 
 * 
 * @param array $pType 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_get_function( $pType ) {
	global $gBitSystem;
	return 'liberty_'.$gBitSystem->getConfig( 'image_processor', 'gd' ).'_'.$pType.'_image';
}

/**
 * liberty_generate_thumbnails 
 * 
 * @param array $pFileHash 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function liberty_generate_thumbnails( &$pFileHash ) {
	global $gBitSystem, $gThumbSizes;
	$resizeFunc = liberty_get_function( 'resize' );

	// allow custom selecteion of thumbnail sizes
	if( empty( $pFileHash['thumbnail_sizes'] ) ) {
		$pFileHash['thumbnail_sizes'] = array( 'icon', 'avatar', 'small', 'medium', 'large' );
	}

	if( empty( $gThumbSizes ) ) {
		$gThumbSizes = array( 
			'icon' => array( 'width'=>48, 'height'=>48 ), 
			'avatar' => array( 'width'=>100, 'height'=>100 ), 
			'small' => array( 'width'=>160, 'height'=>120 ), 
			'medium' => array( 'width'=>400, 'height'=>300 ), 
			'large' => array( 'width'=>800, 'height'=>600 ), 
		);
	}

	if( (!preg_match( '/image\/(gif|jpg|jpeg|png)/', strtolower( $pFileHash['type'] ) ) && $gBitSystem->isFeatureActive( 'liberty_jpeg_originals' ))
		|| in_array( 'original', $pFileHash['thumbnail_sizes'] ) ) {
		// jpeg version of original
		$pFileHash['dest_base_name'] = 'original';
		$pFileHash['name'] = 'original.jpg';
		$pFileHash['max_width'] = MAX_THUMBNAIL_DIMENSION;
		$pFileHash['max_height'] = MAX_THUMBNAIL_DIMENSION;
		$pFileHash['original_path'] = BIT_ROOT_PATH.$resizeFunc( $pFileHash );
	}

	foreach( $pFileHash['thumbnail_sizes'] as $thumbSize ) {
		if( isset( $gThumbSizes[$thumbSize] ) ) {
			// Icon thumb is 48x48
			$pFileHash['dest_base_name'] = $thumbSize;
			$pFileHash['name'] = $thumbSize.'.jpg';
			$pFileHash['max_width'] = $gThumbSizes[$thumbSize]['width'];
			$pFileHash['max_height'] = $gThumbSizes[$thumbSize]['height'];
			$pFileHash['icon_thumb_path'] = BIT_ROOT_PATH.$resizeFunc( $pFileHash );
		}
	}
}
?>
