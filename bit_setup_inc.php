<?php
/**
 * base package include
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.5 $
 * @package  liberty
 * @subpackage functions
 */

global $gBitSystem, $gBitUser, $gBitSmarty;

$registerHash = array(
	'package_name' => 'liberty',
	'package_path' => dirname( __FILE__ ).'/',
);
$gBitSystem->registerPackage( $registerHash );

require_once( LIBERTY_PKG_PATH.'LibertySystem.php' );
?>
