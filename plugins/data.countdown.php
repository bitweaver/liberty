<?php
/**
 * @version  $Revision: 1.8 $
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
// | Author (TikiWiki): Stephan Borg <wolff_borg@users.sourceforge.net>
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up)
// | by: StarRider <starrrider@users.sourceforge.net>
// +----------------------------------------------------------------------+
// $Id: data.countdown.php,v 1.8 2007/06/09 18:09:39 squareing Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATACOUNTDOWN', 'datacountdown' );
global $gLibertySystem;
$pluginParams = array (
	'tag' => 'COUNTDOWN',
	'auto_activate' => FALSE,
	'requires_pair' => FALSE,
	'load_function' => 'data_countdown',
	'title' => 'CountDown',
	'help_page' => 'DataPluginCountDown',
	'description' => tra("Displays a Count-Down until a date:time is reached - then - negative numbers indicate how long it has been since that date. The Count-Down is displayed in the format of (X days, X hours, X minutes and X seconds)."),
	'help_function' => 'data_countdown_help',
	'syntax' => "{COUNTDOWN enddate= localtime= class= punct= text=}",
	'path' => LIBERTY_PKG_PATH.'plugins/data.countdown.php',
	'security' => 'registered',
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATACOUNTDOWN, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATACOUNTDOWN );

// Help Function
function data_countdown_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>enddate</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(mandatory)") . '</td>'
				.'<td>' . tra( "A date used to compare to the present date. Several date formats are accepted, but spelling it out like this: <strong>May 10 2004</strong> is probably the simplest. A time can be include with the date like this: <strong>20:02:00 or 8:02pm</strong> . There is <strong>NO</strong> Default.") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>localtime</td>'
				.'<td>' . tra( "boolean") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Determins if Local Time is displayed or not. Passing any value in this parameter will make it <strong>TRUE</strong>. The Default = <strong>FALSE</strong> so Local Time will not be displayed") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>class</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Classname of the SPAN surrounding the countdown. The date/time segments are each wrapped in a VAR-Tag. Default = countdown") . '</td>'
			.'</tr>'			
			.'<tr class="even">'
				.'<td>punct</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Any kind of punctuation to divide the date/time segments from each other, a comma, a colon, a pipe ... Default = space. To put a non breaking space, use HTML: &amp;nbsp;") . '</td>'
			.'</tr>'			
			.'<tr class="odd">'
				.'<td>text</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Text to be displayed after the date/time string. It's wrapped in &lt;em&gt;.") . '</td>'
			.'</tr>'			
		.'</table>'
		.'<p>' . tra("Example 1: ") . '<input value="{COUNTDOWN enddate=\'8:02pm May 10 2004\' localtime=\'on\' text=\'' . tra(" - Time Passes So Slowly") . '\'}" type="text" size="40" /></p>'
		.'<p>' . tra("Example 2: ") . '<input value="{COUNTDOWN enddate=\'2012-12-22 00:01\' class=\'alert red\' punct=\', \' text=\'Purple Haze\'}" type="text" size="40" /></p>'
	;
	return $help;
}

// Load Function
function data_countdown($data,$params) {
	// The next 2 lines allow access to the $pluginParams given above
	global $gLibertySystem;
	$pluginParams = $gLibertySystem->mPlugins[PLUGIN_GUID_DATACOUNTDOWN];
	extract ($params, EXTR_SKIP);
	
	if (!isset($enddate) ) {  // The Mandatory Parameter is missing
		$ret = tra("The required parameter ") . "<strong>enddate</strong>" . tra(" was missing from the plugin ") . '<strong>"' . $pluginParams['tag'] . '"</strong>';
		$ret.= data_countdown_help();
		return $ret;
	}
	
	$then = strtotime ($enddate);
	
	if ($then == -1) { // strtotime failed so enddate was not a valid date
		$ret = tra("__Error__ - The plugin ") . '<strong>"' . $pluginParams['tag'] . '"</strong>' . tra(" was not given a valid date. The date given was:\n") . "enddate=$enddate";
		return $ret;
	}
	
	if (isset($localtime) && $localtime == 'on') {
		$tz = $_COOKIE['tz_offset'];
	} else {
		$tz = 0;
	}
	
	if (!isset($class)) {
		$class = 'countdown';
	}
	
	if (!isset($punct)) {
		$punct = " ";
	}

	if (!isset($text)) {
		$text = "";
	}

	
	$now = strtotime ("now") + $tz;
	$difference = $then - $now;
	$num = $difference/86400;
	$days = intval($num);
	$num2 = ($num - $days)*24;
	$hours = intval($num2);
	$num3 = ($num2 - $hours)*60;
	$mins = intval($num3);
	$num4 = ($num3 - $mins)*60;
	$secs = intval($num4);
	$ret = "
		<span class='".$class."'>"
		. "<var>" . $days  . " " . tra("days")    . "</var>" . $punct
		. "<var>" . $hours . " " . tra("hours")   . "</var>" . $punct
		. "<var>" . $mins  . " " . tra("minutes") . "</var>" . $punct
		. "<var>" . $secs  . " " . tra("seconds") . "</var>" . " "
		. "<em>"  . $text  . "</em>"
		. "</span>"
	;
	return $ret;
}
?>
