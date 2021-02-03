<?php
/**
 * list_content
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision$
 * @package  liberty
 * @subpackage functions
 */

/**
 * required setup
 */
require_once("../kernel/includes/setup_inc.php");

$gBitSystem->verifyPermission( 'p_liberty_list_content' );

// some content specific offsets and pagination settings
if( !empty( $_REQUEST['sort_mode'] )) {
	$content_sort_mode = $_REQUEST['sort_mode'];
	$gBitSmarty->assign( 'sort_mode', $content_sort_mode );
}

#$max_content = ( !empty( $_REQUEST['max_records'] )) ? $_REQUEST['max_records'] : $gBitSystem->getConfig( 'max_records' );

if( !empty( $_POST ) ) {
	$feedback = array();

	$gBitUser->verifyTicket();
	switch( BitBase::getParameter( $_POST, 'action' ) ) {
		case 'delete':
			if( !empty( $_POST['batch_content_ids'] ) ) {
				// only admins can batch delete
				$gBitSystem->verifyPermission( 'p_admin' );
				$delUsers = $errDelUsers = "";
				foreach( $_POST['batch_content_ids'] as $contentId ) {
					if( ($content = LibertyContent::getLibertyObject( $contentId )) && $content->isValid() ) {
						$title = $content->getTitle();
						if( $content->expunge() ) {
							$delUsers .= '<li>'.$content->getField('content_type_guid').'#'.$contentId." ".$title."</li>";
						} else {
							$errDelUsers .= "<li>#$contentId could not be expunged</li>";
						}
					} else {
						$errDelUsers .= "<li>#$contentId could not be loaded</li>";
					}
				}
			}
			break;
	}
	if( !empty( $delUsers ) ) {
		$feedback['success'][] = tra( 'Content deleted' ).": <ul>$delUsers</ul>";
	} 
	if( !empty( $errDelUsers ) ) {
		$feedback['error'][] = tra( 'Content not deleted' ).": <ul>$errDelUsers</ul>";
	}
	$gBitSmarty->assign( 'feedback', $feedback );
}

if( !empty( $_SESSION['liberty_records_per_page'] )) {
	$max_content = $_SESSION['liberty_records_per_page'];
} else {
	$max_content = $gBitSystem->getConfig( 'max_records', 10 );
}
if( !empty( $_REQUEST["max_records"] )) {
	$max_content = $_REQUEST["max_records"];
	$_SESSION['liberty_records_per_page'] = $max_content;
}

// now that we have all the offsets, we can get the content list
include_once( LIBERTY_PKG_INCLUDE_PATH.'get_content_list_inc.php' );

$gBitSmarty->assign( 'contentSelect', $contentSelect );
$gBitSmarty->assign( 'contentTypes', $contentTypes );
$gBitSmarty->assign( 'contentList', $contentList );
$contentListHash['listInfo']['ihash']['content_type_guid'] = $contentSelect;
$contentListHash['listInfo']['ihash']['user_id'] = @BitBase::verifyId( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : NULL;
$contentListHash['listInfo']['ihash']['find'] = $contentListHash['listInfo']['find'];
$gBitSmarty->assign( 'listInfo', $contentListHash['listInfo'] );
$gBitSmarty->assign( 'content_type_guids', ( isset( $_REQUEST['content_type_guid'] ) ? $_REQUEST['content_type_guid'] : NULL ));

//depricate 'ajax_xml', use 'output'
//@todo clean out from other packages
if( !empty( $_REQUEST['ajax_xml'] )) {
	$_REQUEST['output'] = 'ajax';
}

if( !empty( $_REQUEST['output'] )) {
	switch( $_REQUEST['output'] ) {
	case 'json':
		$gBitSmarty->assignByRef( 'listcontent', $contentList );
		header( 'Content-type:application/json' );
		$gBitSmarty->display( 'bitpackage:liberty/list_content_json.tpl' );
		break;
	case 'ajax':
		/* @TODO the results structure of this are limited and 
		 * seem specific to some package use. It also requires 
		 * an extra value 'id' which also seems very specific. 
		 * Recommend that this be standardized, but 
		 * a package dependency somewhere is likely an issue
		 */
		require_once( UTIL_PKG_PATH.'javascript/suggest/suggest_lib.php' );
		foreach( array_keys( $contentList ) as $row ) {
			$xmlList[$contentList[$row]['content_id']] = $contentList[$row]['title'];
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
