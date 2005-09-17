<?php
/**
 * Management of Liberty Content
 *
 * @package  liberty
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/LibertyStructure.php,v 1.1.1.1.2.15 2005/09/17 16:02:16 squareing Exp $
 * @author   spider <spider@steelsun.com>
 */

/**
 * required setup
 */
require_once( LIBERTY_PKG_PATH.'LibertyBase.php' );

/**
 * System class for handling the liberty package
 *
 * @package liberty
 */
class LibertyStructure extends LibertyBase {
	var $mStructureId;

	function LibertyStructure ( $pStructureId=NULL, $pContentId=NULL ) {
		// we need to init our database connection early
		LibertyBase::LibertyBase();
		$this->mStructureId = $pStructureId;
		$this->mContentId = $pContentId;
	}

	function load() {
		if( $this->mStructureId || $this->mContentId ) {
			if( $this->mInfo = $this->getNode( $this->mStructureId, $this->mContentId ) ) {
				global $gLibertySystem;
				$this->mStructureId = $this->mInfo['structure_id'];
				$this->mContentId = $this->mInfo['content_id'];
				$this->mInfo['content_type'] = $gLibertySystem->mContentTypes[$this->mInfo['content_type_guid']];
			}
		}
		return( $this->mInfo && count( $this->mInfo ) );
	}

	function getNode( $pStructureId=NULL, $pContentId=NULL ) {
		global $gLibertySystem, $gBitSystem;
		$contentTypes = $gLibertySystem->mContentTypes;
		$ret = NULL;
		$query = 'SELECT ts.*, tc.`user_id`, tc.`title`, tc.`content_type_guid`, uu.`login`, uu.`real_name`
				  FROM `'.BIT_DB_PREFIX.'tiki_structures` ts
				  INNER JOIN `'.BIT_DB_PREFIX.'tiki_content` tc ON (ts.`content_id`=tc.`content_id`)
				  LEFT JOIN `'.BIT_DB_PREFIX.'users_users` uu ON ( uu.`user_id` = tc.`user_id` )';

		if( is_numeric( $pStructureId ) ) {
			$query .= ' WHERE ts.`structure_id`=?';
			$bindVars = array( $pStructureId );
		} elseif( is_numeric( $pContentId ) ) {
			$query .= ' WHERE ts.`content_id`=?';
			$bindVars = array( $pContentId );
		}

		if( $result = $this->mDb->query( $query, $bindVars ) ) {
			$ret = $result->fields;
		}

		if( !empty( $contentTypes[$ret['content_type_guid']] ) ) {
			// quick alias for code readability
			$type = &$contentTypes[$ret['content_type_guid']];
			if( empty( $type['content_object'] ) ) {
				// create *one* object for each object *type* to  call virtual methods.
				include_once( $gBitSystem->mPackages[$type['handler_package']]['path'].$type['handler_file'] );
				$type['content_object'] = new $type['handler_class']();
			}
			$ret['title'] = $type['content_object']->getTitle( $ret );
		}

		return $ret;
	}

	function isRootNode() {
		$ret = FALSE;
		if( !empty( $this->mInfo['structure_id'] ) ) {
			$ret = $this->mInfo['root_structure_id'] == $this->mInfo['structure_id'];
		}
		return $ret;
	}

	function getRootTitle() {
		$ret = NULL;
		if( isset( $this->mInfo['structure_path'][0]['title'] ) ) {
			$ret = $this->mInfo['structure_path'][0]['title'];
		}
		return $ret;
	}

	// This is a utility function mainly used for upgrading sites.
	function setTreeRoot( $pRootId, $pTree ) {
		foreach( $pTree as $structRow ) {
			$this->mDb->query( "UPDATE `".BIT_DB_PREFIX."tiki_structures` SET `root_structure_id`=? WHERE `structure_id`=?", array( $pRootId, $structRow["structure_id"] ) );
			if( !empty( $structRow["sub"] ) ) {
				$this->setTreeRoot( $pRootId, $structRow["sub"] );
			}
		}
	}


	function isValid() {
		return( !empty( $this->mStructureId ) && is_numeric( $this->mStructureId ) );
	}

	function loadNavigation() {
		if( $this->isValid() ) {
			$this->mInfo["prev"] = null;
			// Get structure info for this page
			if( !$this->isRootNode() && ($prev_structure_id = $this->get_prev_page( $this->mStructureId )) ) {
				$this->mInfo["prev"]   = $this->getNode($prev_structure_id);
			}
			$next_structure_id = $this->get_next_page( $this->mStructureId );
			$this->mInfo["next"] = null;
			if (isset($next_structure_id)) {
				$this->mInfo["next"]   = $this->getNode( $next_structure_id) ;
			}
			$this->mInfo["parent"] = $this->s_get_parent_info( $this->mStructureId );
			$this->mInfo["home"]   = $this->getNode( $this->mStructureId );
		}
 		return TRUE;
	}

	function loadPath() {
		if( $this->isValid() ) {
			$this->mInfo['structure_path'] = $this->getPath( $this->mStructureId );
		}
		return( !empty( $this->mInfo['structure_path'] ) );
	}

	/**
	* This can be used to construct a path from the structure head to the requested page.
	* @returns an array of page_info arrays.
	*/
	function getPath( $pStructureId ) {
		$structure_path = array();
		$page_info = $this->getNode($pStructureId);

		if ($page_info["parent_id"]) {
			$structure_path = $this->getPath($page_info["parent_id"]);
		}
		$structure_path[] = $page_info;
		return $structure_path;
	}


	function getSubTree( $pStructureId, $level = 0, $parent_pos = '' ) {
		global $gLibertySystem, $gBitSystem;
		if( !empty( $pStructureId ) ) {
			$ret = array();
			$pos = 1;
			//The structure page is used as a title
			if ($level == 0) {
				$struct_info = $this->getNode( $pStructureId );
				$aux["first"]       = true;
				$aux["last"]        = true;
				$aux["level"]       = $level;
				$aux["pos"]         = '';
				$aux["structure_id"] = $struct_info["structure_id"];
				$aux["title"]    = $struct_info["title"];
				$aux["page_alias"]  = $struct_info["page_alias"];
				$ret[] = $aux;
				$level++;
			}

				//Get all child nodes for this structure_id
			$query = "SELECT ts.`content_id`, ts.`structure_id`, ts.`page_alias`, tc.`user_id`, tc.`title`, tc.`content_type_guid`, uu.`login`, uu.`real_name`
				FROM `".BIT_DB_PREFIX."tiki_structures` ts, `".BIT_DB_PREFIX."tiki_content` tc
				LEFT JOIN `".BIT_DB_PREFIX."users_users` uu ON ( uu.`user_id` = tc.`user_id` )
				WHERE tc.`content_id` = ts.`content_id` AND `parent_id`=? ORDER BY `pos` asc";
			$result = $this->mDb->query($query,array( $pStructureId ) );

			$subs = array();
			$row_max = $result->numRows();
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
					$aux['title'] = $type['content_object']->getTitle( $aux );
					$aux["first"]       = ($pos == 1);
					$aux["last"]        = false;
					$aux["level"]       = $level;
					if (strlen($parent_pos) == 0) {
						$aux["pos"] = "$pos";
					}
					else {
						$aux["pos"] = $parent_pos . '.' . "$pos";
					}
					$ret[] = $aux;

					//Recursively add any child nodes
						$subs = $this->getSubTree($res["structure_id"], ($level + 1), $aux["pos"]);
					if(isset($subs)) {
						$ret = array_merge($ret, $subs);
					}
					// Insert a dummy entry to close table/list
					if ($pos == $row_max) {
						$aux["first"] = false;
						$aux["last"]  = true;
						$ret[] = $aux;
					}

					$pos++;
				}
			}
		}
		return $ret;
	}


	function getList( &$pListHash ) {
		global $gBitSystem;

		$this->prepGetList( $pListHash );

		if( !empty( $pListHash['find'] ) ) {
			$findesc = '%' . $pListHash['find'] . '%';
			$mid = " (`parent_id` is null or `parent_id`=0) and (tc.`title` like ?)";
			$bindVars=array($findesc);
		} else {
			$mid = " (`parent_id` is null or `parent_id`=0) ";
			$bindVars=array();
		}

		if( !empty( $pListHash['user_id'] ) ) {
			$mid .= " AND tc.`user_id` = ? ";
			array_push( $bindVars, $pListHash['user_id'] );
		}

		if( !empty( $pListHash['content_type_guid'] ) ) {
			$mid .= " AND tc.`content_type_guid`=? ";
			array_push( $bindVars, $pListHash['content_type_guid'] );
		}
		$query = "SELECT ts.`structure_id`, ts.`parent_id`, ts.`content_id`, `page_alias`, `pos`, tc.`title`, `hits`, `data`, `last_modified`, tc.`modifier_user_id`, `ip`, tc.`user_id` AS `creator_user_id`, uu.`login` AS `user`, uu.`real_name` , uu.`email`
		          FROM `".BIT_DB_PREFIX."tiki_structures` ts INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON ( ts.`content_id` = tc.`content_id` ) INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON ( tc.`user_id` = uu.`user_id` )
				  WHERE $mid
				  ORDER BY ".$this->mDb->convert_sortmode($pListHash['sort_mode']);
		$query_cant = "SELECT count(*)
					   FROM `".BIT_DB_PREFIX."tiki_structures` ts INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON ( ts.`content_id` = tc.`content_id` )
					   WHERE $mid";
		$result = $this->mDb->query($query,$bindVars,$pListHash['max_records'],$pListHash['offset']);
		$cant = $this->mDb->getOne($query_cant,$bindVars);
		$ret = array();

		while ($res = $result->fetchRow()) {
			if( $gBitSystem->isPackageActive( 'bithelp' ) && file_exists(BITHELP_PKG_PATH.$res['title'].'/index.html')) {
			  $res['webhelp']='y';
			} else {
			  $res['webhelp']='n';
			}
			$ret[] = $res;
		}

		$retval = array();
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		return $retval;
	}

	function verifyNode( &$pParamHash ) {
		if( empty( $pParamHash['content_id'] ) || !is_numeric( $pParamHash['content_id'] ) ) {
			$this->mErrors['content'] = 'Could not store structure. Invalid content id. '.$pParamHash['content_id'];
		} else {
			if( empty( $pParamHash['parent_id'] ) || !is_numeric( $pParamHash['parent_id'] ) ) {
				$pParamHash['parent_id'] = 0;
			}
			if( empty( $pParamHash['alias'] ) ) {
				$pParamHash['alias'] = '';
			}
			if( isset( $pParamHash['after_ref_id'] ) ) {
				$pParamHash['max'] = $this->mDb->getOne("select `pos` from `".BIT_DB_PREFIX."tiki_structures` where `structure_id`=?",array((int)$pParamHash['after_ref_id']));
			} else {
				$pParamHash['max'] = $this->mDb->getOne("select max(`pos`) from `".BIT_DB_PREFIX."tiki_structures` where `parent_id`=?",array((int)$pParamHash['parent_id']));
			}
			if( $pParamHash['max'] > 0 ) {
				//If max is 5 then we are inserting after position 5 so we'll insert 5 and move all
				// the others
				$query = "update `".BIT_DB_PREFIX."tiki_structures` set `pos`=`pos`+1 where `pos`>? and `parent_id`=?";
				$result = $this->mDb->query($query,array((int)$pParamHash['max'], (int)$pParamHash['parent_id']));
			}
			$pParamHash['max']++;
		}
		return( count( $this->mErrors ) == 0 );
	}

    /**  Create a structure entry with the given name
	* @param parent_id The parent entry to add this to. If NULL, create new structure.
	* @param after_ref_id The entry to add this one after. If NULL, put it in position 0.
	* @param name The wiki page to reference
	* @param alias An alias for the wiki page name.
	* @return the new entries structure_id or null if not created.
	*/
	function storeNode( &$pParamHash ) {
        global $gBitSystem;
        $ret = null;
        // If the page doesn't exist then create a new wiki page!
		$now = $gBitSystem->getUTCTime();
//		$created = $this->create_page($name, 0, '', $now, tra('created from structure'), 'system', '0.0.0.0', '');
		// if were not trying to add a duplicate structure head
		if ( $this->verifyNode( $pParamHash ) ) {
			$this->mDb->StartTrans();

            //Create a new structure entry
			$pParamHash['structure_id'] = $this->mDb->GenID( 'tiki_structures_id_seq' );
			if( empty( $pParamHash['root_structure_id'] ) || !is_numeric( $pParamHash['root_structure_id'] ) ) {
				$pParamHash['root_structure_id'] = $pParamHash['structure_id'];
			}
			$query = "INSERT INTO `".BIT_DB_PREFIX."tiki_structures`( `structure_id`, `parent_id`,`content_id`, `root_structure_id`, `page_alias`, `pos` ) values(?,?,?,?,?,?)";
			$result = $this->mDb->query( $query, array( $pParamHash['structure_id'], $pParamHash['parent_id'], (int)$pParamHash['content_id'], (int)$pParamHash['root_structure_id'], $pParamHash['alias'], $pParamHash['max'] ) );
			$this->mDb->CompleteTrans();
			$ret = $pParamHash['structure_id'];
		} else {
			//vd( $this->mErrors );
		}
		return $ret;
	}

	function moveNodeWest() {
		if( $this->isValid() ) {
			//If there is a parent and the parent isnt the structure root node.
			$this->mDb->StartTrans();
			if( !empty( $this->mInfo["parent_id"] ) ) {
				$parentNode = $this->getNode( $this->mInfo["parent_id"] );
				if( !empty( $parentNode['parent_id'] ) ) {
					//Make a space for the node after its parent
					$query = "update `".BIT_DB_PREFIX."tiki_structures` set `pos`=`pos`+1 where `pos`>? and `parent_id`=?";
					$this->mDb->query( $query, array( $parentNode['pos'], $parentNode['parent_id'] ) );
					//Move the node up one level
					$query = "update `".BIT_DB_PREFIX."tiki_structures` set `parent_id`=?, `pos`=(? + 1) where `structure_id`=?";
					$this->mDb->query($query, array( $parentNode['parent_id'], $parentNode['pos'], $this->mStructureId ) );
				}
			}
			$this->mDb->CompleteTrans();
		}
	}

	function moveNodeEast() {
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$query = "select `structure_id`, `pos` from `".BIT_DB_PREFIX."tiki_structures` where `pos`<? and `parent_id`=? order by `pos` desc";
			$result = $this->mDb->query($query,array($this->mInfo["pos"], (int)$this->mInfo["parent_id"]));
			if ($previous = $result->fetchRow()) {
				//Get last child nodes for previous sibling
				$query = "select `pos` from `".BIT_DB_PREFIX."tiki_structures` where `parent_id`=? order by `pos` desc";
				$result = $this->mDb->query($query,array((int)$previous["structure_id"]));
				if ($res = $result->fetchRow()) {
					$pos = $res["pos"];
				} else{
					$pos = 0;
				}
				$query = "update `".BIT_DB_PREFIX."tiki_structures` set `parent_id`=?, `pos`=(? + 1) where `structure_id`=?";
				$this->mDb->query( $query, array((int)$previous["structure_id"], (int)$pos, (int)$this->mStructureId) );
				//Move nodes up below that had previous parent and pos
				$query = "update `".BIT_DB_PREFIX."tiki_structures` set `pos`=`pos`-1 where `pos`>? and `parent_id`=?";
				$this->mDb->query( $query, array( $this->mInfo['pos'], $this->mInfo['parent_id'] ) );
			}
			$this->mDb->CompleteTrans();
		}
	}

	function moveNodeSouth() {
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$query = "select `structure_id`, `pos` from `".BIT_DB_PREFIX."tiki_structures` where `pos`>? and `parent_id`=? order by `pos` asc";
			$result = $this->mDb->query($query,array((int)$this->mInfo["pos"], (int)$this->mInfo["parent_id"]));
			$res = $result->fetchRow();
			if ($res) {
				//Swap position values
				$query = "update `".BIT_DB_PREFIX."tiki_structures` set `pos`=? where `structure_id`=?";
				$this->mDb->query($query,array((int)$this->mInfo["pos"], (int)$res["structure_id"]) );
				$this->mDb->query($query,array((int)$res["pos"], (int)$this->mInfo["structure_id"]) );
			}
			$this->mDb->CompleteTrans();
		}
	}

	function moveNodeNorth() {
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$query = "select `structure_id`, `pos` from `".BIT_DB_PREFIX."tiki_structures` where `pos`<? and `parent_id`=? order by `pos` desc";
			$result = $this->mDb->query($query,array((int)$this->mInfo["pos"], (int)$this->mInfo["parent_id"]));
			$res = $result->fetchRow();
			if ($res) {
				//Swap position values
				$query = "update `".BIT_DB_PREFIX."tiki_structures` set `pos`=? where `structure_id`=?";
				$this->mDb->query($query,array((int)$res["pos"], (int)$this->mInfo["structure_id"]) );
				$this->mDb->query($query,array((int)$this->mInfo["pos"], (int)$res["structure_id"]) );
			}
			$this->mDb->CompleteTrans();
		}
	}









	// ============== OLD struct_lib STUFF







	function s_export_structure($structure_id) {
		global $exportlib, $bitdomain, $gBitSystem;

		include_once( WIKI_PKG_PATH.'export_lib.php' );
		include_once (BIT_PKG_PATH."util/tar.class.php");

		$page_info = $this->s_get_structure_info($structure_id);
		$title = $page_info["title"];
		$zipname   = $title . ".zip";
		$tar = new tar();
		$pages = $this->s_get_structure_pages($page_info["structure_id"]);

		foreach ($pages as $page) {
			$data = $exportlib->export_wiki_page($page["title"], 0);
			$tar->addData($page["title"], $data, $gBitSystem->getUTCTime());
		}
		$tar->toTar("dump/$bitdomain" . $title . ".tar", FALSE);
		header ("location: dump/$bitdomain" . $title . ".tar");
		return '';
	}

	function s_export_structure_tree($structure_id, $level = 0) {
		$structure_tree = $this->get_subtree($structure_id);

		$level = 0;
		$first = true;
		foreach ( $structure_tree as $node ) {
			//This special case indicates head of structure
			if ($node["first"] and $node["last"]) {
				print ("Use this tree to copy the structure: " . $node['title'] . "\n\n");
			}
			elseif ($node["first"] or !$node["last"]) {
				if ($node["first"] and !$first) {
			        $level++;
				}
				$first = false;
				for ($i = 0; $i < $level; $i++) {
					print (" ");
				}
				print ($node['title']);
				if (!empty($node['page_alias'])) {
					print("->" . $node['page_alias']);
				}
				print("\n");
			}
			//node is a place holder for last in level
			else {
				$level--;
			}
		}
	}

	function s_remove_page($structure_id, $delete) {
		// Now recursively remove
		if( is_numeric( $structure_id ) ) {
			$query = "SELECT `structure_id`, ts.`content_id`
					  FROM `".BIT_DB_PREFIX."tiki_structures` ts
					  WHERE `parent_id`=?";
			$result = $this->mDb->query( $query,array( (int)$structure_id) );
			//Iterate down through the child nodes
			while ($res = $result->fetchRow()) {
				$this->s_remove_page($res["structure_id"], $delete);
			}

			//Only delete a page if other structures arent referencing it
			if ($delete) {
				$page_info = $this->getNode($structure_id);
				$query = "select count(*) from `".BIT_DB_PREFIX."tiki_structures` where `content_id`=?";
				$count = $this->mDb->getOne($query, array((int)$page_info["page_id"]));
				if ($count = 1) {
					$this->remove_all_versions($page_info["page_id"]);
				}
			}

			//Remove the structure node
			$query = "delete from `".BIT_DB_PREFIX."tiki_structures` where `structure_id`=?";

			$result = $this->mDb->query($query, array( (int)$structure_id) );
			return true;
		}
	}

	/*shared*/
	function remove_from_structure($structure_id) {
		// Now recursively remove
		$query  = "select `structure_id` ";
		$query .= "from `".BIT_DB_PREFIX."tiki_structures` as ts, `".BIT_DB_PREFIX."tiki_pages` as tp ";
		  $query .= "where tp.`content_id`=ts.`content_id` and `parent_id`=?";
		$result = $this->mDb->query($query, array( $structure_id ) );

		while ($res = $result->fetchRow()) {
			$this->remove_from_structure($res["structure_id"]);
		}

		$query = "delete from `".BIT_DB_PREFIX."tiki_structures` where `structure_id`=?";
		$result = $this->mDb->query($query, array( $structure_id ) );
		return true;
	}

  /**Returns an array of info about the parent
     structure_id

     See get_page_info for details of array
  */
	function s_get_parent_info($structure_id) {
		// Try to get the parent of this page
		$parent_id = $this->mDb->getOne("select `parent_id` from `".BIT_DB_PREFIX."tiki_structures` where `structure_id`=?",array((int)$structure_id));

    if (!$parent_id)
      return null;
		return ($this->getNode($parent_id));
	}

	// gets an array of content_id's in order of the hierarchy.
	function getContentIds( $pStructureId, &$pToc, $pLevel=0 ) {
		$ret = array();

		$query = "SELECT * from `".BIT_DB_PREFIX."tiki_structures` where `parent_id`=? ORDER BY pos, page_alias, content_id";
		$result = $this->mDb->query( $query, array( (int)$pStructureId ) );
		while ( !$result->EOF ) {
			array_push( $pToc, $result->fields['content_id'] );
			$this->getContentIds( $result->fields['structure_id'], $pToc, ++$pLevel );
			$result->MoveNext();
		}
	}

	function getContentArray( $pStructureId, &$pToc, $pLevel=0 ) {
		$query = "SELECT * from `".BIT_DB_PREFIX."tiki_structures` where `structure_id`=?";
		$result = $this->mDb->query( $query, array( (int)$pStructureId ) );
		if( !$result->EOF ) {
			array_push( $pToc, $result->fields['content_id'] );
			$this->getContentIds( $pStructureId, $pToc, $pLevel );
		}
	}

	function exportHtml() {
		$ret = array();
		$toc = array();
		$this->getContentArray( $this->mStructureId, $toc );
		if( count( $toc ) ) {
			foreach( $toc as $conId ) {
				if( $viewContent = $this->getLibertyObject( $conId ) ) {
					$ret[] = array(	'type' => $viewContent->mContentTypeGuid,
									'landscape' => FALSE,
									'url' => $viewContent->getDisplayUrl(),
									'content_id' => $viewContent->mContentId,
								);
				}
			}
		}
		return $ret;
	}

	// that is intended to replace the get_subtree_toc and get_subtree_toc_slide
	// it's used only in {toc} thing hardcoded in parse gBitSystem->parse -- (mose)
	// the $tocPrefix can be used to Prefix a subtree as it would start from a given number (e.g. 2.1.3)
	function build_subtree_toc($id,$slide=false,$order='asc',$tocPrefix='') {
		global $gLibertySystem, $gBitSystem;
		$back = array();
		$cant = $this->mDb->getOne("select count(*) from `".BIT_DB_PREFIX."tiki_structures` where `parent_id`=?",array((int)$id));
		if ($cant) {
			$query = "SELECT `structure_id`, `page_alias`, tc.`user_id`, tc.`title`, tc.`content_type_guid`, uu.`login`, uu.`real_name`
					  FROM `".BIT_DB_PREFIX."tiki_structures` ts INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON ( tc.`content_id`=ts.`content_id` )
					  LEFT JOIN `".BIT_DB_PREFIX."users_users` uu ON ( uu.`user_id` = tc.`user_id` )
					  WHERE `parent_id`=?
					  ORDER BY ".$this->mDb->convert_sortmode("pos_".$order);
			$result = $this->mDb->query($query,array((int)$id));
			$prefix=1;
			$contentTypes = $gLibertySystem->mContentTypes;
			while ($res = $result->fetchRow()) {
				$res['prefix']=($tocPrefix=='')?'':"$tocPrefix.";
				$res['prefix'].=$prefix;
				$prefix++;
				if( !empty( $contentTypes[$res['content_type_guid']] ) ) {
					// quick alias for code readability
					$type = &$contentTypes[$res['content_type_guid']];
					if( empty( $type['content_object'] ) ) {
						// create *one* object for each object *type* to  call virtual methods.
						include_once( $gBitSystem->mPackages[$type['handler_package']]['path'].$type['handler_file'] );
						$type['content_object'] = new $type['handler_class']();
					}
					$res['title'] = $type['content_object']->getTitle( $res );
					if ($res['structure_id'] != $id) {
						$sub = $this->build_subtree_toc($res['structure_id'],$slide,$order,$res['prefix']);
						if (is_array($sub)) {
							$res['sub'] = $sub;
						}
					}
				}
				$back[] = $res;
			}
		} else {
			return false;
		}
		return $back;
	}

	function get_toc($pStructureId=NULL,$order='asc',$showdesc=false,$numbering=true,$numberPrefix='') {
		if( empty( $pStructureId ) ) {
			$pStructureId = $this->mStructureId;
		}
		$structure_tree = $this->build_subtree_toc($pStructureId,false,$order,$numberPrefix);
		return $this->fetch_toc($structure_tree,$showdesc,$numbering);
	}

	function fetch_toc($structure_tree,$showdesc,$numbering) {
		global $gBitSmarty;
		$ret='';
		if ($structure_tree != '') {
			$gBitSmarty->verifyCompileDir();
			$ret.=$gBitSmarty->fetch( "bitpackage:wiki/book_toc_startul.tpl");
			foreach($structure_tree as $leaf) {
				//echo "<br />";print_r($leaf);echo "<br />";
				$gBitSmarty->assign_by_ref('structure_tree',$leaf);
				$gBitSmarty->assign('showdesc',$showdesc);
				$gBitSmarty->assign('numbering',$numbering);
				$ret.=$gBitSmarty->fetch( "bitpackage:wiki/book_toc_leaf.tpl");
				if(isset($leaf["sub"]) && is_array($leaf["sub"])) {
					$ret.=$this->fetch_toc($leaf["sub"],$showdesc,$numbering);
				}
			}
			$ret.=$gBitSmarty->fetch( "bitpackage:wiki/book_toc_endul.tpl");
		}
		return $ret;
	}
	// end of replacement


/*
  //Is this page the head page for a structure?
	function get_struct_ref_if_head($title) {
    $query =  "SELECT `structure_id`
			   FROM `".BIT_DB_PREFIX."tiki_structures` ts, `".BIT_DB_PREFIX."tiki_pages` tp,`".BIT_DB_PREFIX."tiki_content` tc
			   WHERE tp.`content_id`=ts.`content_id` AND tc.`content_id` = tp.`content_id` AND (`parent_id` is null or `parent_id`=0) and tc.`title`=?";
		$structure_id = $this->mDb->getOne($query,array($title));
		return $structure_id;
	}
*/
	function get_next_page($structure_id, $deep = true) {
		// If we have children then get the first child
		if ($deep) {
			$query  = "SELECT `structure_id`
					   FROM `".BIT_DB_PREFIX."tiki_structures` ts
					   WHERE `parent_id`=?
					   ORDER BY ".$this->mDb->convert_sortmode("pos_asc");
			$result1 = $this->mDb->query($query,array((int)$structure_id));

			if ($result1->numRows()) {
				$res = $result1->fetchRow();
				return $res["structure_id"];
			}
		}

		// Try to get the next page with the same parent as this
		$page_info = $this->getNode($structure_id);
		$parent_id = $page_info["parent_id"];
		$page_pos = $page_info["pos"];

		if (!$parent_id)
			return null;

		$query  = "SELECT `structure_id`
				   FROM `".BIT_DB_PREFIX."tiki_structures` ts
				   WHERE `parent_id`=? and `pos`>?
				   ORDER BY ".$this->mDb->convert_sortmode("pos_asc");
		$result2 = $this->mDb->query($query,array((int)$parent_id, (int)$page_pos));

		if ($result2->numRows()) {
			$res = $result2->fetchRow();
			return $res["structure_id"];
		}
		else {
			return $this->get_next_page($parent_id, false);
		}
	}

	function get_prev_page($structure_id, $deep = false) {

    //Drill down to last child for this tree node
        if ($deep) {
  	        $query  = "select `structure_id` ";
		    $query .= "from `".BIT_DB_PREFIX."tiki_structures` ts ";
			$query .= "where `parent_id`=? ";
			$query .= "order by ".$this->mDb->convert_sortmode("pos_desc");
			$result = $this->mDb->query($query,array($structure_id));

			if ($result->numRows()) {
				//There are more children
				$res = $result->fetchRow();
				$structure_id = $this->get_prev_page($res["structure_id"], true);
			}
			return $structure_id;
		}
		// Try to get the previous page with the same parent as this
		$page_info = $this->getNode($structure_id);
		$parent_id = $page_info["parent_id"];
		$pos       = $page_info["pos"];

		//At the top of the tree
		if (!isset($parent_id))
			return null;

		$query  = "select `structure_id` ";
		$query .= "from `".BIT_DB_PREFIX."tiki_structures` ts ";
		$query .= "where `parent_id`=? and `pos`<? ";
		$query .= "order by ".$this->mDb->convert_sortmode("pos_desc");
		$result =  $this->mDb->query($query,array((int)$parent_id, (int)$pos));

		if ($result->numRows()) {
			//There is a previous sibling
			$res = $result->fetchRow();
			$structure_id = $this->get_prev_page($res["structure_id"], true);
		}
		else {
			//No previous siblings, just the parent
			$structure_id = $parent_id;
		}
		return $structure_id;
	}

	/** Return an array of subpages
      Used by the 'After Page' select box
  */
	function s_get_pages($parent_id) {
		$ret = array();
	  $query =  "SELECT `pos`, `structure_id`, `parent_id`, ts.`content_id`, tc.`title`, `page_alias`
				 FROM `".BIT_DB_PREFIX."tiki_structures` ts, `".BIT_DB_PREFIX."tiki_content` tc
				 WHERE ts.`content_id` = tc.`content_id` AND `parent_id`=? ";
		$query .= "order by ".$this->mDb->convert_sortmode("pos_asc");
        $result = $this->mDb->query($query,array((int)$parent_id));
		while ($res = $result->fetchRow()) {
			//$ret[] = $this->populate_page_info($res);
			$ret[] = $res;
		}
		return $ret;
	}

	function get_max_children($structure_id) {

		$query = "select `structure_id` from `".BIT_DB_PREFIX."tiki_structures` where `parent_id`=?";
		$result = $this->mDb->query($query,array((int)$structure_id));
		if (!$result->numRows()) {
			return '';
		}
		$res = $result->fetchRow();
		return $res;
	}

	/** Return all the pages belonging to the structure
  \return An array of page_info arrays
  */
  function s_get_structure_pages($structure_id) {
    $ret = array();
    // Add the structure page as well
    $ret[] = $this->getNode($structure_id);
    $ret2  = $this->_s_get_structure_pages($structure_id);
		return array_merge($ret, $ret2);
  }

	/** Return a unique list of pages belonging to the structure
  \return An array of page_info arrays
  */
	function s_get_structure_pages_unique($structure_id) {
    $ret = array();
    // Add the structure page as well
    $ret[] = $this->getNode($structure_id);
    $ret2  = $this->_s_get_structure_pages($structure_id);
		return array_unique(array_merge($ret, $ret2));
  }

	/** Return all the pages belonging to a structure
  \scope private
  \return An array of page_info arrays
  */
	function _s_get_structure_pages($structure_id) {
		$ret = array();
		$query =  "select `pos`, `structure_id`, `parent_id`, ts.`content_id`, tc.`title`, `page_alias`
				   FROM `".BIT_DB_PREFIX."tiki_structures` ts, `".BIT_DB_PREFIX."tiki_content` tc
				   WHERE tc.`content_id` = tp.`content_id` AND tp.`content_id`=ts.`content_id` AND `parent_id`=?
				   ORDER by ".$this->mDb->convert_sortmode("pos_asc");

		$result = $this->mDb->query($query,array((int)$structure_id));
		while ($res = $result->fetchRow()) {
			//$ret[] = $this->populate_page_info($res);
			$ret2 = $this->_s_get_structure_pages($res["structure_id"]);
			$ret = array_merge($res, $ret2);
		}
		return $ret;
	}

  function get_page_alias($structure_id) {
		$query = "select `page_alias` from `".BIT_DB_PREFIX."tiki_structures` where `structure_id`=?";
		$res = $this->mDb->getOne($query, array((int)$structure_id));
    return $res;
  }

  function set_page_alias($structure_id, $pageAlias) {
		$query = "update `".BIT_DB_PREFIX."tiki_structures` set `page_alias`=? where `structure_id`=?";
		$this->mDb->query($query, array($pageAlias, (int)$structure_id));
  }



  //This nifty function creates a static WebHelp version using a TikiStructure as
  //the base.
  function structure_to_webhelp($structure_id, $dir, $top) {
  	global $style_base;

    //The first task is to convert the structure into an array with the
    //proper format to produce a WebHelp project.
	//We have to create something in the form
	//$pages=Array('root'=>Array('pag1'=>'','pag2'=>'','page3'=>Array(...)));
	//Where the name is the title|description and the other side is either ''
	//when the page is a leaf or an Array of pages when the page is a folder
	//Folders that are not BitPages are known for having only a name instead
	//of name|description
	$tree = '$tree=Array('.$this->structure_to_tree($structure_id).');';
	eval($tree);
	//Now we have the tree in $tree!
	$menucode="foldersTree = gFld(\"Index\", \"pages/$top.html\")\n";
	$menucode.=$this->traverse($tree);
	$base = BITHELP_PKG_PATH.$dir;
	copy(BITHELP_PKG_PATH."/menu/options.cfg","$base/menu/menuNodes.js");
	$fw = fopen("$base/menu/menuNodes.js","a+");
	fwrite($fw,$menucode);
	fclose($fw);

	$docs = Array();
	$words = Array();
	$index = Array();
	$first=true;
	$pages = $this->traverse2($tree);
	// Now loop the pages
	foreach($pages as $page)
	{
		$query = "SELECT *
				  FROM `".BIT_DB_PREFIX."tiki_pages` tp, `".BIT_DB_PREFIX."tiki_content` tc
				  WHERE tc.`content_id` = tp.`content_id` AND tc.`title`=?";
		$result = $this->mDb->query($query,array($page));
		$res = $result->fetchRow();
		$docs[] = $res["title"];
		if(empty($res["description"])) $res["description"]=$res["title"];
		$title=$res["title"].'|'.$res["description"];
		$dat = $this->parseData($res['data']);

		//Now dump the page
		$dat = preg_replace("/index.php\?page=([^\'\" ]+)/","$1.html",$dat);
		$dat = str_replace('?nocache=1','',$dat);
		$cs = '';
		$data = "<html><head><script src=\"../js/highlight.js\"></script><link rel=\"StyleSheet\"  href=\"../../../styles/$style_base.css\" type=\"text/css\" /><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /> <title>".$res["title"]."</title></head><body onLoad=\"doProc();\">$cs<div id='tiki-center'><div class='wikitext'>".$dat.'</div></div></body></html>';
		$fw=fopen("$base/pages/".$res['title'].'.html','wb+');
		fwrite($fw,$data);
		fclose($fw);
		unset($dat);

		$page_words = split("[^A-Za-z0-9\-_]",$res["data"]);
		foreach($page_words as $word) {
			$word=strtolower($word);
			if(strlen($word)>3 && preg_match("/^[A-Za-z][A-Za-z0-9\_\-]*[A-Za-z0-9]$/",$word)) {
			if(!in_array($word,$words)) {
				$words[] = $word;
				$index[$word]=Array();
			}
			if(!in_array($res["title"].'|'.$res["description"],$index[$word])) {
				$index[$word][] = $res["title"].'|'.$res["description"];
			}
			}
		}
	}
	sort($words);
	$i=0;
	$fw = fopen("$base/js/searchdata.js","w");
	fwrite($fw,"keywords = new Array();\n");
	foreach($words as $word) {
		fwrite($fw,"keywords[$i] = Array(\"$word\",Array(");
		$first=true;
		foreach($index[$word] as $doc) {
			if(!$first) {fwrite($fw,",");} else {$first=false;}
			fwrite($fw,'"'.$doc.'"');
		}
		fwrite($fw,"));\n");
		$i++;
	}
	fclose($fw);

	}

	function structure_to_tree($structure_id) {
		$query = "select * from `".BIT_DB_PREFIX."tiki_structures` ts,`".BIT_DB_PREFIX."tiki_pages` tp where tp.`content_id`=ts.`content_id` and `structure_id`=?";
		$result = $this->mDb->query($query,array((int)$structure_id));
		$res = $result->fetchRow();
		if(empty($res['description'])) $res['description']=$res['title'];
		$name = $res['description'].'|'.$res['title'];
		$code = '';
		$code.= "'$name'=>";
		$query = "select * from `".BIT_DB_PREFIX."tiki_structures` ts, `".BIT_DB_PREFIX."tiki_pages` tp  where tp.`content_id`=ts.`content_id` and `parent_id`=?";
		$result = $this->mDb->query($query,array((int)$structure_id));
		if($result->numRows()) {
			$code.="Array(";
			$first = true;
			while($res=$result->fetchRow()) {
				if(!$first) {
					$code.=',';
				} else {
					$first = false;
				}
				$code.=$this->structure_to_tree($res['structure_id']);
			}
			$code.=')';
		} else {
			$code.="''";
		}
		return $code;
	}

	function traverse($tree,$parent='') {
		$code='';
		foreach($tree as $name => $node) {
			list($name,$link) = explode('|',$name);
			if(is_array($node)) {
				//New folder node is parent++ folder parent is paren
				$new = $parent . 'A';
				$code.="foldersTree".$new."=insFld(foldersTree$parent,gFld(\"$name\",\"pages/$link.html\"));\n";
				$code.=$this->traverse($node,$new);
			} else {
				$code.="insDoc(foldersTree$parent,gLnk(\"R\",\"$name\",\"pages/$link.html\"));\n";
			}
		}
		return $code;
	}

	function traverse2($tree) {
		$pages = Array();
		foreach($tree as $name => $node) {
			list($name,$link) = explode('|',$name);
			if(is_array($node)) {
				if(isset($name) && isset($link)) {
					$title = $link;
					$pages[] = $title;
				}
				$pages2 = $this->traverse2($node);
				foreach($pages2 as $elem) {
					$pages[] = $elem;
				}
			} else {
				$pages[] = $link;
			}
		}
		return $pages;
	}
}

global $structlib;
$structlib = new LibertyStructure();

?>
