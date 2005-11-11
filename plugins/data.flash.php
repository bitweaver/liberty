<?php
/**
 * @version  $Revision: 1.1.1.1.2.9 $
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
// +----------------------------------------------------------------------+
// $Id: data.flash.php,v 1.1.1.1.2.9 2005/11/11 22:04:09 mej Exp $

/**
 * definitions
 */
global $gBitSystem;
if( $gBitSystem->isPackageActive( 'wiki' ) ) { // Do not include this Plugin if the Package is not active

define( 'PLUGIN_GUID_DATAFLASH', 'dataflash' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'FLASH',
						'auto_activate' => TRUE,
						'requires_pair' => FALSE,
						'load_function' => 'data_flash',
						'title' => 'Flash',
						'help_page' => 'DataPluginFlash',
						'description' => tra("This plugin allows a Flash SWF file to be displayed."),
						'help_function' => 'data_flash_help',
						'syntax' => "{FLASH movie= width= height= quality= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAFLASH, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAFLASH );

// Help Function
function data_flash_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>movie</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(manditory)") . '</td>'
				.'<td>' . tra( "A location where the Flash SWF file can be found. This can be any URL or a site value. See Examples.") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>width</td>'
				.'<td>' . tra( "number or percentage") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The width of the players window. This value can be given in pixels or as a percentage of available area. A pixel value is assumed so only a numeric value is needed. To specify a percentage - the character <strong>% MUST</strong> follow the value. The Default is taken from the SWF file if this parameter is not defined.") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>height</td>'
				.'<td>' . tra( "number or percentage") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The height of the players window. This value can be given in pixels or as a percentage. A pixel value is assumed so only a numeric value is needed. To specify a percentage - the character <strong>% MUST</strong> follow the value. When a percentage is given - the value is defined by the SWF file with a maximum of 100%. <strong>Note:</strong> This is <strong>NOT</strong> a percentage of the available area. Experimentation seems to be the only option available with this parameter. If this parameter is not defined the Default value is taken from the SWF file <strong>(i.e. 100%)</strong>.") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>quality</td>'
				.'<td>' . tra( "key-word") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Specifies the quality to display the picture. Possible values are unknown - except:") . ' <strong>high</strong> ' . tra("and probably") . ' <strong>low</strong> ' . tra("The Default = ") . '<strong>high</strong></td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{FLASH movie=../liberty/icons/Mind-Reader.swf }<br />"
		. tra("Example: ") . "{{FLASH movie=http://www.bitweaver.org/liberty/icons/Mind-Reader.swf width='100%' height='600' quality='high' }<br />"
		. tra('Both of these examples display "The Flash Mind Reader" by Andy Naughton. The first example is on your site and is not very large. The second example is located on the bitweaver.org site and takes the width of the center column with an appropriat height.'); 
	return $help;
}

// Load Function
function data_flash($data, $params) {
	extract ($params, EXTR_SKIP);
	$w	= (isset($width)) 	?	$width : "";
	$h	= (isset($height))	?	$height : "";
	$q	= (isset($quality))	?	$quality : "high";

	$asetup = "<OBJECT CLASSID=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0\" WIDTH=\"$w\" HEIGHT=\"$h\">";
	$asetup .= "<PARAM NAME=\"movie\" VALUE=\"$movie\">";
	$asetup .= "<PARAM NAME=\"quality\" VALUE=\$q\">";
	$asetup .= "<embed src=\"$movie\" quality=\"$q\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\" width=\"$w\" height=\"$h\"></embed></object>";

	return $asetup;
}
}
?>
