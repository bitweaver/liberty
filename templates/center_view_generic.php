<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/templates/center_view_generic.php,v 1.2 2008/07/31 21:23:50 laetzer Exp $
 * @package bitweaver
 */
global $moduleParams, $gContent, $gBitSmarty;

$gContent = NULL;

if( !empty( $moduleParams ) ) {
	extract( $moduleParams );
}

$lookupHash['content_id'] = ( !empty( $module_params['content_id'] ) ? $module_params['content_id'] : NULL );

if( $gContent = LibertyBase::getLibertyObject( $lookupHash['content_id'] ) ) {
	if( !$gContent->hasViewPermission() ){
		// no perm then get rid of the content object
		$gContent = NULL;
	}else{
		// deal with the parsing
		$parseHash['format_guid']		= $gContent->mInfo['format_guid'];
		$parseHash['content_id']		= $gContent->mInfo['content_id'];
		$parseHash['user_id']			= $gContent->mInfo['user_id'];
		$parseHash['data']				= $gContent->mInfo['data'];
		$gContent->mInfo['parsed_data'] = $gContent->parseData( $parseHash );

		if( !empty( $moduleParams['title'] )) {
			$gContent->mInfo['title'] = $moduleParams['title'];
		}

		if ( isset($moduleParams['content_type_guid'] )){
			$gBitSmarty->assign( "contentType", $gContent->mType['content_description'] );
		}
	}
}

$gBitSmarty->assign( "gContent", $gContent );
?>
