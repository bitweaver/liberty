<?php
/**
 * @version  $Revision: 1.17 $
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
// $Id: data.code.php,v 1.17 2006/08/05 16:21:57 squareing Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATACODE', 'datacode' );
global $gLibertySystem;
$pluginParams = array (
	'tag' => 'CODE',
	'auto_activate' => TRUE,
	'requires_pair' => TRUE,
	'load_function' => 'data_code',
	'title' => 'Code',
	'help_page' => 'DataPluginCode',
	'description' => tra("Displays the Source Code Snippet between {Code} blocks."),
	'help_function' => 'data_code_help',
	'syntax' => " {CODE source= num= }". tra("Sorce Code Snippet") . "{/code}",
	'path' => LIBERTY_PKG_PATH.'plugins/data.code.php',
	'security' => 'registered',
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
		$help = $help . '<br /><strong>ActionScript</strong> &bull; <strong>Ada</strong> &bull; Apache Log File = <strong>Apache</strong> &bull; <strong>AppleScript</strong> &bull; ASM (NASM based) = <strong>Asm</strong> &bull; <strong>ASP</strong> &bull; AutoCAD DCL = <strong>CadDcl</strong> &bull; AutoCAD LISP = <strong>CadLisp</strong> &bull; <strong>Bash</strong> &bull; <strong>BLITZ BASIC</strong> &bull; <strong>C</strong> &bull; C++ = <strong>Cpp</strong> &bull; C# = <strong>CSharp</strong> &bull; C for Macs = <strong>C_Mac</strong> &bull; <strong>CSS</strong> &bull; <strong>D</strong> &bull; <strong>Delphi</strong> &bull; Diff Output = <strong>Diff</strong> &bull; <strong>DIV</strong> &bull; <strong>DOS</strong> &bull; <strong>Eiffel</strong> &bull; <strong>FreeBasic</strong> &bull; <strong>GML</strong> &bull; HTML (4.0.1) = <strong>Html4Strict</strong> &bull; <strong>ini</strong> &bull; <strong>Inno</strong> &bull; <strong>Java</strong> &bull; <strong>JavaScript</strong> &bull; <strong>Lisp</strong> &bull; <strong>Lua</strong> &bull; <strong>MatLab</strong> &bull; <strong>MpAsm</strong> &bull; <strong>MySQL</strong> &bull; NullSoft Installer = <strong>Niss</strong> &bull; Objective C = <strong>ObjC</strong> &bull; <strong>OCaml</strong> &bull; OpenOffice.org Basic = <strong>OoBas</strong> &bull; <strong>Oracle8</strong> &bull; <strong>Pascal</strong> &bull; <strong>Perl</strong> &bull; <strong>Php</strong> &bull; <strong>Php_Brief</strong> &bull; <strong>Python</strong> &bull; QuickBasic = <strong>QBasic</strong> &bull; <strong>Ruby</strong> &bull; <strong>Scheme</strong> &bull; <strong>Smarty</strong> &bull; <strong>SQL</strong> &bull; VB.NET = <strong>VbNet</strong> &bull; <strong>VHDL</strong> &bull; <strong>Visual Basic</strong> &bull; VisualBasic = <strong>Vb</strong> &bull; <strong>VisualFoxPro</strong> &bull; <strong>XML</strong>';
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
					.tra("line instead of <strong>1</strong>. Any other value will turn <strong>Line Numbering OFF</strong> ")
					.tra("and only the <strong>Code</strong> will be displayed.")
					.'<br />' . tra("The Default =") .' <strong>FALSE</strong> ' .tra("Line Numbers are <strong>Not</strong> displayed.")
				.'</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{CODE source='php' num='on' }" . tra("Sorce Code Snippet") . "{/code}";
	return $help;
}

if( !function_exists( 'unHtmlEntities' )) { // avoid name collisions
	function unHtmlEntities($str) {
		$tTbl = get_html_translation_table(HTML_ENTITIES);
		$tTbl = array_flip($tTbl);
		return strtr($str, $tTbl);
	}
}
if( !function_exists( 'deCodeHTML' )) { // avoid name collisions
	function deCodeHTML($str) {
		$str = strtr($str, array_flip(get_html_translation_table(HTML_ENTITIES)));
		$str = preg_replace("/&#([0-9]+);/me", "chr('\\1')", $str);
		return $str;
	}
}


// Load Function
function data_code( $data, $params ) { // Pre-Clyde Changes
// Parameters were $In & $Colors
// Added testing to maintain Pre-Clyde compatability
//	$num = NULL;
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
		}
	}
	$num = (isset($num)) ? $num : FALSE;

	// trim any trailing spaces
	$code = '';
	$lines = explode("\n", $data);
	foreach ($lines as $line) {
		$code .=  rtrim($line) . "\n";
	}

	$code = unHtmlEntities( $code );

	// Trim any leading blank lines
	$code = preg_replace('/^[\n\r]+/', "",$code);
	// Trim any trailing blank lines
	if( file_exists( UTIL_PKG_PATH.'geshi/geshi.php' ) ) {
		$code = preg_replace('/[\n\r]+$/', "",$code);
	} else {
		$code = preg_replace('/[\n\r]+$/', "\n",$code);
	}

	if( file_exists( UTIL_PKG_PATH.'geshi/geshi.php' ) ) {
		// Include the GeSHi library
		include_once( UTIL_PKG_PATH.'geshi/geshi.php' );
		$geshi = new GeSHi($code, $source, UTIL_PKG_PATH.'geshi/geshi' );
		if ($num) { // Line Numbering has been requested
			$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
			if (is_numeric($num)) $geshi->start_line_numbers_at($num);
		}
		$code = deCodeHTML(htmlentities($geshi->parse_code()));
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
		switch (strtoupper($source)) { 	// I used a switch here to make it easy to expand this plugin for other kinds of source code
			case 'HTML':
				$code = highlight_string(deCodeHTML($code),true);
				if (substr($code, 0, 6) == '<code>') { // Remove the first <code>" tags
					$code = substr($code, 6, (strlen($code) - 13));
				}
				break;
			case 'PHP':
				if(!preg_match( '/^[ 0-9:]*<\?/i', $code ) ) { // Check it if code starts with PHP tags, if not: add 'em.
					$code = "<?php\n".$code."?>"; // The require these tags to function
				}
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

	return "<!--~np~-->".( !empty( $title ) ? "<p class=\"code highlight\">{$title}</p>" : "" )."<div class='codelisting'>".$code."</div><!--~/np~-->";
}
?>
