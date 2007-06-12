<?php
/**
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/plugins/filter.simplepurifier.php,v 1.1 2007/06/12 13:57:58 nickpalmer Exp $
 * @package  liberty
 * @subpackage plugins_filter
 */

/**
 * definitions ( guid character limit is 16 chars )
 */
define( 'PLUGIN_GUID_FILTERSIMPLEPURIFIER', 'filtersimplepure' );

global $gLibertySystem;

// Set of default acceptable HTML tags
define( 'DEFAULT_ACCEPTABLE_TAGS', '<a><br><b><blockquote><cite><code><div><dd><dl><dt><em><h1><h2><h3><h4><hr>'
		.'<i><it><img><li><ol><p><pre><span><strong><table><tbody><div><tr><td><th><u><ul>'
		.'<button><fieldset><form><label><input><option><select><textarea>' );

$pluginParams = array (
	// plugin title
	'title'                    => 'Simple HTML Purification',
	// help page on bitweaver org that explains this plugin
	'help_page'                => 'Simple Purifier',
	// brief description of the plugin
	'description'              => 'Uses some very nieve methods to try to protect against cross site scripting (XSS) attacks. It is known not to pass XSS smoke tests but is less invasive than HTMLPurifier.',
	// should this plugin be active or not when loaded for the first time
	'auto_activate'            => FALSE,
	// absolute path to this plugin
	'path'                     => LIBERTY_PKG_PATH.'plugins/filter.simplepurifier.php',
	// type of plugin
	'plugin_type'              => FILTER_PLUGIN,
	// url to page with options for this plugin
	'plugin_settings_url'      => LIBERTY_PKG_URL.'admin/filter_simplepurifier.php',

	// various filter functions and when they are called
	// called before the data is parsed
	'prefilter_function'       => 'simplepure_filter',
	// called after the data has been parsed
	//	'postfilter_function'      => 'simplepure_postfilter',
	// called before the data is parsed if there is a split
	'presplitfilter_function'  => 'simplepure_filter',
	// called after the data has been parsed if there is a split
	//	'postsplitfilter_function' => 'simplepure_filter',
	// called before the data is saved
	//	'prestore_function'		   => 'simplepure_filter',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_FILTERSIMPLEPURIFIER, $pluginParams );

function simplepure_filter( $pData, $pFilterHash ) {
	// This function is a menagerie of the techniques of the comments listed at
	// http://www.php.net/manual/en/function.strip-tags.php - spiderr
	global $gBitSystem, $gBitUser;
	
	// convert all HTML entites to catch people trying to sneak stuff by with things like &#123; etc..

	if( function_exists( 'html_entity_decode' ) ) {
		// quieten this down since it causes an error in PHP4
		// http://bugs.php.net/bug.php?id=25670
		$text = @html_entity_decode( $pData, ENT_COMPAT, 'UTF-8' );
	} else {
		$trans_tbl = get_html_translation_table(HTML_ENTITIES);
		$trans_tbl = array_flip($trans_tbl);
		$text = strtr($pData, $trans_tbl);
	}

	// strip_tags() appears to become nauseated at the site of a <!DOCTYPE> declaration
	$text = str_replace( '<!DOCTYPE', '<DOCTYPE', $text );

	// Strip all evil tags that remain
	// this comes out of gBitSystem->getConfig() set in Liberty Admin
	$acceptableTags = $gBitSystem->getConfig( 'approved_html_tags', DEFAULT_ACCEPTABLE_TAGS );

	// Destroy all script code "manually" - strip_tags will leave code inline as plain text
	if( !preg_match( '/\<script\>/', $acceptableTags ) ) {
		$text = preg_replace( "/(\<script)(.*?)(script\>)/si", '', $text );
	}
	
	$text = strip_tags( $text, $acceptableTags );
	$text = str_replace("<!--", "&lt;!--", $text);
	$text = preg_replace("/(\<)(.*?)(--\>)/mi", "".nl2br("\\2")."", $text);
	
	return( $text );
}

?>
