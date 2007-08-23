<?php
/**
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/plugins/Attic/filter.stencil.php,v 1.1 2007/08/23 15:18:50 squareing Exp $
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
	'presplit_function'  => 'stencil_filter',
	'preparse_function'  => 'stencil_filter',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_FILTERSTENCIL, $pluginParams );

function stencil_filter( &$pData, &$pFilterHash ) {
	global $gBitSystem, $gBitSmarty;
	if( $gBitSystem->isPackageActive( 'stencil' )) {
		require_once( STENCIL_PKG_PATH.'BitStencil.php' );
		$pData = preg_replace_callback( "/\{\{\/?([^|]+)([^\}]*)\}\}/", 'stencil_parse_data', $pData );
	}
}

function stencil_parse_data( $matches ) {
	static $sStencilObjects = array();
	$output = $matches[0];
	if( !empty( $matches[2] ) ) {
		$output = '';
		$templateVars = array();
		$templateName = $matches[1];
		if( empty( $sStencilObjects[$templateName] ) ) {
			if( $stencilContentId = BitStencil::findByTitle( $templateName, NULL, BITSTENCIL_CONTENT_TYPE_GUID ) ) {
				$sStencilObjects[$templateName] = new BitStencil( NULL, $stencilContentId );
				if( $sStencilObjects[$templateName]->load() ) {
					$output = $sStencilObjects[$templateName]->getField( 'data' );
				}
			}
		}

		if( $lines = explode( '|', $matches[2] ) ) {
			foreach( $lines as $line ) {
				if( strpos( $line, '=' ) ) {
					list( $name, $value ) = split( '=', trim( $line ) );
					$templateVars[$name] = $value;
					$output = preg_replace( '/\{\{\{'.$name.'\}\}\}/', $value, $output );
				}
			}
		}
		// now need to do the substitution
	}
	return( $output );
}
?>
