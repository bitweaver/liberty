<?php
/**
 * attachment_browser
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.12 $
 * @package  liberty
 * @subpackage functions
 */

/**
 * bit setup
 */
require_once("../bit_setup_inc.php");

global $gBitSmarty, $gContent, $gBitUser, $gBitSystem, $gLibertySystem;
$listHash = $_REQUEST;
$listHash = array(
	'page' => @BitBase::verifyId( $_REQUEST['pgnPage'] ) ? $_REQUEST['pgnPage'] : NULL
);
$userAttachments = $gBitUser->getUserAttachments( $listHash );
// DEPRECATED - Slated for removal -wjames5
//$gBitSmarty->assign( 'userAttachments', $userAttachments );

// Fake the storage assignment for edit_storage_list.tpl
$gContent->mStorage = $userAttachments;
$gBitSmarty->assign('gContent', $gContent);

// pagination
$offset = @BitBase::verifyId( $_REQUEST['offset'] ) ? $_REQUEST['offset'] : 0;
$gBitSmarty->assign( 'curPage', $pgnPage = @BitBase::verifyId( $_REQUEST['pgnPage'] ) ? $_REQUEST['pgnPage'] : 1 );
$offset = ( $pgnPage - 1 ) * $gBitSystem->getConfig( 'max_records' );

// calculate page number
$numPages = ceil( $listHash['cant'] / $gBitSystem->getConfig( 'max_records' ) );
$gBitSmarty->assign( 'numPages', $numPages );
?>
