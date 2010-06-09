<?php
// $Header$
/**
 * Params:
 * - content_type_guid : if set, show only those content_type_guid's
 * @package liberty
 * @subpackage modules
 */

global $gQueryUser, $gBitUser, $gLibertySystem, $moduleParams;
extract( $moduleParams );

/* this doesn't work as expected. without it, the user can fill in the title himself
if( empty( $module_title ) ) {
	if( !empty( $module_params['content_type_guid'] ) && !empty( $gLibertySystem->mContentTypes[$module_params['content_type_guid']] ) ) {
		$title = tra( "Top Authors" ).': '.$gLibertySystem->getContentTypeName( $module_params['content_type_guid'] );
	} else {
		$gBitSmarty->assign( 'showContentType', TRUE );
		$title = tra( "Top Authors" );
	}
	$gBitSmarty->assign( 'moduleTitle', $title );
}
*/

$listHash = $_REQUEST;
if( !empty( $module_params['content_type_guid'] ) ) {
	$listHash['content_type_guid'] = $module_params['content_type_guid'];
}
$listHash['max_records'] = $module_rows;

$modAuthors = $gBitUser->getAuthorList( $listHash );
$gBitSmarty->assign_by_ref( 'modAuthors', $modAuthors );
?>
