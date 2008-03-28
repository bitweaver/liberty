<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/templates/center_list_generic.php,v 1.1 2008/03/28 21:09:48 wjames5 Exp $
 * @package bitweaver
 */
global $gBitSmarty, $gBitSystem, $gQueryUserId, $moduleParams, $gBitUser, $gLibertySystem, $gContent;

if( !empty( $moduleParams ) ) {
	extract( $moduleParams );
}

$_REQUEST['output'] = "raw";

include_once( LIBERTY_PKG_PATH.'list_content.php' );

if ( isset($moduleParams['content_type_guid'] )){
   	$contentType = $gLibertySystem->mContentTypes[ $moduleParams['content_type_guid'] ]['content_description'];
	$gBitSmarty->assign( "contentType", $contentType );
}
?>
