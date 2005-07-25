<?php
// $Header: /cvsroot/bitweaver/_bit_liberty/modules/mod_last_changes.php,v 1.1.1.1.2.2 2005/07/25 14:40:23 spiderr Exp $
/**
 * Params:
 * - content_type_guid : if set, show only those content_type_guid's
 * - show_date : if set, show date of last modification
 * @package Liberty
 * @subpackage modules
 */


global $gQueryUser, $gBitUser, $module_rows, $module_params, $gLibertySystem, $module_title;


$userId = NULL;
if( !empty( $gQueryUser->mUserId ) ) {
	$userId = $gQueryUser->mUserId;
}

if( empty( $module_title ) ) {
	if( !empty( $module_params['content_type_guid'] ) && !empty( $gLibertySystem->mContentTypes[$module_params['content_type_guid']] ) ) {
		$title = tra( "Last Changes" ).': '.tra( $gLibertySystem->mContentTypes[$module_params['content_type_guid']]['content_description'] );
		$smarty->assign( 'contentType', $module_params['content_type_guid'] );
	} else {
		$smarty->assign( 'contentType', FALSE );
		$title = tra( "Last Changes" );
	}
	$smarty->assign( 'moduleTitle', $title );
}

if( !empty( $module_params['show_date'] ) ) {
	$smarty->assign( 'showDate' , TRUE );
}

$modLastContent = $gBitUser->getContentList( !empty( $module_params['content_type_guid'] ) ? $module_params['content_type_guid'] : NULL, 0, $module_rows, 'last_modified_desc', NULL, $userId );
$smarty->assign_by_ref( 'modLastContent', $modLastContent['data'] );
?>
