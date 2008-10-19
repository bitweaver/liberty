<?php
/**
 * Management of Liberty Content
 *
 * @package  liberty
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/LibertyStructure.php,v 1.40 2008/10/19 08:14:21 squareing Exp $
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

	/**
	 * get the details to a given node
	 * 
	 * @param array $pStructureId Structure ID of the node
	 * @param array $pContentId Content ID of the node
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getNode( $pStructureId=NULL, $pContentId=NULL ) {
		global $gLibertySystem, $gBitSystem;
		static $sStructureNodeCache;
		$contentTypes = $gLibertySystem->mContentTypes;

		if( @$this->verifyId( $pStructureId ) ) {
			if (!empty($sStructureNodeCache['structure_id'][$pStructureId])) {
				return $sStructureNodeCache['structure_id'][$pStructureId];
			}
			$where = ' WHERE ls.`structure_id`=?';
			$bindVars = array( $pStructureId );
		} elseif( @$this->verifyId( $pContentId ) ) {
			if (!empty($sStructureNodeCache['content_id'][$pContentId])) {
				return $sStructureNodeCache['content_id'][$pContentId];
			}
			$where = ' WHERE ls.`content_id`=?';
			$bindVars = array( $pContentId );
		}

		$ret = NULL;
		$query = 'SELECT ls.*, lc.`user_id`, lc.`title`, lc.`content_type_guid`, uu.`login`, uu.`real_name`
				  FROM `'.BIT_DB_PREFIX.'liberty_structures` ls
				  INNER JOIN `'.BIT_DB_PREFIX.'liberty_content` lc ON (ls.`content_id`=lc.`content_id`)
				  LEFT JOIN `'.BIT_DB_PREFIX.'users_users` uu ON ( uu.`user_id` = lc.`user_id` )' . $where;

		if( $result = $this->mDb->query( $query, $bindVars ) ) {
			$ret = $result->fetchRow();
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

		$sStructureNodeCache['structure_id'][$ret['structure_id']] = $ret;
		$sStructureNodeCache['content_id'][$ret['content_id']] = $ret;

		return $ret;
	}

	/**
	 * Check if a node is a root node
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function isRootNode() {
		$ret = FALSE;
		if( @$this->verifyId( $this->mInfo['structure_id'] ) ) {
			$ret = $this->mInfo['root_structure_id'] == $this->mInfo['structure_id'];
		}
		return $ret;
	}

	/**
	 * get the title of the root node
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getRootTitle() {
		$ret = NULL;
		if( isset( $this->mInfo['structure_path'][0]['title'] ) ) {
			$ret = $this->mInfo['structure_path'][0]['title'];
		}
		return $ret;
	}

	/**
	 * if you only have a structure id and you want to figure out the root structure id, use this 
	 * 
	 * @param array $pParamHash['structure_id'] is the structure id from which you want to figure out the root structure id
	 * @access public
	 * @return none. updates $pParamHash['root_structure_id'] by reference
	 */
	function getRootStructureId( &$pParamHash ) {
		if( @BitBase::verifyId( $pParamHash['root_structure_id'] ) ) {
			$pParamHash['root_structure_id'] = $pParamHash['root_structure_id'];
		} elseif( @BitBase::verifyId( $this->mInfo['root_structure_id'] ) ) {
			$pParamHash['root_structure_id'] = $this->mInfo['root_structure_id'];
		} elseif( @BitBase::verifyId( $pParamHash['structure_id'] ) ) {
			$pParamHash['root_structure_id'] = $this->mDb->getOne( "SELECT `root_structure_id` FROM `".BIT_DB_PREFIX."liberty_structures` WHERE `structure_id` = ?", array( $pParamHash['structure_id'] ) );
		} else {
			$pParamHash['root_structure_id'] = NULL;
		}
	}

	// This is a utility function mainly used for upgrading sites.
	function setTreeRoot( $pRootId, $pTree ) {
		foreach( $pTree as $structRow ) {
			$this->mDb->query( "UPDATE `".BIT_DB_PREFIX."liberty_structures` SET `root_structure_id`=? WHERE `structure_id`=?", array( $pRootId, $structRow["structure_id"] ) );
			if( !empty( $structRow["sub"] ) ) {
				$this->setTreeRoot( $pRootId, $structRow["sub"] );
			}
		}
	}


	function isValid() {
		return( $this->verifyId( $this->mStructureId ) );
	}

	/**
	 * loadNavigation 
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function loadNavigation() {
		if( $this->isValid() ) {
			$this->mInfo["prev"] = null;
			// Get structure info for this page
			if( !$this->isRootNode() && ($prev_structure_id = $this->getPrevStructureNode( $this->mStructureId )) ) {
				$this->mInfo["prev"]   = $this->getNode($prev_structure_id);
			}
			$next_structure_id = $this->getNextStructureNode( $this->mStructureId );
			$this->mInfo["next"] = null;
			if (isset($next_structure_id)) {
				$this->mInfo["next"]   = $this->getNode( $next_structure_id) ;
			}
			$this->mInfo["parent"] = $this->getStructureParentInfo( $this->mStructureId );
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

	/**
	* Get full structure from database
	* @param $pStructureId structure for which we want structure
	* @return full structure
	*/
	function getStructure( &$pParamHash ) {
		global $gBitSystem, $gLibertySystem;
		// make sure we have the correct id to get the entire structure
		LibertyStructure::getRootStructureId( $pParamHash );

		$ret = FALSE;

		if( @BitBase::verifyId( $pParamHash['root_structure_id'] ) ) {
			// Get all nodes for this structure
			$query = "SELECT ls.*, lc.`user_id`, lc.`title`, lc.`content_type_guid`, uu.`login`, uu.`real_name`
				FROM `".BIT_DB_PREFIX."liberty_structures` ls
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( ls.`content_id` = lc.`content_id` )
				INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON ( uu.`user_id` = lc.`user_id` )
				WHERE ls.`root_structure_id` = ? ORDER BY `pos` ASC";
			$result = $this->mDb->query( $query, array( $pParamHash['root_structure_id'] ) );

			$subs = array();
			$row_max = $result->numRows();
			$contentTypes = $gLibertySystem->mContentTypes;
			while( $res = $result->fetchRow() ) {
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
					if( !empty( $pParamHash['thumbnail_size'] ) ) {
						$aux['content_object'] = new $type['handler_class']( NULL, $aux['content_id'] );
						if( $aux['content_object']->load() ) {
							$aux['thumbnail_url'] = $aux['content_object']->getThumbnailUrl( $pParamHash['thumbnail_size'] );
						}
					}
					$aux['title'] = $type['content_object']->getTitle( $aux );
					$ret[] = $aux;
				}
			}
		}
		return $ret;
	}

	/**
	* Get all structures in $pStructureHash that have a given parent_id
	* @param $pStructureHash full menu as supplied by '$this->getItemList( $pMenuId );'
	* @return array of nodes with a given parent_id
	*/
	function getChildNodes( $pStructureHash, $pParentId = 0 ) {
		$ret = array();
		if( !empty( $pStructureHash ) ) {
			foreach( $pStructureHash as $node ) {
				if( $node['parent_id'] == $pParentId ) {
					$ret[] = $node;
				}
			}
		}
		return $ret;
	}

	/**
	* Create a usable array from the data in the database from getStructure()
	* @param $pStructureHash raw structure data from database
	* @return nicely formatted and cleaned up structure array
	*/
	function createSubTree( $pStructureHash, $pParentId = 0, $pParentPos = '', $pLevel = 0 ) {
		$ret = array();
		// get all child menu Nodes for this structure_id
		$children = LibertyStructure::getChildNodes( $pStructureHash, $pParentId );
		$pos = 1;
		$row_max = count( $children );

		// we need to insert the root structure item first
		if (!$pLevel && !empty($pStructureHash)) {
			foreach( $pStructureHash as $node ) {
			  if( ( $pParentId == 0 && $node['structure_id'] == $node['root_structure_id'] ) || $node['structure_id'] == $pParentId) {
					$aux		  = $node;
					$aux["first"] = true;
					$aux["last"]  = true;
					$aux["pos"]   = '';
					$aux["level"] = $pLevel++;
					$ret[] = $aux;
				}
			}
		}

		foreach( $children as $node ) {
			$aux = $node;
			$aux['level'] = $pLevel;
			$aux['first'] = ( $pos == 1 );
			$aux['last']  = FALSE;
			$aux['has_children'] = FALSE;
			if( strlen( $pParentPos ) == 0 ) {
				$aux["pos"] = "$pos";
			} else {
				$aux["pos"] = $pParentPos . '.' . "$pos";
			}
			$ret[] = $aux;
			//Recursively add any children
			$subs = LibertyStructure::createSubTree( $pStructureHash, $node['structure_id'], $aux['pos'], ( $pLevel + 1 ) );
			if( !empty( $subs ) ) {
				$r = array_pop( $ret );
				$r['has_children'] = TRUE;
				array_push( $ret, $r );
				$ret = array_merge( $ret, $subs );
			}

			if( $pos == $row_max ) {
				$aux['structure_id'] = $node['structure_id'];
				$aux['first'] = FALSE;
				$aux['last']  = TRUE;
				$ret[] = $aux;
			}
			$pos++;
		}
		return $ret;
	}

	// get sub tree of $pStructureId
	function getSubTree( $pStructureId, $pRootTree = FALSE, $pListHash=NULL ) {
		global $gLibertySystem, $gBitSystem;
		$ret = array();
		if( @BitBase::verifyId( $pStructureId ) ) {
			$pListHash['structure_id'] = $pStructureId;
			$structureHash = $this->getStructure( $pListHash );
			$ret = $this->createSubTree( $structureHash, ( ( $pRootTree ) ? $pListHash['root_structure_id'] : $pStructureId ) );
		}
		return $ret;
	}

	/**
	 * getList 
	 * 
	 * @param array $pListHash 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getList( &$pListHash ) {
		global $gBitSystem, $gBitUser;

		$this->prepGetList( $pListHash );

		if( !empty( $pListHash['find'] ) ) {
			$findesc = '%' . $pListHash['find'] . '%';
			$mid = " (`parent_id` is null or `parent_id`=0) and (lc.`title` like ?)";
			$bindVars=array($findesc);
		} else {
			$mid = " (`parent_id` is null or `parent_id`=0) ";
			$bindVars=array();
		}

		if( @$this->verifyId( $pListHash['user_id'] ) ) {
			$mid .= " AND lc.`user_id` = ? ";
			array_push( $bindVars, $pListHash['user_id'] );
		}

		if( !empty( $pListHash['content_type_guid'] ) ) {
			$mid .= " AND lc.`content_type_guid`=? ";
			array_push( $bindVars, $pListHash['content_type_guid'] );
		}
		$query = "SELECT ls.`structure_id`, ls.`parent_id`, ls.`content_id`, `page_alias`, `pos`, lc.`title`, `data`, `last_modified`, lc.`modifier_user_id`, `ip`, lc.`user_id` AS `creator_user_id`, uu.`login` AS `creator_user`, uu.`real_name` , uu.`email`
				  FROM `".BIT_DB_PREFIX."liberty_structures` ls INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( ls.`content_id` = lc.`content_id` ) INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON ( lc.`user_id` = uu.`user_id` )
				  WHERE $mid
				  ORDER BY ".$this->mDb->convertSortmode($pListHash['sort_mode']);
		$query_cant = "SELECT count(*)
					   FROM `".BIT_DB_PREFIX."liberty_structures` ls INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( ls.`content_id` = lc.`content_id` )
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

	/**
	* prepare a structure node for storage in the database
	* @param $pParamHash contains various settings for the node to be stored
	* @return TRUE on success, FALSE on failure where $this->mErrors will contain the reason why it failed
	*/
	function verifyNode( &$pParamHash ) {
		if( !@$this->verifyId( $pParamHash['content_id'] ) ) {
			$this->mErrors['content'] = 'Could not store structure. Invalid content id. '.$pParamHash['content_id'];
		} else {
			if( !@$this->verifyId( $pParamHash['parent_id'] ) ) {
				$pParamHash['parent_id'] = 0;
			}
			if( empty( $pParamHash['alias'] ) ) {
				$pParamHash['alias'] = '';
			}
			if( isset( $pParamHash['after_ref_id'] ) ) {
				$pParamHash['max'] = $this->mDb->getOne("select `pos` from `".BIT_DB_PREFIX."liberty_structures` where `structure_id`=?",array((int)$pParamHash['after_ref_id']));
			} else {
				$pParamHash['max'] = $this->mDb->getOne("select max(`pos`) from `".BIT_DB_PREFIX."liberty_structures` where `parent_id`=?",array((int)$pParamHash['parent_id']));
			}
			if( $pParamHash['max'] > 0 ) {
				//If max is 5 then we are inserting after position 5 so we'll insert 5 and move all
				// the others
				$query = "update `".BIT_DB_PREFIX."liberty_structures` set `pos`=`pos`+1 where `pos`>? and `parent_id`=?";
				$result = $this->mDb->query($query,array((int)$pParamHash['max'], (int)$pParamHash['parent_id']));
			}
			$pParamHash['max']++;
		}
		return( count( $this->mErrors ) == 0 );
	}

	/**
	* clean up and prepare a complete structure in the form of arrays about to be stored
	* @param $pParamHash is a set of arrays generated by the DynamicTree javascript tree builder
	* @return TRUE on success, FALSE on failure where $this->mErrors will contain the reason why it failed
	*/
	function verifyStructure( &$pParamHash ) {
		if( !empty( $pParamHash['structure_string'] ) ) {
			eval( $pParamHash['structure_string'] );
			$pParamHash = array_merge( $pParamHash, $tree );
		}

		if( !empty( $pParamHash['structure'] ) && @BitBase::verifyId( $pParamHash['root_structure_id'] ) ) {
			LibertyStructure::embellishStructureHash( $pParamHash['structure'] );
			$structureHash = LibertyStructure::flattenStructureHash( $pParamHash['structure'] );

			// replace the 'tree' in the data array with the root_structure_id
			foreach( $pParamHash['data'] as $structure_id => $node ) {
				if( !@BitBase::verifyId( $pParamHash['data'][$structure_id]['parent_id'] ) ) {
					$pParamHash['data'][$structure_id]['parent_id'] = $pParamHash['root_structure_id'];
				}
			}

			foreach( $structureHash as $node ) {
				if( @BitBase::verifyId( $node['structure_id'] ) ) {
					$pParamHash['structure_store'][$node['structure_id']] = array_merge( $node, $pParamHash['data'][$node['structure_id']] );
					$pParamHash['structure_store'][$node['structure_id']]['root_structure_id'] = $pParamHash['root_structure_id'];
				}
			}
		} else {
			$this->mErrors['verify_structure'] = tra( "The structure could not be stored because of missing data." );
		}

		// clear up some memory
		if( !empty( $pParamHash['structure_string'] ) ) { unset( $pParamHash['structure_string'] ); }
		if( !empty( $pParamHash['structure'] ) )        { unset( $pParamHash['structure'] ); }
		if( !empty( $pParamHash['data'] ) )             { unset( $pParamHash['data'] ); }
		return( count( $this->mErrors ) == 0 );
	}

	/**
	* store a complete structure where ever subarray contains a complete node as it should go into the database
	* @param $pParamHash is an array with subarrays, each representing a structure node ready to associativley inserted into the database
	* @return TRUE on success, FALSE on failure where $this->mErrors will contain the reason why it failed
	*/
	function storeStructure( $pParamHash ) {
		if( $this->verifyStructure( $pParamHash ) ) {
			// now that the structure is ready to be stored, we remove the old structure first and then insert the new one.
			$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_structures` WHERE `root_structure_id`=? AND `structure_id`<>?";
			$result = $this->mDb->query( $query, array( (int)$pParamHash['root_structure_id'], (int)$pParamHash['root_structure_id'] ) );
			$query = "";
			$this->mDb->StartTrans();
			foreach( $pParamHash['structure_store'] as $node ) {
				$this->mDb->associateInsert( BIT_DB_PREFIX."liberty_structures", $node );
			}
			$this->mDb->CompleteTrans();
		}
		return( count( $this->mErrors ) == 0 );
	}

	/**
	* make sure the array only contains one level depth
	* @param $pParamHash contains a nested set of arrays with structure_id and pos values set
	* @return flattened array
	*/
	function flattenStructureHash( $pParamHash, $i = -10000 ) {
		$ret = array();
		foreach( $pParamHash as $key => $node ) {
			if( !empty( $node ) && count( $node ) > 2 ) {
				$ret = array_merge( $ret, LibertyStructure::flattenStructureHash( $node, $i ) );
				$i++;
			} elseif( count( $node ) == 2 ) {
				$ret[] = $node;
				$i++;
			} else {
				$ret[$i][$key] = $node;
			}
		}
		return $ret;
	}

	/**
	* cleans up and reorganises data in nested array where keys are structure_id
	* @param $pParamHash contains a nested set of arrays with structure_id as key
	* @return reorganised array
	*/
	function embellishStructureHash( &$pParamHash ) {
		$pos = 1;
		foreach( $pParamHash as $structure_id => $node ) {
			if( !empty( $node ) ) {
				LibertyStructure::embellishStructureHash( $node );
			}
			$node['pos'] = $pos++;
			$node['structure_id'] = $structure_id;
			$pParamHash[$structure_id] = $node;
		}
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
			$pParamHash['structure_id'] = $this->mDb->GenID( 'liberty_structures_id_seq' );
			if( !@$this->verifyId( $pParamHash['root_structure_id'] ) ) {
				$pParamHash['root_structure_id'] = $pParamHash['structure_id'];
			}
			$query = "INSERT INTO `".BIT_DB_PREFIX."liberty_structures`( `structure_id`, `parent_id`,`content_id`, `root_structure_id`, `page_alias`, `pos` ) values(?,?,?,?,?,?)";
			$result = $this->mDb->query( $query, array( $pParamHash['structure_id'], $pParamHash['parent_id'], (int)$pParamHash['content_id'], (int)$pParamHash['root_structure_id'], $pParamHash['alias'], $pParamHash['max'] ) );
			$this->mDb->CompleteTrans();
			$ret = $pParamHash['structure_id'];
		} else {
			//vd( $this->mErrors );
		}
		return $ret;
	}

	/**
	 * moveNodeWest 
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function moveNodeWest() {
		if( $this->isValid() ) {
			//If there is a parent and the parent isnt the structure root node.
			$this->mDb->StartTrans();
			if( @$this->verifyId( $this->mInfo["parent_id"] ) ) {
				$parentNode = $this->getNode( $this->mInfo["parent_id"] );
				if( @$this->verifyId( $parentNode['parent_id'] ) ) {
					//Make a space for the node after its parent
					$query = "update `".BIT_DB_PREFIX."liberty_structures` set `pos`=`pos`+1 where `pos`>? and `parent_id`=?";
					$this->mDb->query( $query, array( $parentNode['pos'], $parentNode['parent_id'] ) );
					//Move the node up one level
					$query = "update `".BIT_DB_PREFIX."liberty_structures` set `parent_id`=?, `pos`=(? + 1) where `structure_id`=?";
					$this->mDb->query($query, array( $parentNode['parent_id'], $parentNode['pos'], $this->mStructureId ) );
				}
			}
			$this->mDb->CompleteTrans();
		}
	}

	/**
	 * moveNodeEast 
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function moveNodeEast() {
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$query = "select `structure_id`, `pos` from `".BIT_DB_PREFIX."liberty_structures` where `pos`<? and `parent_id`=? order by `pos` desc";
			$result = $this->mDb->query($query,array($this->mInfo["pos"], (int)$this->mInfo["parent_id"]));
			if ($previous = $result->fetchRow()) {
				//Get last child nodes for previous sibling
				$query = "select `pos` from `".BIT_DB_PREFIX."liberty_structures` where `parent_id`=? order by `pos` desc";
				$result = $this->mDb->query($query,array((int)$previous["structure_id"]));
				if ($res = $result->fetchRow()) {
					$pos = $res["pos"];
				} else{
					$pos = 0;
				}
				$query = "update `".BIT_DB_PREFIX."liberty_structures` set `parent_id`=?, `pos`=(? + 1) where `structure_id`=?";
				$this->mDb->query( $query, array((int)$previous["structure_id"], (int)$pos, (int)$this->mStructureId) );
				//Move nodes up below that had previous parent and pos
				$query = "update `".BIT_DB_PREFIX."liberty_structures` set `pos`=`pos`-1 where `pos`>? and `parent_id`=?";
				$this->mDb->query( $query, array( $this->mInfo['pos'], $this->mInfo['parent_id'] ) );
			}
			$this->mDb->CompleteTrans();
		}
	}

	/**
	 * moveNodeSouth 
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function moveNodeSouth() {
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$query = "select `structure_id`, `pos` from `".BIT_DB_PREFIX."liberty_structures` where `pos`>? and `parent_id`=? order by `pos` asc";
			$result = $this->mDb->query($query,array((int)$this->mInfo["pos"], (int)$this->mInfo["parent_id"]));
			$res = $result->fetchRow();
			if ($res) {
				//Swap position values
				$query = "update `".BIT_DB_PREFIX."liberty_structures` set `pos`=? where `structure_id`=?";
				$this->mDb->query($query,array((int)$this->mInfo["pos"], (int)$res["structure_id"]) );
				$this->mDb->query($query,array((int)$res["pos"], (int)$this->mInfo["structure_id"]) );
			}
			$this->mDb->CompleteTrans();
		}
	}

	/**
	 * moveNodeNorth 
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function moveNodeNorth() {
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$query = "select `structure_id`, `pos` from `".BIT_DB_PREFIX."liberty_structures` where `pos`<? and `parent_id`=? order by `pos` desc";
			$result = $this->mDb->query($query,array((int)$this->mInfo["pos"], (int)$this->mInfo["parent_id"]));
			$res = $result->fetchRow();
			if ($res) {
				//Swap position values
				$query = "update `".BIT_DB_PREFIX."liberty_structures` set `pos`=? where `structure_id`=?";
				$this->mDb->query($query,array((int)$res["pos"], (int)$this->mInfo["structure_id"]) );
				$this->mDb->query($query,array((int)$this->mInfo["pos"], (int)$res["structure_id"]) );
			}
			$this->mDb->CompleteTrans();
		}
	}









	// ============== OLD struct_lib STUFF



	function removeStructureNode( $structure_id, $delete=FALSE ) {
		// Now recursively remove
		if( @$this->verifyId( $structure_id ) ) {
			$query = "SELECT *
					  FROM `".BIT_DB_PREFIX."liberty_structures`
					  WHERE `parent_id`=?";
			$result = $this->mDb->query( $query, array( (int)$structure_id ) );
			// Iterate down through the child nodes
			while( $res = $result->fetchRow() ) {
				$this->removeStructureNode( $res["structure_id"], $delete );
			}

			// Only delete a page if other structures arent referencing it
			if( $delete ) {
				$page_info = $this->getNode( $structure_id );
				$query = "SELECT COUNT(*) FROM `".BIT_DB_PREFIX."liberty_structures` WHERE `content_id`=?";
				$count = $this->mDb->getOne( $query, array( (int)$page_info["page_id"] ) );
				if( $count = 1 ) {
					$this->remove_all_versions( $page_info["page_id"] );
				}
			}

			// If we are removing the root node, remove the entry in liberty_content as well
			$query = "SELECT `content_id`
					  FROM `".BIT_DB_PREFIX."liberty_structures`
					  WHERE `structure_id`=? AND `structure_id`=`root_structure_id`";
			$content_id = $this->mDb->getOne( $query, array( (int)$structure_id ) );
			
			// Delete the liberty_content stuff
			$lc = new LibertyContent($content_id);
			$lc->expunge();

			// Remove the structure node
			$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_structures` WHERE `structure_id`=?";
			$result = $this->mDb->query( $query, array( (int)$structure_id) );
			return true;
		}
	}

	/**
	 * Returns an array of info about the parent
	 * 
	 * @param array $structure_id 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getStructureParentInfo($structure_id) {
		$parent_id = $this->mDb->getOne( "SELECT `parent_id` FROM `".BIT_DB_PREFIX."liberty_structures` WHERE `structure_id`=?", array( (int)$structure_id ) );

		if( !@BitBase::verifyId( $parent_id ) ) {
			return null;
		}

		return( $this->getNode( $parent_id ) );
	}

	/**
	 * getContentIds 
	 * 
	 * @param array $pStructureId 
	 * @param array $pToc 
	 * @param float $pLevel 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getContentIds( $pStructureId, &$pToc, $pLevel=0 ) {
		$ret = array();

		$query = "SELECT * from `".BIT_DB_PREFIX."liberty_structures` where `parent_id`=? ORDER BY pos, page_alias, content_id";
		$result = $this->mDb->query( $query, array( (int)$pStructureId ) );
		while ( $row = $result->fetchRow() ) {
			array_push( $pToc, $row['content_id'] );
			$this->getContentIds( $row['structure_id'], $pToc, ++$pLevel );
		}
	}

	/**
	 * getContentArray 
	 * 
	 * @param array $pStructureId 
	 * @param array $pToc 
	 * @param float $pLevel 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getContentArray( $pStructureId, &$pToc, $pLevel=0 ) {
		$query = "SELECT * from `".BIT_DB_PREFIX."liberty_structures` where `structure_id`=?";
		$result = $this->mDb->query( $query, array( (int)$pStructureId ) );
		while ( $row = $result->fetchRow() ) {
			array_push( $pToc, $row['content_id'] );
			$this->getContentIds( $pStructureId, $pToc, $pLevel );
		}
	}

	/**
	 * exportHtml 
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function exportHtml() {
		$ret = array();
		$toc = array();
		$this->getContentArray( $this->mStructureId, $toc );
		if( count( $toc ) ) {
			foreach( $toc as $conId ) {
				if( $viewContent = $this->getLibertyObject( $conId ) ) {
					$ret[] = array(
						'type'       => $viewContent->mContentTypeGuid,
						'landscape'  => FALSE,
						'url'        => $viewContent->getDisplayUrl(),
						'content_id' => $viewContent->mContentId,
					);
				}
			}
		}
		return $ret;
	}

	/**
	 * buildSubtreeToc 
	 * 
	 * @param array $id 
	 * @param array $slide 
	 * @param string $order 
	 * @param string $tocPrefix can be used to Prefix a subtree as it would start from a given number (e.g. 2.1.3)
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function buildSubtreeToc($id,$slide=false,$order='asc',$tocPrefix='') {
		global $gLibertySystem, $gBitSystem;
		$back = array();
		$cant = $this->mDb->getOne("select count(*) from `".BIT_DB_PREFIX."liberty_structures` where `parent_id`=?",array((int)$id));
		if ($cant) {
			$query = "SELECT `structure_id`, `page_alias`, lc.`user_id`, lc.`title`, lc.`content_type_guid`, uu.`login`, uu.`real_name`
					  FROM `".BIT_DB_PREFIX."liberty_structures` ls INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id`=ls.`content_id` )
					  LEFT JOIN `".BIT_DB_PREFIX."users_users` uu ON ( uu.`user_id` = lc.`user_id` )
					  WHERE `parent_id`=?
					  ORDER BY ".$this->mDb->convertSortmode("pos_".$order);
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
						$sub = $this->buildSubtreeToc($res['structure_id'],$slide,$order,$res['prefix']);
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

	/**
	 * getToc 
	 * 
	 * @param array $pStructureId 
	 * @param string $order 
	 * @param array $showdesc 
	 * @param array $numbering 
	 * @param string $numberPrefix 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getToc($pStructureId=NULL,$order='asc',$showdesc=false,$numbering=true,$numberPrefix='') {
		if( !@$this->verifyId( $pStructureId ) ) {
			$pStructureId = $this->mStructureId;
		}
		$structure_tree = $this->buildSubtreeToc($pStructureId,false,$order,$numberPrefix);
		return $this->fetchToc($structure_tree,$showdesc,$numbering);
	}

	/**
	 * fetchToc 
	 * 
	 * @param array $structure_tree 
	 * @param array $showdesc 
	 * @param array $numbering 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function fetchToc($structure_tree,$showdesc,$numbering) {
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
					$ret.=$this->fetchToc($leaf["sub"],$showdesc,$numbering);
				}
			}
			$ret.=$gBitSmarty->fetch( "bitpackage:wiki/book_toc_endul.tpl");
		}
		return $ret;
	}

	/**
	 * getNextStructureNode 
	 * 
	 * @param array $structure_id 
	 * @param array $deep 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getNextStructureNode($structure_id, $deep = true) {
		// If we have children then get the first child
		if ($deep) {
			$query  = "SELECT `structure_id`
					   FROM `".BIT_DB_PREFIX."liberty_structures` ls
					   WHERE `parent_id`=?
					   ORDER BY ".$this->mDb->convertSortmode("pos_asc");
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
				   FROM `".BIT_DB_PREFIX."liberty_structures` ls
				   WHERE `parent_id`=? and `pos`>?
				   ORDER BY ".$this->mDb->convertSortmode("pos_asc");
		$result2 = $this->mDb->query($query,array((int)$parent_id, (int)$page_pos));

		if ($result2->numRows()) {
			$res = $result2->fetchRow();
			return $res["structure_id"];
		}
		else {
			return $this->getNextStructureNode($parent_id, false);
		}
	}

	/**
	 * getPrevStructureNode 
	 * 
	 * @param array $structure_id 
	 * @param array $deep 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getPrevStructureNode($structure_id, $deep = false) {
		//Drill down to last child for this tree node
		if ($deep) {
			$query  = "select `structure_id` ";
			$query .= "from `".BIT_DB_PREFIX."liberty_structures` ls ";
			$query .= "where `parent_id`=? ";
			$query .= "order by ".$this->mDb->convertSortmode("pos_desc");
			$result = $this->mDb->query($query,array($structure_id));

			if ($result->numRows()) {
				//There are more children
				$res = $result->fetchRow();
				$structure_id = $this->getPrevStructureNode($res["structure_id"], true);
			}
			return $structure_id;
		}
		// Try to get the previous page with the same parent as this
		$page_info = $this->getNode($structure_id);
		$parent_id = $page_info["parent_id"];
		$pos	   = $page_info["pos"];

		//At the top of the tree
		if (!isset($parent_id))
			return null;

		$query  = "select `structure_id` ";
		$query .= "from `".BIT_DB_PREFIX."liberty_structures` ls ";
		$query .= "where `parent_id`=? and `pos`<? ";
		$query .= "order by ".$this->mDb->convertSortmode("pos_desc");
		$result =  $this->mDb->query($query,array((int)$parent_id, (int)$pos));

		if ($result->numRows()) {
			//There is a previous sibling
			$res = $result->fetchRow();
			$structure_id = $this->getPrevStructureNode($res["structure_id"], true);
		}
		else {
			//No previous siblings, just the parent
			$structure_id = $parent_id;
		}
		return $structure_id;
	}

	/**
	 * Return an array of subpages
	 * 
	 * @param array $pParentId 
	 * @access public
	 * @return array of child structure pages
	 */
	function getStructureNodes( $pParentId ) {
		$ret = array();
		$query =  "SELECT `pos`, `structure_id`, `parent_id`, ls.`content_id`, lc.`title`, `page_alias`
			FROM `".BIT_DB_PREFIX."liberty_structures` ls, `".BIT_DB_PREFIX."liberty_content` lc
			WHERE ls.`content_id` = lc.`content_id` AND `parent_id`=? ";
		$query .= "order by ".$this->mDb->convertSortmode("pos_asc");
		$result = $this->mDb->query($query,array((int)$pParentId));
		while ($res = $result->fetchRow()) {
			//$ret[] = $this->populate_page_info($res);
			$ret[] = $res;
		}
		return $ret;
	}



	// {{{ the following is just for the transition phase...
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function s_remove_page()     { deprecated( 'Please use removeStructureNode() instead' ); }
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function s_get_pages()       { deprecated( 'Please use getStructureNodes() instead' ); }
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function get_prev_page()     { deprecated( 'Please use getPrevStructureNode() instead' ); }
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function get_next_page()     { deprecated( 'Please use getNextStructureNode() instead' ); }
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function get_toc()           { deprecated( 'Please use getToc() instead' ); }
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function fetch_toc()         { deprecated( 'Please use fetchToc() instead' ); }
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function s_get_parent_info() { deprecated( 'Please use getStructureParentInfo() instead' ); }
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function build_subtree_toc() { deprecated( 'Please use buildSubtreeToc() instead' ); }
	// }}}
}
?>
