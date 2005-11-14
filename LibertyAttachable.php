<?php
/**
 * Management of Liberty Content
 *
 * @package  liberty
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/LibertyAttachable.php,v 1.1.1.1.2.26 2005/11/14 02:29:19 spiderr Exp $
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
	* getStoragePath - get path to store files for the feature centralized_upload_dir. It creates a calculable hierarchy of directories
	*
	* @access public
	* @author Christian Fowler<spider@steelsun.com>
	* @param $pSubDir any desired directory below the StoragePath. this will be created if it doesn't exist
	* @param $pCommon indicates not to use the 'common' branch, and not the 'users/.../<user_id>' branch
	* @return string full path on local filsystem to store files.
	*/
	function getStoragePath( $pSubDir = NULL, $pUserId = NULL, $pPackage = ACTIVE_PACKAGE ) {
		$ret = null;
		if( $storageUrl = $this->getStorageBranch( $pSubDir, $pUserId, $pPackage ) ) {
			$ret = BIT_ROOT_PATH.$storageUrl;
			//$ret = $storageUrl;
		}
		return $ret;
	}


	function getStorageUrl( $pSubDir = NULL, $pUserId = NULL, $pPackage = ACTIVE_PACKAGE ) {
		return BIT_ROOT_URL.$this->getStorageBranch( $pSubDir, $pUserId, $pPackage );
	}


	/**
	* getStorageBranch - get url to store files for the feature centralized_upload_dir. It creates a calculable hierarchy of directories
	*
	* @access public
	* @author Christian Fowler<spider@steelsun.com>
	* @param $pSubDir any desired directory below the StoragePath. this will be created if it doesn't exist
	* @param $pUserId indicates the 'users/.../<user_id>' branch or use the 'common' branch if null
	* @return string full path on local filsystem to store files.
	*/
	function getStorageBranch( $pSubDir = NULL, $pUserId = NULL, $pPackage = ACTIVE_PACKAGE ) {
		// *PRIVATE FUNCTION. GO AWAY! DO NOT CALL DIRECTLY!!!
		global $gBitSystem;
		$baseUrl = null;
		$pathParts = array();
		$pref = split( '/',$gBitSystem->getPreference('centralized_upload_dir') );
		if( empty( $pref ) ) {
			$pathParts[] = 'storage';
		} else {
			$pathParts = $pref;
		}
		if( !$pUserId ) {
			$pathParts[] = 'common';
		} else {
			$pathParts[] = 'users';
			$pathParts[] = (int)($pUserId % 1000);
			$pathParts[] = $pUserId;
		}
		if ($pPackage) {
			$pathParts[] = $pPackage;
		}
		// In case $pSubDir is multiple levels deep we'll need to mkdir each directory if they don't exist
		$pSubDirParts = split('/',$pSubDir);
		foreach ($pSubDirParts as $subDir) {
			$pathParts[] = $subDir;
		}
		foreach( $pathParts as $p ) {
			if( !empty( $p ) ) {
				$baseUrl .= $p.'/';
				if( !file_exists( BIT_ROOT_PATH.$baseUrl ) ) {
					if( !mkdir( BIT_ROOT_PATH.$baseUrl ) ) {
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
	function verifyStorageFile( $pFileName, $pSubDir = NULL, $pUserId = NULL, $pPackage = ACTIVE_PACKAGE ) {
		// don't worry about double slashes '//' for now. we'll remove them later
		$path = $this->getPreference('centralized_upload_dir').'/';
		if( empty( $path ) ) {
			$path = 'storage/';
		}
		if( !$pUserId ) {
			$path .= 'common/';
		} else {
			$path .= 'users/'.(int)($pUserId % 1000).'/'.$pUserId.'/';
		}
		$path .= $pPackage.'/'.$pSubDir.'/'.$pFileName;
		$path = BIT_ROOT_PATH.ereg_replace( '//','/',$path );
		if( file_exists( $path ) ) {
			return $path;
		} else {
			return FALSE;
		}
	}

	function verify( &$pParamHash ) {
		global $gBitSystem, $gBitUser;
		if( !empty( $pParamHash['attachment_id'] ) && !is_numeric( $pParamHash['attachment_id'] ) ) {
			$this->mErrors['file'] = 'System Error: Non-numeric storage_id.';
		}

		if( empty( $pParamHash['user_id'] ) ) {
			// storage is always owned by the user that uploaded it!
			// er... or at least admin if somehow we have a NULL mUserId - anon uploads maybe?
			$pParamHash['user_id'] = is_numeric( $gBitUser->mUserId ) ? $gBitUser->mUserId : ROOT_USER_ID;
		}
		if( empty( $pParamHash['process_storage'] ) ) {
			$pParamHash['process_storage'] = NULL;
		}

		if( empty( $pParamHash['subdir'] ) ) {
			$pParamHash['subdir'] = 'files';
		}

		if( !empty( $_FILES['upload'] ) ) {
			// tiki files upload
			$pParamHash['upload'] = $_FILES['upload'];
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
				$storageGuid = $gBitSystem->getPreference( 'common_storage_plugin', PLUGIN_GUID_BIT_FILES );
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
						$sql = "SELECT `attachment_id` FROM `".BIT_DB_PREFIX."tiki_attachments`
								WHERE `attachment_plugin_guid` = ? AND `content_id` = ? AND `foreign_id`=?";
						$rs = $this->mDb->query( $sql, array( $storeRow['plugin_guid'], (int)$storeRow['content_id'], (int)$storeRow['foreign_id'] ) );
						if( empty( $rs ) || !$rs->NumRows() ) {
							$pParamHash['attachment_id'] = $this->mDb->GenID( 'tiki_attachments_id_seq' );
							$sql = "INSERT INTO `".BIT_DB_PREFIX."tiki_attachments` ( `attachment_id`, `attachment_plugin_guid`, `content_id`, `foreign_id`, `user_id` ) VALUES ( ?, ?, ?, ?, ? )";
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
						$storeRow['upload']['dest_path'] = $this->getStorageBranch( $storeRow['attachment_id'], $pParamHash['user_id'], 'images' );
						$storagePath = liberty_process_upload( $storeRow );
						// We're gonna store to local file system & tiki_files table
						if( empty( $storagePath ) ) {
							$this->mErrors['file'] = 'Could not store file '.$storeRow['upload']['name'].'.';
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

		if( !empty( $pParamHash['existing_attachment_id'] ) ) {
			foreach($pParamHash['existing_attachment_id'] as $existingAttachmentId) {
				// allow for multiple values seperated by any non numeric character
				$ids = preg_split( '/\D/', $existingAttachmentId );
				foreach( $ids as $id ) {
					$id = ( int )$id;
					if( !empty( $id ) ) {
						$this->cloneAttachment( $id, $pParamHash['content_id'] );
					}
				}
			}
		}
		return( count( $this->mErrors ) == 0 );
	}

	// Clone an existing attachment but have it reference another content_id
	function cloneAttachment($pAttachmentId, $pNewContentId) {
		global $gLibertySystem;
		global $gBitUser;

		$sql = "SELECT * FROM `".BIT_DB_PREFIX."tiki_attachments` WHERE `attachment_id` = ?";
		$rs = $this->mDb->query($sql, array( $pAttachmentId ));
		$tmpAttachment = $rs->fields;

		if ( !empty($tmpAttachment['attachment_id']) ) {
			$newAttachmentId = $this->mDb->GenID( 'tiki_attachments_id_seq' );
			$sql = "INSERT INTO `".BIT_DB_PREFIX."tiki_attachments` ( `attachment_id`, `attachment_plugin_guid`, `content_id`, `foreign_id`, `user_id` ) VALUES ( ?, ?, ?, ?, ? )";
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

		if( is_numeric( $pAttachmentId ) ) {
			$sql = "SELECT `attachment_plugin_guid`, `user_id` FROM `".BIT_DB_PREFIX."tiki_attachments` WHERE `attachment_id`=?";
			$rs = $this->mDb->query( $sql, array( $pAttachmentId ) );
			$guid = $rs->fields['attachment_plugin_guid'];
			$user_id = $rs->fields['user_id'];

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
						$sql = "DELETE FROM `".BIT_DB_PREFIX."tiki_attachments` WHERE `attachment_id`=?";
						$this->mDb->query( $sql, array( $pAttachmentId ) );
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
		if (is_numeric($pAttachmentId)) {
			$attachmentInfo = $this->getAttachment($pAttachmentId);
			if (!empty($attachmentInfo['user_id'])) {
				$attachmentOwner = new BitUser($attachmentInfo['user_id']);
				$attachmentOwner->load();
				if ($attachmentOwner->mContentId) {
					$query = "UPDATE `".BIT_DB_PREFIX."tiki_attachments` SET `content_id` = ? WHERE `attachment_id` = ?";
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
		// assume a derived class has joined on the tiki_content table, and loaded it's columns already.
		global $gLibertySystem;
		$conId = ( isset( $pContentId ) && is_numeric( $pContentId ) ? $pContentId : $this->mContentId );

		if( !empty( $conId ) ) {
			LibertyContent::load($pContentId);
			$query = "SELECT * FROM `".BIT_DB_PREFIX."tiki_attachments` ta
					  WHERE ta.`content_id`=?";
			if( $result = $this->mDb->query($query,array((int) $conId)) ) {
				$this->mStorage = array();
				while( !$result->EOF ) {
					$row = &$result->fields;
					if( $func = $gLibertySystem->getPluginFunction( $row['attachment_plugin_guid'], 'load_function'  ) ) {
						$this->mStorage[$row['attachment_id']] = $func( $row );
					} else {
						print "NO load_function for ".$row['attachment_plugin_guid']." ".$gLibertySystem->mPlugins[$row['attachment_plugin_guid']];
					}
					$result->MoveNext();
				}
			}
		}
		return( TRUE );
	}

	// allow an optional content_id to be passed in to ease legacy lib style objects (like blogs, articles, etc.)
	function getAttachment( $pAttachmentId ) {
		// assume a derived class has joined on the tiki_content table, and loaded it's columns already.
		global $gLibertySystem;
		$ret = NULL;

		if( is_numeric( $pAttachmentId ) ) {
			$query = "SELECT * FROM `".BIT_DB_PREFIX."tiki_attachments` ta
					  WHERE ta.`attachment_id`=?";
			if( $result = $this->mDb->query($query,array((int) $pAttachmentId)) ) {
				$ret = array();
				if( !$result->EOF ) {
					$row = &$result->fields;
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

		if (!empty($attachmentInfo['attachment_id']) && !empty($attachmentInfo['foreign_id']) && !empty($attachmentInfo['attachment_plugin_guid']) ) {
			$query = "SELECT  * FROM `".BIT_DB_PREFIX."tiki_attachments` WHERE `foreign_id` = ? AND `attachment_plugin_guid` = ? AND `attachment_id` <> ?";
			$result = $this->mDb->query($query, array($attachmentInfo['foreign_id'], $attachmentInfo['attachment_plugin_guid'], $attachment['attachment_id']));
			$ret = $result->getRows();
		}

		return $ret;
	}
}

function liberty_process_upload( &$pFileHash ) {
	// Check for evil file extensions that could be execed on the server
	if( preg_match( '/(.pl|.php|.php3|.php4|.phtml|.cgi|.asp|.sh|.shtml)$/', $pFileHash['upload']['name'] ) ) {
		$pFileHash['upload']['name'] = $pFileHash['upload']['name'].'.txt';
	}
	// Thumbs.db is a windows My Photos/ folder file, and seems to really piss off imagick
	if( (preg_match( '/^image\/*/', $pFileHash['upload']['type'] ) || preg_match( '/pdf/i', $pFileHash['upload']['type'] ) )
		 && $pFileHash['upload']['name'] != 'Thumbs.db' ) {
		$ret = liberty_process_image( $pFileHash['upload'] );
	} else {
		$ret = liberty_process_generic( $pFileHash['upload'] );
	}
	return $ret;
}

function liberty_process_archive( &$pFileHash ) {
	$cwd = getcwd();
	$dir = dirname( $pFileHash['tmp_name'] );
	$upExt = strtolower( substr( $pFileHash['name'], (strrpos( $pFileHash['name'], '.' ) + 1) ) );
	$baseDir = $dir.'/';
	if( is_uploaded_file( $pFileHash['tmp_name'] ) ) {
		global $gBitUser;
		$baseDir .= $gBitUser->mUserId;
	}
	$destDir = $baseDir.'/'.basename( $pFileHash['tmp_name'] );
	if( (is_dir( $baseDir ) || mkdir( $baseDir )) && @mkdir( $destDir ) ) {
		// Some commands don't nicely support extracting to other directories
		chdir( $destDir );
		list( $mimeType, $mimeExt ) = split( '/', $pFileHash['type'] );
		switch( $mimeExt ) {
			case 'x-rar-compressed':
			case 'x-rar':
				$shellResult = shell_exec( "unrar x $pFileHash[tmp_name] \"$destDir\"" );
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
				$shellResult = shell_exec( "tar -x $compressFlag -f $pFileHash[tmp_name]  -C \"$destDir\"" );
				break;
			case 'x-zip-compressed':
			case 'x-zip':
			case 'zip':
				$shellResult = shell_exec( "unzip $pFileHash[tmp_name] -d \"$destDir\"" );
				break;
			case 'x-stuffit':
			case 'stuffit':
				$shellResult = shell_exec( "unstuff -d=\"$destDir\" $pFileHash[tmp_name] " );
				break;
			default:
				if( $upExt == 'zip' ) {
					$shellResult = shell_exec( "unzip $pFileHash[tmp_name] -d \"$destDir\"" );
				} elseif( $upExt == 'rar' ) {
					$shellResult = shell_exec( "unrar x $pFileHash[tmp_name] \"$destDir\"" );
				} elseif( $upExt == 'sit' || $upExt == 'sitx' ) {
					print( "unstuff -d=\"$destDir\" $pFileHash[tmp_name] " );
					$shellResult = shell_exec( "unstuff -d=\"$destDir\" $pFileHash[tmp_name] " );
				} else {
					$destDir = NULL;
				}
				break;
		}
	}
	chdir( $cwd );
	return $destDir;
}

function liberty_process_generic( &$pFileHash ) {
	$ret = NULL;
	$destBase = $pFileHash['dest_path'].$pFileHash['name'];
	$actualPath = BIT_ROOT_PATH.$destBase;
	if( is_uploaded_file( $pFileHash['source_file']) ) {
		if( move_uploaded_file( $pFileHash['source_file'], $actualPath ) ) {
			$ret = $destBase;
		}
	} elseif( copy( $pFileHash['source_file'], $actualPath ) ) {
		$ret = $destBase;
	}
	$pFileHash['size'] = filesize( $actualPath );

	return $ret;
}


function liberty_process_image( &$pFileHash ) {
	global $gBitSystem;
	$ret = NULL;
	$resizeFunc = liberty_get_function( 'resize' );

	list($type, $ext) = split( '/', strtolower( $pFileHash['type'] ) );
	mkdir_p( BIT_PKG_PATH.$pFileHash['dest_path'] );
	if( $resizePath = liberty_process_generic( $pFileHash, $ext ) ) {
		$pFileHash['source_file'] = BIT_ROOT_PATH.$resizePath;
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


function liberty_clear_thumbnails( &$pFileHash ) {
	$thumbsizes = array( 'avatar', 'small', 'medium', 'large' );
	foreach( $thumbsizes as $size ) {
		$fullPath =  BIT_PKG_PATH.$pFileHash['dest_path']."$size.jpg";
		if( file_exists( $fullPath ) ) {
			unlink( $fullPath );
		}
	}
}

function liberty_get_function( $pType ) {
	global $gBitSystem;
	$ret = NULL;
	switch( $gBitSystem->getPreference( 'image_processor' ) ) {
		case 'imagick':
			$ret = 'liberty_imagick_'.$pType.'_image';
			break;
		case 'magickwand':
			$ret = 'liberty_magickwand_'.$pType.'_image';
			break;
		default:
			$ret = 'liberty_gd_'.$pType.'_image';
			break;
	}
	return $ret;
}

define( 'MAX_THUMBNAIL_DIMENSION', 99999 );

function liberty_generate_thumbnails( &$pFileHash ) {
	global $gBitSystem;
	$resizeFunc = liberty_get_function( 'resize' );
	if( !preg_match( '/image\/(gif|jpg|jpeg|png)/', strtolower( $pFileHash['type'] ) ) && $gBitSystem->isFeatureActive( 'liberty_jpeg_originals' ) ) {
		// jpeg version of original
		$pFileHash['dest_base_name'] = 'original';
		$pFileHash['name'] = 'original.jpg';
		$pFileHash['max_width'] = MAX_THUMBNAIL_DIMENSION;
		$pFileHash['max_height'] = MAX_THUMBNAIL_DIMENSION;
		$pFileHash['icon_thumb_path'] = BIT_ROOT_PATH.$resizeFunc( $pFileHash );
	}
	// Icon thumb is 48x48
	$pFileHash['dest_base_name'] = 'icon';
	$pFileHash['name'] = 'icon.jpg';
	$pFileHash['max_width'] = 48;
	$pFileHash['max_height'] = 48;
	$pFileHash['icon_thumb_path'] = BIT_ROOT_PATH.$resizeFunc( $pFileHash );
	// Avatar thumb is 100x100
	$pFileHash['dest_base_name'] = 'avatar';
	$pFileHash['name'] = 'avatar.jpg';
	$pFileHash['max_width'] = 100;
	$pFileHash['max_height'] = 100;
	$pFileHash['small_thumb_path'] = BIT_ROOT_PATH.$resizeFunc( $pFileHash );
	// Small thumb is 160x120
	$pFileHash['dest_base_name'] = 'small';
	$pFileHash['name'] = 'small.jpg';
	$pFileHash['max_width'] = 160;
	$pFileHash['max_height'] = 120;
	$pFileHash['small_thumb_path'] = BIT_ROOT_PATH.$resizeFunc( $pFileHash );
	// Medium thumb is 400x300
	$pFileHash['dest_base_name'] = 'medium';
	$pFileHash['name'] = 'medium.jpg';
	$pFileHash['max_width'] = 400;
	$pFileHash['max_height'] = 300;
	$pFileHash['medium_thumb_path'] = BIT_ROOT_PATH.$resizeFunc( $pFileHash );
	// Large thumb is 800x600
	$pFileHash['dest_base_name'] = 'large';
	$pFileHash['name'] = 'large.jpg';
	$pFileHash['max_width'] = 800;
	$pFileHash['max_height'] = 600;
	$pFileHash['large_thumb_path'] = BIT_ROOT_PATH.$resizeFunc( $pFileHash );
}


// =-=-=-=-=-=-=-=-=-=- gd functions -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

function liberty_gd_resize_image( &$pFileHash, $pFormat = NULL ) {
	$ret = NULL;
	list($iwidth, $iheight, $itype, $iattr) = @getimagesize( $pFileHash['source_file'] );
	list($type, $ext) = split( '/', strtolower( $pFileHash['type'] ) );
	$destUrl = $pFileHash['dest_path'].$pFileHash['dest_base_name'];
	if( (empty( $pFileHash['max_width'] ) || empty( $pFileHash['max_height'] )) || ($iwidth <= $pFileHash['max_width'] && $iheight <= $pFileHash['max_height'] && ( $ext == 'gif' || $ext == 'png'  || $ext == 'jpg'   || $ext == 'jpeg' ) ) ) {
		// Keep the same dimensions as input file
		$pFileHash['max_width'] = $iwidth;
		$pFileHash['max_height'] = $iheight;
	} elseif( $iheight && (($iwidth / $iheight) > 0) && !empty( $pFileHash['max_width'] ) && !empty( $pFileHash['max_height'] ) ) {
		// we have a portrait image, flip everything
		$temp = $pFileHash['max_width'];
		$pFileHash['max_height'] = $pFileHash['max_width'];
		$pFileHash['max_width'] = $temp;
	}

	// we need to scale and/or reformat
	$fp = fopen( $pFileHash['source_file'], "rb" );
	$data = fread( $fp, filesize( $pFileHash['source_file'] ) );
	fclose ($fp);
	if( function_exists("ImageCreateFromString") ) {
		$img = @imagecreatefromstring($data);
	}

	if( !empty( $img ) ) {
		$size_x = imagesx($img);
		$size_y = imagesy($img);
	}

	if( !empty( $img ) && $size_x && $size_y ) {
		$transColor = imagecolortransparent( $img );
		if( $size_x > $size_y && !empty( $pFileHash['max_width'] ) ) {
			$tscale = ((int)$size_x / $pFileHash['max_width']);
		} elseif( !empty( $pFileHash['max_height'] ) ) {
			$tscale = ((int)$size_y / $pFileHash['max_height']);
		} else {
			$tscale = 1;
		}
		$tw = ((int)($size_x / $tscale));
		$ty = ((int)($size_y / $tscale));
		if (chkgd2()) {
			$t = imagecreatetruecolor($tw, $ty);
// png alpha stuff - needs more testing - spider
//	imagecolorallocatealpha ( $t, 0, 0, 0, 127 );
//	$ImgWhite = imagecolorallocate($t, 255, 255, 255);
//	imagefill($t, 0, 0, $ImgWhite);
//	imagecolortransparent($t, $ImgWhite);
			imagecopyresampled($t, $img, 0, 0, 0, 0, $tw, $ty, $size_x, $size_y);
		} else {
			$t = imagecreate($tw, $ty);
			$imagegallib->ImageCopyResampleBicubic($t, $img, 0, 0, 0, 0, $tw, $ty, $size_x, $size_y);
		}
		switch( $pFormat ) {
			case 'png':
				$ext = '.png';
				$destFile = BIT_PKG_PATH.'/'.$destUrl.$ext;
				imagepng( $t, $destFile );
				break;
			case 'gif':
				// This must go immediately before default so default will be hit for PHP's without gif support
				if( function_exists( 'imagegif' ) ) {
					$ext = '.gif';
					$destFile = BIT_PKG_PATH.'/'.$destUrl.$ext;
					imagegif( $t, $destFile );
					break;
				}
			default:
				$ext = '.jpg';
				$destFile = BIT_PKG_PATH.'/'.$destUrl.$ext;
				imagejpeg( $t, $destFile );
				break;
		}
		$pFileHash['name'] = $pFileHash['dest_base_name'].$ext;
		$pFileHash['size'] = filesize( $destFile );
		$ret = $destUrl.$ext;
	} elseif( $iwidth && $iheight ) {
		$ret = liberty_process_generic( $pFileHash );
	}

	return $ret;
}

function liberty_gd_rotate_image( &$pFileHash, $pFormat = NULL ) {
	if( !function_exists( 'imagerotate' ) ) {
		$pFileHash['error'] = "Rotate is not available on this webserver.";
	} elseif( empty( $pFileHash['degrees'] ) || !is_numeric( $pFileHash['degrees'] ) ) {
		$pFileHash['error'] = tra( 'Invalid rotation amount' );
	} else {
		// we need to scale and/or reformat
		$fp = fopen( $pFileHash['source_file'], "rb" );
		$data = fread( $fp, filesize( $pFileHash['source_file'] ) );
		fclose ($fp);
		if( function_exists("ImageCreateFromString") ) {
			$img = @imagecreatefromstring($data);
		}

		if( !empty( $img ) ) {
			//image rotate degrees seems back ass words.
			$rotateImg = imagerotate ( $img, (-1 * $pFileHash['degrees']), 0 );
			if( !empty( $rotateImg ) ) {
				imagejpeg( $rotateImg, $pFileHash['source_file'] );
			} else {
				$pFileHash['error'] = "Image rotation failed.";
			}
		} else {
			$pFileHash['error'] = "Image could not be opened for rotation.";
		}
	}

	return( empty( $pFileHash['error'] ) );
}

function liberty_gd_can_thumbnail_image( $pMimeType ) {
	$ret = FALSE;
	if( !empty( $pMimeType ) ) {
		$ret = preg_match( '/^image/i', $pMimeType );
	}
	return $ret;

}



// =-=-=-=-=-=-=-=-=-=- php-imagick functions -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

function liberty_imagick_resize_image( &$pFileHash, $pFormat = NULL ) {
	$pFileHash['error'] = NULL;
	$ret = NULL;
	if( !empty( $pFileHash['source_file'] ) && is_file( $pFileHash['source_file'] ) ) {
		$iImg = imagick_readimage( $pFileHash['source_file'] );
		if( !$iImg ) {
//			$pFileHash['error'] = $pFileHash['name'].' '.tra ( "is not a known image file" );
			$destUrl = liberty_process_generic( $pFileHash );
		} elseif( imagick_iserror( $iImg ) ) {
//			$pFileHash['error'] = imagick_failedreason( $iImg ) . imagick_faileddescription( $iImg );
			$destUrl = liberty_process_generic( $pFileHash );
		} else {
			imagick_set_image_quality( $iImg, 85 );
			$iwidth = imagick_getwidth( $iImg );
			$iheight = imagick_getheight( $iImg );
			if( (($iwidth / $iheight) > 0) && !empty( $pFileHash['max_width'] ) && !empty( $pFileHash['max_height'] ) ) {
				// we have a portrait image, flip everything
				$temp = $pFileHash['max_width'];
				$pFileHash['max_height'] = $pFileHash['max_width'];
				$pFileHash['max_width'] = $temp;
			}
			$itype = imagick_getmimetype( $iImg );
			list($type, $mimeExt) = split( '/', strtolower( $itype ) );
			if( !empty( $pFileHash['max_width'] ) && !empty( $pFileHash['max_height'] ) && ( ($pFileHash['max_width'] < $iwidth || $pFileHash['max_height'] < $iheight ) || ($mimeExt != 'jpeg')) ) {
				// We have to resize. *ALL* resizes are converted to jpeg
				$destExt = '.jpg';
				$destUrl = $pFileHash['dest_path'].$pFileHash['dest_base_name'].$destExt;
				$destFile = BIT_PKG_PATH.'/'.$destUrl;
				$pFileHash['name'] = $pFileHash['dest_base_name'].$destExt;
//	print "			if ( !imagick_resize( $iImg, $pFileHash[max_width], $pFileHash[max_height], IMAGICK_FILTER_LANCZOS, 0.5, $pFileHash[max_width] x $pFileHash[max_height] > ) ) {";

				// Alternate Filter settings can seen here http://www.dylanbeattie.net/magick/filters/result.html

				if ( !imagick_resize( $iImg, $pFileHash['max_width'], $pFileHash['max_height'], IMAGICK_FILTER_CATROM, 1.00, '>' ) ) {
					$pFileHash['error'] .= imagick_failedreason( $iImg ) . imagick_faileddescription( $iImg );
				}
// 	print "2YOYOYOYO $iwidth x $iheight $destUrl <br/>"; flush();

				if( function_exists( 'imagick_set_attribute' ) ) {
					// this exists in the PECL package, but not php-imagick
					$imagick_set_attribute($iImg,array("quality"=>1) );
				}

				if( !imagick_writeimage( $iImg, $destFile ) ) {
					$pFileHash['error'] .= imagick_failedreason( $iImg ) . imagick_faileddescription( $iImg );
				}
				$pFileHash['size'] = filesize( $destFile );
			} else {
	//print "GENERIC";
				$destUrl = liberty_process_generic( $pFileHash );
			}
		}
		$ret = $destUrl;
	} else {
		$pFileHash['error'] = "No source file to resize";
	}

	return $ret;
}


function liberty_imagick_rotate_image( &$pFileHash ) {
	$ret = FALSE;
	if( !empty( $pFileHash['source_file'] ) && is_file( $pFileHash['source_file'] ) ) {
		$iImg = imagick_readimage( $pFileHash['source_file'] );
		if( !$iImg ) {
			$pFileHash['error'] = $pFileHash['name'].' '.tra ( "is not a known image file" );
		} elseif( imagick_iserror( $iImg ) ) {
			$pFileHash['error'] = imagick_failedreason( $iImg ) . imagick_faileddescription( $iImg );
		} elseif( empty( $pFileHash['degrees'] ) || !is_numeric( $pFileHash['degrees'] ) ) {
			$pFileHash['error'] = tra( 'Invalid rotation amount' );
		} else {
			if ( !imagick_rotate( $iImg, $pFileHash['degrees'] ) ) {
				$pFileHash['error'] .= imagick_failedreason( $iImg ) . imagick_faileddescription( $iImg );
			}
			if( !imagick_writeimage( $iImg, $pFileHash['source_file'] ) ) {
				$pFileHash['error'] .= imagick_failedreason( $iImg ) . imagick_faileddescription( $iImg );
			}
		}
	} else {
		$pFileHash['error'] = "No source file to resize";
	}

	return( empty( $pFileHash['error'] ) );
}

function liberty_imagick_can_thumbnail_image( $pMimeType ) {
	$ret = FALSE;
	if( !empty( $pMimeType ) ) {
		$ret = preg_match( '/^image/i', $pMimeType );
	}
	return $ret;

}


// =-=-=-=-=-=-=-=-=-=- magickwand functions -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

function liberty_magickwand_resize_image( &$pFileHash, $pFormat = NULL ) {
	$magickWand = NewMagickWand();
	$pFileHash['error'] = NULL;
	$ret = NULL;
	$isPdf = preg_match( '/pdf/i', $pFileHash['type'] );
	if( !empty( $pFileHash['source_file'] ) && is_file( $pFileHash['source_file'] ) ) {
		// This has to come BEFORE the MagickReadImage
		if( $isPdf ) {
			MagickSetImageColorspace( $magickWand, MW_RGBColorspace );
			MagickSetImageUnits( $magickWand, MW_PixelsPerInchResolution );
			$rez =  empty( $pFileHash['max_width'] ) || $pFileHash['max_width'] == MAX_THUMBNAIL_DIMENSION ? 250 : 72;
			MagickSetResolution( $magickWand, 300, 300 );
		}
		if( $error = liberty_magickwand_check_error( MagickReadImage( $magickWand, $pFileHash['source_file'] ), $magickWand ) ) {
//			$pFileHash['error'] = $error;
			$destUrl = liberty_process_generic( $pFileHash );
		} else {

			if( $isPdf ) {
				MagickResetIterator( $magickWand );
				MagickNextImage( $magickWand );
			}

			MagickSetImageCompressionQuality( $magickWand, 85 );
			$iwidth = round( MagickGetImageWidth( $magickWand ) );
			$iheight = round( MagickGetImageHeight( $magickWand ) );
			$itype = MagickGetImageMimeType( $magickWand );

			MagickSetImageFormat( $magickWand, 'JPG' );

			if( empty( $pFileHash['max_width'] ) || empty( $pFileHash['max_height'] ) || $pFileHash['max_width'] == MAX_THUMBNAIL_DIMENSION || $pFileHash['max_height'] == MAX_THUMBNAIL_DIMENSION ) {
				$pFileHash['max_width'] = $iwidth;
				$pFileHash['max_height'] = $iheight;
			} elseif( (($iwidth / $iheight) < 1) && !empty( $pFileHash['max_width'] ) && !empty( $pFileHash['max_height'] ) ) {
				// we have a portrait image, flip everything
				$temp = $pFileHash['max_width'];
				$pFileHash['max_height'] = $pFileHash['max_width'];
				$pFileHash['max_width'] = round( ($iwidth / $iheight) * $pFileHash['max_height'] );
			} elseif( !empty( $pFileHash['max_width'] ) ) {
				$pFileHash['max_height'] = round( ($iheight / $iwidth) * $pFileHash['max_width'] );
			}

			list($type, $mimeExt) = split( '/', strtolower( $itype ) );
			if( !empty( $pFileHash['max_width'] ) && !empty( $pFileHash['max_height'] ) && ( ($pFileHash['max_width'] < $iwidth || $pFileHash['max_height'] < $iheight ) || ($mimeExt != 'jpeg')) ) {
				// We have to resize. *ALL* resizes are converted to jpeg
				$destExt = '.jpg';
				$destUrl = $pFileHash['dest_path'].$pFileHash['dest_base_name'].$destExt;
				$destFile = BIT_PKG_PATH.'/'.$destUrl;
				$pFileHash['name'] = $pFileHash['dest_base_name'].$destExt;
				// Alternate Filter settings can seen here http://www.dylanbeattie.net/magick/filters/result.html
				if ( $error = liberty_magickwand_check_error( MagickResizeImage( $magickWand, $pFileHash['max_width'], $pFileHash['max_height'], MW_CatromFilter, 1.00 ), $magickWand ) ) {
					$pFileHash['error'] .= $error;
				}
				if( $error = liberty_magickwand_check_error( MagickWriteImage( $magickWand, $destFile ), $magickWand ) ) {
					$pFileHash['error'] .= $error;
				}
				$pFileHash['size'] = filesize( $destFile );
			} else {
				$destUrl = liberty_process_generic( $pFileHash );
			}
		}
		$ret = $destUrl;
	} else {
		$pFileHash['error'] = "No source file to resize";
	}
	DestroyMagickWand( $magickWand );
	return $ret;
}


function liberty_magickwand_rotate_image( &$pFileHash ) {
	$ret = FALSE;
	$magickWand = NewMagickWand();
	$pFileHash['error'] = NULL;
	if( !empty( $pFileHash['source_file'] ) && is_file( $pFileHash['source_file'] ) ) {
		if( $error = liberty_magickwand_check_error( MagickReadImage( $magickWand, $pFileHash['source_file'] ), $magickWand ) ) {
			$pFileHash['error'] = $error;
		} elseif( empty( $pFileHash['degrees'] ) || !is_numeric( $pFileHash['degrees'] ) ) {
			$pFileHash['error'] = tra( 'Invalid rotation amount' );
		} else {
			$bgWand = NewPixelWand('white');
			if( $error = liberty_magickwand_check_error( MagickRotateImage( $magickWand, $bgWand, $pFileHash['degrees'] ), $magickWand ) ) {
				$pFileHash['error'] .= $error;
			}
			if( $error = liberty_magickwand_check_error( MagickWriteImage( $magickWand, $pFileHash['source_file'] ), $magickWand ) ) {
				$pFileHash['error'] .= $error;
			}
		}
	} else {
		$pFileHash['error'] = "No source file to resize";
	}

	return( empty( $pFileHash['error'] ) );
}




function liberty_magickwand_check_error( $pResult, $pWand ) {
	$ret = FALSE;
    if( $pResult === FALSE && WandHasException( $pWand ) ) {
        $ret = 'An image processing error occurred : '.WandGetExceptionString($pWand);
    }
    return $ret;
}

function liberty_magickwand_can_thumbnail_image( $pMimeType ) {
	$ret = FALSE;
	if( !empty( $pMimeType ) ) {
		$ret = preg_match( '/(^image|pdf)/i', $pMimeType );
	}
	return $ret;

}


?>
