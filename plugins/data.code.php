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
// | Author (TikiWiki): Luis Argerich <lrargerich@users.sourceforge.net>
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up) 
// | by: StarRider <starrrider@users.sourceforge.net>
// | Reworked from: wikiplugin_code.php - see deprecated code below
// +----------------------------------------------------------------------+
// $Id: data.code.php,v 1.1.1.1.2.2 2005/06/25 09:29:25 squareing Exp $
// Initialization
define( 'PLUGIN_GUID_DATACODE', 'datacode' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'CODE',
						'auto_activate' => TRUE,
						'requires_pair' => TRUE,
						'load_function' => 'data_code',
						'title' => 'Code',
						'description' => tra("Displays the Source Code Snippet between {Code} blocks."),
						'help_function' => 'data_code_help',
						'syntax' => " {code source= num= }". tra("Sorce Code Snippet") . "{code}",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATACODE, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATACODE );

// Help Function
function data_code_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>source</td>'
				.'<td>' . tra( "key-word") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Defines the format of the Source Code Snippet. Possible values are:") . ' <strong>HTML or PHP</strong>. '
				. tra("The Default = ") . '<strong>HTML</strong></td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>num</td>'
				.'<td>' . tra( "boolean") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Determins if line numbers are displayed. Passing") . ' <strong>TRUE, ON, or YES</strong> ' . tra("in this parameter will make it") . ' <strong>TRUE</strong>. ' . tra("Any ohter value will make it") . ' <strong>FALSE</strong>' . tra("The Default =") . ' <strong>FALSE</strong> ' . tra("so Line Numbers are not displayed.") . '</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{code source='php' num='on' }" . tra("Sorce Code Snippet") . "{code}";
	return $help;
}

function decodeHTML($string) {
    $string = strtr($string, array_flip(get_html_translation_table(HTML_ENTITIES)));
    $string = preg_replace("/&#([0-9]+);/me", "chr('\\1')", $string);
    return $string;
}

// Load Function
function data_code( $data, $params ) { // Pre-Clyde Changes
// Parameters were $In & $Colors
// Added testing to maintain Pre-Clyde compatability
	$num = NULL;
	extract ($params);
	// This maintains Pre-Clyde Parameters
	if (isset($colors) and ($colors == 'php') ) $source = 'HTML';
	if (isset($in) ) $source = $in;
	$source = isset($source) ? strtoupper($source) : 'HTML'; // if not specified the default is HTML
	if (isset($in) and ($in == 1) ) $num = 'ON'; // This maintains Pre-Clyde Parameters
	switch (strtoupper($num)) {
	    case 'TRUE': case 'ON': case 'YES':
		    $num = 1;
			break;
		default: // could have done FALSE/OFF/NO but we want any other value to be False
		    $num = 0;
			break;
	}
	$code = ''; // Lets make it pretty by eliminating all empty lines
	$lines = explode("\n", $data);
	foreach ($lines as $line) {
		if (strlen($line) > 1)
			$code .=  rtrim($line) . "\n"; // The Strings length is > 1
	}
	if( file_exists( UTIL_PKG_PATH.'geshi/geshi.php' ) ) {
		// Include the GeSHi library
		include_once( UTIL_PKG_PATH.'geshi/geshi.php' );
		$geshi = new GeSHi($code, $source, UTIL_PKG_PATH.'geshi/geshi' );
		if ($num) { // Line Numbering has been requested
			$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
		}
		$code = $geshi->parse_code();
	} else {
		if ($num) { // Line Numbering has been requested
			$lines = explode("\n", $code);
			$code = '';
			$i = 1; // The current line number
			foreach ($lines as $line) {
				if (strlen($line) > 1) {
				$code .= sprintf("%3d", $i) . ": " . $line . "\n";
				$i++;
				}
			}
		}
		if( preg_match( '/php/i', $source ) && substr($code, 0, 2) != '<?') { // Check it if code starts with PHP tags, if not: add 'em.
			$code = "<?php\n".$code."\n?>"; // The require these tags to function
			$add_tags = true;
		}
	// To Here
		switch ($source) { 	// I used a switch here to make it easy to expand this plugin for other kinds of source code
			case 'HTML':
				$code = highlight_string(decodeHTML($code),true);
				if (substr($code, 0, 6) == '<code>') // Remove the first <code>" tags
					$code = substr($code, 6, (strlen($code) - 13));
				if ($add_tags) { //strip the PHP tags if they were added by the script
					if ($num) { // Line Numbering has been added
						$code = substr($code, 50, (strlen($code) -125));
					} else {
						$code = substr($code, 63, (strlen($code) -125));
					}
				}
				break;
			case 'PHP':
			   $code = highlight_string($code, true);
	/*
				SPIDERKILL this code was not properly checking and doing the right stuff. just removed for now
			   if (substr($code, 0, 6) == '<code>') // Remove the first <code>" tags
				   $code = substr($code, 6, (strlen($code) - 13));
				if ($add_tags) { //strip the PHP tags if they were added by the script
					if ($num) { // Line Numbering has been added
						$code = substr($code, 50, (strlen($code) -125));
					} else {
						$code = substr($code, 63, (strlen($code) -125));
					}
				}
	*/
			   $convmap = array( // Replacement-map to replace Colors
					'#000000">' => '#004A4A">', // The Default Color
					'#006600">' => '#2020FF">', // Color for Functions/Variables/Numbers/&/Constants
					'#0000CC">' => '#209020">', // Color for KeyWords
					'#FF9900">' => '#BB4040">', // Color for Constants
					'#CC0000">' => '#903030">' // Color for Strings
				);// <-- # Assigned by HighLight_String / --> # Color to be Displayed
	// NOTE: The colors assigned by HighLight_String have changed with different versions of PHP - these are for PHP 4.3.4
				$code = strtr($code, $convmap); // Change the Colors
				break;
			default:
				$code = highlight_string( $code, true );
				break;
		}
		$code = "<pre>$code</pre>";
	}
    return "~np~<div class='codelisting'>".unhtmlentities( $code )."</div>~/np~";
}

function unhtmlentities($string) {
	$trans_tbl = get_html_translation_table(HTML_ENTITIES);
	$trans_tbl = array_flip($trans_tbl);
	return strtr($string, $trans_tbl);
}

/******************************************************************************
The code below is from the deprecated CODE plugin. All comments and the help routines have been removed. - StarRider

function wikiplugin_code($data, $params) {
	$code = $data;
	extract ($params);
	if (isset($colors) and ($colors == 'php')) {
		$data = "<div class='codelisting'><pre>".highlight_string(decodeHTML($code),1)."</pre></div>";
	} else {
		if (isset($in) && $in == 1) {
			$lines = explode("\n", $code);
			$i = 1; // The current line number
			$code = '';
			// This will skip leading and trailing empty lines to make snippet look better :)
			$fl = 0; // first code line printed' flag
			$ae = '';
			foreach ($lines as $line) {
				$len = strlen($line);
				if (!($len || $fl))	continue; // skip leading empty lines
				if ($len) {	// OK len >0
					$code .= $ae . ($fl ? "\n" : '') . sprintf("%3d", $i). ':  ' . $line;
					$fl = 1; // first line already printed
					$ae = '';
				} else {
					$ae .= "\n" . sprintf("%3d", $i). ':  ' . $line;
				}
				$i++;
			}
			$code = rtrim($code);
		}
		$data = "<div class='codelisting'><pre>" . $code . "</pre></div>";
	}
	return $data;
}
*/
?>
