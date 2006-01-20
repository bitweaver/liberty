<?php
/**
 * @version  $Revision: 1.1.1.1.2.7 $
 * @package  liberty
 * @subpackage plugins_format
 */
global $gLibertySystem;

/**
 * definitions
 */
define( 'PLUGIN_GUID_BITHTML', 'bithtml' );

$pluginParams = array ( 'store_function' => 'bithtml_save_data',
						'load_function' => 'bithtml_parse_data',
						'verify_function' => 'bithtml_verify_data',
						'description' => 'HTML Syntax Format Parser',
						'edit_label' => 'HTML',
						'edit_field' => '<input type="radio" name="format_guid" value="'.PLUGIN_GUID_BITHTML.'"',
						'plugin_type' => FORMAT_PLUGIN
					  );

$gLibertySystem->registerPlugin( PLUGIN_GUID_BITHTML, $pluginParams );

function bithtml_verify_data( &$pParamHash ) {
	$errorMsg = NULL;
	$pParamHash['content_store']['data'] = purge_html( $pParamHash['edit'] );
	return $errorMsg;
}

// This function is a menagerie of the techniques of the comments listed at
// http://www.php.net/manual/en/function.strip-tags.php - spiderr
function purge_html( $pText ) {
	global $gBitSystem, $gBitUser;

	// convert all HTML entites to catch people trying to sneak stuff by with things like &#123; etc..
	if( function_exists( 'html_entity_decode' ) ) {
        // quieten this down since it causes an error in PHP4
        // http://bugs.php.net/bug.php?id=25670
		$text = @html_entity_decode( $pText, ENT_COMPAT, 'UTF-8' );
	} else {
		$trans_tbl = get_html_translation_table(HTML_ENTITIES);
		$trans_tbl = array_flip($trans_tbl);
		$text = strtr($pText, $trans_tbl);
	}

	// strip_tags() appears to become nauseated at the site of a <!DOCTYPE> declaration
	$text = str_replace( '<!DOCTYPE', '<DOCTYPE', $text );

	// Yank style - both tag and inline attributes
	// strip_tags has doesn't recognize that css within the style tags are not document text. To fix this do something similar to the following:
	if( !$gBitUser->hasPermission( 'bit_p_edit_html_style' ) ) {
		$text = preg_replace( "/<style[^>]*>.*<\/style>/siU", '', $text );
	}
	$text = stripslashes($text);
	if( !$gBitUser->hasPermission( 'bit_p_edit_html_style' ) ) {
		$text = preg_replace( "/ (style|class)=[\"]?([^\"]*)[\"]?/i", '', $text);
	}

	// Strip all evil tags that remain
	// this comes out of gBitSystem->getPreference() set in Liberty Admin
	$acceptableTags = $gBitSystem->getPreference( 'approved_html_tags', DEFAULT_ACCEPTABLE_TAGS );

	// Destroy all script code "manually" - strip_tags will leave code inline as plain text
	if( !preg_match( '/\<script\>/', $acceptableTags ) ) {
		$text = preg_replace( "/(\<script)(.*?)(script\>)/si", '', $text );
	}

	$text = strip_tags( $text, $acceptableTags );
	$text = str_replace("<!--", "&lt;!--", $text);
	$text = preg_replace("/(\<)(.*?)(--\>)/mi", "".nl2br("\\2")."", $text);

	return( $text );
}

function bithtml_save_data( &$pParamHash ) {
	static $parser;
	if( empty( $parser ) ) {
		$parser = new TikiWikiParser();
	}
	if( $pParamHash['edit'] ) {
		$parser->storeLinks( $pParamHash );
	}
}

function bithtml_parse_data( &$pData, &$pCommonObject ) {
	global $gLibertySystem;
	// eventually we should strip tags, maybe tikilink, or other things.
	parse_data_plugins( $pData, $foo, $bar, $empty );
	// this function is called manually, since it processes the HTML code
	if( preg_match( "/\{maketoc.*?\}/i", $pData ) && @$gLibertySystem->mPlugins['datamaketoc']['is_active'] == 'y' ) {
		$pData = data_maketoc( $pData );
	}
	return $pData;
}

?>
