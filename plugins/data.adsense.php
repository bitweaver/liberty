<?php
	/******************
	* Initialization *
	******************/
	global $gLibertySystem;
	define( 'PLUGIN_GUID_ADSENSE', 'adsense' );
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
	$gLibertySystem->registerPlugin( PLUGIN_GUID_ADSENSE, $pluginParams );
	$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_ADSENSE );
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
