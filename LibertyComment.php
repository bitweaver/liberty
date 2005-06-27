<?php
/**
 * Management of Liberty Content
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.2.2.3 $
 * @package  Liberty
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
* @author   spider <spider@steelsun.com>
* @package  Liberty
* @subpackage  LibertyComment
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
			$mid = 'WHERE tc.`comment_id` = ?';
			$bindVars = array($this->mCommentId);
		} else {
			$mid = 'WHERE tcn.`content_id` = ?';
			$bindVars = array($this->mContentId);
		}

		$sql = "SELECT tc.*, tcn.*, uu.`email`, uu.`real_name`, uu.`login` AS `user`
				FROM `".BIT_DB_PREFIX."tiki_comments` tc LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content` tcn ON (tc.`content_id` = tcn.`content_id`)
					 LEFT OUTER JOIN `".BIT_DB_PREFIX."users_users` uu ON (tcn.`user_id` = uu.`user_id`)
				$mid";
		$rs = $this->query($sql, $bindVars);

		if ($rs && $rs->numRows()) {
			$this->mInfo = $rs->fields;
			$this->mContentId = $rs->fields['content_id'];
			$this->mCommentId = $rs->fields['comment_id'];
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
					$this->mCommentId = $this->GenID( 'tiki_comments_comment_id_seq');
					$sql = "INSERT INTO `".BIT_DB_PREFIX."tiki_comments` (`comment_id`, `content_id`, `parent_id`) VALUES (?,?,?)";
					$this->query($sql, array($this->mCommentId, $pStorageHash['content_id'], $pStorageHash['parent_id']));
					$this->mInfo['parent_id'] = $pStorageHash['parent_id'];
					$this->mInfo['content_id'] = $pStorageHash['content_id'];
					$this->mContentId = $pStorageHash['content_id'];
				}
			}
		} else {
			if( $this->verifyComment($pStorageHash) && LibertyContent::store($pStorageHash) ) {
				$sql = "UPDATE `".BIT_DB_PREFIX."tiki_comments` SET `parent_id` = ?, `content_id`= ? WHERE `comment_id` = ?";
				$this->query($sql, array($pStorageHash['parent_id'], $pStorageHash['content_id'], $this->mCommentId));
				$this->mInfo['parent_id'] = $pStorageHash['parent_id'];
				$this->mInfo['content_id'] = $pStorageHash['content_id'];
				$this->mContentId = $pStorageHash['content_id'];
			}
		}
		return (count($this->mErrors) == 0);
	}

	function deleteComment() {
		$sql = "SELECT `comment_id` FROM `".BIT_DB_PREFIX."tiki_comments` WHERE `parent_id` = ?";
		$rs = $this->query($sql, array($this->mContentId));

		$rows = $rs->getRows();
		foreach ($rows as $row) {
			$comment = new LibertyComment($row['comment_id']);
			$comment->deleteComment();
		}

		$sql = "DELETE FROM `".BIT_DB_PREFIX."tiki_comments` WHERE `comment_id` = ?";
		$rs = $this->query($sql, array($this->mCommentId));

		$sql = "DELETE FROM `".BIT_DB_PREFIX."tiki_content` WHERE `content_id` = ?";
		$rs = $this->query($sql, array($this->mContentId));
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
		if( !empty( $pMixed['parent_id'] ) && $viewContent = LibertyBase::getLibertyObject( $pMixed['parent_id'] ) ) {
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
			$pParamHash['max_records'] = $gBitSystem->getPreference( 'maxRecords' );
		}
		LibertyContent::prepGetList( $pParamHash );
		$sort_mode = $this->convert_sortmode($pParamHash['sort_mode']);

		$mid = '';
		$bindVars = array();
		if ( !empty( $pParamHash['content_type_guid'] ) ) {
			$mid .= " AND tc.`content_type_guid`=? ";
			$bindVars[] = $pParamHash['content_type_guid'];
		}
		if ( !empty( $pParamHash['user_id'] ) ) {
			$mid .= " AND tc.`user_id`=? ";
			$bindVars[] = $pParamHash['user_id'];
		}

		$query = "SELECT DISTINCT( tc.`content_id` ), tc.`title` AS `content_title`, tc.`created`, tcmc.`last_modified`, tcmc.`title`, uu.`login` AS `user`, uu.`real_name`
				  FROM `".BIT_DB_PREFIX."tiki_comments` tcm INNER JOIN `".BIT_DB_PREFIX."tiki_content` tcmc ON (tcm.`content_id`=tcmc.`content_id` ), `".BIT_DB_PREFIX."tiki_content` tc, `".BIT_DB_PREFIX."users_users` uu
				  WHERE tcm.`parent_id`=tc.`content_id` AND uu.`user_id`=tcmc.`user_id` $mid  ORDER BY $sort_mode";
		if( $result = $this->query($query, $bindVars, $pParamHash['max_records'], $pParamHash['offset']) ) {
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
			$sql = "SELECT tcm.*, tcmc.`parent_id` AS `child_content_id`
					FROM `".BIT_DB_PREFIX."tiki_comments` tcm LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_comments` tcmc ON (tcm.`content_id`=tcmc.`parent_id`)
					WHERE tcm.`parent_id` = ?";
			$rows = $this->GetAssoc($sql, array($contentId));
			$commentCount += count($rows);
/* We do not want to count the children since they will be displayed on the page and it would mess up pagination - spiderr
   I may be wrong, so I'll leave the complete code in for now
			foreach ($rows as $row) {
				if( !empty( $row['child_content_id'] ) ) {
					$commentCount += $this->getNumComments( $row['child_content_id'] );
				}
			}
*/
		}
		return $commentCount;
	}

	// Returns a hash containing the comment tree of comments related to this content
	function getComments( $pContentId = NULL, $pMaxComments = NULL, $pOffset = NULL ) {
		static $curLevel = 0;

		$contentId = NULL;
		$ret = array();
		if (!$pContentId && $this->mContentId) {
			$contentId = $this->mContentId;
		} elseif ($pContentId) {
			$contentId = $pContentId;
		}
		if ($contentId) {
			$sql = "SELECT tcm.`comment_id` FROM `".BIT_DB_PREFIX."tiki_comments` tcm, `".BIT_DB_PREFIX."tiki_content` tc
					WHERE tcm.`parent_id` = ? AND tcm.`content_id` = tc.`content_id` ORDER BY tc.`created`";
			if( $rs = $this->query( $sql, array($contentId), $pMaxComments, $pOffset ) ) {
				$rows = $rs->getRows();
				foreach ($rows as $row) {
					$comment = new LibertyComment($row['comment_id']);
					$comment->mInfo['level'] = $curLevel;
					$curLevel++;
					$comment->mInfo['children'] = $this->getComments($comment->mInfo['content_id']);
					$comment->mInfo['parsed_data'] = $this->parseData($comment->mInfo['data'], $comment->mInfo['format_guid']);
					$curLevel--;
					$ret[] = $comment->mInfo;
				}
			}
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
