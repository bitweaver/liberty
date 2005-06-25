<?php
// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See copyright.txt for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Author: StarRider <starrrider@sbcglobal.net>
// +----------------------------------------------------------------------+
//
// $Id: data.tp.php,v 1.1.1.1.2.2 2005/06/25 09:29:25 squareing Exp $
// Initialization
global $gBitSystem;

define( 'PLUGIN_GUID_DATATP', 'datatp' );

global $gLibertySystem;
$pluginParams = array ( 'tag' => 'TP',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'datatp',
						'title' => 'TestParms',
						'description' => tra("This plugin will display the parameters passed to it."),
						'help_function' => 'datatp_help',
						'syntax' => "~np~{TP(p1= ,p2= ,p3= )}~/np~",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATATP, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATATP );

function datatp_help() {
	$back = tra("^__Parameter Syntax:__ ") . "~np~{TP(Key=value)}~/np~\n";
	$back.= tra("||__::key::__ | __::value::__ | __::Comments::__\n");
	$back.= "::p1::" . tra(" | ::Anything:: | Any parameter. \n");
	$back.= "::p2::" . tra(" | ::Anything:: | Any parameter. \n");
	$back.= "::p3::" . tra(" | ::Anything:: | Any parameter. \n");
	$back.= tra("^__Example:__ ") . "~np~{TP(p1=AAAA, p2=BBBB, p3=CCCC)}~/np~^";
	return $back;
}

function datatp($data, $params) {
	$p1='';
	$p2='';
	$p3='';
	extract ($params);
	$ret = "))TestParms Reporting:<br/> P1=" . $p1 . "<br/>   P2=" . $p2 . "<br/>   P3=" . $p3 . "\n((";
	return $ret;
}
?>
