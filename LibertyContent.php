<?php
/**
* Management of Liberty content
*
* @package  liberty
* @version  $Header: /cvsroot/bitweaver/_bit_liberty/LibertyContent.php,v 1.2.2.45 2005/11/22 17:24:19 squareing Exp $
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

// define( 'CONTENT_TYPE_WIKI', '1' );
// define( 'CONTENT_TYPE_COMMENT', '3' );
// define( 'CONTENT_TYPE_USER', '4' );

/**
 * Maximum lengths for database fields
 */
define( 'BIT_CONTENT_MAX_TITLE_LEN', 160);
define( 'BIT_CONTENT_MAX_LANGUAGE_LEN', 4);
define( 'BIT_CONTENT_MAX_IP_LEN', 39);
define( 'BIT_CONTENT_MAX_FORMAT_GUID_LEN', 16);

/**
 * required setup
 */
require_once( LIBERTY_PKG_PATH.'LibertyBase.php' );

/**
 * Virtual base class (as much as one can have such things in PHP) for all
 * derived tikiwiki classes that require database access.
 *
 * @package liberty
 */
class LibertyContent extends LibertyBase {
    /**
    * Content Id if an object has been loaded
    * @public
    */
	var $mContentId;
    /**
    * If this content is being viewed within a structure
    * @public
    */
	var $mStructureId;
    /**
    * Content type GUID for this LibertyContent object
    * @public
    */
	var $mContentTypeGuid;
    /**
    * Content type hash for this LibertyContent object
    * @public
    */
	var $mType;
    /**
    * Permissions hash specific to this LibertyContent object
    * @public
    */
	var $mPerms;
    /**
    * Admin control permission specific to this LibertyContent type
    * @private
    */
	var $mAdminContentPerm;

    /**
    * Construct an empty LibertyBase object with a blank permissions array
    */
	function LibertyContent () {
		LibertyBase::LibertyBase();
		$this->mPerms = array();
		if( empty( $this->mAdminContentPerm ) ) {
			$this->mAdminContentPerm = 'bit_p_admin_content';
		}
	}

    /**
    * Assume a derived class has joined on the tiki_content table, and loaded it's columns already.
    */
	function load($pContentId = NULL) {
		if( !empty( $this->mInfo['content_type_guid'] ) ) {
			global $gLibertySystem, $gBitSystem;
			$this->mInfo['content_type'] = $gLibertySystem->mContentTypes[$this->mInfo['content_type_guid']];
			if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
//				$this->mInfo['perm_level'] = $this->getUserPermissions();
			}
		}

	}

    /**
     * Verify the core class data required to update the tiki_content table entries
	 *
	 * Verify will build an array [content_store] with all of the required values
	 * and populate it with the relevent data to create/update the tiki_content
	 * table record
	 *
	 * @param array Array of content data to be stored <br>
	 * [pParamHash] <br>
	 * content_id <br>
	 * user_id <br>
	 * modifier_user_id <br>
	 * created <br>
	 * last_modified <br>
	 * content_type_guid <br>
	 * format_guid <br>
	 * hits <br>
	 * language <br>
	 * title <br>
	 * ip <br>
	 * data <br>
	 * <br>
	 * @return integer Count of the number of errors ( 0 for success ) <br>
	 * [pParamHash] will be extended to include array [content_store] populated
	 * with the require values for LibertyContent::store()
	 */
	function verify( &$pParamHash ) {
		global $gLibertySystem, $gBitSystem;
		if( empty( $pParamHash['user_id'] ) ) {
			global $gBitUser;
			$pParamHash['user_id'] = $gBitUser->getUserId();
		}

		if( empty( $pParamHash['content_id'] ) ) {
			if( empty( $this->mContentId ) ) {
				// These should never be updated, only inserted
				$pParamHash['content_store']['created'] = !empty( $pParamHash['created'] ) ? $pParamHash['created'] : $gBitSystem->getUTCTime();
				$pParamHash['content_store']['user_id'] = $pParamHash['user_id'];
			} else {
				$pParamHash['content_id'] = $this->mContentId;
			}
		}

		$pParamHash['field_changed'] = empty( $pParamHash['content_id'] )
					   || (!empty($this->mInfo["data"]) && !empty($_REQUEST["edit"]) && (md5($this->mInfo["data"]) != md5($_REQUEST["edit"])))
					   || (!empty($_REQUEST["title"]) && !empty($this->mInfo["title"]) && (md5($this->mInfo["title"]) != md5($_REQUEST["title"])));
		// check some lengths, if too long, then truncate
		if( !empty( $pParamHash['title'] ) ) {
			$pParamHash['content_store']['title'] = substr( $pParamHash['title'], 0, 160 );
		} elseif( isset( $pParamHash['title'] ) ) {
			$pParamHash['content_store']['title'] = NULL;
		}

		$pParamHash['content_store']['last_modified'] = !empty( $pParamHash['last_modified'] ) ? $pParamHash['last_modified'] : $gBitSystem->getUTCTime();

		// WARNING: Assume WIKI if t
		if( isset( $pParamHash['content_id'] ) ) {
			// do NOT allow changing of content_type_guid in update for safety of overridden secondary classes (like BitBook )
			unset( $pParamHash['content_store']['content_type_guid'] );
		} elseif( empty( $pParamHash['content_type_guid'] ) ) {
			$this->mErrors['content_type'] = tra( 'System Error: Unknown content type' );
		} else {
			$pParamHash['content_store']['content_type_guid'] = $pParamHash['content_type_guid'];
		}

		// setup some required defaults if not defined
		if( empty( $pParamHash['ip'] ) ) {
			if( empty( $_SERVER["REMOTE_ADDR"] ) ) {
				$pParamHash['ip'] = '127.0.0.1';
			} else {
				$pParamHash['ip'] = $_SERVER["REMOTE_ADDR"];
			}
		}
		$pParamHash['content_store']['ip'] = $pParamHash['ip'];

		if( empty( $pParamHash['modifier_user_id'] ) ) {
			global $gBitUser;
			$pParamHash['modifier_user_id'] = $gBitUser->getUserId();
		}
		$pParamHash['content_store']['modifier_user_id'] = $pParamHash['modifier_user_id'];

		if( empty( $pParamHash['format_guid'] ) ) {
			$pParamHash['format_guid'] = 'tikiwiki';
		}
		$pParamHash['content_store']['format_guid'] = $pParamHash['format_guid'];

		if( !empty( $pParamHash['hits'] ) ) {
			$pParamHash['content_store']['hits'] = $pParamHash['hits'] + 1;
		}

		if( !empty( $pParamHash['edit'] ) && $func = $gLibertySystem->getPluginFunction( $pParamHash['format_guid'], 'verify_function' ) ) {
			$error = $func( $pParamHash );
			if( $error ) {
				$this->mErrors['format'] = $error;
			}
		}

		if( empty( $pParamHash['edit'] ) && !empty( $this->mInfo['data'] ) ) {
			// someone has deleted the data entirely - common for fisheye
			$pParamHash['content_store']['data'] = NULL;
		}
		$pParamHash['content_store']['format_guid'] = $pParamHash['format_guid'];

		return( count( $this->mErrors ) == 0 );

	}

    /**
     * Create a new content object or update an existing one
	 *
	 * @param array Array of content data to be stored <br>
	 * See verify for details of the values required
	 */
	function store( &$pParamHash ) {
		global $gLibertySystem;
		if( LibertyContent::verify( $pParamHash ) ) {
			$this->mDb->StartTrans();
			$table = BIT_DB_PREFIX."tiki_content";
			if( empty( $pParamHash['content_id'] ) ) {
				$pParamHash['content_store']['content_id'] = $this->mDb->GenID( 'tiki_content_id_seq' );
				$pParamHash['content_id'] = $pParamHash['content_store']['content_id'];
				// make sure some variables are stuff in case services need getObjectType, mContentId, etc...
				$this->mInfo['content_type_guid'] = $pParamHash['content_type_guid'];
				$this->mContentId = $pParamHash['content_store']['content_id'];
				$result = $this->mDb->associateInsert( $table, $pParamHash['content_store'] );
			} else {
				if( !empty( $pParamHash['content_store']['title'] ) && !empty( $this->mInfo['title'] ) ) {
					$renamed = $pParamHash['content_store']['title'] != $this->mInfo['title'];
				}
				$locId = array ( "name" => "content_id", "value" => $pParamHash['content_id'] );
				$result = $this->mDb->associateUpdate( $table, $pParamHash['content_store'], $locId );
			}

			$this->invokeServices( 'content_store_function', $pParamHash );

			// Call the formatter's save
			if( !empty( $pParamHash['edit'] ) ) {
				if( $func = $gLibertySystem->getPluginFunction( $pParamHash['format_guid'], 'store_function' ) ) {
					$ret = $func( $pParamHash );
				}
			}

			// If we renamed the page, we need to update the backlinks
			if( !empty( $renamed ) && $func = $gLibertySystem->getPluginFunction( $pParamHash['format_guid'], 'rename_function' ) ) {
				$ret = $func( $this->mContentId, $this->mInfo['title'], $pParamHash['content_store']['title'], $this );
			}
			$this->mDb->CompleteTrans();
		}
		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * Delete comment entries relating to the content object
	 */
	function expungeComments() {
		require_once( LIBERTY_PKG_PATH.'LibertyComment.php' );
		// Delete all comments associated with this piece of content
		$query = "SELECT `comment_id` FROM `".BIT_DB_PREFIX."tiki_comments` WHERE `parent_id` = ?";
		$result = $this->mDb->query($query, array( $this->mContentId ) );
		$commentIds = $result->getRows();
		foreach ($commentIds as $commentId) {
			$tmpComment = new LibertyComment($commentId);
			$tmpComment->deleteComment();
		}
		return TRUE;
	}

	/**
	 * Delete content object and all related records
	 */
	function expunge() {
		global $gBitSystem;
		$ret = FALSE;
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$this->expungeComments();

			$this->invokeServices( 'content_expunge_function', $this );

			/* seems out of place - xing
			if( $func = $gLibertySystem->getPluginFunction( $pParamHash['format_guid'], 'expunge_function' ) ) {
				$ret = $func( $this->mContentId );
			}
			*/

			$query = "DELETE FROM `".BIT_DB_PREFIX."tiki_content` WHERE `content_id` = ?";
			$result = $this->mDb->query( $query, array( $this->mContentId ) );
			$this->mDb->CompleteTrans();
			$ret = TRUE;
		}
/*
// ported from gBitSystem - this should probably be execute as well - spiderr
	function remove_object($type, $id) {
		$this->uncategorize_object($type, $id);
		// Now remove comments
		$object = $type . $id;
		$query = "delete from `".BIT_DB_PREFIX."tiki_comments` where `object`=?  and `object_type`=?";
		$result = $this->mDb->query($query, array( $id, $type ));
		// Remove individual permissions for this object if they exist
		$query = "delete from `".BIT_DB_PREFIX."users_objectpermissions` where `object_id`=? and `object_type`=?";
		$result = $this->mDb->query($query,array((int)$object,$type));
		return true;
	}
*/
		return $ret;
	}

	/**
	 * Create an export object from the data
	 *
	 * @param array Not used
	 */
	function exportHtml( $pData = NULL ) {
		$ret = NULL;
		$ret[] = array(	'type' => $this->mContentTypeGuid,
						'landscape' => FALSE,
						'url' => $this->getDisplayUrl(),
						'content_id' => $this->mContentId,
					);
		return $ret;
	}

	/**
	 * Check mContentId to establish if the object has been loaded with a valid record
	 */
	function isValid() {
		return( !empty( $this->mContentId ) && is_numeric( $this->mContentId ) && $this->mContentId );
	}

	/**
	 * Check user_id to establish if the object that has been loaded was created by the current user
	 */
	function isOwner() {
		global $gBitUser;
		return( $this->isValid() && !empty( $this->mInfo['user_id'] ) && $this->mInfo['user_id'] == $gBitUser->mUserId );
	}


	/**
	 * Check user_id to establish if the object that has been loaded was created by the current user
	 */
	function isContentType( $pContentGuid ) {
		global $gBitUser;
		return( $this->isValid() && !empty( $this->mInfo['content_type_guid'] ) && $this->mInfo['content_type_guid'] == $pContentGuid );
	}


	function verifyAccessControl() {
		$this->invokeServices( 'content_verify_access' );
	}


	function invokeServices( $pServiceFunction, $pParamHash=NULL ) {
		global $gLibertySystem;
		$errors = array();
		// Invoke any services store functions such as categorization or access control
		if( $serviceFunctions = $gLibertySystem->getServiceValues( $pServiceFunction ) ) {
			foreach ( $serviceFunctions as $func ) {
				if( function_exists( $func ) ) {
					if( $errors = $func( $this, $pParamHash ) ) {
						$this->mErrors = array_merge( $this->mErrors, $errors );
					}
				}
			}
		}
		return $errors;
	}


	function getServicesSql( $pServiceFunction, &$pSelectSql, &$pJoinSql, &$pWhereSql, &$pBindVars ) {
		global $gLibertySystem;
		if( $loadFuncs = $gLibertySystem->getServiceValues( $pServiceFunction ) ) {
			foreach( $loadFuncs as $func ) {
				if( function_exists( $func ) ) {
					$loadHash = $func();
					if( !empty( $loadHash['select_sql'] ) ) {
						$pSelectSql .= $loadHash['select_sql'];
					}
					if( !empty( $loadHash['join_sql'] ) ) {
						$pJoinSql .= $loadHash['join_sql'];
					}
					if( !empty( $loadHash['where_sql'] ) ) {
						$pWhereSql .= $loadHash['where_sql'];
					}
				}
			}
		}
	}


	/**
	 * Check permissions for the object that has been loaded against the permission database
	 */
	function loadPermissions() {
		if( $this->isValid() && empty( $this->mPerms ) && $this->mContentTypeGuid ) {
			//$object_id = md5($object_type . $object_id);
			$query = "select uop.`perm_name`, ug.`group_id`, ug.`group_name`
					  FROM `".BIT_DB_PREFIX."users_objectpermissions` uop
						INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON( uop.`group_id`=ug.`group_id` )
					  WHERE uop.`object_id` = ? AND uop.`object_type` = ?";
			$bindVars = array( $this->mContentId, $this->mContentTypeGuid );
			$this->mPerms = $this->mDb->getAssoc($query, $bindVars);
		}
		return( count( $this->mPerms ) );
	}

	/**
	 * Function that determines if this content specified permission for the current gBitUser
	 *
	 * @param string Name of the permission to check
	 * @param bool Generate fatal message if permission denigned
	 * @param string Message if permission denigned
	 * @return bool true if user has permission to access file
	 * @todo Fatal message still to be implemented
	 */
	function hasUserPermission( $pPermName, $pFatalIfFalse=FALSE, $pFatalMessage=NULL  ) {
		global $gBitUser;
		if( !$gBitUser->isRegistered() || !($ret = $this->isOwner()) ) {
			if( !($ret = $this->hasAdminPermission()) ) {
				$this->verifyAccessControl();
				if( $this->loadPermissions() ) {
					$userPerms = $this->getUserPermissions( $gBitUser->mUserId );
					$ret = isset( $userPerms[$pPermName]['user_id'] ) && ( $userPerms[$pPermName]['user_id'] == $gBitUser->mUserId );
				} else {
					$ret = $gBitUser->hasPermission( $pPermName );
				}
			}
		}
		return( $ret );
	}

	/**
	 * Determine if current user has the ability to administer this type of content
	 *
	 * @return bool True if user has this type of content administration permission
	 */
	function hasAdminPermission() {
		global $gBitUser;
		return( $gBitUser->isAdmin() || $gBitUser->hasPermission( $this->mAdminContentPerm ) );
	}


	/**
	 * Get specific permissions for the specified user for this content
	 *
	 * @param integer Id of user for whom permissions are to be loaded
	 * @return array Array of user permissions
	 */
	function getUserPermissions( $pUserId ) {
		$ret = array();
		if( $pUserId ) {
			$query = "SELECT uop.`perm_name`, ug.`group_id`, ug.`group_name`, ugm.`user_id`
					  FROM `".BIT_DB_PREFIX."users_objectpermissions` uop
						INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON( uop.`group_id`=ug.`group_id` )
						INNER JOIN `".BIT_DB_PREFIX."users_groups_map` ugm ON( ugm.`group_id`=ug.`group_id` )
					  WHERE ugm.`user_id`=? AND uop.`object_id` = ? AND uop.`object_type` = ? ";
			$bindVars = array( $pUserId, $this->mContentId, $this->mContentTypeGuid );
			$ret = $this->mDb->getAssoc($query, $bindVars);
		}
		return $ret;
	}

	/**
	 * Store a permission for the object that has been loaded in the permission database
	 *
	 * Any old copy of the permission is deleted prior to loading the new copy
	 * @param integer Group Identifier
	 * @param string Name of the permission
	 * @param integer Content Itentifier
	 * @return bool true ( will not currently report a failure )
	 */
	function storePermission( $pGroupId, $perm_name, $object_id=NULL ) {
		if( empty( $object_id ) ) {
			$object_id = $this->mContentId;
		}
		//$object_id = md5($object_type . $object_id);
		$query = "DELETE FROM `".BIT_DB_PREFIX."users_objectpermissions`
				  WHERE `group_id` = ? AND `perm_name` = ? AND `object_id` = ?";
		$result = $this->mDb->query($query, array($pGroupId, $perm_name, $object_id), -1, -1);
		$query = "insert into `".BIT_DB_PREFIX."users_objectpermissions`
				  (`group_id`,`object_id`, `object_type`, `perm_name`)
				  VALUES ( ?, ?, ?, ? )";
		$result = $this->mDb->query($query, array($pGroupId, $object_id, $this->mContentTypeGuid, $perm_name));
		return true;
	}

	/**
	 * Check is a user has permission to access the object
	 *
	 * @param integer User Identifier
	 * @param integer Content Itentifier
	 * @param string Content Type GUID
	 * @param string Name of the permission
	 * @return bool true if access is allowed
	 */
	function hasPermission( $pUserId, $object_id, $object_type, $perm_name ) {
		$ret = FALSE;
		$groups = $this->get_user_groups( $pUserId );
		foreach ( $groups as $group_name ) {
			$query = "SELECT count(*)
					  FROM `".BIT_DB_PREFIX."users_objectpermissions`
					  WHERE `group_name` = ? and `object_id` = ? and `object_type` = ? and `perm_name` = ?";
			$bindVars = array($group_name, $object_id, $object_type, $perm_name);
			$result = $this->mDb->getOne( $query, $bindVars );
			if ($result>0) {
				$ret = true;
			}
		}
		return $ret;
	}

	/**
	 * Remove a permission to access the object
	 *
	 * @param integer Group Identifier
	 * @param string Name of the permission
	 * @return bool true ( will not currently report a failure )
	 */
	function removePermission( $pGroupId, $perm_name ) {
		//$object_id = md5($object_type . $object_id);
		$query = "delete from `".BIT_DB_PREFIX."users_objectpermissions`
			where `group_id` = ? and `object_id` = ?
			and `object_type` = ? and `perm_name` = ?";
		$bindVars = array($pGroupId, $this->mContentId, $this->mContentTypeGuid, $perm_name);
		$result = $this->mDb->query($query, $bindVars);
		return true;
	}

	/**
	 * Copy current permissions to another object
	 *
	 * @param integer Content Identifier of the target object
	 * @return bool true ( will not currently report a failure )
	 */
	function copyPermissions( $destinationObjectId ) {
		//$object_id = md5($object_type.$object_id);
		$query = "select `perm_name`, `group_name`
			from `".BIT_DB_PREFIX."users_objectpermissions`
			where `object_id` =? and `object_type` = ?";
		$bindVars = array( $this->mContentId, $this->mContentTypeGuid );
		$result = $this->mDb->query($query, $bindVars);
		while($res = $result->fetchRow()) {
			$this->storePermission( $res["group_name"], $this->mContentTypeGuid, $res["perm_name"], $destinationObjectId );
		}
		return true;
	}

	/**
	 * Copy current permissions to another object
	 *
	 * @param string Content Type GUID
	 * @param array Array of content type data
	 * Populates the mType array with the following entries
	 * string	content_type_guid
	 * string
	 */
	function registerContentType( $pContentGuid, $pTypeParams ) {
		global $gLibertySystem;
		$gLibertySystem->registerContentType( $pContentGuid, $pTypeParams );
		$this->mType = $pTypeParams;
	}

	/**
	 * Increment the content item hit flag by 1
	 *
	 * @return bool true ( will not currently report a failure )
	 */
	function addHit() {
		global $gBitUser;
		if( empty( $_REQUEST['post_comment_submit'] ) && empty( $_REQUEST['post_comment_request'] ) ) {
			if( $this->mContentId && ( $gBitUser->mUserId != $this->mInfo['user_id'] ) ) {
				$query = "UPDATE `".BIT_DB_PREFIX."tiki_content` SET `hits`=`hits`+1 WHERE `content_id` = ?";
				$result = $this->mDb->query( $query, array( $this->mContentId ) );
			}
		}
		return TRUE;
	}

    /**
    * Determines if a wiki page (row in tiki_pages) exists, and returns a hash of important info. If N pages exists with $pPageName, returned existsHash has a row for each unique pPageName row.
    * @param pPageName name of the wiki page
    * @param pCaseSensitive look for case sensitive names
    */
	function pageExists( $pPageName, $pCaseSensitive=FALSE ) {
		$ret = NULL;
		$pageWhere = $pCaseSensitive ? 'tc.`title`' : 'LOWER( tc.`title` )';
		$bindVars = array( ($pCaseSensitive ? $pPageName : strtolower( $pPageName ) ) );
		$query = "SELECT `page_id`, tp.`content_id`, `description`, tc.`last_modified`, tc.`title`
				  FROM `".BIT_DB_PREFIX."tiki_pages` tp, `".BIT_DB_PREFIX."tiki_content` tc
				  WHERE tc.`content_id`=tp.`content_id` AND $pageWhere = ?";
		$result = $this->mDb->query($query, array( $bindVars ));

		if( $result->numRows() ) {
			$ret = $result->getArray();
		}

		return $ret;
	}

	/**
	 * Create the generic title for a content item
	 *
	 * This will normally be overwriten by extended classes to provide
	 * an appropriate title title string
	 * @param array mInfo type hash of data to be used to provide base data
	 * @return string Descriptive title for the object
	 */
	function getTitle( $pHash=NULL ) {
		$ret = NULL;
		if( empty( $pHash ) ) {
			$pHash = &$this->mInfo;
		}
		if( !empty( $pHash['title'] ) ) {
			$ret = $pHash['title'];
		} elseif( !empty( $pHash['content_description'] ) ) {
			$ret = $pHash['content_description'];
		}
		return $ret;
	}

	/**
	 * Access a content item type GUID
	 *
	 * @return string content_type_guid for the object
	 */
	function getContentType() {
		$ret = NULL;
		if( isset( $this->mInfo['content_type_guid'] ) ) {
			$ret = $this->mInfo['content_type_guid'];
		}
		return $ret;
	}

	/**
	 * Access a content item type GUID
	 *
	 * @return string content_type_guid for the object
	 */
	function getContentId() {
		$ret = NULL;
		if( isset( $this->mContentId ) ) {
			$ret = $this->mContentId;
		}
		return $ret;
	}

	/**
	 * Return content type description for this content object.
	 *
	 * @return string content_type_guid description for the object
	 */
	function getContentDescription() {
		$ret = NULL;
		if( isset( $this->mInfo['content_type_guid'] ) ) {
			global $gLibertySystem;
			if( !empty( $gLibertySystem->mContentTypes[$this->mInfo['content_type_guid']]['content_description'] ) ) {
				$ret = $gLibertySystem->mContentTypes[$this->mInfo['content_type_guid']]['content_description'];
			} else {
				$ret = $this->mInfo['content_type_guid'];
			}
		}
		return $ret;
	}


    /**
    * Pure virtual function that returns the include file that should render a page of content of this type
    * @return the fully specified path to file to be included
    */
	function getRenderFile() {
		return LIBERTY_PKG_PATH.'display_content_inc.php';
	}

    /**
    * Pure virtual function that returns link to display a piece of content
    * @param string Text for the link unless overriden by object title
    * @param array different possibilities depending on derived class
    * @return string Formated html the link to display the page.
    */
	function getDisplayLink( $pLinkText, $pMixed ) {
		$ret = '';
		if( $this ) {
			$title = $this->getTitle();
			if( empty( $title ) && !empty( $pMixed['title'] ) ) {
				$title = $pMixed['title'];
			}
			$ret = '<a href="'.BIT_ROOT_URL.'index.php?content_id='.$pMixed['content_id'].'">'.$title.'</a>';
		}
		return $ret;
	}

    /**
    * Pure virtual function that returns Request_URI to a piece of content
    * @param string Text for DisplayLink function
    * @param array different possibilities depending on derived class
    * @return string Formated URL address to display the page.
    */
	function getDisplayUrl( $pLinkText, $pMixed ) {
		print "UNDEFINED PURE VIRTUAL FUNCTION: LibertyContent::getDisplayUrl";
	}

    /**
    * Updates results from any getList function to provide the control set
    * displaying in the smarty template
    * @param array hash of parameters returned by any getList() function
    * @return - none the hash is updated via the reference
    */
	function postGetList( &$pListHash ) {

		$pListHash['control']['cant_pages'] = ceil($pListHash["cant"] / $pListHash['max_records']);

		$pListHash['control']['actual_page'] = 1 + ($pListHash['offset'] / $pListHash['max_records']);

		if ($pListHash["cant"] > ($pListHash['offset'] + $pListHash['max_records']) ) {
			$pListHash['control']['next_offset'] = $pListHash['offset'] + $pListHash['max_records'];
		} else {
			$pListHash['control']['next_offset'] = -1;
		}
		// If offset is > 0 then prev_offset
		if ($pListHash['offset'] > 0) {
			$pListHash['control']['prev_offset'] = $pListHash['offset'] - $pListHash['max_records'];
		} else {
			$pListHash['control']['prev_offset'] = -1;
		}
		$pListHash['control']['offset'] = $pListHash['offset'];
		$pListHash['control']['find'] = $pListHash['find'];
		$pListHash['control']['sort_mode'] = $pListHash['sort_mode'];
		$pListHash['control']['max_records'] = $pListHash['max_records'];
	}

    /**
	* Get a list of users who have created entries in the content table
	*
    * @param array hash of parameters ( content_type_guid will limit list to a single content type
    * @return - none the hash is updated via the reference
	**/
	function getAuthorList( &$pListHash ) {
		$ret = NULL;
		$mid = '';

		$bindVars = array();
		if( !empty( $pListHash['content_type_guid'] ) ) {
			$mid .= ' AND tc.`content_type_guid`=? ';
			$bindVars[] = $pListHash['content_type_guid'];
		}

		$this->prepGetList( $pListHash );
		$query = "SELECT DISTINCT(uu.`user_id`) AS hash_key, uu.`user_id`, SUM( tc.`hits` ) AS `ag_hits`, uu.`login` AS `user`, uu.`real_name`
				  FROM `".BIT_DB_PREFIX."tiki_content` tc INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON( uu.`user_id`=tc.`user_id` )
				  WHERE uu.`user_id` != ".ANONYMOUS_USER_ID." AND tc.`hits` > 0 $mid
				  GROUP BY uu.`user_id`, uu.`login`, uu.`real_name`
				  ORDER BY `ag_hits` DESC";
		if( $result = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] ) ) {
			$ret = $result->GetRows();
		}
		return $ret;
	}

    /**
	 * Get a list of all structures this content is a member of
	 *
	 * @param string Content GUID to limit the list to
	 * @param integer Number of the first record to access ( used to page the list )
	 * @param integer Number of records to return
	 * @param string Name of the field to sort by ( extended by _asc or _desc for sort direction )
	 * @param array List of text elements to filter the results by
	 * @param integer User ID - If set, then only the objcets created by that user will be returned
	 * @return array An array of mInfo type arrays of content objects
	 **/
	function getContentList( $pListHash ) {
		global $gLibertySystem, $gBitSystem, $gBitUser, $gBitSmarty;

		$this->prepGetList( $pListHash );

		if( $pListHash['sort_mode'] == 'size_desc' ) {
			$pListHash['sort_mode'] = 'page_size_desc';
		}

		if( $pListHash['sort_mode'] == 'size_asc' ) {
			$pListHash['sort_mode'] = 'page_size_asc';
		}

		$old_sort_mode = '';

		if (in_array($pListHash['sort_mode'], array(
				'versions_desc',
				'versions_asc',
				'links_asc',
				'links_desc',
				'backlinks_asc',
				'backlinks_desc'
				))) {
			$old_offset = $pListHash['offset'];
			$old_maxRecords = $pListHash['max_records'];
			$old_sort_mode = $pListHash['sort_mode'];
			$pListHash['sort_mode'] = 'modifier_user_desc';
			$pListHash['offset'] = 0;
			$pListHash['max_records'] = -1;
		}

		$bindVars = array();
		$mid = NULL;
		$gateSelect = '';
		$gateFrom = '';

		if( is_array( $pListHash['find'] ) ) { // you can use an array of titles
			$mid = " AND tc.`title` IN ( ".implode( ',',array_fill( 0,count( $pListHash['find'] ),'?' ) ).")";
			$bindVars[] = $pListHash['find'];
		} elseif( !empty($pListHash['find'] ) && is_string( $pListHash['find'] ) ) { // or a string
			$mid = " AND UPPER(tc.`title`) like ? ";
			$bindVars[] = ( '%' . strtoupper( $pListHash['find'] ) . '%' );
		}

		// calendar specific selection method - use timestamps to limit selection
		if( !empty( $pListHash['start'] ) && !empty( $pListHash['stop'] ) ) {
			$mid .= " AND ( tc.`".$pListHash['calendar_sort_mode']."` > ? AND tc.`".$pListHash['calendar_sort_mode']."` < ? ) ";
			$bindVars[] = $pListHash['start'];
			$bindVars[] = $pListHash['stop'];
		}

		if( !empty( $pListHash['user_id'] ) ) {
			$mid .= " AND tc.`user_id` = ? ";
			$bindVars[] = $pListHash['user_id'];
		}

		if( !empty( $pListHash['content_type_guid'] ) && is_string( $pListHash['content_type_guid'] ) ) {
			$mid .= ' AND `content_type_guid`=? ';
			$bindVars[] = $pListHash['content_type_guid'];
		} elseif( !empty( $pListHash['content_type_guid'] ) && is_array( $pListHash['content_type_guid'] ) ) {
			$mid .= " AND tc.`content_type_guid` IN ( ".implode( ',',array_fill ( 0, count( $pListHash['content_type_guid'] ),'?' ) )." )";
			$bindVars = array_merge( $bindVars, $pListHash['content_type_guid'] );
		}

		if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
			$gateSelect .= ' ,ts.`security_id`, ts.`security_description`, ts.`is_private`, ts.`is_hidden`, ts.`access_question`, ts.`access_answer` ';
			$gateFrom .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content_security_map` tcs ON (tc.`content_id`=tcs.`content_id`) LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_security` ts ON (ts.`security_id`=tcs.`security_id` )";
			$mid .= ' AND (tcs.`security_id` IS NULL OR tc.`user_id`=?) ';
			$bindVars[] = $gBitUser->mUserId;
			if( $gBitSystem->isPackageActive( 'fisheye' ) ) {
				// This is really ugly to have in here, and really would be better off somewhere else.
				// However, because of the specific nature of the current implementation of fisheye galleries, I am afraid
				// this is the only place it can go to properly enforce gatekeeper protections. Hopefully a new content generic
				// solution will be available in ReleaseTwo - spiderr
				if( $this->mDb->isAdvancedPostgresEnabled() ) {
// 					$gateFrom .= " LEFT OUTER JOIN  `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` tfgim ON (tfgim.`item_content_id`=tc.`content_id`)";
					$mid .= " AND (SELECT ts.`security_id` FROM connectby('tiki_fisheye_gallery_image_map', 'gallery_content_id', 'item_content_id', tc.`content_id`, 0, '/')  AS t(`cb_gallery_content_id` int, `cb_item_content_id` int, level int, branch text), `".BIT_DB_PREFIX."tiki_content_security_map` tcsm,  `".BIT_DB_PREFIX."tiki_security` ts
							WHERE ts.`security_id`=tcsm.`security_id` AND tcsm.`content_id`=`cb_gallery_content_id` LIMIT 1) IS NULL";
				}
			}
		}

		if( in_array( $pListHash['sort_mode'], array(
				'modifier_user_desc',
				'modifier_user_asc',
				'modifier_real_name_desc',
				'modifier_real_name_asc',
				'creator_user_desc',
				'creator_user_asc',
				'creator_real_name_desc',
				'creator_real_name_asc',
		))) {
			$orderTable = '';
		} else {
			$orderTable = 'tc.';
		}


		// If sort mode is versions then offset is 0, maxRecords is -1 (again) and sort_mode is nil
		// If sort mode is links then offset is 0, maxRecords is -1 (again) and sort_mode is nil
		// If sort mode is backlinks then offset is 0, maxRecords is -1 (again) and sort_mode is nil
		$query = "SELECT uue.`login` AS `modifier_user`, uue.`real_name` AS `modifier_real_name`, uue.`user_id` AS `modifier_user_id`, uuc.`login` AS`creator_user`, uuc.`real_name` AS `creator_real_name`, uuc.`user_id` AS `creator_user_id`, `hits`, tc.`title`, tc.`last_modified`, tc.`content_type_guid`, `ip`, tc.`created`, tc.`content_id` $gateSelect
				  FROM `".BIT_DB_PREFIX."tiki_content` tc $gateFrom, `".BIT_DB_PREFIX."users_users` uue, `".BIT_DB_PREFIX."users_users` uuc
				  WHERE tc.`modifier_user_id`=uue.`user_id` AND tc.`user_id`=uuc.`user_id` $mid
				  ORDER BY ".$orderTable.$this->mDb->convert_sortmode($pListHash['sort_mode']);
		$query_cant = "select count(tc.`content_id`) FROM `".BIT_DB_PREFIX."tiki_content` tc $gateFrom, `".BIT_DB_PREFIX."users_users` uu WHERE uu.`user_id`=tc.`user_id` $mid";
		// previous cant query - updated by xing
		// $query_cant = "select count(*) from `".BIT_DB_PREFIX."tiki_pages` tp INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON (tc.`content_id` = tp.`content_id`) $mid";
		$result = $this->mDb->query($query,$bindVars,$pListHash['max_records'],$pListHash['offset']);
		$cant = $this->mDb->getOne($query_cant,$bindVars);
		$ret = array();
		$contentTypes = $gLibertySystem->mContentTypes;
		while ($res = $result->fetchRow()) {
			$aux = array();
			$aux = $res;
			if( !empty( $contentTypes[$res['content_type_guid']] ) ) {
				// quick alias for code readability
				$type = &$contentTypes[$res['content_type_guid']];
				if( empty( $type['content_object'] ) ) {
					// create *one* object for each object *type* to  call virtual methods.
					include_once( $gBitSystem->mPackages[$type['handler_package']]['path'].$type['handler_file'] );
					$type['content_object'] = new $type['handler_class']();
				}
				$aux['creator'] = (isset( $res['creator_real_name'] ) ? $res['creator_real_name'] : $res['creator_user'] );
				$aux['real_name'] = (isset( $res['creator_real_name'] ) ? $res['creator_real_name'] : $res['creator_user'] );
				$aux['editor'] = (isset( $res['modifier_real_name'] ) ? $res['modifier_real_name'] : $res['modifier_user'] );
				$aux['content_description'] = $type['content_description'];
				$aux['user'] = $res['creator_user'];
				$aux['real_name'] = (isset( $res['creator_real_name'] ) ? $res['creator_real_name'] : $res['creator_user'] );
				$aux['user_id'] = $res['creator_user_id'];
				require_once $gBitSmarty->_get_plugin_filepath( 'modifier', 'bit_long_date' );
				$aux['display_link'] = $type['content_object']->getDisplayLink( $aux['title'], $aux );
				// getDisplayUrl is currently a pure virtual method in LibertyContent, so this cannot be called currently
//	 				$aux['display_url'] = $type['content_object']->getDisplayUrl( $aux['title'], $aux );
				$aux['title'] = $type['content_object']->getTitle( $aux );
				$ret[] = $aux;
			}
		}

		// If sortmode is versions, links or backlinks sort using the ad-hoc function and reduce using old_offse and old_maxRecords
		if ($old_sort_mode == 'versions_asc' && !empty( $ret['versions'] ) ) {
			usort($ret, 'compare_versions');
		}

		if ($old_sort_mode == 'versions_desc' && !empty( $ret['versions'] ) ) {
			usort($ret, 'r_compare_versions');
		}

		if ($old_sort_mode == 'links_desc' && !empty( $ret['links'] ) ) {
			usort($ret, 'compare_links');
		}

		if ($old_sort_mode == 'links_asc' && !empty( $ret['links'] ) ) {
			usort($ret, 'r_compare_links');
		}

		if( $old_sort_mode == 'backlinks_desc' && !empty( $ret['backlinks'] ) ) {
			usort($ret, 'compare_backlinks');
		}

		if( $old_sort_mode == 'backlinks_asc' && !empty( $ret['backlinks'] ) ) {
			usort($ret, 'r_compare_backlinks');
		}

		if (in_array($old_sort_mode, array(
				'versions_desc',
				'versions_asc',
				'links_asc',
				'links_desc',
				'backlinks_asc',
				'backlinks_desc'
				))) {
			$ret = array_slice($ret, $old_offset, $old_maxRecords);
		}

		$retval = array();
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		return $retval;
	}


    /**
	* Get a list of all structures this content is a member of
	**/
	function getStructures() {
		$ret = NULL;
		if( $this->isValid() ) {
			$ret = array();
			$structures_added = array();
			$query = 'SELECT ts.*, tc.`title`, tcr.`title` AS `root_title`
					  FROM `'.BIT_DB_PREFIX.'tiki_content` tc, `'.BIT_DB_PREFIX.'tiki_structures` ts
					  	INNER JOIN  `'.BIT_DB_PREFIX.'tiki_structures` tsr ON( tsr.`structure_id`=ts.`root_structure_id` )
						INNER JOIN `'.BIT_DB_PREFIX.'tiki_content` tcr ON( tsr.`content_id`=tcr.`content_id` )
					  WHERE tc.`content_id`=ts.`content_id` AND ts.`content_id`=?';
			if( $result = $this->mDb->query( $query,array( $this->mContentId ) ) ) {
				while ($res = $result->fetchRow()) {
					$ret[] = $res;
					$result->MoveNext();
				}
			}
		}
		return $ret;
	}

	/**
	 * Process the raw content blob using the speified content GUID processor
	 *
	 * This is the "object like" method. It should be more object like,
	 * but for now, we'll just point to the old lib style "parse_data" - XOXO spiderr
	 * @param string Data to be formated
	 * @param string Format GUID processor to use
	 * @return string Formated data string
	 */
	function parseData( $pData=NULL, $pFormatGuid=NULL ) {
		$ret = &$pData;
		if( empty( $pFormatGuid ) ) {
			$pFormatGuid = isset( $this->mInfo['format_guid'] ) ? $this->mInfo['format_guid'] : NULL;
		}
		if( empty( $pData ) ) {
			$pData = isset( $this->mInfo['data'] ) ? $this->mInfo['data'] : NULL;
		}
		if( $pData && $pFormatGuid ) {
			global $gLibertySystem;
			if( $func = $gLibertySystem->getPluginFunction( $pFormatGuid, 'load_function' ) ) {
				$ret = $func( $pData, $this );
			}
		}
		return $ret;
	}


	/**
	 * Special parsing for multipage articles
	 *
	 * Temporary remove &lt;PRE&gt;&lt;/PRE&gt; secions to protect
	 * from broke &lt;PRE&gt; tags and leave well known &lt;PRE&gt;
	 * behaviour (i.e. type all text inside AS IS w/o
	 * any interpretation)
	 * @param string Data to process
	 * @return string Extracted pages
	 */
	function getNumberOfPages( &$data ) {
		$preparsed = array();

		preg_match_all("/(<[Pp][Rr][Ee]>)((.|\n)*?)(<\/[Pp][Rr][Ee]>)/", $data, $preparse);
		$idx = 0;

		foreach (array_unique($preparse[2])as $pp) {
			$key = md5(BitSystem::genPass());

			$aux["key"] = $key;
			$aux["data"] = $pp;
			$preparsed[] = $aux;
			$data = str_replace($preparse[1][$idx] . $pp . $preparse[4][$idx], $key, $data);
			$idx = $idx + 1;
		}

		$parts = explode(defined('PAGE_SEP') ? PAGE_SEP : "...page...", $data);
		return count($parts);
	}

	/**
	 * Special parsing for a particular page of a multipage article
	 *
	 * Temporary remove &lt;PRE&gt;&lt;/PRE&gt; secions to protect
	 * from broke &lt;PRE&gt; tags and leave well known &lt;PRE&gt;
	 * behaviour (i.e. type all text inside AS IS w/o
	 * any interpretation)
	 * @param string Data to process
	 * @param integer Number of page to extract
	 * @return string Extracted page
	 */
	function getPage( &$data, $i ) {
		$preparsed = array();

		preg_match_all("/(<[Pp][Rr][Ee]>)((.|\n)*?)(<\/[Pp][Rr][Ee]>)/", $data, $preparse);
		$idx = 0;

		foreach (array_unique($preparse[2])as $pp) {
			$key = md5(BitSystem::genPass());

			$aux["key"] = $key;
			$aux["data"] = $pp;
			$preparsed[] = $aux;
			$data = str_replace($preparse[1][$idx] . $pp . $preparse[4][$idx], $key, $data);
			$idx = $idx + 1;
		}

		// Get slides
		$parts = explode(defined('PAGE_SEP') ? PAGE_SEP : "...page...", $data);

		if (substr($parts[$i - 1], 1, 5) == "<br/>")
			$ret = substr($parts[$i - 1], 6);
		else
			$ret = $parts[$i - 1];

		// Replace back <PRE> sections
		foreach ($preparsed as $pp)
			$ret = str_replace($pp["key"], "<pre>" . $pp["data"] . "</pre>", $ret);

		return $ret;
	}


	/**
	 * Check if given url is currently cached locally
	 *
	 * @param string URL to check
	 * @return integer Id of the cached item
	 * @todo LEGACY FUNCTIONS that need to be cleaned / moved / or deprecated & deleted
	 */
	function isCached($url) {
		$query = "select `cache_id`  from `".BIT_DB_PREFIX."tiki_link_cache` where `url`=?";
		// sometimes we can have a cache_id of 0(?!) - seen it with my own eyes, spiderr
		$ret = $this->mDb->getOne($query, array( $url ) );
		return $ret;
	}

	/**
	 * Cache given url
	 * If \c $data present (passed) it is just associated \c $url and \c $data.
	 * Else it will request data for given URL and store it in DB.
	 * Actualy (currently) data may be proviced by TIkiIntegrator only.
	 * @param string URL to cache
	 * @param string Data to be cached
	 * @return bool True if item was successfully cached
	 * @todo LEGACY FUNCTIONS that need to be cleaned / moved / or deprecated & deleted
	 */
	function cacheUrl($url, $data = '') {
		// Avoid caching internal references... (only if $data not present)
		// (cdx) And avoid other protocols than http...
		// 03-Nov-2003, by zaufi
		// preg_match("_^(mailto:|ftp:|gopher:|file:|smb:|news:|telnet:|javascript:|nntp:|nfs:)_",$url)
		// was removed (replaced to explicit http[s]:// detection) bcouse
		// I now (and actualy use in my production Tiki) another bunch of protocols
		// available in my konqueror... (like ldap://, ldaps://, nfs://, fish://...)
		// ... seems like it is better to enum that allowed explicitly than all
		// noncacheable protocols.
		if (((strstr($url, 'tiki-') || strstr($url, 'messu-')) && $data == '')
		 || (substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://'))
			return false;
		// Request data for URL if nothing given in parameters
		// (reuse $data var)
		if ($data == '') $data = tp_http_request($url);

		// If stuff inside [] is *really* malformatted, $data
		// will be empty.  -rlpowell
		if (!$this->isCached( $url ) && $data)
		{	global $gBitSystem;
			$refresh = $gBitSystem->getUTCTime();
			$query = "insert into `".BIT_DB_PREFIX."tiki_link_cache`(`url`,`data`,`refresh`) values(?,?,?)";
			$result = $this->mDb->query($query, array($url,BitDb::db_byte_encode($data),$refresh) );
			return !isset($error);
		}
		else return false;
	}

	/**
	 * Set content related mStructureId
	 *
	 * @param integer Structure ID
	 */
	function setStructure( $pStructureId ) {
		if( $this->verifyId( $pStructureId ) ) {
			$this->mStructureId = $pStructureId;
		}
	}

	/**
	 * Check the number of structures that the content object is being used in
	 *
	 * @param integer Structure ID ( If NULL or not supplied check all structures )
	 * @return integer Number of structures that this content object is located in
	 */
	function isInStructure( $pStructureId=NULL ) {
		if( $this->isValid() ) {
			$whereSql = NULL;
			$bindVars = array( $this->mContentId );
			if( $pStructureId ) {
				array_push( $bindVars, $pStructureId );
				$whereSql = ' AND ts.`root_structure_id`=? ';
			}
			$query  = "SELECT `structure_id` FROM `".BIT_DB_PREFIX."tiki_structures` ts
					   WHERE ts.`content_id`=? $whereSql";
			$cant = $this->mDb->getOne( $query, $bindVars );
			return $cant;
		}
	}

}

?>
