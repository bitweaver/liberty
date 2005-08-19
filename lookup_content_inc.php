<?php
/**
 * lookup_content_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.1.1.1.2.6 $
 * @package  liberty
 * @subpackage functions
 */
	global $gContent;

	if( !empty( $_REQUEST['structure_id'] ) ) {
		/**
		 * required setup
		 */
		require_once( LIBERTY_PKG_PATH.'LibertyStructure.php');
		$gStructure = new LibertyStructure( $_REQUEST['structure_id'] );
		if( $gStructure->load() ) {
//	vd( $gStructure->mInfo );
			$gStructure->loadNavigation();
			$gStructure->loadPath();
			$gBitSmarty->assign( 'structureInfo', $gStructure->mInfo );
	//		$_REQUEST['page_id'] = $gStructure->mInfo['page_id'];
			if( $viewContent = $gStructure->getLibertyObject( $gStructure->mInfo['content_id'], $gStructure->mInfo['content_type']['content_type_guid'] ) ) {
				$viewContent->setStructure( $_REQUEST['structure_id'] );
				$gBitSmarty->assign_by_ref( 'pageInfo', $viewContent->mInfo );
				$gContent = &$viewContent;
				$gBitSmarty->assign_by_ref( 'gContent', $gContent );
			}
		}
	} elseif( !empty( $_REQUEST['content_id'] ) ) {
		require_once( LIBERTY_PKG_PATH.'LibertyBase.php');
		if( $gContent = LibertyBase::getLibertyObject( $_REQUEST['content_id'] ) ) {
			$gBitSmarty->assign_by_ref( 'gContent', $gContent );
			$gBitSmarty->assign_by_ref( 'pageInfo', $gContent->mInfo );
		}
	}

?>
