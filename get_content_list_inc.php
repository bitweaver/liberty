<?php
/**
 * get_content_list
 *
 * @author   Christian Fowler>
 * @version  $Revision: 1.19 $
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

if( !empty($_REQUEST['content_type_guid']) ){
	$contentTypeGuids = explode( ",", $_REQUEST['content_type_guid'] );
}

// get_content_list_inc doesn't use $_REQUEST parameters as it might not be the only list in the page that needs sorting and limiting
if( empty( $contentListHash ) ) {
	$contentListHash = array(
		'content_type_guid' => $contentSelect = empty( $_REQUEST['content_type_guid'] ) ? NULL : $contentTypeGuids,
		// pagination offset
		'offset'            => !empty( $offset_content ) ? $offset_content : NULL,
		// maximum number of records displayed on a page
		'max_records'       => !empty( $max_content ) ? $max_content : 100,
		// sort by this: <table column>_asc (or _desc)
		'sort_mode'         => !empty( $content_sort_mode ) ? $content_sort_mode : 'title_asc',
		// limit the result to this set
		'find'              => !empty( $_REQUEST["find_objects"] ) ? $_REQUEST["find_objects"] : NULL,
		// display this page number - replaces antiquated offset
		'page'              => !empty( $_REQUEST["list_page"] ) ? $_REQUEST["list_page"] : NULL,
		// only display content by this user
		'user_id'           => @BitBase::verifyId( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : NULL,
		// only display content modified more recently than this (UTC timestamp)
		'from_date'         => !empty( $_REQUEST["from_date"] ) ? $_REQUEST["from_date"] : NULL,
		// only display content modified before this (UTC timestamp)
		'until_date'        => !empty( $_REQUEST["until_date"] ) ? $_REQUEST["until_date"] : NULL,
	);
}

// Gate on positional data
if( !empty( $_REQUEST['up_lat'] ) && !empty( $_REQUEST['down_lng'] ) && !empty( $_REQUEST['down_lat'] ) && !empty( $_REQUEST['left_lng'] ) ) {
	$contentListHash['geo_coords'] = array (
		'up_lat' => $_REQUEST['up_lat'],
		'right_lng' => $_REQUEST['right_lng'],
		'down_lat' => $_REQUEST['down_lat'],
		'left_lng' => $_REQUEST['left_lng'],
	);
}

// Only return data that contains geo positional information
$contentListHash['geo_notnull'] = !empty( $_REQUEST['geo_notnull'] );

// Gate on categorised content
if( !empty( $_REQUEST['category_filter'] ) ) {
	if( is_array( $_REQUEST['category_filter'] ) ) {
		$contentListHash['category_filter'] = $_REQUEST['category_filter'];
	} else {
		$contentListHash['category_filter'] = explode( ",", $_REQUEST['category_filter'] );
	}
}

// Finally we're ready to get some content
$contentList = $gContent->getContentList( $contentListHash );

$contentTypes = array( '' => 'All Content' );
foreach( $gLibertySystem->mContentTypes as $cType ) {
	$contentTypes[$cType['content_type_guid']] = $cType['content_description'];
}
?>
