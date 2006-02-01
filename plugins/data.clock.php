<?php
// $id: data.example.php,v 1.4.2.9 2005/07/14 09:03:36 starrider Exp $
/**
 * assigned_modules
 *
 * @author     xing
 * @version    $Revision: 1.4 $
 * @package    liberty
 * @subpackage plugins_data
 * @copyright  Copyright (c) 2004, bitweaver.org
 */

/**
 * Setup Code
 */
define( 'PLUGIN_GUID_DATACLOCK', 'dataclock' );
global $gLibertySystem;
$pluginParams = array ( 
	'tag' => 'CLOCK',
	'auto_activate' => FALSE,
	'requires_pair' => FALSE,
	'load_function' => 'data_clock',
	'title' => 'Clock',
	'help_page' => 'DataPluginClock',
	'description' => "This plugin allows you to insert flexible date/time strings into your pages.",
	'help_function' => 'data_clock_help',
	'syntax' => "{clock format='%c'}",
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATACLOCK, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATACLOCK );

function data_clock_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>' . tra( "format" ) . '</td>'
				.'<td>' . tra( "string") . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "strftime-style format for clock display (default:  '%c')") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>' . tra( "timestamp" ) . '</td>'
				.'<td>' . tra( "string") . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "Any date/time expression PHP can recognize (default:  current time)") . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>' . tra( "timezone" ) . '</td>'
				.'<td>' . tra( "string") . '<br />' . tra( "(optional)" ) . '</td>'
				.'<td>' . tra( "POSIX timezone name or GMT offset (default:  current timezone)") . '</td>'
			.'</tr>'
 		.'</table>'
		. tra( "Example: " ) . "{CLOCK format='%a, %d %b %Y, %H:%M:%S %Z' timestamp='2005-07-01 03:00:30'}";
	return $help;
}

function data_clock( $data, $params ) {
	global $gBitSystem;
	$save_tz = getenv('TZ');

	if ($params['format']) {
		$format = $params['format'];
	} else {
		$format = '%c';
	}
	if ($params['timestamp']) {
		$ls = strtotime($params['timestamp']);
	} else {
		$ts = time();
	}
	if ($params['timezone']) {
		$_ENV['TZ'] = $params['timezone'];
		putenv("TZ=$_ENV[TZ]");
    }

	$result = strftime($format, $ts);
	$_ENV['TZ'] = $save_tz;
	putenv("TZ=$save_tz");

	return "<span class=\"clock\">$result</span>";
}
?>
