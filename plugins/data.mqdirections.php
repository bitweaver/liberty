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
// $Id: data.mqdirections.php,v 1.2.2.9 2005/11/11 22:04:09 mej Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATAMQDIR', 'datamqdir' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'MQDIR',
						'auto_activate' => TRUE,
						'requires_pair' => FALSE,
						'load_function' => 'data_mqdir',
						'title' => 'MapQuest Directions',
						'help_page' => 'DataPluginMapQuestDirections',
						'description' => tra("Creates an Icon link to MapQuest with a form to get Directions from MapQuest based on a Destination Address."),
						'help_function' => 'data_mqdir_help',
						'syntax' => "{MQDIR icon= myicon= text= address= city= state= zip= country= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAMQDIR , $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAMQDIR );

// Help Function
function data_mqdir_help() {
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
					. tra("<br />The Default = ") . ' <strong>sm</strong> ' . tra("The Small MapQuest Icon is displayed.")
				.'</td>'
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
				. "<a href=\"http://www.bcpl.net/~j1m5path/isocodes-table.html\">" . tra("ISO Country Codes").'</a></td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{MQDIR icon=sm address='1730 Blake St' city=Denver state=CO zip=80202 }";
	return $help;
}

// Load Function
function data_mqdir( $data, $params ) { 
	extract ($params, EXTR_SKIP);

	$a2a = isset($address) ? $address : ' ';
	$a2c = isset($city) ? $city : ' ';
	$a2s = isset($state) ? $state : ' ';
	$a2z = isset($zip) ? $zip : ' ';
	$a2y = isset($country) ? $country : 'US';

    $icon = isset($icon) ? $icon : "SM"; // Test for the MapQuest Icon
	switch(strtoupper($icon)) {
		case 'SM':
   			$icn = 'ws_wt_sm';
			break;
		case 'MED':
   			$icn = 'ws_wt_md';
			break;
		case 'LG':
   			$icn = 'ws_wt_lg';
			break;
		case 'NONE':
   			$ret = ' ';
			break;
	}

	$ret = 
		'<form action="http://www.mapquest.com/directions/main.adp" method="get">'
			.'<div align="center">'
				.'<input type="hidden" name="go" value="1+">'
				.'<input type="hidden" name="2a" value="' . $a2a . '">'
				.'<input type="hidden" name="2c" value="' . $a2c . '">'
				.'<input type="hidden" name="2s" value="' . $a2s . '">'
				.'<input type="hidden" name="2z" value="' . $a2z . '">'
				.'<input type="hidden" name="2y" value="' . $a2y . '">'
				.'<br />'
				.'<table border="0" cellpadding="0" cellspacing="0" style="font: 11px Arial,Helvetica;">'
					.'<tr><td colspan="2" style="font-weight: bold;">'
						.'<div align="center">'
							.'<a href="http://www.mapquest.com/"><img border="0" src="http://cdn.mapquest.com/mqstyleguide/' . $icn . '" alt="MapQuest"></a>'
						.'</div>'
					.'</td></tr>'
					.'<tr><td colspan="2" style="font-weight: bold;">FROM:</td></tr>'
					.'<tr><td colspan="2">Address or Intersection: </td></tr>'
					.'<tr><td colspan="2"><input type="text" name="1a" size="22" maxlength="30" value=""></td></tr>'
					.'<tr> <td colspan="2">City: </td></tr>'
					.'<tr> <td colspan="2"><input type="text" name="1c" size="22" maxlength="30" value=""></td></tr>'
					.'<tr><td>State:</td>'
					.'<td> ZIP Code:</td></tr>'
					.'<tr><td><input type="text" name="1s" size="4" maxlength="2" value=""></td><td>'
					.'<input type="text" name="1z" size="8" maxlength="10" value=""></td></tr>'
					.'<tr><td colspan="2">Country:</td></tr>'
					.'<tr><td colspan="2"><select name="1y"><option value="CA">Canada</option><option value="US" selected>United States</option></select></td></tr>'
					.'<tr> <td colspan="2" style="text-align: center; padding-top: 10px;"><input type="submit" name="dir" value="Get Directions" border="0"></td></tr>'
					.'<input type="hidden" name="CID" value="lfddwid">'
				.'</table>'
			.'</div>'
		.'</form>';
	return $ret;
}
?>
