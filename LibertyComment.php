<?php
/**
 * Management of Liberty Content
 *
 * @package  liberty
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/LibertyComment.php,v 1.11 2006/02/06 00:09:01 squareing Exp $
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

	function LibertyComment($pCommentId = NULL, $pContentId = NULL) {
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

		$sql = "SELECT lc.*, tcn.*, uu.`email`, uu.`real_name`, uu.`login` AS `user`
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
		return (count($this->mErrors) == 0);
	}

	function storeComment($pStorageHash) {
		$pStorageHash['content_type_guid'] = BITCOMMENT_CONTENT_TYPE_GUID;
		if (!$this->mCommentId) {
			if( LibertyContent::store( $pStorageHash ) ) {
				if ($this->verifyComment($pStorageHash)) {
					$this->mCommentId = $this->mDb->GenID( 'liberty_comment_id_seq');
					$sql = "INSERT INTO `".BIT_DB_PREFIX."liberty_comments` (`comment_id`, `content_id`, `parent_id`) VALUES (?,?,?)";
					$this->mDb->query($sql, array($this->mCommentId, $pStorageHash['content_id'], $pStorageHash['parent_id']));
					$this->mInfo['parent_id'] = $pStorageHash['parent_id'];
					$this->mInfo['content_id'] = $pStorageHash['content_id'];
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
		$rs = $this->mDb->query($sql, array($this->mContentId));

		$rows = $rs->getRows();
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


	function getList( $pParamHash ) {
		global $gBitSystem;
		if ( !isset( $pParamHash['sort_mode']) or $pParamHash['sort_mode'] == '' ){
			$pParamHash['sort_mode'] = 'last_modified_desc';
		}
		if( empty( $pParamHash['max_records'] ) ) {
			$pParamHash['max_records'] = $gBitSystem->getPreference( 'max_records' );
		}
		LibertyContent::prepGetList( $pParamHash );
		$sort_mode = $this->mDb->convert_sortmode($pParamHash['sort_mode']);

		$mid = '';
		$bindVars = array();
		if ( !empty( $pParamHash['content_type_guid'] ) ) {
			$mid .= " AND lc.`content_type_guid`=? ";
			$bindVars[] = $pParamHash['content_type_guid'];
		}
		if ( @$this->verifyId( $pParamHash['user_id'] ) ) {
			$mid .= " AND lc.`user_id`=? ";
			$bindVars[] = $pParamHash['user_id'];
		}

		$query = "SELECT DISTINCT( lc.`content_id` ), lc.`title` AS `content_title`, lc.`created`, tcmc.`last_modified`, tcmc.`title`, uu.`login` AS `user`, uu.`real_name`
				  FROM `".BIT_DB_PREFIX."liberty_comments` lcom INNER JOIN `".BIT_DB_PREFIX."liberty_content` tcmc ON (lcom.`content_id`=tcmc.`content_id` ), `".BIT_DB_PREFIX."liberty_content` lc, `".BIT_DB_PREFIX."users_users` uu
				  WHERE lcom.`parent_id`=lc.`content_id` AND uu.`user_id`=tcmc.`user_id` $mid  ORDER BY $sort_mode";
		if( $result = $this->mDb->query($query, $bindVars, $pParamHash['max_records'], $pParamHash['offset']) ) {
			$ret = $result->GetRows();
		}

		return $ret;
	}


	function getNumComments($pContentId = NULL) {
		$contentId = NULL;
		if (!$pContentId && $this->mContentId) {
			$contentId = $this->mContentId;
		} elseif ($pContentId) {
			$contentId = $pContentId;
		}
		$commentCount = 0;
		if ($contentId) {
			$sql = "SELECT lcom.*, tcmc.`parent_id` AS `child_content_id`
					FROM `".BIT_DB_PREFIX."liberty_comments` lcom LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_comments` tcmc ON (lcom.`content_id`=tcmc.`parent_id`)
					WHERE lcom.`parent_id` = ?";
			$rows = $this->mDb->getAssoc($sql, array($contentId));
			$commentCount += count($rows);
			foreach ($rows as $row) {
				if( @$this->verifyId( $row['child_content_id'] ) ) {
					$commentCount += $this->getNumComments( $row['child_content_id'] );
				}
			}
		}
		return $commentCount;
	}


	//input is a set of nested hashes
	//output is a single flat hash of comments in thread order
	function flatten_threads($threaded_comments = NULL) {
		$flat_comments = array();
		foreach ($threaded_comments as $threaded_comment) {
			$flat_comments[] = $threaded_comment;
			if (!empty($threaded_comment['children'])) {
				$children = $this->flatten_threads($threaded_comment['children']);
				foreach ($children as $child) {
					array_push($flat_comments, $child );
				}
			}
		}
		return $flat_comments;	
	}

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
					$comment->mInfo['parsed_data'] = $this->parseData($comment->mInfo['data'], $comment->mInfo['format_guid']);
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

		$sort_order = "ASC";
		if (!empty($pSortOrder)) {
			if ($pSortOrder == 'commentDate_desc') {
				$sort_order = 'DESC';
				}
			if ($pSortOrder == 'commentDate_asc') {
				$sort_order = 'ASC';
				}
			if ($pSortOrder == 'thread_asc') {
				$sort_order = 'TASC';
				}
			// thread newest first is harder...
			if ($pSortOrder == 'thread_desc') {
				$sort_order = 'TDESC';
				}
			}

		if ($contentId) {

			if ($sort_order == 'TDESC') {
			
				$threaded_comments = $this->getComments_threaded($pContentId, 999999, 0, 'commentDate_desc');
				}
			else {
				$threaded_comments = $this->getComments_threaded($pContentId, 999999, 0);
				}
	
			# DB not structured to make FLAT easy
			# have to retreive entire threaded comment set and then date sort in memory
			# then take the chunk of N comments wanted.

			# now flatten into a linear array
			$flat_comments = $this->flatten_threads($threaded_comments);

			# now sort by date
			$last_modified = array();
			foreach ($flat_comments as $key => $comment) {
				if (!empty( $comment['last_modified'])) {
				   $last_modified[$key]  = $comment['last_modified'];
				}
				   $comment['children'] = array();
			}

			if ($sort_order == 'ASC') {
				array_multisort($last_modified, SORT_ASC, $flat_comments);
				}
			elseif ($sort_order == 'DESC') {
				array_multisort($last_modified, SORT_DESC, $flat_comments);
				}
			// else default order
	
			# now select comments wanted
			$ret = array_slice($flat_comments,$pOffset,$pMaxComments);

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
