<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/modules/mod_last_comments.php,v 1.3 2007/06/22 10:16:09 lsces Exp $
 * @package liberty
 * @subpackage modules
 */

/**
 * Initial Setup
 */
require_once( LIBERTY_PKG_PATH.'LibertyComment.php' );
global $gQueryUser, $gBitUser, $gLibertySystem, $moduleParams;
$params = $moduleParams['module_params'];

$userId = NULL;
if( !empty( $gQueryUser->mUserId ) ) {
	$userId = $gQueryUser->mUserId;
}

$listHash = array(
	'user_id' => $userId,
	'max_records' => $moduleParams['module_rows'],
);

if( !empty( $params['root_content_type_guid'] ) && in_array( $params['root_content_type_guid'], array_keys( $gLibertySystem->mContentTypes ))) {
	$moduleTitle = $gLibertySystem->mContentTypes[$params['root_content_type_guid']]['content_description'].' '.tra( 'Comments' );
	$gBitSmarty->assign( 'moduleTitle', $moduleTitle );
	$listHash['root_content_type_guid'] = $params['root_content_type_guid'];
}

$lcom = new LibertyComment();
$modLastComments = $lcom->getList( $listHash );
$gBitSmarty->assign( 'modLastComments', $modLastComments );
?>
