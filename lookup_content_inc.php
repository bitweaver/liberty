<?php
/**
 * lookup_content_inc
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision$
 * @package  liberty
 * @subpackage functions
 */
	global $gContent;

	if( @BitBase::verifyId( $_REQUEST['structure_id'] ) ) {
		/**
		 * required setup
		 */
		require_once( LIBERTY_PKG_PATH.'LibertyStructure.php');
		$_REQUEST['structure_id'] = preg_replace( '/[\D]/', '', $_REQUEST['structure_id'] );
		$gStructure = new LibertyStructure( $_REQUEST['structure_id'] );
		if( $gStructure->load() ) {
			$gStructure->loadNavigation();
			$gStructure->loadPath();
			$gBitSmarty->assign( 'structureInfo', $gStructure->mInfo );
	//		$_REQUEST['page_id'] = $gStructure->mInfo['page_id'];
			if( $viewContent = LibertyBase::getLibertyObject( $gStructure->mInfo['content_id'], $gStructure->mInfo['content_type']['content_type_guid'] ) ) {
				$viewContent->setStructure( $_REQUEST['structure_id'] );
				$gBitSmarty->assign_by_ref( 'pageInfo', $viewContent->mInfo );
				$gContent = &$viewContent;
				$gBitSmarty->assign_by_ref( 'gContent', $gContent );
			}
		}
	} elseif( @BitBase::verifyId( $_REQUEST['content_id'] ) ) {
		$_REQUEST['content_id'] = preg_replace( '/[\D]/', '', $_REQUEST['content_id'] );
		require_once( LIBERTY_PKG_PATH.'LibertyBase.php');
		if( $gContent = LibertyBase::getLibertyObject( $_REQUEST['content_id'] ) ) {
			$gBitSmarty->assign_by_ref( 'gContent', $gContent );
			$gBitSmarty->assign_by_ref( 'pageInfo', $gContent->mInfo );
		}
	}

?>
