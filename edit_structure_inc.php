<?php
/**
 * edit_structure_inc
 *
 * @author   Christian Fowler>
 * @version  $Revision: 1.1.1.1.2.11 $
 * @package  liberty
 * @subpackage functions
 */

// Copyright (c) 2004, Christian Fowler, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
include_once( LIBERTY_PKG_PATH.'LibertyStructure.php');

if( empty( $_REQUEST["structure_id"] ) ) {
	$gBitSystem->fatalError( "No structure indicated" );
} else {
	global $gStructure;
	$gStructure = new LibertyStructure( $_REQUEST["structure_id"] );
	$gStructure->load();

	// order matters for these conditionals
	if( empty( $gStructure ) || !$gStructure->isValid() ) {
		$gBitSystem->fatalError( 'Invalid structure' );
	}

	if( $gStructure->mInfo['root_structure_id'] == $gStructure->mInfo['structure_id'] ) {
		$rootStructure = &$gStructure;
	} else {
		$rootStructure = new LibertyStructure( $gStructure->mInfo['root_structure_id'] );
		$rootStructure->load();
		$rootStructure->loadNavigation();
		$rootStructure->loadPath();
	}

	if( ( $gBitUser->mUserId != $rootStructure->mInfo['user_id'] ) ) {
		$gBitSystem->verifyPermission( !empty( $verifyStructurePermission ) ? $verifyStructurePermission : 'bit_p_admin' );
	}
	$gBitSmarty->assign_by_ref( 'gStructure', $gStructure );
	$gBitSmarty->assign('structureInfo', $gStructure->mInfo);

	// Store the actively stored structure name
	$gBitUser->storePreference( 'edit_structure_name', $rootStructure->mInfo['title'] );
	$gBitUser->storePreference( 'edit_structure_id', $rootStructure->mStructureId );

	if( !$gBitSystem->isFeatureActive( 'wikibook_hide_add_content' ) ) {
		include_once( LIBERTY_PKG_PATH.'get_content_list_inc.php' );
		foreach( $contentList['data'] as $cItem ) {
			$cList[$contentTypes[$cItem['content_type_guid']]][$cItem['content_id']] = $cItem['title'].' [id: '.$cItem['content_id'].']';
		}
		$gBitSmarty->assign( 'contentList', $cList );
		$gBitSmarty->assign( 'contentSelect', $contentSelect );
		$gBitSmarty->assign( 'contentTypes', $contentTypes );
		$gBitSmarty->assign( 'contentTypes', $contentTypes );

		$subpages = $gStructure->s_get_pages($_REQUEST["structure_id"]);
		$max = count($subpages);
		$gBitSmarty->assign_by_ref('subpages', $subpages);
		if ($max != 0) {
			$last_child = $subpages[$max - 1];
			$gBitSmarty->assign('insert_after', $last_child["structure_id"]);
		}
	}

	if( ( isset( $_REQUEST["action"] ) && ( $_REQUEST["action"] == 'remove' ) ) || isset( $_REQUEST["confirm"] ) ) {

		if( isset( $_REQUEST["confirm"] ) ) {
			if( $gStructure->s_remove_page( $_REQUEST["structure_id"], false ) ) {
				header( "Location: ".$_SERVER['PHP_SELF'].'?structure_id='.$gStructure->mInfo["parent_id"] );
				die;
			} else {
				vd( $gStructure->mErrors );
			}
		}
		$gBitSystem->setBrowserTitle( 'Confirm removal of '.$gContent->getTitle() );
		$formHash['remove'] = TRUE;
		$formHash['structure_id'] = $_REQUEST['structure_id'];
		$msgHash = array(
			'label' => 'Remove content from Structure',
			'confirm_item' => $gContent->getTitle().'<br />and any subitems',
			'warning' => 'This will remove the content from the structure but will <strong>not</strong> modify or remove the content itself.',
		);
		$gBitSystem->confirmDialog( $formHash,$msgHash );
	} elseif (isset($_REQUEST["move_node"])) {
		if ($_REQUEST["move_node"] == '1') {
			$gStructure->moveNodeWest();
		} elseif ($_REQUEST["move_node"] == '2') {
			$gStructure->moveNodeNorth();
		}	elseif ($_REQUEST["move_node"] == '3') {
			$gStructure->moveNodeSouth();
		} elseif ($_REQUEST["move_node"] == '4') {
			$gStructure->moveNodeEast();
		}
		header( "Location: ".$_SERVER['PHP_SELF'].'?structure_id='.$gStructure->mInfo["structure_id"] );
		die;
	} elseif (isset($_REQUEST["create"])) {

		if (isset($_REQUEST["pageAlias"]))	{
			$gStructure->set_page_alias($_REQUEST["structure_id"], $_REQUEST["pageAlias"]);
		}

		$structureHash['root_structure_id'] = $rootStructure->mStructureId;
		$structureHash['parent_id'] = $_REQUEST['structure_id'];

		$after = null;
		if (isset($_REQUEST['after_ref_id'])) {
			$structureHash['after_ref_id'] = $_REQUEST['after_ref_id'];
		}
		if (!(empty($_REQUEST['name']))) {
			$gStructure->s_create_page($_REQUEST["structure_id"], $after, $_REQUEST["name"], '');
			$gBitUser->copy_object_permissions($page_info["page_name"], $_REQUEST["name"],'wiki page');

		} elseif(!empty($_REQUEST['content'])) {
			foreach ($_REQUEST['content'] as $conId ) {
				$structureHash['content_id'] = $conId;
				$new_structure_id = $gStructure->storeNode( $structureHash );
				$structureHash['after_ref_id'] = $new_structure_id;
			}
		}
	}

	$gBitSmarty->assign( (!empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'body').'TabSelect', 'tdefault' );
	$gBitSmarty->assign('subtree', $rootTree = $rootStructure->getSubTree( $rootStructure->mStructureId ));
}

?>
