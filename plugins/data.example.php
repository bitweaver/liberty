<?php
/**
 * @version  $Revision: 1.4.2.6 $
 * @package  Liberty
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
// | Note: A plugin with this name did exist as a TikiWiki Plugin
// | This is not that plugin. 
// +----------------------------------------------------------------------+
// $Id: data.example.php,v 1.4.2.6 2005/06/27 10:08:37 lsces Exp $
// Initialization
define( 'PLUGIN_GUID_DATAEXAMPLE', 'dataexample' );
// NOTE: The GUID is used as a link to a help page on bitweaver.org
// Please be sure to create this page when the plugin is created. 
// In this case - the pagename should be "DataPluginExample"
						
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'EXAMPLE',
						'auto_activate' => FALSE,
						'requires_pair' => FALSE, // Make this TRUE if the plugin needs to operate on free form text
						'load_function' => 'data_example', // Points to the Load Function
						'title' => 'Example', // Name of the Plugin
						'help_page' => 'DataPluginExample', // Name of Help Page on Bitweaver.org
						'description' => tra("This plugin is an example to show how plugins operate. It can also function as a template for the creation of new plugins since it contains a lot of spare code and explanations about how - and when - they should be used."), // What it does
						'help_function' => 'data_example_help', // Points to the Help Function
						'syntax' => "{EXAMPLE p1= p2= }", // A listing of parameters
						'plugin_type' => DATA_PLUGIN // Don't Touch 
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAEXAMPLE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAEXAMPLE );

/**************** Lets talk about what all of this means.
The GUID ----> define( 'PLUGIN_GUID_DATAEXAMPLE', 'dataexample' );  <------- Line 17
The GUID does several thing. First - it identifies the plugin to the Liberty System so that it can be found
Second - The GUID (in this case 'dataexample') is used as a link to a help page on bitweaver.org
The pagename should be "DataPluginExample"
// Please be sure to create this page when the plugin is created. 
*/




// Help Function
function data_example_help() { // Specified by $pluginParams['help_function']
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>p1</td>'
				.'<td>' . tra( "anything") . '<br />' . tra("(Manditory)") . '</td>'
				.'<td>' . tra( "The first parameter. There is no Default") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>p2</td>'
				.'<td>' . tra( "anything") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "The second parameter. There is no Default") . '</td>'

// At times there is more reference data available than the Help Function can readilly display
// When this happens - we provide a link in the Help Function. Each of the following snippets
// Creates a link that opens a new window (so the user is not taken from his work) - HTML Compliant

// This creates a link for ISO Country Codes
/*
				. tra("<br /><strong>Note:</strong> 2-Digit ISO Country Codes are available from ")
				. '<a href="http://www.bcpl.net/~j1m5path/isocodes-table.html" title="Launch BCPL.net in New Window" onkeypress="popUpWin(this.href,\'standard\',800,800);" onclick="popUpWin(this.href,\'standard\',800,800);return false;">' . tra( "ISO Country Codes" ) . '</a>'
*/

// This creates 2 links / 1- to a BitWeaver.org Page     2- to PageTutor.com's Color Picker II
/*
				. tra("<strong>Note:</strong> Browser Safe Colornames are available on the ") 
				. '<a href="http://www.bitweaver.org/wiki/index.php?page=Web-Safe+HTML+Colors" title="Launch BitWeaver.Org in New Window" onkeypress="popUpWin(this.href,\'standard\',800,800);" onclick="popUpWin(this.href,\'standard\',800,800);return false;">' . tra( "BitWeaver Web Site" ) . '</a>'
				. tra(" Another useful site for obtaining HTML colors is ")
 				. '<a href="http://www.pagetutor.com/pagetutor/makapage/picker" title="Launch PageTutor.com in New Window" onkeypress="popUpWin(this.href,\'standard\',800,800);" onclick="popUpWin(this.href,\'standard\',800,800);return false;">' . tra( "The Color Picker II" ) . '</a>';
*/

// This creates a link to the user's site to get BitWeaver's Content ID Numbers
/*
				. tra("This a Numeric Content Id. This allows blog posts, images, wiki pages . . . (and more) to be added.")
				. tra("<br /><strong>Note 1:</strong> A listing of Content Id's can be found ") 
				. '<a href="'.LIBERTY_PKG_URL.'list_content.php" title="Launch BitWeaver Content Browser in New Window" onkeypress="popUpWin(this.href,\'standard\',800,800);" onclick="popUpWin(this.href,\'standard\',800,800);return false;">' . tra( "Here" ) . '</a>'
*/

			.'</tr>'
 		.'</table>'
		. tra("Example: ") . "{EXAMPLE p1='7' p2='8' }<br />"
		. tra("This will display - P1 was set to = 7 & P2 was set to = 8");
	return $help;
}

// Load Function
function data_example($data, $params) { // Specified by $pluginParams['load_function']
	extract ($params); // This extracts any parameter and creates a variable with the same name

// Use this if you need to a Manditory Parameter
    if (!isset($p1) ) {  // The Manditory Parameter is missing
        $ret = tra("The parameter ") . "__p1__" . tra(" was missing from the plugin ") . "__~np~{EXAMPLE}~/np~__.";
		$ret.= data_example_help();
	    return $ret;
	}

// Of course - any Manditory Parameter needs to be tested to see if it is valid - and provide an error message if it isn't
	if( $p1 == 5) {
	    $ret = '<strong>Error</strong> ' . tra("- The plugin ") . '<strong>~np~{EXAMPLE}~/np~</strong>' . tra(" was given the parameter ") . '<strong>p1=' . $p1 . '</strong> ' . tra("which is not valid.");
   	    return $ret;
   	}
	
// Some plugin are designed to opperate with a lot of text. To do this the 'requires_pair' should be set to TRUE
// Use this if the plugin needs to operate on text - the 'requires_pair' should be set to TRUE - so the text will be between {plugin()} Blocks {plugin}
	if (!isset($data)) { // There is no data between the Plugin Blocks
		$ret = tra("__Error__ - There was no data between the ") . "__~np~{EXAMPLE}~/np~__" . tra(" blocks for the plugin to operate on.");
	    return $ret;
	}
// There are several ways of testing to ensure that a parameter has a value
	if (!isset($p1)) { // Always test each parameter to be sure it has a value before you use it.
	  $p1 = 5;
	}
// Of course - this is another way
    $ret = isset($p1) ? "P1 was set to = $p1" : "This should never be seen - it should be caught by the test for the Manditory Parameter";
    $ret = isset($p2) ? $ret." & P2 was set to = $p2" : $ret." & P2 was not set";
	return $ret;

// This is not seen by this plugin but it works - comment out the last 3 lines to see
// There are many times when you have several possible values for a given key - a case statemenmt works wonders
	switch (strtoupper ($p2)) {
	    case 'ABC':
    		  $ret = "P2 was ABC";
	          return $ret;
	    case 'DEF':
    		  $ret = "P2 was DEF";
	          return $ret;
	    default:
    		  $ret = "P2 was Something Else";
	          return $ret;
	}

// Boolean values (with a default) can be handled like this
    $p1 = FALSE; // Ensure that $p1 starts with your default value before the extract
	extract ($params);
    $ret = $p1 ? "P1 was TRUE = $p1" : "P1 was FALSE = $p1";
	return $ret;  // Parameter values can be False / True or 0=False / >0=True
}
/******************************************************************************
This plugin was made to simplify the creation of new plugins.
As an example - lets say I wanted to create a new plugin called DOGS (Hope you know what it will do because I don't)
The first step would be to copy this file and rename it data.dogs.php in the same directory (Liberty\Plugins)
Next - in your editor do 2 case sensitive search and replace - changing EXAMPLE to DOGS / AND / example to dogs
The next changes are in the pluginParams array
    Change the Title to "Dogs"
    Change the description to briefly explain what your plugin will do
    Decide on the number of parameters and parameter names to be passed to the plugin and place them in syntax.
	The format we are currently using is {pluginname parameter='strings are OK'}
    NOTE: If your plugin will operate on text that is entered at the same time the plugin is called - then you will probably want
    to use the format {pluginname any-parameter-needed='3'} Text to be operated on {pluginname} - if this is the case - be sure
	to change the requires_pair to TRUE
Next - Change the Help Function so that all of the parameters are all listed / what each one does / and any default value it may 
have - be sure to mention if it is a Manditory Parameter or an Optional Parameter
NOTE: The Help Function uses Wiki-Syntax to improve the appearance of the help message. By this point, you should be able to
see your plugin in the Wiki Page Editor / Plugin Tab of Help - so make it look good.
NOTE 2: Part of the reason the help routine looks as complex as it does is the fact that bitWeaver is an international program. The 
tra( function is used to convert text from one language to another. Most of the text can be changed for clarity - but some like the name 
of the plugin / the parameter names and some specific values can not be change - so keep that in mind when adding to the Help - 
Function. This also applies to Error Messages. 
Finally - Change the Load Function so that it does what you want it to do
NOTE: I added a bunch of simple tests to the Load Function that should help a novice - this is not all inclusive listing but it does 
show what I am using to standardize these plugins - use them or blow them away - as you will - StarRider 
*/
?>
