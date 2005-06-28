<?php
/**
 * attachment_browser
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.2 $
 * @package  Liberty
 * @subpackage functions
 */

/**
 * bit setup
 */
include("../bit_setup_inc.php");

$userAttachments = $gBitUser->getUserAttachments();
$smarty->assign('userAttachments', $userAttachments);
if (empty($gBitLoc['styleSheet'])) {
	$gBitLoc['styleSheet'] = $gBitSystem->getStyleCss();
}
$gBitLoc['browserStyleSheet'] = $gBitSystem->getBrowserStyleCss();
$gBitLoc['customStyleSheet'] = $gBitSystem->getCustomStyleCss();
$gBitLoc['THEMES_STYLE_URL'] = $gBitSystem->getStyleUrl();
//vd($userAttachments);
$smarty->display('bitpackage:liberty/attachment_browser.tpl');
?>
