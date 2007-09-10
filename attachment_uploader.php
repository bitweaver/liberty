<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/attachment_uploader.php,v 1.7 2007/09/10 19:26:09 squareing Exp $
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
if( !empty( $storeHash['liberty_attachments']['content_id'] )) {
	$gContent->mContentId = $storeHash['liberty_attachments']['content_id'];
	$storeHash['content_id'] = $storeHash['liberty_attachments']['content_id'];
}

if( isset( $_FILES['upload'] )) {
	if( !$gContent->storeAttachments( $storeHash, FALSE )) {
		$gBitSmarty->assign('errors', $gContent->mErrors);
	} elseif( empty( $gContent->mContentId )) {
		// Fake it for preflight
		$gContent->mStorage[$storeHash['attachment_id']] = $gContent->getAttachment( $storeHash['attachment_id'] );
	}
}

// load the attachments up
if( !empty( $gContent->mContentId )) {
	$gContent->load();
} elseif( !empty( $storeHash['existing_attachment_id'] )) {
	// Fake it for preflight
	foreach( $storeHash['existing_attachment_id'] as $id ) {
		if( !empty( $id )) {
			$gContent->mStorage[$id] = $gContent->getAttachment( $id );
		}
	}
}

// Make them come out in the right order
if( !empty( $gContent->mStorage )) {
	ksort( $gContent->mStorage );
}

$gBitSmarty->assign( 'gContent', $gContent );
$gBitSmarty->assign( 'libertyUploader', TRUE );
echo $gBitSmarty->display( 'bitpackage:liberty/attachment_uploader.tpl' );
?>
