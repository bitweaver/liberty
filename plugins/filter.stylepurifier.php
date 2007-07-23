<?php
/**
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/plugins/filter.stylepurifier.php,v 1.5 2007/07/23 20:17:34 squareing Exp $
 * @package  liberty
 * @subpackage plugins_filter
 */

/**
 * definitions ( guid character limit is 16 chars )
 */
define( 'PLUGIN_GUID_FILTERSTYLEPURIFIER', 'filterstylepure' );

global $gLibertySystem;

$pluginParams = array (
	// plugin title
	'title'             => 'Style Purification',
	// help page on bitweaver org that explains this plugin
	'help_page'         => 'Style Purifier',
	// brief description of the plugin
	'description'       => 'Strips out both inline and attribute style for users who don\'t have p_liberty_edit_html_style.',
	// should this plugin be active or not when loaded for the first time
	'auto_activate'     => TRUE,
	// type of plugin
	'plugin_type'       => FILTER_PLUGIN,
	// filter hooks
	'prestore_function' => 'stylepure_filter',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_FILTERSTYLEPURIFIER, $pluginParams );

/*
 * Removes all style both inline and attributes unless the user
 * has permission to edit styles.
 */
function stylepure_filter( &$pData, &$pFilterHash ) {
	global $gBitUser;

	// strip_tags has doesn't recognize that css within the style tags are not document text. To fix this do something similar to the following:
	if( !$gBitUser->hasPermission( 'p_liberty_edit_html_style' )) {
		$pattern = array(
            "!<style[^>]*>.*</style>!siU",             // <style>...</style>
            '![\s\n]*(style|class)\s*=\s*"[^">]*"?!i', // style="..." | class="..."
            "![\s\n]*(style|class)\s*=\s*'[^'>]*'?!i", // style='...' | class='...'
            "![\s\n]*(style|class)\s*=\s*[^\s>]*!i",   // style=...   | class=...
		);
		$pData = preg_replace( $pattern, '', $pData );
	}
}

?>
