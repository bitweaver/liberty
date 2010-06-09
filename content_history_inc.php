<?php
/**
 * @version  $Revision$
 * @package  liberty
 * @subpackage functions
 */


$gBitSmarty->assign( 'source', 0 );
// If we have to include a preview please show it
$gBitSmarty->assign( 'preview', FALSE );
$gBitSmarty->assign( 'compare', 'n' );
$gBitSmarty->assign( 'diff2', 'n' );

if( isset( $_REQUEST["delete"] ) && isset( $_REQUEST["hist"] )) {
	foreach( array_keys( $_REQUEST["hist"] ) as $version ) {
		$gContent->expungeVersion( $version );
	}

} elseif( isset( $_REQUEST['source'] )) {
	$gBitSmarty->assign( 'source', $_REQUEST['source'] );
	if( $_REQUEST['source'] == 'current' ) {
		$gBitSmarty->assign( 'sourcev', nl2br( htmlentities( $gContent->mInfo["data"] )));
	} else {
		$version = $gContent->getHistory( $_REQUEST["source"] );
		$gBitSmarty->assign( 'sourcev', nl2br( htmlentities( $version["data"][0]["data"] )));
	}

} elseif( @BitBase::verifyId( $_REQUEST["preview"] )) {
	if( $version = $gContent->getHistory( $_REQUEST["preview"] )) {
		$version['data'][0]['no_cache'] = TRUE;
		$version['data'][0]['parsed_data'] = $gContent->parseData( $version["data"][0] );
		$gBitSmarty->assign_by_ref( $smartyContentRef, $version['data'][0] );
		$gBitSmarty->assign_by_ref( 'version', $_REQUEST["preview"] );
	}

} elseif( @BitBase::verifyId( $_REQUEST["diff2"] ) ) {
	$from_version = $_REQUEST["diff2"];
	$from_page = $gContent->getHistory( $from_version );
	$from_lines = explode( "\n",$from_page["data"][0]["data"] );
	if( isset( $_REQUEST["diff_to"] ) && $_REQUEST["diff_to"] != $gContent->mInfo["version"] ) {
		$to_version = $_REQUEST["diff_to"];
		$to_page = $gContent->getHistory( $to_version );
		$to_lines = explode( "\n",$to_page["data"][0]["data"] );
	} else {
		$to_version = $gContent->mInfo["version"];
		$to_lines = explode( "\n",$gContent->mInfo["data"] );
	}
	/**
	 * run 'pear install Text_Diff' to install the library,
	 */
	if( $gBitSystem->isFeatureActive( 'liberty_inline_diff' ) && @include_once( 'Text/Diff.php' )) {
		include_once( 'Text/Diff/Renderer/inline.php' );
		$diff = &new Text_Diff( $from_lines, $to_lines );
		$renderer = &new Text_Diff_Renderer_inline();
		$html = $renderer->render( $diff );
	} else {
		include_once( UTIL_PKG_PATH.'diff.php');
		$diffx = new WikiDiff( $from_lines,$to_lines );
		$fmt = new WikiUnifiedDiffFormatter;
		$html = $fmt->format( $diffx, $from_lines );
	}
	$gBitSmarty->assign( 'diffdata', $html );
	$gBitSmarty->assign( 'diff2', 'y' );
	$gBitSmarty->assign( 'version_from', $from_version );
	$gBitSmarty->assign( 'version_to', $to_version );

} elseif( @BitBase::verifyId( $_REQUEST["compare"] )) {
	$from_version = $_REQUEST["compare"];
	$from_page = $gContent->getHistory( $from_version );
	$from_page['data'][0]['no_cache'] = TRUE;
	$gBitSmarty->assign( 'compare', 'y' );
	$gBitSmarty->assign_by_ref( 'diff_from', $gContent->parseData( $from_page['data'][0] ) );
	$gBitSmarty->assign_by_ref( 'diff_to', $gContent->parseData() );
	$gBitSmarty->assign_by_ref( 'version_from', $from_version );

} elseif( @BitBase::verifyId( $_REQUEST["rollback"] )) {
	$gContent->verifyUserPermission( !empty( $rollbackPerm ) ? $rollbackPerm : $gContent->mUpdateContentPerm );
	if( !isset( $_REQUEST["rollback_comment"] )) {
		$_REQUEST["rollback_comment"] = '';
	}
	if( $gContent->rollbackVersion( $_REQUEST["rollback"], $_REQUEST["rollback_comment"] )) {
		bit_redirect( $gContent->getDisplayUrl() );
	}
}

?>
