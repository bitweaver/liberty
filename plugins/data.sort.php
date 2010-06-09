<?php
/**
 * @version  $Revision$
 * @package  liberty
 * @subpackage plugins_data
 */

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATASORT', 'datasort' );
global $gLibertySystem;
$pluginParams = array (
	'tag'           => 'SORT',
	'auto_activate' => FALSE,
	'requires_pair' => TRUE,
	'load_function' => 'data_sort',
	'title'         => 'Sort',
	'help_page'     => 'DataPluginSort',
	'description'   => tra( "This plugin will sort the lines within a {sort} block." ),
	'help_function' => 'data_sort_help',
	'syntax'        => "{sort sort= }".tra( "Lines to be sorted" )."{sort}",
	'plugin_type'   => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATASORT, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATASORT );

/**
 * Help Function
 */
function data_sort_help() {
	$help ='
		<table class="data help">
			<tr>
				<th>'.tra( 'Key' ).'</th>
				<th>'.tra( 'Value' ).'</th>
				<th>'.tra( 'Comments' ).'</th>
			</tr>
			<tr class="even">
				<td>'.'sort' .'</td>
				<td>'.tra( "key-words").'<br />'.tra("(optional)").'</td>
				<td>'.tra( 'Will sort the lines in the desired direction.  Choices are:' ).'<strong>asc</strong>, <strong>desc</strong>, <strong>reverse</strong>, <strong>shuffle</strong>'.tra( 'Default:' ).'<strong>asc</strong>'.'</td>
			</tr>
		</table>'.
		tra( "Example: " ).'{sort sort=shuffle}<br />Line 1<br />Line 2<br />Line 3<br />{sort}';
	return $help;
}

/**
 * Load Function
 */
function data_sort( $pData, $pParams, $pCommonObject, $pParseHash ) {
	$sort = ( !empty( $pParams['sort'] )) ? $pParams['sort'] : 'asc';
	$lines = explode( "\n", $pData );
	if( $sort == "asc" ) {
		sort( $lines );
	} elseif( $sort == "desc" ) {
		rsort( $lines );
	} elseif( $sort == "reverse" ) {
		$lines = array_reverse( $lines );
	} elseif( $sort == "shuffle" ) {
		srand(( float )microtime() * 1000000 );
		shuffle( $lines );
	}
	reset( $lines );
	if( is_array( $lines )) {
		$pData = implode( "\n", $lines );
	}

	$parseHash['content_id'] = $pParseHash['content_id'];
	$parseHash['user_id']    = $pParseHash['user_id'];
	$parseHash['no_cache']   = TRUE;
	$parseHash['data']       = trim( $pData );
	return $pCommonObject->parseData( $parseHash );
}
?>
