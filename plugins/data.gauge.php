<?php
// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See copyright.txt for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Author: StarRider <starrrider@sbcglobal.net>
// | Reworked from: wikiplugin_gauge.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.gauge.php,v 1.3 2005/06/20 06:49:35 lsces Exp $
// Initialization
define( 'PLUGIN_GUID_DATAGAUGE', 'datagauge' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'GAUGE',
						'auto_activate' => TRUE,
						'requires_pair' => TRUE,
						'load_function' => 'data_gauge',
						'title' => 'Gauge',
						'description' => tra("This plugin displays a graphical GAUGE."),
						'help_function' => 'data_gauge_help',
						'tp_helppage' => "http://www.bitweaver.org/wiki/index.php", // Update this URL when a page on BitWeaver.Org exists
						'syntax' => "{GAUGE color= bgcolor= max= value= size= perc= height= }" . tra("Description") . "{GAUGE}",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAGAUGE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAGAUGE );

// Help Function
function data_gauge_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>color</td>'
				.'<td>' . tra( "colorname or hex color") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Specifies the color of the of the Bar in the Gauge. Colornames or HTML colors can be used. To specify HTML color the <strong>#</strong> character <strong>MUST</strong> be included like this: ( <strong>#RRGGBB </strong> ). If not specified - the <strong>Current Text Color</strong> will be used. See Note below for Colornames & HTML Colors Sources.") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>bgcolor</td>'
				.'<td>' . tra( "colorname or hex color") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Specifies the color of the Gauges Background. Colornames or HTML colors can be used. To specify HTML color the <strong>#</strong> character <strong>MUST</strong> be included like this: ( <strong>#RRGGBB </strong> ). If not specified - the <strong>Current Background Color</strong> will be used. See Note below for Colornames & HTML Colors Sources.") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>max</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The maximum possible value to be displayed. The Gauge was designed to be used with percentages - so the Default = ") . '<strong>100</strong></td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>value</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(Required)") . '</td>'
				.'<td>' . tra( "The current value that the Guage will display. There is") . ' <strong>NO</strong> ' . tra("Default value.") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>size</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The width of the Bar in pixels. The Default = ") . '<strong>150</strong></td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>height</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The height of the Bar in pixels. The Default = ") . '<strong>14</strong></td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>perc</td>'
				.'<td>' . tra( "boolean") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Determines if the <strong>%</strong> character is displayed after the value. Passing any value in this parameter will make it <strong>TRUE</strong>. The Default = <strong>FALSE</strong> so the <strong>%</strong> character will not be displayed") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>Description</td>'
				.'<td>' . tra( "boolean") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra("<strong>This is NOT a Parameter.</strong> Text can be place between the 2 code blocks ( in this case:") . ' <strong>{GUAGE}</strong> ' . tra(" ). If present the text will be displayed below the Guage.") . '</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{GAUGE color='red' bgcolor='blue' value='25' perc='True' }A Simple Gauge{GAUGE}" . '<br />'
		. tra("<strong>Note:</strong> Browser Safe Colornames are available on the ") 
			. "<a class='wiki' target=" . '"_blank"' . " href=http://www.tikipro.org/wiki/index.php?page=Web-Safe+HTML+Colors>" . tra("TiKiPro Web Site</a> ")
			. tra("Another useful site for obtaining HTML colors is ") . "<a class='wiki' target=" . '"_blank"' . " href='http://www.pagetutor.com/pagetutor/makapage/picker/'>" 
			. tra("The Color Picker II</a>");
	return $help;
}

// Load Function
function data_gauge($data, $params) {
	extract ($params);
	if (!isset($max)) {
		$max = 100;
	}
	if (!isset($value)) {
		return tra("<b>ERROR</b> - Missing parameter. The ") . "__Gauge__" . tra(" plugin requires a value in the parameter ") . "__value__.";
	}
	if (!isset($size)) {
		$size = 150;
	}
	if (!isset($bgcolor)) {
		$bgcolor = '#0000FF';
	}
	if (!isset($color)) {
		$color = '#FF0000';
	}
	if (!isset($perc)) {
		$perc = false;
	}
	if ($perc) {
		$perc = number_format($value / $max * 100, 2);
		$perc = '&nbsp;&nbsp;' . $perc . '%';
	} else {
		$perc = '';
	}
	$h_size = floor($value / $max * $size);
	if (!isset($height)) {
		$height = 14;
	}
	$html = "<table border='0' cellpadding='0' cellspacing='0'><tr><td><table border='0' height='$height' cellpadding='0' cellspacing='0' width='$size' style='background-color:$bgcolor;'><tr><td style='background-color:$color;' width='$h_size'>&nbsp;</td><td>&nbsp;</td></tr></table></td><td>$perc</td></tr>";
	if (!empty($data)) {
		$html .= "<tr><td colspan='2'><small>$data</small></td></tr>";
	}
	$html .= "</table>";
	return $html;
}
/******************************************************************************
The code below is from the deprecated GAUGE plugin. All comments and the help routines have been removed. - StarRider

function wikiplugin_gauge($data, $params) {
	extract ($params);
	if (!isset($max)) {
		$max = 100;
	}
	if (!isset($value)) {
		return tra("<b>ERROR</b> - Missing parameter. The ") . "__Gauge__" . tra(" plugin requires a value in the parameter ") . "__value__.";
	}
	if (!isset($size)) {
		$size = 150;
	}
	if (!isset($bgcolor)) {
		$bgcolor = '#0000FF';
	}
	if (!isset($color)) {
		$color = '#FF0000';
	}
	if (!isset($perc)) {
		$perc = false;
	}
	if ($perc) {
		$perc = number_format($value / $max * 100, 2);
		$perc = '&nbsp;&nbsp;' . $perc . '%';
	} else {
		$perc = '';
	}
	$h_size = floor($value / $max * $size);
	if (!isset($height)) {
		$height = 14;
	}
	$html = "<table border='0' cellpadding='0' cellspacing='0'><tr><td><table border='0' height='$height' cellpadding='0' cellspacing='0' width='$size' style='background-color:$bgcolor;'><tr><td style='background-color:$color;' width='$h_size'>&nbsp;</td><td>&nbsp;</td></tr></table></td><td>$perc</td></tr>";
	if (!empty($data)) {
		$html .= "<tr><td colspan='2'><small>$data</small></td></tr>";
	}
	$html .= "</table>";
	return $html;
}
*/
?>
