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
// | Author: Southpaw <southpawz@users.sourceforge.net>
// +----------------------------------------------------------------------+

/**
 * definitions
 */
global $gLibertySystem;

define( 'PLUGIN_GUID_FREEMIND', 'datafreemind' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'MM',
						'auto_activate' => TRUE,
						'requires_pair' => FALSE,
						'load_function' => 'data_freemind',
						'title' => 'FreeMind (Mind Map)',
						'help_page' => 'DataPluginFreeMind',
						'description' => tra("Displays a Freemind mindmap"),
						'help_function' => 'data_freemind_help',
						'syntax' => "{MM src= height= width= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_FREEMIND, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_FREEMIND );

// Help Function
function data_freemind_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>src</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(manditory)") . '</td>'
				.'<td>' . tra( "Location where the Mindmap MM file can be found. This can be any URL or a site value. See Examples.") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>width</td>'
				.'<td>' . tra( "number or percentage") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The width of the Mindmap window. This value can be given in pixels or as a percentage of available area. A pixel value is assumed so only a numeric value is needed. To specify a percentage - the character <strong>% MUST</strong> follow the value. The Default is taken from the Mindmap file if this parameter is not defined.") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>height</td>'
				.'<td>' . tra( "number or percentage") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The height of the Mindmap window. This value can be given in pixels or as a percentage. A pixel value is assumed so only a numeric value is needed. To specify a percentage - the character <strong>% MUST</strong> follow the value. When a percentage is given - the value is defined by the Mindmap file with a maximum of 100%. <strong>Note:</strong> The Default is taken from the Mindmap file if this parameter is not defined.") . '</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{MM src='http://www.bitweaver.org/examples/bitweaver.mm' width='100%' height='600' }";
	return $help;
}

// Load Function
function data_freemind( $data, $params ) { 
	$repl = '';
	if( !empty( $params['src'] ) ) {
		$height = !empty( $params['height'] ) ? $params['height'] : '600';
		$width = !empty( $params['width'] ) ? $params['width'] : '100%';
		$drvr = '../drivers/freemind.jar';
		if (!file_exists($drvr)) {
			$repl = tra("Error - the file") . '<strong> freemind.jar </strong>' . tra("does not exist in the site's <strong>drivers</strong> directory."); 
		} else {
			$repl =
				 '<script language="JavaScript">'.
				 '        if(!navigator.javaEnabled()) {'.
				 '                document.write(\'Please install a <a href="http://www.java.com/">Java Runtime Environment</a> on your computer.\');'.
				 '        }'.
				 '</script>'.
				 '<applet code="freemind.main.FreeMindApplet.class" archive="../drivers/freemind.jar" '.
				 ' width="' . $width . '" height="' . $height .'" >'.
				 '        <param name="type" value="application/x-java-applet;version=1.4" >'.
				 '        <param name="scriptable" value="false" >'.
				 '        <param name="modes" value="freemind.modes.browsemode.BrowseMode" >'.
				 '        <param name="browsemode_initial_map" value="' . $params["src"] . '" >'.
				 '        <param name="initial_mode" value="Browse" >'.
				 '</applet>'.
				 '<br />'.
				 '<span class="">Download <a href="' . $params["src"] . '">this mind map</a> and use this application to edit it: <a href="http://freemind.sourceforge.net/">Freemind </a> </span>';
		}		
	}
	return $repl;
}
?>
