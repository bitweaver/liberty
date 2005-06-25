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
// | Author (TikiWiki): Damian Parker <damosoft@users.sourceforge.net>
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up) 
// | by: StarRider <starrrider@users.sourceforge.net>
// | Reworked from: wikiplugin_agentinfo.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.agentinfo.php,v 1.1.1.1.2.3 2005/06/25 12:01:32 starrrider Exp $
// Initialization
define( 'PLUGIN_GUID_DATAAGENTINFO', 'dataagentinfo' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'AGENTINFO',
						'auto_activate' => TRUE,
						'requires_pair' => FALSE,
						'load_function' => 'data_agentinfo',
						'title' => 'AgentInfo',
						'help_page' => 'DataPluginAgentInfo',
						'description' => tra("This plugin will display the viewer's IP address, the Browser they are using, or the info about the site's Server software."),
						'help_function' => 'data_agentinfo_help',
						'syntax' => "{agentinfo info= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAAGENTINFO, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAAGENTINFO );

// Help Function
function data_agentinfo_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>info</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Show information about the Browser being used.") . '<br />'
				.'<strong>ip</strong>: ' . tra( "To get the client\'s IP address (default)" ) . '<br />'
				.'<strong>browser</strong>: ' . tra( "To get the clients Browser infromation." ) . '<br />'
				.'<strong>server</strong>: ' . tra( "To get the site\'s server software" ) . '<br />'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{agentinfo info='browser'}";
	return $help;
}

// Load Function
function data_agentinfo($data, $params) {
    $info = 'IP';
	extract ($params);
	switch (strtoupper ($info)) {
		case 'SVRSW': // To maintain Pre-Clyde Parameters
	    case 'SERVER':
    		  $ret = $_SERVER["SERVER_SOFTWARE"];
	          return $ret;
	    case 'BROWSER':
    		  $ret = $_SERVER["HTTP_USER_AGENT"];
	          return $ret;
	    default:
    		  $ret = $_SERVER["REMOTE_ADDR"];
	          return $ret;
	}

}
/******************************************************************************
The code below is from the deprecated AGENTINFO plugin. All comments and the help routines have been removed. - StarRider
// Wiki plugin to display a browser client information
// damian aka damosoft May 2004

function wikiplugin_agentinfo($data, $params) {
	extract ($params);
	$ret = '';
	if (!isset($info)) {
		$info = 'IP';
	}
	if ($info == 'IP') {
		$ret = $_SERVER["REMOTE_ADDR"];
	}
	if ($info == 'SVRSW') {
		$ret = $_SERVER["SERVER_SOFTWARE"];
	}
	if ($info == 'BROWSER') {
		$ret = $_SERVER["HTTP_USER_AGENT"];
	}
	return $ret;
}
*/
?>
