<?php
/**
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/plugins/filter.htmlpurifier.php,v 1.14 2007/12/12 14:40:24 wjames5 Exp $
 * @package  liberty
 * @subpackage plugins_filter
 */

/**
 * definitions ( guid character limit is 16 chars )
 */
define( 'PLUGIN_GUID_FILTERHTMLPURIFIER', 'filterhtmlpure' );

global $gLibertySystem;

$pluginParams = array (
	// plugin title
	'title'                    => 'HTMLPurifier',
	// help page on bitweaver org that explains this plugin
	'help_page'                => 'HTMLPurifier',
	// brief description of the plugin
	'description'              => 'Uses <a href="http://htmlpurifier.org">HTMLPurifier</a> to cleanup the HTML submitted to your site and ensure that it is standards compliant and does not contain anything malicious. It is also used to ensure that the various places that input is split for previews does not cause bad markup to break the page. This filter is <strong>highly</strong> recommended if you are allowing HTML but is still good for sites that are not using thse formats for the ability to cleanup markup which has been split for preview properly though this may disable certain plugins that insert non standards compliant code.',
	// should this plugin be active or not when loaded for the first time
	'auto_activate'            => FALSE,
	// absolute path to this plugin
	'path'                     => LIBERTY_PKG_PATH.'plugins/filter.htmlpurifier.php',
	// type of plugin
	'plugin_type'              => FILTER_PLUGIN,
	// url to page with options for this plugin
	'plugin_settings_url'      => LIBERTY_PKG_URL.'admin/filter_htmlpurifier.php',

	// various filter functions and when they are called
	// called before the data is parsed
	//	'pre_function'       => 'htmlpure_filter',
	// called after the data has been parsed
	'preparse_function'  => 'htmlpure_filter',
	// called before the data is parsed if there is a split
	//	'presplit_function'  => 'htmlpure_filter',
	// called after the data has been parsed if there is a split
	'postsplit_function' => 'htmlpure_filter',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_FILTERHTMLPURIFIER, $pluginParams );

function htmlpure_filter( &$pString, &$pFilterHash ) {
	global $gHtmlPurifier, $gBitSystem;
	
	if (!isset($gHtmlPurifier)) {
		$blacklistedTags = $gBitSystem->
			getConfig('blacklisted_html_tags', '');

		$pear_version = false;
		if (@include_once("PEAR.php")) {
			if (@include_once("HTMLPurifier.php")) {

				$config = HTMLPurifier_Config::createDefault();

				// Set the cache path
				$config->set('Cache', 'SerializerPath', STORAGE_PKG_PATH );

				if ($gBitSystem->getConfig('htmlpure_escape_bad', 'y') == 'y') {
					$config->set('Core', 'EscapeInvalidTags', true);
					$config->set('Core', 'EscapeInvalidChildren', true);
				}
				if ($gBitSystem->getConfig('htmlpure_disable_extern') == 'y') {
					$config->set('URI', 'DisableExternal', true);
				}
				if ($gBitSystem->getConfig('htmlpure_disable_extern_res', 'y') == 'y') {
					$config->set('URI', 'DisableExternalResources', true);
				}
				if ($gBitSystem->getConfig('htmlpure_disable_res') == 'y') {
					$config->set('URI', 'DisableResources', true);
				}
				if ($gBitSystem->getConfig('htmlpure_disable_uri') == 'y') {
					$config->set('URI', 'Disable', true);
				}
				if ($gBitSystem->getConfig('htmlpure_use_redirect') == 'y') {
					$config->set('URI', 'Munge', LIBERTY_PKG_URL.'redirect.php?q=%s');
				}
				if ($gBitSystem->getConfig('htmlpure_strict_html', 'y') == 'y') {
					$config->set('HTML', 'Strict', true);
				}
				if ($gBitSystem->getConfig('htmlpure_xhtml', 'n') == 'n') {
					$config->set('Core', 'XHTML', true);
				}

				// Set that we are using a div to wrap things.
				$config->set('HTML', 'BlockWrapper', 'div');

				$def =& $config->getHTMLDefinition();
				// HTMLPurifier doesn't have a blacklist feature. Duh guys!
				// Note that this has to come last since the other configs
				// may tweak the def.
				foreach (explode(',',$blacklistedTags) as $tag) {
					unset($def->info[$tag]);
				}
		
				// As suggested here:  http://www.bitweaver.org/forums/index.php?t=8554
				$gHtmlPurifier = new HTMLPurifier($config);

				// TODO: devise a way to parse plugins dir
				// and check for the right property here
				// so new plugins are just drop in place.
				if ($gBitSystem->isFeatureActive('htmlpure_allow_youtube')) {
					require_once(UTIL_PKG_PATH.'htmlpure/Filter/YouTube.php');
					$gHtmlPurifier->addFilter(new HTMLPurifier_Filter_YouTube());
				}
			}
		}
	}

	// Did we manage to create one?
	 if (isset($gHtmlPurifier)) { 
		/* Clean up the paragraphs a bit */
		//		$start = $pData;
		$pString = htmlpure_cleanupPeeTags($pString);
		//		$pee = $pString;
		$gHtmlPurifier->purify( html_entity_decode( $pString ) );

		/*
		echo "<br/><hr/><br/>".$start;
		include_once( 'Text/Diff.php' );
		include_once( 'Text/Diff/Renderer/inline.php' );
		$diff = &new Text_Diff(explode("\n", $start), explode("\n",$pee));
		$renderer = &new Text_Diff_Renderer_inline();
		echo "<br/><hr/><br/>". $renderer->render($diff);
	
		echo "<br/><hr/><br/>".$pString;
		include_once( 'Text/Diff.php' );
		include_once( 'Text/Diff/Renderer/inline.php' );
		$diff = &new Text_Diff(explode("\n", $pee), explode("\n",$pString));
		$renderer = &new Text_Diff_Renderer_inline();
		echo "<br/><hr/><br/>". $renderer->render($diff);
		*/
	} else {
		bit_log_error("HTMLPurifier not installed. Install with: pear channel-discover htmlpurifier.org; pear install hp/HTMLPurifier;");
	}

	return $pString;
}

function htmlpure_cleanupPeeTags( $pee ) {
	
	// Convert us some form feeds for better cross platform support
	$pee = str_replace(array("\r\n", "\r"), "\n", $pee);
	
	// Strip out lots of duplicate newlines now
	$pee = preg_replace("#\n\n+#", "\n\n", $pee);
	
	// Pee in block quotes - Removed as we now have purifier insert a div instead. See above.
	//	$pee = preg_replace('#<blockquote(.*?(?:[^>]*))>(.*?)</blockquote>#s', '<blockquote$1><p>$2</p></blockquote>', $pee);
	
	// Strip empty pee
	$pee = preg_replace('#<p>\s*</p>#', '', $pee);
		
	// Unpee pre blocks
	$pee = preg_replace('#(<pre.*?(?:[^>]*)>)(.*?)</pre>#sie',
						" '$1' . preg_replace('#<br[\s/]*(?:[^>]*)/>#', '"."\n"."',
						preg_replace('#<p[\s]*(?:[^>]*)>#', '"."\n"."',
						preg_replace('#</p[\s]*(?:[^>]*)>#', '', '$2'))). '</pre>'", $pee);

	// Fixup align divs so we can keep them.
	$pee = preg_replace('#<div(.*?)align="(.*?)"(.*?)>#', '<div$1style="text-align:$2;"$3>', $pee);
	
	return $pee;
}

?>
