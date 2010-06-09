<?php

require_once( LIBERTY_PKG_PATH.'LibertyComment.php' );
global $gQueryUser, $gBitUser, $gLibertySystem, $moduleParams;
$params = $moduleParams['module_params'];
$moduleTitle = !empty($moduleParams['title'])? $moduleParams['title'] : 'Recent Activity';

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

$listHash['full'] = (!empty( $params['full'] ) ? $params['full'] : TRUE);
$listHash['thumb_size'] = (!empty( $params['thumb_size'] ) ? $params['thumb_size'] : 'avatar');
$listHash['show_date'] = (!empty( $params['show_date'] ) ? $params['show_date'] : TRUE);;

if( !empty( $params['root_content_type_guid'] ) ) {
	$listHash['root_content_type_guid'] = $params['root_content_type_guid'];
}

$gBitSmarty->assign( 'moduleTitle', $moduleTitle );
$lcom = new LibertyComment();
$modLastComments = $lcom->getList( $listHash );
$keys = array_keys( $modLastComments );
foreach( $keys as $k ) {
	if($modLastComments[$k]['parent_content_type_guid'] == 'feedstatus'){ //if comment is a reply to a status, use the poster as the object, otherwise our thumbnail will be of the content we commented on (the other user,status)
		$user = new BitUser( $modLastComments[$k]['user_id'] );
		$user->load();
 		$modLastComments[$k]['object'] = $user;
	}else{ //If a comment on a piece of content, use piece of content as object in question
		$modLastComments[$k]['object'] = LibertyBase::getLibertyObject( $modLastComments[$k]['root_id'], $modLastComments[$k]['root_content_type_guid'] );
	}
}

$gBitSmarty->assign( 'modLastComments', $modLastComments );

?>
