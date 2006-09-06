<?php
/**
 * list_content
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.19 $
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

$max_content = ( !empty( $_REQUEST['max_records'] ) )?$_REQUEST['max_records']:$gBitSystem->getConfig( 'max_records' );
$gBitSmarty->assign( 'user_id', @BitBase::verifyId( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : NULL );

// now that we have all the offsets, we can get the content list
include_once( LIBERTY_PKG_PATH.'get_content_list_inc.php' );

$gBitSmarty->assign( 'contentSelect', $contentSelect );
$gBitSmarty->assign( 'contentTypes', $contentTypes );
$gBitSmarty->assign( 'contentList', $contentList['data'] );
$contentList['listInfo']['parameters']['content_type_guid'] = $contentSelect;
$gBitSmarty->assign( 'listInfo', $contentList['listInfo'] );

//depricate 'ajax_xml', use 'output'
//@todo clean out from other packages
if( !empty( $_REQUEST['ajax_xml'] ) ) {
  $_REQUEST['output'] = 'ajax';
}

if ( !empty( $_REQUEST['output'] ) ){
  switch ($_REQUEST['output']){
    case 'json':
      $gBitSmarty->assign_by_ref('listcontent', $contentList['data']);
      header("content-type:text/javascript");			
      $gBitSmarty->display( 'bitpackage:liberty/list_content_json.tpl' );
      break;
    case 'ajax':
      /*@todo the results structure of this are limited and 
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
  }
} else {
	$gBitSystem->setBrowserTitle( 'List Content' );
	$gBitSystem->display( 'bitpackage:liberty/list_content.tpl' );
}
?>
