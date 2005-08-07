<?php
// $Header: /cvsroot/bitweaver/_bit_liberty/modules/mod_last_changes.php,v 1.5 2005/08/07 17:40:30 squareing Exp $
/**
 * Params:
 * - content_type_guid : if set, show only those content_type_guid's
 * - show_date : if set, show date of last modification
 * @package liberty
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
		$gBitSmarty->assign( 'contentType', $module_params['content_type_guid'] );
	} else {
		$gBitSmarty->assign( 'contentType', FALSE );
		$title = tra( "Last Changes" );
	}
	$gBitSmarty->assign( 'moduleTitle', $title );
}

if( !empty( $module_params['show_date'] ) ) {
	$gBitSmarty->assign( 'showDate' , TRUE );
}

$modLastContent = $gBitUser->getContentList( !empty( $module_params['content_type_guid'] ) ? $module_params['content_type_guid'] : NULL, 0, $module_rows, 'last_modified_desc', NULL, $userId );
$gBitSmarty->assign_by_ref( 'modLastContent', $modLastContent['data'] );
?>
