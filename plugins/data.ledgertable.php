<?php
// $Id: data.ledgertable.php,v 1.1.2.2 2005/11/17 23:55:55 mej Exp $
/**
 * assigned_modules
 *
 * @author   KainX <mej@kainx.org>
 * @version  $Revision: 1.1.2.2 $
 * @package  liberty
 * @subpackage plugins_data
 * @copyright Copyright (c) 2004, bitweaver.org
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * @license Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
 */
/******************
 * Initialization *
 ******************/
define( 'PLUGIN_GUID_DATALEDGERTABLE', 'dataledgertable' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'LEDGERTABLE',
	'auto_activate' => FALSE,
	'requires_pair' => TRUE,
	'load_function' => 'data_ledgertable',
	'title' => 'Ledger Table (LEDGERTABLE)',
	'help_page' => 'DataPluginLedgertable',
	'description' => tra("This Plugin creates a ledger-like table with even/odd row colors, optional top- or left-placed headers, and support for row/column spans."),
	'help_function' => 'data_ledgertable_help',
	'syntax' => "{LEDGERTABLE loc= head= }",
	'plugin_type' => DATA_PLUGIN
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATALEDGERTABLE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATALEDGERTABLE );
/*****************
 * Help Function *
 *****************/
function data_ledgertable_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>loc</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Where to display row/column headers (\"left\" or \"top\", default <strong>top</strong>).")
				.'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>head</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Header(s) separated by \"~|~\", default <strong>none</strong>")
				.'</td>'
			.'</tr>'
 		.'</table>'
		. tra("LedgerTable: ") . "{LEDGERTABLE loc=>left head=>Row1~|~Row2~|~Row3}<br />"
		. tra("This will display")
		. data_ledgertable('Example', array('loc' => 'left', 'head' => 'Row1~|~Row2~|~Row3'));
	return $help;
}
/****************
* Load Function *
 ****************/
function data_ledgertable($data, $params) {
    global $gBitSystem;

	if (empty($data)) {
		return "<!-- Error:  No data passed to LEDGERTABLE plugin. -->";
	}
	if (substr($data, 0, 1) == "\n") {
		$data = substr($data, 1);
	}

	$ret = '';
    if (isset($params['loc'])) {
		$ret .= "<!-- Header row set to $params[loc]. -->";
		$plugdata_loc = $params['loc'];
	} else {
		$ret .= "<!-- Defaulting header row to top. -->";
        $plugdata_loc = 'top';
    }
    if (isset($params['head'])) {
		$ret .= "<!-- Got headers. -->";
		$plugdata_head = $params['head'];
    } else {
		$ret .= "<!-- No headers specified. -->";
    }
    if (isset($params['width'])) {
		$ret .= "<!-- Got width $params[width]. -->";
		$plugdata_width = " style=\"width: " . $params['width'] . '"';
    } else {
		$plugdata_width = "";
    }

    $ret .= "<table class=\"ledgertable\"$plugdata_width>";

    if (isset($plugdata_head)) {
        $headers = explode('~|~', $plugdata_head);
        if ($plugdata_loc == 'top') {
            $ret .= "    <!-- Placing header row on top. -->";
            $ret .= "    <tr class=\"ledgertable header row\">";
            foreach ($headers as $hdr) {
                $ret .= "        <th class=\"header highlight\">$hdr</td>";
            }
            $ret .= "  </tr>";
		}
	}

    $lines = split("\n", $data);
    $line_count = 0;
    foreach ($lines as $line) {
        $line = trim($line);
        if (strlen($line) <= 0) {
            continue;
        }
        $line_count++;

        $ret .= "    <!-- Displaying row $line_count. -->";
        $ret .= "    <tr class=\"" . (($line_count % 2) ? ("odd") : ("even")) . "\">";
        if (isset($plugdata_head) && ($plugdata_loc == "left")) {
            $ret .= "        <!-- Placing header on left. -->";
            $ret .= "        <th class=\"header highlight\"";
            $header = array_shift($headers);
            if (preg_match("/^~(row|col)span:(\d+)~(.*)$/", $header, $matches)) {
                $ret .= " $matches[1]span=\"$matches[2]\"";
                $header = $matches[3];
            }
            $ret .= ">$header</td>";
        }
        $cells = explode("~|~", $line);
        foreach ($cells as $col) {
            $ret .= "        <td class=\"" . (($line_count % 2) ? ("odd") : ("even")) . "\"";
            $col = trim($col);
            if (!strcmp($col, "~blank~")) {
                $col = "&nbsp;";
            } else if (preg_match("/^~(row|col)span:(\d+)~(.*)$/", $col, $matches)) {
                $ret .= " $matches[1]span=\"$matches[2]\"";
                $col = $matches[3];
            }
            $ret .= ">$col</td>";
        }
        $ret .= "    </tr>";
    }
    $ret .= "</table>";

    return $ret;
}
?>
