<?php
/**
 * list_content
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.27 $
 * @package  liberty
 * @subpackage functions
 */

/**
 * required setup
 */
require_once("../bit_setup_inc.php");

$gBitSystem->verifyPermission( 'p_liberty_list_content' );

// some content specific offsets and pagination settings
if( !empty( $_REQUEST['sort_mode'] )) {
	$content_sort_mode = $_REQUEST['sort_mode'];
	$gBitSmarty->assign( 'sort_mode', $content_sort_mode );
}

#$max_content = ( !empty( $_REQUEST['max_records'] )) ? $_REQUEST['max_records'] : $gBitSystem->getConfig( 'max_records' );

if (!empty($_SESSION['liberty_records_per_page'])) {
	$max_content = $_SESSION['liberty_records_per_page'];
	}
else {
	$max_content = $gBitSystem->getConfig( 'max_records', 10 );
	}
if (!empty($_REQUEST["max_records"])) {
	$max_content = $_REQUEST["max_records"];
	$_SESSION['liberty_records_per_page'] = $max_content;
}

// now that we have all the offsets, we can get the content list
include_once( LIBERTY_PKG_PATH.'get_content_list_inc.php' );

$gBitSmarty->assign( 'contentSelect', $contentSelect );
$gBitSmarty->assign( 'contentTypes', $contentTypes );
$gBitSmarty->assign( 'contentList', $contentList['data'] );
$contentList['listInfo']['ihash']['content_type_guid'] = $contentSelect[0];
$contentList['listInfo']['ihash']['user_id'] = @BitBase::verifyId( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : NULL;
$contentList['listInfo']['ihash']['find_objects'] = $contentList['listInfo']['find'];
$gBitSmarty->assign( 'listInfo', $contentList['listInfo'] );
$gBitSmarty->assign( 'content_type_guids', ( isset( $_REQUEST['content_type_guid'] ) ? $_REQUEST['content_type_guid'] : NULL ));

//depricate 'ajax_xml', use 'output'
//@todo clean out from other packages
if( !empty( $_REQUEST['ajax_xml'] )) {
	$_REQUEST['output'] = 'ajax';
}

if( !empty( $_REQUEST['output'] )) {
	switch( $_REQUEST['output'] ) {
	case 'json':
		$gBitSmarty->assign_by_ref( 'listcontent', $contentList['data'] );
		header( 'Content-type:application/json' );
		$gBitSmarty->display( 'bitpackage:liberty/list_content_json.tpl' );
		break;
	case 'ajax':
		/* @TODO: the results structure of this are limited and 
		 * seem specific to some package use. It also requires 
		 * an extra value 'id' which also seems very specific. 
		 * Recommend that this be standardized, but 
		 * a package dependency somewhere is likely an issue
		 */
		require_once( UTIL_PKG_PATH.'javascript/libs/suggest/suggest_lib.php' );
		foreach( array_keys( $contentList['data'] ) as $row ) {
			$xmlList[$contentList['data'][$row]['content_id']] = $contentList['data'][$row]['title'];
		}
		$xml = SuggestLib::exportXml( $xmlList, $_REQUEST['id'] );
		header( "Content-Type: text/xml\n\n" );
		print $xml;
		break;
	case 'raw':
		//means we just want the contents of $contentList when we include this file
		break;
	}
} else {
	$gBitSystem->setBrowserTitle( 'List Content' );
	$gBitSystem->display( 'bitpackage:liberty/list_content.tpl' , NULL, array( 'display_mode' => 'list' ));
}
?>
