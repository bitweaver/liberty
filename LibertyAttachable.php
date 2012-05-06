<?php
/**
 * Management of Liberty Content
 *
 * @package  liberty
 * @version  $Header$
 * @author   spider <spider@steelsun.com>
 */
// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See below for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
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

/**
 * LibertyAttachable class
 *
 * @package liberty
 */
class LibertyAttachable extends LibertyContent {
	var $mContentId;
	var $mStorage;

	function LibertyAttachable() {
		parent::__construct();
	}

	// {{{ =================== Deprecated Methods ====================
	/**
	 * TODO: This code is old and is not used by any package in the bitweaver CVS anymore.
	 * We will clean up this code as soon as we are sure that noone is using this code.
	 * Please look for the closing tripple '}' brackets to see where this section ends.
	 */

	/**
	 * fully load content and insert any attachments in $this->mStorage
	 * allow an optional content_id to be passed in to ease legacy lib style objects (like blogs, articles, etc.)
	 *
	 * @param array $pContentId
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 * @deprecated deprecated since version 2.1.0-beta
	 */

	/**
	 * TODO: This code is old and is not used by any package in the bitweaver CVS anymore.
	 * We will clean up this code as soon as we migrated all legacy code
	 */
	function load( $pContentId = NULL, $pPluginParams = NULL ) {
		//deprecated( "This method has been replaced by a method in LibertyMime. Please try to migrate your code." );
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
					if( $func = $gLibertySystem->getPluginFunction( $row['attachment_plugin_guid'], 'load_function', 'mime' )) {
						// this dummy is needed for forward compatability with LibertyMime plugins
						$dummy = array();
						$this->mStorage[$row['attachment_id']] = $func( $row, $dummy );
						//$this->mStorage[$row['attachment_id']]['is_primary'] = !empty( $row['primary_attachment_id'] );
					} else {
						print "No load_function for ".$row['attachment_plugin_guid']." ".$gLibertySystem->mPlugins[$row['attachment_plugin_guid']];
					}
				}
			}
		}
		return( TRUE );
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
	 * @deprecated deprecated since version 2.1.0-beta
	 */

	/**
	 * TODO: This code is old and is not used by any package in the bitweaver CVS anymore.
	 * We will clean up this code as soon as we migrated all legacy code
	 */
	function store( &$pParamHash ) {
		//deprecated( "This method has been replaced by a method in LibertyMime. Please try to migrate your code." );
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
								$storeRow['upload']['dest_branch'] = $this->getStorageBranch( $storeRow['attachment_id'], $storeRow['user_id'], $this->getStorageSubDirName() );
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
							if( @BitBase::verifyId( $storeRow['attachment_id'] ) && !isset( $storeRow['skip_insert'] ) && !LibertyMime::loadAttachment( $storeRow['attachment_id'] )) {
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
	 * verifyAttachment
	 *
	 * @param array $pParamHash
	 * @param array $pFile
	 * @param array $pKey
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function verifyAttachment( &$pParamHash, $pFile, $pKey ) {
		//deprecated( "This method has been replaced by a method in LibertyMime. Please try to migrate your code." );
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
						$tmp = preg_split("#[\\\]#",$pFile['name']);
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
					//$pFile['type'] = $gBitSystem->verifyMimeType( $pFile['tmp_name'] );
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
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function verify( &$pParamHash ) {
		//deprecated( "This method has been replaced by a method in LibertyMime. Please try to migrate your code." );
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

		// if we have an error we get them all by checking parent classes for additional errors
		if( count( $this->mErrors ) > 0 ){
			parent::verify( $pParamHash );
		}

		return ( count( $this->mErrors ) == 0 );
	}

	/**
	 * extractMetaData extract meta data from images
	 *
	 * @param array $pParamHash
	 * @param array $pFile
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function extractMetaData( &$pParamHash, &$pFile ) {
		//deprecated( "This method has been replaced by a method in LibertyMime. Please try to migrate your code." );
		// Process a JPEG , jpeg_metadata_tk REQUIRES short_tags because that is the way it was written. feel free to fix something. XOXO spiderr
		if( ini_get( 'short_open_tag' ) && function_exists( 'exif_read_data' ) && !empty( $pFile['tmp_name'] ) && strpos( strtolower($pFile['type']), 'jpeg' ) !== FALSE ) {
			$exifHash = @exif_read_data( $pFile['tmp_name'], 0, true);

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

	// }}}


}

// FIXME: this is really dirty and needs to go away from here
// make sure LibertyMime is available during this transition phase
// we need to call this down here since LM extends LA and can't be included before LA is available
require_once( LIBERTY_PKG_PATH.'LibertyMime.php' );

/* vim: :set fdm=marker : */
?>
