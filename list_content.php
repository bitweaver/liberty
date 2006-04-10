<?php
/**
 * list_content
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.18 $
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

$max_content = $gBitSystem->getConfig( 'max_records' );
$gBitSmarty->assign( 'user_id', @BitBase::verifyId( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : NULL );

// now that we have all the offsets, we can get the content list
include_once( LIBERTY_PKG_PATH.'get_content_list_inc.php' );

$gBitSmarty->assign( 'contentSelect', $contentSelect );
$gBitSmarty->assign( 'contentTypes', $contentTypes );
$gBitSmarty->assign( 'contentList', $contentList['data'] );
$contentList['listInfo']['parameters']['content_type_guid'] = $contentSelect;
$gBitSmarty->assign( 'listInfo', $contentList['listInfo'] );

if( !empty( $_REQUEST['ajax_xml'] ) ) {
	require_once( UTIL_PKG_PATH.'javascript/libs/suggest/suggest_lib.php' );
	foreach( array_keys( $contentList['data'] ) as $row ) {
		$xmlList[$contentList['data'][$row]['content_id']] = $contentList['data'][$row]['title'];
	}
	$xml = SuggestLib::exportXml( $xmlList, $_REQUEST['id'] );
	header( "Content-Type: text/xml\n\n" );
	print $xml;
} else {
	$gBitSystem->setBrowserTitle( 'List Content' );
	$gBitSystem->display( 'bitpackage:liberty/list_content.tpl' );
}
?>
