<?php
// $Id: data.ledgertable.php,v 1.1.2.1 2005/11/11 22:04:09 mej Exp $
/**
 * assigned_modules
 *
 * @author   KainX <mej@kainx.org>
 * @version  $Revision: 1.1.2.1 $
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
	'requires_pair' => FALSE,
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
				.'<td>' . tra( "Where to display row/column headers (\"left\" or \"top\").")
					.'<br />' . tra( "The Default = <strong>top</strong>")
				.'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>head</td>'
				.'<td>' . tra( "string") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Header(s) separated by \"~|~\".")
					.'<br />' . tra( "The Default =") . ' <strong>none</strong> '
				.'</td>'
			.'</tr>'
 		.'</table>'
		. tra("LedgerTable: ") . "{LEDGERTABLE(loc=>left,head=>Row1~|~Row2~|~Row3)}<br />"
		. tra("This will display");
	return $help;
}
/****************
* Load Function *
 ****************/
function data_ledgertable($data, $params) {
    global $gBitSystem;

	extract($params, EXTR_PREFIX_ALL, "plugdata_");
    if (!isset($plugdata_loc)) {
        $plugdata_loc = "top";
    }

    $ret = "\n<table class=\"normal\">\n";

    if (isset($plugdata_head)) {
        $headers = explode("~|~", $plugdata_head);
        if ($plugdata_loc == "top") {
            $ret .= "    <!-- Placing header row on top. -->\n";
            $ret .= "    <tr>\n";
            foreach ($headers as $hdr) {
                $ret .= "        <td class=\"heading\">$hdr</td>\n";
            }
            $ret .= "  </tr>\n";
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

        $ret .= "    <!-- Displaying row $line_count. -->\n";
        $ret .= "    <tr class=\"" . (($line_count % 2) ? ("odd") : ("even")) . "\">\n";
        if (isset($plugdata_head) && ($plugdata_loc == "left")) {
            $ret .= "        <!-- Placing header on left. -->\n";
            $ret .= "        <td class=\"heading\"";
            $header = array_shift($headers);
            if (preg_match("/^~(row|col)span:(\d+)~(.*)$/", $header, $matches)) {
                $ret .= " $matches[1]span=\"$matches[2]\"";
                $header = $matches[3];
            }
            $ret .= ">$header</td>\n";
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
            $ret .= ">$col</td>\n";
        }
        $ret .= "    </tr>\n";
    }
    $ret .= "</table>\n\n";

    return $ret;
}
?>
