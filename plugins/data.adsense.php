<?php
/**
 * @version  $Revision: 1.3 $
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
// $Id: data.adsense.php,v 1.3 2006/08/07 22:14:57 squareing Exp $

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
	'path' => LIBERTY_PKG_PATH.'plugins/data.adsense.php',
	'security' => 'registered',
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAADSENSE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAADSENSE );
/*****************
* Help Function *
*****************/
function data_adsense_help() {
	return 'NO HELP WRITTEN FOR {ADSENSE} YET';
}
/****************
* Load Function *
****************/
function data_adsense($data, $params) {
	return '<!--~np~--><script type="text/javascript"><!-- ' . "\n" . 'google_ad_client = "pub-xxxxxxxxxxxxxxxx";' . "\n" . 'google_ad_width = 728;' . "\n" . 'google_ad_height = 90;' . "\n" . 'google_ad_format = "728x90_as";' . "\n" . 'google_ad_type = "text_image";' . "\n" . 'google_ad_channel ="";' . "\n" . '//--></script>' . "\n" . '<script type="text/javascript"' . "\n" . ' src="http://pagead2.googlesyndication.com/pagead/show_ads.js">' . "\n" . '</script><!--~/np~-->';
}
?>
