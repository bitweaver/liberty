<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/modules/mod_last_changes.php,v 1.1.1.1.2.6 2005/10/30 21:03:50 lsces Exp $
 * @package liberty
 * @subpackage modules
 * Params:
 * - content_type_guid : if set, show only those content_type_guid's
 * - show_date : if set, show date of last modification
 */

/**
 * Initialization
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

$listHash = array(
	'content_type_guid' => !empty( $module_params['content_type_guid'] ) ? $module_params['content_type_guid'] : NULL,
	'offset' => 0,
	'max_records' => $module_rows,
	'sort_mode' => 'last_modified_desc',
	'user_id' => $userId,
);
$modLastContent = $gBitUser->getContentList( $listHash );
$gBitSmarty->assign_by_ref( 'modLastContent', $modLastContent['data'] );
?>
