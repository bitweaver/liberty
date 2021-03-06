<?php
/**
 * @version  $Revision$
 * @package  liberty
 * @subpackage plugins_data
 */
// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See below for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Author (TikiWiki): Luis Argerich <lrargerich@users.sourceforge.net>
// | Reworked for Bitweaver (& Undoubtedly Screwed-Up)
// | by: StarRider <starrrider@users.sourceforge.net>
// +----------------------------------------------------------------------+
// $Id$

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATACODE', 'datacode' );
global $gLibertySystem;
$pluginParams = array (
	'tag'                 => 'CODE',
	'auto_activate'       => TRUE,
	'requires_pair'       => TRUE,
	'load_function'       => 'data_code',
	'title'               => 'Code',
	'help_page'           => 'DataPluginCode',
	'description'         => tra( "Displays the Source Code Snippet between {code} blocks." ),
	'help_function'       => 'data_code_help',
	'syntax'              => "{code source= num= }". tra( "Sorce Code Snippet" ) . "{/code}",
	'plugin_type'         => DATA_PLUGIN,
	'plugin_settings_url' => LIBERTY_PKG_URL.'admin/plugins/data_code.php',
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
	if( file_exists( UTIL_PKG_INCLUDE_PATH.'geshi/geshi.php' ) ) {
		$help = $help . '<br />
			<strong>ActionScript</strong> &bull;
			<strong>Ada</strong> &bull;
			Apache Log File = <strong>Apache</strong> &bull;
			<strong>AppleScript</strong> &bull;
			ASM (NASM based) = <strong>Asm</strong> &bull;
			<strong>ASP</strong> &bull;
			AutoCAD DCL = <strong>CadDcl</strong> &bull;
			AutoCAD LISP = <strong>CadLisp</strong> &bull;
			<strong>Bash</strong> &bull;
			<strong>BLITZ BASIC</strong> &bull;
			<strong>C</strong> &bull;
			C++ = <strong>Cpp</strong> &bull;
			C# = <strong>CSharp</strong> &bull;
			C for Macs = <strong>C_Mac</strong> &bull;
			<strong>CSS</strong> &bull;
			<strong>D</strong> &bull;
			<strong>Delphi</strong> &bull;
			Diff Output = <strong>Diff</strong> &bull;
			<strong>DIV</strong> &bull;
			<strong>DOS</strong> &bull;
			<strong>Eiffel</strong> &bull;
			<strong>FreeBasic</strong> &bull;
			<strong>GML</strong> &bull;
			HTML (4.0.1) = <strong>Html4Strict</strong> &bull;
			<strong>ini</strong> &bull;
			<strong>Inno</strong> &bull;
			<strong>Java</strong> &bull;
			<strong>JavaScript</strong> &bull;
			<strong>Lisp</strong> &bull;
			<strong>Lua</strong> &bull;
			<strong>MatLab</strong> &bull;
			<strong>MpAsm</strong> &bull;
			<strong>MySQL</strong> &bull;
			NullSoft Installer = <strong>Niss</strong> &bull;
			Objective C = <strong>ObjC</strong> &bull;
			<strong>OCaml</strong> &bull;
			OpenOffice.org Basic = <strong>OoBas</strong> &bull;
			<strong>Oracle8</strong> &bull;
			<strong>Pascal</strong> &bull;
			<strong>Perl</strong> &bull;
			<strong>Php</strong> &bull;
			<strong>Php_Brief</strong> &bull;
			<strong>Python</strong> &bull;
			QuickBasic = <strong>QBasic</strong> &bull;
			<strong>Ruby</strong> &bull;
			<strong>Scheme</strong> &bull;
			<strong>Smarty</strong> &bull;
			<strong>SQL</strong> &bull;
			VB.NET = <strong>VbNet</strong> &bull;
			<strong>VHDL</strong> &bull;
			<strong>Visual Basic</strong> &bull;
			VisualBasic = <strong>Vb</strong> &bull;
			<strong>VisualFoxPro</strong> &bull;
			<strong>XML</strong>';
	} else {
		$help = $help .'<strong>HTML</strong> or <strong>PHP</strong>. ';
	}
	$help = $help . '<br />' . tra("The Default = ") . '<strong>PHP</strong></td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>title</td>'
				.'<td>'.tra( "string").'<br />'.tra("(optional)").'</td>'
				.'<td>'.tra( "Give the codelisting a title.").'</td>'
			.'</tr>'
			.'<tr class="odd">'
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
		. tra("Example: ") . "{code source='php' num='on'}" . tra("Sorce Code Snippet") . "{/code}";
	return $help;
}

if( !function_exists( 'unHtmlEntities' )) { // avoid name collisions
	function unHtmlEntities( $pStr ) {
		$tTbl = get_html_translation_table( HTML_ENTITIES );
		$tTbl = array_flip( $tTbl );
		return strtr( $pStr, $tTbl );
	}
}

if( !function_exists( 'deCodeHTML' )) { // avoid name collisions
	function deCodeHTML( $pStr ) {
		$pStr = strtr( $pStr, array_flip( get_html_translation_table( HTML_ENTITIES )));
		$pStr = preg_replace_callback( "/&#([0-9]+);/m", function($matches){ foreach($matches as $match){ return chr($match); } }, $pStr );
		return $pStr;
	}
}


// Load Function
function data_code( $pData, $pParams ) { // Pre-Clyde Changes
	global $gBitSystem;
	extract( $pParams, EXTR_SKIP );

	if( !empty( $colors ) and ( $colors == 'php' )) {
		$source = 'php';
	}

	if( !empty( $in )) {
		$source = $in;
	}

	$source = isset( $source ) ? strtolower( $source ) : $gBitSystem->getConfig( 'liberty_plugin_code_default_source', 'php' );

	if( !empty( $num ) && !is_numeric( $num )) {
		switch( strtoupper( $num )) {
		case 'TRUE': case 'ON': case 'YES':
			$num = 1;
			break;
		default:
			$num = 0;
			break;
		}
	}
	$num = ( isset( $num )) ? $num : FALSE;

	// trim any trailing spaces
	$code = '';
	$lines = explode( "\n", $pData );
	foreach( $lines as $line ) {
		$code .=  rtrim( $line )."\n";
	}

	$code = unHtmlEntities( $code );

	// Trim any leading blank lines
	$code = preg_replace( '/^[\n\r]+/', "", $code );
	// Trim any trailing blank lines
	if( file_exists( UTIL_PKG_INCLUDE_PATH.'geshi/geshi.php' )) {
		$code = preg_replace('/[\n\r]+$/', "", $code );
	} else {
		$code = preg_replace('/[\n\r]+$/', "\n", $code );
	}

	if( file_exists( UTIL_PKG_INCLUDE_PATH.'geshi/geshi.php' ) ) {
		// Include the GeSHi library
		include_once( UTIL_PKG_INCLUDE_PATH.'geshi/geshi.php' );
		$geshi = new GeSHi($code, $source, UTIL_PKG_INCLUDE_PATH.'geshi/geshi' );
		if( $num ) { // Line Numbering has been requested
			$geshi->enable_line_numbers( GESHI_FANCY_LINE_NUMBERS );
			if( is_numeric( $num )) {
				$geshi->start_line_numbers_at( $num );
			}
		}
		$code = deCodeHTML( htmlentities( $geshi->parse_code() ));
	} else {
		// Line Numbering has been requested
		if( $num ) {
			$lines = explode( "\n", $code );
			$code = '';
			//Line Number
			$i = ( is_numeric( $num )) ? $num : 1;
			foreach( $lines as $line ) {
				if( strlen( $line ) > 1 ) {
					$code .= sprintf( "%3d", $i ).": ".$line."\n";
					$i++;
				}
			}
		}

		switch( strtoupper( $source )) {
			case 'HTML':
				$code = highlight_string( deCodeHTML( $code ), TRUE );
				// Remove the first <code>" tags
				if( substr( $code, 0, 6 ) == '<code>') {
					$code = substr( $code, 6, ( strlen( $code ) - 13 ));
				}
				break;
			case 'PHP':
				// Check it if code starts with PHP tags, if not: add 'em.
				if( !preg_match( '/^[ 0-9:]*<\?/i', $code )) {
					// The require these tags to function
					$code = "<?php\n".$code."?>";
				}
				$code = highlight_string( $code, TRUE );
				// Replacement-map to replace Colors
				$convmap = array(
					// The Default Color
					'#000000">' => '#004A4A">',
					// Color for Functions/Variables/Numbers/&/Constants
					'#006600">' => '#2020FF">',
					// Color for KeyWords
					'#0000CC">' => '#209020">',
					// Color for Constants
					'#FF9900">' => '#BB4040">',
					// Color for Strings
					'#CC0000">' => '#903030">'
				);
				// <-- # Assigned by HighLight_String / --> # Color to be Displayed
				// NOTE: The colors assigned by HighLight_String have changed with different versions of PHP - these are for PHP 4.3.4
				// Change the Colors
				$code = strtr( $code, $convmap );
				break;
			default:
				$code = highlight_string( $code, TRUE );
				break;
		}

		$code = "<pre>$code</pre>";
	}

	return ( !empty( $title ) ? '<p class="codetitle">'.$title.'</p>' : "" )."<div class='codelisting'>".$code."</div>";
}
?>
