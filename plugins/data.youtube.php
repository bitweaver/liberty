<?php
/**
 * @version  $Revision: 1.2 $
 * @package  liberty
 * @subpackage plugins_data
 */
// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See copyright.txt for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Author:  xing
// +----------------------------------------------------------------------+
// $Id: data.youtube.php,v 1.2 2008/05/07 21:39:51 squareing Exp $

/**
 * definitions
 */

/******************
* Initialization *
******************/
global $gLibertySystem;
define( 'PLUGIN_GUID_DATAYOUTUBE', 'datayoutube' );
$pluginParams = array (
	'tag'           => 'YOUTUBE',
	'auto_activate' => FALSE,
	'requires_pair' => FALSE,
	'load_function' => 'data_youtube',
	'title'         => 'Youtube',
	'help_page'     => 'DataPluginYoutube',
	'description'   => tra( "This plugin allows you to simply and safely insert a YouTube video in a page." ),
	'help_function' => 'data_youtube_help',
	'syntax'        => "{youtube id=?}",
	'path'          => LIBERTY_PKG_PATH.'plugins/data.youtube.php',
	'plugin_type'   => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAYOUTUBE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAYOUTUBE );

/*****************
* Help Function *
*****************/
function data_youtube_help() {
	return 'no help written for {youtube} yet. You need to set id and can optionally set width and height of video.';
}

/****************
* Load Function *
****************/
function data_youtube( $pData, $pParams ) {
	extract( $pParams );
	$width   = ( !empty( $width )   ? $width   : "425" );
	$height  = ( !empty( $height )  ? $height  : "355" );
	$hl      = ( !empty( $lang )    ? $lang    : "en" );

	if( !empty( $id )) {
		//return '<!--~np~--><object width="'.$width.'" height="'.$height.'"><param name="movie" value="http://www.youtube.com/v/'.$id.'&hl='.$hl.'">';
		return '<!--~np~--><object width="'.$width.'" height="'.$height.'"><param name="movie" value="http://www.youtube.com/v/'.$id.'&hl='.$hl.'"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/'.$id.'&hl='.$hl.'" type="application/x-shockwave-flash" wmode="transparent" width="'.$width.'" height="'.$height.'"></embed></object><!--~/np~-->';
	} else {
		return tra( 'No ID given' );
	}
}
?>
