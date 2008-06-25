<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/add_structure_content.php,v 1.6 2008/06/25 22:21:12 spiderr Exp $
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.6 $
 * @package  liberty
 * @subpackage functions
 */

/**
 * required setup
 */
$gLiteweightScan = TRUE;
require_once( '../bit_setup_inc.php' );

if( !empty( $_REQUEST['modal'] ) ) {
	$gBitSystem->mConfig['site_top_bar'] = FALSE;
	$gBitSystem->mConfig['site_left_column'] = FALSE;
	$gBitSystem->mConfig['site_right_column'] = FALSE;
	$gBitSmarty->assign( 'popupPage', '1' );
}

require_once( LIBERTY_PKG_PATH.'lookup_content_inc.php' );
require_once( LIBERTY_PKG_PATH.'edit_structure_inc.php' );

if( !empty( $_SERVER['HTTP_REFERER'] ) ) {
	$urlHash = parse_url( $_SERVER['HTTP_REFERER'] );
	if( $urlHash['path'] != $_SERVER['SCRIPT_NAME'] ) {
		$_SESSION['structure_referer'] = $_SERVER['HTTP_REFERER'];
	}
}

if( $gBitThemes->isAjaxRequest() ) {
	header( 'Content-Type: text/html; charset=utf-8' );
	print $gBitSmarty->fetch( "bitpackage:liberty/add_structure_feedback_inc.tpl" ); 
	exit;
} else {

	$_REQUEST['thumbnail_size'] = 'icon';
	include_once( LIBERTY_PKG_PATH.'get_content_list_inc.php' );
	foreach( $contentList['data'] as $cItem ) {
		$cList[$contentTypes[$cItem['content_type_guid']]][$cItem['content_id']] = $cItem['title'].' [id: '.$cItem['content_id'].']';
	}
	$gBitSmarty->assign_by_ref( 'contentListHash', $contentList['data'] );
	$gBitSmarty->assign( 'contentList', $cList );
	$gBitSmarty->assign( 'contentSelect', $contentSelect );
	$gBitSmarty->assign( 'contentTypes', $contentTypes );

	$subpages = $gStructure->getStructureNodes($_REQUEST["structure_id"]);
	$max = count($subpages);
	$gBitSmarty->assign_by_ref('subpages', $subpages);
	if ($max != 0) {
		$last_child = $subpages[$max - 1];
		$gBitSmarty->assign('insert_after', $last_child["structure_id"]);
	}

	if( !empty( $_REQUEST['done'] ) ) {
		bit_redirect( $_SESSION['structure_referer'] );
	}
	if( !$gBitThemes->loadAjax( 'mochikit', array( 'Iter.js', 'DOM.js', 'Format.js', 'Style.js', 'Signal.js', 'Logging.js', 'ThickBox.js' ) ) ) {
		// do something....
	}
	$gBitSystem->display( 'bitpackage:liberty/add_structure_content.tpl', "Add Content" , array( 'display_mode' => 'display' ));
}

?>
