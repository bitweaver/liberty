<?php
/**
 * attachment_browser
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.9 $
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
$gBitSmarty->assign( 'userAttachments', $userAttachments );

// pagination
$offset = @BitBase::verifyId( $_REQUEST['offset'] ) ? $_REQUEST['offset'] : 0;
$gBitSmarty->assign( 'curPage', $pgnPage = @BitBase::verifyId( $_REQUEST['pgnPage'] ) ? $_REQUEST['pgnPage'] : 1 );
$offset = ( $pgnPage - 1 ) * $gBitSystem->getPreference( 'max_records' );

// calculate page number
$numPages = ceil( $userAttachments['cant'] / $gBitSystem->getPreference( 'max_records' ) );
$gBitSmarty->assign( 'numPages', $numPages );
?>
