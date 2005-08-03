<?php
/**
 * base package include
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.1.1.1.2.3 $
 * @package  liberty
 * @subpackage functions
 */

global $gBitSystem, $gBitUser, $gBitSmarty;

$gBitSystem->registerPackage( 'liberty', dirname( __FILE__).'/' );

require_once( LIBERTY_PKG_PATH.'LibertySystem.php' );
?>
