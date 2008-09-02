<?php
// $Header: /cvsroot/bitweaver/_bit_liberty/modules/mod_top_authors.php,v 1.7 2008/09/02 05:26:59 laetzer Exp $
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
		$title = tra( "Top Authors" ).': '.tra( $gLibertySystem->mContentTypes[$module_params['content_type_guid']]['content_description'] );
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
