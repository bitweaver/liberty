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
		$_template->tpl_vars['showContentType'] = new Smarty_variable( TRUE );
		$title = tra( "Top Authors" );
	}
	$_template->tpl_vars['moduleTitle'] = new Smarty_variable( $title );
}
*/

$listHash = $_REQUEST;
if( !empty( $module_params['content_type_guid'] ) ) {
	$listHash['content_type_guid'] = $module_params['content_type_guid'];
}
$listHash['max_records'] = $module_rows;

$modAuthors = $gBitUser->getAuthorList( $listHash );
$_template->tpl_vars['modAuthors'] = new Smarty_variable( $modAuthors );
?>
