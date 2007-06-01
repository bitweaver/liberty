<?php

$gBitSmarty->assign('source', 0);
// If we have to include a preview please show it
$gBitSmarty->assign('preview', false);
$gBitSmarty->assign('compare', 'n');
$gBitSmarty->assign('diff2', 'n');
if (isset($_REQUEST["delete"]) && isset($_REQUEST["hist"])) {
	foreach (array_keys($_REQUEST["hist"])as $version) {
		$gContent->expungeVersion( $version );
	}
} elseif (isset($_REQUEST['source'])) {
	$gBitSmarty->assign('source', $_REQUEST['source']);
	if ($_REQUEST['source'] == 'current') {
		$gBitSmarty->assign('sourcev', nl2br(htmlentities($gContent->mInfo["data"])));
	} else {
		$version = $gContent->getHistory($_REQUEST["source"]);
		$gBitSmarty->assign('sourcev', nl2br(htmlentities($version["data"][0]["data"])));
	}
} elseif (isset($_REQUEST["preview"])) {
	if( $version = $gContent->getHistory( $_REQUEST["preview"] ) ) {
		$version['data'][0]['parsed_data'] = $gContent->parseData( $version["data"][0]);
		$gBitSmarty->assign_by_ref($smartyContentRef, $version['data'][0] );
		$gBitSmarty->assign_by_ref('version', $_REQUEST["preview"]);		
	}
} elseif( isset( $_REQUEST["diff2"] ) ) {
	$from_version = $_REQUEST["diff2"];
	$from_page = $gContent->getHistory( $from_version );
	$from_lines = explode("\n",$from_page["data"][0]["data"]);
	$to_version = $gContent->mInfo["version"];
	$to_lines = explode("\n",$gContent->mInfo["data"]);

	include_once( UTIL_PKG_PATH.'diff.php');
	$diffx = new WikiDiff($from_lines,$to_lines);
	$fmt = new WikiUnifiedDiffFormatter;
	$html = $fmt->format($diffx, $from_lines);
	$gBitSmarty->assign('diffdata', $html);
	$gBitSmarty->assign('diff2', 'y');
	$gBitSmarty->assign('version_from', $from_version);
	$gBitSmarty->assign('version_to', $to_version);

} elseif( isset( $_REQUEST["compare"] ) ) {
	$from_version = $_REQUEST["compare"];
	$from_page = $gContent->getHistory($from_version);
	$gBitSmarty->assign('compare', 'y');
	$gBitSmarty->assign_by_ref('diff_from', $gContent->parseData( $from_page["data"][0] ) );
	$gBitSmarty->assign_by_ref('diff_to', $gContent->parseData() );
	$gBitSmarty->assign_by_ref('version_from', $from_version);
} elseif (isset($_REQUEST["rollback"])) {
	if( $version = $gContent->getHistory( $_REQUEST["preview"] ) ) {
		$gBitSmarty->assign_by_ref('parsed', $gContent->parseData( $version["data"][0] ) );
		$gBitSmarty->assign_by_ref('version', $_REQUEST["preview"]);
	}
}

?>