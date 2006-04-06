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
// | Author (TikiWiki): Damian Parker <damosoft@users.sourceforge.net>
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up)
// | by: StarRider <starrrider@users.sourceforge.net>
// | Rewrote data function so plugin can cover more types of objects than just Flash
// | by: Jasp (Jared Woodbridge) <jaspp@users.sourceforge.net>
// +----------------------------------------------------------------------+
// $Id: data.object.php,v 1.3 2006/04/06 05:06:11 starrrider Exp $

/**
 * definitions
 */
global $gBitSystem;
if( $gBitSystem->isPackageActive( 'wiki' ) ) { // Do not include this Plugin if the Package is not active

define( 'PLUGIN_GUID_DATAOBJECT', 'dataobject' );
global $gLibertySystem;
$pluginParams = array (
	'tag' => 'OBJECT',
	'auto_activate' => TRUE,
	'requires_pair' => FALSE,
	'load_function' => 'data_object',
	'title' => 'Object',
	'help_page' => 'DataPluginObject',
	'description' => tra("This plugin displays a Flash, Tcl or Java applet/object."),
	'help_function' => 'data_object_help',
	'syntax' => "{OBJECT type= src= width= height=}",
	'path' => LIBERTY_PKG_PATH.'plugins/data.object.php',
	'security' => 'registered',
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAOBJECT, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAOBJECT );


function data_object_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>type</td>'
				.'<td>' . tra( "key-word" ) . '<br />' . tra( "(manditory)" ) . '</td>'
				.'<td>' . tra( "The type of object being displayed. Possible values are:") . ' <strong>tcl, flash, java</strong>.' . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>src</td>'
				.'<td>' . tra( "string" ) . '<br />' . tra( "(manditory)" ) . '</td>'
				.'<td>' . tra( "The location of the file used for the object. This can be any URL or a site value. See Examples.") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>param_<i>name</i></td>'
				.'<td>' . tra( "string" ) . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "Can be used to specify custom object parameters. Currently only available for Tcl applets. Replace \"<i>name</i>\" with the name of the parameter.") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>width</td>'
				.'<td>' . tra( "number or percentage" ) . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "The width of the object. This value can be given in pixels or as a percentage of available area. A pixel value is assumed so only a numeric value is needed. To specify a percentage - the character <strong>% MUST</strong> follow the value.") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>height</td>'
				.'<td>' . tra( "number or percentage" ) . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "The height of the object. This value can be given in pixels or as a percentage. A pixel value is assumed so only a numeric value is needed. To specify a percentage - the character <strong>% MUST</strong> follow the value.") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>float</td>'
				.'<td>' . tra( "key-words" ) . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "Specifies how the object is to float on the page. Floating elements are positioned on the side specified, with content flowing around. Possible values are:") . ' <strong>left, right, none</strong>. '
				. tra("(Default = ") . '<strong>' . tra( 'none - object is shown inline' ) . '</strong>)</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>clear</td>'
				.'<td>' . tra( "key-words" ) . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "Specifies which horizontal sides of the object can not have other content flowing around. Possible values are:") . ' <strong>left, right, both, none</strong>. '
				. tra("(Default = ") . '<strong>' . tra( 'none - content is allowed to flow around object' ) . '</strong>)</td>'
			.'</tr>'
		.'</table>'

		.'<table class="data help">'
			.'<caption>' . tra( "Flash specific parameters" ) . '</caption>'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>quality</td>'
				.'<td>' . tra( "key-word" ) . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "The quality at which to display a Flash applet. Possible values are unknown - except:") . ' <strong>high</strong> ' . tra("and probably") . ' <strong>low</strong>.</td>'
			.'</tr>'
		.'</table>'

		.'<table class="data help">'
			.'<caption>' . tra( "Java specific parameters" ) . '</caption>'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>vmversion</td>'
				.'<td>' . tra( "version number" ) . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "The version of Java required for the applet. Should be in the form of <strong>X.x</strong>, eg: <strong>1.3</strong>." ) . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>pagescript</td>'
				.'<td>' . tra( "boolean" ) . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "Specifies if the applet can access Javascript features on the web page. Possible values are:") . ' <strong>true, false</strong>.</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>appletscript</td>'
				.'<td>' . tra( "boolean" ) . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "Specifies whether the applet is scriptable from the web page using JavaScript or VBScript. Possible values are:") . ' <strong>true, false</strong>.</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>srcbase</td>'
				.'<td>' . tra( "string" ) . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "The base location of the Java applet." ) . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>archive</td>'
				.'<td>' . tra( "string" ) . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "Specifies the name of the Java archive." ) . '</td>'
			.'</tr>'
		.'</table>'

		. tra("Example: ") . "{OBJECT type=flash src=../liberty/icons/Mind-Reader.swf}<br />"
		. tra("Example: ") . "{OBJECT type=flash src=http://www.bitweaver.org/liberty/icons/Mind-Reader.swf width='100%' height='600' quality='high'}<br />"
		. tra('Both of these examples display "The Flash Mind Reader" by Andy Naughton. The first example is on your site and is not very large. The second example is located on the bitweaver.org site and takes the width of the center column with an appropriate height.');
	return $help;
}


function data_object ($data, $params) {
	// Need these plugin parameters
	foreach (array("type", "src") as $parameter) {
		if (!array_key_exists($parameter, $params))
			return '<span class="warning">'.tra('When using <strong>{object}</strong>, a <strong>type</strong> and <strong>src</strong> parameter is required.').'</span>';
	}

	$objectParams = array();

	switch ($params["type"]) {
		case "tcl":
			// This loop scans for and sets param_ custom object parmeters. Note that in the future, it may be used for object types other than Tcl, so don't go making this part of the tcl clause below.
			foreach (array_keys($params) as $parameter) {
				if (ereg("param_*", $parameter))
					$objectParams[substr($parameter, 6)] = $params[$parameter];
			}

		case "tcl":
			// Tcl Plugin applet
			$classid = "clsid:D27CDB6E-AE6D-11cf-96B8-444553540000";
			$objectParams["type"] = "application/x-tcl";
			$objectParams["pluginspage"] = "http://www.tcl.tk/software/plugin/";
			$objectParams["src"] = $params["src"];
			break;

		case "flash":
			// Macromedia Flash movie
			$classid = "clsid:D27CDB6E-AE6D-11cf-96B8-444553540000";
			$objectParams["movie"] = $params["src"];
			if (array_key_exists("quality", $params))
				$objectParams["quality"] = $params["quality"];
			break;

		case "java":
			// Java applet
			$classid = "clsid:8AD9C840-044E-11D1-B3E9-00805F499D93";
			$objectParams["code"] = $params["src"];
			$objectParams["type"] = "application/x-java-applet";
			if (array_key_exists("vmversion", $params))
				$objectParams["type"] .= ';version='.$params["vmversion"];
			if (array_key_exists("pagescript", $params))
				$objectParams["mayscript"] = $params["pagescript"];
			if (array_key_exists("appletscript", $params))
				$objectParams["scriptable"] = $params["appletscript"];
			if (array_key_exists("srcbase", $params))
				$objectParams["codebase"] = $params["srcbase"];
			if (array_key_exists("archive", $params))
				$objectParams["archive"] = $params["archive"];
			break;

		default:
			// Unrecognized object type
			return '<span class="warning">'.tra('The <strong>type</strong> parameter of <strong>{object}</strong> must either be <strong>tcl</strong>, <strong>flash</strong> or <strong>java</strong>.').'</span>';
	}

	// Build the <object> HTML code
	$result  = '<object classid="'.$classid.'" style="';
	$result .= (array_key_exists("float",  $params)) ? ' float: ' .$params["float"]. ';' : '';
	$result .= (array_key_exists("clear",  $params)) ? ' clear: ' .$params["clear"]. ';' : '';
	$result .= '"';
	$result .= (array_key_exists("width",  $params)) ? ' width="' .$params["width"]. '"' : '';
	$result .= (array_key_exists("height", $params)) ? ' height="'.$params["height"].'"' : '';
	$result .= '>';
	foreach (array_keys($objectParams) as $parameter)
		$result .= '<param name="'.$parameter.'" value="'.$objectParams[$parameter].'"/>';
	$result .= '</object>';

	// ...and we're done
	return $result;
}

} ?>