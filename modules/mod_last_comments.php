<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/modules/mod_last_comments.php,v 1.4 2007/09/22 15:07:31 nickpalmer Exp $
 * @package liberty
 * @subpackage modules
 */

/**
 * Initial Setup
 */
require_once( LIBERTY_PKG_PATH.'LibertyComment.php' );
global $gQueryUser, $gBitUser, $gLibertySystem, $moduleParams;
$params = $moduleParams['module_params'];
$moduleTitle = !empty($moduleParams['title'])? $moduleParams['title'] : NULL;

$userId = NULL;
if( !empty( $gQueryUser->mUserId ) ) {
	$userId = $gQueryUser->mUserId;
}

$listHash = array(
	'user_id' => $userId,
	'max_records' => $moduleParams['module_rows'],
);

if (!empty($params['full'])) {
	$listHash['parse'] = TRUE;
}

if (!empty($params['pigeonholes'])) {
	$listHash['pigeonholes']['root_filter'] = $params['pigeonholes'];
}

if( !empty( $params['root_content_type_guid'] ) && in_array( $params['root_content_type_guid'], array_keys( $gLibertySystem->mContentTypes ))) {
	if (empty($moduleTitle)) {
		$moduleTitle = $gLibertySystem->mContentTypes[$params['root_content_type_guid']]['content_description'].' '.tra( 'Comments' );
	}
	$listHash['root_content_type_guid'] = $params['root_content_type_guid'];
}
$gBitSmarty->assign( 'moduleTitle', $moduleTitle );

$lcom = new LibertyComment();
$modLastComments = $lcom->getList( $listHash );
$gBitSmarty->assign( 'modLastComments', $modLastComments );
?>
