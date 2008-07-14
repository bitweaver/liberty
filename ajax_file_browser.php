<?php
require_once( '../bit_setup_inc.php' );

if( !empty( $_REQUEST['ajax_path_conf'] )) {
	$fileList = ajax_dir_list( $gBitSystem->getConfig( $_REQUEST['ajax_path_conf'] ), ( !empty( $_REQUEST['relpath'] ) ? $_REQUEST['relpath']."/" : "" ));
	$gBitSmarty->assign( 'fileList', $fileList );
}
$gBitThemes->loadAjax( 'mochikit', array( 'DOM.js', 'Async.js' ));
$gBitThemes->loadJavascript( LIBERTY_PKG_PATH."scripts/FileBrowser.js" );

if( $gBitThemes->isAjaxRequest() ) {
	$gBitSmarty->display( 'bitpackage:liberty/ajax_file_browser.tpl' );
}

function ajax_dir_list( $pDir, $pRelFile ) {
	global $gBitSystem;
	$temp = '';
	$ret = $files = array();

	if( $handle = opendir( $pDir.$pRelFile )) {
		while( FALSE !== ( $file = readdir( $handle ))) {
			array_push( $files, $file );
		}
		sort( $files );
		foreach( $files as $i ) {
			$file = $pDir.$pRelFile.$i;
			$relFile = $pRelFile.$i;
			if( !preg_match( "#^\.#",$i ) && is_readable( $file )) {
				$info = array(
					'name'    => $i,
					'relpath' => $relFile,
					'indent'  => ( count( explode( '/', $relFile )) * 10 ),
					'size'    => filesize( $file ),
					'mtime'   => filemtime( $file ),
				);
				if( is_dir( $file )) {
					$ret['dir'][$i] = $info;
				} elseif( !preg_match( EVIL_EXTENSION_PATTERN, $file )) {
					$ret['file'][$i] = $info;
				}
			}
		}
		closedir( $handle );
	}

	if( empty( $ret )) {
		$ret['file'][] = array(
			'indent'  => ( count( explode( '/', $pRelFile )) * 10 ),
		);
	}

	return $ret;
}
?>
