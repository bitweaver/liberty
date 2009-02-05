<?php
/**
 * get_content_list
 *
 * @author   Christian Fowler>
 * @version  $Revision: 1.34 $
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

$contentTypeGuids = array();
if( !empty( $_REQUEST['content_type_guid'] )) {
	if( !is_array( $_REQUEST['content_type_guid'] )) {
		$guids = explode( ",", $_REQUEST['content_type_guid'] );
	} else {
		$guids = $_REQUEST['content_type_guid'];
	}
	/**
	 * if an empty string was passed in an array (likely since it is used for ALL) then the user has requested all so return all
	 * even if they have requested additional content types too - ALL is ALL
	 * this check is reversed in that if no empty string in the array then we pass the array of content types to be limited on
	 **/
	if( !in_array( "", $guids ) ){
		$contentTypeGuids = $guids;
	}
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
		/**
		 * NOTE: the use of 'find' here replaces the former value 'find_objects' to standardize the use of 'find' as a 
		 * search param. 'find' is used commonly in getList methods both in LibertyContent and throughout bitweaver packages
		 * to search titles for matches. 'find_objects' was perhaps used in the past to distinguish from 'find' as there seemed
		 * to be collisions with modules that might have used $_REQUEST['find'] in their processes for unknown reasons. Those collisions
		 * do not appear to exist any longer. Module processes in general should not use $_REQUEST params. Further an investigation
		 * of the bitweaver code base turned up no obvious instances where the use of find here would collide with other uses.
		 * Thus there is no anticipation that this change should effect any other processess that are actively maintained. That said, 
		 * should some conflict be found there are two possible solutions. One is investigating whether the use of $_REQUEST['find']
		 * is necessary to the process or if find should be passed to the process in another hash. The other is to decease all use of 
		 * $_REQUEST here and instead pass a hash to this process, which would likely be a more intellegent solution.
		 *
		 * This change was made Jan 24 2008. If after 6 months no conflict has been discovered, you may remove this comment bloat. -wjames5
		 **/
		'find'              => !empty( $_REQUEST["find"] ) ? $_REQUEST["find"] : NULL,
		// display this page number - replaces antiquated offset
		'page'              => !empty( $_REQUEST["list_page"] ) ? $_REQUEST["list_page"] : NULL,
		// only display content by this user
		'user_id'           => @BitBase::verifyId( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : NULL,
		// only display content modified more recently than this (UTC timestamp)
		'from_date'         => !empty( $_REQUEST["from_date"] ) ? $_REQUEST["from_date"] : NULL,
		// only display content modified before this (UTC timestamp)
		'until_date'        => !empty( $_REQUEST["until_date"] ) ? $_REQUEST["until_date"] : NULL,
		// get a thumbnail - off by default because it is expensive
		'thumbnail_size'    => !empty( $_REQUEST["thumbnail_size"] ) ? $_REQUEST["thumbnail_size"] : NULL,
	);

	if( !empty( $_REQUEST['output'] ) && ( $_REQUEST['output'] == 'json' || $_REQUEST['output'] == 'ajax' ) ) {	
		foreach( $_REQUEST as $key => $value ) {
			if ( !is_array($value) ){
				if( strstr( $value, ',' ) ) {
					$_REQUEST[$key] = explode( ",", $value );
				}
			}
		}
	}

	$contentListHash = array_merge( $_REQUEST, $contentListHash );
}

// Finally we're ready to get some content
$contentList = $gContent->getContentList( $contentListHash );

if( empty( $contentTypes ) ) {
	$contentTypes = array( '' => tra( 'All Content' ) );
	foreach( $gLibertySystem->mContentTypes as $cType ) {
		$contentTypes[$cType['content_type_guid']] = $cType['content_description'];
	}
	asort( $contentTypes );
}
global $gBitSystem, $gBitUser;
if( $gBitSystem->isFeatureActive( 'liberty_display_status' ) &&  $gBitUser->hasPermission( 'p_liberty_view_all_status' )) {
	$contentStatuses = $gContent->getAvailableContentStatuses();
	$contentStatuses[''] = 'All Statuses';
	$contentStatuses['not_available'] = 'All but Available';
	$gBitSmarty->assign( 'content_statuses', $contentStatuses );
}
?>
