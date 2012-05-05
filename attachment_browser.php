<?php
/**
 * attachment_browser
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision$
 * @package  liberty
 * @subpackage functions
 */

/**
 * bit setup
 */
require_once("../kernel/setup_inc.php");

global $gBitSmarty, $gContent, $gBitUser, $gBitSystem, $gLibertySystem;

// we just want information about a single attachment
if (isset($_REQUEST['attachment_id']) && is_numeric($_REQUEST['attachment_id'])){
	if ( !$gContent ){
		$gContent = new LibertyMime();
	}
	// this is a hack to make it compatible with existing tpls for now
	$attachment = $gContent->getAttachment($_REQUEST['attachment_id']);
	$ret = array();
	$ret[$attachment['attachment_id']] = $attachment;
	$userAttachments = $ret;
	$gContent->mStorage = $userAttachments;
	$gBitSmarty->assign('gContent', $gContent);
}else{
// we want a list of user attachments
	$listHash = $_REQUEST;
	$listHash = array(
		'page' => @BitBase::verifyId( $_REQUEST['pgnPage'] ) ? $_REQUEST['pgnPage'] : NULL,
		'load_attached_to' => true,
	);
	$userAttachments = $gBitUser->getUserAttachments( $listHash );

	// Fake the storage assignment for edit_storage_list.tpl
	$gContent->mStorage = $userAttachments;
	$gBitSmarty->assign('gContent', $gContent);

	// pagination
	$offset = @BitBase::verifyId( $_REQUEST['offset'] ) ? $_REQUEST['offset'] : 0;
	$gBitSmarty->assign( 'curPage', $pgnPage = @BitBase::verifyId( $_REQUEST['pgnPage'] ) ? $_REQUEST['pgnPage'] : 1 );
	$offset = ( $pgnPage - 1 ) * $gBitSystem->getConfig( 'max_records' );

	// calculate page number
	$numPages = ceil( $listHash['cant'] / $gBitSystem->getConfig( 'max_records' ) );
	$gBitSmarty->assign( 'cant', $listHash['cant'] );
	$gBitSmarty->assign( 'numPages', $numPages );
}
?>
