<?php
/**
 * @version  $Revision: 1.6 $
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
// | Author: StarRider <starrrider@sbcglobal.net>
// | Rewritten for bitweaver by Author
// | wikiplugin_pluginhelp.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.pluginhelp.php,v 1.6 2006/03/03 07:07:15 starrrider Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATAPLUGINHELP', 'datapluginhelp' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'PLUGINHELP',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE,
						'load_function' => 'data_pluginhelp',
						'title' => 'PluginHelp',                                                                             // and Remove the comment from the start of this line
						'help_page' => 'DataPluginPluginHelp',
						'description' => tra("This plugin will display the plugin's Help."),
						'help_function' => 'data_pluginhelp_help',
						'syntax' => "{PLUGINHELP plugin= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAPLUGINHELP, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAPLUGINHELP );

// Help Function
function data_pluginhelp_help() {
	$help = libHelpParam( // $name,$type,$descr,$req,$default,$notes,$keywords )
				'plugin', // Name
				'string', // Type
				tra('The Name of the Plugin to be displayed.'), // Description
				TRUE, // Required
				tra( "There is").' <strong>No</strong> '.tra( "Default.") // Default
			);
	return libPluginHelp( //$tbl,$notes,$example)
				libHelpTable($help), // Creates the Table
				NULL, // Notes
				"{PLUGINHELP plugin='pluginhelp' }" // Example
			);
}

// Load Function
function data_pluginhelp($data, $params) {
	global $gLibertySystem;
	extract ($params);

	if (!isset($plugin)) {// Exit if the Parameter is not set
		return pluginError('PluginHelp', tra('There was No Plugin Named for').' <strong>PluginHelp</strong> '.tra('to work with.'));
	}
	foreach (array_keys($gLibertySystem->mPlugins) as $pluginGuid) {
		$pluginParams = $gLibertySystem->mPlugins[$pluginGuid];
		if ($pluginParams['plugin_type'] == DATA_PLUGIN && isset($pluginParams['description']) && $pluginParams['tag'] == strtoupper($plugin))
			$thisGuid = $pluginGuid;
	}
	if (!isset($thisGuid)) { // The Plugin was not found
		return libPluginError('PluginHelp', tra('The Plugin Name Specified').' <strong>plugin='.$plugin.'</strong> '.tra('does not exist.'));
	}

	$pluginParams = $gLibertySystem->mPlugins[$thisGuid];

	if (!is_array($pluginParams)) // Something is Wrong - Exit
		return tra('The Plugin Name Given To <strong>PluginHelp "').plugin.tra('"</strong> Either Does Not Exist Or Is Not Active.');
	$runhelp = $pluginParams['help_function'];
	$runhelp = $runhelp();
	$ret =
		'<table class="data help" style="width: 100%;" border="2" cellpadding="4">'
			.'<caption><strong><big><big>Plugin Data</big></big></strong></caption>'
			.'<tr>'
				.'<th colspan="4" style="text-align: center;"><strong><big><big>'.$pluginParams['title'].'</big></big></strong></th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td title="'.tra('The GUID is a string used to locate the Plugins Data.').'">GUID => '.$thisGuid.'</td>'
				.'<td title="'.tra('The Tag is the string you add to the text that calls the Plugin.').'">tag => '.$pluginParams['tag'].'</td>'
				.'<td title="'.tra('Provides a Default value for the Administrator.').'">auto_activate => '.($pluginParams['auto_activate'] ? 'True' : 'False').'</td>'
				.'<td title="'.tra('The Number of Code Blocks required by the Plugin. Can be 1 or 2').'">requires_pair => '.($pluginParams['requires_pair'] ? 'True' : 'False').'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td colspan="4" title="'.tra('The Description states what the Plugin does.">').'description => '.$pluginParams['description'].'</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td colspan="2" title="'.tra('This function does the work & is called by the Parser when the Tag is found.').'">load_function => '.$pluginParams['load_function'].'</td>'
				.'<td colspan="2" title="'.tra('This function displays the Extended Help Data for the Plugin.').'">help_function => '.$pluginParams['help_function'].'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td colspan="4" title="'.tra('The Syntax to be inserted into an editor for useage.').'">syntax => '.$pluginParams['syntax'].'</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td colspan="4" title="'.tra('Provides a link to a Help Page on bitweaver.org.').'">help_page => '.$pluginParams['help_page'].'</td>'
			.'</tr>';
	if ($thisGuid != 'datalibrary') $ret .= // This button is not needed by the Plugin Library {LIB}
			'<tr class="even">'
				.'<td colspan="4" style="text-align: center;" title="'.tra('Click to Visit the Help Page on bitweaver.org in a new window.').'">'
					.'<input type="button" value="Visit the Help Page" onClick="javascript:popUpWin(\'http://bitweaver.org/wiki/index.php?page='.$pluginParams['help_page'].'\',\'standard\',800,800)"></input>'
				.'</td>'
			.'</tr>';
	$ret .= '</table>'
		.'<div style="text-align: center;"><strong><big><big>'.tra('Parameter Data').'</big></big></strong></div>'
		.'<div class="help box">~np~'.$runhelp.'~/np~</div>';
	return $ret;
}
?>
