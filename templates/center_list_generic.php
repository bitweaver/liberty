<?php
/**
 * @version $Header$
 * @package bitweaver
 */
global $gBitSmarty, $gBitSystem, $gQueryUserId, $moduleParams, $gBitUser, $gLibertySystem, $gContent;

if( !empty( $moduleParams ) ) {
	extract( $moduleParams );
}

$_REQUEST['output'] = "raw";

include_once( LIBERTY_PKG_PATH.'list_content.php' );

if ( isset($moduleParams['content_type_guid'] )){
	$contentType = $gLibertySystem->getContentTypeName( $moduleParams['content_type_guid'] );
	$gBitSmarty->assign( "contentType", $contentType );
}
?>
