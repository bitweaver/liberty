<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/attachment_uploader.php,v 1.9 2007/09/30 00:49:23 nickpalmer Exp $
 * @package liberty
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
require_once( LIBERTY_PKG_PATH."LibertyAttachable.php" );
global $gBitSmarty, $gContent;

$gContent = new LibertyAttachable();

// make a copy of $_REQUEST that we can mess with it without interfering with the rest of the page
$storeHash = $_REQUEST;

// Take the one we are going to delete out of existing.
//if( !empty($_REQUEST['deleteAttachment'] ) ) {
//	unset($storeHash['STORAGE']['existing'][$_REQUEST['deleteAttachment']]);
//}

// Do we have an attachment to save?
if( !empty($_FILES['upload']) ) {

	// Do we have a content_id already or is this a preflight?
	if( !empty( $storeHash['liberty_attachments']['content_id'] ) ) {

		// Get the content_id into the right places.
		$gContent->mContentId = $storeHash['content_id'] = $storeHash['liberty_attachments']['content_id'];
	}
	else {
		$storeHash['content_id'] = $gContent->mContentId = NULL;
	}

	$storeHash['skip_content_store'] = true;

	// store the attachment.
	if ( !$gContent->store( $storeHash ) ) {
		$gBitSmarty->assign('errors', $gContent->mErrors);
		// If this is the first set it to be primary
		if ( empty( $storeHash['STORAGE']['existing'] ) ) {
			foreach ( $storeHash['STORAGE'] as $id => $file ) {
				if ( $id != 'existing' ) {
					foreach ( $file as $key => $data ) {
						$gContent->setPrimaryAttachment( $key );
						$done = true;
						break;
					}
				}
				if ( $done ) {
					break;
				}
			}
		}
	}
	else {
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
else {
	$gBitSmarty->assign( 'errors', tra( 'There was an unknown error with the upload.' ) );
}

if ( empty($gContent->mContentId) ) {
	// Setup the existing_attachment_id stuff
	if( !empty( $storeHash['STORAGE']['existing'] ) ) {
		// Fake it for preflight
		foreach( $storeHash['STORAGE']['existing'] as $id ) {
			if( !empty( $id ) ) {
				$gContent->mStorage[$id] = $gContent->getAttachment( $id );
			}
		}
	}
}
else {
	$gContent->load();
}

// Make them come out in the right order
if( !empty( $gContent->mStorage ) ) {
	ksort( $gContent->mStorage );
}

$gBitSmarty->assign( 'gContent', $gContent );
$gBitSmarty->assign( 'libertyUploader', TRUE );
$gBitSmarty->assign( 'uploadTab', TRUE );
echo $gBitSystem->display( 'bitpackage:liberty/attachment_uploader.tpl', NULL, 'none' );
?>
