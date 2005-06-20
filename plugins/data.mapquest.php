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
// | New Code
// +----------------------------------------------------------------------+
// $Id: data.mapquest.php,v 1.2 2005/06/20 07:34:17 lsces Exp $
// Initialization
define( 'PLUGIN_GUID_DATAMAPQUEST', 'datamapquest' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'MAPQUEST',
						'auto_activate' => TRUE,
						'requires_pair' => FALSE,
						'load_function' => 'data_mapquest',
						'title' => 'MapQuest',
						'description' => tra("Creates an Icon link to MapQuest and/or a link to a Specific Map based on an Address."),
						'help_function' => 'data_mapquest_help',
						'tp_helppage' => "http://www.bitweaver.org/wiki/index.php", // Update this URL when a page on bitweaver.org exists
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
				. "<a class='wiki' target=" . '"_blank"' . " href=http://www.bcpl.net/~j1m5path/isocodes-table.html>" . tra("ISO Country Codes</a> ")
			.'</tr>'
			.'<tr class="odd">'
				.'<td>title</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "MapQuest labels each Map with the Address, City, State, & Zip Code. This parameter overwrites that label when defined.")
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
		. tra("Example: ") . "{code source='php' num='on' }" . tra("Sorce Code Snippet") . "{code}";
	return $help;
}

// Load Function
function data_mapquest( $data, $params ) { 
	extract ($params);

    $icon = isset($icon) ? $icon : "SM"; // Test for the MapQuest Icon
	switch(strtoupper($icon)) {
		case 'SM':
   			$ret = '<a class="wiki" target=' . '"_blank"' . ' href="http://www.mapquest.com/?cid=lfhplink"><img border="0" src="http://cdn.mapquest.com/mqstyleguide/ws_wt_sm" alt="MapQuest"></a>';
			break;
		case 'MED':
   			$ret = '<a class="wiki" target=' . '"_blank"' . ' href="http://www.mapquest.com/?cid=lfhplink"><img border="0" src="http://cdn.mapquest.com/mqstyleguide/ws_wt_md" alt="MapQuest"></a>';
			break;
		case 'LG':
   			$ret = '<a class="wiki" target=' . '"_blank"' . ' href="http://www.mapquest.com/?cid=lfhplink"><img border="0" src="http://cdn.mapquest.com/mqstyleguide/ws_wt_lg" alt="MapQuest"></a>';
			break;
		case 'NONE':
   			$ret = ' ';
			break;
	}
	$text = isset($text) ? $text : 'Get Map'; // Set the Link Text
	
	$map = '<a class="wiki" target=' . '"_blank"' . ' href="http://www.mapquest.com/maps/map.adp?cid=lfmaplink" alt="' . $text . '"'; // Set up Link to a Specific Map
	$map = isset($address) ? $map . '&address=' . implode('+', explode(' ',$address)) : $map;
	$map = isset($city) ? $map . '&city=' . implode('+', explode(' ',$city)) : $map;
	$map = isset($state) ? $map . '&state=' . $state : $map;
	$map = isset($zip) ? $map . '&zipcode=' . $zip : $map;
	$map = isset($country) ? $map . '&country=' . $country : $map . '&country=US';
	$map = isset($style) ? $map . '&style=' . $style : $map;
	$map = isset($title) ? $map . '&title=' . implode('+', explode(' ',$title)) . '">' : $map . '">';

    if (isset($myicon)) { // Test for the existance of MyIcon
		$ret = $map . '<img border="0" src="' . $myicon . '"></a>';
	} else {
		$ret = $ret . $map . $text . '</a>';
	}
	return $ret;
}
?>
