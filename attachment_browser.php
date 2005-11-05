<?php
/**
 * attachment_browser
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.1.1.1.2.6 $
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
	'page' => !empty( $_REQUEST['pgnPage'] ) ? $_REQUEST['pgnPage'] : NULL
);
$userAttachments = $gBitUser->getUserAttachments( $listHash );
$gBitSmarty->assign( 'userAttachments', $userAttachments );

// pagination
$offset = !empty( $_REQUEST['offset'] ) ? $_REQUEST['offset'] : 0;
$gBitSmarty->assign( 'curPage', $pgnPage = !empty( $_REQUEST['pgnPage'] ) ? $_REQUEST['pgnPage'] : 1 );
$offset = ( $pgnPage - 1 ) * $gBitSystem->mPrefs['maxRecords'];

// calculate page number
$numPages = ceil( $userAttachments['cant'] / $gBitSystem->mPrefs['maxRecords'] );
$gBitSmarty->assign( 'numPages', $numPages );
?>
