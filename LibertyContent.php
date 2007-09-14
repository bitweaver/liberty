<?php
/**
* Management of Liberty content
*
* @package  liberty
* @version  $Header: /cvsroot/bitweaver/_bit_liberty/LibertyContent.php,v 1.289 2007/09/14 07:02:51 squareing Exp $
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

define( 'LIBERTY_SPLIT_REGEX', "!\.{3}split\.{3}[\t ]*\n?!" );

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
	* initialize to null, loadPermissions will set to empty array if nothing present, and this is used to prevent subsequent SQL statements
	* @public
	*/
	var $mPerms = NULL;
	/**
	* Preferences hash specific to this LibertyContent object - accessed via getPreference/storePreference
	* @private
	*/
	var $mPrefs = NULL;
	/**
	* Control permission specific to this LibertyContent type
	* @private
	*/
	var $mViewContentPerm;
	var $mEditContentPerm;
	var $mAdminContentPerm;

	/**
	* Construct an empty LibertyBase object with a blank permissions array
	*/
	function LibertyContent () {
		LibertyBase::LibertyBase();
		$this->mPrefs = NULL; // init to NULL so getPreference can determine if a load is necessary
		$this->mPerms = NULL; // init to NULL so loadPermissions can determine if a sql call is necessary

		// NOTE: we are not assigning anything to mViewContentPerm. if this is empty, we will return TRUE in hasViewPermission()
		if( empty( $this->mEditContentPerm )) {
			$this->mEditContentPerm = 'p_admin_content';
		}

		if( empty( $this->mAdminContentPerm )) {
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

		if( @BitBase::verifyId( $pParamHash['content_id'] )) {
			$pParamHash['content_store']['content_id'] = $pParamHash['content_id'];
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
		if( !empty( $pParamHash['event_time'] ) ) {
			$pParamHash['content_store']['event_time'] = $pParamHash['event_time'];
		}

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
			$pParamHash['format_guid'] = $gBitSystem->getConfig( 'default_format', 'tikiwiki' );
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

		if( !empty( $pParamHash['content_store']['data'] )) {
			$this->filterData( $pParamHash['content_store']['data'], $pParamHash['content_store'], 'prestore' );
		} else {
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
				// make sure some variables are stuff in case services need getObjectType, mContentId, etc...
				$this->mContentId = $pParamHash['content_id'] = $pParamHash['content_store']['content_id'] = $this->mDb->GenID( 'liberty_content_id_seq' );
				$this->mContentTypeGuid = $this->mInfo['content_type_guid'] = $pParamHash['content_type_guid'];
				$result = $this->mDb->associateInsert( $table, $pParamHash['content_store'] );
				$this->mLogs['content_store'] = "Created";
			} else {
				if( !empty( $pParamHash['content_store']['title'] ) && !empty( $this->mInfo['title'] ) && $pParamHash['content_store']['title'] != $this->mInfo['title'] ) {
					$this->mLogs['rename_page'] = "Renamed from {$this->mInfo['title']} to {$pParamHash['content_store']['title']}.";
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
			if( !empty( $pParamHash['content_store']['data'] )) {
				if( $func = $gLibertySystem->getPluginFunction( $pParamHash['format_guid'], 'store_function' ) ) {
					$ret = $func( $pParamHash );
				}

				// post store filter - this is needed to deal with filters that need the content_id on the first save
				$this->filterData( $pParamHash['content_store']['data'], $pParamHash['content_store'], 'poststore' );
			}
			LibertyContent::expungeCacheFile( $pParamHash['content_id'] );

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

			// services, filters and cache
			$this->invokeServices( 'content_expunge_function', $this );
			if( $func = $gLibertySystem->getPluginFunction( $this->getField( 'format_guid' ), 'expunge_function' ) ) {
				$func( $this->mContentId );
			}
			$this->filterData( $this->mInfo['data'], $this->mInfo, 'expunge' );
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
		global $gBitSystem;

		$ret = FALSE;
		if( $this->isValid() ) {
			// Ensure that edit_comment and description don't run over
			if ( ($edit_comment = $this->getField('edit_comment')) != NULL ) {
				$edit_comment = substr($this->getField( 'edit_comment' ), 0, 200);
			}
			if ( ($description = $this->getField('description')) != NULL ) {
				$description = substr($this->getField( 'description' ), 0, 200);
			}
			// Ensure that format_guid is defaulted properly
			if(( $format_guid = $this->getField( 'format_guid' )) == NULL ) {
				$format_guid = $gBitSystem->getConfig( 'default_format', 'tikiwiki' );
			}
			$query = "insert into `".BIT_DB_PREFIX."liberty_content_history` ( `content_id`, `version`, `last_modified`, `user_id`, `ip`, `history_comment`, `data`, `description`, `format_guid`) values(?,?,?,?,?,?,?,?,?)";
			$result = $this->mDb->query( $query, array( $this->mContentId, (int)$this->getField( 'version' ), (int)$this->getField( 'last_modified' ) , $this->getField( 'modifier_user_id' ), $this->getField( 'ip' ),  $edit_comment, $this->getField( 'data' ), $description, $format_guid ) );
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
			$data = array();
			while( !$result->EOF ) {
				$aux = $result->fields;
				$aux['creator'] = (isset( $aux['creator_real_name'] ) ? $aux['creator_real_name'] : $aux['creator_user'] );
				$aux['editor'] = (isset( $aux['modifier_real_name'] ) ? $aux['modifier_real_name'] : $aux['modifier_user'] );
				$data[] = $aux;
//				array_push( $ret, $aux );
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
// Temporary patch to get a $pListHash array for the output
// this needs to be tidied on the input side
		$pListHash = array();
		$pListHash["data"] = $data;
		$pListHash["cant"] = $cant;
		$pListHash["max_records"] = $max_records;
		$pListHash["offset"] = $pOffset;
		$pListHash["find"] = NULL;
		$pListHash["sort_mode"] = NULL;
		LibertyContent::postGetList( $pListHash );
		return $pListHash;
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

	/**
	 * Load all permissions assigned to a given object. This is not for general consumption.
	 * This funtions sole purpose is for displaying purposes. if you want to get all permissions
	 * assigned to a given object use LibertyContent::loadPermissions();
	 * 
	 * @access public
	 */
	function getContentPermissionsList() {
		global $gBitUser;
		$ret = FALSE;
		if( $this->isValid() ) {
			$query = "
				SELECT lcperm.`perm_name`, lcperm.`is_revoked`, ug.`group_id`, ug.`group_name`, up.`perm_desc`
				FROM `".BIT_DB_PREFIX."liberty_content_permissions` lcperm
					INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON( lcperm.`group_id`=ug.`group_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."users_permissions` up ON( up.`perm_name`=lcperm.`perm_name` )
				WHERE lcperm.`content_id` = ?";
			$perms = $this->mDb->getAll( $query, array( $this->mContentId ));
			foreach( $perms as $perm ) {
				$ret[$perm['group_id']][$perm['perm_name']] = $perm;
			}
		}
		return $ret;
	}

	/**
	 * Get a list of content with permissions
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getContentWithPermissionsList() {
		global $gBitSystem;
		$ret = array();
		$query = "
			SELECT lcperm.`perm_name`, lc.`title`, lc.`content_id`, lc.`content_type_guid`, lcperm.`is_revoked`, ug.`group_id`, ug.`group_name`, up.`perm_desc`
			FROM `".BIT_DB_PREFIX."liberty_content_permissions` lcperm
				INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON( lcperm.`group_id`=ug.`group_id` )
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON( lcperm.`content_id`=lc.`content_id` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."users_permissions` up ON( up.`perm_name`=lcperm.`perm_name` )
			ORDER BY ".$gBitSystem->mDb->convertSortmode( 'content_type_guid_asc' ).", ".$gBitSystem->mDb->convertSortmode( 'title_asc' );
		$perms = $gBitSystem->mDb->getAll( $query );
		foreach( $perms as $perm ) {
			$ret[$perm['content_type_guid']][$perm['content_id']][] = $perm;
		}
		return $ret;
	}

	/**
	 * Expunge Object Permissions 
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function expungeContentPermissions() {
		$ret = FALSE;
		if( $this->isValid() ) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_content_permissions` WHERE `content_id` = ?";
			$ret = $this->mDb->query( $query, array( $this->mContentId ));
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
			$this->mPerms = NULL;
		}
		if( $this->isValid() && is_null( $this->mPerms ) ) {
			$query = "
				SELECT lcperm.`perm_name`, lcperm.`is_revoked`, ug.`group_id`, ug.`group_name`, up.`perm_desc`
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
	function hasUserPermission( $pPermName, $pVerifyAccessControl=TRUE, $pCheckGlobalPerm=TRUE ) {
		global $gBitUser;
		$ret = FALSE;
		if( !$gBitUser->isRegistered() || !( $ret = $this->isOwner() || $ret = $gBitUser->isAdmin() )) {
			if( $gBitUser->isAdmin() || $gBitUser->hasPermission( $this->mAdminContentPerm )) {
				$ret = TRUE;
			} else {
				if( $pVerifyAccessControl ) {
					$this->verifyAccessControl();
				}
				if( $this->loadPermissions() ) {
					// this content has assigned perms

					// make a copy of the user's global perms
					$checkPerms = $this->getUserPermissions( $gBitUser->mUserId );
					$ret = !empty( $checkPerms[$this->mAdminContentPerm] ) || !empty( $checkPerms[$pPermName] ); // && ( $checkPerms[$pPermName]['user_id'] == $gBitUser->mUserId );
				} elseif( $pCheckGlobalPerm ) {
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
	function hasAdminPermission( $pVerifyAccessControl=TRUE ) {
		return( $this->hasUserPermission( $this->mAdminContentPerm, $pVerifyAccessControl ) );
	}

	// === verifyAdminPermission
	/**
	* This code was duplicated _EVERYWHERE_ so here is an easy template to cut that down.
	* It will verify if a given user has a given $permission and if not, it will display the error template and die()
	* @param $pVerifyAccessControl check access control service if available
	* @return TRUE if permitted, method will fatal out if not
	* @access public
	*/
	function verifyAdminPermission( $pVerifyAccessControl=TRUE ) {
		global $gBitSystem;
		if( $this->hasAdminPermission( $pVerifyAccessControl ) ) {
			return TRUE;
		} else {
			$gBitSystem->fatalPermission( $this->mAdminContentPerm );
		}
	}

	/**
	* Determine if current user has the ability to edit this type of content
	*
	* @return bool True if user has this type of content administration permission
	*/
	function hasEditPermission( $pVerifyAccessControl=TRUE ) {
		return( $this->hasAdminPermission( $pVerifyAccessControl ) || $this->hasUserPermission( $this->mEditContentPerm, $pVerifyAccessControl, FALSE ) || $this->isOwner() );
	}

	// === verifyEditPermission
	/**
	* This code was duplicated _EVERYWHERE_ so here is an easy template to cut that down.
	* It will verify if a given user has a given $permission and if not, it will display the error template and die()
	* @param $pVerifyAccessControl check access control service if available
	* @return TRUE if permitted, method will fatal out if not
	* @access public
	*/
	function verifyEditPermission( $pVerifyAccessControl=TRUE ) {
		global $gBitSystem;
		if( $this->hasEditPermission( $pVerifyAccessControl ) ) {
			return TRUE;
		} else {
			$gBitSystem->fatalPermission( $this->mEditContentPerm );
		}
	}

	/**
	* Determine if current user has the ability to view this type of content
	* Note that this will always return TRUE if you haven't set the mViewContentPerm in your class
	*
	* @return bool True if user has this type of content administration permission
	*/
	function hasViewPermission( $pVerifyAccessControl=TRUE ) {
		return( $this->hasEditPermission( $pVerifyAccessControl ) || empty( $this->mViewContentPerm ) || $this->hasUserPermission( $this->mViewContentPerm, $pVerifyAccessControl ));
	}

	// === verifyViewPermission
	/**
	* This code was duplicated _EVERYWHERE_ so here is an easy template to cut that down.
	* It will verify if a given user has a given $permission and if not, it will display the error template and die()
	* @param $pVerifyAccessControl check access control service if available
	* @return TRUE if permitted, method will fatal out if not
	* @access public
	*/
	function verifyViewPermission( $pVerifyAccessControl=TRUE ) {
		global $gBitSystem;
		if( $this->hasViewPermission( $pVerifyAccessControl ) ) {
			return TRUE;
		} else {
			$gBitSystem->fatalPermission( $this->mViewContentPerm );
		}
	}


	/**
	* Get specific permissions for the specified user for this content
	*
	* @param integer Id of user for whom permissions are to be loaded
	* @return array Array of all permissions for the current user joined with perms for the current content. This should handle cases where non-default permissions is assigned, default permission is removed, and duplicate default permissions where one group's perm is revoked, but another is still permitted. If the permission is revoked, is_revoked will be set to 'y'
	*/
	function getUserPermissions( $pUserId ) {
		// cache this out to a static hash to reduce query load
		static $sUserPerms = array();
		$ret = array();

		if( !isset( $sUserPerms[$pUserId][$this->mContentId] )) {
//$startTime = microtime(); 

			// get the default permissions for specified user
			$query = "SELECT ugp.`perm_name` as `hash_key`, ugp.* 
					  FROM `".BIT_DB_PREFIX."users_groups_map` ugm
						LEFT JOIN `".BIT_DB_PREFIX."users_group_permissions` ugp ON(ugm.`group_id`=ugp.`group_id`) 
						LEFT JOIN `".BIT_DB_PREFIX."liberty_content_permissions` lcp ON(lcp.`group_id`=ugm.`group_id` AND lcp.`content_id`=?) 
					  WHERE (ugm.`user_id`=? OR ugm.`user_id`=?) AND lcp.perm_name IS NULL";
			$defaultPerms = $this->mDb->getAssoc( $query, array( $this->mContentId, $pUserId, ANONYMOUS_USER_ID ) );

			$query = "SELECT lcp.`perm_name` AS `hash_key`, lcp.* 
					  FROM `".BIT_DB_PREFIX."liberty_content_permissions` lcp 
						INNER JOIN `".BIT_DB_PREFIX."users_groups_map` ugm ON(lcp.group_id=ugm.group_id) 
						LEFT JOIN `".BIT_DB_PREFIX."users_group_permissions` ugp ON(ugm.group_id=ugp.group_id AND ugp.group_id!=lcp.group_id AND ugp.perm_name=lcp.perm_name) 
					  WHERE lcp.content_id=? AND (ugm.user_id=? OR ugm.user_id=?) AND lcp.is_revoked IS NULL";
			$nonDefaultPerms = $this->mDb->getAssoc( $query, array( $this->mContentId, $pUserId, ANONYMOUS_USER_ID ) );

			$ret = array_merge( $defaultPerms, $nonDefaultPerms );
//vd ( "exec time ".(microtime() - $startTime) );

			/*
			 * this has to work in the following conditions - result in () as viewed by registered user:
			 *        - no global p_wiki_page_view and we assign it to anon (TRUE), registered (TRUE) or editors (FALSE)
			 *        - anon have p_wiki_page_view and we re-assign it to registered (TRUE)
			 *        - anon have p_wiki_page_view and we re-assign it to editors (FALSE)
			 *        - registered have p_wiki_page_view and we re-assign it to editors (FALSE)
			 *        - registered have p_wiki_page_view and we re-assign it to anon (TRUE)
			 *        - editors have p_wiki_page_view and we (re-)assing it to anon (TRUE) or registered (TRUE)
			 *        - anon and registed have p_wiki_page_view and we unassign from anon (TRUE), registered (TRUE) and both (FALSE)
			 */
			/* previous query did not provide above functionality
			$query = "SELECT lcperm.`perm_name`, lcperm.`is_revoked`,ug.`group_id`, ug.`group_name`, ugm.`user_id`
                    FROM `".BIT_DB_PREFIX."liberty_content_permissions` lcperm
                        INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON( lcperm.`group_id`=ug.`group_id` )
                        INNER JOIN `".BIT_DB_PREFIX."users_groups_map` ugm ON( ugm.`group_id`=ug.`group_id` )
                    WHERE (ugm.`user_id`=? OR ugm.`user_id`=?) AND lcperm.`content_id` = ?
                    ORDER BY lcperm.`is_revoked`"; // order by is_revoked so null's come first and last to be checked will be 'y'
            $bindVars = array( $this->mContentId, $pUserId, ANONYMOUS_USER_ID );
            $sUserPerms[$pUserId][$this->mContentId] = $this->mDb->getAssoc( $query, $bindVars );
			 */
/*
$startTime = microtime(); 
			$query = "SELECT up.`perm_name`, up.`perm_desc`, up.`perm_level`, up.`package`, ugp.`group_id`
					  FROM `".BIT_DB_PREFIX."users_permissions` up
						INNER JOIN `".BIT_DB_PREFIX."users_group_permissions` ugp ON ( ugp.`perm_name`=up.`perm_name` )
						INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON ( ug.`group_id`=ugp.`group_id` )
					    LEFT OUTER JOIN `".BIT_DB_PREFIX."users_groups_map` ugm ON ( ugm.`group_id`=ugp.`group_id` AND ugm.`user_id` = ? )
					  WHERE ug.`group_id`= ".ANONYMOUS_GROUP_ID." OR ugm.`group_id`=ug.`group_id`";
			$userPerms = $this->mDb->getAll( $query, array( $gBitUser->mUserId ) );

			// remove revoked permissions
			foreach( $this->mPerms as $perm ) {
				if( $perm['is_revoked'] == 'y' ) {
					foreach( $userPerms as $uk => $up ) {
						if( $up['perm_name'] == $perm['perm_name'] && $up['group_id'] == $perm['group_id'] ) {
							unset( $userPerms[$uk] );
						}
					}
				} else {
					$userPerms[] = $perm;
				}
			}

			// merge data that it looks like an updated version of user permissions
			foreach( $userPerms as $perm ) {
				if( in_array( $perm['group_id'], array_keys( $gBitUser->mGroups ))) {
					$ret[$perm['perm_name']] = $perm;
				}
			}
vd ( "exec time ".(microtime() - $startTime) );
vd( $ret );
*/
			$sUserPerms[$pUserId][$this->mContentId] = $ret;
		}

		return $sUserPerms[$pUserId][$this->mContentId];
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
	function storePermission( $pGroupId, $pPermName, $pIsRevoked=FALSE ) {
		$ret = FALSE;
		if( @BitBase::verifyId( $pGroupId ) && !empty( $pPermName ) && $this->isValid() ) {
			$this->removePermission( $pGroupId, $pPermName );
			$storeHash = array(
				'group_id' => $pGroupId,
				'perm_name' => $pPermName,
				'content_id' => $this->mContentId,
			);
			// check to see if this is an exclusion
			if( $pIsRevoked ) {
				$storeHash['is_revoked'] = 'y';
			}
			$ret = $this->mDb->associateInsert( BIT_DB_PREFIX."liberty_content_permissions", $storeHash );
		}
		return $ret;
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
		return TRUE;
	}

	/**
	 * Check to see if this permission is already in the global permissions table.
	 * 
	 * @param array $pGroupId 
	 * @param array $pPermName 
	 * @access public
	 * @return TRUE if present, FALSE if not
	 */
	function isExcludedPermission( $pGroupId, $pPermName ) {
		if( @BitBase::verifyId( $pGroupId ) && !empty( $pPermName )) {
			$query = "SELECT `perm_name` FROM `".BIT_DB_PREFIX."users_group_permissions` WHERE `group_id` = ? AND `perm_name` = ?";
			return( $this->mDb->getOne( $query, array( $pGroupId, $pPermName )) == $pPermName );
		}
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

	/**
	 * loadPreferences of the currently loaded object or pass in to get preferences of a specific content_id
	 * 
	 * @param numeric $pContentId content_id of the item we want the prefs from (optional)
	 * @access public
	 * @return array of preferences if $pContentId is set or pass preferences on to $this->mPrefs
	 */
	function loadPreferences( $pContentId = NULL ) {
		global $gBitSystem;
		if( @BitBase::verifyId( $pContentId )) {
			return $gBitSystem->mDb->getAssoc( "SELECT `pref_name`, `pref_value` FROM `".BIT_DB_PREFIX."liberty_content_prefs` WHERE `content_id`=?", array( $pContentId ));
		} elseif( $this->isValid() ) {
			// If no results, getAssoc will return an empty array (ie not a true NULL value) so getPreference can tell we have attempted a load
			$this->mPrefs = @$this->mDb->getAssoc( "SELECT `pref_name`, `pref_value` FROM `".BIT_DB_PREFIX."liberty_content_prefs` WHERE `content_id`=?", array( $this->mContentId ));
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
			if( @BitBase::verifyId( $this->mContentId ) && (($gBitUser->isRegistered() && !$this->isOwner()) || ($gBitUser->getField('user_id') == ANONYMOUS_USER_ID)) && !$gBitUser->isAdmin() ) {
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
	* Not-so-pure virtual function that returns fully qualified URI to a piece of content
	* @param string Text for DisplayLink function
	* @param array different possibilities depending on derived class
	* @return string Formated URL address to display the page.
	*/
	function getDisplayUri( $pContentId=NULL, $pMixed=NULL ) {
		return BIT_ROOT_URI.substr( $this->getDisplayUrl( $pContentId, $pMixed ), 1 );
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
			$ret = NULL;
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
		return '';
	}


	/**
	 * Liberty override to stuff content_status_id and prepares parameters with default values for any getList function
	 * @param pParamHash hash of parameters for any getList() function
	 * @return the link to display the page.
	 */
	function prepGetList( &$pListHash ) {
		global $gBitUser;
		if( $gBitUser->isAdmin() ) {
			$pListHash['min_content_status_id'] = -9999;
		} elseif( !empty( $this ) && is_object( $this ) && $this->hasAdminPermission() ) {
			$pListHash['min_content_status_id'] = -999;
		} elseif( !empty( $this ) && is_object( $this ) && $this->hasEditPermission() ) {
			$pListHash['min_content_status_id'] = -99;
		} else {
			$pListHash['min_content_status_id'] = 1;
		}

		// if sort_mode is not set then use last_modified_desc
		if( !empty( $pListHash['sort_mode'] )) {
			if( is_string( $pListHash['sort_mode'] ) && strpos( $pListHash['sort_mode'], 'hits_' ) === 0 ) {
				// if sort mode is hits_*, then assume liberty content
				$pListHash['sort_mode'] = 'lch.'.$pListHash['sort_mode'];
			} elseif( is_array( $pListHash['sort_mode'] )) {
				foreach( $pListHash['sort_mode'] as $key => $mode ) {
					if( strpos( $mode, 'hits_' ) === 0 ) {
						$pListHash['sort_mode'][$key] = 'lch.'.$mode;
					}
				}
			}
		}

		return parent::prepGetList( $pListHash );
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
				$aux['content_description'] = $type['content_description'];
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
					if( $aux['content_type_guid'] == BITUSER_CONTENT_TYPE_GUID ) {
						// here we provide getDisplay(Link|Url) with user-specific information that we get the correct links to display in pages
						$userInfo = $gBitUser->getUserInfo( array( 'content_id' => $aux['content_id'] ));
						$aux['title']        = $type['content_object']->getTitle( $userInfo );
						$aux['display_link'] = $type['content_object']->getDisplayLink( $userInfo['login'], $userInfo );
						$aux['display_url']  = $type['content_object']->getDisplayUrl( $userInfo['login'] );
					} else {
						$aux['title']        = $type['content_object']->getTitle( $aux );
						$aux['display_link'] = $type['content_object']->getDisplayLink( $aux['title'], $aux );
						$aux['display_url']  = $type['content_object']->getDisplayUrl( $aux['content_id'], $aux );
					}

					if( !empty( $pListHash['thumbnail_size'] ) ) {
						$aux['content_object'] = new $type['handler_class']( NULL, $aux['content_id'] );
						if( $aux['content_object']->load() ) {
							$aux['thumbnail_url'] = $aux['content_object']->getThumbnailUrl( $pListHash['thumbnail_size'] );
						}
					}
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
	 * @return parsed data cut at LIBERTY_SPLIT_REGEX or at $pLength
	 */
	function parseSplit( $pParseHash, $pLength = 500, $pForceLength = FALSE ) {
		global $gLibertySystem, $gBitSystem;

		if( $pForceLength ) {
			$res['data'] = preg_replace( LIBERTY_SPLIT_REGEX, '', $res['data'] );
		}

		// Indicate that we are parsing split data. This will clean up the HTML better and avoid pre / post filters
		$pParseHash['split_parse'] = TRUE;

		// copy data that we can compare strings later on
		$res['data'] = $pParseHash['data'];

		// allways set the cache extension to description if it's not set manually
		$pParseHash['cache_extension'] = !empty( $pParseHash['cache_extension'] ) ? $pParseHash['cache_extension'] : 'desc';

		// split data according to user specifications
		if( preg_match( LIBERTY_SPLIT_REGEX, $res['data'] )) {
			// this has been manually split
			$res['man_split'] = TRUE;
			$parts = preg_split( LIBERTY_SPLIT_REGEX, $res['data'] );
			$pParseHash['data'] = $parts[0];
		} else {
			// Include length in cache file
			$pParseHash['cache_extension'] .= '.'.$pLength;
			$pParseHash['data'] = substr( $res['data'], 0, $pLength );
		}

		// set 'has_more' and remove cache_extension if we don't need it
		if( !( $res['has_more'] = ( $res['data'] != $pParseHash['data'] ))) {
			$pParseHash['cache_extension'] = NULL;
		}

		if( !empty( $pParseHash['data'] )) {
			// parse data and run it through postsplit filter
			if( $parsed = $this->parseData( $pParseHash )) {
				// parsing split content can break stuff so we remove trailing junk
				$res['parsed'] = $res['parsed_description'] = preg_replace( '!((<br\b[^>]*>)*\s*)*$!si', '', $parsed );

				// we append '...' when the split was generated automagically
				if( empty( $res['man_split'] ) && !empty( $res['has_more'] )) {
					$res['parsed_description'] .= '&hellip;';
				}
			}
		} else {
			// did we parse an empty page?
			$res['parsed'] = $res['parsed_description'] = '';
			$res['has_more'] = FALSE;
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
		$parseHash['content_id']      = !empty( $parseHash['content_id'] )      ? $parseHash['content_id']      : NULL;
		$parseHash['cache_extension'] = !empty( $parseHash['cache_extension'] ) ? $parseHash['cache_extension'] : NULL;
		$parseHash['format_guid']     = !empty( $parseHash['format_guid'] )     ? $parseHash['format_guid']     : $pFormatGuid;

		// Ensure we have a format
		if( empty( $parseHash['format_guid'] )) {
			$parseHash['format_guid'] = $gBitSystem->getConfig( 'default_format', 'tikiwiki' );
		}

		$ret = NULL;
		// Handle caching if it is enabled.
		if( $gBitSystem->isFeatureActive( 'liberty_cache' ) && !empty( $parseHash['content_id'] ) && empty( $parseHash['no_cache'] ) ) {
			if( $cacheFile = LibertyContent::getCacheFile( $parseHash['content_id'], $parseHash['cache_extension'] ) ) {
				// Attempt to read cache file
				if( !( $ret = LibertyContent::readCacheFile( $cacheFile ))) {
					// failed to read from cache.
					$parseAndCache = TRUE;
				} else {
					// Note that we read from cache.
					$this->mInfo['is_cached'] = TRUE;
				}
			}
		}

		// if $ret is empty, we haven't read anything from cache yet - we need to parse the raw data
		if( empty( $ret ) || !empty( $parseAndCache )) {
			if( !empty( $parseHash['data'] ) && $parseHash['format_guid'] ) {
				$replace = array();
				// extract and protect ~pp~...~/pp~ and ~np~...~/np~ sections
				parse_protect( $parseHash['data'], $replace );

				// some few filters such as stencils need to be before the data plugins
				LibertyContent::filterData( $parseHash['data'], $parseHash, 'preplugin' );

				// this will handle all liberty data plugins like {code} and {attachment} usage in all formats
				parse_data_plugins( $parseHash['data'], $replace, $this, $parseHash );

				// pre parse filter according to what we're parsing - split or full body
				$filter = empty( $parseHash['split_parse'] ) ? 'parse' : 'split';
				LibertyContent::filterData( $parseHash['data'], $parseHash, 'pre'.$filter );

				if( $func = $gLibertySystem->getPluginFunction( $parseHash['format_guid'], 'load_function' ) ) {
					// get the beast parsed
					if( $ret = $func( $parseHash, $this )) {
						// post parse filter
						LibertyContent::filterData( $ret, $parseHash, 'post'.$filter );

						// before we cache we insert the protected sections back - currently this is even after the filters.
						// this might not be ideal but it allows stuff like ~pp~{maketoc}~/pp~
						$replace = array_reverse( $replace );
						foreach( $replace as $rep ) {
							$ret = str_replace( $rep["key"], $rep["data"], $ret );
						}

						if( !empty( $parseAndCache )) {
							LibertyContent::writeCacheFile( $cacheFile, $ret );
						}
					}
				}
			}
		}

		return $ret;
	}

	/**
	 * filterData will apply one of the specified filter stages to the input data
	 * 
	 * @param array $pFilterHash array of data that should be filtered
	 * @param string $pFilterHash[data] is the actual data that needs to be filtered
	 * @param keyword $pFilterStage specify what filter stage the data is at: pre, post, presplit or postsplit
	 * @access public
	 * @return filtered data
	 */
	function filterData( &$pData, &$pFilterHash, $pFilterStage = 'preparse' ) {
		global $gLibertySystem;
		if( !empty( $pData ) && $filters = $gLibertySystem->getPluginsOfType( FILTER_PLUGIN )) {
			foreach( $filters as $guid => $filter ) {
				if( $gLibertySystem->isPluginActive( $guid ) && $func = $gLibertySystem->getPluginFunction( $guid, $pFilterStage.'_function' )) {
					$func( $pData, $pFilterHash, ( !empty( $this ) ? $this : NULL ));
				}
			}
		}
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
	function readCacheFile( $pCacheFile ) {
		global $gBitSystem;
		$ret = NULL;
		if( is_file( $pCacheFile ) && ( time() - filemtime( $pCacheFile )) < $gBitSystem->getConfig('liberty_cache') && filesize( $pCacheFile ) > 0 ) {
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
	function writeCacheFile( $pCacheFile, $pData ) {
		// Cowardly refuse to write nothing.
		if( !empty( $pData )) {
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

	/**
	 * Delete liberty cache
	 * 
	 * @param array $pContentId 
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function expungeCache() {
		global $gBitSystem;
		$ret = FALSE;
		if( $gBitSystem->isFeatureActive( 'liberty_cache' )) {
			$cacheDir = LibertyContent::getCacheBasePath();
			// make sure that we're in the temp dir at least
			if( strstr( $cacheDir, str_replace( '//', '/', TEMP_PKG_PATH ))) {
				unlink_r( $cacheDir );
				// make sure we have a usable cache directory to work with
				$ret = ( is_dir( $cacheDir ) || mkdir_p( $cacheDir ));
			}
		}
		return $ret;
	}

	// i think this function is not being used and will hopefully be removed soon - xing - Saturday Jul 07, 2007   19:54:02 CEST
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
	 * isCommentable will check allow_comments in mInfo or if it's set as a preference.
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function isCommentable() {
		if( $this->getPreference( 'allow_comments' ) == 'y' ) {
			return TRUE;
		} else {
			$setting = $this->getField( 'allow_comments' );
			return( $setting == TRUE || $setting == 'y' );
		}
	}

	/**
	 * getListingPreview -- Returns a string with a preview of the content. 
	 * @access public
	 * @return the preview string
	 **/
	function getListingPreview( $pMixed ) {
		global $gBitSystem, $gContent, $gBitSmarty;



		return $ret;
	}

	/**
	 * getPreview -- Returns a string with a preview of the content. Default implementation runs getRenderFile() with $liberty_preview set in the context and gBitSystem set to only render the content.
	 *
	 * @access public
	 * @return the preview string
	 **/
	function getPreview() {
		global $gBitSystem, $gContent, $gBitSmarty;
		// Tell gBitSystem not to do modules and such
		$gBitSystem->setFormatHeader( "center_only" );
		// Tell the content we are previewing (in case they care)
		$gBitSmarty->assign('liberty_preview', true);
		// Save current gContent
		$oldGContent = $gContent;
		// Make us the content
		$gContent = $this;

		$ret = get_include_contents($this->getRenderFile());

		// Return gBitSystem to full render mode
		$gBitSystem->setFormatHeader( "html" );
		// Clear the preview flag
		$gBitSmarty->assign('liberty_preview', false);
		// Restore gContent
		$gContent = $oldGContent;

		return $ret;
	}

}
?>
