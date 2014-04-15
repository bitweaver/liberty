<?php
/**
/* Management of Liberty content
*
* @package  liberty
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
	 *Permissions hash specific to the user accessing this LibertyContetn object
	 * @public
	 */
	var $mUserContentPerms;

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
	var $mUpdateContentPerm;
	var $mCreateContentPerm;
	var $mExpungeContentPerm;
	var $mAdminContentPerm;

	/**
	 * Construct an empty LibertyBase object with a blank permissions array
	 */
	function __construct() {
		parent::__construct();
		$this->mPrefs = NULL; // init to NULL so getPreference can determine if a load is necessary

		// NOTE: we are not assigning anything to mViewContentPerm. if this is empty, we will return TRUE in hasViewPermission()
		if( empty( $this->mUpdateContentPerm )) {
			$this->mUpdateContentPerm = 'p_admin_content';
		}

		if( empty( $this->mCreateContentPerm )) {
			$this->mCreateContentPerm = 'p_admin_content';
		}

		if( empty( $this->mExpungeContentPerm )) {
			$this->mExpungeContentPerm = 'p_admin_content';
		}

		if( empty( $this->mAdminContentPerm )) {
			$this->mAdminContentPerm = 'p_admin_content';
		}
	}

	public static function getCacheKey( $pCacheKeyUuid = '' ) {
		return parent::getCacheKey().'#'.$pCacheKeyUuid;
	}

	/**
	 * load Assume a derived class has joined on the liberty_content table, and loaded it's columns already.
	 *
	 * @access public
	 * @return void
	 */
	function load( $pContentId = NULL, $pPluginParams = NULL ) {
		if( !empty( $this->mInfo['content_type_guid'] )) {
			global $gLibertySystem, $gBitSystem, $gBitUser;
			$this->loadPreferences();
			$this->mInfo['content_type'] = $gLibertySystem->mContentTypes[$this->mInfo['content_type_guid']];
			$this->invokeServices( 'content_load_function', $this );
		}
	}

	/**
	 * Verify the core class data required to update the liberty_content table entries
	 *
	 * Verify will build an array [content_store] with all of the required values
	 * and populate it with the relevent data to create/update the liberty_content
	 * table record
	 *
	 * @param array $pParamHash Array of content data to be stored
	 *
	 * @param array $pParamHash[content_id]
	 * @param array $pParamHash[user_id]
	 * @param array $pParamHash[modifier_user_id]
	 * @param array $pParamHash[created]
	 * @param array $pParamHash[last_modified]
	 * @param array $pParamHash[content_type_guid]
	 * @param array $pParamHash[format_guid]
	 * @param array $pParamHash[last_hit]
	 * @param array $pParamHash[event_time]
	 * @param array $pParamHash[hits]
	 * @param array $pParamHash[lang_code]
	 * @param array $pParamHash[title]
	 * @param array $pParamHash[ip]
	 * @param array $pParamHash[edit]
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
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
				// Set a default status when creating if none is set
				// This may get overwritten below
				if( empty($pParamHash['content_store']['content_status_id'] ) ){
						$pParamHash['content_store']['content_status_id'] = $gBitSystem->getConfig('liberty_default_status', BIT_CONTENT_DEFAULT_STATUS);
				}
			} else {
				$pParamHash['content_id'] = $this->mContentId;
			}
		}

		if( @BitBase::verifyId( $pParamHash['content_id'] )) {
			$pParamHash['content_store']['content_id'] = $pParamHash['content_id'];
		}

		// Are we allowed to override owner?
		if( !empty($pParamHash['owner_id'] ) ) {
			if( $gBitUser->isAdmin() || ($gBitSystem->isFeatureActive('liberty_allow_change_owner') && $gBitUser->hasPermission('p_liberty_edit_content_owner') && !empty($pParamHash['owner_id']) && !empty($pParamHash['current_owner_id']) && $pParamHash['owner_id'] != $pParamHash['current_owner_id']) ) {
				// If an owner is being set override user_id
				$pParamHash['content_store']['user_id'] = $pParamHash['owner_id'];
			}
		}

		// Do we need to change the status
		if (!empty($pParamHash['content_status_id'])) {
			if( $this->hasUserPermission( 'p_liberty_edit_content_status' ) || $gBitUser->hasUserPermission( 'p_liberty_edit_all_status') ) {
				$allStatus = $this->getAvailableContentStatuses();
				if (empty($allStatus[$pParamHash['content_status_id']])) {
					$this->mErrors['content_status_id'] = "No such status ID or permission denied.";
				} else {
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
		$pParamHash['data_store']['summary'] = !empty( $pParamHash['summary'] ) ? $pParamHash['summary'] : NULL ;

		// call verify service to see if any services have errors
		$this->invokeServices( 'content_verify_function', $pParamHash );

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
				//$action = "Created";
				//$mailEvents = 'wiki_page_changes';
			}

			$this->storeAliases( $pParamHash );

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

			// store data
			foreach( $pParamHash['data_store'] AS $dataType => $data ) {
				$this->storeData( $data, $dataType );
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
	 *
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function expungeComments() {
		require_once( LIBERTY_PKG_PATH.'LibertyComment.php' );
		// Delete all comments associated with this piece of content
		$query = "SELECT `comment_id` FROM `".BIT_DB_PREFIX."liberty_comments` WHERE `root_id` = ?";
		if( $commentIds = $this->mDb->getCol($query, array( $this->mContentId ) ) ) {
			foreach ($commentIds as $commentId) {
				$tmpComment = new LibertyComment($commentId);
				$tmpComment->expunge();
			}
		}
		return TRUE;
	}

	/**
	 * Delete content object and all related records
	 *
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function expunge() {
		global $gBitSystem, $gLibertySystem;
		$ret = FALSE;
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$this->expungeComments();

			// services, filters and cache
			$this->invokeServices( 'content_expunge_function', $this );
			if(  $this->getField( 'format_guid' ) && $func = $gLibertySystem->getPluginFunction( $this->getField( 'format_guid' ), 'expunge_function' ) ) {
				$func( $this->mContentId );
			}
			$this->filterData( $this->mInfo['data'], $this->mInfo, 'expunge' );
			LibertyContent::expungeCacheFile( $this->mContentId );

			// remove favorites - this probably should be a content_expunge_function in users
			$this->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."users_favorites_map` WHERE `favorite_content_id`=?", array( $this->mContentId ) );

			// remove entries in the history
			$this->expungeVersion();

			// Remove individual permissions for this object if they exist
			$query = "delete from `".BIT_DB_PREFIX."liberty_content_permissions` where `content_id`=?";
			$result = $this->mDb->query( $query, array( $this->mContentId ) );

			// Remove aliases
			$this->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."liberty_aliases` WHERE `content_id`=?", array( $this->mContentId ) );

			// Remove structures
			// it's not this simple. what about orphans? needs real work. :( xoxo - spider
//			$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_structures` WHERE `content_id` = ?";
//			$result = $this->mDb->query( $query, array( $this->mContentId ) );

			// Remove any queued data processing (images, movies, etc.)
			$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_process_queue` WHERE `content_id` = ?";
			$result = $this->mDb->query( $query, array( $this->mContentId ) );

			// Remove data
			$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_content_data` WHERE `content_id` = ?";
			$result = $this->mDb->query( $query, array( $this->mContentId ) );

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

	/**
	 * storeAliases will store aliases to a given content item
	 *
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function storeAliases( $pParamHash ) {
		$ret = FALSE;
		if( $this->isValid() && isset( $pParamHash['alias_string']) ) {
			$this->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."liberty_aliases` WHERE `content_id`=?", array( $this->mContentId ) );
			$trimmedAliases = trim( $pParamHash['alias_string'] );
			if( !empty( $trimmedAliases ) && $aliases = explode( "\n", $trimmedAliases ) ) {
				foreach( $aliases as $a ) {
					$this->mDb->query( "INSERT INTO `".BIT_DB_PREFIX."liberty_aliases` (`content_id`, `alias_title`) VALUES (?,?)", array( $this->mContentId, trim( $a ) ) );
				}
			}
			$ret = TRUE;
		}
		return $ret;
	}

	/**
	 * storeHistory will store the previous data into the history table for reference
	 *
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function storeHistory() {
		global $gBitSystem;

		$ret = FALSE;
		if( $this->isValid() ) {
			$storeHash = array(
				"content_id"      => $this->mContentId,
				"version"         => $this->getField( "version" ),
				"last_modified"   => $this->getField( "last_modified" ),
				"user_id"         => $this->getField( "modifier_user_id" ),
				"ip"              => $this->getField( "ip" ),
				"data"            => $this->getField( "data" ),
				"summary"         => $this->getField( "summary" ),
				"history_comment" => (string)substr( $this->getField( "edit_comment" ), 0, 200 ),
				"format_guid"     => $this->getField( "format_guid", $gBitSystem->getConfig( "default_format", "tikiwiki" )),
			);
			$this->mDb->associateInsert( BIT_DB_PREFIX."liberty_content_history", $storeHash );
			$ret = TRUE;
		}
		return( $ret );
	}

	/**
	 * Get count of the number of historic records for the page
	 *
	 * @access public
	 * @return count
	 */
	function getHistoryCount() {
		$ret = NULL;
		if( $this->isValid() ) {
			$query = "
				SELECT COUNT(*) AS `hcount`
				FROM `".BIT_DB_PREFIX."liberty_content_history`
				WHERE `content_id` = ?";
			$rs = $this->mDb->query($query, array($this->mContentId));
			$ret = $rs->fields['hcount'];
		}
		return $ret;
	}

	/**
	 * Get complete set of historical data in order to display a given wiki page version
	 *
	 * @param array $pVersion
	 * @param array $pUserId
	 * @param int $pOffset
	 * @param array $max_records
	 * @access public
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

			$query = "SELECT COUNT(*) AS `hcount`
					FROM `".BIT_DB_PREFIX."liberty_content_history`
					WHERE `content_id` = ?";
			$rs = $this->mDb->query($query, array($this->mContentId));
			$cant = $rs->fields['hcount'];

			# Check for offset out of range
			if ( $pOffset < 0 ) {
				$pOffset = 0;
			} elseif ( $pOffset > $cant ) {
				$lastPageNumber = ceil ( $cant / $max_records ) - 1;
				$pOffset = $max_records * $lastPageNumber;
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
				//array_push( $ret, $aux );
				$result->MoveNext();
			}
		}
		// Temporary patch to get a $pListHash array for the output
		// this needs to be tidied on the input side
		// TODO: update this to work like newer getList methods
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
	 *
	 * @param string $pComment
	 * @access public
	 * @return void
	 */
	function removeLastVersion( $pComment = '' ) {
		if( $this->isValid() ) {
			global $gBitSystem;
			$this->expungeCacheFile($this->mContentId);
			$query = "select * from `".BIT_DB_PREFIX."liberty_content_history` where `content_id`=? order by ".$this->convertSortMode("last_modified_desc");
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
			$result = $this->mDb->query( $query, array( $action, $this->mContentId, $t, ROOT_USER_ID, $_SERVER["REMOTE_ADDR"], $pComment ));
		}
	}

	/**
	 * Roll back to a specific version of a page
	 * @param pVersion Version number to roll back to
	 * @param pComment Comment text to be added to the action log
	 * @return TRUE if completed successfully
	 */
	function rollbackVersion( $pVersion, $pComment = '' ) {
		$ret = FALSE;
		if( $this->isValid() ) {
			global $gBitUser,$gBitSystem;
			$this->mDb->StartTrans();
			// JHT - cache invalidation appears to be handled by store function - so don't need to do it here
			$query = "select lch.*, lch.`user_id` AS modifier_user_id, lch.`data` AS `edit` from `".BIT_DB_PREFIX."liberty_content_history` lch where lch.`content_id`=? and lch.`version`=?";
			if( $res = $this->mDb->getRow($query,array( $this->mContentId, $pVersion ) ) ) {
				$res['edit_comment'] = 'Rollback to version '.$pVersion.' by '.$gBitUser->getDisplayName();
				if (!empty($pComment)) {
					$res['edit_comment'] .=": $pComment";
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
				$this->mDb->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
			}
		}
		return $ret;
	}

	/**
	 * Removes a specific version of a page
	 *
	 * @param pVersion Version number to roll back to
	 * @param pComment Comment text to be added to the action log
	 * @return TRUE if completed successfully
	 */
	function expungeVersion( $pVersion=NULL, $pComment = '' ) {
		global $gBitUser;
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
			$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_content_history` WHERE `content_id`=? $versionSql ";
			$result = $this->mDb->query( $query, $bindVars );
			if( $hasRows ) {
				global $gBitSystem;
				$action = "Removed version $pVersion";
				$t = $gBitSystem->getUTCTime();
				$query = "INSERT INTO `".BIT_DB_PREFIX."liberty_action_log` (`log_message`,`content_id`,`last_modified`,`user_id`,`ip`,`error_message`) VALUES (?,?,?,?,?,?)";
				$result = $this->mDb->query($query,array($action,$this->mContentId,$t,$gBitUser->mUserId,$_SERVER["REMOTE_ADDR"],$pComment));
				$ret = TRUE;
			}
			$this->mDb->CompleteTrans();
		}
		return $ret;
	}

	function exportList( $pList ) {
		$ret = array();
		$keys = array_merge( array( 'content_type_guid', 'title', 'uri', 'url', 'content_id' ), $this->invokeServices( 'content_export_keys_function', $pList ) );
		foreach( $pList as $key=>$hash ) {
			foreach( $keys as $field ) {
				if( isset( $hash[$field] ) ) {
					$ret[$key][$field] = $hash[$field];
				}
			}
			$ret[$key]['content_id'] = $hash['content_id'];
			$ret[$key]['date_created'] = date( DateTime::W3C, $hash['created'] );
			$ret[$key]['date_last_modified'] = date( DateTime::W3C, strtotime( $hash['last_modified'] ) );
		}
		return $ret;
	}

	/**
	 * Create an export hash from the data
	 *
	 * @access public
	 * @return export data
	 */
	function exportHash() {
		$ret = array();
		if( $this->isValid() ) {
			$ret = array(
				'type' => $this->getContentType(),
				'title'  	=> $this->getTitle(),
				'uri'        => $this->getDisplayUri(),
				'url'        => $this->getDisplayUrl(),
				'content_id' => $this->mContentId,
				'date_created' => date( DateTime::W3C, $this->getField('created') ),
				'date_last_modified' => date( DateTime::W3C, $this->getField('last_modified') ),
			);
		}
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
		return( @BitBase::verifyId( $user_id ) && $user_id != ANONYMOUS_USER_ID && $user_id == $gBitUser->mUserId );
	}

	/**
	 * Check if content matches content type GUID - must also be a valid content object, it will not work for generic content class
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
	function invokeServices( $pServiceFunction, &$pFunctionParam=NULL ) {
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
	 * check if a service is active for this content type
	 * requires package LCConfig
	 * provisional method until LCConfig package is integrated into the core
	 */
	function hasService( $pServiceGuid ){
		global $gBitSystem;
		$ret = TRUE; // we return true by default to preserve legacy service opperation which has no content type preferences

		if( $gBitSystem->isPackageActive( 'lcconfig' ) ){
			// LCConfig is a singleton class
			$LCConfig = LCConfig::getInstance();
			// LCConfig negates services by content type
			// if result is not 'n' then service should apply to this content type
			if( $LCConfig->getConfig( 'service_'.$pServiceGuid, $this->mContentTypeGuid ) == 'n' ){
				$ret = FALSE;
			}
		}

		return $ret;
	}


	/**
	 * check if a service is required for this content type
	 * requires package LCConfig
	 * provisional method until LCConfig package is integrated into the core
	 */
	function isServiceRequired( $pServiceGuid ){
		global $gBitSystem;
		$ret = TRUE; // we return true by default to preserve legacy service opperation which has no content type preferences

		if( $gBitSystem->isPackageActive( 'lcconfig' ) ){
			// LCConfig is a singleton class
			$LCConfig = LCConfig::getInstance();
			return ( $LCConfig->getConfig( 'service_'.$pServiceGuid, $this->mContentTypeGuid ) == 'required' );
		}

		return $ret;
	}


	/**
	 * Default liberty sql for joining a content object table to liberty.
	 * We are proposing a new way of building queries here where we build up everything in a hash with implicit AND over all
	 * where clauses and then do an array_merge and concatenation in a single function at the end. See convertQueryHash for details.
	 *
	 *	This is an example current, and would be invoked in getList
	 *   $queryHash = array('summary', 'users', 'hits', 'avatar', 'primary'), array('select' => array('sql' => $selectSql), 'join' => array('sql' => $joinSql), 'where' => array('sql' => $whereSql, 'var' => $bindVars ));
	 *	$this->getLibertySql( 'bp.`content_id`', $queryHash);
	 */
	function getLibertySql( $pJoinColumn, &$pQueryHash, $pJoins = NULL, $pServiceFunction = NULL, $pObject = NULL, $pParamHash = NULL ) {
		$pQueryHash['select']['sql'][] = "lc.*";
		$pQueryHash['join']['sql'][] = "
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON( lc.`content_id` = $pJoinColumn )";
		if( empty( $pJoins ) || in_array( 'summary', $pJoins )) {
			$pQueryHash['select']['sql'][] = "lcds.`data` AS `summary`";
			$pQueryHash['join']['sql'][] = "
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_data` lcds ON( lc.`content_id` = lcds.`content_id` AND lcds.`data_type` = ? )";
			$pQueryHash['join']['var'][] = 'summary';
		}
		if( empty( $pJoins ) || in_array( 'hits', $pJoins )) {
			$pQueryHash['select']['sql'][] = "lch.`hits`, lch.`last_hit`";
			$pQueryHash['join']['sql'][] = "
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON( lc.`content_id` = lch.`content_id` )";
		}
		if( empty( $pJoins ) || in_array( 'users', $pJoins )) {
			$pQueryHash['select']['sql'][] = "
				uu.`email` AS creator_email, uu.`login` AS creator_user, uu.`real_name` AS creator_real_name,
				uue.`email` AS modifier_email, uue.`login` AS modifier_user, uue.`real_name` AS modifier_real_name";
			$pQueryHash['join']['sql'][] = "
				INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON( uu.`user_id` = lc.`user_id` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."users_users` uue ON( uue.`user_id` = lc.`modifier_user_id` )";
		}
		if( empty( $pJoins ) || in_array( 'avatar', $pJoins )) {
			$pQueryHash['select']['sql'][] = "ulf.`file_name` AS `avatar_file_name`, ulf.`mime_type` AS `avatar_mime_type`, ula.`attachment_id` AS `avatar_attachment_id`";
			$pQueryHash['join']['sql'][] = "
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments` ula ON( uu.`user_id` = ula.`user_id` AND ula.`attachment_id` = uu.`avatar_attachment_id` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files` ulf ON( ulf.`file_id` = ula.`foreign_id` )";
		}
		if( empty( $pJoins ) || in_array( 'primary', $pJoins )) {
			$pQueryHash['select']['sql'][] = "pla.`attachment_id` AS `primary_attachment_id`, plf.`file_name` AS `primary_file_name`, plf.`mime_type` AS `primary_mime_type`";
			$pQueryHash['join']['sql'][] = "
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments` pla ON( pla.`content_id` = lc.`content_id` AND pla.`is_primary` = 'y' )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files` plf ON( plf.`file_id` = pla.`foreign_id` )";
		}

		if( !empty( $pServiceFunction )) {
			$this->getServicesSql2( $pServiceFunction, $pQueryHash, $pObject, $pParamHash );
		}
	}

	/**
	 * getServicesSql2
	 *
	 * @param array $pServiceFunction
	 * @param array $pQueryHash
	 * @param array $pObject
	 * @param array $pParamHash
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 * @TODO this function still contains legacy code.
	 * @TODO rename this function to getServicesSql has been weened out
	 */
	function getServicesSql2( $pServiceFunction, &$pQueryHash, $pObject = NULL, $pParamHash = NULL ) {
		global $gLibertySystem;
		if( $loadFuncs = $gLibertySystem->getServiceValues( $pServiceFunction ) ) {
			// TODO: clear out this legacy code
			$pQueryHash['service_select_sql'] = $pQueryHash['service_join_sql'] = $pQueryHash['service_where_sql'] = '';
			foreach( $loadFuncs as $func ) {
				if( function_exists( $func ) ) {
					if( !empty( $pObject ) && is_object( $pObject )) {
						$queryHash = $func( $pObject, $pParamHash );
					} else {
						$queryHash = $func( $this, $pParamHash );
					}

					// work out if we're using the old services sql method or the new one
					if( !empty( $queryHash['select'] ) || !empty( $queryHash['from'] ) || !empty( $queryHash['join'] ) || !empty( $queryHash['where'] )) {
						// we're using the new method
						$pQueryHash = array_merge_recursive( $pQueryHash, $queryHash );
					} else {
						// TODO: clean out this legacy code {{{
						// old method: warn the developer
						//deprecated( 'This service is still using the old LibertyContent::getServicesSql() method. Please update the service to use the new SQL hash method' );
						if( !empty( $queryHash['select_sql'] )) {
							$pQueryHash['service_select_sql'] .= $queryHash['select_sql'];
						}
						if( !empty( $queryHash['join_sql'] )) {
							$pQueryHash['service_join_sql'] .= $queryHash['join_sql'];
						}
						if( !empty( $queryHash['where_sql'] )) {
							$pQueryHash['service_where_sql'] .= $queryHash['where_sql'];
						}
						if( !empty( $queryHash['bind_vars'] )) {
							if ( is_array( $pQueryHash['service_bind_vars'] )) {
								$pQueryHash ['service_bind_vars']= array_merge( $pQueryHash['service_bind_vars'], $queryHash['bind_vars'] );
							} else {
								$pQueryHash['service_bind_vars'] = $queryHash['bind_vars'];
							}
						}
						// }}}
					}
				}
			}
		}
	}

	/**
	 * Convert a built up pQueryHash into a single query string and set of bind variables.
	 *
	 * A pQueryHash is an array with required keys select and from, and optional keys join, where and order.
	 * Each key other than order should be an array with an 'sql' key which points to an array with statements.
	 * Statements should not include the keywords to start them excluding join statements nor should they
	 * include trailing delimeters such as commas as the conversion adds these where required.
	 * All where statments are automatically ANDed together.
	 * Each key other than order can optionally have a 'vars' key which points to an array with bind variables.
	 * The order key can either be an array or a single value. convertSortmode is automatically called on each order
	 * statement and built into the ORDER BY clause with delimeters where required.
	 *
	 * @return Results come back in $pQueryHash['query'] $pQueryHash['bind_vars'] and $pQueryHash['query_count'] if requested
	 * @TODO this function still contains legacy code.
	 */
	function convertQueryHash( &$pQueryHash, $pCountQuery = FALSE ) {
		global $gBitSystem;

		// initiate some variables
		if( empty( $pQueryHash['query'] )) {
			$pQueryHash['query'] = '';
		}

		if( empty( $pQueryHash['query_count'] )) {
			$pQueryHash['query_count'] = '';
		}

		if( empty( $pQueryHash['bind_vars'] )) {
			$pQueryHash['bind_vars'] = array();
		}

		// Build up all the parts of the query
		$queryParts = array( 'select', 'from', 'join', 'where' );
		foreach( $queryParts as $part ) {
			if( !empty( $pQueryHash[$part] ) && !empty( $pQueryHash[$part]['sql'] )) {
				// Add the required keyword -- joins include their own
				if( $part != 'join' ) {
					$pQueryHash['query'] .= strtoupper( " $part " );
					if( $pCountQuery ) {
						$pQueryHash['query_count'] .= strtoupper( " $part " );
					}
				}

				// Add the count for the count query
				if( $pCountQuery && $part == 'select' ) {
					$pQueryHash['query_count'] .= 'COUNT( ';
				}

				$first = TRUE;
				foreach( $pQueryHash[$part]['sql'] as $sql ) {
					if( !$first ) {
						// WHERE clauses have an implicit AND over all terms
						if( $part == 'where' ) {
							$pQueryHash['query'] .= " AND ";
							if( $pCountQuery ) {
								$pQueryHash['query_count'] .= " AND ";
							}
						} elseif( $part == 'select' || $part == 'from' ) {
							$pQueryHash['query'] .= ", ";
							if( $pCountQuery ) {
								$pQueryHash['query_count'] .= ", ";
							}
						}
					} else {
						$first = FALSE;
					}

					$pQueryHash['query'] .= $sql;
					if( $pCountQuery ) {
						$pQueryHash['query_count'] .= $sql;
					}
				}

				// Close the count for the count query
				if( $pCountQuery && $part == 'select' ) {
					$pQueryHash['query_count'] .= ' )';
				}

				if( !empty( $pQueryHash[$part]['var'] )) {
					$pQueryHash['bind_vars'] = array_merge( $pQueryHash['bind_vars'], $pQueryHash[$part]['var'] );
				}
			}

			// TODO: clean out this legacy code {{{
			// append old style serivce sql arguments
			// since we don't allow bind_vars in the old services style, we can append everything here and then later on add the bind vars
			if( !empty( $pQueryHash['service_'.$part.'_sql'] )) {
				$pQueryHash['query'] .= $pQueryHash['service_'.$part.'_sql'];
				if( $pCountQuery ) {
					$pQueryHash['query_count'] .= $pQueryHash['service_'.$part.'_sql'];
				}
			}
			// }}}
		}

		// TODO: clean out this legacy code {{{
		// append legacy service bind vars
		if( !empty( $pQueryHash['service_bind_vars'] )) {
			$pQueryHash['bind_vars'] = array_merge( $pQueryHash['bind_vars'], $pQueryHash['service_bind_vars'] );
		}
		/// }}}

		// Order can be a single value or an array of values all of which get passed to convertSortmode
		if( !empty( $pQueryHash['order'] )) {
			if( is_array( $pQueryHash['order'] )) {
				$first = true;
				foreach( $pQueryHash['order'] as $order ) {
					if( !$first ) {
						$pQueryHash['query'] .= ', ';
					} else {
						$pQueryHash['query'] .= ' ORDER BY ';
						$first = false;
					}
					$pQueryHash['query'] .= $gBitSystem->mDb->convertSortmode( $order );
				}
			} else {
				$pQueryHash['query'] .= ' ORDER BY '.$gBitSystem->mDb->convertSortmode( $pQueryHash['order'] );
			}
		}
	}

	/**
	* Set up SQL strings for services used by the object
	* TODO: set this function deprecated and eventually nuke it
	*/
	function getServicesSql( $pServiceFunction, &$pSelectSql, &$pJoinSql, &$pWhereSql, &$pBindVars, $pObject = NULL, &$pParamHash = NULL ) {
		//deprecated( 'You package is calling the deprecated LibertyContent::getServicesSql() method. Please update your code to use LibertyContent::getLibertySql' );
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

	// -------------------------------- Content Permission Functions

	/**
	 * Check to see if the loaded content has individually assigned permissions
	 *
	 * @access public
	 * @return Number of custom assigned permissions set for the loaded content item
	 */
	function hasUserPermissions() {
		$ret = FALSE;
		if( $this->isValid() ) {
			$ret = $this->mDb->getOne( "SELECT COUNT(`perm_name`) FROM `".BIT_DB_PREFIX."liberty_content_permissions` WHERE `content_id` = ?", array( $this->mContentId ));
		}
		return $ret;
	}

	/**
	 * getContentPermissionsSql
	 *
	 * @param array $pPermName
	 * @param array $pSelectSql
	 * @param array $pJoinSql
	 * @param array $pWhereSql
	 * @param array $pBindVars
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getContentPermissionsSql( $pPermName, &$pSelectSql, &$pJoinSql, &$pWhereSql, &$pBindVars ) {
		global $gBitUser;
		if ( defined('ROLE_MODEL') ) {
			$pJoinSql .= "
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_permissions` lcperm ON (lc.`content_id`=lcperm.`content_id`)
				LEFT OUTER JOIN `".BIT_DB_PREFIX."users_roles_map` urm ON (urm.`role_id`=lcperm.`role_id`) ";
			$pWhereSql .= " OR (lcperm.perm_name=? AND (urm.user_id=? OR urm.user_id=?)) ";
		} else {
			$pJoinSql .= "
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_permissions` lcperm ON (lc.`content_id`=lcperm.`content_id`)
				LEFT OUTER JOIN `".BIT_DB_PREFIX."users_groups_map` ugm ON (ugm.`group_id`=lcperm.`group_id`) ";
			$pWhereSql .= " OR (lcperm.perm_name=? AND (ugm.user_id=? OR ugm.user_id=?)) ";
		}
		$pBindVars[] = $pPermName;
		$pBindVars[] = $gBitUser->mUserId;
		$pBindVars[] = ANONYMOUS_USER_ID;
	}

	/**
	 * getContentListPermissionsSql
	 *
	 * @param array $pPermName
	 * @param array $pSelectSql
	 * @param array $pJoinSql
	 * @param array $pWhereSql
	 * @param array $pBindVars
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getContentListPermissionsSql( $pPermName, &$pSelectSql, &$pJoinSql, &$pWhereSql, &$pBindVars ) {
		global $gBitUser;
		if ( defined('ROLE_MODEL') ) {
			$pJoinSql .= "
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_permissions` lcperm ON (lc.`content_id`=lcperm.`content_id`)
				LEFT OUTER JOIN `".BIT_DB_PREFIX."users_roles_map` urm ON (urm.`role_id`=lcperm.`role_id`) ";
			$pWhereSql .= " AND ( lcperm.perm_name IS NULL OR ( lcperm.perm_name=? AND urm.user_id=? AND ( (lcperm.is_revoked !=? OR lcperm.is_revoked IS NULL) OR lc.`user_id`=? ) ) )";
		} else {
			$pJoinSql .= "
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_permissions` lcperm ON (lc.`content_id`=lcperm.`content_id`)
				LEFT OUTER JOIN `".BIT_DB_PREFIX."users_groups_map` ugsm ON (ugsm.`group_id`=lcperm.`group_id`) ";
			$pWhereSql .= " AND ( lcperm.perm_name IS NULL OR ( lcperm.perm_name=? AND ugsm.user_id=? AND ( (lcperm.is_revoked !=? OR lcperm.is_revoked IS NULL) OR lc.`user_id`=? ) ) )";
		}
		$pBindVars[] = $pPermName;
		$pBindVars[] = $gBitUser->mUserId;
		$pBindVars[] = "y";
		$pBindVars[] = $gBitUser->mUserId;
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

			if( !empty( $pParamHash['role_id'] ) ) {
				$whereSql .= " AND lcperm.`role_id` = ? ";
				$bindVars[] = $pParamHash['role_id'];
			}

			$permWhereSql = '';
			$this->getContentPermissionsSql( $pParamHash['perm_name'], $selectSql, $joinSql, $permWhereSql, $bindVars );

			if( !empty( $whereSql ) ) {
				$whereSql = preg_replace( '/^[\s]*AND/', '  ', $whereSql );
			}

			$query = "SELECT COUNT(*)
					  FROM `".BIT_DB_PREFIX."liberty_content` lc  $joinSql
					  WHERE lc.`content_id`=? AND ( $whereSql $permWhereSql ) ";
			$ret = $this->mDb->getOne( $query, $bindVars );
		}
		return( !empty( $ret ) );
	}

	/**
	 * Load all permissions assigned to a given object.
	 * This function is mainly used to fetch a list of custom permissions of a given content item.
	 *
	 * @access public
	 */
	function getContentPermissionsList() {
		global $gBitUser;
		$ret = FALSE;
		if( $this->isValid() ) {
			if ( defined('ROLE_MODEL') ) {
				$query = "
					SELECT lcperm.`perm_name`, lcperm.`is_revoked`, ur.`role_id`, ur.`role_name`, up.`perm_desc`
					FROM `".BIT_DB_PREFIX."liberty_content_permissions` lcperm
						INNER JOIN `".BIT_DB_PREFIX."users_roles` ur ON( lcperm.`role_id`=ur.`role_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."users_permissions` up ON( up.`perm_name`=lcperm.`perm_name` )
					WHERE lcperm.`content_id` = ?";
				$team = 'role_id';
			} else {
				$query = "
					SELECT lcperm.`perm_name`, lcperm.`is_revoked`, ug.`group_id`, ug.`group_name`, up.`perm_desc`
					FROM `".BIT_DB_PREFIX."liberty_content_permissions` lcperm
						INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON( lcperm.`group_id`=ug.`group_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."users_permissions` up ON( up.`perm_name`=lcperm.`perm_name` )
					WHERE lcperm.`content_id` = ?";
				$team = 'group_id';
			}
			$perms = $this->mDb->getAll( $query, array( $this->mContentId ));
			foreach( $perms as $perm ) {
				$ret[$perm[$team]][$perm['perm_name']] = $perm;
			}
		}
		return $ret;
	}

	/**
	 * Get a list of content with permissions
	 *
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	public static function getContentWithPermissionsList() {
		global $gBitSystem;
		$ret = array();
		if ( defined('ROLE_MODEL') ) {
			$query = "
				SELECT lcperm.`perm_name`, lc.`title`, lc.`content_id`, lc.`content_type_guid`, lcperm.`is_revoked`, ur.`role_id`, ur.`role_name`, up.`perm_desc`
				FROM `".BIT_DB_PREFIX."liberty_content_permissions` lcperm
					INNER JOIN `".BIT_DB_PREFIX."users_roles` ur ON( lcperm.`role_id`=ur.`role_id` )
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON( lcperm.`content_id`=lc.`content_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."users_permissions` up ON( up.`perm_name`=lcperm.`perm_name` )
				ORDER BY ".$gBitSystem->mDb->convertSortmode( 'content_type_guid_asc' ).", ".$gBitSystem->mDb->convertSortmode( 'title_asc' );
		} else {
			$query = "
				SELECT lcperm.`perm_name`, lc.`title`, lc.`content_id`, lc.`content_type_guid`, lcperm.`is_revoked`, ug.`group_id`, ug.`group_name`, up.`perm_desc`
				FROM `".BIT_DB_PREFIX."liberty_content_permissions` lcperm
					INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON( lcperm.`group_id`=ug.`group_id` )
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON( lcperm.`content_id`=lc.`content_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."users_permissions` up ON( up.`perm_name`=lcperm.`perm_name` )
				ORDER BY ".$gBitSystem->mDb->convertSortmode( 'content_type_guid_asc' ).", ".$gBitSystem->mDb->convertSortmode( 'title_asc' );
		}
		$perms = $gBitSystem->mDb->getAll( $query );
		foreach( $perms as $perm ) {
			$ret[$perm['content_type_guid']][$perm['content_id']][] = $perm;
		}
		return $ret;
	}

	/**
	 * Expunge Content Permissions
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
	 * Function that determines if this content specified permission for the current gBitUser, and will throw a fatal error if not.
	 *
	 * @param string Name of the permission to check
	 * @param string Message if permission denigned
	 */
	function verifyUserPermission( $pPermName, $pFatalMessage = NULL ) {
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
	 * @param string Check access control service if available
	 * @param string return default user permission setting when no content perms are set
	 * @return bool true if user has permission to access file
	 */
	function hasUserPermission( $pPermName, $pVerifyAccessControl=TRUE ) {
		global $gBitUser;
		$ret = FALSE;
		if( !$this->isValid() ) {
			// return default user permission setting when no content is loaded
			$ret = $gBitUser->hasPermission( $pPermName );
		} elseif( !$gBitUser->isRegistered() || !( $ret = $this->isOwner() || $ret = $gBitUser->isAdmin() )) {
			if( $gBitUser->isAdmin() || $gBitUser->hasPermission( $this->mAdminContentPerm )) {
				$ret = TRUE;
			} else {
				if( $pVerifyAccessControl ) {
					$this->verifyAccessControl();
				}
				$checkPerms = $this->getUserPermissions();
				if ( !empty( $checkPerms ) ) {
					// Do they have the admin permission or the one we want?
					if ( !empty( $checkPerms[$this->mAdminContentPerm] ) ) {
						$ret = TRUE;
					} elseif ( !empty( $checkPerms[$pPermName] ) ) {
						$ret = TRUE;
					}
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
	 * Determine if current user has the ability to delete/expunge this type of content
	 *
	 * @return bool True if user has this type of content expunge permission
	 */
	function hasExpungePermission( $pVerifyAccessControl=TRUE ) {
		return( $this->hasUserPermission( $this->mExpungeContentPerm, $pVerifyAccessControl ) );
	}

	// === verifyExpungePermission
	/**
	 * It will verify if a given user has a given $permission and if not, it will display the error template and die()
	 * @param $pVerifyAccessControl check access control service if available
	 * @return TRUE if permitted, method will fatal out if not
	 * @access public
	 */
	function verifyExpungePermission( $pVerifyAccessControl=TRUE ) {
		global $gBitSystem;
		if( $this->hasExpungePermission( $pVerifyAccessControl ) ) {
			return TRUE;
		} else {
			$gBitSystem->fatalPermission( $this->mExpungeContentPerm );
		}
	}

	/**
	 * Determine if current user has the ability to edit this type of content
	 *
	 * @return bool True if user has this type of content administration permission
	 */
	function hasUpdatePermission( $pVerifyAccessControl=TRUE ) {
		return( $this->hasUserPermission( $this->mUpdateContentPerm, $pVerifyAccessControl ) );
	}

	/**
	 * Deprecated, use hasUpdatePermission
	 *
	 * @return bool True if user has this type of content administration permission
	 */
	function hasEditPermission( $pVerifyAccessControl=TRUE, $pCheckGlobalPerm=TRUE ) {
		deprecated( "LibertyContent::hasEditPermission has been replaced with LibertyContent::hasUpdatePermission and pCheckGlobal has been change to always be the case" );
		return( $this->hasUpdatePermission( $pVerifyAccessControl ) );
	}

	// === verifyUpdatePermission
	/**
	 * This code was duplicated _EVERYWHERE_ so here is an easy template to cut that down.
	 * It will verify if a given user has a given $permission and if not, it will display the error template and die()
	 * @param $pVerifyAccessControl check access control service if available
	 * @return TRUE if permitted, method will fatal out if not
	 * @access public
	 */
	function verifyUpdatePermission( $pVerifyAccessControl=TRUE ) {
		global $gBitSystem;
		if( $this->hasUpdatePermission( $pVerifyAccessControl ) ) {
			return TRUE;
		} else {
			$gBitSystem->fatalPermission( $this->mUpdateContentPerm );
		}
	}

	// === verifyEditPermission
	/**
	 * Deprecated, use verifyUpdatePermission
	 */
	function verifyEditPermission( $pVerifyAccessControl=TRUE, $pCheckGlobalPerm=TRUE ) {
		deprecated( "LibertyContent::verifyEditPermission has been replaced with LibertyContent::verifyUpdatePermission and pCheckGlobal has been change to always be the case" );
		$this->verifyUpdatePermission( $pVerifyAccessControl );
	}

	/**
	 * Determine if current user has the ability to craete this type of content
	 *
	 * @return bool True if user has this type of content administration permission
	 */
	function hasCreatePermission( $pVerifyAccessControl=TRUE ) {
		return( $this->hasUserPermission( $this->mCreateContentPerm, $pVerifyAccessControl ) );
	}

	// === verifyCreatePermission
	/**
	 * Determine if current user has the ability to create this type of content
	 * Note this will always return FALSEif the content isValid
	 *
	 * @return bool True if user has this type of content administration permission
	 **/
	function verifyCreatePermission( $pVerifyAccessControl=TRUE ) {
		global $gBitSystem;
		if( !$this->isValid() && $this->hasCreatePermission( $pVerifyAccessControl ) ) {
			return TRUE;
		} else {
			$gBitSystem->fatalPermission( $this->mCreateContentPerm );
		}
	}

	/**
	 * Determine if current user has the ability to view this type of content
	 * Note that this will always return TRUE if you haven't set the mViewContentPerm in your class
	 *
	 * @return bool True if user has this type of content administration permission
	 */
	function hasViewPermission( $pVerifyAccessControl=TRUE ) {
		return( $this->hasUpdatePermission( $pVerifyAccessControl ) || empty( $this->mViewContentPerm ) || $this->hasUserPermission( $this->mViewContentPerm, $pVerifyAccessControl ));
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
	 * Determine if current user has the ability to post comments to this type of content
	 *
	 * @return bool True if user has this type of content administration permission
	 */
	function hasPostCommentsPermission( $pVerifyAccessControl=TRUE ) {
		return( $this->hasUserPermission( 'p_liberty_post_comments', $pVerifyAccessControl ));
	}

	// === verifyPostCommentsPermission
	/**
	 * It will verify if a given user has a given $permission and if not, it will display the error template and die()
	 * @param $pVerifyAccessControl check access control service if available
	 * @return TRUE if permitted, method will fatal out if not
	 * @access public
	 */
	function verifyPostCommentsPermission( $pVerifyAccessControl=TRUE ) {
		global $gBitSystem;
		if( $this->hasPostCommentPermission( $pVerifyAccessControl ) ) {
			return TRUE;
		} else {
			$gBitSystem->fatalPermission( 'p_liberty_post_comments' );
		}
	}

	/**
	 * Get specific permissions for the specified user for this content
	 *
	 * @return Array of all permissions for the current user joined with perms
	 *         for the current content. This should handle cases where
	 *         non-default permissions is assigned, default permission is
	 *         removed, and duplicate default permissions where one team's perm
	 *         is revoked, but another is still permitted. If the permission is
	 *         revoked, is_revoked will be set to 'y'
	 */
	function getUserPermissions() {
		global $gBitUser;

		$userId = $gBitUser->mUserId;
		// Prevent null entires when creating database
		if( !is_numeric( $userId ) ) $userId = 0;
		if( !is_numeric( $this->mContentId ) ) $this->mContentId = 0;
		if( !isset( $this->mUserContentPerms )) {
			// get the default permissions for specified user
			if ( defined('ROLE_MODEL') ) {
				$query = "
					SELECT urp.`perm_name` as `hash_key`, 1 as `role_perm`, urp.`perm_name`, urp.`perm_value`, urp.`role_id`
					FROM `".BIT_DB_PREFIX."users_roles_map` urm
						LEFT JOIN `".BIT_DB_PREFIX."users_role_permissions` urp ON(urm.`role_id`=urp.`role_id`)
						LEFT JOIN `".BIT_DB_PREFIX."liberty_content_permissions` lcp ON(lcp.`role_id`=urm.`role_id` AND lcp.`content_id`=? AND urp.`perm_name`=lcp.`perm_name`)
					WHERE (urm.`user_id`=? OR urm.`user_id`=?) AND lcp.`perm_name` IS NULL";
			} else {
				$query = "
	                SELECT ugp.`perm_name` as `hash_key`, 1 as `group_perm`, ugp.`perm_name`, ugp.`perm_value`, ugp.`group_id`
					FROM `".BIT_DB_PREFIX."users_groups_map` ugm
						LEFT JOIN `".BIT_DB_PREFIX."users_group_permissions` ugp ON(ugm.`group_id`=ugp.`group_id`)
						LEFT JOIN `".BIT_DB_PREFIX."liberty_content_permissions` lcp ON(lcp.`group_id`=ugm.`group_id` AND lcp.`content_id`=? AND ugp.`perm_name`=lcp.`perm_name`)
					WHERE (ugm.`user_id`=? OR ugm.`user_id`=?) AND lcp.`perm_name` IS NULL";
			}
			if( !$defaultPerms = $this->mDb->getAssoc( $query, array( $this->mContentId, $userId, ANONYMOUS_USER_ID ) ) ) {
				$defaultPerms = array();
			}
			if ( defined('ROLE_MODEL') ) {
				$query = "
					SELECT lcp.`perm_name` AS `hash_key`, lcp.*
					FROM `".BIT_DB_PREFIX."liberty_content_permissions` lcp
						INNER JOIN `".BIT_DB_PREFIX."users_roles_map` urm ON(lcp.role_id=urm.role_id)
						LEFT JOIN `".BIT_DB_PREFIX."users_role_permissions` urp ON(urm.role_id=urp.role_id AND urp.role_id!=lcp.role_id AND urp.perm_name=lcp.perm_name)
					WHERE lcp.content_id=? AND (urm.user_id=? OR urm.user_id=?) AND lcp.is_revoked IS NULL";
			} else {
				$query = "
					SELECT lcp.`perm_name` AS `hash_key`, lcp.*
					FROM `".BIT_DB_PREFIX."liberty_content_permissions` lcp
						INNER JOIN `".BIT_DB_PREFIX."users_groups_map` ugm ON(lcp.group_id=ugm.group_id)
						LEFT JOIN `".BIT_DB_PREFIX."users_group_permissions` ugp ON(ugm.group_id=ugp.group_id AND ugp.group_id!=lcp.group_id AND ugp.perm_name=lcp.perm_name)
					WHERE lcp.content_id=? AND (ugm.user_id=? OR ugm.user_id=?) AND lcp.is_revoked IS NULL";
			}
			if( !$nonDefaultPerms = $this->mDb->getAssoc( $query, array( $this->mContentId, $userId, ANONYMOUS_USER_ID ) ) ) {
				$nonDefaultPerms = array();
			}

			$this->mUserContentPerms = array_merge( $defaultPerms, $nonDefaultPerms );

			$this->invokeServices( 'content_user_perms_function' );
		}

		return $this->mUserContentPerms;
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
	function storePermission( $pTeamId, $pPermName, $pIsRevoked=FALSE, $pContentId=NULL ){
		$ret = FALSE;
		$pContentId = $pContentId == NULL?$this->mContentId:$pContentId;
		if( @BitBase::verifyId( $pGroupId ) && !empty( $pPermName ) && @BitBase::verifyId( $pContentId ) ) {
			$this->removePermission( $pGroupId, $pPermName, $pContentId );
			$storeHash = array(
				'perm_name' => $pPermName,
				'content_id' => $pContentId,
			);
			if ( defined('ROLE_MODEL') ) {
				$storeHash['role_id'] = $pTeamId;
			} else {
				$storeHash['group_id'] = $pTeamId;
			}
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
	function removePermission( $pTeamId, $pPermName, $pContentId=NULL ) {
		$pContentId = $pContentId == NULL?$this->mContentId:$pContentId;
		if( @BitBase::verifyId( $pTeamId ) && !empty( $pPermName ) && @BitBase::verifyId( $pContentId ) ) {
			if ( defined('ROLE_MODEL') ) {
				$team = 'role_id';
			} else {
				$team = 'group_id';
			}
			$query = "
				DELETE FROM `".BIT_DB_PREFIX."liberty_content_permissions`
				WHERE `$team` = ? and `content_id` = ? and `perm_name` = ?";
			$bindVars = array( $pTeamId, $pContentId, $pPermName );
			$result = $this->mDb->query( $query, $bindVars );
		}
		return TRUE;
	}

	/**
	 * Check to see if this permission is already in the global permissions table.
	 *
	 * @param array $pTeamId
	 * @param array $pPermName
	 * @access public
	 * @return TRUE if present, FALSE if not
	 */
	function isExcludedPermission( $pTeamId, $pPermName ) {
		if( @BitBase::verifyId( $pTeamId ) && !empty( $pPermName )) {
			if ( defined('ROLE_MODEL') ) {
				$query = "SELECT `perm_name` FROM `".BIT_DB_PREFIX."users_role_permissions` WHERE `role_id` = ? AND `perm_name` = ?";
			} else {
				$query = "SELECT `perm_name` FROM `".BIT_DB_PREFIX."users_group_permissions` WHERE `group_id` = ? AND `perm_name` = ?";
			}
			return( $this->mDb->getOne( $query, array( $pTeamId, $pPermName )) == $pPermName );
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

		if( $pContentId && !empty( $pPrefName )) {
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
				$bindvars[] = substr( $pPrefValue, 0, 250 );
				$result     = $this->mDb->query( $query, $bindvars );
				$this->mPrefs[$pPrefName] = $pPrefValue;
			}
			$this->mPrefs[$pPrefName] = $pPrefValue;
		}
		return $ret;
	}

	/**
	 * Register the content type for reference
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
			if( @BitBase::verifyId( $this->mContentId ) && (( $gBitUser->isRegistered() && !$this->isOwner() ) || ( $gBitUser->getField( 'user_id' ) == ANONYMOUS_USER_ID )) && ( $gBitSystem->isFeatureActive( 'users_count_admin_pageviews' ) || !$gBitUser->isAdmin() ) ) {
				if( $this->mDb->getOne( "SELECT `content_id` FROM `".BIT_DB_PREFIX."liberty_content_hits` WHERE `content_id`=?", array( $this->mContentId ))) {
					$query = "UPDATE `".BIT_DB_PREFIX."liberty_content_hits` SET `hits`=`hits`+1, `last_hit`= ? WHERE `content_id` = ?";
				} else {
					$query = "INSERT INTO `".BIT_DB_PREFIX."liberty_content_hits` ( `hits`, `last_hit`, `content_id` ) VALUES ( ?,?,? )";
					$bindVars[] = 1;
				}
				$bindVars[] = $gBitSystem->getUTCTime();
				$bindVars[] = $this->mContentId;
				$this->mDb->StartTrans();
				$result = $this->mDb->query( $query, $bindVars );
				$this->mDb->CompleteTrans();
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
	 * Create the generic title for a content item
	 *
	 * This will normally be overwriten by extended classes to provide
	 * an appropriate title string
	 * @param array pHash type hash of data to be used to provide base data
	 * @return string Descriptive title for the page
	 */
	function getTitle( $pHash=NULL, $pDefault=TRUE ) {
		$ret = NULL;
		if( empty( $pHash ) ) {
			$pHash = &$this->mInfo;
		}
		if( !empty( $pHash['title'] ) ) {
			$ret = $pHash['title'];
		} elseif( $pDefault && !empty( $pHash['content_name'] ) ) {
			$ret = $pHash['content_name'];
		}
		return $ret;
	}

	/**
	 * Attempt to create a brief description of this object, most useful for <meta name="description" />
	 *
	 * @return array list of aliases
	 */
	function generateDescription() {
		$ret = NULL;
		if( $this->isValid() ) {
			if( $this->getField('summary') ) {
				$ret = $this->getField('summary');
			} elseif( $this->getField('data') ) {
				// 250 to 300 is max description
				$ret = substr( $this->parseData(), 0, 250 );
			}
		}
		return $ret;
	}

	/**
	 * Attempt to create a collection of relevant words about this object, most useful for <meta name="keywords" />
	 *
	 * @return array list of aliases
	 */
	function generateKeywords() {
		$ret = array();
		if( $this->isValid() ) {
		}
		return $ret;
	}

	/**
	 * Get array of aliases for this content object
	 *
	 * @return array list of aliases
	 */
	function getAliases() {
		$ret = array();
		if( $this->isValid() ) {
			$ret = $this->mDb->getCol( "SELECT `alias_title` FROM `".BIT_DB_PREFIX."liberty_aliases` lal INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON(lal.`content_id`=lc.`content_id`) WHERE lal.`content_id`=? ", array( $this->mContentId ) );
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

	/**
	 * Get the display name of the content type
	 * @param boolean $pPlural true will return the plural form of the content type display name
	 * @return string the display name of the content type
 	 */
	function getContentTypeName( $pPlural=FALSE ){
		global $gLibertySystem;
		return $gLibertySystem->getContentTypeName( $this->getContentType(), $pPlural );
	}


	/**
	 * getContentTypeDescription
	 *
	 * @param array $pContentType
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function getContentTypeDescription( $pContentType=NULL ) {
		deprecated( 'You are calling the deprecated method getContentTypeDescription, use getContentTypeName( $pPlural )' );
		return $this->getContentTypeName();
		/*
		global $gLibertySystem;
		if( is_null( $pContentType ) ) {
			$pContentType = $this->getContentType();
		}
		return $gLibertySystem->getContentTypeDescription( $pContentType );
 		*/
	}

	/**
	 * Access a content item content_id
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
		deprecated( 'You are calling the deprecated method getContentDescription, use getContentTypeName( $pPlural )' );
		return $this->getContentTypeName();
	}


	/**
	 * returns a path to the template type requested
	 * this is intended for package override. while not a requirement please use a naming convention of center_<action>_<content_type_guid>.tpl for new tpls
	 *
	 * @param string $pAction the type of template. common types are view and list
	 */
	function getViewTemplate( $pAction ) {
		$ret = null;
		switch ( $pAction ){
			case "view":
			case "list":
				$ret = "bitpackage:liberty/center_".$pAction."_generic.tpl";
				break;
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
			} elseif( !empty( $pMixed['content_name'] ) ) {
				$pLinkText = "[ ".$pMixed['content_name']." ]";
			}
		}

		if( empty( $pLinkText )) {
			$pLinkText = "[ ".tra( "No Title" )." ]";
		}

		// we add some more info to the title of the link
		if( !empty( $pMixed['created'] )) {
			$gBitSmarty->loadPlugin( 'smarty_modifier_bit_short_date' );
			$linkTitle = tra( 'Created' ).': '.smarty_modifier_bit_short_date( $pMixed['created'] );
		} else {
			$linkTitle = $pLinkText;
		}

		// finally we are ready to create the full link
		if( !empty( $pMixed['content_id'] )) {
			$ret = '<a title="'.htmlspecialchars( $linkTitle ).'" href="'.LibertyContent::getDisplayUrlFromHash( $pMixed ).$pAnchor.'">'.htmlspecialchars( $pLinkText ).'</a>';
		}
		return $ret;
	}

	/**
	 * Not-so-pure virtual function that returns fully qualified URI to a piece of content
	 * @param string Text for DisplayLink function
	 * @param array different possibilities depending on derived class
	 * @return string Formated URL address to display the page.
	 */
	public function getDisplayUri() {
		if( $this->isValid() ) {
			return BIT_ROOT_URI.substr( static::getDisplayUrlFromHash( $this->mInfo ), strlen( BIT_ROOT_URL ) );
		}
	}

	/**
	 * Not-so-pure virtual function that returns fully qualified URI to a piece of content
	 * @param string Text for DisplayLink function
	 * @param array different possibilities depending on derived class
	 * @return string Formated URL address to display the page.
	 */
	public static function getDisplayUriFromHash( &$pParamHash ) {
		return BIT_ROOT_URI.substr( static::getDisplayUrlFromHash( $pParamHash ), strlen( BIT_ROOT_URL ) );
	}

	/**
	 * Not-so-pure virtual function that returns Request_URI to a piece of content
	 * @param array $pMixed a hash of params to add to the url
	 * @return string Formated URL address to display the page.
	 */
	public static function getDisplayUrlFromHash( &$pParamHash ) {
		$ret = NULL;
		if( @static::verifyId( $pParamHash['content_id'] ) ) {
			$ret = BIT_ROOT_URL.'index.php?content_id='.$pParamHash['content_id'];
		}
		return $ret;
	}

	/**
	 * Returns Request URL to a piece of content
	 */
	public function getDisplayUrl() {
		$ret = NULL;
		if( !empty( $this ) && $this->isValid() ) {
			$ret = static::getDisplayUrlFromHash( $this->mInfo );
		}
		return $ret;
	}

	/**
	 * Returns the create/edit url to a piece of content
	 * @param number $pContentId a valid content id
	 * @param array $pMixed a hash of params to add to the url
	 */
	function getEditUrl( $pContentId = NULL, $pMixed = NULL ){
		global $gLibertySystem;
		$package = $gLibertySystem->mContentTypes[$this->mType['content_type_guid']]['handler_package'];

		$pathConst = strtoupper( $package ).'_PKG_URL';
		if( defined( $pathConst ) ) {
			$packagePath = constant( $pathConst );
		}else{
			$packagePath = BIT_ROOT_URL.$package."/";
		}

		if( @BitBase::verifyId( $pContentId ) ) {
			$ret = $packagePath.'edit.php?content_id='.$pContentId;
		} elseif( $this->isValid() ) {
			$ret = $packagePath.'edit.php?content_id='.$this->mContentId;
		} else {
			$ret = $packagePath.'edit.php'.(!empty( $pMixed )?"?":"");
		}
		foreach( $pMixed as $key => $value ){
			if( $key != "content_id" || ( $key == "content_id" && @BitBase::verifyId( $value ) ) ) {
				$ret .= (isset($amp)?"&":"").$key."=".$value;
			}
			$amp = TRUE;
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
	function getThumbnailUrl( $pSize = 'small', $pInfoHash = NULL, $pSecondaryId = NULL, $pDefault=TRUE ) {
		$ret = '';
		if( !empty( $this->mInfo['content_type']['handler_package'] ) ) {
			$pkgName = $this->mInfo['content_type']['handler_package'];
			if( $pkgPath = constant( strtoupper( $pkgName ).'_PKG_PATH' ) ) {
				if( file_exists( $pkgPath.'icons/pkg_'.$pkgName.'.png' ) ) {
					$ret = constant( strtoupper( $pkgName ).'_PKG_URL' ).'icons/pkg_'.$pkgName.'.png';
				}
			}
		}
		return $ret;
	}


	function getThumbnailUri( $pSize='small', $pInfoHash=NULL ) {
		$ret = $this->getThumbnailUrl( $pSize, $pInfoHash );
		// Check to make sure we don't have an absolute URI already, which could be the case for custom classes
		if( strpos( $ret, 'http' ) !== 0 ) {
			$ret = STORAGE_HOST_URI.substr( $ret, strlen( BIT_ROOT_URL ) );
		}
		return( $ret );
	}


	function getThumbnailFile( $pSize='small', $pInfoHash=NULL ) {
		$ret = $this->getThumbnailUrl( $pSize, $pInfoHash );
		// Check to make sure we don't have an absolute URI already, which could be the case for custom classes
		if( strpos( $ret, 'http' ) !== 0 ) {
			$ret = substr( $ret, strlen( BIT_ROOT_URL ) );
		}
		return( BIT_ROOT_PATH.$ret );
	}


	/**
	 * Validate inbound sort_mode parameter
	 * @param pParamHash hash of parameters for any getList() function
	 * @return the link to display the page.
	 */
	public static function getSortModeFields() {
		return array(
			'content_id',
			'modifier_user',
			'modifier_real_name',
			'creator_user',
			'creator_real_name',
			'title',
			'content_type_guid',
			'ip',
			'last_modified',
			'created',
		);
	}

	/**
	 * Validate inbound sort_mode parameter
	 * @param pParamHash hash of parameters for any getList() function
	 * @return the link to display the page.
	 */
	public function convertSortMode( &$pSortMode, $pDefault='last_modified_desc' ) {

		$sortHash = static::getSortModeFields();

		$baseSortMode = str_replace( '_asc', '', str_replace( '_desc', '', $pSortMode ) );

		$baseSortMode = preg_replace( '/^.*\./', '', $baseSortMode );

		if( !in_array( $baseSortMode, $sortHash ) ) {
			$pSortMode = $pDefault;
		}

		return $this->mDb->convertSortmode( $pSortMode );
	}


	/**
	 * Liberty override to stuff content_status_id and prepares parameters with default values for any getList function
	 * @param pParamHash hash of parameters for any getList() function
	 * @return the link to display the page.
	 */
	public static function prepGetList( &$pListHash ) {
		global $gBitUser;
		if( $gBitUser->isAdmin() ) {
			$pListHash['min_content_status_id'] = -9999;
		} elseif( !empty( $this ) && is_object( $this ) && $this->hasAdminPermission() ) {
			$pListHash['min_content_status_id'] = -999;
		} elseif( !empty( $this ) && is_object( $this ) && $this->hasUpdatePermission() ) {
			$pListHash['min_content_status_id'] = -99;
		} else {
			$pListHash['min_content_status_id'] = 1;
		}

		if( empty( $pListHash['query_cache_time'] ) ) {
			$pListHash['query_cache_time'] = 0;
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
		} else {
			// if sort_mode is not set then use last_modified_desc
			$pListHash['sort_mode'] = 'last_modified_desc';
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

		LibertyContent::prepGetList( $pListHash );
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
			$ret['data'] = $libertyContent->getContentList( $pListHash );
		}

		$ret['title']     = !empty( $pListHash['title'] ) ? $pListHash['title'] : tra( "Content Ranking" );
		$ret['attribute'] = !empty( $pListHash['attribute'] ) ? $pListHash['attribute'] : tra( "Hits" );

		return $ret;
	}

	/**
	 * Get a list of all content
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
	function getContentList( &$pListHash ) {
		global $gLibertySystem, $gBitSystem, $gBitUser, $gBitSmarty;

		LibertyContent::prepGetList( $pListHash );

		$hashSql = array('select'=>array(), 'join'=>array(),'where'=>array() );
		$hashBindVars = array('select'=>array(), 'where'=>array(), 'join'=>array());
		if( !empty( $pListHash['content_type_guid'] ) && is_array( $pListHash['content_type_guid'] )) {
			foreach( $pListHash['content_type_guid'] as $contentTypeGuid ) {
				$this->getFilter( $contentTypeGuid, $hashSql, $hashBindVars, $pListHash );
			}
		} elseif( !empty( $pListHash['content_type_guid'] )) {
			$this->getFilter( $pListHash['content_type_guid'], $hashSql, $hashBindVars, $pListHash );
		}

		if( !empty( $hashSql['select'] )) {
			$selectSql = ','.implode( ',', $hashSql['select'] );
		} else {
			$selectSql = '';
		}
		$joinSql = implode( ' ', $hashSql['join'] );
		$whereSql = '';
		if( empty( $hashBindVars['join'] )) {
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
			$bindVars = array_merge( $pListHash['find'], $pListHash['find'] );
		} elseif( !empty( $pListHash['find'] ) && is_string( $pListHash['find'] ) ) { // or a string
			$whereSql .= " AND UPPER(lc.`title`) like ? ";
			$bindVars[] = ( '%' . strtoupper( $pListHash['find'] ) . '%' );
		}

		if( !empty( $pListHash['content_id_list'] ) ) { // you can use an array of titles
			$whereSql .= " AND lc.`content_id` IN ( ".implode( ',',array_fill( 0,count( $pListHash['content_id_list'] ),'?' ) ).") ";
			$bindVars = array_merge( $bindVars, $pListHash['content_id_list'] );
		}

		// this is necessary to display useful information in the liberty RSS feed
		if( !empty( $pListHash['include_data'] ) ) {
			$selectSql .= ", lc.`data`, lc.`format_guid`";
		}

		// if we want the primary attachment for each object
		if(  $gBitSystem->isFeatureActive( 'liberty_display_primary_attach' )  ){
			$selectSql .= ', lfp.`file_name`, lfp.`mime_type`, la.`attachment_id`, ';
			$joinSql .= "LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments` la ON( la.`content_id` = lc.`content_id` AND la.`is_primary` = 'y' )
						 LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files` lfp ON( lfp.`file_id` = la.`foreign_id` )";
		}

		// Allow selection based on arbitrary time limits -- used in calendar
		// TODO: We should replace usages of from_date and until_date with this generic setup and depricate those
		if( !empty( $pListHash['time_limit_column'] )) {
			if( empty( $pListHash['time_limit_table'] ) ) {
				$pListHash['time_limit_table'] = 'lc.';
			}
			if( !empty( $pListHash['time_limit_start'] ) ) {
				$whereSql .= " AND ".$pListHash['time_limit_table']."`".$pListHash['time_limit_column']."` >= ? ";
				$bindVars[] = $pListHash['time_limit_start'];
			}
			if( !empty( $pListHash['time_limit_stop'] ) ) {
				$whereSql .= " AND ".$pListHash['time_limit_table']."`".$pListHash['time_limit_column']."` <= ? ";
				$bindVars[] = $pListHash['time_limit_stop'];
			}
		}

		if( @$this->verifyId( $pListHash['user_id'] ) ) {
			$whereSql .= " AND lc.`user_id` = ? ";
			$bindVars[] = $pListHash['user_id'];
		}

		if( @$this->verifyId( $pListHash['link_content_id'] ) ){
			$joinSql .= " INNER JOIN `".BIT_DB_PREFIX."liberty_content_links` lclk ON ( lc.`content_id` = lclk.`to_content_id` )";
			$whereSql .= " AND lclk.`from_content_id` = ? ";
			$bindVars[] = (int)$pListHash['link_content_id'];
		}

		if( $gBitSystem->isFeatureActive( 'liberty_display_status' ) &&  $gBitUser->hasPermission( 'p_liberty_view_all_status' )) {
			$selectSql .= ", lcs.`content_status_id`, lcs.`content_status_name`";
			$joinSql   .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_status` lcs ON ( lc.`content_status_id` = lcs.`content_status_id` )";
			if( !empty( $pListHash['content_status_id'] )) {
				if( $pListHash['content_status_id'] == 'not_available' ) {
					$whereSql .= " AND lcs.`content_status_id` <> ? ";
					$bindVars[] = 50;
				} else {
					$whereSql .= " AND lcs.`content_status_id` = ? ";
					$bindVars[] = (int)$pListHash['content_status_id'];
				}
			}
		}

		// join on specific content_type_guids
		if( !empty( $pListHash['content_type_guid'] ) && is_string( $pListHash['content_type_guid'] ) ) {
			$whereSql .= ' AND lc.`content_type_guid`=? ';
			$bindVars[] = $pListHash['content_type_guid'];
		} elseif( !empty( $pListHash['content_type_guid'] ) && is_array( $pListHash['content_type_guid'] ) ) {
			$whereSql .= " AND lc.`content_type_guid` IN ( ".implode( ',',array_fill ( 0, count( $pListHash['content_type_guid'] ),'?' ) )." )";
			$bindVars = array_merge( $bindVars, $pListHash['content_type_guid'] );
		}

		// exclude by content_type_guids
		if( !empty( $pListHash['exclude_content_type_guid'] ) && is_string( $pListHash['exclude_content_type_guid'] ) ) {
			$whereSql .= " AND lc.`content_type_guid` != ?";
			$bindVars[] = $pListHash['exclude_content_type_guid'];
		} elseif( !empty( $pListHash['exclude_content_type_guid'] ) && is_array( $pListHash['exclude_content_type_guid'] ) ) {
			$whereSql .= " AND lc.`content_type_guid` NOT IN ( ".implode( ',',array_fill ( 0, count( $pListHash['exclude_content_type_guid'] ),'?' ) )." )";
			$bindVars = array_merge( $bindVars, $pListHash['exclude_content_type_guid'] );
		}

		// only display content modified more recently than this (UTC timestamp)
		if( !empty( $pListHash['from_date'] ) ) {
			$whereSql .= ' AND lc.`last_modified` >= ?';
			$bindVars[] = $pListHash['from_date'];
		}

		// only display content modified before this (UTC timestamp)
		if( !empty( $pListHash['until_date'] ) ) {
			$whereSql .= ' AND lc.`last_modified` <= ?';
			$bindVars[] = $pListHash['until_date'];
		}

		// Should results be hashed or sequential indexed
		$hashKeySql = '';
		if( !empty( $pListHash['hash_key'] ) ) {
			$hashKeySql = $pListHash['hash_key'].' AS `hash_key`, ';
		}

		if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
			if( $gBitSystem->isPackageActive( 'fisheye' ) ) {
				// This is really ugly to have in here, and really would be better off somewhere else.
				// However, because of the specific nature of the current implementation of fisheye galleries, I am afraid
				// this is the only place it can go to properly enforce gatekeeper protections. Hopefully a new content generic
				// solution will be available in ReleaseTwo - spiderr
				if( $this->mDb->isAdvancedPostgresEnabled() ) {
// 					$joinSql .= " LEFT OUTER JOIN  `".BIT_DB_PREFIX."fisheye_gallery_image_map` fgim ON (fgim.`item_content_id`=lc.`content_id`)";
					$whereSql .= " AND (SELECT ls.`security_id` FROM connectby('fisheye_gallery_image_map', 'gallery_content_id', 'item_content_id', 'item_content_id', text( lc.`content_id` ), 0, '/')  AS t(`cb_gallery_content_id` int, `cb_item_content_id` int, level int, branch text, pos int), `".BIT_DB_PREFIX."gatekeeper_security_map` cgm,  `".BIT_DB_PREFIX."gatekeeper_security` ls
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
		} elseif( strpos( $pListHash['sort_mode'], '.' ) ) {
			// do not specifiy orderTable of sort_mode already has a . in it
			$orderTable = '';
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
				$hashKeySql
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
				lc.`content_id`,
				lcds.`data` AS `summary`
				$selectSql
			FROM `".BIT_DB_PREFIX."liberty_content` lc
				INNER JOIN `".BIT_DB_PREFIX."users_users` uuc ON (lc.`user_id`=uuc.`user_id`)
				INNER JOIN `".BIT_DB_PREFIX."users_users` uue ON (lc.`modifier_user_id`=uue.`user_id`)
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON( lc.`content_id` =  lch.`content_id`)
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_data` lcds ON (lc.`content_id` = lcds.`content_id` AND lcds.`data_type`='summary')
				$joinSql
				$whereSql
			ORDER BY ".$orderTable.$this->convertSortMode($pListHash['sort_mode']);

		$query_cant = "
			SELECT
				COUNT(lc.`content_id`)
			FROM `".BIT_DB_PREFIX."liberty_content` lc
				INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`modifier_user_id`=uu.`user_id`)
			$joinSql
			$whereSql";

		$cant = $this->mDb->getOne( $query_cant, $bindVars );
		$pListHash["cant"] = $cant;

		# Check for offset out of range
		if( $pListHash['offset'] < 0 ) {
			$pListHash['offset'] = 0;
		} elseif ( $pListHash['offset']	> $pListHash["cant"] ) {
			$lastPageNumber = ceil ( $pListHash["cant"] / $pListHash['max_records'] ) - 1;
			$pListHash['offset'] = $pListHash['max_records'] * $lastPageNumber;
		}


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
				$aux['content_name'] 		= $type['content_name'];
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
						/**
						 * @TODO standardize getDisplayUrl params
						 * nice try, but you can't do this because individual classes have gone off the reservation changing the params they accept
						 * for distributed packages we need to enforce that method overrides all take the same basic params.
						 **/
						// $aux['display_url']  = $type['content_object']->getDisplayUrl( NULL, $aux );
						$aux['display_url'] = BIT_ROOT_URL."index.php?content_id=".$aux['content_id'];
					}

					if( !empty( $pListHash['thumbnail_size'] ) ) {
						$aux['content_object'] = new $type['handler_class']( NULL, $aux['content_id'] );
						if( $aux['content_object']->load( FALSE ) ) {
							$aux['thumbnail_url'] = $aux['content_object']->getThumbnailUrl( $pListHash['thumbnail_size'] );
						}
					}

				}

				/**
				 * @TODO standardize use of thumbnail_url and provision for hash of thumbnail sizes
				 *
				 * We have a bit of a mess with the use of thumbnail_url where sometimes it is a hash of sizes, and sometimes it is a single size
				 * we should standardize the param and what kind of value it returns, and if we need both types then have two params.
				 * This ultimately might need to be more sophisticated to deal with different mime types.
				 **/
				if(  $gBitSystem->isFeatureActive( 'liberty_display_primary_attach' ) ) {
					$aux['thumbnail_urls'] = liberty_fetch_thumbnails( $aux );
				}

				if( isset( $aux['hash_key'] ) ) {
					$ret[$aux['hash_key']] = $aux;
				} else {
					$ret[] = $aux;
				}
			}
		}

		// If sortmode is versions, links or backlinks sort using the ad-hoc function and reduce using old_offse and old_max_records
		if( $old_sort_mode == 'versions_asc' && !empty( $ret['versions'] ) ) {
			usort( $ret, 'compare_versions' );
		}

		if( $old_sort_mode == 'versions_desc' && !empty( $ret['versions'] ) ) {
			usort( $ret, 'r_compare_versions' );
		}

		if( $old_sort_mode == 'links_desc' && !empty( $ret['links'] ) ) {
			usort( $ret, 'compare_links' );
		}

		if( $old_sort_mode == 'links_asc' && !empty( $ret['links'] ) ) {
			usort( $ret, 'r_compare_links' );
		}

		if( $old_sort_mode == 'backlinks_desc' && !empty( $ret['backlinks'] ) ) {
			usort( $ret, 'compare_backlinks' );
		}

		if( $old_sort_mode == 'backlinks_asc' && !empty( $ret['backlinks'] ) ) {
			usort( $ret, 'r_compare_backlinks' );
		}

		if( in_array( $old_sort_mode, array(
				'versions_desc',
				'versions_asc',
				'links_asc',
				'links_desc',
				'backlinks_asc',
				'backlinks_desc'
			))) {
			$ret = array_slice( $ret, $old_offset, $old_max_records );
		}

		LibertyContent::postGetList( $pListHash );
		return $ret;
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
			// snip off a broken tag at the end if there is one
			$pParseHash['data'] = preg_replace( '!<[a-zA-Z/][^>]*?$!', '', $pParseHash['data'] );
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
		global $gLibertySystem, $gBitSystem, $gBitUser;

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
		$parseHash['user_id']         = !empty( $parseHash['user_id'] )         ? $parseHash['user_id']         : is_object( $gBitUser ) ? $gBitUser->mUserId : ANONYMOUS_USER_ID;

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

		if (substr($parts[$i - 1], 1, 5) == "<br/>") {
			$ret = substr($parts[$i - 1], 6);
		} else {
			$ret = $parts[$i - 1];
		}

		// Replace back <PRE> sections
		foreach ($preparsed as $pp) {
			$ret = str_replace($pp["key"], "<pre>" . $pp["data"] . "</pre>", $ret);
		}

		return $ret;
	}

	/**
	 * convenience function to process a $_REQUEST array
	 **/
	function decodeAjaxRequest( &$pParamHash ){
		foreach( $pParamHash as $key => $value ){
			if( is_string($value) ){
				$pParamHash[$key] = htmlspecialchars_decode( $value );
			}
		}
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
		$sql = "SELECT lc.`title`, lc.`data`, lcds.`data` AS `summary`, uu.`login`, uu.`real_name`
				FROM `" . BIT_DB_PREFIX . "liberty_content` lc
					INNER JOIN `" . BIT_DB_PREFIX . "users_users` uu ON uu.`user_id`    = lc.`user_id`
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_data` lcds ON (lc.`content_id` = lcds.`content_id` AND lcds.`data_type`='summary')
				WHERE lc.`content_id` = ?" ;
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
		global $gBitSystem;
		if( empty( $pContentId ) && @BitBase::verifyId( $this->mContentId ) ) {
			$pContentId = $this->mContentId;
		}

		$ret = FALSE;
		if( @BitBase::verifyId( $pContentId ) ) {
			if( $gBitSystem->isFeatureActive( 'liberty_flat_cache' )) {
				$subdir = floor( $pContentId / 1000 );
				$path = LibertyContent::getCacheBasePath().$subdir.'/';
			} else {
				$subdir = $pContentId % 1000;
				$path = LibertyContent::getCacheBasePath().$subdir.'/'.$pContentId.'/';
			}
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
		global $gBitSystem;
		if( $gBitSystem->isFeatureActive( 'liberty_cache' ) && @BitBase::verifyId( $pContentId ) ) {
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

	/**
	 * getFilter
	 *
	 * @param array $pContentTypeGuid
	 * @param array $pSql
	 * @param array $pBindVars
	 * @param array $pHash
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 * @todo
	 * - i think this function is not being used and will hopefully be removed soon - xing - Saturday Jul 07, 2007   19:54:02 CEST
	 * - it is called in getContentList but I think that services can do what it does now - nick - Sunday Sep 30, 2007
	 */
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
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	public static function storeActionLogFromHash( $pParamHash = NULL ) {
		global $gBitSystem;

		if( $gBitSystem->isFeatureActive( 'liberty_action_log' ) && $this->verifyActionLog( $pParamHash ) ) {
			$gBitSystem->mDb->associateInsert( BIT_DB_PREFIX."liberty_action_log", $pParamHash['action_log_store'] );
		}
	}

	/**
	 * storeActionLog
	 * Note: use $gBitSystem throughout that this function can be called statically if needed
	 *
	 * @param array $pParamHash
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	public function storeActionLog( $pParamHash = NULL ) {
		global $gBitSystem;

		if( !empty( $this ) && @BitBase::verifyId( $this->mContentId ) ) {
			$pParamHash['action_log']['content_id'] = $this->mContentId;
		}
		if( !empty( $this->mInfo['title'] ) ) {
			$pParamHash['action_log']['title'] = $this->mInfo['title'];
		}
		$log_message = '';
		if( empty( $pParamHash['action_log']['log_message'] ) && !empty( $this->mLogs ) ) {
			foreach( $this->mLogs as $key => $msg ) {
				$log_message .= "$msg";
			}
			$pParamHash['action_log']['log_message'] = $log_message;
		}
		$error_message = '';
		if( empty( $pParamHash['action_log']['error_message'] ) && !empty( $this->mErrors ) ) {
			foreach( $this->mErrors as $key => $msg ) {
				$error_message .= "$msg\n";
			}
			$pParamHash['action_log']['error_message'] = $error_message;
		}
		if( $gBitSystem->isFeatureActive( 'liberty_action_log' ) && static::verifyActionLog( $pParamHash ) ) {
			$gBitSystem->mDb->associateInsert( BIT_DB_PREFIX."liberty_action_log", $pParamHash['action_log_store'] );
		}
	}

	/**
	 * verify the data in the action log is ready for storing
	 * First checks $pParamHash['action_log'] for information and then the content_store stuff
	 * Note: use $gBitSystem throughout that this function can be called statically if needed
	 *
	 * @param array $pParamHash
	 * @return TRUE on success, FALSE on failure
	 */
	public static function verifyActionLog( &$pParamHash ) {
		global $gBitUser, $gBitSystem;

		// we will set $ret FALSE if there is a problem along the way
		// we can't populate mErrors since it would defeat the purpose having errors about the logging system
		$ret = TRUE;

		// content_id isn't strictly needed
		if( @BitBase::verifyId( $pParamHash['action_log']['content_id'] ) ) {
			$pParamHash['action_log_store']['content_id'] = $pParamHash['action_log']['content_id'];
		} elseif( @BitBase::verifyId( $pParamHash['content_id'] ) ) {
			$pParamHash['action_log_store']['content_id'] = $pParamHash['content_id'];
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
				$log_message .= "$msg";
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
		if( !empty( $pParamHash['action_log']['error_message'] ) ) {
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

		if( !empty( $pListHash['user_id'] ) ) {
			$whereSql .= empty( $whereSql ) ? ' WHERE ' : ' AND ';
			$whereSql .= " lal.`user_id` = ? ";
			$bindVars[] = $pListHash['user_id'];
		}

		if( !empty( $pListHash['content_id'] ) ) {
			$whereSql .= empty( $whereSql ) ? ' WHERE ' : ' AND ';
			$whereSql .= " lal.`content_id` = ? ";
			$bindVars[] = $pListHash['content_id'];
		}

		if( !empty( $pListHash['sort_mode'] )) {
			if( preg_match( "/^last_modified|^title/", $pListHash['sort_mode'] )) {
				$pListHash['sort_mode'] = "lal.".$pListHash['sort_mode'];
			}
			$orderSql = " ORDER BY ".$this->convertSortMode( $pListHash['sort_mode'] )." ";
		}

		$query = "
			SELECT lal.*,
				lc.`content_type_guid`, lc.`created`, lct.`content_name`, lct.`content_name_plural`,
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
			$aux['display_name'] = BitUser::getDisplayNameFromHash( NULL, $aux );
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
	 * getAvailableContentStatus
	 *
	 * @access public
	 * @return an array of content_status_id, content_status_names the current
	 * user can use on this content. Subclases may easily override with return
	 * LibertyContent::getAvailableContentStatus(-100, 0) for example to restrict to
	 * only hidden content types.
	 */
	function getAvailableContentStatuses( $pUserMinimum=-100, $pUserMaximum=100 ) {
		global $gBitUser;
		if( $gBitUser->hasPermission( 'p_liberty_edit_all_status' )) {
			return( $this->mDb->getAssoc( "SELECT `content_status_id`,`content_status_name` FROM `".BIT_DB_PREFIX."liberty_content_status` ORDER BY `content_status_id`" ) );
		} else {
			return( $this->mDb->getAssoc( "SELECT `content_status_id`, `content_status_name` FROM `".BIT_DB_PREFIX."liberty_content_status` WHERE `content_status_id` > ? AND `content_status_id` < ? ORDER BY `content_status_id`", array( $pUserMinimum, $pUserMaximum )));
		}
	}

	/**
	 * getContentStatus will return the content status of the currently loaded content.
	 *
	 * @param array $pContentId Content ID of the content in question
	 * @access public
	 * @return Status ID
	 */
	function getContentStatus( $pDefault = 50, $pContentId = NULL ) {
		$ret = NULL;
		if ( @!BitBase::verifyId( $pContentId ) && $this->isValid() ){
			if ( !( $ret = $this->getField( 'content_status_id' ) ) ){
				$pContentId = $this->mContentId;
			}
		}
		if( !is_null( $pContentId )) {
			$ret = $this->mDb->getOne( "SELECT `content_status_id` FROM `".BIT_DB_PREFIX."liberty_content` WHERE `content_id` = ?", array( $pContentId ));
		}
		$ret = is_null( $ret ) ? $pDefault : $ret;
		return $ret;
	}

	/**
	 * isDeleted status test
	 *
	 * @return true when the content status = -999
	 */
	function isDeleted() {
		global $gBitSystem;
		return( $this->getField( 'content_status_id' ) <= $gBitSystem->getConfig( 'liberty_status_deleted', -999 ) );
	}

	/**
	 * isPrivate status test
	 *
	 * @return true when the content status = -999
	 */
	function isPrivate() {
		global $gBitSystem;
		return( $this->getField( 'content_status_id' ) <= $gBitSystem->getConfig( 'liberty_status_threshold_private', -40 ) );
	}

	/**
	 * isProtected status test
	 *
	 * @return true when the content status = -20 or content has protection flag set
	 */
	function isProtected() {
		global $gBitSystem;
		return( $this->getField( 'content_status_id' ) <= $gBitSystem->getConfig( 'liberty_status_threshold_protected', -20 ) );
	}

	/**
	 * isHidden status test
	 *
	 * @return true when the content status = -10
	 */
	function isHidden() {
		global $gBitSystem;
		return( $this->getField( 'content_status_id' ) <= $gBitSystem->getConfig( 'liberty_status_threshold_hidden', -10 ) );
	}

	/**
	 * getContentStatusName
	 *
	 * @param array $pStatusId Status ID if not available in $this->mInfo['content_status_id']
	 * @access public
	 * @return The name of the content status based on the status id of the content
	 */
	function getContentStatusName( $pStatusId = NULL ) {
		$ret = 'Not a valid content status';

		// check to see where we can get the status information from
		if( !empty( $this ) && !empty( $this->mInfo['content_status_name'] )) {
			return( $this->mInfo['content_status_name'] );
		} elseif( is_null( $pStatusId ) && !empty( $this ) && !empty( $this->mInfo['content_status_id'] )) {
			$pStatusId = $this->mInfo['content_status_id'];
		}

		// fetch from db if needed
		if( !is_null( $pStatusId )) {
			if( $ret = $this->mDb->getOne( "SELECT `content_status_name` FROM `".BIT_DB_PREFIX."liberty_content_status` WHERE `content_status_id` = ?", array( $pStatusId ))) {
			}
		}

		return $ret;
	}

	/**
	 * Store Data into liberty_content_data
	 *
	 * @return bool true ( will not currently report a failure )
	 */
	function storeData( $pData, $pType ) {
		if( $this->mContentId ) {
			$pData = trim( $pData );
			if( empty( $pData ) ) {
				$this->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."liberty_content_data` WHERE `content_id`=? AND `data_type`=?", array( $this->mContentId, $pType ) );
			} else {
				if( $this->mDb->getOne( "SELECT `content_id` FROM `".BIT_DB_PREFIX."liberty_content_data` WHERE `content_id`=? AND `data_type`=?", array( $this->mContentId, $pType ) ) ) {
					$query = "UPDATE `".BIT_DB_PREFIX."liberty_content_data` SET `data`= ? WHERE `content_id` = ? AND `data_type`=?";
				} else {
					$query = "INSERT INTO `".BIT_DB_PREFIX."liberty_content_data` ( `data`, `content_id`, `data_type` ) VALUES (?,?,?)";
				}
				$result = $this->mDb->query( $query, array( $pData, $this->mContentId, $pType ) );
			}
		}
		return TRUE;
	}

	/**
	 * storeStatus store liberty contenet status
	 *
	 * @param array $pContentStatusId
	 * @access public
	 * @return void
	 */
	function storeStatus( $pContentStatusId ) {
		if( $this->isValid() && $pContentStatusId ) {
			return $this->mDb->query( "UPDATE `".BIT_DB_PREFIX."liberty_content` SET `content_status_id`=? WHERE `content_id`=?", array( $pContentStatusId, $this->mContentId ) );
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
		// TODO!
		return $ret;
	}

	/**
	 * getPreview -- Returns a string with a preview of the content. Default implementation runs getRenderFile() with $liberty_preview set in the context and gBitSystem set to only render the content.
	 *
	 * @access public
	 * @return the preview string
	 **/
	function getPreview() {
		global $gBitSystem, $gContent, $gBitSmarty, $gBitThemes;
		// Tell gBitSystem not to do modules and such
		$gBitThemes->setFormatHeader( "center_only" );
		// Tell the content we are previewing (in case they care)
		$gBitSmarty->assign('liberty_preview', true);
		// Save current gContent
		$oldGContent = $gContent;
		// Make us the content
		$gContent = $this;

		$ret = get_include_contents($this->getRenderFile());

		// Return gBitSystem to full render mode
		$gBitThemes->setFormatHeader( "html" );
		// Clear the preview flag
		$gBitSmarty->assign('liberty_preview', false);
		// Restore gContent
		$gContent = $oldGContent;

		return $ret;
	}

}
