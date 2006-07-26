<?php
/**
 * base package include
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.7 $
 * @package  liberty
 * @subpackage functions
 */

$registerHash = array(
	'package_name' => 'liberty',
	'package_path' => dirname( __FILE__ ).'/',
    'required_package'=> TRUE,
);
$gBitSystem->registerPackage( $registerHash );

require_once( LIBERTY_PKG_PATH.'LibertySystem.php' );
?>
