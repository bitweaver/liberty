<?php
/**
 * get_content_list
 *
 * @author   Christian Fowler>
 * @version  $Revision: 1.1.1.1.2.8 $
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

// get_content_list_inc doesn't use $_REQUEST parameters as it might not be the only list in the page that needs sorting and limiting
if( empty( $contentListHash ) ) {
	$contentListHash = array(
		'content_type_guid' =>   $contentSelect = empty( $_REQUEST['content_type_guid'] ) ? NULL : $_REQUEST['content_type_guid'],
		'offset' =>              !empty( $offset_content ) ? $offset_content : 0,
		'max_records' =>         !empty( $max_content ) ? $max_content : 500,
		'sort_mode' =>           !empty( $content_sort_mode ) ? $content_sort_mode : 'title_asc',
		'find' =>                !empty( $_REQUEST["find_objects"] ) ? $_REQUEST["find_objects"] : NULL,
		'user_id' =>             !empty( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : NULL,
	);
}
$contentList = $gContent->getContentList( $contentListHash );

$contentTypes = array( '' => tra( 'All Content' ) );
foreach( $gLibertySystem->mContentTypes as $cType ) {
	$contentTypes[$cType['content_type_guid']] = $cType['content_description'];
}
?>
