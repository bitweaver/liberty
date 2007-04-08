<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/attachment_uploader.php,v 1.2 2007/04/08 17:19:54 nickpalmer Exp $
 * @package liberty
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
include (LIBERTY_PKG_PATH."LibertyAttachable.php");
$la = new LibertyAttachable();
if (!$la->storeAttachments($_REQUEST)) {
	// Do something to indicate failure.
}
// Do whatever you do on success
?>
