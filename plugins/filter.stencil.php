<?php
/**
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/plugins/Attic/filter.stencil.php,v 1.5 2008/02/29 03:59:52 spiderr Exp $
 * @package  liberty
 * @subpackage plugins_filter
 */

/**
 * definitions ( guid character limit is 16 chars )
 */
define( 'PLUGIN_GUID_FILTERSTENCIL', 'filterstencil' );

global $gLibertySystem, $gBitSystem;

$pluginParams = array(
	'title'              => 'Stencil',
	'description'        => 'If you are using the stencil package, you need to enable this filter.',
	'auto_activate'      => TRUE,
	'path'               => LIBERTY_PKG_PATH.'plugins/filter.stencil.php',
	'plugin_type'        => FILTER_PLUGIN,

	// filter functions
	'preplugin_function' => 'stencil_filter',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_FILTERSTENCIL, $pluginParams );

function stencil_filter( &$pData, &$pFilterHash ) {
	global $gBitSystem, $gBitSmarty;
	if( $gBitSystem->isPackageActive( 'stencil' )) {
		require_once( STENCIL_PKG_PATH.'BitStencil.php' );
		$pData = preg_replace_callback( "/\{\{\/?([^|]+)(.*?)\}\}/s", 'stencil_parse_data', $pData );
	}
}

function stencil_parse_data( $matches ) {
	static $sStencilObjects = array();
	$output = $matches[0];
	if( !empty( $matches[2] )) {
		$output = '';
		$templateName = $matches[1];
		if( empty( $sStencilObjects[$templateName] ) ) {
			if( $stencilContentId = BitStencil::findByTitle( $templateName, NULL, BITSTENCIL_CONTENT_TYPE_GUID ) ) {
				$sStencilObjects[$templateName] = new BitStencil( NULL, $stencilContentId );
				if( $sStencilObjects[$templateName]->load() ) {
					$output = $sStencilObjects[$templateName]->getField( 'data' );
				}
			}
		} else {
			$output = $sStencilObjects[$templateName]->getField( 'data' );
		}

		if( $lines = explode( '|', $matches[2] )) {
			foreach( $lines as $line ) {
				if( strpos( $line, '=' ) ) {
					list( $name, $value ) = split( '=', trim( $line ), 2 );
					// if the value is empty, we remove all the conditional stuff surrounding it
					if( empty( $value ) && !is_numeric( $value )) {
						$output = preg_replace( "!\{{3}$name>.*?<$name\}{3}!s", "", $output );
					} else {
						$pattern = array(
							"!\{{3}$name\}{3}!",
							"!\{{3}$name>!",
							"!<$name\}{3}!",
						);
						$replace = array( $value, "", "" );
						$output = preg_replace( $pattern, $replace, $output );
					}
				}
			}
			// any remaining {{{vars}}} will be removed
			$pattern = array(
				"!\{{3}\w+?>.*?<\w+\}{3}!s",
				"!\{{3}\w+?\}{3}!",
			);
			$output = preg_replace( $pattern, "", $output );
		}
	}
	return( $output );
}
?>
