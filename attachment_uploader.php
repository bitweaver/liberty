<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/attachment_uploader.php,v 1.6 2007/04/18 20:52:39 nickpalmer Exp $
 * @package liberty
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
require_once(LIBERTY_PKG_PATH."LibertyAttachable.php");
global $gBitSmarty, $gContent;

$gContent = new LibertyAttachable();

if (!empty($_REQUEST['content_id'])) {
	$gContent->mContentId = $_REQUEST['content_id'];
}

if (isset($_FILES['upload'])) {
	if (!$gContent->storeAttachments($_REQUEST, FALSE)) {
		$gBitSmarty->assign('errors', $gContent->mErrors);
	}
	elseif (empty($gContent->mContentId)) {
		// Fake it for preflight
		$gContent->mStorage[$_REQUEST['attachment_id']] = $gContent->getAttachment($_REQUEST['attachment_id']);		
	}
}

// load the attachments up
if (!empty($gContent->mContentId)) {
	$gContent->load();
}
elseif (!empty($_REQUEST['existing_attachment_id'])) {
	// Fake it for preflight
	foreach( $_REQUEST['existing_attachment_id'] as $id) {
		if (!empty($id)) {
			$gContent->mStorage[$id] = $gContent->getAttachment($id);
		}
	}
}

// Make them come out in the right order
if (!empty($gContent->mStorage)) {
	ksort($gContent->mStorage);
}

$gBitSmarty->assign('gContent', $gContent);
$gBitSmarty->assign('libertyUploader', true);
echo $gBitSmarty->display('bitpackage:liberty/attachment_uploader.tpl');
?>
