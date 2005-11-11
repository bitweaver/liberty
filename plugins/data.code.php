<?php
/**
 * @version  $Revision: 1.1.1.1.2.14 $
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
// $Id: data.code.php,v 1.1.1.1.2.14 2005/11/11 22:04:09 mej Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATACODE', 'datacode' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'CODE',
						'auto_activate' => TRUE,
						'requires_pair' => TRUE,
						'load_function' => 'data_code',
						'title' => 'Code',
						'help_page' => 'DataPluginCode',
						'description' => tra("Displays the Source Code Snippet between {Code} blocks."),
						'help_function' => 'data_code_help',
						'syntax' => " {CODE source= num= }". tra("Sorce Code Snippet") . "{code}",
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
				.'<td>' . tra( "Defines the format of the Source Code Snippet. Possible values are:");
	if( file_exists( UTIL_PKG_PATH.'geshi/geshi.php' ) ) {
		$help = $help . '<br /><strong>ActionScript / Ada / Apache Log File=Apache / ASM (NASM based)=Asm / ASP / Bash / C '
						.'/ C for Macs=C_Mac / AutoCAD DCL=CadDcl / AutoCAD LISP=CadLisp / C++=Cpp / C#=CSharp / CSS / D '
						.'/ Delphi / Diff Output=Diff / HTML (4.0.1)=Html4Strict / Java / JavaScript / Lisp / Lua / MatLab '
						.'/ MpAsm / NullSoft Installer=Niss / Objective C=ObjC / OpenOffice.org Basic=OoBas / Oracle8'
						.'/ Pascal / Perl / Php / Php_Brief / Python / QuickBasic=QBasic / Smarty / SQL / VisualBasic=Vb '
						.'/ VB.NET=VbNet / VHDL / VisualFoxPro / XML</strong>. ';
	} else {
		$help = $help .'HTML or PHP</strong>. ';
	}
	$help = $help . '<br />' . tra("The Default = ") . '<strong>PHP</strong></td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>num</td>'
				.'<td>' .tra( "boolean/number") .'<br />'. tra("(optional)") . '</td>'
				.'<td>' .tra( "Determins if Line Numbers are displayed with the code. Specifing:")
					.'<strong>TRUE / ON / YES /</strong> or a <strong>Number</strong> '
					.tra("will turn <strong>Line Numbering On</strong>. When a Number is specified - the Number is used for the first ")
					.tra("line instead of <strong>1</strong>. Any ohter value will turn <strong>Line Numbering OFF</strong> ")
					.tra("and only the <strong>Code</strong> will be displayed.")
					.'<br />' . tra("The Default =") .' <strong>FALSE</strong> ' .tra("Line Numbers are <strong>Not</strong> displayed.")
				.'</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{CODE source='php' num='on' }" . tra("Sorce Code Snippet") . "{code}";
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
//	$num = NULL;
	$add_tags = false;
	extract ($params, EXTR_SKIP);
	// This maintains Pre-Clyde Parameters
	if (isset($colors) and ($colors == 'php') ) $source = 'php';
	if (isset($in) ) $source = $in;
	$source = isset($source) ? strtolower($source) : 'php'; // if not specified the default is HTML
	if (isset($in)) $num = $in; // This maintains Pre-Clyde Parameters
    if (isset($num) && (!is_numeric ($num))) {
		switch (strtoupper($num)) {
		    case 'TRUE': case 'ON': case 'YES':
			    $num = 1;
				break;
			default: // could have done FALSE/OFF/NO but we want any other value to be False
			    $num = 0;
				break;
	}	}
	$num = (isset($num)) ? $num : FALSE;

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
			if (is_numeric($num)) $geshi->start_line_numbers_at($num);
		}
		$code = decodeHTML(htmlentities($geshi->parse_code()));
	} else {
		if ($num) { // Line Numbering has been requested
			$lines = explode("\n", $code);
			$code = '';
			$i = (is_numeric($num)) ? $num : 1; //Line Number
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
?>
