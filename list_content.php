<?php
/**
 * list_content
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.11 $
 * @package  liberty
 * @subpackage functions
 */

/**
 * required setup
 */
require_once("../bit_setup_inc.php");

// some content specific offsets and pagination settings
if( !empty( $_REQUEST['sort_mode'] ) ) {
	$content_sort_mode = $_REQUEST['sort_mode'];
	$gBitSmarty->assign( 'sort_mode', $content_sort_mode );
}

$max_content = $gBitSystem->getPreference( 'max_records' );
$offset_content = @BitBase::verifyId( $_REQUEST['offset'] ) ? $_REQUEST['offset'] : 0;
$gBitSmarty->assign( 'user_id', @BitBase::verifyId( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : NULL );
$gBitSmarty->assign( 'curPage', $page = @BitBase::verifyId( $_REQUEST['page'] ) ? $_REQUEST['page'] : 1 );
$offset_content = ( $page - 1 ) * $gBitSystem->getPreference( 'max_records' );

// now that we have all the offsets, we can get the content list
include_once( LIBERTY_PKG_PATH.'get_content_list_inc.php' );
 
// calculate page number
$numPages = ceil( $contentList['cant'] / $gBitSystem->getPreference( 'max_records' ) );
$gBitSmarty->assign( 'numPages', $numPages );

//$gBitSmarty->assign_by_ref('offset', $offset);
$gBitSmarty->assign( 'contentSelect', $contentSelect );
$gBitSmarty->assign( 'contentTypes', $contentTypes );
$gBitSmarty->assign( 'contentList', $contentList['data'] );
$gBitSmarty->assign( 'listInfo', $contentList['listInfo'] );

$gBitSystem->setBrowserTitle( 'List Content' );
$gBitSystem->display( 'bitpackage:liberty/list_content.tpl' );
?>
