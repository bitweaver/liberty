<?php
/**
* Management of Liberty content
*
* @package  liberty
* @version  $Header: /cvsroot/bitweaver/_bit_liberty/LibertyContent.php,v 1.212 2007/05/20 16:56:31 nickpalmer Exp $
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
if( !defined( 'BIT_CONTENT_MAX_TITLE_LEN' ) ) {
	define( 'BIT_CONTENT_MAX_TITLE_LEN', 160);
}
define( 'BIT_CONTENT_MAX_LANGUAGE_LEN', 4);
define( 'BIT_CONTENT_MAX_IP_LEN', 39);
define( 'BIT_CONTENT_MAX_FORMAT_GUID_LEN', 16);

if( !defined( 'BIT_CONTENT_DEFAULT_STATUS' ) ) {
	define( 'BIT_CONTENT_DEFAULT_STATUS', 50);
}
//$gBitSystem->getConfig( 'liberty_status_deleted', -999 ) );
//$gBitSystem->getConfig( 'liberty_status_threshold_private', -40 ) );
//$gBitSystem->getConfig( 'liberty_status_threshold_protected', -20 ) );
//$gBitSystem->getConfig( 'liberty_status_threshold_hidden', -10 ) );

/**
 * required setup
 */
require_once( LIBERTY_PKG_PATH.'LibertyBase.php' );

define( 'LIBERTY_SPLIT_REGEX', "/\.{3}split\.{3}\s*/i" );

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
	var $mPerms = array();
	/**
	* Preferences hash specific to this LibertyContent object - accessed via getPreference/storePreference
	* @private
	*/
	var $mPrefs = array();
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
		$this->mPrefs = NULL; // init to NULL so getPreference can determine if a load is necessary
		$this->mPerms = array();
		if( empty( $this->mAdminContentPerm ) ) {
			$this->mAdminContentPerm = 'p_admin_content';
		}
	}

	/**
	* Assume a derived class has joined on the liberty_content table, and loaded it's columns already.
	*/
	function load($pContentId = NULL) {
		if( !empty( $this->mInfo['content_type_guid'] ) ) {
			global $gLibertySystem, $gBitSystem, $gBitUser;
			$this->loadPreferences();
			$this->loadPermissions();
			$this->mInfo['content_type'] = $gLibertySystem->mContentTypes[$this->mInfo['content_type_guid']];
			$this->invokeServices('content_load_function', $this);
		}
	}

	/**
	* Verify the core class data required to update the liberty_content table entries
	*
	* Verify will build an array [content_store] with all of the required values
	* and populate it with the relevent data to create/update the liberty_content
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
	* last_hit <br>
	* event_time <br>
	* hits <br>
	* lang_code <br>
	* title <br>
	* ip <br>
	* edit <br>
	* <br>
	* @return integer Count of the number of errors ( 0 for success ) <br>
	* [pParamHash] will be extended to include array [content_store] populated
	* with the require values for LibertyContent::store()
	*/
	function verify( &$pParamHash ) {
		global $gLibertySystem, $gBitSystem, $gBitLanguage, $gBitUser;

		// It is possible a derived class set this to something different
		if( empty( $pParamHash['content_type_guid'] ) ) {
			$pParamHash['content_type_guid'] = $this->mContentTypeGuid;
		}

		if( empty( $pParamHash['user_id'] ) ) {
			$pParamHash['user_id'] = $gBitUser->getUserId();
		}

		if( !@$this->verifyId( $pParamHash['content_id'] ) ) {
			if( !@$this->verifyId( $this->mContentId ) ) {
				// These should never be updated, only inserted
				$pParamHash['content_store']['created'] = !empty( $pParamHash['created'] ) ? $pParamHash['created'] : $gBitSystem->getUTCTime();
				// This may get overridden by owner set
				$pParamHash['content_store']['user_id'] = $pParamHash['user_id'];
				// Set a default status when creating
				// This may get overwritten below
				$pParamHash['content_store']['content_status_id'] = $gBitSystem->getConfig('liberty_default_status', BIT_CONTENT_DEFAULT_STATUS);
			} else {
				$pParamHash['content_id'] = $this->mContentId;
			}
		}
				
		// Are we allowed to override owner?
		if ($gBitSystem->isFeatureActive('liberty_allow_change_owner') && $gBitUser->hasPermission('p_liberty_edit_content_owner')) {
			// If an owner is being set override user_id
			if (!empty($pParamHash['owner_id']) && !empty($pParamHash['current_owner_id']) && $pParamHash['owner_id'] != $pParamHash['current_owner_id']) {
				$pParamHash['content_store']['user_id'] = $pParamHash['owner_id'];
			}
		}

		// Do we need to change the status
		if ($gBitSystem->isFeatureActive('liberty_display_status') && ($gBitUser->hasPermission('p_liberty_edit_content_status') || $gBitUser->hasPermission('p_liberty_edit_all_status'))) {
		  	$allStatus = $this->getContentStatus();
			if (!empty($pParamHash['content_status_id'])) {
				if (empty($allStatus[$pParamHash['content_status_id']])) {
					$this->mError['content_status_id'] = "No such status ID or permission denied.";
				}
				else {
					$pParamHash['content_store']['content_status_id'] = $pParamHash['content_status_id'];
				}
			}
		}

		$pParamHash['field_changed'] = empty( $pParamHash['content_id'] )
					|| (!empty($this->mInfo["data"]) && !empty($pParamHash["edit"]) && (md5($this->mInfo["data"]) != md5($pParamHash["edit"])))
					|| (!empty($pParamHash["title"]) && !empty($this->mInfo["title"]) && (md5($this->mInfo["title"]) != md5($pParamHash["title"])))
					|| (!empty($pParamHash["edit_comment"]) && !empty($this->mInfo["edit_comment"]) && (md5($this->mInfo["edit_comment"]) != md5($pParamHash["edit_comment"])));
		// check some lengths, if too long, then truncate
		if( !empty( $pParamHash['title'] ) ) {
			$pParamHash['content_store']['title'] = substr( $pParamHash['title'], 0, BIT_CONTENT_MAX_TITLE_LEN );
		} elseif( isset( $pParamHash['title'] ) ) {
			$pParamHash['content_store']['title'] = NULL;
		}

		// get the lang code from $_REQUEST if it's not set
		if( !empty( $pParamHash['lang_code'] ) && in_array( $pParamHash['lang_code'], array_keys( $gBitLanguage->mLanguageList ) ) ) {
			$pParamHash['content_store']['lang_code'] = $pParamHash['lang_code'];
		} elseif( !empty( $_REQUEST['i18n']['lang_code'] ) && in_array( $_REQUEST['i18n']['lang_code'], array_keys( $gBitLanguage->mLanguageList ) ) ) {
			$pParamHash['content_store']['lang_code'] = $_REQUEST['i18n']['lang_code'];
		}

		$pParamHash['content_store']['last_modified'] = !empty( $pParamHash['last_modified'] ) ? $pParamHash['last_modified'] : $gBitSystem->getUTCTime();
		$pParamHash['content_store']['event_time'] = !empty( $pParamHash['event_time'] ) ? $pParamHash['event_time'] : $pParamHash['content_store']['last_modified'];

		// WARNING: Assume WIKI if t
		if( !empty( $pParamHash['content_id'] ) ) {
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

		if( !@$this->verifyId( $pParamHash['modifier_user_id'] ) ) {
			$pParamHash['modifier_user_id'] = $gBitUser->getUserId();
		}
		$pParamHash['content_store']['modifier_user_id'] = $pParamHash['modifier_user_id'];

		if( empty( $pParamHash['format_guid'] ) ) {
			if ( $current_default_format_guid = $gBitSystem->getConfig( 'default_format' ) ) {
				$pParamHash['format_guid'] = $current_default_format_guid;
			} else {
				$pParamHash['format_guid'] = 'tikiwiki';
			}
		}
		$pParamHash['content_store']['format_guid'] = $pParamHash['format_guid'];

		if( !empty( $pParamHash['hits'] ) ) {
			$pParamHash['content_store']['hits'] = $pParamHash['hits'] + 1;
			$pParamHash['content_store']['last_hit'] = $gBitSystem->getUTCTime();
		}

		if( !empty( $pParamHash['edit'] ) && $func = $gLibertySystem->getPluginFunction( $pParamHash['format_guid'], 'verify_function' ) ) {
			$error = $func( $pParamHash );
			if( $error ) {
				$this->mErrors['format'] = $error;
			}
		}

		if( empty( $pParamHash['edit'] ) ) {
			// someone has deleted the data entirely - common for fisheye
			$pParamHash['content_store']['data'] = NULL;
		}
		$pParamHash['content_store']['format_guid'] = $pParamHash['format_guid'];

		if( !@BitBase::verifyId( $this->mInfo['version'] ) ) {
			$pParamHash['content_store']['version'] = 1;
		} else {
			$pParamHash['content_store']['version'] = $this->mInfo['version'] + 1;
		}

		// search related stuff
		if ( ( !(isset($this->mInfo['no_index']) and $this->mInfo['no_index'] == true ) ) and !isset($this->mInfo['index_data']) ) {
			$this->mInfo['index_data'] = "";
			if ( isset($pParamHash["title"]) )       $this->mInfo['index_data'] .= $pParamHash["title"] . ' ';
			if ( isset($pParamHash["author_name"]) ) $this->mInfo['index_data'] .= $pParamHash["author_name"] . ' ';
			if ( isset($pParamHash["edit"]) )        $this->mInfo['index_data'] .= $pParamHash["edit"];
		}

		// content preferences
		$prefs = array();
		if( $gBitUser->hasPermission( 'p_liberty_enter_html' ) ) {
			$prefs[] = 'content_enter_html';
		}

		foreach( $prefs as $pref ) {
			if( !empty( $pParamHash['preferences'][$pref] ) ) {
				$pParamHash['preferences_store'][$pref] = $pParamHash['preferences'][$pref];
			} else {
				$pParamHash['preferences_store'][$pref] = NULL;
			}
		}

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
			$table = BIT_DB_PREFIX."liberty_content";
			if( !@$this->verifyId( $pParamHash['content_id'] ) ) {
				$pParamHash['content_store']['content_id'] = $this->mDb->GenID( 'liberty_content_id_seq' );
				$pParamHash['content_id'] = $pParamHash['content_store']['content_id'];
				// make sure some variables are stuff in case services need getObjectType, mContentId, etc...
				$this->mInfo['content_type_guid'] = $pParamHash['content_type_guid'];
				$this->mContentId = $pParamHash['content_store']['content_id'];
				$result = $this->mDb->associateInsert( $table, $pParamHash['content_store'] );

				$this->mLogs['content_store'] = "Created";
			} else {
				if( !empty( $pParamHash['content_store']['title'] ) && !empty( $this->mInfo['title'] ) ) {
					$renamed = $pParamHash['content_store']['title'] != $this->mInfo['title'];
				}
				$result = $this->mDb->associateUpdate( $table, $pParamHash['content_store'], array("content_id" => $pParamHash['content_id'] ) );
				$this->mLogs['content_store'] = "Updated";
			}

			if( !empty( $pParamHash['force_history'] ) || ( empty( $pParamHash['minor'] ) && $this->getField( 'version' ) && $pParamHash['field_changed'] )) {
				if( empty( $pParamHash['has_no_history'] ) ) {
					$this->storeHistory();
				}
				$action = "Created";
				$mailEvents = 'wiki_page_changes';
			}

			$this->invokeServices( 'content_store_function', $pParamHash );

			// Call the formatter's save
			if( !empty( $pParamHash['edit'] ) ) {
				if( $func = $gLibertySystem->getPluginFunction( $pParamHash['format_guid'], 'store_function' ) ) {
					$ret = $func( $pParamHash );
				}
			}
			LibertyContent::expungeCacheFile( $pParamHash['content_id'] );

			// If we renamed the page, we need to update the backlinks
			if( !empty( $renamed ) && $func = $gLibertySystem->getPluginFunction( $pParamHash['format_guid'], 'rename_function' ) ) {
				$ret = $func( $this->mContentId, $this->mInfo['title'], $pParamHash['content_store']['title'], $this );
				$this->mLogs['rename_page'] = "Renamed from {$this->mInfo['title']} to {$pParamHash['content_store']['title']}.";
			}


			// store content preferences
			if( @is_array( $pParamHash['preferences_store'] ) ) {
				foreach( $pParamHash['preferences_store'] as $pref => $value ) {
					$this->storePreference( $pref, $value );
				}
			}

			// store hits and last hit
			if( !empty( $pParamHash['content_store']['hits'] )  ) {
				$this->setHits($pParamHash['content_store']['hits'], $pParamHash['content_store']['last_hit']);
			}

			// store any messages in the logs
			$this->storeActionLog( $pParamHash );

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
		$query = "SELECT `comment_id` FROM `".BIT_DB_PREFIX."liberty_comments` WHERE `parent_id` = ?";
		if( $commentIds = $this->mDb->getCol($query, array( $this->mContentId ) ) ) {
			foreach ($commentIds as $commentId) {
				$tmpComment = new LibertyComment($commentId);
				$tmpComment->deleteComment();
			}
		}
		return TRUE;
	}

	/**
	* Delete content object and all related records
	*/
	function expunge() {
		global $gBitSystem, $gLibertySystem;
		$ret = FALSE;
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$this->expungeComments();

			$this->invokeServices( 'content_expunge_function', $this );
			if( $func = $gLibertySystem->getPluginFunction( $this->getField( 'format_guid' ), 'expunge_function' ) ) {
				$func( $this->mContentId );
			}
			LibertyContent::expungeCacheFile( $this->mContentId );


			// remove entries in the history
			$this->expungeVersion();

			// Remove individual permissions for this object if they exist
			$query = "delete from `".BIT_DB_PREFIX."liberty_content_permissions` where `content_id`=?";
			$result = $this->mDb->query( $query, array( $this->mContentId ) );

			// Remove structures
			// it's not this simple. what about orphans? needs real work. :( xoxo - spider
//			$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_structures` WHERE `content_id` = ?";
//			$result = $this->mDb->query( $query, array( $this->mContentId ) );

			// Remove hits
			$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_content_hits` WHERE `content_id` = ?";
			$result = $this->mDb->query( $query, array( $this->mContentId ) );

			// Remove content preferences
			$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_content_prefs` WHERE `content_id` = ?";
			$result = $this->mDb->query( $query, array( $this->mContentId ) );

			// Remove content links
			$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_content_links` WHERE `to_content_id` = ? or `from_content_id` = ?";
			$result = $this->mDb->query( $query, array( $this->mContentId, $this->mContentId ) );

			// Remove content
			$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_content` WHERE `content_id` = ?";
			$result = $this->mDb->query( $query, array( $this->mContentId ) );

			$this->mLogs['content_expunge'] = "Deleted";
			$this->storeActionLog();

			$this->mDb->CompleteTrans();
			$ret = TRUE;
		}
		return $ret;
	}

	// *********  History functions for the wiki ********** //
	function storeHistory() {
		$ret = FALSE;
		if( $this->isValid() ) {
			$query = "insert into `".BIT_DB_PREFIX."liberty_content_history`( `content_id`, `version`, `last_modified`, `user_id`, `ip`, `history_comment`, `data`, `description`, `format_guid`) values(?,?,?,?,?,?,?,?,?)";
			$result = $this->mDb->query( $query, array( $this->mContentId, (int)$this->getField( 'version' ), (int)$this->getField( 'last_modified' ) , $this->getField( 'modifier_user_id' ), $this->getField( 'ip' ), $this->getField( 'edit_comment' ), $this->getField( 'data' ), $this->getField( 'description' ), $this->getField( 'format_guid' ) ) );
			$ret = TRUE;
		}
		return( $ret );
	}
	
	/**
	* Get count of the number of historic records for the page
	* @return count
	*/
	function getHistoryCount() {
		$ret = NULL;
		if( $this->isValid() ) {
			$query = "SELECT COUNT(*) AS `hcount`
					FROM `".BIT_DB_PREFIX."liberty_content_history`
					WHERE `content_id` = ?";
			$rs = $this->mDb->query($query, array($this->mContentId));
			$ret = $rs->fields['hcount'];
		}
		return $ret;
	}

	/**
	* Get complete set of historical data in order to display a given wiki page version
	* @param pExistsHash the hash that was returned by LibertyContent::pageExists
	* @return array of mInfo data
	*/
	function getHistory( $pVersion=NULL, $pUserId=NULL, $pOffset = 0, $max_records = -1 ) {
		$ret = NULL;
		$cant = 0;
		if( $this->isValid() ) {
			global $gBitSystem;

			$selectSql = '';
			$joinSql = '';
			$whereSql = '';
			$bindVars = array();
			$this->getServicesSql( 'content_list_history_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

			$versionSql = '';
			if( @BitBase::verifyId( $pUserId ) ) {
				$bindVars[] = $pUserId;
				$whereSql .= ' th.`user_id`=? ';
			} else {
				$bindVars[] = $this->mContentId;
				$whereSql .= ' th.`content_id`=? ';
			}
			if( !empty( $pVersion ) ) {
				array_push( $bindVars, $pVersion );
				$versionSql = ' AND th.`version`=? ';
			}
			$query = "SELECT lc.`title`, th.*,
				uue.`login` AS modifier_user, uue.`real_name` AS modifier_real_name,
				uuc.`login` AS creator_user, uuc.`real_name` AS creator_real_name
				$selectSql
				FROM `".BIT_DB_PREFIX."liberty_content_history` th INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lc.`content_id` = th.`content_id`)
				LEFT JOIN `".BIT_DB_PREFIX."users_users` uue ON (uue.`user_id` = th.`user_id`)
				LEFT JOIN `".BIT_DB_PREFIX."users_users` uuc ON (uuc.`user_id` = lc.`user_id`)
				$joinSql
				WHERE $whereSql $versionSql order by th.`version` desc";

			$result = $this->mDb->query( $query, $bindVars, $max_records, $pOffset );
			$ret = array();
			while( !$result->EOF ) {
				$aux = $result->fields;
				$aux['creator'] = (isset( $aux['creator_real_name'] ) ? $aux['creator_real_name'] : $aux['creator_user'] );
				$aux['editor'] = (isset( $aux['modifier_real_name'] ) ? $aux['modifier_real_name'] : $aux['modifier_user'] );
				array_push( $ret, $aux );
				$result->MoveNext();
			}


			$query = "SELECT COUNT(*) AS `hcount`
				FROM `".BIT_DB_PREFIX."liberty_content_history` th 
				WHERE $whereSql $versionSql";
			$query = "SELECT COUNT(*) AS `hcount`
					FROM `".BIT_DB_PREFIX."liberty_content_history`
					WHERE `content_id` = ?";
			$rs = $this->mDb->query($query, array($this->mContentId));
			$cant = $rs->fields['hcount'];
			

		}
		$ret["cant"] = $cant;
		$ret["max_records"] = $max_records;
		$ret["offset"] = $pOffset;
// code needs to be updated with pListHash
//		$ret["find"] = $find;
//		$ret["sort_mode"] = $sort_mode;
		return $ret;
	}

	/**
	 * Removes last version of the page (from pages) if theres some
	 * version in the liberty_content_history then the last version becomes the actual version
	 */
	function removeLastVersion( $comment = '' ) {
		if( $this->isValid() ) {
			global $gBitSystem;
			$this->expungeCacheFile($this->mContentId);
			$query = "select * from `".BIT_DB_PREFIX."liberty_content_history` where `content_id`=? order by ".$this->mDb->convertSortmode("last_modified_desc");
			$result = $this->mDb->query($query, array( $this->mContentId ) );
			if ($result->numRows()) {
				// We have a version
				$res = $result->fetchRow();
				$this->rollbackVersion( $res["version"] );
				$this->expungeVersion( $res["version"] );
			} else {
				$this->remove_all_versions($page);
			}
			$action = "Removed last version";
			$t = $gBitSystem->getUTCTime();
			$query = "insert into `".BIT_DB_PREFIX."liberty_action_log`( `log_message`, `content_id`, `last_modified`, `user_id`, `ip`, `error_message`) values( ?, ?, ?, ?, ?, ?)";
			$result = $this->mDb->query($query, array( $action, $this->mContentId, $t, ROOT_USER_ID, $_SERVER["REMOTE_ADDR"], $comment ) );
		}
	}

	/**
	 * Roll back to a specific version of a page
	 * @param pVersion Version number to roll back to
	 * @param comment Comment text to be added to the action log
	 * @return TRUE if completed successfully
	 */
	function rollbackVersion( $pVersion, $comment = '' ) {
		$ret = FALSE;
		if( $this->isValid() ) {
			global $gBitUser,$gBitSystem;
			$this->mDb->StartTrans();
			// JHT - cache invalidation appears to be handled by store function - so don't need to do it here
			$query = "select *, `user_id` AS modifier_user_id, `data` AS `edit` from `".BIT_DB_PREFIX."liberty_content_history` where `content_id`=? and `version`=?";
			if( $res = $this->mDb->getRow($query,array( $this->mContentId, $pVersion ) ) ) {
				$res['edit_comment'] = 'Rollback to version '.$pVersion.' by '.$gBitUser->getDisplayName();
				if (!empty($comment)) {
					$res['edit_comment'] .=" $comment";
				}
				// JHT 2005-06-19_15:22:18
				// set ['force_history'] to
				// make sure we don't destory current content without leaving a copy in history
				// if rollback can destroy the current page version, it can be used
				// maliciously
				$res['force_history'] = 1;
				// JHT 2005-10-16_22:21:10
				// title must be set or store fails
				// we use current page name
				$res['title'] = $this->getTitle();
				if( $this->store( $res ) ) {
					$ret = TRUE;
				}
//vd( $this->mErrors );
				
				$this->mDb->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
			}
		}
		return $ret;
	}

	/**
	 * Removes a specific version of a page
	 * @param pVersion Version number to roll back to
	 * @param comment Comment text to be added to the action log
	 * @return TRUE if completed successfully
	 */
	function expungeVersion( $pVersion=NULL, $comment = '' ) {
		$ret = FALSE;
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$bindVars = array( $this->mContentId );
			$versionSql = '';
			if( $pVersion ) {
				$versionSql = " and `version`=? ";
				array_push( $bindVars, $pVersion );
			}
			$hasRows = $this->mDb->getOne( "SELECT COUNT(`version`) FROM `".BIT_DB_PREFIX."liberty_content_history` WHERE `content_id`=? $versionSql ", $bindVars );
			$query = "delete from `".BIT_DB_PREFIX."liberty_content_history` where `content_id`=? $versionSql ";
			$result = $this->mDb->query( $query, $bindVars );
			if( $hasRows ) {
				global $gBitSystem;
				$action = "Removed version $pVersion";
				$t = $gBitSystem->getUTCTime();
				$query = "insert into `".BIT_DB_PREFIX."liberty_action_log`(`log_message`,`content_id`,`last_modified`,`user_id`,`ip`,`error_message`) values(?,?,?,?,?,?)";
				$result = $this->mDb->query($query,array($action,$this->mContentId,$t,ROOT_USER_ID,$_SERVER["REMOTE_ADDR"],$comment));
				$ret = TRUE;
			}
			$this->mDb->CompleteTrans();
		}
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
		return( BitBase::verifyId( $this->mContentId ) );
	}

	/**
	* Check permissions to establish if user has permission to view the object
	* Should be provided by the decendent package
	*/
	function isViewable($pContentId = NULL) {
		return( true );
	}

	/**
	* Check permissions to establish if user has permission to edit the object
	* Should be provided by the decendent package
	*/
	function isEditable($pContentId = NULL) {
		return( false );
	}

	/**
	* Check permissions to establish if user has permission to admin the object
	* That would include permission to delete an object or change it's permissions
	* Should be provided by the decendent package
	*/
	function isAdminable($pContentId = NULL) {
		return( false );
	}

	/**
	* Check user_id to establish if the object that has been loaded was created by the current user
	* @param $pParamHash optionally pass in the hash to check against
	* @return TRUE if user owns the content
	*/
	function isOwner( $pParamHash = NULL ) {
		global $gBitUser;
		if( @BitBase::verifyId( $pParamHash['user_id'] ) ) {
			$user_id = $pParamHash['user_id'];
		} elseif( $this->isValid() && @$this->verifyId( $this->mInfo['user_id'] ) ) {
			$user_id = $this->mInfo['user_id'];
		}
		return( @BitBase::verifyId( $user_id ) && $user_id == $gBitUser->mUserId );
	}


	/**
	* Check user_id to establish if the object that has been loaded was created by the current user
	*/
	function isContentType( $pContentGuid ) {
		global $gBitUser;
		return( $this->isValid() && !empty( $this->mInfo['content_type_guid'] ) && $this->mInfo['content_type_guid'] == $pContentGuid );
	}

	/**
	* Check permissions to establish if user has permission to access the object
	*/
	function verifyAccessControl() {
		if( $this->isValid() ) {
			$this->invokeServices( 'content_verify_access' );
		}
	}

	/**
	* Set up access to services used by the object
	*/
	function invokeServices( $pServiceFunction, $pFunctionParam=NULL ) {
		global $gLibertySystem;
		$errors = array();
		// Invoke any services store functions such as categorization or access control
		if( $serviceFunctions = $gLibertySystem->getServiceValues( $pServiceFunction ) ) {
			foreach ( $serviceFunctions as $func ) {
				if( function_exists( $func ) ) {
					if( $errors = $func( $this, $pFunctionParam ) ) {
						$this->mErrors = array_merge( $this->mErrors, $errors );
					}
				}
			}
		}
		return $errors;
	}

	/**
	* Set up SQL strings for services used by the object
	*/
	function getServicesSql( $pServiceFunction, &$pSelectSql, &$pJoinSql, &$pWhereSql, &$pBindVars, $pObject = NULL, $pParamHash = NULL) {
		global $gLibertySystem;
		if( $loadFuncs = $gLibertySystem->getServiceValues( $pServiceFunction ) ) {
			foreach( $loadFuncs as $func ) {
				if( function_exists( $func ) ) {
					if( !empty( $pObject ) && is_object( $pObject ) ) {
						$loadHash = $func( $pObject, $pParamHash );
					} else {
						$loadHash = $func( $this, $pParamHash );
					}
					if( !empty( $loadHash['select_sql'] ) ) {
						$pSelectSql .= $loadHash['select_sql'];
					}
					if( !empty( $loadHash['join_sql'] ) ) {
						$pJoinSql .= $loadHash['join_sql'];
					}
					if( !empty( $loadHash['where_sql'] ) ) {
						$pWhereSql .= $loadHash['where_sql'];
					}
					if( !empty( $loadHash['bind_vars'] ) ) {
						if ( is_array( $pBindVars ) ) {
							$pBindVars = array_merge( $pBindVars, $loadHash['bind_vars'] );
						} else {
							$pBindVars = $loadHash['bind_vars'];
						}
					}
				}
			}
		}
	}

	// -------------------------------- Content Permission Funtions

	function getContentPermissionsSql( $pPermName, &$pSelectSql, &$pJoinSql, &$pWhereSql, &$pBindVars ) {
		global $gBitUser;
		$pJoinSql .= "
			LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_permissions` lcperm ON (lc.`content_id`=lcperm.`content_id`)
			LEFT OUTER JOIN `".BIT_DB_PREFIX."users_groups_map` ugm ON (ugm.`group_id`=lcperm.`group_id`) ";
 		$pWhereSql .= " OR (lcperm.perm_name=? AND (ugm.user_id=? OR ugm.user_id=?)) ";
 		$pBindVars[] = $pPermName;
 		$pBindVars[] = $gBitUser->mUserId;
 		$pBindVars[] = ANONYMOUS_USER_ID;
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
	function checkContentPermission( $pParamHash ) {
		global $gBitUser;

		$ret = FALSE;

		if( !empty( $this->mAdminContentPerm ) && $gBitUser->hasPermission( $this->mAdminContentPerm ) ) {
			// content admin shortcut
			$ret = TRUE;
		} else {
			$selectSql = ''; $joinSql = ''; $whereSql = '';
			$bindVars = array();

			if( !empty( $pParamHash['content_id'] ) ) {
				$bindVars[] = $pParamHash['content_id'];
			} elseif( $this->isValid() ) {
				$bindVars[] = $this->mContentId;
			}

			if( @$this->verifyId( $pParamHash['user_id'] ) ) {
				$whereSql .= " AND lc.`user_id` = ? ";
				$bindVars[] = $pParamHash['user_id'];
			}

			if( !empty( $pParamHash['group_id'] ) ) {
				$whereSql .= " AND lcperm.`group_id` = ? ";
				$bindVars[] = $pParamHash['group_id'];
			}

			$permWhereSql = '';
			$this->getContentPermissionsSql( $pParamHash['perm_name'], $selectSql, $joinSql, $permWhereSql, $bindVars );

			if( !empty( $whereSql ) ) {
				$whereSql = preg_replace( '/^[\s]*AND/', '  ', $whereSql );
			}

			$query = "SELECT COUNT(*)
					  FROM `liberty_content` lc  $joinSql
					  WHERE lc.`content_id`=? AND ( $whereSql $permWhereSql ) ";
			$ret = $this->mDb->getOne( $query, $bindVars );
		}
		return( !empty( $ret ) );
	}


/*
	function assign_object_permission($pGroupId, $object_id, $object_type, $perm_name) {
		//$object_id = md5($object_type . $object_id);
		$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_content_permissions`
				  WHERE `group_id` = ? AND `perm_name` = ? AND `content_id` = ?";
		$result = $this->mDb->query($query, array($pGroupId, $perm_name, $object_id), -1, -1);
		$query = "insert into `".BIT_DB_PREFIX."liberty_content_permissions`
				  (`group_id`,`content_id`, `object_type`, `perm_name`)
				  VALUES ( ?, ?, ?, ? )";
		$result = $this->mDb->query($query, array($pGroupId, $object_id,$object_type, $perm_name));
		return true;
	}

	function object_has_permission( $pUserId = NULL, $object_id, $object_type, $perm_name, $pForceRefresh = FALSE ) {
		$ret = FALSE;
		$groups = $this->getGroups($pUserId, $pForceRefresh);

		foreach ( $groups as $groupId => $group_name ) {
			$query = "SELECT count(*)
					  FROM `".BIT_DB_PREFIX."liberty_content_permissions`
					  WHERE `group_id` = ? and `content_id` = ? and `object_type` = ? and `perm_name` = ?";
					  //pvd($query);pvd($sd="groupid: $groupId | object_id: $object_id | object_type: $object_type | permname: $perm_name");
			$bindvars = array($groupId, $object_id, $object_type, $perm_name);
			$result = $this->mDb->getOne( $query, $bindvars );
			if ($result>0) {
				$ret = true;
			}
		}
		return $ret;
	}


	function remove_object_permission($pGroupId, $object_id, $object_type, $perm_name) {
		//$object_id = md5($object_type . $object_id);
		$query = "delete from `".BIT_DB_PREFIX."liberty_content_permissions`
			where `group_id` = ? and `content_id` = ?
			and `object_type` = ? and `perm_name` = ?";
		$bindvars = array($pGroupId, $object_id, $object_type, $perm_name);
		$result = $this->mDb->query($query, $bindvars);
		return true;
	}


	function copy_object_permissions($object_id,$destinationObjectId,$object_type) {
		//$object_id = md5($object_type.$object_id);
		$query = "select `perm_name`, `group_name`
			from `".BIT_DB_PREFIX."liberty_content_permissions`
			where `content_id` =? and
			`object_type` = ?";
		$bindvars = array($object_id, $object_type);
		$result = $this->mDb->query($query, $bindvars);
		while($res = $result->fetchRow()) {
			$this->assign_object_permission($res["group_name"],$destinationObjectId,$object_type,$res["perm_name"]);
		}
		return true;
	}


	function get_object_permissions($object_id, $object_type) {
		//$object_id = md5($object_type . $object_id);
		$query = "select ug.`group_id`, ug.`group_name`, lcperm.`perm_name`
				  FROM `".BIT_DB_PREFIX."liberty_content_permissions` lcperm
					INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON( lcperm.`group_id`=ug.`group_id` )
				  WHERE lcperm.`content_id` = ? AND lcperm.`object_type` = ?";
		$bindvars = array($object_id, $object_type);
		$result = $this->mDb->query($query, $bindvars);
		$ret = array();
		while ($res = $result->fetchRow()) {
			$ret[] = $res;
		}
		return $ret;
	}


	function object_has_one_permission( $pContentId ) {
		$ret = NULL;
		if( @$this->verifyId( $pContentId )  ) {
			//$object_id = md5($object_type . $object_id);
			$query = "select count(*) from `".BIT_DB_PREFIX."liberty_content_permissions` where `content_id`=?";
			$ret = $this->mDb->getOne($query, array( $pContentId ));
		}
		return $ret;
	}
*/


	/**
	 * Load all permissions assigned to a given object. This is not for general consumption.
	 * This funtions sole purpose is for displaying purposes. if you want to get all permissions
	 * assigned to a given object use LibertyContent::loadPermissions();
	 * 
	 * @access public
	 */
	function loadAllObjectPermissions( $pParamHash = NULL ) {
		global $gBitUser;
		$ret = FALSE;
		if( empty( $pParamHash['sort_mode'] )) {
			$pParamHash['sort_mode'] = 'group_name_asc';
		}

		LibertyContent::prepGetList( $pParamHash );
		if( $this->isValid() && $this->mContentTypeGuid ) {
			$query = "
				SELECT lcperm.`perm_name`, ug.`group_id`, ug.`group_name`, up.`perm_desc`
				FROM `".BIT_DB_PREFIX."liberty_content_permissions` lcperm
					INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON( lcperm.`group_id`=ug.`group_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."users_permissions` up ON( up.`perm_name`=lcperm.`perm_name` )
				WHERE lcperm.`content_id` = ?
				ORDER BY ".$this->mDb->convertSortmode( $pParamHash['sort_mode'] );
			$bindVars = array( $this->mContentId );
			$ret = $this->mDb->getAll( $query, $bindVars );
		}
		return $ret;
	}

	/**
	 * Check permissions for the object that has been loaded against the permission database
	 * 
	 * @access public
	 * @return TRUE if permissions were inserted into $this->mPerms
	 */
	function loadPermissions( $pForce = FALSE ) {
		if( $pForce ) {
			$this->mPerms = array();
		}
		if( $this->isValid() && empty( $this->mPerms ) && $this->mContentTypeGuid ) {
			$query = "
				SELECT lcperm.`perm_name`, ug.`group_id`, ug.`group_name`, up.`perm_desc`
				FROM `".BIT_DB_PREFIX."liberty_content_permissions` lcperm
					INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON( lcperm.`group_id`=ug.`group_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."users_permissions` up ON( up.`perm_name`=lcperm.`perm_name` )
				WHERE lcperm.`content_id` = ?";
			$bindVars = array( $this->mContentId );
			$this->mPerms = $this->mDb->getAssoc( $query, $bindVars );
		}
		return( count( $this->mPerms ));
	}

	/**
	 * This function will replace all permissions for a particular package with the ones set using the content permissions
	 * 
	 * @param array $pPackage 
	 * @access public
	 * @return TRUE if changes were made to the $gBitUser->mPrefs hash
	 */
	function updateUserPermissions( $pPackage = NULL ) {
		$ret = FALSE;
		if( $this->isValid() && $this->hasAssignedPermissions() ) {
			global $gBitUser;

			if( empty( $pPackage ) ) {
				$pPackage = ACTIVE_PACKAGE;
			}

			// weed out permissions
			foreach( $gBitUser->mPerms as $key => $userPerm ) {
				if( $userPerm['package'] != $pPackage ) {
					$setPerms[$key] = $userPerm;
				}
			}
			$gBitUser->mPerms = array_merge( $setPerms, $this->mPerms );

			$ret = TRUE;
		}
		return $ret;
	}

	/**
	 * Check to see if the loaded content has individually assigned permissions
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function hasAssignedPermissions() {
		$ret = FALSE;
		if( $this->isValid() ) {
			$ret = $this->loadPermissions();
		}
		return $ret;
	}

	/**
	* Function that determines if this content specified permission for the current gBitUser, and will throw a fatal error if not.
	*
	* @param string Name of the permission to check
	* @param string Message if permission denigned
	*/
	function verifyPermission( $pPermName, $pFatalMessage = NULL ) {
		$ret = TRUE;
		if( $this->isValid() && !$this->hasUserPermission( $pPermName ) ) {
			global $gBitSystem;
			$gBitSystem->fatalPermission( $pPermName, $pFatalMessage );
		}
		return $ret;
	}

	/**
	 * Function that determines if this content specified permission for the current gBitUser. 
	 * Assigned content perms override the indvidual global perms, so the result is the union of the global permission set + overridden individual content perms
	*
	* @param string Name of the permission to check
	* @return bool true if user has permission to access file
	*/
	function hasUserPermission( $pPermName ) {
		global $gBitUser;
		$ret = FALSE;
		if( !$gBitUser->isRegistered() || !( $ret = $this->isOwner() || $ret = $gBitUser->isAdmin() )) {
			if( $gBitUser->isAdmin() || $gBitUser->hasPermission( $this->mAdminContentPerm )) {
				$ret = TRUE;
			} else {
				$this->verifyAccessControl();
				if( $this->loadPermissions() ) {
					// this content has assigned perms
					$globalPerms = $gBitUser->mPerms;

					// unset all perms in the default that are custom assigned
					// they might have been removed for this user...
					foreach( array_keys( $this->mPerms ) as $permName ) {
						if( isset( $globalPerms[$permName] )) {
							unset( $globalPerms[$permName] );
						}
					}

					// union the global perms plus the assigned perms
					$checkPerms = array_merge( $globalPerms, $this->getUserPermissions( $gBitUser->mUserId ));
					$ret = !empty( $checkPerms[$this->mAdminContentPerm] ) || !empty( $checkPerms[$pPermName] ); // && ( $checkPerms[$pPermName]['user_id'] == $gBitUser->mUserId );
				} else {
					// return default user permission setting when no content perms are set
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
		return( $this->hasUserPermission( $this->mAdminContentPerm ) );
	}

	/**
	* Determine if current user has the ability to edit this type of content
	*
	* @return bool True if user has this type of content administration permission
	*/
	function hasEditPermission() {
		global $gBitUser;
		return( $gBitUser->isAdmin() || $this->hasUserPermission( $this->mAdminContentPerm ) || $this->isOwner() );
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
			$query = "SELECT lcperm.`perm_name`, ug.`group_id`, ug.`group_name`, ugm.`user_id`
					FROM `".BIT_DB_PREFIX."liberty_content_permissions` lcperm
						INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON( lcperm.`group_id`=ug.`group_id` )
						INNER JOIN `".BIT_DB_PREFIX."users_groups_map` ugm ON( ugm.`group_id`=ug.`group_id` )
					WHERE ugm.`user_id`=? AND lcperm.`content_id` = ?";
			$bindVars = array( $pUserId, $this->mContentId );
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
	function storePermission( $pGroupId, $pPermName, $pContentId=NULL ) {
		if( !@$this->verifyId( $pContentId )) {
			$pContentId = $this->mContentId;
		}

		if( @BitBase::verifyId( $pGroupId ) && !empty( $pPermName ) && @BitBase::verifyId( $pContentId )) {
			$query = "
				DELETE FROM `".BIT_DB_PREFIX."liberty_content_permissions`
				WHERE `group_id` = ? AND `perm_name` = ? AND `content_id` = ?";
			$result = $this->mDb->query( $query, array( $pGroupId, $pPermName, $pContentId ), -1, -1 );
			$query = "
				INSERT INTO `".BIT_DB_PREFIX."liberty_content_permissions`
				( `group_id`,`content_id`, `perm_name` )
				VALUES( ?, ?, ? )";
			$result = $this->mDb->query( $query, array( $pGroupId, $pContentId, $pPermName ));
			return TRUE;
		}
		return FALSE;
	}

	/**
	* Remove a permission to access the content
	*
	* @param integer Group Identifier
	* @param string Name of the permission
	* @return bool true ( will not currently report a failure )
	*/
	function removePermission( $pGroupId, $pPermName ) {
		$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_content_permissions`
				  WHERE `group_id` = ? and `content_id` = ? and `perm_name` = ?";
		$bindVars = array( $pGroupId, $this->mContentId, $pPermName );
		$result = $this->mDb->query( $query, $bindVars );
		return true;
	}

	/**
	* Copy current permissions to another content
	*
	* @param integer Content Identifier of the target content
	* @return bool true ( will not currently report a failure )
	*/
	function copyPermissions( $destinationObjectId ) {
		$query = "SELECT `perm_name`, `group_name`
			FROM `".BIT_DB_PREFIX."liberty_content_permissions`
			WHERE `content_id` =?";
		$bindVars = array( $this->mContentId );
		$result = $this->mDb->query( $query, $bindVars );
		while( $res = $result->fetchRow() ) {
			$this->storePermission( $res["group_name"], $this->mContentTypeGuid, $res["perm_name"], $destinationObjectId );
		}
		return TRUE;
	}


	// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= Preferences Functions

	/**
	* Returns the content preferences value for the passed in key.
	*
	* @param string Hash key for the mPrefs value
	* @param string Default value to return if the preference is empty
	* @param int Optional content_id for arbitrary content preference
	*/
	function getPreference( $pPrefName, $pPrefDefault=NULL, $pContentId = NULL ) {
		global $gBitDb;
		$ret = NULL;

		if ($pContentId && !empty($pPrefName)) {
			// Get a user preference for an arbitrary user
			$sql = "SELECT `pref_value` FROM `".BIT_DB_PREFIX."liberty_content_prefs` WHERE `content_id`=? AND `pref_name`=?";

			if( !$ret = $gBitDb->getOne( $sql, array( $pContentId, $pPrefName ) ) ) {
				$ret = $pPrefDefault;
			}
		} else {
			if( is_null( $this->mPrefs ) ) {
				$this->loadPreferences();
			}
			if( isset( $this->mPrefs ) && isset( $this->mPrefs[$pPrefName] ) ) {
				$ret = $this->mPrefs[$pPrefName];
			} else {
				$ret = $pPrefDefault;
			}
		}
		return $ret;
	}

	function loadPreferences( $pContentId = NULL ) {
		if( @BitBase::verifyId( $pContentId ) ) {
			return $this->mDb->getAssoc( "SELECT `pref_name`, `pref_value` FROM `".BIT_DB_PREFIX."liberty_content_prefs` WHERE `content_id`=?", array( $pContentId ) );
		} elseif( $this->isValid() ) {
			// If no results, getAssoc will return an empty array (ie not a true NULL value) so getPreference can tell we have attempted a load
			$this->mPrefs = @$this->mDb->getAssoc( "SELECT `pref_name`, `pref_value` FROM `".BIT_DB_PREFIX."liberty_content_prefs` WHERE `content_id`=?", array( $this->mContentId ) );
		}
	}

	/**
	* Set a hash value in the mPrefs hash. This does *NOT* store the value in the database. It does no checking for existing or duplicate values. the main point of this function is to limit direct accessing of the mPrefs hash. I will probably make mPrefs private one day.
	*
	* @param string Hash key for the mPrefs value
	* @param string Value for the mPrefs hash key
	*/
	function setPreference( $pPrefName, $pPrefValue ) {
		$this->mPrefs[$pPrefName] = $pPrefValue;
	}


	/**
	* Saves a preference to the liberty_content_prefs database table with the given pref name and value. If the value is NULL, the existing value will be delete and the value will not be saved. However, a zero will be stored. This will update the mPrefs hash.
	*
	* @param string Hash key for the mPrefs value
	* @param string Value for the mPrefs hash key
	*/
	function storePreference( $pPrefName, $pPrefValue = NULL ) {
		$ret = FALSE;
		if( LibertyContent::isValid() ) {
			$query    = "DELETE FROM `".BIT_DB_PREFIX."liberty_content_prefs` WHERE `content_id`=? AND `pref_name`=?";
			$bindvars = array( $this->mContentId, $pPrefName );
			$result   = $this->mDb->query($query, $bindvars);
			if( !is_null( $pPrefValue )) {
				$query      = "INSERT INTO `".BIT_DB_PREFIX."liberty_content_prefs` (`content_id`,`pref_name`,`pref_value`) VALUES(?, ?, ?)";
				$bindvars[] = $pPrefValue;
				$result     = $this->mDb->query( $query, $bindvars );
				$this->mPrefs[$pPrefName] = $pPrefValue;
			}
			$this->mPrefs[$pPrefName] = $pPrefValue;
		}
		return $ret;
	}

	/**
	* Copy current permissions to another content
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
		global $gBitUser,$gBitSystem;
		if( empty( $_REQUEST['post_comment_submit'] ) && empty( $_REQUEST['post_comment_request'] ) ) {
			if( $this->mContentId && ( $gBitUser->mUserId != $this->mInfo['user_id'] ) ) {
				$query = "UPDATE `".BIT_DB_PREFIX."liberty_content_hits` SET `hits`=`hits`+1, `last_hit`= ? WHERE `content_id` = ?";
				$result = $this->mDb->query( $query, array( $gBitSystem->getUTCTime(), $this->mContentId ) );
				$affected_rows = $this->mDb->Affected_Rows();
				if( !$affected_rows ) {
					$query = "INSERT INTO `".BIT_DB_PREFIX."liberty_content_hits` ( `hits`, `last_hit`, `content_id` ) VALUES (?,?,?)";
					$result = $this->mDb->query( $query, array( 1, $gBitSystem->getUTCTime(), $this->mContentId ) );
				}
			}
		}
		return TRUE;
	}

	/**
	* Set Hits and Last Hit
	*
	* @return bool true ( will not currently report a failure )
	*/
	function setHits($pHits, $pLastHit=0) {
		if( $this->mContentId && !empty($pHits) ) {
			$query = "UPDATE `".BIT_DB_PREFIX."liberty_content_hits` SET `hits`= ?, `last_hit`= ? WHERE `content_id` = ?";
			$result = $this->mDb->query( $query, array( $pHits, $pLastHit, $this->mContentId ) );
			$affected_rows = $this->mDb->Affected_Rows();
			if( !$affected_rows ) {
				$query = "INSERT INTO `".BIT_DB_PREFIX."liberty_content_hits` ( `hits`, `last_hit`, `content_id` ) VALUES (?,?,?)";
				$result = $this->mDb->query( $query, array( $pHits, $pLastHit, $this->mContentId ) );
			}
		}
		return TRUE;
	}


	/**
	* Get Hits and Last Hit
	*
	* @return bool true ( will not currently report a failure )
	*/
	function getHits() {
		if( $this->mContentId  ) {
			$query = "SELECT `hits`,`last_hit` FROM `".BIT_DB_PREFIX."liberty_content_hits` where `content_id` = ?";
			$row = $this->mDb->getRow( $query, array( $this->mContentId ) );
			if ( !empty($row) ) {
				$this->mInfo['hits'] = $row['hits'];
				$this->mInfo['last_hit'] = $row['last_hit'];
			}
		}
		return TRUE;
	}

	/**
	* Determines if a wiki page (row in wiki_pages) exists, and returns a hash of important info. If N pages exists with $pPageName, returned existsHash has a row for each unique pPageName row.
	* @param pPageName name of the wiki page
	* @param pCaseSensitive look for case sensitive names
	* @param pContentId if you insert the content id of the currently viewed content, non-existing links can be created immediately
	*/
	function pageExists( $pPageName, $pCaseSensitive=FALSE, $pContentId=NULL ) {
		global $gBitSystem;
		$ret = NULL;
		if( $gBitSystem->isPackageActive( 'wiki' ) ) {
			$columnExpression = $this->mDb->getCaseLessColumn('lc.title');

			$pageWhere = $pCaseSensitive ? 'lc.`title`' : $columnExpression;
			$bindVars = array( ($pCaseSensitive ? $pPageName : strtoupper( $pPageName ) ) );
			$query = "SELECT `page_id`, wp.`content_id`, `description`, lc.`last_modified`, lc.`title`
				FROM `".BIT_DB_PREFIX."wiki_pages` wp, `".BIT_DB_PREFIX."liberty_content` lc
				WHERE lc.`content_id`=wp.`content_id` AND $pageWhere = ?";
			$ret = $this->mDb->getAll( $query, $bindVars );
		}
		return $ret;
	}

	/**
	* Create the generic title for a content item
	*
	* This will normally be overwriten by extended classes to provide
	* an appropriate title title string
	* @param array mInfo type hash of data to be used to provide base data
	* @return string Descriptive title for the page
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
	* @return string content_type_guid for the content
	*/
	function getContentType() {
		$ret = NULL;
		if( isset( $this->mInfo['content_type_guid'] ) ) {
			$ret = $this->mInfo['content_type_guid'];
		} elseif( $this->mContentTypeGuid ) {
			// for unloaded classes
			$ret = $this->mContentTypeGuid;
		} elseif( $this->mType['content_type_guid'] ) {
			// unloaded content might have this
			$ret = $this->mType['content_type_guid'];
		}
		return $ret;
	}

	function getContentTypeDescription( $pContentType=NULL ) {
		global $gLibertySystem;
		if( is_null( $pContentType ) ) {
			$pContentType = $this->getContentType();
		}
		return $gLibertySystem->getContentTypeDescription( $pContentType );
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
	*
	* @param string $pLinkText Text for the link unless overriden by object title
	* @param array $pMixed different possibilities depending on derived class
	* @param string $pAnchor anchor string e.g.: #comment_123
	* @return string Formated html the link to display the page.
	*/
	function getDisplayLink( $pLinkText=NULL, $pMixed=NULL, $pAnchor=NULL ) {
		global $gBitSmarty;
		$ret = '';
		if( empty( $pMixed ) && !empty( $this->mInfo )) {
			$pMixed = &$this->mInfo;
		}

		if( empty( $pLinkText )) {
			if( !empty( $pMixed['title'] )) {
				$pLinkText = $pMixed['title'];
			} elseif( !empty( $pMixed['content_description'] ) ) {
				$pLinkText = "[ ".$pMixed['content_description']." ]";
			}
		}

		if( empty( $pLinkText )) {
			$pLinkText = "[ ".tra( "No Title" )." ]";
		}

		// we add some more info to the title of the link
		if( !empty( $pMixed['created'] )) {
			require_once $gBitSmarty->_get_plugin_filepath( 'modifier', 'bit_short_date' );
			$linkTitle = tra( 'Created' ).': '.smarty_modifier_bit_short_date( $pMixed['created'] );
		} else {
			$linkTitle = $pLinkText;
		}

		// finally we are ready to create the full link
		if( !empty( $pMixed['content_id'] )) {
			$ret = '<a title="'.htmlspecialchars( $linkTitle ).'" href="'.LibertyContent::getDisplayUrl( $pMixed['content_id'], $pMixed ).$pAnchor.'">'.htmlspecialchars( $pLinkText ).'</a>';
		}
		return $ret;
	}

	/**
	* Not-so-pure virtual function that returns Request_URI to a piece of content
	* @param string Text for DisplayLink function
	* @param array different possibilities depending on derived class
	* @return string Formated URL address to display the page.
	*/
	function getDisplayUrl( $pContentId = NULL, $pMixed = NULL ) {
		if( @BitBase::verifyId( $pContentId ) ) {
			$ret = BIT_ROOT_URL.'index.php?content_id='.$pContentId;
		} elseif( @BitBase::verifyId( $pMixed['content_id'] ) ) {
			$ret = BIT_ROOT_URL.'index.php?content_id='.$pMixed['content_id'];
		} elseif( $this->isValid() ) {
			$ret = BIT_ROOT_URL.'index.php?content_id='.$this->mContentId;
		} else {
			$ret = '#';
		}
		return $ret;
	}


	/**
	* Not-so-pure virtual function that returns Request_URI to the preview.
	* @param string Text for DisplayLink function
	* @param array different possibilities depending on derived class
	* @return string Formated URL address to display the page.
	*/
	function getPreviewUrl( $pContentId = NULL, $pMixed = NULL ) {
		if( @BitBase::verifyId( $pContentId ) ) {
			$ret = LIBERTY_PKG_URL.'preview.php?content_id='.$pContentId;
		} elseif( @BitBase::verifyId( $pMixed['content_id'] ) ) {
			$ret = LIBERTY_PKG_URL.'preview.php?content_id='.$pMixed['content_id'];
		} elseif( $this->isValid() ) {
			$ret = LIBERTY_PKG_URL.'preview.php?content_id='.$this->mContentId;
		} else {
			$ret = '#';
		}
		return $ret;
	}


	/**
	* Not-so-pure virtual function that returns Request_URI to a content's thumbnail representation. It is up to the derived content what exactly this means
	* If not implemented in the content's class, this class will return NULL, which is an acceptable case meaning no thumbnail is available.
	* FisheyeGallery, BitUser might return pictures, BitArticle might return the article topic image, etc.
	* @param string Size of the url to return - should be a standard thumbnail size such as 'icon', 'avatar', 'small', 'medium', or 'large'
	* @param int optional contentId tp generate the thumbnail, if empty, the mContentId variable should be used
	* @param int optional secondary id, such as user_id or products_id, etc
	* @return string Formated URL address to display the page.
	*/
	function getThumbnailUrl( $pSize='small', $pContentId=NULL, $pSecondaryId=NULL ) {
	}

	/**
	* Updates results from any getList function to provide the control set
	* displaying in the smarty template
	* @param array hash of parameters returned by any getList() function
	* @return - none the hash is updated via the reference
	*/
	function postGetList( &$pListHash ) {
		global $gBitSystem;
		$pListHash['listInfo']['total_records'] = $pListHash["cant"];
		$pListHash['listInfo']['total_pages'] = ceil( $pListHash["cant"] / $pListHash['max_records'] );
		$pListHash['listInfo']['current_page'] = 1 + ( $pListHash['offset'] / $pListHash['max_records'] );

		if( $pListHash["cant"] > ( $pListHash['offset'] + $pListHash['max_records'] ) ) {
			$pListHash['listInfo']['next_offset'] = $pListHash['offset'] + $pListHash['max_records'];
		} else {
			$pListHash['listInfo']['next_offset'] = -1;
		}

		// If offset is > 0 then prev_offset
		if( $pListHash['offset'] > 0 ) {
			$pListHash['listInfo']['prev_offset'] = $pListHash['offset'] - $pListHash['max_records'];
		} else {
			$pListHash['listInfo']['prev_offset'] = -1;
		}

		$pListHash['listInfo']['offset'] = $pListHash['offset'];
		$pListHash['listInfo']['find'] = $pListHash['find'];
		$pListHash['listInfo']['sort_mode'] = $pListHash['sort_mode'];
		$pListHash['listInfo']['max_records'] = $pListHash['max_records'];

		// calculate what links to show
		if( $gBitSystem->isFeatureActive( 'site_direct_pagination' ) ) {
			// number of continuous links to display on either side
			$continuous = 5;
			// number of skipping links to display on either side
			$skipping = 5;

			// size of steps to take when skipping
			// if you have more than 1000 pages, you should consider not using the pagination form
			if( $pListHash['listInfo']['total_pages'] < 50 ) {
				$step = 5;
			} elseif( $pListHash['listInfo']['total_pages'] < 100 ) {
				$step = 10;
			} elseif( $pListHash['listInfo']['total_pages'] < 250 ) {
				$step = 25;
			} elseif( $pListHash['listInfo']['total_pages'] < 500 ) {
				$step = 50;
			} else {
				$step = 100;
			}

			$prev  = ( $pListHash['listInfo']['current_page'] - $continuous > 0 ) ? $pListHash['listInfo']['current_page'] - $continuous : 1;
			$next  = ( $pListHash['listInfo']['current_page'] + $continuous < $pListHash['listInfo']['total_pages'] ) ? $pListHash['listInfo']['current_page'] + $continuous : $pListHash['listInfo']['total_pages'];
			for( $i = $pListHash['listInfo']['current_page'] - 1; $i >= $prev; $i -= 1 ) {
				$pListHash['listInfo']['block']['prev'][$i] = $i;
			}
			if( $prev != 1 ) {
				// replace the last of the continuous links with a ...
				$pListHash['listInfo']['block']['prev'][$i + 1] = "&hellip;";
				// add $skipping links to pages separated by $step pages
				if( ( $min = $pListHash['listInfo']['current_page'] - $continuous - ( $step * $skipping ) ) < 0 ) {
					$min = 0;
				}
				for( $j = ( floor( $i / $step ) * $step ); $j > $min; $j -= $step ) {
					$pListHash['listInfo']['block']['prev'][$j] = $j;
				}
				$pListHash['listInfo']['block']['prev'][1] = 1;
			}
			// reverse array that links are in the correct order
			if( !empty( $pListHash['listInfo']['block']['prev'] ) ) {
				$pListHash['listInfo']['block']['prev'] = array_reverse( $pListHash['listInfo']['block']['prev'], TRUE );
			}

			// here we start adding next links
			for( $i = $pListHash['listInfo']['current_page'] + 1; $i <= $next; $i += 1 ) {
				$pListHash['listInfo']['block']['next'][$i] = $i;
			}
			if( $next != $pListHash['listInfo']['total_pages'] ) {
				// replace the last of the continuous links with a ...
				$pListHash['listInfo']['block']['next'][$i - 1] = "&hellip;";
				// add $skipping links to pages separated by $step pages
				if( ( $max = $pListHash['listInfo']['current_page'] + $continuous + ( $step * $skipping ) ) > $pListHash['listInfo']['total_pages'] ) {
					$max = $pListHash['listInfo']['total_pages'];
				}
				for( $j = ( ceil( $i / $step ) * $step ); $j < $max; $j += $step ) {
					$pListHash['listInfo']['block']['next'][$j] = $j;
				}
				$pListHash['listInfo']['block']['next'][$pListHash['listInfo']['total_pages']] = $pListHash['listInfo']['total_pages'];
			}
		}
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
			$mid .= ' AND lc.`content_type_guid`=? ';
			$bindVars[] = $pListHash['content_type_guid'];
		}

		$this->prepGetList( $pListHash );
		$query = "SELECT DISTINCT(uu.`user_id`) AS hash_key, uu.`user_id`, SUM( lch.`hits` ) AS `ag_hits`, uu.`login`, uu.`real_name`
				FROM `".BIT_DB_PREFIX."liberty_content` lc INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON( uu.`user_id`=lc.`user_id` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch 
					ON (`lc`.`content_id` =  `lch`.`content_id`)
				WHERE uu.`user_id` != ".ANONYMOUS_USER_ID." AND lch.`hits` > 0 $mid
				GROUP BY uu.`user_id`, uu.`login`, uu.`real_name`
				ORDER BY `ag_hits` DESC";
		$result = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] );
		while( $aux = $result->fetchRow() ) {
			$ret[] = $aux;
		}
		return $ret;
	}

	/**
	* Get a list of content ranked by certain criteria set in $pListHash['sort_mode']
	*
	* @param array hash of parameters ( content_type_guid will limit list to a single content type
	* @return - data
	**/
	function getContentRanking( $pListHash ) {
		$pListHash['sort_mode'] = !empty( $pListHash['sort_mode'] ) ? $pListHash['sort_mode'] : 'hits_desc';

		if( $pListHash['sort_mode'] == 'top_authors' ) {
			global $gBitUser;
			$ret['data'] = $gBitUser->getAuthorList( $pListHash );
		} else {
			include_once( LIBERTY_PKG_PATH.'LibertyContent.php' );
			$libertyContent = new LibertyContent();
			$ret = $libertyContent->getContentList( $pListHash );
		}

		$ret['title']     = !empty( $pListHash['title'] ) ? $pListHash['title'] : tra( "Content Ranking" );
		$ret['attribute'] = !empty( $pListHash['attribute'] ) ? $pListHash['attribute'] : tra( "Hits" );

		return $ret;
	}

	/**
	* Get a list of all structures this content is a member of
	*
	* @param string $pListHash['content_type_guid'] Content GUID to limit the list to
	* @param integer $pListHash['max_records'] Number of the first record to access ( used to page the list )
	* @param integer $pListHash['offset'] Number of records to return
	* @param string $pListHash['sort_mode'] Name of the field to sort by ( extended by _asc or _desc for sort direction )
	* @param array $pListHash['find'] List of text elements to filter the results by
	* @param integer $pListHash[''] User ID - If set, then only the objcets created by that user will be returned
	* $pListHash['last_modified'] date - modified since
	* $pListHash['end_date'] date - modified before
	* @return array An array of mInfo type arrays of content objects
	**/
	function getContentList( $pListHash ) {

		global $gLibertySystem, $gBitSystem, $gBitUser, $gBitSmarty;

		$this->prepGetList( $pListHash );

		$hashSql = array('select'=>array(), 'join'=>array(),'where'=>array() );
		$hashBindVars = array('select'=>array(), 'where'=>array(), 'join'=>array());
		if (!empty($pListHash['content_type_guid']) && is_array($pListHash['content_type_guid'])) {
			foreach ($pListHash['content_type_guid'] as $contentTypeGuid) {
				$this->getFilter($contentTypeGuid, $hashSql, $hashBindVars, $pListHash);
			}
		} elseif (!empty($pListHash['content_type_guid'])) {
			$this->getFilter($pListHash['content_type_guid'], $hashSql, $hashBindVars, $pListHash);
		}

		if (!empty($hashSql['select'])) {
			$selectSql = ','.implode(',', $hashSql['select']);
		} else {
			$selectSql = '';
		}
		$joinSql = implode(' ', $hashSql['join']);
		$whereSql = '';
		if (empty($hashBindVars['join'])) {
			$bindVars = array();
		} else {
			$bindVars = $hashBindVars['join'];
		}
		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars, NULL, $pListHash );

		if( $pListHash['sort_mode'] == 'size_desc' ) {
			$pListHash['sort_mode'] = 'wiki_page_size_desc';
		}

		if( $pListHash['sort_mode'] == 'size_asc' ) {
			$pListHash['sort_mode'] = 'wiki_page_size_asc';
		}

		$old_sort_mode = '';

		$sortHash = array(
			'versions_desc',
			'versions_asc',
			'links_asc',
			'links_desc',
			'backlinks_asc',
			'backlinks_desc'
		);

		if( in_array( $pListHash['sort_mode'], $sortHash ) ) {
			$old_offset = $pListHash['offset'];
			$old_max_records = $pListHash['max_records'];
			$old_sort_mode = $pListHash['sort_mode'];
			$pListHash['sort_mode'] = 'modifier_user_desc';
			$pListHash['offset'] = 0;
			$pListHash['max_records'] = -1;
		}

		if( is_array( $pListHash['find'] ) ) { // you can use an array of titles
			$whereSql .= " AND lc.`title` IN ( ".implode( ',',array_fill( 0,count( $pListHash['find'] ),'?' ) ).") ";
			$bindVars = array_merge( $pListHash['find'], $pListHash['find']);
		} elseif( !empty($pListHash['find'] ) && is_string( $pListHash['find'] ) ) { // or a string
			$whereSql .= " AND UPPER(lc.`title`) like ? ";
			$bindVars[] = ( '%' . strtoupper( $pListHash['find'] ) . '%' );
		}

		// this is necessary to display useful information in the liberty RSS feed
		if( !empty( $pListHash['include_data'] ) ) {
			$selectSql .= ", lc.`data`, lc.`format_guid`";
		}

		// calendar specific selection method - use timestamps to limit selection
		if( !empty( $pListHash['start'] ) && !empty( $pListHash['stop'] ) ) {
			$whereSql .= " AND ( lc.`".$pListHash['calendar_sort_mode']."` > ? AND lc.`".$pListHash['calendar_sort_mode']."` < ? ) ";
			$bindVars[] = $pListHash['start'];
			$bindVars[] = $pListHash['stop'];
		}

		if( @$this->verifyId( $pListHash['user_id'] ) ) {
			$whereSql .= " AND lc.`user_id` = ? ";
			$bindVars[] = $pListHash['user_id'];
		}

		if( !empty( $pListHash['content_type_guid'] ) && is_string( $pListHash['content_type_guid'] ) ) {
			$whereSql .= ' AND `content_type_guid`=? ';
			$bindVars[] = $pListHash['content_type_guid'];
		} elseif( !empty( $pListHash['content_type_guid'] ) && is_array( $pListHash['content_type_guid'] ) ) {
			$whereSql .= " AND lc.`content_type_guid` IN ( ".implode( ',',array_fill ( 0, count( $pListHash['content_type_guid'] ),'?' ) )." )";
			$bindVars = array_merge( $bindVars, $pListHash['content_type_guid'] );
		}

		// only display content modified more recently than this (UTC timestamp)
		if ( !empty( $pListHash['from_date'] ) ) {
			$whereSql .= ' AND lc.`last_modified` >= ?';
			$bindVars[] = $pListHash['from_date'];
		}

		// only display content modified before this (UTC timestamp)
		if ( !empty( $pListHash['until_date'] ) ) {
			$whereSql .= ' AND lc.`last_modified` <= ?';
			$bindVars[] = $pListHash['until_date'];
		}

		if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
			$selectSql .= ' ,ls.`security_id`, ls.`security_description`, ls.`is_private`, ls.`is_hidden`, ls.`access_question`, ls.`access_answer` ';
			$joinSql .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."gatekeeper_security_map` cg ON (lc.`content_id`=cg.`content_id`) LEFT OUTER JOIN `".BIT_DB_PREFIX."gatekeeper_security` ls ON (ls.`security_id`=cg.`security_id` ) ";
			$whereSql .= ' AND (cg.`security_id` IS NULL OR lc.`user_id`=?) ';
			$bindVars[] = $gBitUser->mUserId;
			if( $gBitSystem->isPackageActive( 'fisheye' ) ) {
				// This is really ugly to have in here, and really would be better off somewhere else.
				// However, because of the specific nature of the current implementation of fisheye galleries, I am afraid
				// this is the only place it can go to properly enforce gatekeeper protections. Hopefully a new content generic
				// solution will be available in ReleaseTwo - spiderr
				if( $this->mDb->isAdvancedPostgresEnabled() ) {
// 					$joinSql .= " LEFT OUTER JOIN  `".BIT_DB_PREFIX."fisheye_gallery_image_map` fgim ON (fgim.`item_content_id`=lc.`content_id`)";
					$whereSql .= " AND (SELECT ls.`security_id` FROM connectby('fisheye_gallery_image_map', 'gallery_content_id', 'item_content_id', lc.`content_id`, 0, '/')  AS t(`cb_gallery_content_id` int, `cb_item_content_id` int, level int, branch text), `".BIT_DB_PREFIX."gatekeeper_security_map` cgm,  `".BIT_DB_PREFIX."gatekeeper_security` ls
							WHERE ls.`security_id`=cgm.`security_id` AND cgm.`content_id`=`cb_gallery_content_id` LIMIT 1) IS NULL";
				}
			}
		}

		$sortHash = array(
			'content_id_desc',
			'content_id_asc',
			'modifier_user_desc',
			'modifier_user_asc',
			'modifier_real_name_desc',
			'modifier_real_name_asc',
			'creator_user_desc',
			'creator_user_asc',
			'creator_real_name_desc',
			'creator_real_name_asc',
		);

		if( in_array( $pListHash['sort_mode'], $sortHash ) ) {
			$orderTable = '';
		} elseif( !empty( $pListHash['order_table'] ) ) {
			$orderTable = $pListHash['order_table'];
		} elseif( !empty( $pListHash['sort_mode'] ) && strtolower( substr( $pListHash['sort_mode'], 0, 4 ) ) =='hits' ) {
			$orderTable = 'lch.';
		} else {
			$orderTable = 'lc.';
		}

		if (!empty($hashSql['where'])) {
			$whereSql .= ' AND '.implode(' ', $hashSql['where']);
		}
		if (!empty($hashBindVars['where'])) {
			$bindVars = array_merge($bindVars, $hashBindVars['where']);
		}
		$whereSql = preg_replace( '/^[\s]*AND\b/i', 'WHERE ', $whereSql );

		// If sort mode is versions then offset is 0, max_records is -1 (again) and sort_mode is nil
		// If sort mode is links then offset is 0, max_records is -1 (again) and sort_mode is nil
		// If sort mode is backlinks then offset is 0, max_records is -1 (again) and sort_mode is nil
		$query = "
			SELECT
				uue.`login` AS `modifier_user`,
				uue.`real_name` AS `modifier_real_name`,
				uue.`user_id` AS `modifier_user_id`,
				uuc.`login` AS `creator_user`,
				uuc.`real_name` AS `creator_real_name`,
				uuc.`user_id` AS `creator_user_id`,
				lch.`hits`,
				lch.`last_hit`,
				lc.`event_time`,
				lc.`title`,
				lc.`last_modified`,
				lc.`content_type_guid`,
				lc.`ip`,
				lc.`created`,
				lc.`content_id`
				$selectSql
			FROM `".BIT_DB_PREFIX."liberty_content` lc
				INNER JOIN `".BIT_DB_PREFIX."users_users` uuc ON (lc.`modifier_user_id`=uuc.`user_id`)
				INNER JOIN `".BIT_DB_PREFIX."users_users` uue ON (lc.`modifier_user_id`=uue.`user_id`)
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON( lc.`content_id` =  lch.`content_id`)
				$joinSql
				$whereSql
			ORDER BY ".$orderTable.$this->mDb->convertSortmode($pListHash['sort_mode']);

		$query_cant = "
			SELECT
				COUNT(lc.`content_id`)
			FROM `".BIT_DB_PREFIX."liberty_content` lc
				INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`modifier_user_id`=uu.`user_id`)
			$joinSql
			$whereSql";
		$cant = $this->mDb->getOne( $query_cant, $bindVars );

		if( !empty( $hashBindVars['select'] ) ) {
			$bindVars = array_merge($hashBindVars['select'], $bindVars);
		}
		$result = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] );
		$ret = array();
		$contentTypes = $gLibertySystem->mContentTypes;
		while( $aux = $result->fetchRow() ) {
			if( !empty( $contentTypes[$aux['content_type_guid']] ) ) {
				// quick alias for code readability
				$type                       = &$contentTypes[$aux['content_type_guid']];
				$aux['content_description'] = tra( $type['content_description'] );
				$aux['creator']             = (isset( $aux['creator_real_name'] ) ? $aux['creator_real_name'] : $aux['creator_user'] );
				$aux['real_name']           = (isset( $aux['creator_real_name'] ) ? $aux['creator_real_name'] : $aux['creator_user'] );
				$aux['editor']              = (isset( $aux['modifier_real_name'] ) ? $aux['modifier_real_name'] : $aux['modifier_user'] );
				$aux['user']                = $aux['creator_user'];
				$aux['user_id']             = $aux['creator_user_id'];
				// create *one* object for each object *type* to  call virtual methods.
				if( empty( $type['content_object'] ) ) {
					include_once( $gBitSystem->mPackages[$type['handler_package']]['path'].$type['handler_file'] );
					$type['content_object'] = new $type['handler_class']();
				}
				if( !empty( $gBitSystem->mPackages[$type['handler_package']] ) ) {
					$aux['title']        = $type['content_object']->getTitle( $aux );
					$aux['display_link'] = $type['content_object']->getDisplayLink( $aux['title'], $aux );
					$aux['display_url']  = $type['content_object']->getDisplayUrl( $aux['content_id'], $aux );
				}
				$ret[] = $aux;
			}
		}

		// If sortmode is versions, links or backlinks sort using the ad-hoc function and reduce using old_offse and old_max_records
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
			$ret = array_slice($ret, $old_offset, $old_max_records);
		}

		$pListHash["data"] = $ret;
		$pListHash["cant"] = $cant;
		LibertyContent::postGetList( $pListHash );
		return $pListHash;
	}


	/**
	* Get a list of all structures this content is a member of
	**/
	function getStructures() {
		$ret = NULL;
		if( $this->isValid() ) {
			$ret = array();
			$structures_added = array();
			$query = 'SELECT ls.*, lc.`title`, tcr.`title` AS `root_title`
				FROM `'.BIT_DB_PREFIX.'liberty_content` lc, `'.BIT_DB_PREFIX.'liberty_structures` ls
				INNER JOIN  `'.BIT_DB_PREFIX.'liberty_structures` tsr ON( tsr.`structure_id`=ls.`root_structure_id` )
				INNER JOIN `'.BIT_DB_PREFIX.'liberty_content` tcr ON( tsr.`content_id`=tcr.`content_id` )
				WHERE lc.`content_id`=ls.`content_id` AND ls.`content_id`=?';
			if( $result = $this->mDb->query( $query,array( $this->mContentId ) ) ) {
				while ($res = $result->fetchRow()) {
					$ret[] = $res;
				}
			}
		}
		return $ret;
	}

	
	/*
	 * Splits content either at the ...split... or at the
	 * length specified if no manual split is in the content.
	 *
	 * @param pParseHash a hash with 'data' in it and any
	 *        arguments to the parser as required
	 * @param pLength the length to split at if no ...split... is present
	 * @param pForceLength force split at length (default false)
	 */
	function parseSplit($pParseHash, $pLength, $pForceLength = false) {
		global $gLibertySystem, $gBitSystem;
		$res = NULL;
		// Force cache extension
		$pParseHash['cache_extension'] = 'desc';
		// Strip trailing breaks and fixup tags.
		$pParseHash['cleanup'] = true;

		$res['data'] = $pParseHash['data'];
		if( $pForceLength ) {
			$res['data'] = preg_replace( LIBERTY_SPLIT_REGEX, '', $res['data'] );
		}
		if( preg_match( LIBERTY_SPLIT_REGEX, $res['data'] ) ) {
			$res['man_split'] = TRUE;
			$parts = preg_split( LIBERTY_SPLIT_REGEX, $res['data'] );
			if( empty( $parts[1] ) ) {
				$res['has_more'] = FALSE;
			}
			$pParseHash['data'] = $parts[0];
		} else {
			// Include length in cache file
			$pParseHash['cache_extension'] .= '.'.$pLength;
			$pParseHash['data'] = substr( $res['data'], 0, $pLength );
		}
		
		// description shouldn't contain {maketoc}
		$pParseHash['data'] = preg_replace( "/\{maketoc[^\}]*\}/i", "", $pParseHash['data'] );

		// Do the actual parsing.
		$res['parsed'] = $res['parsed_description'] = $this->parseData($pParseHash);

		// Setup the has_more properly and add ... if required.
		if( preg_replace( "/\{maketoc[^\}]*\}/i", "", $res['data'] ) != $pParseHash['data'] && empty( $res['man_split'] )) {
			// we append ... when the split was generated automagically
			$res['parsed_description'] .= '&hellip;';
			$res['has_more'] = TRUE;
		} elseif( preg_replace( "/\{maketoc[^\}]*\}/i", "", $res['data'] ) != $pParseHash['data'] ) {
			$res['has_more'] = TRUE;
		}

		return $res;
	}

	/**
	* Process the raw content blob using the speified content GUID processor
	*
	* This is the "object like" method. It should be more object like,
	* but for now, we'll just point to the old lib style "parse_data" - XOXO spiderr
	* @param         pMixed can be a string or a hash - if a string is given, it will be parsed without the use of cache
	* @param string  pMixed['data'] string to be parsed
	* @param int     pMixed['content_id'] content_id or the item to be parsed - required for caching and optimal parser performance
	* @param boolean pMixed['no_cache'] disable caching
	* @param string  pMixed['cache_extension'] cache to a separate file. useful for truncated displays of parsed content such as article front page
	* @param string pFormatGuid processor to use
	* @return string Formated data string
	*/
	function parseData( $pMixed=NULL, $pFormatGuid=NULL ) {
		global $gLibertySystem, $gBitSystem;

		// get the data into place
		if( empty( $pMixed ) && !empty( $this->mInfo['data'] ) ) {
			$parseHash = $this->mInfo;
		} elseif( is_array( $pMixed ) ) {
			$parseHash = $pMixed;
			if( empty( $parseHash['data'] ) ) {
				$parseHash['data'] = '';
			}
		} else {
			$parseHash['data'] = $pMixed;
		}

		// sanitise parseHash a bit
		if( empty( $parseHash['content_id'] ) ) {
			$parseHash['content_id'] = NULL;
		}

		if( empty( $parseHash['cache_extension'] ) ) {
			$parseHash['cache_extension'] = NULL;
		}

		// get the format guid into place
		if( !empty( $parseHash['format_guid'] ) ) {
			$formatGuid = $parseHash['format_guid'];
		} else {
			$formatGuid = $pFormatGuid;
		}

		$ret = NULL;

		// Handle caching if it is enabled.
		if( $gBitSystem->isFeatureActive( 'liberty_cache' ) && !empty( $parseHash['content_id'] ) && empty( $parseHash['no_cache'] ) ) {			
			if( $cacheFile = LibertyContent::getCacheFile( $parseHash['content_id'], $parseHash['cache_extension'] ) ) {
				// Attempt to read cache file
				if (! ($ret = LibertyContent::readCacheFile($cacheFile)) ) {
					// Read failed. Parse and store.
					if( !empty( $parseHash['data'] ) && $formatGuid ) {
						if( $func = $gLibertySystem->getPluginFunction( $formatGuid, 'load_function' ) ) {
							$ret = $func( $parseHash, $this );
							if (!empty($ret) && !empty($parseHash['cleanup']) && $parseHash['cleanup'] ) {
								$ret = preg_replace( '/(<br *\/? *>)*$/i', '', $ret);
								$ret = $gLibertySystem->purifyHtml($ret, true);
							}
						}
					}
					LibertyContent::writeCacheFile($cacheFile, $ret);
				}
				else {
					// Note that we read from cache.
					$pCommonObject->mInfo['is_cached'] = TRUE;
				}
			}
		}
					
		if (!$ret) {
			if( !empty( $parseHash['data'] ) && $formatGuid ) {
				if( $func = $gLibertySystem->getPluginFunction( $formatGuid, 'load_function' ) ) {
					$ret = $func( $parseHash, $this );
					if (!empty($ret) && !empty($parseHash['cleanup']) && $parseHash['cleanup'] ) {
						$ret = preg_replace( '/(<br *\/? *>)*$/i', '', $ret);
						$ret = $gLibertySystem->purifyHtml($ret, true);
					}
				}
			}
		}

		return $ret;
	}


	/**
	* Special parsing for multipage articles
	*
	* Temporarily remove <pre>...</pre> sections to protect
	* from broke <pre>pre</pre> tags and leave well known <pre>pre</pre>
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
	function isUrlCached($url) {
		// return false until this is fixed
		return FALSE;

		$query = "select `cache_id`  from `".BIT_DB_PREFIX."liberty_link_cache` where `url`=?";
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
		// return  TRUE until this is fixed
		return TRUE;

		// Avoid caching internal references... (only if $data not present)
		// (cdx) And avoid other protocols than http...
		// 03-Nov-2003, by zaufi
		// preg_match("_^(mailto:|ftp:|gopher:|file:|smb:|news:|telnet:|javascript:|nntp:|nfs:)_",$url)
		// was removed (replaced to explicit http[s]:// detection) bcouse
		// I now (and actualy use in my production Tiki) another bunch of protocols
		// available in my konqueror... (like ldap://, ldaps://, nfs://, fish://...)
		// ... seems like it is better to enum that allowed explicitly than all
		// noncacheable protocols.
		if (((strstr($url, 'tiki-') || strstr($url, 'messages-')) && $data == '')
		|| (substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://'))
			return false;
		// Request data for URL if nothing given in parameters
		// (reuse $data var)
		if ($data == '') $data = bit_http_request($url);

		// If stuff inside [] is *really* malformatted, $data
		// will be empty.  -rlpowell
		if (!$this->isUrlCached( $url ) && $data)
		{	global $gBitSystem;
			$refresh = $gBitSystem->getUTCTime();
			$query = "insert into `".BIT_DB_PREFIX."liberty_link_cache`(`url`,`data`,`refresh`) values(?,?,?)";
			$result = $this->mDb->query($query, array($url,BitDb::dbByteEncode($data),$refresh) );
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
				$whereSql = ' AND ls.`root_structure_id`=? ';
			}
			$query  = "SELECT `structure_id` FROM `".BIT_DB_PREFIX."liberty_structures` ls
					WHERE ls.`content_id`=? $whereSql";
			$cant = $this->mDb->getOne( $query, $bindVars );
			return $cant;
		}
	}

	/**
	 * This is a generic liberty content function to gather indexable words. Override this function
	 * in your BitPackage.php file if you need to add more indexable words from files other than
	 * tiki_content and users_users.
	 */

	function setIndexData( $pContentId = 0 ) {
		global $gBitSystem ;
		if ( $pContentId == 0 ) $pContentId = $this->mContentId;
		$sql = "SELECT lc.`title`, lc.`data`, uu.`login`, uu.`real_name` " .
				"FROM `" . BIT_DB_PREFIX . "liberty_content` lc " .
				"INNER JOIN `" . BIT_DB_PREFIX . "users_users` uu ON uu.`user_id` = lc.`user_id` " .
				"WHERE lc.`content_id` = ?" ;
		$res = $gBitSystem->mDb->getRow($sql, array($pContentId));
		if (!(isset($this->mInfo['no_index']) and $this->mInfo['no_index'] == true)) {
			$this->mInfo['index_data'] = $res["title"] . " " . $res["data"] . " " . $res["login"] . " " . $res["real_name"] ;
		}
	}

	// -------------------- Cache Funtions -------------------- //

	/**
	 * Check if content has a cache file
	 * 
	 * @param array $pContentId Content id of cached item
	 * @access public
	 * @return absolute path
	 */
	function isCached( $pContentId = NULL ) {
		global $gBitSystem;
		if( empty( $pContentId ) && @BitBase::verifyId( $this->mContentId ) ) {
			$pContentId = $this->mContentId;
		}

		return( $gBitSystem->getConfig( 'liberty_cache' ) && is_file( LibertyContent::getCacheFile( $pContentId )));
	}

	/**
	 * Get the path where we store liberty cached content
	 * 
	 * @access public
	 * @return absolute path
	 */
	function getCacheBasePath() {
		return str_replace( '//', '/', TEMP_PKG_PATH.LIBERTY_PKG_NAME.'/cache/' );
	}

	/**
	 * Get the path to directory where an individual cache item is stored
	 * 
	 * @param array $pContentId Content id of cached item
	 * @access public
	 * @return path on success, FALSE on failure
	 */
	function getCachePath( $pContentId = NULL ) {
		if( empty( $pContentId ) && @BitBase::verifyId( $this->mContentId ) ) {
			$pContentId = $this->mContentId;
		}

		$ret = FALSE;
		if( @BitBase::verifyId( $pContentId ) ) {
			$subdir = $pContentId % 1000;
			$path = LibertyContent::getCacheBasePath().$subdir.'/'.$pContentId.'/';
			if( is_dir( $path ) || mkdir_p( $path ) ) {
				$ret = $path;
			}
		}

		return $ret;
	}

	/**
	 * Attempts to read from the specified cache file checking if the
	 * cached data has expired.
	 *
	 * @param the name of the cache file from getCacheFile()
	 * @return the contents of the cache file or NULL
	 */
	function readCacheFile($pCacheFile) {
		global $gBitSystem;
		$ret = NULL;
		if (is_file($pCacheFile) && (time() - filemtime( $pCacheFile )) < $gBitSystem->getConfig('liberty_cache') && filesize( $pCacheFile ) > 0 ) {
			// get contents from cache file
			$h = fopen( $pCacheFile, 'r' );
			$ret = fread( $h, filesize( $pCacheFile ) );
			fclose( $h );
		}
		return $ret;
	}

	/**
	 * Unconditionally writes data to the cache file.
	 * Does not check for error assuming if write failed that the
	 * read will as well.
	 *
	 * @param the name of the cache file from getCacheFile() to write
	 * @param the contents to write to the file
	 */
	function writeCacheFile($pCacheFile, $pData) {
		// Cowardly refuse to write nothing.
		if (!empty($pData)) {
			// write parsed contents to cache file
			$h = fopen( $pCacheFile, 'w' );
			fwrite( $h, $pData );
			fclose( $h );
		}
	}

	/**
	 * Get the path to file where an individual cache item is stored
	 * 
	 * @param array $pContentId Content id of cached item
	 * @access public
	 * @return filename on success, FALSE on failure
	 */
	function getCacheFile( $pContentId = NULL, $pCacheExtension = NULL ) {
		if( $ret = LibertyContent::getCachePath( $pContentId ) ) {
			return( $ret.$pContentId.( !empty( $pCacheExtension ) ? '.'.$pCacheExtension : '') );
		} else {
			return FALSE;
		}
	}

	/**
	 * Delete cache files for a given content item
	 * 
	 * @param array $pContentId 
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function expungeCacheFile( $pContentId = NULL ) {
		if( @BitBase::verifyId( $pContentId ) ) {
			// we need to unlink all files with the same id and any extension
			if( $dh = opendir( $cacheDir = LibertyContent::getCachePath( $pContentId ) ) ) {
				while( FALSE !== ( $file = readdir( $dh ) ) ) {
					if( $file != '.' && $file != '..' && ( preg_match( "/^".$pContentId."$/", $file ) || preg_match( "/^".$pContentId."\..*/", $file ) ) ) {
						@unlink( $cacheDir.$file );
					}
				}
			}
		}

		return TRUE;
	}

	function getFilter( $pContentTypeGuid, &$pSql, &$pBindVars, $pHash = null) {
		global $gLibertySystem, $gBitSystem;
		foreach ($gLibertySystem->mContentTypes as $type) {
			if ($type['content_type_guid'] == $pContentTypeGuid) {
				$path = $gBitSystem->mPackages[$type['handler_package']]['path'];//constant(strtoupper($type['handler_package']).'_PKG_PATH');	
				include_once($path.$type['handler_file']);
				$content = new $type['handler_class'];
				if (method_exists($content, 'getFilterSql')) {
					$content->getFilterSql($pSql, $pBindVars, $pHash);
				}
			}
		}
	}

	// -------------------- Action Logging Funtions -------------------- //

	/**
	 * storeActionLog 
	 * Note: use $gBitSystem throughout that this function can be called statically if needed
	 * 
	 * @param array $pParamHash 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function storeActionLog( $pParamHash = NULL ) {
		global $gBitSystem;
		if( $gBitSystem->isFeatureActive( 'liberty_action_log' ) && LibertyContent::verifyActionLog( $pParamHash ) ) {
			$gBitSystem->mDb->associateInsert( BIT_DB_PREFIX."liberty_action_log", $pParamHash['action_log_store'] );
		}
	}

	/**
	 * verify the data in the action log is ready for storing
	 * First checks $pParamHash['action_log'] for information and then the content_store stuff
	 * Note: use $gBitSystem throughout that this function can be called statically if needed
	 * 
	 * @param array $pParamHash 
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function verifyActionLog( &$pParamHash ) {
		global $gBitUser, $gBitSystem;

		// we will set $ret FALSE if there is a problem along the way
		// we can't populate mErrors since it would defeat the purpose having errors about the logging system
		$ret = TRUE;

		// content_id isn't strictly needed
		if( @BitBase::verifyId( $pParamHash['action_log']['content_id'] ) ) {
			$pParamHash['action_log_store']['content_id'] = $pParamHash['action_log']['content_id'];
		} elseif( @BitBase::verifyId( $pParamHash['content_id'] ) ) {
			$pParamHash['action_log_store']['content_id'] = $pParamHash['content_id'];
		} elseif( !empty( $this ) && @BitBase::verifyId( $this->mContentId ) ) {
			$pParamHash['action_log_store']['content_id'] = $this->mContentId;
		}

		// generic information needed in log
		if( !empty( $pParamHash['action_log']['user_id'] ) ) {
			$pParamHash['action_log_store']['user_id'] = $pParamHash['action_log']['user_id'];
		} else {
			$pParamHash['action_log_store']['user_id'] = $gBitUser->mUserId;
		}

		if( !empty( $pParamHash['action_log']['title'] ) ) {
			$pParamHash['action_log_store']['title'] = $pParamHash['action_log']['title'];
		} elseif( !empty( $pParamHash['content_store']['title'] ) ) {
			$pParamHash['action_log_store']['title'] = $pParamHash['content_store']['title'];
		} elseif( !empty( $this ) && !empty( $this->mInfo['title'] ) ) {
			$pParamHash['action_log_store']['title'] = $this->mInfo['title'];
		} else {
			$ret = FALSE;
		}

		// IP of the user
		if( empty( $pParamHash['action_log']['ip'] ) ) {
			if( !empty( $pParamHash['content_store']['ip'] ) ) {
				$pParamHash['action_log']['ip'] = $pParamHash['content_store']['ip'];
			} elseif( empty( $_SERVER["REMOTE_ADDR"] ) ) {
				$pParamHash['action_log']['ip'] = '127.0.0.1';
			} else {
				$pParamHash['action_log']['ip'] = $_SERVER["REMOTE_ADDR"];
			}
		}
		$pParamHash['action_log_store']['ip'] = $pParamHash['action_log']['ip'];
		$pParamHash['action_log_store']['last_modified'] = $gBitSystem->getUTCTime();

		// the log message
		$log_message = '';
		if( empty( $pParamHash['action_log']['log_message'] ) && !empty( $this ) && !empty( $this->mLogs ) ) {
			foreach( $this->mLogs as $key => $msg ) {
				$log_message .= "$msg\n";
			}
		} elseif( !empty( $pParamHash['action_log']['log_message'] ) ) {
			$log_message = $pParamHash['action_log']['log_message'];
		}

		// trim down log
		if( !empty( $log_message ) ) {
			$pParamHash['action_log_store']['log_message'] = substr( $log_message, 0, 250 );
		}

		// error message - default is to put in any stuff in mErrors
		$error_message = '';
		if( empty( $pParamHash['action_log']['error_message'] ) && !empty( $this ) && !empty( $this->mErrors ) ) {
			foreach( $this->mErrors as $key => $msg ) {
				$error_message .= "$msg\n";
			}
		} elseif( !empty( $pParamHash['action_log']['error_message'] ) ) {
			$error_message = $pParamHash['action_log']['error_message'];
		}

		// trim down error message
		if( !empty( $error_message ) ) {
			$pParamHash['action_log_store']['error_message'] = substr( $error_message, 0, 250 );
		}

		if( empty( $pParamHash['action_log_store']['error_message'] ) && empty( $pParamHash['action_log_store']['log_message'] )) {
			$ret = FALSE;
		}

		// if we get as far as here, we can
		return $ret;
	}

	/**
	 * Get a list of action log entries
	 * 
	 * @param array $pListHash List options
	 * @access public
	 * @return List of entries on success, FALSE on failure
	 */
	function getActionLogs( &$pListHash ) {
		LibertyContent::prepGetList( $pListHash );

		$ret = $bindVars = array();
		$selectSql = $joinSql = $orderSql = $whereSql = '';

		if( !empty( $pListHash['find'] ) ) {
			$whereSql .= empty( $whereSql ) ? ' WHERE ' : ' AND ';
			$whereSql .= " UPPER( lal.`log_message` ) LIKE ? ";
			$bindVars[] = '%'.strtoupper( $pListHash['find'] ).'%';
		}

		if( !empty( $pListHash['find_title'] ) ) {
			$whereSql .= empty( $whereSql ) ? ' WHERE ' : ' AND ';
			$whereSql .= " UPPER( lal.`title` ) LIKE ? ";
			$bindVars[] = '%'.strtoupper( $pListHash['find_log'] ).'%';
		}

		if( !empty( $pListHash['sort_mode'] ) ) {
			$orderSql = " ORDER BY ".$this->mDb->convertSortmode( $pListHash['sort_mode'] )." ";
		}

		$query = "
			SELECT lal.*,
				lc.`content_type_guid`, lc.`created`, lct.`content_description`,
				uue.`login` AS modifier_user, uue.`real_name` AS modifier_real_name
			FROM `".BIT_DB_PREFIX."liberty_action_log` lal
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = lal.`content_id` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_types` lct ON ( lct.`content_type_guid` = lc.`content_type_guid` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."users_users` uue ON ( uue.`user_id` = lal.`user_id` )
			$whereSql $orderSql";

		$result = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] );

		while( $aux = $result->fetchRow() ) {
			$aux['user']         = $aux['modifier_user'];
			$aux['editor']       = ( isset( $aux['modifier_real_name'] ) ? $aux['modifier_real_name'] : $aux['modifier_user'] );
			$aux['display_name'] = BitUser::getTitle( $aux );
			$ret[]               = $aux;
		}

		$query = "SELECT COUNT( lal.`user_id` ) FROM `".BIT_DB_PREFIX."liberty_action_log` lal $whereSql";
		$pListHash['cant'] = $this->mDb->getOne( $query, $bindVars );
		LibertyContent::postGetList( $pListHash );

		return $ret;
	}

	/**
	 * expungeActionLog 
	 * 
	 * @param array $pTimeSpan Anything older than this timespan will be removed
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function expungeActionLog( $pTimeSpan = NULL ) {
		global $gBitSystem;
		$where = '';
		$bindVars = array();
		if( @BitBase::verifyId( $pTimeSpan ) ) {
			$where = "WHERE `last_modified` < ?";
			$bindVars[] = $gBitSystem->mServerTimestamp->getUTCTime() - $pTimeSpan;
		}
		$this->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."liberty_action_log` $where", $bindVars );
		return TRUE;
	}

	/**
	 * getContentStatus
	 * 
	 * @access public
	 * @return an array of content_status_id, content_status_names the current user can use on this content. Subclases may easily override with return LibertyContent::getContentStatus(-100, 0) for example to restrict to only hidden content types.
	 */
	function getContentStatus($pUserMinimum=-100, $pUserMaximum=100) {
		global $gBitUser;
		if ($gBitUser->hasPermission('p_liberty_edit_all_status')) {
			return( $this->mDb->getAssoc( "SELECT `content_status_id`,`content_status_name` FROM `".BIT_DB_PREFIX."liberty_content_status` ORDER BY `content_status_id`" ) );
		} else {
			return( $this->mDb->getAssoc( "SELECT `content_status_id`, `content_status_name` FROM `".BIT_DB_PREFIX."liberty_content_status` WHERE `content_status_id` > ? AND `content_status_id` < ? ORDER BY `content_status_id`", array($pUserMinimum, $pUserMaximum)));
		}
	}

	function isDeleted() {
		global $gBitSystem;
		return( $this->getField( 'content_status_id' ) <= $gBitSystem->getConfig( 'liberty_status_deleted', -999 ) );
	}

	function isPrivate() {
		global $gBitSystem;
		return( $this->getField( 'content_status_id' ) <= $gBitSystem->getConfig( 'liberty_status_threshold_private', -40 ) );
	}

	function isProtected() {
		global $gBitSystem;
		return( $this->getField( 'content_status_id' ) <= $gBitSystem->getConfig( 'liberty_status_threshold_protected', -20 ) );
	}

	function isHidden() {
		global $gBitSystem;
		return( $this->getField( 'content_status_id' ) <= $gBitSystem->getConfig( 'liberty_status_threshold_hidden', -10 ) );
	}


	function storeStatus( $pContentStatusId ) {
		if( $this->isValid() && $pContentStatusId ) {
			$this->mDb->query( "UPDATE `".BIT_DB_PREFIX."liberty_content` SET `content_status_id`=? WHERE `content_id`=?", array( $pContentStatusId, $this->mContentId ) );
		}
	}

	/**
	 * getPreview -- Returns a string with a preview of the content. Default implementation runs getRenderFile() with preview set in the context and gBitSystem set to only render the content.
	 *
	 * @access public
	 * @return the preview string
	 **/
	function getPreview() {
		global $gBitSystem, $gContent, $gBitSmarty;
		// Tell gBitSystem not to do modules and such
		$gBitSystem->onlyRenderContent();
		// Tell the content we are previewing (in case they care)
		$gBitSmarty->assign('preview', true);
		// Save current gContent
		$oldGContent = $gContent;
		// Make us the content
		$gContent = $this;

		$ret = get_include_contents($this->getRenderFile());

		// Return gBitSystem to full render mode
		$gBitSystem->onlyRenderContent(false);
		// Clear the preview flag
		$gBitSmarty->assign('preview', false);
		// Restore gContent
		$gContent = $oldGContent;

		return $ret;
	}

}
?>
