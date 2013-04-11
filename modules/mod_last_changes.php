<?php
/**
 * @version $Header$
 * @package liberty
 * @subpackage modules
 * Params:
 * - content_type_guid : if set, show only those content_type_guid's
 * - show_date : if set, show date of last modification
 */

/**
 * Initialization
 */
global $gQueryUser, $gBitUser, $gLibertySystem, $moduleParams;
extract( $moduleParams );

$userId = NULL;
if( !empty( $gQueryUser->mUserId ) ) {
	$userId = $gQueryUser->mUserId;
}

if( empty( $module_title ) ) {
	if( !empty( $module_params['content_type_guid'] ) && !empty( $gLibertySystem->mContentTypes[$module_params['content_type_guid']] ) ) {
		$title = tra( "Last Changes" ).': '.$gLibertySystem->getContentTypeName( $module_params['content_type_guid'] );
	} else {
		$title = tra( "Last Changes" );
	}
	$moduleParams['title'] = $title;
}

if( !empty( $module_params['show_date'] ) ) {
	$_template->tpl_vars['showDate'] = new Smarty_variable(  TRUE  );
}

$_template->tpl_vars['contentType'] = new Smarty_variable( !empty( $module_params['content_type_guid'] );

$listHash = array(
	'content_type_guid' => !empty( $module_params['content_type_guid'] ) ? $module_params['content_type_guid'] : NULL,
	'offset' => 0,
	'max_records' => $module_rows,
	'sort_mode' => 'last_modified_desc',
	'user_id' => $userId,
);
$modLastContent = $gBitUser->getContentList( $listHash );
$_template->tpl_vars['modLastContent'] = new Smarty_variable( $modLastContent );
?>
