<?php
/**
 * @version  $Revision: 1.5 $
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
// $Id: data.adsense.php,v 1.5 2008/11/09 09:08:55 squareing Exp $

/**
 * definitions
 */

/******************
* Initialization *
******************/
global $gLibertySystem;
define( 'PLUGIN_GUID_DATAADSENSE', 'dataadsense' );
$pluginParams = array (
	'tag' => 'ADSENSE',
	'auto_activate' => FALSE,
	'requires_pair' => FALSE,
	'load_function' => 'data_adsense',
	'title' => 'Adsense',
	'help_page' => 'DataPluginAdsense',
	'description' => tra("This plugin adds Adsense Code to page."),
	'help_function' => 'data_adsense_help',
	'syntax' => "{ADSENSE}",
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAADSENSE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAADSENSE );

/*****************
* Help Function *
*****************/
function data_adsense_help() {
	return 'NO HELP WRITTEN FOR {ADSENSE} YET. You can set: client, width, height, format, type and channel.';
}

/****************
* Load Function *
****************/
function data_adsense( $pData, $pParams ) {
	extract( $pParams );
	$width   = ( !empty( $width )   ? $width   : "728" );
	$height  = ( !empty( $height )  ? $height  : "90" );
	$client  = ( !empty( $client )  ? $client  : "pub-xxxxxxxxxxxxxxxx" );
	$format  = ( !empty( $format )  ? $format  : "728x90_as" );
	$type    = ( !empty( $type )    ? $type    : "text_image" );
	$channel = ( !empty( $channel ) ? $channel : "" );

	return "<!--~np~--><script type=\"text/javascript\">/* <![CDATA[ */
		google_ad_width   = $width;
		google_ad_height  = $height;
		google_ad_client  = \"$client\";
		google_ad_format  = \"$format\";
		google_ad_type    = \"$type\";
		google_ad_channel = \"$channel\";
	/* ]]> */</script>
	<script type=\"text/javascript\" src=\"http://pagead2.googlesyndication.com/pagead/show_ads.js\"></script><!--~/np~-->";
}
?>
