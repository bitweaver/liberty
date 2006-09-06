<?php
/**
 * get_content_list
 *
 * @author   Christian Fowler>
 * @version  $Revision: 1.13 $
 * @package  liberty
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( LIBERTY_PKG_PATH."LibertyContent.php" );
global $gContent;
global $gLibertySystem;

if( empty( $gContent ) || !is_object( $gContent ) ) {
	$gContent = new LibertyContent();
}

if ( !empty($_REQUEST['content_type_guid']) ){
  $content_guids = explode(",", $_REQUEST['content_type_guid']);
}

// get_content_list_inc doesn't use $_REQUEST parameters as it might not be the only list in the page that needs sorting and limiting
if( empty( $contentListHash ) ) {
	$contentListHash = array(
		'content_type_guid' =>   $contentSelect = empty( $_REQUEST['content_type_guid'] ) ? NULL : $content_guids,
		'offset' =>              !empty( $offset_content ) ? $offset_content : NULL,
		'max_records' =>         !empty( $max_content ) ? $max_content : 100,
		'sort_mode' =>           !empty( $content_sort_mode ) ? $content_sort_mode : 'title_asc',
		'find' =>                !empty( $_REQUEST["find_objects"] ) ? $_REQUEST["find_objects"] : NULL,
		'page' =>                !empty( $_REQUEST["list_page"] ) ? $_REQUEST["list_page"] : NULL,
		'user_id' =>             @BitBase::verifyId( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : NULL,
		'last_modified' =>       !empty( $_REQUEST["last_modified"] ) ? $_REQUEST["last_modified"] : NULL,
		'end_date' =>            !empty( $_REQUEST["end_date"] ) ? $_REQUEST["end_date"] : NULL,
	);
}

if ( !empty($_REQUEST['up_lat']) && !empty($_REQUEST['up_lng']) && !empty($_REQUEST['down_lat']) && !empty($_REQUEST['down_lng']) ){
  $contentListHash['up']['lat'] = $_REQUEST['up_lat'];
  $contentListHash['up']['lng'] = $_REQUEST['up_lng'];
  $contentListHash['down']['lat'] = $_REQUEST['down_lat'];
  $contentListHash['down']['lng'] = $_REQUEST['down_lng'];
}

//bleck! wish this service call could be tied to a service name, instead of a specific key for the package
if ( !empty($_REQUEST['pigeonholes']) ){
 $contentListHash['pigeonholes'] = explode(",", $_REQUEST['pigeonholes']);
}

$contentList = $gContent->getContentList( $contentListHash );

$contentTypes = array( '' => 'All Content' );
foreach( $gLibertySystem->mContentTypes as $cType ) {
	$contentTypes[$cType['content_type_guid']] = $cType['content_description'];
}
?>
