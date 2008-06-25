<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/attachment_uploader.php,v 1.13 2008/06/25 22:21:12 spiderr Exp $
 * @package liberty
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
global $gBitSmarty, $gContent;

$error = NULL;
if ( !isset($_FILES['upload'] ) ) {
	$error = tra( "No upload submitted." );
}elseif( !empty( $_REQUEST['liberty_attachments']['content_id'] )) {
// if we have a content id then we just load up that
	if( !($gContent = LibertyBase::getLibertyObject( $_REQUEST['liberty_attachments']['content_id'] )) ) {
		// if there is something wrong with the content id spit back an error
		$error = tra( "You are attempting to upload a file to a content item that does not exist." );
	}
}elseif( isset ( $_REQUEST['liberty_attachments']['content_type_guid'] ) ){
/* if we don't have a content id then we assume this is new content and we need to create a draft.
 * we'll pass a new content_id back to the edit form so it can make the right association later on save.
 */
	// if we are creating new content the status must be enforced, so status recognition must be enabled
	if( !$gBitSystem->isFeatureActive( "liberty_display_status" ) ){
		$error = tra( "You must save the content to upload an attachment." );
	}elseif( !isset( $gLibertySystem->mContentTypes[$_REQUEST['liberty_attachments']['content_type_guid']] ) ){
		$error = tra( "You are attempting to upload a file to an invalid content type" );
	}else{
		// load up the requested content type handler class
		$contentType = $_REQUEST['liberty_attachments']['content_type_guid'];
		$contentTypeHash = $gLibertySystem->mContentTypes[$contentType];
		$class =  $contentTypeHash['handler_class'];
		$classFile =  $contentTypeHash['handler_file'];
		$package = $contentTypeHash['handler_package'];
		$pathVar = strtoupper($package).'_PKG_PATH';

		if( !defined( $pathVar ) ) {
			$error = tra( "Undefined handler package path" );
		}else{
			require_once( constant( $pathVar ).$classFile );
			$gContent = new $class();
		}
	}
}else{
// if we don't have a valid content_id or content_type_guid we can't do nothing for you
	$error = "You have not specified a content item or content type to associate this upload with";
}

if( isset( $gContent ) ){
	$storeHash = $_REQUEST['liberty_attachments'];

	if ( !$gContent->isValid() ){
		// if we dont have a content object for this attachment yet, lets create a draft.
		$storeHash['content_status_id'] = -5;
	}else{
		// else we'll skip storing the content
		$storeHash['skip_content_store'] = true;
	}

	// store the attachment.
	if ( !$gContent->store( $storeHash ) ) {
		$error = $gContent->mErrors;
	}else{
		// Load up the new attachment.
		if ( !empty($storeHash['STORAGE'] ) ) {
			foreach ( $storeHash['STORAGE'] as $id => $file ) {
				if ( $id != 'existing' ) {
					foreach ( $file as $key => $data ) {
						$gContent->mStorage[$data['attachment_id']] = $gContent->getAttachment( $data['attachment_id'] );
					}
				}
			}
		}
	}
}

if ( !is_null( $error ) ){
	if ( is_array( $error ) ){
		$error = implode("\n", $error);
	} 
	$gBitSmarty->assign('errors', $error);
}else{
	// @Todo is this stuff necessary?
	$gContent->load();
	// Make them come out in the right order
	if( !empty( $gContent->mStorage ) ) {
		ksort( $gContent->mStorage );
	}
}

$gBitSmarty->assign( 'gContent', $gContent );
$gBitSmarty->assign( 'libertyUploader', TRUE );
$gBitSmarty->assign( 'uploadTab', TRUE );

echo $gBitSystem->display( 'bitpackage:liberty/attachment_uploader.tpl', NULL, 'none' , array( 'display_mode' => 'display' ));
?>
