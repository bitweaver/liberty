<?php
/**
 * list_content
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.16 $
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
$gBitSmarty->assign( 'user_id', @BitBase::verifyId( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : NULL );

// now that we have all the offsets, we can get the content list
include_once( LIBERTY_PKG_PATH.'get_content_list_inc.php' );

$gBitSmarty->assign( 'contentSelect', $contentSelect );
$gBitSmarty->assign( 'contentTypes', $contentTypes );
$gBitSmarty->assign( 'contentList', $contentList['data'] );
$contentList['listInfo']['parameters']['content_type_guid'] = $contentSelect;
$gBitSmarty->assign( 'listInfo', $contentList['listInfo'] );

$gBitSystem->setBrowserTitle( 'List Content' );
$gBitSystem->display( 'bitpackage:liberty/list_content.tpl' );
?>
