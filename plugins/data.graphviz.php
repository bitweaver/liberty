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
// | Author: StarRider starrrider@sourceforge.net
// +----------------------------------------------------------------------+
// $id: data.example.php,v 1.4.2.9 2005/07/14 09:03:36 starrider Exp $

/******************
 * Initialization *
 ******************/

define( 'PLUGIN_GUID_DATAGRAPHVIZ', 'graphviz' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'GRAPHVIZ',
	'auto_activate' => FALSE,
	'requires_pair' => TRUE,
	'load_function' => 'data_graphviz',
	'title' => 'GraphViz',
	'help_page' => 'DataPluginExample',
	'description' => tra("This plugin renders it's content as a graphviz image (dot or neato). It requies the Image_GraphViz pear plugin and graphviz to be installed: pear install Image_GraphViz"),
	'help_function' => 'data_graphviz_help',
	'syntax' => "{GRAPHVIZ}digraph  ... {/GRAPHVIZ}",
	'path' => LIBERTY_PKG_PATH.'plugins/data.graphviz.php',
	'security' => 'registered',
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAGRAPHVIZ, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAGRAPHVIZ );
/*****************
 * Help Function *
 *****************/
function data_graphviz_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>x1</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Specifies something / probably to be displayed.")
					.'<br />' . tra( "The Default = <strong>Sorry About That</strong>")
				.'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>XXX</td>'
				.'<td>' . tra( "number") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Specifies something / probably to be displayed.")
					.'<br />' . tra( "The Default =") . ' <strong>3</strong> ' . tra( "Which means - What")
				.'</td>'
			.'</tr>'
 		.'</table>'
		. tra("Example: ") . "{EXAM x1=' ' x2=5 }<br />"
		. tra("This will display");
	return $help;
}
/****************
* Load Function *
 ****************/
function data_graphviz($data, $params) {
	$data = trim($data);
	$data = html_entity_decode( $data );
	$storageurl = STORAGE_PKG_URL.'GraphViz/';
	$storagepath = STORAGE_PKG_PATH.'GraphViz/';
	$temppath = TEMP_PKG_PATH.'GraphViz/';
			
	if( !is_dir( $temppath ) ) {
		mkdir_p( $temppath );
	}
	if( !is_dir( $storagepath ) ) {
		mkdir_p( $storagepath );
	}
	
	$file = md5( $data );
	$dotFile = $temppath . $file . '.dot';
	$pngFile = $storagepath . $file . '.png';
	$pngURL = $storageurl . $file . '.png';
	
	if( !file_exists( $pngFile ) ) {
		if( @include_once('PEAR.php') ) {
			if(@include_once( 'Image/GraphViz.php' ) ) {
				$graph = new Image_GraphViz;
				$error = '<div class=error>'.tra("Unable to write temporary file. Please check your server configuration.").'</div>';	
				if (!$fp = fopen($dotFile, 'w')) {
					return $error;
				}
				if (fwrite($fp, $data)===false) {
					return $error;
				}
				fclose($fp);
				$graph->renderDotFile( $dotFile, $pngFile, 'png' );
				// No need to keep this lying around
				unlink($dotFile);
				
				// If it still isn't there....
				if (!file_exists($pngFile)) {
					return '<div class=error>'.tra("Unable to generate graphviz image file. Please check your data.").'</div>';
				}
			}
			else {
				return "<div class=error>".tra("The Image_Graphviz pear plugin is not installed. Install with `pear install Image_Graphviz`.")."</div>";
			}
		}
		else {		   
			return "<div class=error>".tra("PEAR and the Image_Graphviz pear plugin are not installed.")."</div>";
		}
	}
	return "<img src=\"$pngURL\"/> ";

}
?>
