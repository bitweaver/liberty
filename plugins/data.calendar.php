<?php
/**
 * @version  $Revision: 1.4 $
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
// | Author (TikiWiki): Luis Argerich <lrargerich@users.sourceforge.net>
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up)
// | by: StarRider <starrrider@users.sourceforge.net>
// +----------------------------------------------------------------------+
// $Id: data.calendar.php,v 1.4 2008/02/14 18:51:49 bitweaver Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATACALENDAR', 'datacalendar' );
global $gLibertySystem;
$pluginParams = array (
	'tag' => 'CALENDAR',
	'auto_activate' => TRUE,
	'requires_pair' => FALSE,
	'load_function' => 'data_calendar',
	'title' => 'Calendar',
	'help_page' => 'DataPluginCalendar',
	'description' => tra("Displays a mini calendar that links to the calendar package."),	
	'syntax' => " {CALENDAR} ",
	'path' => LIBERTY_PKG_PATH.'plugins/data.calendar.php',
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATACALENDAR, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATACALENDAR );

function data_calendar( $data, $params ) {
	 global $gBitSmarty, $gBitSystem;

	 if ($gBitSystem->isPackageActive('calendar')) {
		 $offset = $gBitSystem->get_display_offset();
		 $bitDate = new BitDate($offset);	
		 
		 $time = $bitDate->getUTCTime();
		 $date = $bitDate->getDate($time, true);
		 
		 $month = $date['mon'];
		 $year = $date['year'];
		 $month_day = $date['mday'];
		 $month_name = $date['month'];
		 
		 // reset time so we can make today look different in template with compare
		 $time = $bitDate->mktime(0, 0, 0, $month, $month_day, $year);
		 
		 $last_time = $bitDate->mktime(0, 0, 0, $month, 0, $year);
		 $next_time = $bitDate->mktime(0, 0, 0, $month + 1, 1, $year);
		 $last = $bitDate->getDate($last_time);
		 $next = $bitDate->getDate($next_time);
		 
		 $days = array();
		 for ($i = 2; $i < 9; $i++) {
			 // Start from known sunday.
			 $timestamp = $bitDate->mktime(0, 0, 0, 1, $i, 2000);
			 $days[] = $bitDate->strftime('%a', $timestamp);
		 }
		 
		 // Build a two-dimensional array of UNIX timestamps.
		 $calendar = array();
		 
		 // Start with last days of previous month.
		 $week = array();
		 $month_begin = $bitDate->mktime(0, 0, 0, $month, 1, $year);
		 $month_begin_dow = strftime('%w', $month_begin);
		 
		 $days_last_month = $bitDate->daysInMonth($last['month'], $last['year']);
		 for ($dow = 0;
			  $dow < $month_begin_dow;
			  $dow++) {
			 $day = $days_last_month - $month_begin_dow + $dow;
			 $d['time'] = $bitDate->mktime(0, 0, 0, $month - 1, $day, $year);
			 $d['dim'] = true;
			 $week[] = $d;
		 }
		 
		 // Do this month
		 $days_in_month = $bitDate->daysInMonth($month, $year);
		 for ($i = 1; $i <= $days_in_month; $i++) {
			 if ($dow == 7) {
				 $calendar[] = $week;
				 
				 // Done with row
				 $dow = 0;
				 unset($week);
				 $week = array();
			 }
			 $d['time'] = $bitDate->mktime(0, 0, 0, $month, $i, $year);
			 $d['dim'] = false;
			 // Flag today
			 if ($i == $month_day) {
				 $d['today'] = true;
			 }
			 $week[] = $d;
			 unset($d['today']);
			 $dow++;
		}
		 
		 // Do the last month.
		 for ($i = 1; $dow < 7; $i++, $dow++) {
			 $d['time'] = $bitDate->mktime(0, 0, 0, $month + 1, $i, $year);
			 $d['dim'] = true;
			 $week[] = $d;
		 }
		 $calendar[] = $week;

		 $gBitSmarty->assign('minical', true);
		 $gBitSmarty->assign('month_name', $month_name);
		 $gBitSmarty->assign('month', $month);
		 $gBitSmarty->assign('year', $year);
		 $gBitSmarty->assign('last_month', $last_time);
		 $gBitSmarty->assign('next_month', $next_time);
		 $gBitSmarty->assign('dow_abbrevs', $days);
		 $gBitSmarty->assign('calendar', $calendar);
		 $gBitSmarty->assign('today', $time);

		 // Assign a base url
		 if (empty($params['events'])) {
			 $pBaseUrl = CALENDAR_PKG_URL.'index.php';
		 }
		 else {
			 $pBaseUrl = EVENTS_PKG_URL.'calendar.php';
		 }
		 $gBitSmarty->assign('baseCalendarUrl', $pBaseUrl);

		 return $gBitSmarty->fetch('bitpackage:calendar/minical.tpl');
	 }
	 
	 return '<div class="error">Calendar Package Not Active</div>';
}
?>
