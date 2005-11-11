<?php
/**
 * @version  $Revision: 1.1.2.5 $
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
// | Author (TikiWiki): Unknown
// | Reworked & Undoubtedly Screwed-Up for (Bitweaver) 
// | by: wolff_borg <wolff_borg@yahoo.com.au>
// | Reworked from: wikiplugin_wikigraph.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.wikigraph.php,v 1.1.2.5 2005/11/11 22:04:09 mej Exp $
/**
 * definitions
 */
global $gBitSystem;
if ($gBitSystem->isPackageActive( 'wiki' ) ) { // Do not include this Plugin if the Package is not active
define( 'PLUGIN_GUID_DATAWIKIGRAPH', 'datawikigraph' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'WIKIGRAPH',
						'auto_activate' => FALSE,
						'requires_pair' => TRUE,
						'load_function' => 'data_wikigraph',
						'title' => 'WikiGraph',
						'help_page' => 'DataPluginWikiGraph',
						'description' => tra("Inserts a graph for visual navigation. The graph shows the page and every page that can be reached from that page."),
						'help_function' => 'data_wikigraph_help',
						'syntax' => "{WIKIGRAPH(level=> ,title=> ,nodesep=> ,rankdir=> ,bgcolor=> ,size=> ,fontsize=> ,fontname=> ,shap=> ,nodestyle=> ,nodecolor=> ,nodefillcolor=> ,nodewidth=> ,nodeheight=> ,edgecolor=> ,edgestyle=> )}".tra("Wiki page name")."{WIKIGRAPH}",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAWIKIGRAPH, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAWIKIGRAPH );

function data_wikigraph_help() {
	$back = tra("^__Parameter Syntax:__ ") . "~np~{WIKIGRAPH" . tra("(key=>value)}~/np~\n");
	$back.= tra("Inserts a graph for visual navigation. The graph shows the page and every page that can be reached from that page. Each node in the graph can be clicked to navigate to the selected page. Unless a PageName is included between the  ") . "__~np~{WIKIGRAPH}~/np~__" . tra(" blocks, the graph will be created for the Current Page. __Note:__ This plugin requires the installation of ))GraphViz(( on the server.");
	$back.= tra("||__::key::__ | __::value::__ | __::Comments::__\n");
	$back.= "::level::" . tra(" | ::number:: | the number of levels that will be followed from the starting page. __Optional__ - can be omitted - the __Default = 0__ so only the links from the page will be displayed.\n");
	$back.= "::title::" . tra(" | ::string:: |  the title of the graph. __Optional__ - can be omitted - the __Default = ~034~Wiki-Graph~034~__.\n");
	$back.= "::nodesep::" . tra(" | ::inches:: | the minimum distance between two nodes at the same level.Use the format __1.2__ in inches. __Optional__ - can be omitted - the __Default = .1__.\n");
	$back.= "::rankdir::" . tra(" | ::string:: | direction of graph layout. Can be Left to Right ") . "__LR__ or __RL__" . tra(" Right to Left. Vertical graphs can also be made using Top to Bottom ") . "__TB__ or __BT__" . tra(" Bottom to Top. __Optional__ - can be omitted - the __Default = ") . "TB__.\n";
	$back.= "::bgcolor::" . tra(" | ::colorname or hex color:: | specifies the background color for the graph. HTML colors (#RRGGBB) can be used if preceeded by the character #. __Optional__ - can be omitted - the __Default = ~034~transparent~034~__.\n");
	$back.= "::size::" . tra(" | ::inches:: | the width and height of the graph. Use the format __5,3__ in inches. If the graph is larger that the size it will be scaled down to fit. __Optional__ - can be omitted - the __Default = Unlimited__.\n");
	$back.= "::fontsize::" . tra(" | ::points:: |  the font size in points - used in all text. __Optional__ - can be omitted - the __Default = 9__.\n");
	$back.= "::fontname::" . tra(" | ::font name:: |  the name of the font used for the labels. It is better to use a font that is generally available, such as Times-Roman, Helvetica or Courier. __Optional__ - can be omitted - the __Default =  Helvetica__.\n");
	$back.= "::shap::" . tra(" | ::value:: |  the shape of the nodes. Values can be") . "__a1 / box / circle / diamond / doublecircle / doubleoctagon / egg / ellipse / hexagon / house / invhouse / invtrapezium / invtriangle / Mcircle / Mdiamond / Msquare / octagon / parallelogram / pentagon / point / polygon / rect / rectangle / septagon / trapezium / triangle / tripleoctagon__." . tra(" __Optional__ - can be omitted - the __Default = ") . "box__.\n";
	$back.= "::nodestyle::" . tra(" | ::value:: | the style used for creating the nodes. Values are: ") . "__dashed / dotted / solid / invis / bold / filled / diagonals / rounded__." . tra(" __Optional__ - can be omitted - the __Default = ") . "filled__.\n";
	$back.= "::nodecolor::" . tra(" | ::colorname or hex-color:: | basic color for all graphics. HTML colors (#RRGGBB) can be used if preceeded by the character #. __Optional__ - can be omitted - the __Default = #aeaeae__ (a light gray).\n");
	$back.= "::nodefillcolor::" . tra(" | ::colorname or hex-color:: | specifies the background color for the nodes. HTML colors (#RRGGBB) can be used if preceeded by the character #. __Optional__ - can be omitted - the __Default = #FFFFFF__ (White).\n");
	$back.= "::nodewidth::" . tra(" | ::inches:: | width of the nodes. This is the initial, minimum width of a node. Use the format __1.2__ in inches. __Optional__ - can be omitted - the __Default = .1__.\n");
	$back.= "::nodeheight::" . tra(" | ::inches:: | height of the nodes. This is the initial, minimum height of a node. Use the format __1.2__ in inches. __Optional__ - can be omitted - the __Default = .1__.\n");
	$back.= "::edgecolor::" . tra(" | ::colorname or hex-color:: | specifies the color of the links. HTML colors (#RRGGBB) can be used if preceeded by the character #. __Optional__ - can be omitted - the __Default = #999999__ (a darker gray).\n");
	$back.= "::edgestyle::" . tra(" | ::value:: | the shape of the arrow that points at the link. Values are: ") . "__normal / inv / dot / invdot / odot / invodot / none / tree / empty / invempty / diamond / ediamond / odiamond / crow / box / obox / open / halfopen__." . tra(" __Optional__ - can be omitted - the __Default = ") . "normal__.||^"; // This one may not be correct - this had a default of "solid" but the ArrowTypes given at http://www.research.att.com/~erg/graphviz/info/attrs.html#k:arrowType specify the default as "normal"
	$back.= tra("^__Example:__ ") . "~np~{WIKIGRAPH(level=>0)}HomePage{WIKIGRAPH}~/np~^";
	$back.= tra("^__Note 1:__ Plugin's are __case sensitive__. The Name of the plugin __MUST__ be UPPERCASE. The Key(s) are __always__ lowercase. Some Values are mixed-case but most require lowercase. When in doubt - look at the Example.\n");
	$back.= tra("__Note 2:__ A listing of ColorNames can be found at ") . "<a class='wiki' target=" . '"_blank"' . " href='http://www.tikipro.org/wiki/index.php?page=Browser+ColorNames/'>" . tra("TikiPro</a>^");
	$back.= tra("__Note 3:__ One useful place for obtaining HTML colors is ") . "<a class='wiki' target=" . '"_blank"' . " href='http://www.pagetutor.com/pagetutor/makapage/picker/'>" . tra("The Color Picker II</a>^");
	return $back;
}

include_once( WIKI_PKG_PATH.'BitPage.php');
include_once( UTIL_PKG_PATH.'GraphViz.php' );

function data_wikigraph($data, $params) {
	global $gContent, $wikilib;
	$add = "";
	extract ($params, EXTR_SKIP);
  if(!isset($level)) $level = 0;
	if(!isset($title)) $title = "Wiki-Graph";
	if(isset($nodesep)) $add.="&amp;nodesep=$nodesep";
	if(isset($rankdir)) $add.="&amp;rankdir=$rankdir";
	if(isset($size)) $add.="&amp;size=$size";
	if(isset($bgcolor)) $add.="&amp;bgcolor=$bgcolor";
	if(isset($fontsize)) $add.="&amp;fontsize=$fontsize";
	if(isset($fontname)) $add.="&amp;fontname=$fontname";
	if(isset($shape)) $add.="&amp;shape=$shape";
	if(isset($nodestyle)) $add.="&amp;nodestyle=$nodestyle";
	if(isset($nodecolor)) $add.="&amp;nodecolor=$nodecolor";
	if(isset($nodefillcolor)) $add.="&amp;nodefillcolor=$nodefillcolor";
	if(isset($nodewidth)) $add.="&amp;nodewidth=$nodewidth";
	if(isset($nodeheight)) $add.="&amp;nodeheight=$nodeheight";
	if(isset($edgecolor)) $add.="&amp;edgecolor=$edgecolor";
	if(isset($edgestyle)) $add.="&amp;edgestyle=$edgestyle";
  if(empty($data)) $data=$gContent->mInfo['title'];
  $mapname=md5(uniqid("."));
  $ret='';
$garg = array(
  'att' => array(
    'level' => $level,
    'nodesep' => isset($nodesep) ? $nodesep : ".1",
    'rankdir' => isset($rankdir) ? $rankdir : "LR",
    'bgcolor' => isset($bgcolor) ? $bgcolor : "transparent",
    'size' => isset($size) ? $size : ""
	),
	'node' => array(
    'fontsize' => isset($fontsize) ? $fontsize : "9",
    'fontname' => isset($fontname) ? $fontname : "Helvetica",
    'shape' => isset($shape) ? $shape : "box",
    'style' => isset($nodestyle) ? $nodestyle : "filled",
    'color' => isset($nodecolor) ? $nodecolor : "#aeaeae",
    'fillcolor' => isset($nodefillcolor) ? $nodefillcolor : "#FFFFFF",
    'width' => isset($nodewidth) ? $nodewidth : ".1",
    'height' => isset($nodeheight) ? $nodeheight : ".1"
  ),
  'edge' => array(
    'color' => isset($edgecolor) ? $edgecolor : "#999999",
    'style' => isset($edgestyle) ? $edgestyle : "solid"
));
	$ret .= "<div align='center'><img border='0' src=\"".WIKI_PKG_URL."wiki_graph.php?page=".urlencode($data)."&amp;level=$level$add\" alt='graph' usemap='#$mapname' />";
	$mapdata = $wikilib->get_graph_map($data, $level, $garg);
	$mapdata = preg_replace("/\n|\r/", '', $mapdata);
	$ret .= "<map name='$mapname'>$mapdata</map></div>";
	return $ret;
}
}
?>
