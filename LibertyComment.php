<?php
/**
 * Management of Liberty Content
 *
 * @package  liberty
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/LibertyComment.php,v 1.23 2006/04/12 08:41:45 squareing Exp $
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
			$mid = 'WHERE lc.`comment_id` = ?';
			$bindVars = array($this->mCommentId);
		} else {
			$mid = 'WHERE tcn.`content_id` = ?';
			$bindVars = array($this->mContentId);
		}

		$sql = "SELECT lc.*, tcn.*, uu.`email`, uu.`real_name`, uu.`login`
				FROM `".BIT_DB_PREFIX."liberty_comments` lc LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content` tcn ON (lc.`content_id` = tcn.`content_id`)
					 LEFT OUTER JOIN `".BIT_DB_PREFIX."users_users` uu ON (tcn.`user_id` = uu.`user_id`)
				$mid";
		if( $row = $this->mDb->getRow($sql, $bindVars) ) {
			$this->mInfo = $row;
			$this->mContentId = $row['content_id'];
			$this->mCommentId = $row['comment_id'];
		}
		return count($this->mInfo);
	}

	function verifyComment(&$pStorageHash) {
		if (!$pStorageHash['parent_id']) {
			$this->mErrors['parent_id'] = "Missing parent id for comment";
		}
		if (!$pStorageHash['root_id']) {
			$this->mErrors['root_id'] = "Missing root id for comment";
		}
		return (count($this->mErrors) == 0);
	}

	function storeComment($pStorageHash) {
		$pStorageHash['content_type_guid'] = BITCOMMENT_CONTENT_TYPE_GUID;
		if (!$this->mCommentId) {
			if( LibertyContent::store( $pStorageHash ) ) {
				if ($this->verifyComment($pStorageHash)) {
					$this->mCommentId = $this->mDb->GenID( 'liberty_comment_id_seq');


					if (!empty($pStorageHash['parent_id'])) {
						$parentComment = new LibertyComment(NULL,$pStorageHash['parent_id']);
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

					$sql = "INSERT INTO `".BIT_DB_PREFIX."liberty_comments` (`comment_id`, `content_id`, `parent_id`, `root_id`, `thread_forward_sequence`, `thread_reverse_sequence`) VALUES (?,?,?,?,?,?)";

					$this->mDb->query($sql, array($this->mCommentId, $pStorageHash['content_id'], $pStorageHash['parent_id'],
						$pStorageHash['root_id'], $this->mInfo['thread_forward_sequence'], $this->mInfo['thread_reverse_sequence']));
					$this->mInfo['parent_id'] = $pStorageHash['parent_id'];
					$this->mInfo['content_id'] = $pStorageHash['content_id'];
					$this->mInfo['root_id'] = $pStorageHash['root_id'];
					$this->mContentId = $pStorageHash['content_id'];
				}
			}
		} else {
			if( $this->verifyComment($pStorageHash) && LibertyContent::store($pStorageHash) ) {
				$sql = "UPDATE `".BIT_DB_PREFIX."liberty_comments` SET `parent_id` = ?, `content_id`= ? WHERE `comment_id` = ?";
				$this->mDb->query($sql, array($pStorageHash['parent_id'], $pStorageHash['content_id'], $this->mCommentId));
				$this->mInfo['parent_id'] = $pStorageHash['parent_id'];
				$this->mInfo['content_id'] = $pStorageHash['content_id'];
				$this->mContentId = $pStorageHash['content_id'];
			}
		}
		return (count($this->mErrors) == 0);
	}

	function deleteComment() {
		$sql = "SELECT `comment_id` FROM `".BIT_DB_PREFIX."liberty_comments` WHERE `parent_id` = ?";
		$rows = $this->mDb->getAll($sql, array($this->mContentId));

		foreach ($rows as $row) {
			$comment = new LibertyComment($row['comment_id']);
			$comment->deleteComment();
		}

		$sql = "DELETE FROM `".BIT_DB_PREFIX."liberty_comments` WHERE `comment_id` = ?";
		$rs = $this->mDb->query($sql, array($this->mCommentId));

		$sql = "DELETE FROM `".BIT_DB_PREFIX."liberty_content` WHERE `content_id` = ?";
		$rs = $this->mDb->query($sql, array($this->mContentId));
	}

	function userCanEdit($pUserId = NULL) {
		global $gBitUser;

		if (empty($pUserId)) {
			if (!empty($gBitUser)) {
				return ($gBitUser->isAdmin() || $gBitUser->mUserId == $this->mInfo['user_id']);
			} else {
				return FALSE;
			}
		}
		if ($pUserId == $this->mInfo['user_id']) {
			return TRUE;
		}
		$tmpUser = new BitUser($pUserId);
		$tmpUser->load();
		return ($tmpUser->isAdmin());
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
			$ret = $viewContent->getDisplayUrl();
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

	function getList( $pParamHash ) {
		global $gBitSystem;
		if ( !isset( $pParamHash['sort_mode']) or $pParamHash['sort_mode'] == '' ){
			$pParamHash['sort_mode'] = 'last_modified_desc';
		}
		if( empty( $pParamHash['max_records'] ) ) {
			$pParamHash['max_records'] = $gBitSystem->getConfig( 'max_records' );
		}
		LibertyContent::prepGetList( $pParamHash );
		$sort_mode = $this->mDb->convert_sortmode($pParamHash['sort_mode']);

		$joinSql = '';
		$whereSql = '';
		$bindVars = array();
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
			. " lcm.`comment_id` as comment_id, "
			. " lc.`content_id` as content_id, "
			. " lcm.`parent_id` as parent_id, "
			. " lcm.`root_id` as root_id, "
			. " lc.`title` AS `content_title`, "
			. " rlc.`title` AS `root_content_title`, "
			. " lc.`created` as created, "
			. " lc.`data` as data, "
			. " lc.`last_modified` as last_modified, "
			. " lc.`title` as title,  "
			. " ptc.content_type_guid as parent_content_type_guid, "
			. " lc.content_type_guid as content_type_guid, "
			. " uu.`login` AS `user`, "
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
				    where tc.root_id =? and last_modified < ?
					$mid";
			$commentCount = $this->mDb->getOne($sql, array($contentId, $last_modified));
		}
		return $commentCount;
	}


	//input is a set of nested hashes
	//output is a single flat hash of comments in thread order
//	function flatten_threads($threaded_comments = NULL) {
//		$flat_comments = array();
//		foreach ($threaded_comments as $threaded_comment) {
//			$flat_comments[] = $threaded_comment;
//			if (!empty($threaded_comment['children'])) {
//				$children = $this->flatten_threads($threaded_comment['children']);
//				foreach ($children as $child) {
//					array_push($flat_comments, $child );
//				}
//			}
//		}
//		return $flat_comments;
//	}

	// Returns a hash containing the comment tree of comments related to this content
	function getComments( $pContentId = NULL, $pMaxComments = NULL, $pOffset = NULL, $pSortOrder = NULL, $pDisplayMode = NULL ) {
		if( $pDisplayMode == "flat" ) {
			return $this->getComments_flat ($pContentId, $pMaxComments, $pOffset, $pSortOrder);
		} else {
			#return $this->getComments_threaded ($pContentId, $pMaxComments, $pOffset, $pSortOrder);
			//use flat mode retreival so we get exactly the number of messages requested.
			if ($pSortOrder == "commentDate_asc") {
				return $this->getComments_flat ($pContentId, $pMaxComments, $pOffset, 'thread_asc');
			} else {
				//descending thread order is not currently supported correctly, but we make an attempt anyway
				return $this->getComments_flat ($pContentId, $pMaxComments, $pOffset, 'thread_desc');
			}
		}
	}

	// returns a set of nested hashes
	function getComments_threaded( $pContentId = NULL, $pMaxComments = NULL, $pOffset = NULL, $pSortOrder = NULL ) {
		static $curLevel = 0;

		$contentId = NULL;
		$ret = array();
		if (!$pContentId && $this->mContentId) {
			$contentId = $this->mContentId;
		} elseif ($pContentId) {
			$contentId = $pContentId;
		}


		$sort_order = "ASC";
		if (!empty($pSortOrder)) {
			if ($pSortOrder == 'commentDate_desc') {
				$sort_order = 'DESC';
			} elseif ($pSortOrder == 'commentDate_asc') {
				$sort_order = 'ASC';
			}
		}

		if ($contentId) {
			$sql = "SELECT lcom.`comment_id` FROM `".BIT_DB_PREFIX."liberty_comments` lcom, `".BIT_DB_PREFIX."liberty_content` lc
					WHERE lcom.`parent_id` = ? AND lcom.`content_id` = lc.`content_id` ORDER BY lc.`created` $sort_order";
			if( $rows = $this->mDb->getAll( $sql, array($contentId), $pMaxComments, $pOffset ) ) {
				foreach ($rows as $row) {
					$comment = new LibertyComment( $row['comment_id'] );
					$comment->mInfo['level'] = $curLevel;
					$curLevel++;
					$comment->mInfo['children'] = $this->getComments_threaded($comment->mInfo['content_id']);
					$comment->mInfo['parsed_data'] = $this->parseData($comment->mInfo);
					$curLevel--;
					$ret[] = $comment->mInfo;
				}
			}
		}
		return $ret;
	}


	// Returns a hash containing the comment tree of comments related to this content
	function getComments_flat( $pContentId = NULL, $pMaxComments = NULL, $pOffset = NULL, $pSortOrder = NULL ) {
		static $curLevel = 0;

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
				$mid = $this->mDb->convert_sortmode( $pSortOrder );
			}
		}
		$mid = 'order by ' . $mid;

		if (is_array( $pContentId ) ) {
			$mid2 = 'in ('.implode(',', array_fill(0, count( $pContentId ), '?')).')';
			$bindVars = $pContentId;
		} elseif ($pContentId) {
			$mid2 = '=?';
			$bindVars = array( $pContentId );
		}

		if ($pContentId) {
			$sql = "SELECT tc.comment_id, tc.parent_id, tc.root_id, tc.thread_forward_sequence, tc.thread_reverse_sequence, tcn.*, uu.`email`, uu.`real_name`, uu.`login`
					FROM `".BIT_DB_PREFIX."liberty_comments` tc
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content` tcn ON (tc.`content_id` = tcn.`content_id`)
					LEFT OUTER JOIN `".BIT_DB_PREFIX."users_users` uu ON (tcn.`user_id` = uu.`user_id`)
				    where tc.root_id $mid2 $mid";
			$flat_comments = array();
			if( $result = $this->mDb->query($sql,$bindVars,$pMaxComments,$pOffset) ) {
				while( $row = $result->FetchRow() ) {
					$row['parsed_data'] = $this->parseData($row);
					$row['level'] = substr_count ( $row['thread_forward_sequence'], '.' ) - 1;
					$flat_comments[] = $row;
				}
			}

			# now select comments wanted
			$ret = $flat_comments;

			}
		return $ret;
	}



 	// Basic formatting for quoting comments
 	function quoteComment($commentData) {
		$ret = '> '.$commentData;
		$ret = eregi_replace("\n", "\n>", $ret);
		return $ret;
	}
}

?>
