<?php
/**
 * @version  $Header$
 * 
 * settings that are useful to know about at upload time
 * 
 * @package  liberty
 * @subpackage functions
 */
$postMax = str_replace( 'M', '', ini_get( 'post_max_size' ));
$uploadMax = str_replace( 'M', '', ini_get( 'upload_max_filesize' ) );

if( $postMax < $uploadMax ) {
	$uploadMax = $postMax;
}

/** 
 * calculate user quota
 */
if( $gBitSystem->isPackageActive( 'quota' ) ) {
	require_once( QUOTA_PKG_PATH.'calculate_quota_inc.php' );
}

$gBitSmarty->assignByRef( 'uploadMax', $uploadMax );
?>
