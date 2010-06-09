<?php
/**
 * @version  $Header$
 * lookup_content_inc
 *
 * @author   spider <spider@steelsun.com>
 * @package  liberty
 * @subpackage functions
 */

/**
 * Required setup
 */
global $gLightweightScan;
$gLightwieightScan = TRUE;
require_once( '../kernel/setup_inc.php' );

global $gContent, $gBitSystem, $gBitSmarty;
include_once( LIBERTY_PKG_PATH.'lookup_content_inc.php' );
// If we can't find one make an invalid one to keep the template happy.
if (empty($gContent)) {
	$gContent = new LibertyContent();
	$gBitSmarty->assign_by_ref('gContent', $gContent);
}
// Should we tell the template to generate a closeclick icon
if (isset($_REQUEST['closeclick'])) {
	$gBitSmarty->assign('closeclick', true);
}

header( 'Content-Type: text/html; charset=utf-8' );
echo $gContent->getPreview();

?>
