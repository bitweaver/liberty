<?php
/**
* Management of Liberty content
*
* @author   spider <spider@steelsun.com>
* @version  $Revision: 1.2.2.3 $
* @package  Liberty
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
//
// $Id: LibertyContent.php,v 1.2.2.3 2005/06/27 14:13:22 lsces Exp $

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
* @author   spider <spider@steelsun.com>
* @package  Liberty
* @subpackage  LibertyContent
*/
class LibertyContent extends LibertyBase {
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

	function LibertyContent () {
		LibertyBase::LibertyBase();
		$this->mPerms = array();
	}

	function load($pContentId = NULL) {
		// assume a derived class has joined on the tiki_content table, and loaded it's columns already.
		if( !empty( $this->mInfo['content_type_guid'] ) ) {
			global $gLibertySystem, $gBitSystem;
			$this->mInfo['content_type'] = $gLibertySystem->mContentTypes[$this->mInfo['content_type_guid']];
			if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
//				$this->mInfo['perm_level'] = $this->getUserPermissions();
			}
		}

	}

	function verify( &$pParamHash ) {
		global $gLibertySystem;
		if( empty( $pParamHash['user_id'] ) ) {
			global $gBitUser;
			$pParamHash['user_id'] = $gBitUser->getUserId();
		}

		if( empty( $pParamHash['content_id'] ) ) {
			if( empty( $this->mContentId ) ) {
				// These should never be updated, only inserted
				$pParamHash['content_store']['created'] = !empty( $pParamHash['created'] ) ? $pParamHash['created'] : date( "U" );
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
		}

		$pParamHash['content_store']['last_modified'] = !empty( $pParamHash['last_modified'] ) ? $pParamHash['last_modified'] : date("U");

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

	// Things to be stored should be shoved in the array $pParamHash['STORAGE']
	function store( &$pParamHash ) {
		global $gBitSystem;
		global $gLibertySystem;
		if( LibertyContent::verify( $pParamHash ) ) {
			$this->mDb->StartTrans();
			$table = BIT_DB_PREFIX."tiki_content";
			if( empty( $pParamHash['content_id'] ) ) {
				$pParamHash['content_store']['content_id'] = $this->GenID( 'tiki_content_id_seq' );
				$pParamHash['content_id'] = $pParamHash['content_store']['content_id'];
				$result = $this->associateInsert( $table, $pParamHash['content_store'] );
			} else {
				if( !empty( $pParamHash['content_store']['title'] ) && !empty( $this->mInfo['title'] ) ) {
					$renamed = $pParamHash['content_store']['title'] != $this->mInfo['title'];
				}
				$locId = array ( "name" => "content_id", "value" => $pParamHash['content_id'] );
				$result = $this->associateUpdate( $table, $pParamHash['content_store'], $locId );
			}

			// If a content access system is active, let's call it
			if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
				global $gGatekeeper;
				if( !$gGatekeeper->storeSecurity( $pParamHash ) ) {
					$this->mErrors['security'] = $gGatekeeper->mErrors['security'];
				}
			}

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

	function expungeComments() {
		require_once( LIBERTY_PKG_PATH.'LibertyComment.php' );
		// Delete all comments associated with this piece of content
		$query = "SELECT `comment_id` FROM `".BIT_DB_PREFIX."tiki_comments` WHERE `parent_id` = ?";
		$result = $this->query($query, array( $this->mContentId ) );
		$commentIds = $result->getRows();
		foreach ($commentIds as $commentId) {
			$tmpComment = new LibertyComment($commentId);
			$tmpComment->deleteComment();
		}
		return TRUE;
	}

	function expunge() {
		global $gBitSystem;
		$ret = FALSE;
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$this->expungeComments();

			if( $gBitSystem->isPackageActive( 'categories' ) ) {
				require_once( CATEGORIES_PKG_PATH.'categ_lib.php' );
				global $categlib;
				$categlib->uncategorize_object( $this->mType['content_type_guid'], $this->mContentId );
			}

			/* seems out of place - xing
			if( $func = $gLibertySystem->getPluginFunction( $pParamHash['format_guid'], 'expunge_function' ) ) {
				$ret = $func( $this->mContentId );
			}
			*/

			$query = "DELETE FROM `".BIT_DB_PREFIX."tiki_content` WHERE `content_id` = ?";
			$result = $this->query( $query, array( $this->mContentId ) );
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
		$result = $this->query($query, array( $id, $type ));
		// Remove individual permissions for this object if they exist
		$query = "delete from `".BIT_DB_PREFIX."users_objectpermissions` where `object_id`=? and `object_type`=?";
		$result = $this->query($query,array((int)$object,$type));
		return true;
	}
*/
		return $ret;
	}

	function exportHtml( $pData = NULL ) {
		$ret = NULL;
		$ret[] = array(	'type' => $this->mContentTypeGuid,
						'landscape' => FALSE,
						'url' => $this->getDisplayUrl(),
						'content_id' => $this->mContentId,
					);
		return $ret;
	}

	function isValid() {
		return( !empty( $this->mContentId ) && is_numeric( $this->mContentId ) );
	}

	function isOwner() {
		global $gBitUser;
		return( $this->isValid() && !empty( $this->mInfo['user_id'] ) && $this->mInfo['user_id'] == $gBitUser->mUserId );
	}

	function loadPermissions() {
		if( $this->isValid() && empty( $this->mPerms ) && $this->mContentTypeGuid ) {
			//$object_id = md5($object_type . $object_id);
			$query = "select uop.`perm_name`, ug.`group_id`, ug.`group_name`
					  FROM `".BIT_DB_PREFIX."users_objectpermissions` uop
						INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON( uop.`group_id`=ug.`group_id` )
					  WHERE uop.`object_id` = ? AND uop.`object_type` = ?";
			$bindVars = array( $this->mContentId, $this->mContentTypeGuid );
			$this->mPerms = $this->GetAssoc($query, $bindVars);
		}
		return( count( $this->mPerms ) );
	}


    /**
    * Function that determines if this content specified permission for the current gBitUser
    * @return the fully specified path to file to be included
    */
	function hasUserPermission( $pPermName, $pFatalIfFalse=FALSE, $pFatalMessage=NULL  ) {
		global $gBitUser;
		if( !$gBitUser->isRegistered() || !($ret = $this->isOwner()) ) {
			if( !($ret = $gBitUser->isAdmin()) ) {
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


	// get specific permissions for the specified user for this content
	function getUserPermissions( $pUserId ) {
		$ret = array();
		if( $pUserId ) {
			$query = "SELECT uop.`perm_name`, ug.`group_id`, ug.`group_name`, ugm.`user_id`
					  FROM `".BIT_DB_PREFIX."users_objectpermissions` uop
						INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON( uop.`group_id`=ug.`group_id` )
						INNER JOIN `".BIT_DB_PREFIX."users_groups_map` ugm ON( ugm.`group_id`=ug.`group_id` )
					  WHERE ugm.`user_id`=? AND uop.`object_id` = ? AND uop.`object_type` = ? ";
			$bindVars = array( $pUserId, $this->mContentId, $this->mContentTypeGuid );
			$ret = $this->GetAssoc($query, $bindVars);
		}
		return $ret;
	}


	function storePermission( $pGroupId, $perm_name, $object_id=NULL ) {
		if( empty( $object_id ) ) {
			$object_id = $this->mContentId;
		}
		//$object_id = md5($object_type . $object_id);
		$query = "DELETE FROM `".BIT_DB_PREFIX."users_objectpermissions`
				  WHERE `group_id` = ? AND `perm_name` = ? AND `object_id` = ?";
		$result = $this->query($query, array($pGroupId, $perm_name, $object_id), -1, -1);
		$query = "insert into `".BIT_DB_PREFIX."users_objectpermissions`
				  (`group_id`,`object_id`, `object_type`, `perm_name`)
				  VALUES ( ?, ?, ?, ? )";
		$result = $this->query($query, array($pGroupId, $object_id, $this->mContentTypeGuid, $perm_name));
		return true;
	}


	function hasPermission( $pUserId, $object_id, $object_type, $perm_name ) {
		$ret = FALSE;
		$groups = $this->get_user_groups( $pUserId );
		foreach ( $groups as $group_name ) {
			$query = "SELECT count(*)
					  FROM `".BIT_DB_PREFIX."users_objectpermissions`
					  WHERE `group_name` = ? and `object_id` = ? and `object_type` = ? and `perm_name` = ?";
			$bindVars = array($group_name, $object_id, $object_type, $perm_name);
			$result = $this->getOne( $query, $bindVars );
			if ($result>0) {
				$ret = true;
			}
		}
		return $ret;
	}


	function removePermission( $pGroupId, $perm_name ) {
		//$object_id = md5($object_type . $object_id);
		$query = "delete from `".BIT_DB_PREFIX."users_objectpermissions`
			where `group_id` = ? and `object_id` = ?
			and `object_type` = ? and `perm_name` = ?";
		$bindVars = array($pGroupId, $this->mContentId, $this->mContentTypeGuid, $perm_name);
		$result = $this->query($query, $bindVars);
		return true;
	}


	function copyPermissions( $destinationObjectId ) {
		//$object_id = md5($object_type.$object_id);
		$query = "select `perm_name`, `group_name`
			from `".BIT_DB_PREFIX."users_objectpermissions`
			where `object_id` =? and `object_type` = ?";
		$bindVars = array( $this->mContentId, $this->mContentTypeGuid );
		$result = $this->query($query, $bindVars);
		while($res = $result->fetchRow()) {
			$this->storePermission( $res["group_name"], $this->mContentTypeGuid, $res["perm_name"], $destinationObjectId );
		}
		return true;
	}




	function registerContentType( $pContentGuid, $pTypeParams ) {
		global $gLibertySystem;
		$gLibertySystem->registerContentType( $pContentGuid, $pTypeParams );
		$this->mType = $pTypeParams;
	}

	function addHit() {
		global $gBitUser;
		if( $this->mContentId && ($gBitUser->mUserId != $this->mInfo['user_id'] ) ) {
			$query = "update `".BIT_DB_PREFIX."tiki_content` set `hits`=`hits`+1 where `content_id` = ?";
			$result = $this->query( $query, array( $this->mContentId ) );
		}
		return true;
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
		$result = $this->query($query, array( $bindVars ));

		if( $result->numRows() ) {
			$ret = $result->getArray();
		}

		return $ret;
	}


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


	function getContentType() {
		$ret = NULL;
		if( isset( $this->mInfo['content_type_guid'] ) ) {
			$ret = $this->mInfo['content_type_guid'];
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
    * @param pLinkText name of
    * @param pMixed different possibilities depending on derived class
    * @return the link to display the page.
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
    * @param pLinkText name of
    * @param pMixed different possibilities depending on derived class
    * @return the link to display the page.
    */
	function getDisplayUrl( $pLinkText, $pMixed ) {
		print "UNDEFINED PURE VIRTUAL FUNCTION: LibertyContent::getDisplayUrl";
	}

    /**
    * Updates results from any getList function to provide the control set
    * displaying in the smarty template
    * @param pParamHash hash of parameters returned by any getList() function
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
	* Get a list of users this content is a member of
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
		if( $result = $this->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] ) ) {
			$ret = $result->GetRows();
		}
		return $ret;
	}


    /**
	* Get a list of all structures this content is a member of
	**/
	function getContentList( $pContentGuid=NULL, $offset = 0, $maxRecords = -1, $sort_mode = 'title_desc', $find = NULL, $pUserId=NULL ) {
		global $gLibertySystem, $gBitSystem, $gBitUser, $smarty;
		if ($sort_mode == 'size_desc') {
			$sort_mode = 'page_size_desc';
		}

		if ($sort_mode == 'size_asc') {
			$sort_mode = 'page_size_asc';
		}

		$old_sort_mode = '';

		if (in_array($sort_mode, array(
				'versions_desc',
				'versions_asc',
				'links_asc',
				'links_desc',
				'backlinks_asc',
				'backlinks_desc'
				))) {
			$old_offset = $offset;
			$old_maxRecords = $maxRecords;
			$old_sort_mode = $sort_mode;
			$sort_mode = 'modifier_user_desc';
			$offset = 0;
			$maxRecords = -1;
		}

		$bindVars = array();
		$mid = NULL;
		$gateSelect = '';
		$gateFrom = '';

		if (is_array($find)) { // you can use an array of pages
			$mid = " WHERE tc.`title` IN (".implode(',',array_fill(0,count($find),'?')).")";
			$bindVars[] = $find;
		} elseif (!empty($find) && is_string($find)) { // or a string
			$mid = " WHERE UPPER(tc.`title`) like ? ";
			$bindVars[] = ('%' . strtoupper( $find ) . '%');
		}

		if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
			empty( $mid ) ? $mid = ' WHERE ' : $mid .= ' AND ';
			$gateSelect .= ' ,ts.`security_id`, ts.`security_description`, ts.`is_private`, ts.`is_hidden`, ts.`access_question`, ts.`access_answer` ';
			$gateFrom .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content_security_map` tcs ON (tc.`content_id`=tcs.`content_id`) LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_security` ts ON (ts.`security_id`=tcs.`security_id` )";
			$mid .= ' (tcs.`security_id` IS NULL OR tc.`user_id`=?) ';
			$bindVars[] = $gBitUser->mUserId;
		}

		if( !empty( $pUserId ) ) {
			empty( $mid ) ? $mid = ' WHERE ' : $mid .= ' AND ';
			$mid .= " tc.`user_id` = ? ";
			$bindVars[] = $pUserId;
		}

		if( !empty( $pContentGuid ) ) {
			empty( $mid ) ? $mid = ' WHERE ' : $mid .= ' AND ';
			$mid .= ' `content_type_guid`=? ';
			$bindVars[] = $pContentGuid;
		}



		// If sort mode is versions then offset is 0, maxRecords is -1 (again) and sort_mode is nil
		// If sort mode is links then offset is 0, maxRecords is -1 (again) and sort_mode is nil
		// If sort mode is backlinks then offset is 0, maxRecords is -1 (again) and sort_mode is nil
		$query = "SELECT uue.`login` AS `modifier_user`, uue.`real_name` AS `modifier_real_name`, uue.`user_id` AS `modifier_user_id`, uuc.`login` AS`creator_user`, uuc.`real_name` AS `creator_real_name`, uuc.`user_id` AS `creator_user_id`, `hits`, tc.`title`, tc.`last_modified`, tc.`content_type_guid`, `ip`, tc.`content_id` $gateSelect
				  FROM `".BIT_DB_PREFIX."tiki_content` tc $gateFrom, `".BIT_DB_PREFIX."users_users` uue, `".BIT_DB_PREFIX."users_users` uuc
				  ".(!empty( $mid ) ? $mid.' AND ' : ' WHERE ')." tc.`modifier_user_id`=uue.`user_id` AND tc.`user_id`=uuc.`user_id`
				  ORDER BY tc.".$this->convert_sortmode($sort_mode);
		$query_cant = "select count(*) FROM `".BIT_DB_PREFIX."tiki_content` tc $gateFrom $mid";
		// previous cant query - updated by xing
		// $query_cant = "select count(*) from `".BIT_DB_PREFIX."tiki_pages` tp INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON (tc.`content_id` = tp.`content_id`) $mid";
		$result = $this->query($query,$bindVars,$maxRecords,$offset);
		$cant = $this->getOne($query_cant,$bindVars);
		$ret = array();
		$contentTypes = $gLibertySystem->mContentTypes;
		while ($res = $result->fetchRow()) {
			$aux = array();
			$aux = $res;
			if( !empty( $contentTypes[$res['content_type_guid']] ) ) {
				$contentHash = &$contentTypes[$res['content_type_guid']];
				if( empty( $contentHash['content_object'] ) ) {
					include_once( $gBitSystem->mPackages[$contentHash['handler_package']]['path'].$contentHash['handler_file'] );
					$contentHash['content_object'] = new $contentHash['handler_class']();
				}
				$aux['creator'] = (isset( $res['creator_real_name'] ) ? $res['creator_real_name'] : $res['creator_user'] );
				$aux['real_name'] = (isset( $res['creator_real_name'] ) ? $res['creator_real_name'] : $res['creator_user'] );
				$aux['editor'] = (isset( $res['modifier_real_name'] ) ? $res['modifier_real_name'] : $res['modifier_user'] );
				$aux['content_description'] = $contentHash['content_description'];
//WIKI_PKG_URL."index.php?page_d=".$res['page_id'];
				$aux['user'] = $res['creator_user'];
				$aux['real_name'] = (isset( $res['creator_real_name'] ) ? $res['creator_real_name'] : $res['creator_user'] );
				$aux['user_id'] = $res['creator_user_id'];
				require_once $smarty->_get_plugin_filepath( 'modifier', 'bit_long_date' );
				$aux['display_link'] =
					'<a title="'.tra( 'Last modified by' ).': '.$gBitUser->getDisplayName( FALSE, $aux ).' - '.smarty_modifier_bit_long_date( $aux['last_modified'], $smarty ).
					'" href="'.BIT_ROOT_URL.'index.php?content_id='.$aux['content_id'].'">'.
					$contentHash['content_object']->getTitle( $aux ).
					'</a>';
//				$aux['display_url'] = $contentType['content_object']->getDisplayUrl( $aux['title'], $aux );
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
			if( $result = $this->query( $query,array( $this->mContentId ) ) ) {
				while ($res = $result->fetchRow()) {
					$ret[] = $res;
					$result->MoveNext();
				}
			}
		}
		return $ret;
	}





	// This is the "object like" method. It should be more object like,
	// but for now, we'll just point to the old lib style "parse_data" - XOXO spiderr
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


	//Special parsing for multipage articles
	function getNumberOfPages( &$data ) {
		// Temporary remove <PRE></PRE> secions to protect
		// from broke <PRE> tags and leave well known <PRE>
		// behaviour (i.e. type all text inside AS IS w/o
		// any interpretation)
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

	function getPage( &$data, $i ) {
		// Temporary remove <PRE></PRE> secions to protect
		// from broke <PRE> tags and leave well known <PRE>
		// behaviour (i.e. type all text inside AS IS w/o
		// any interpretation)
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



	// ****** LEGACY FUNCTIONS that need to be cleaned / moved / or deprecated & deleted

	function isCached($url) {
		$query = "select `cache_id`  from `".BIT_DB_PREFIX."tiki_link_cache` where `url`=?";
		// sometimes we can have a cache_id of 0(?!) - seen it with my own eyes, spiderr
		$ret = $this->getOne($query, array( $url ) );
		return( isset( $ret ) );
	}

	/**
	 * \brief Cache given url
	 * If \c $data present (passed) it is just associated \c $url and \c $data.
	 * Else it will request data for given URL and store it in DB.
	 * Actualy (currently) data may be proviced by TIkiIntegrator only.
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
		{
			$refresh = date("U");
			$query = "insert into `".BIT_DB_PREFIX."tiki_link_cache`(`url`,`data`,`refresh`) values(?,?,?)";
			$result = $this->query($query, array($url,BitDb::db_byte_encode($data),$refresh) );
			return !isset($error);
		}
		else return false;
	}

	function setStructure( $pStructureId ) {
		if( $this->verifyId( $pStructureId ) ) {
			$this->mStructureId = $pStructureId;
		}
	}

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
			$cant = $this->getOne( $query, $bindVars );
			return $cant;
		}
	}

}

?>
