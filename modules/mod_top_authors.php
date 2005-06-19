<?php
// $Header: /cvsroot/bitweaver/_bit_liberty/modules/mod_top_authors.php,v 1.1 2005/06/19 04:55:51 bitweaver Exp $
/**
* Params:
* - content_type_guid : if set, show only those content_type_guid's
*/


global $gQueryUser, $gBitUser, $module_rows, $module_params, $module_title, $gLibertySystem;


if( empty( $module_title ) ) {
	if( !empty( $module_params['content_type_guid'] ) && !empty( $gLibertySystem->mContentTypes[$module_params['content_type_guid']] ) ) {
		$title = tra( "Top Authors" ).': '.tra( $gLibertySystem->mContentTypes[$module_params['content_type_guid']]['content_description'] );
	} else {
		$smarty->assign( 'showContentType', TRUE );
		$title = tra( "Top Authors" );
	}
	$smarty->assign( 'moduleTitle', $title );
}


$listHash = $_REQUEST;
if( !empty( $module_params['content_type_guid'] ) ) {
	 $listHash['content_type_guid'] = $module_params['content_type_guid'];
}
$listHash['max_request'] = $module_rows;

$modAuthors = $gBitUser->getAuthorList( $listHash );
$smarty->assign_by_ref( 'modAuthors', $modAuthors );
?>
