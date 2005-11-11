<?php
/**
 * @version  $Revision: 1.2.2.9 $
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
// | Author: StarRider <starrrider@users.sourceforge.net>
// +----------------------------------------------------------------------+
// $Id: data.mapquest.php,v 1.2.2.9 2005/11/11 22:04:09 mej Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATAMAPQUEST', 'datamapquest' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'MAPQUEST',
						'auto_activate' => TRUE,
						'requires_pair' => FALSE,
						'load_function' => 'data_mapquest',
						'title' => 'MapQuest',
						'help_page' => 'DataPluginMapQuest',
						'description' => tra("Creates an Icon link to MapQuest and/or a link to a Specific Map based on an Address."),
						'help_function' => 'data_mapquest_help',
						'syntax' => "{MAPQUEST icon= myicon= text= address= city= state= zip= country= title= style= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAMAPQUEST, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAMAPQUEST );

// Help Function
function data_mapquest_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>icon</td>'
				.'<td>' . tra( "key-word") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Creates an Icon Link to MapQuests primary URL. The size of the Icon can be:") 
					.' <strong>sm</strong> ' . tra("= Small,")
					.' <strong>med</strong> ' . tra("= Medium,") 
					.' <strong>lg</strong> ' . tra("= Large or")
					.' <strong>none</strong> ' . tra("= The primary URL to MapQuest is <strong>NOT</strong> Displayed)")
					. tra("<br />The Default = ") . ' <strong>sm</strong> ' . tra("The Small MapQuest Icon is displayed.")
				.'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>myicon</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Address of an icon used for a link to a Specific Map.")
					. tra("<br />The Default = ") . '<strong>NONE</strong>'
					. tra("<br /><strong>NOTE:</strong> The primary URL to MapQuest is not displayed when using this parameter.")
				.'</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>text</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The Text used for the link to the Specific Map")
				. tra("<br />The Default = ") . '<strong>Get Map</strong></td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>address</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The Street Address")
				. tra("<br />The Default = ") . '<strong>NONE</strong></td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>city</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The City")
				. tra("<br />The Default = ") . '<strong>NONE</strong></td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>state</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The State or Province")
				. tra("<br />The Default = ") . '<strong>NONE</strong></td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>zip</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The Zip or Postal Code")
				. tra("<br />The Default = ") . '<strong>NONE</strong></td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>country</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The Country (Uses 2-digit ISO Codes)")
				. tra("<br />The Default = ") . '<strong>US</strong>'
				. tra("<br /><strong>Note:</strong> 2-Digit ISO Country Codes are available from ")
				. '<a href="http://www.bcpl.net/~j1m5path/isocodes-table.html" title="Launch BCPL.net in New Window" onkeypress="popUpWin(this.href,\'standard\',800,800);" onclick="popUpWin(this.href,\'standard\',800,800);return false;">' . tra( "ISO Country Codes" ) . '</a></td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>title</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "MapQuest labels each Map with the Address, City, State, &amp; Zip Code. This parameter overwrites that label when defined.")
				. tra("<br />The Default = ") . '<strong>NONE</strong></td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>style</td>'
				.'<td>' . tra( "key-word") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Specifies the Map's Colors. Possible values are:")
					.' <strong>0</strong> ' . tra("(Gray Scale),")
					.' <strong>1</strong> ' . tra("(Neutrals),")
					.' <strong>2</strong> ' . tra("(Yellows),")
					.' <strong>3</strong> ' . tra("(European Style)")
					. tra("<br />The Default = ") . ' <strong>1</strong> ' . tra("(Neutrals)")
				.'</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{MAPQUEST icon=sm address='1730 Blake St' city=Denver state=CO zip=80202 }";
	return $help;
}

// Load Function
function data_mapquest( $data, $params ) { 
	extract ($params, EXTR_SKIP);

	$ret = '<a href="http://www.mapquest.com" title="Launch Map Quest in a New Window" onkeypress="popUpWin(this.href,\'standard\',800,800);" onclick="popUpWin(this.href,\'standard\',800,800);return false;">';

	$text = isset($text) ? $text : 'Get Map'; // Set the Link Text
    $icon = isset($icon) ? $icon : "SM"; // Test for the MapQuest Icon
	switch(strtoupper($icon)) {
		case 'SM':
   			$ret = $ret . '<img border="0" src="http://cdn.mapquest.com/mqstyleguide/ws_wt_sm" alt="MapQuest"></a><br/>';
			break;
		case 'MED':
   			$ret = $ret . '<img border="0" src="http://cdn.mapquest.com/mqstyleguide/ws_wt_md" alt="MapQuest"></a><br/>';
			break;
		case 'LG':
   			$ret = $ret . '<img border="0" src="http://cdn.mapquest.com/mqstyleguide/ws_wt_lg" alt="MapQuest"></a><br/>';
			break;
		case 'NONE':
   			$ret = ' ';
			break;
	}
	$map = 'http://www.mapquest.com/maps/map.adp?';
	$map = isset($address) ? $map . '&address=' . implode('+', explode(' ',$address)) : $map;
	$map = isset($city) ? $map . '&city=' . implode('+', explode(' ',$city)) : $map;
	$map = isset($state) ? $map . '&state=' . $state : $map;
	$map = isset($zip) ? $map . '&zipcode=' . $zip : $map;
	$map = isset($country) ? $map . '&country=' . $country : $map . '&country=US';
	$map = isset($style) ? $map . '&style=' . $style : $map;
	$map = isset($title) ? $map . '&title=' . implode('+', explode(' ',$title)) : $map;
	$map = '"' . $map . '&cid=lfmaplink"';
	
	$map = '<a href="' . $map . '" title="Launch Map Quest in a New Window" onkeypress="popUpWin(this.href,\'standard\',800,800);" onclick="popUpWin(this.href,\'standard\',800,800);return false;">';
	
    if (isset($myicon)) { // Test for the existance of MyIcon
		$ret = $map . '<img border="0" src="' . $myicon . '"></a>';
	} else {
		$ret = $ret . $map . $text . '</a>';
	}
	return $ret;
}
?>
