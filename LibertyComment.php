<?php
/**
 * Management of Liberty Content
 *
 * @package  liberty
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/LibertyComment.php,v 1.45 2007/03/06 16:16:46 squareing Exp $
 * @author   spider <spider@steelsun.com>
 */

/**
 * required setup
 */
require_once( LIBERTY_PKG_PATH.'LibertyContent.php' );

define( 'BITCOMMENT_CONTENT_TYPE_GUID', 'bitcomment' );

/**
 * Virtual base class (as much as one can have such things in PHP) for all
 * derived tikiwiki classes that require database access.
 *
 * @package kernel
 */
class LibertyComment extends LibertyContent {
	var $mCommentId;

	function LibertyComment($pCommentId = NULL, $pContentId = NULL, $pInfo = NULL) {
		LibertyContent::LibertyContent();
		$this->registerContentType( BITCOMMENT_CONTENT_TYPE_GUID, array(
				'content_type_guid' => BITCOMMENT_CONTENT_TYPE_GUID,
				'content_description' => 'Comment',
				'handler_class' => 'LibertyComment',
				'handler_package' => 'liberty',
				'handler_file' => 'LibertyComment.php',
				'maintainer_url' => 'http://www.bitweaver.org'
			) );
		$this->mCommentId = $pCommentId;
		$this->mContentId = $pContentId;
		$this->mInfo = $pInfo;
		$this->mAdminContentPerm = 'p_liberty_admin_comments';

		if ($this->mCommentId || $this->mContentId) {
			$this->loadComment();
		}
	}


	function loadComment() {
		global $gBitSystem;
		if (!$this->mCommentId && !$this->mContentId) {
			return NULL;
		}

		if ($this->mCommentId) {
			$mid = 'WHERE lcom.`comment_id` = ?';
			$bindVars = array($this->mCommentId);
		} else {
			$mid = 'WHERE lc.`content_id` = ?';
			$bindVars = array($this->mContentId);
		}

		$joinSql = $selectSql = $whereSql = '';
		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars, $this );

		$sql = "SELECT lcom.*, lc.*, uu.`email`, uu.`real_name`, uu.`login` $selectSql
				FROM `".BIT_DB_PREFIX."liberty_comments` lcom
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lcom.`content_id` = lc.`content_id`)
					LEFT OUTER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`user_id` = uu.`user_id`) $joinSql
				$mid $whereSql";
		if( $row = $this->mDb->getRow( $sql, $bindVars ) ) {
			$this->mInfo = $row;
			$this->mContentId = $row['content_id'];
			$this->mCommentId = $row['comment_id'];
		}
		return count($this->mInfo);
	}

	function verifyComment(&$pParamHash) {
		global $gBitUser;

		if (!$pParamHash['parent_id']) {
			$this->mErrors['parent_id'] = "Missing parent id for comment";
		}

		if (!$pParamHash['root_id']) {
			$this->mErrors['root_id'] = "Missing root id for comment";
		}

		if (empty($pParamHash['anon_name'])) {
			$pParamHash['anon_name']=null;
		}

		if( !@$gBitUser->verifyCaptcha( $pParamHash['captcha'] ) ) {
			$this->mErrors['store'] = tra( 'Incorrect validation code' );
		}

		if( empty( $pParamHash['edit'] ) ) {
			$this->mErrors['store'] = tra( 'Your comment was empty.' );
		} else {
			$dupeQuery = "SELECT `data` FROM `".BIT_DB_PREFIX."liberty_content` WHERE `user_id`=? AND `content_type_guid`='".BITCOMMENT_CONTENT_TYPE_GUID."' AND `ip`=? ORDER BY `last_modified` DESC";
			if( $lastPostData = $this->mDb->getOne( $dupeQuery, array( $gBitUser->mUserId, $_SERVER['REMOTE_ADDR'] ) ) ) {
				if( trim( $lastPostData ) == trim( $pParamHash['edit'] ) ) {
					$this->mErrors['store'] = tra( 'Duplicate comment.' );	
				}
			}
		}

		return (count($this->mErrors) == 0);
	}

	function storeComment( &$pParamHash ) {
		$pParamHash['content_type_guid'] = BITCOMMENT_CONTENT_TYPE_GUID;
		if (!$this->mCommentId) {
			if( $this->verifyComment($pParamHash) && LibertyContent::store( $pParamHash ) ) {
				$this->mCommentId = $this->mDb->GenID( 'liberty_comment_id_seq');


				if (!empty($pParamHash['parent_id'])) {
					$parentComment = new LibertyComment(NULL,$pParamHash['parent_id']);
				}
				$parent_sequence_forward = '';
				$parent_sequence_reverse = '';
				if (!empty($parentComment->mInfo['thread_forward_sequence'])) {
					$parent_sequence_forward = $parentComment->mInfo['thread_forward_sequence'];
					$parent_sequence_reverse = $parentComment->mInfo['thread_reverse_sequence'];
				}
				# if nesting level > 25 deep, put it on level 25
				if (strlen($parent_sequence_forward) > 10*24) {
					$parent_sequence_forward = substr($parent_sequence_forward,0,10*24);
				}

				$this->mInfo['thread_forward_sequence'] = $parent_sequence_forward . sprintf("%09d.",$this->mCommentId);
				$this->mInfo['thread_reverse_sequence'] = strtr($parent_sequence_forward . sprintf("%09d.",$this->mCommentId),
						'0123456789', '9876543210');

				$sql = "INSERT INTO `".BIT_DB_PREFIX."liberty_comments` (`comment_id`, `content_id`, `parent_id`, `root_id`, `anon_name`, `thread_forward_sequence`, `thread_reverse_sequence`) VALUES (?,?,?,?,?,?,?)";

				$this->mDb->query($sql, array($this->mCommentId, $pParamHash['content_id'], $pParamHash['parent_id'],
					$pParamHash['root_id'], $pParamHash['anon_name'],
					$this->mInfo['thread_forward_sequence'], $this->mInfo['thread_reverse_sequence']));
				$this->mInfo['parent_id'] = $pParamHash['parent_id'];
				$this->mInfo['content_id'] = $pParamHash['content_id'];
				$this->mInfo['root_id'] = $pParamHash['root_id'];
				$this->mContentId = $pParamHash['content_id'];
			}
		} else {
			if( $this->verifyComment($pParamHash) && LibertyContent::store($pParamHash) ) {
				$sql = "UPDATE `".BIT_DB_PREFIX."liberty_comments` SET `parent_id` = ?, `content_id`= ? WHERE `comment_id` = ?";
				$this->mDb->query($sql, array($pParamHash['parent_id'], $pParamHash['content_id'], $this->mCommentId));
				$this->mInfo['parent_id'] = $pParamHash['parent_id'];
				$this->mInfo['content_id'] = $pParamHash['content_id'];
				$this->mContentId = $pParamHash['content_id'];
			}
		}
		return (count($this->mErrors) == 0);
	}

	function deleteComment() {
		global $gBitSystem;
		$ret = FALSE;
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$sql = "SELECT `comment_id` FROM `".BIT_DB_PREFIX."liberty_comments` WHERE `parent_id` = ?";
			$rows = $this->mDb->getAll($sql, array($this->mContentId));

			foreach ($rows as $row) {
				$comment = new LibertyComment($row['comment_id']);
				$comment->deleteComment();
			}

			$sql = "DELETE FROM `".BIT_DB_PREFIX."liberty_comments` WHERE `comment_id` = ?";
			$rs = $this->mDb->query($sql, array($this->mCommentId));

			if (method_exists($this,'expungeMetaData')) {
				$this->expungeMetaData();
			} elseif ($gBitSystem->isPackageActive('bitboards')) {
				require_once(BITBOARDS_PKG_PATH.'BitBoardPost.php');
				BitBoardPost::expungeMetaData($this->mContentId);
			}

			if( LibertyAttachable::expunge() ) {
				$ret = TRUE;
				$this->mDb->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
			}
		}
		return $ret;
	}

	function userCanEdit($pUserId = NULL) {
		global $gBitUser;

		if (!empty($pUserId)) {
			$tmpUser = new BitUser($pUserId);
			$tmpUser->load();
		} else {
			$tmpUser = &$gBitUser;
		}
		if($tmpUser->isRegistered()) {
			return ($tmpUser->isAdmin() || ($tmpUser->mUserId == $this->mInfo['user_id']));
		} elseif($this->mInfo['user_id']==ANONYMOUS_USER_ID) {
			return (($_SERVER['REMOTE_ADDR']==$this->mInfo['ip'])&&(($this->mInfo['created']-time())<3600));
		}
		return FALSE;
	}

    /**
    * @param pLinkText name of
    * @param pMixed different possibilities depending on derived class
    * @return the link to display the page.
    */
	function getDisplayUrl( $pLinkText=NULL, $pMixed=NULL ) {
		if( empty( $pMixed ) ) {
			$pMixed = &$this->mInfo;
		}
		$ret = NULL;
		if( @$this->verifyId( $pMixed['parent_id'] ) && $viewContent = LibertyBase::getLibertyObject( $pMixed['parent_id'] ) ) {
			$ret = $viewContent->getDisplayUrl().( @BitBase::verifyId( $pMixed['content_id'] ) ? "#comment_".$pMixed['content_id'] : '' );
		}
		return( $ret );
	}

	//generate a URL to directly access and display a single comment and the associated root content
	function getDisplayUrl2( $pLinkText=NULL, $pMixed=NULL ) {
			if( empty( $pMixed ) ) {
					$pMixed = &$this->mInfo;
			}
			$ret = NULL;
			if( !empty( $pMixed['root_id'] ) && $viewContent = LibertyBase::getLibertyObject( $pMixed['root_id'] ) ) {
					$ret = $viewContent->getDisplayUrl();
					$ret .= "&view_comment_id=" . $pMixed['comment_id'];
			}
			return ( $ret );
	}

	function getDisplayLink( $pLinkText=NULL, $pMixed=NULL ) {
		$anchor = '';
		if( @BitBase::verifyId( $pMixed['content_id'] )) {
			$anchor = "#comment_{$pMixed['content_id']}";
		}
		return parent::getDisplayLink( $pLinkText, $pMixed, $anchor );
	}

	function getList( $pParamHash ) {
		global $gBitSystem;
		if ( !isset( $pParamHash['sort_mode']) or $pParamHash['sort_mode'] == '' ){
			$pParamHash['sort_mode'] = 'last_modified_desc';
		}
		if( empty( $pParamHash['max_records'] ) ) {
			$pParamHash['max_records'] = $gBitSystem->getConfig( 'max_records' );
		}
		LibertyContent::prepGetList( $pParamHash );
		$sort_mode = $this->mDb->convertSortmode($pParamHash['sort_mode']);

		$joinSql = $whereSql = '';
		$bindVars = $ret = array();
		if ( !empty( $pParamHash['content_type_guid'] ) ) {
			$whereSql .= " AND rlc.`content_type_guid`=? ";
			$bindVars[] = $pParamHash['content_type_guid'];
		}

		if ( !empty( $pParamHash['user_id'] ) ) {
			$whereSql .= " AND ptc.`user_id`=? ";
			$bindVars[] = $pParamHash['user_id'];
		}

		if ( !empty( $pParamHash['created_ge'] ) ) {
			$whereSql .= " AND lc.`last_modified`>=? ";
			$bindVars[] = $pParamHash['created_ge'];
		}

		// left outer join on root so updater works

		$query = "SELECT"
			. " lcm.`comment_id` AS comment_id, "
			. " lc.`content_id` AS content_id, "
			. " lcm.`parent_id` AS parent_id, "
			. " lcm.`anon_name` AS anon_name, "
			. " lcm.`root_id` AS root_id, "
			. " lc.`title` AS `content_title`, "
			. " rlc.`title` AS `root_content_title`, "
			. " lc.`created` as created, "
			. " lc.`data` as data, "
			. " lc.`last_modified` as last_modified, "
			. " lc.`title` as title,  "
			. " ptc.content_type_guid as parent_content_type_guid, "
			. " lc.content_type_guid as content_type_guid, "
			. " uu.`login` AS `creator_user`, "
			. " uu.`real_name`"
				  . " FROM `".BIT_DB_PREFIX."liberty_comments` lcm
				  		INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lcm.`content_id`=lc.`content_id` )
			      		INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (uu.`user_id`=lc.`user_id`)
						LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content` rlc ON (rlc.`content_id`=lcm.`root_id` )
						$joinSql ,`".BIT_DB_PREFIX."liberty_content` ptc
				  	 WHERE lcm.`parent_id`=ptc.`content_id` $whereSql
				  	 ORDER BY $sort_mode";
		if( $result = $this->mDb->query($query, $bindVars, $pParamHash['max_records'], $pParamHash['offset']) ) {
			$ret = $result->GetRows();
		}

		return $ret;
	}

	/**
	* Fill title with date if available
	*
	* This will normally be overwriten by extended classes to provide
	* an appropriate title title string
	* @param array mInfo type hash of data to be used to provide base data
	* @return string Descriptive title for the object
	*/
	function getTitle( $pHash=NULL ) {
		global $gBitSmarty;
		$ret = NULL;
		if( empty( $pHash ) ) {
			$pHash = &$this->mInfo;
		}
		if( !empty( $pHash['title'] ) ) {
			$ret = $pHash['title'];
		} elseif( !empty( $pHash['created'] ) ) {
			require_once $gBitSmarty->_get_plugin_filepath( 'modifier', 'bit_short_date' );
			$ret = smarty_modifier_bit_short_date( $pHash['created'] );
		} elseif( !empty( $pHash['content_description'] ) ) {
			$ret = $pHash['content_description'];
		}
		return $ret;
	}


	function getNumComments($pContentId = NULL) {
		$bindVars = NULL;
		if (!$pContentId && $this->mContentId) {
			$mid = '=?';
			$bindVars = array($this->mContentId);
		} elseif (is_array($pContentId)) {
			$mid = 'in ('.implode(',', array_fill(0, count( $pContentId ), '?')).')';
			$bindVars = $pContentId;
		} elseif ($pContentId) {
			$mid = '=?';
			$bindVars = array($pContentId);
		}
		$commentCount = 0;
		if ($bindVars) {
			$sql = "SELECT count(*) as comment_count
					FROM `".BIT_DB_PREFIX."liberty_comments` lcm
					WHERE lcm.`root_id` $mid";
			$commentCount = $this->mDb->getOne($sql, $bindVars);
		}
		return $commentCount;
	}


	// used for direct access to view a single comment
	// see usage in: liberty/comments_inc.php
    // there ought to be a better way to do this...
	function getNumComments_upto($pCommentId = NULL, $pContentId = NULL) {

		$comment = new LibertyComment($pCommentId, $pContentId);

		#assume flat mode
		$comment_fields = $comment->mInfo;
		$last_modified = $comment_fields['last_modified'];
		$contentId = $comment_fields['root_id'];

		$mid = "";

		$sort_order = "ASC";
		$mid = 'last_modified ASC';
		if (!empty($pSortOrder)) {
			if ($pSortOrder == 'commentDate_desc') {
				$mid = 'last_modified DESC';
				}
			if ($pSortOrder == 'commentDate_asc') {
				$mid = 'last_modified ASC';
				}
			if ($pSortOrder == 'thread_asc') {
				$mid = 'thread_forward_sequence  ASC';
				}
			// thread newest first is harder...
			if ($pSortOrder == 'thread_desc') {
				$mid = 'thread_reverse_sequence  ASC';
				}
			}
		$mid = ' order by ' . $mid;

		$commentCount = 0;
		if ($contentId) {
			$sql = "SELECT count(*)
					FROM `".BIT_DB_PREFIX."liberty_comments` tc LEFT OUTER JOIN
					 `".BIT_DB_PREFIX."liberty_content` tcn
					 ON (tc.`content_id` = tcn.`content_id`)
				    where tc.`root_id` =? and `last_modified` < ?
					$mid";
			$commentCount = $this->mDb->getOne($sql, array($contentId, $last_modified));
		}
		return $commentCount;
	}

	// Returns a hash containing the comment tree of comments related to this content
	function getComments( $pContentId = NULL, $pMaxComments = NULL, $pOffset = NULL, $pSortOrder = NULL, $pDisplayMode = NULL ) {
		if( $pDisplayMode != "flat" ) {
			if ($pSortOrder == "commentDate_asc") {
				$pSortOrder = 'thread_asc';
			} else {
				$pSortOrder = 'thread_desc';
			}
		}

		$contentId = NULL;
		$ret = array();
		if (!$pContentId && $this->mContentId) {
			$contentId = $this->mContentId;
		} elseif ($pContentId) {
			$contentId = $pContentId;
		}

		$mid = "";

		$sort_order = "ASC";
		$mid = 'last_modified ASC';
		if (!empty($pSortOrder)) {
			if ($pSortOrder == 'commentDate_desc') {
				$mid = 'last_modified DESC';
			} else if ($pSortOrder == 'commentDate_asc') {
				$mid = 'last_modified ASC';
			} elseif ($pSortOrder == 'thread_asc') {
				$mid = 'thread_forward_sequence  ASC';
			// thread newest first is harder...
			} elseif ($pSortOrder == 'thread_desc') {
				$mid = 'thread_reverse_sequence  ASC';
			} else {
				$mid = $this->mDb->convertSortmode( $pSortOrder );
			}
		}
		$mid = 'order by ' . $mid;

		$bindVars = array();
		if (is_array( $pContentId ) ) {
			$mid2 = 'in ('.implode(',', array_fill(0, count( $pContentId ), '?')).')';
			$bindVars = $pContentId;
			$select1 = ', lcp.`content_type_guid` as parent_content_type_guid, lcp.`title` as parent_title ';
			$join1 = " LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content` lcp ON (lcp.`content_id` = lcom.`parent_id`) ";
		} elseif ($pContentId) {
			$mid2 = '=?';
			$bindVars = array( $pContentId );
			$select1 = '';
			$join1 = '';
		}

		$joinSql = $selectSql = $whereSql = '';
		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars, $this );

		if ($pContentId) {
			$sql = "SELECT lcom.`comment_id`, lcom.`parent_id`, lcom.`root_id`, lcom.`thread_forward_sequence`, lcom.`thread_reverse_sequence`, lcom.`anon_name`, lc.*, uu.`email`, uu.`real_name`, uu.`login` $selectSql $select1
					FROM `".BIT_DB_PREFIX."liberty_comments` lcom
						LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lcom.`content_id` = lc.`content_id`)
						LEFT OUTER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`user_id` = uu.`user_id`) $joinSql $join1
				    WHERE lcom.root_id $mid2 $whereSql $mid";
			$flat_comments = array();
			if( $result = $this->mDb->query( $sql, $bindVars, $pMaxComments, $pOffset ) ) {
				while( $row = $result->FetchRow() ) {
					$row['parsed_data'] = $this->parseData( $row );
					$row['level'] = substr_count ( $row['thread_forward_sequence'], '.' ) - 1;
					$c = new LibertyComment();
					$c->mInfo=$row;
					$row['editable'] = $c->userCanEdit();
					$flat_comments[] = $row;
				}
			}

			# now select comments wanted
			$ret = $flat_comments;

			}
		return $ret;
	}

	function getQuoted() {
		$data = $this->mInfo['data'];
		$pattern = '/\{quote .*\}(.*)\{\/quote\}/i';
		$replacement = '';
		$data = preg_replace($pattern, $replacement, $data);
		return '{quote format_guid="'.$this->mInfo['format_guid'].'" comment_id="'.$this->mCommentId.'" user="'.$this->mInfo['login'].'"}'.trim($data).'{/quote}';
	}

 	// Basic formatting for quoting comments
 	function quoteComment($commentData) {
		$ret = '> '.$commentData;
		$ret = eregi_replace("\n", "\n>", $ret);
		return $ret;
	}
}

?>
