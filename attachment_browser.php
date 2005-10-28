<?php
/**
 * attachment_browser
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.1.1.1.2.5 $
 * @package  liberty
 * @subpackage functions
 */

/**
 * bit setup
 */
include_once("../bit_setup_inc.php");

$userAttachments = $gBitUser->getUserAttachments();
$gBitSmarty->assign('userAttachments', $userAttachments);
if (empty($gBitSystem->mStyles['styleSheet'])) {
	$gBitSystem->mStyles['styleSheet'] = $gBitSystem->getStyleCss();
}
$gBitSystem->mStyles['browserStyleSheet'] = $gBitSystem->getBrowserStyleCss();
$gBitSystem->mStyles['customStyleSheet'] = $gBitSystem->getCustomStyleCss();
if( !defined( 'THEMES_STYLE_URL' ) ) {
	define( 'THEMES_STYLE_URL', $gBitSystem->getStyleUrl() );
}

//vd($userAttachments);
$gBitSmarty->display('bitpackage:liberty/attachment_browser.tpl');
?>
