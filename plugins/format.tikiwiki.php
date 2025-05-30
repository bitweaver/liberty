<?php
/**
 * @version  $Revision$
 * @package  liberty
 * @subpackage plugins_format
 */
global $gLibertySystem;

/**
 * definitions
 */
define( 'PLUGIN_GUID_TIKIWIKI', 'tikiwiki' );

$pluginParams = array (
	'auto_activate'    => TRUE,
	'store_function'   => 'tikiwiki_save_data',
	'load_function'    => 'tikiwiki_parse_data',
	'verify_function'  => 'tikiwiki_verify_data',
	'description'      => 'TikiWiki Syntax Format Parser',
	'edit_label'       => 'Tiki Wiki Syntax',
	'edit_field'       => PLUGIN_GUID_TIKIWIKI,
	'help_page'        => 'TikiWikiSyntax',
	'plugin_type'      => FORMAT_PLUGIN,
	'linebreak'        => "\r\n"
);

$gLibertySystem->registerPlugin( PLUGIN_GUID_TIKIWIKI, $pluginParams );

/**
 * tikiwiki_save_data
 */
function tikiwiki_save_data( &$pParamHash ) {
	static $parser;
	if( empty( $parser ) ) {
		$parser = new TikiWikiParser();
	}
}

function tikiwiki_verify_data( &$pParamHash ) {
	$errorMsg = NULL;
	$pParamHash['content_store']['data'] = $pParamHash['edit'];
	return( $errorMsg );
}

function tikiwiki_parse_data( &$pParseHash, &$pCommonObject ) {
	global $gBitSystem;
	$ret = '';

	static $parser;
	if( empty( $parser ) ) {
		$parser = new TikiWikiParser();
	}
	$ret = $parser->parseData( $pParseHash, $pCommonObject );

	return $ret;
}

/**
 * TikiWikiParser
 *
 * @package kernel
 */
class TikiWikiParser extends BitBase {
	function TikiWikiParser() {
		parent::__construct();
	}

	// This function handles wiki codes for those special HTML characters
	// that textarea won't leave alone.
	function parseHtmlchar( &$pData ) {
		// cleaning some user input
		$pData = preg_replace( "/&(?!([a-z]{1,7};))/", "&amp;", $pData );

		// oft-used characters (case insensitive)
		$patterns = array(
			"~bull~" => "&bull;",
			"~bs~"   => "&#92;",
			"~hs~"   => "&nbsp;",
			"~amp~"  => "&amp;",
			"~ldq~"  => "&ldquo;",
			"~rdq~"  => "&rdquo;",
			"~lsq~"  => "&lsquo;",
			"~rsq~"  => "&rsquo;",
			"~copy~" => "&copy;",
			"~c~"    => "&copy;",
			"~--~"   => "&mdash;",
			" -- "   => " &mdash; ",
			"~lt~"   => "&lt;",
			"~gt~"   => "&gt;",
			"~euro~" => "&euro;",
		);

		foreach( $patterns as $pattern => $replace ) {
			$pData = str_ireplace( $pattern, $replace, $pData );
		}

		// add an easy method to clear floats
		$pData = preg_replace( "/(\r|\n)?~clear~/i", '<br style="clear:both;" />', $pData );

		// HTML numeric character entities
		$pData = preg_replace( "/~([0-9]+)~/", "&#$1;", $pData );
	}

	function getLinks( $pData ) {
		$links = array();

		// Match things like [...], but ignore things like [[foo].
		// -Robin
		if( preg_match_all( "/(?<!\[)\[([^\[\|\]]+)(\||\])/", $pData, $r1 )) {
			$links = array_unique( $r1[1] );
		}

		return $links;
	}

	function howManyAtStart($str, $car) {
		$cant = 0;
		$i = 0;
		while (($i < strlen($str)) && (isset($str[$i])) && ($str[$i]== $car)) {
			$i++;
			$cant++;
		}
		return $cant;
	}

	function parseMediawikiTables( $pData ) {
		// Find all matches to {|...|} with no {| inside.
		while( preg_match( '/\n?\{\|(.*?)\n\|\}/sm', $pData, $matches )) {
			$table_data = str_replace( "\r", "", $matches[1] );
			$table_data = str_replace( '||', "\n|", $table_data );

			// get all instances where put in info like: background=blue and convert it to background="blue"
			$xhtmlfix['pattern'] = "!=([^'\"][^\s]*)!";
			$xhtmlfix['replace'] = '="$1"';

			while( preg_match('/^![^!]+!!/m', $table_data )) {
				/* Replace !! with \n! but ONLY in !-defined header rows. */
				$table_data = preg_replace( '/^!([^!]+)!!/m', "!$1\n!", $table_data );
			}

			if( substr( $table_data, 0, 1 ) != "\n" ) {
				// We have table parameters.
				list( $table_params, $table_data ) = explode( "\n", $table_data, 2 );
				$table_params = preg_replace( $xhtmlfix['pattern'], $xhtmlfix['replace'], trim( $table_params ));
				/* TODO: This attempt to support foo:bar table params needs help!
				if (strlen($table_params)) {
					$table_params = preg_replace("/\b(\w+):/", '$1=', $table_params);
				}
				*/
			} else {
				$table_params = '';
			}

			// apply default class if no other class has been set
			if( !empty( $table_params ) && strpos( 'class=', $table_params ) !== FALSE ) {
				$table_params .= ' class="table"';
			}
			$content = "<table $table_params>";

			$lines = explode( "\n", str_replace( "\\n", "<br />", $table_data ));
			$row = 0;
			foreach( $lines as $line ) {
				if(( substr( $line, 0, 1 ) == '|' ) || ( substr( $line, 0, 1 ) == '!' )) {
					if( preg_match( '/^\|\+\s*(.+)$/', $line, $row_matches )) {
						$content .= "<caption>$row_matches[1]</caption>";
					} elseif( preg_match( '/^\|-\s*(.+)?$/', $line, $row_matches )) {
						if( $row ) {
							$content .= '</tr>';
							$row++;
						} else {
							$row = 1;
						}

						if( !empty( $row_matches[1] )) {
							$row_matches[1] = preg_replace( $xhtmlfix['pattern'], $xhtmlfix['replace'], trim( $row_matches[1] ));
							$content .= "<tr {$row_matches[1]}>";
						} else {
							$content .= '<tr>';
						}
					} elseif( preg_match( '/^([\|!])\s*([^\|]+\s*\|)?\s*(.*)$/', $line, $row_matches )) {
						if( !$row ) {
							$content .= '<tr>';
							$row = 1;
						}

						if( !empty( $row_matches[2] )) {
							$row_matches[2] = preg_replace( $xhtmlfix['pattern'], $xhtmlfix['replace'], trim( $row_matches[2] ));
						}

						$td       = 't'.(( $row_matches[1] == '!' ) ? 'h' : 'd' );
						$content .= "<$td".(( !empty( $row_matches[2] )) ? ' '.trim( substr( $row_matches[2], 0, -1 )) : '' ).'>'.$row_matches[3]."</$td>";
					} else {
						$content .= "<!-- ERROR:  Ignoring invalid line \"$line\" -->";
					}
				} else {
					$content .= "<!-- ERROR:  Ignoring invalid line \"$line\" -->";
				}
			}
			$content .= '</tr></table>';
			$pData = str_replace( $matches[0], $content, $pData );
		}
		return $pData;
	}

	function parseData( $pParseHash, &$pCommonObject ) {
		global $gBitSystem, $gLibertySystem, $gBitUser, $page;

		$data      = $pParseHash['data'];
		$contentId = $pParseHash['content_id'];

		// this is used for setting the links when section editing is enabled
		$section_count = 1;

		if( $gBitSystem->isPackageActive( 'wiki' ) ) {
			// force wiki page to load
			global $gLibertySystem;
			$gLibertySystem->getContentClassName( 'bitpage' );
		}

		
		// only strip out html if needed
		if( $gBitSystem->isFeatureActive( 'content_allow_html' ) || $gBitSystem->isFeatureActive( 'content_force_allow_html' )) {
			// we allow html unconditionally with this parser
		} else {
			// we are parsing this page and we either have no way of checking permissions or we have no need for html
			$data = htmlspecialchars( $data, ENT_NOQUOTES, 'UTF-8' );
		}

		// Extract [link] sections (to be re-inserted later)
		$noparsedlinks = array();

		// This section matches [...].
		// Added handling for [[foo] sections.  -rlpowell
		preg_match_all("/(?<!\[)\[([^\[][^\]]+)\]/", $data, $noparseurl);

		foreach (array_unique($noparseurl[1])as $np) {
			$key = md5(BitSystem::genPass());

			$aux["key"] = $key;
			$aux["data"] = $np;
			$noparsedlinks[] = $aux;
			$data = str_replace("$np", $key, $data);
		}

		// Replace special characters
		//done after url catching because otherwise urls of dyn. sites will be modified
		$this->parseHtmlchar( $data );

		//$data = strip_tags($data);
		// BiDi markers
		$bidiCount = 0;
		$bidiCount = preg_match_all("/(\{l2r\})/", $data, $pages);
		$bidiCount += preg_match_all("/(\{r2l\})/", $data, $pages);

		$data = preg_replace("/\{l2r\}/", "<div dir='ltr'>", $data);
		$data = preg_replace("/\{r2l\}/", "<div dir='rtl'>", $data);
		$data = preg_replace("/\{lm\}/", "&lrm;", $data);
		$data = preg_replace("/\{rm\}/", "&rlm;", $data);

		// Parse MediaWiki-style pipe syntax tables.
		if(( strpos( $data, "{|" ) === 0 || strpos( $data, "\n{|" ) !== FALSE ) && strpos( $data, "\n|}" ) !== FALSE ) {
			$data = $this->parseMediawikiTables($data);
		}

		// ============================================= this should go - xing
		// Replace dynamic variables
		// Dynamic variables are similar to dynamic content but they are editable
		// from the page directly, intended for short data, not long text but text
		// will work too
		//     Now won't match HTML-style '%nn' letter codes.
/*
		if (preg_match_all("/%([^% 0-9][^% 0-9][^% ]*)%/",$data,$dvars)) {
			// remove repeated elements
			$dvars = array_unique($dvars[1]);
			// Now replace each dynamic variable by a pair composed of the
			// variable value and a text field to edit the variable. Each
			foreach($dvars as $dvar) {
				$query = "select `data` from `".BIT_DB_PREFIX."liberty_dynamic_variables` where `name`=?";
				$result = $this->mDb->query($query,Array($dvar));
				if($result->numRows()) {
					$value = $result->fetchRow();
					$value = $value["data"];
				} else {
					//Default value is NULL
					$value = "NaV";
				}
				// Now build 2 divs
				$id = 'dyn_'.$dvar;

				if( $gBitUser->hasPermission( 'p_wiki_edit_dynvar' ) ) {
					$span1 = "<span  style='display:inline;' id='dyn_".$dvar."_display'><a class='dynavar' onclick='javascript:toggle_dynamic_var(\"$dvar\");' title='".tra('Click to edit dynamic variable').": $dvar'>$value</a></span>";
					$span2 = "<span style='display:none;' id='dyn_".$dvar."_edit'><input type='text' name='dyn_".$dvar."' value='".$value."' /></span>";
				} else {
					$span1 = "<span class='dynavar' style='display:inline;' id='dyn_".$dvar."_display'>$value</span>";
					$span2 = '';
				}
				$html = $span1.$span2;
				//It's important to replace only once
				$dvar_preg = preg_quote( $dvar );
				$data = preg_replace("+%$dvar_preg%+",$html,$data,1);
				//Further replacements only with the value
				$data = str_replace("%$dvar%",$value,$data);

			}
			//At the end put an update button
			//<br /><div style="text-align:center"><input type="submit" name="dyn_update" value="'.tra('Update variables').'"/></div>
			$data='<form method="post" name="dyn_vars">'.$data.'<div style="display:none;"><input type="submit" name="_dyn_update" value="'.tra('Update variables').'"/></div></form>';
		}
*/

		// Replace boxes - add a new line that we can have something like: ^!heading^ without the need for a \n after the initial ^ - \n will be removed below
		$data = preg_replace("/\^([^\^]+)\^/", "<div class=\"alert alert-info bitbox\"><!-- bitremovebr -->\n$1</div>", $data);
		// Replace colors ~~color:text~~
		$data = preg_replace("/\~\~([^\:]+):([^\~]+)\~\~/", "<span style=\"color:$1;\">$2</span>", $data);
		// Replace background colors ++color:text++
		$data = preg_replace("/\+\+([^\s][^\: ]+):([^\+]+)\+\+/", "<span style=\"background:$1;\">$2</span>", $data);
		// Underlined text
		$data = preg_replace("/===([^\=]+)===/", "<span style=\"text-decoration:underline;\">$1</span>", $data);
		// Center text
		$data = preg_replace("/::(.+?)::/", "<div style=\"text-align:center;\">$1</div>", $data);
		// Line breaks
		$data = preg_replace('/%%%/', '<br />', $data);

		// reinsert hash-replaced links into page
		foreach ($noparsedlinks as $np) {
			$data = str_replace($np["key"], $np["data"], $data);
		}

		$links = $this->getLinks( $data );

		// Note that there're links that are replaced
		foreach( $links as $link ) {
			if(( strstr( $link, $_SERVER["SERVER_NAME"] )) || ( !strstr( $link, '//' ))) {
				$attributes = '';
			} else {
				$attributes = 'class="external"';
			}

			// comments and anonymously created pages get nofollow
			if( is_object( $pCommonObject ) && ( get_class( $pCommonObject ) == 'comments' || ( isset( $pCommonObject->mInfo['user_id'] ) &&  $pCommonObject->mInfo['user_id'] == ANONYMOUS_USER_ID ))) {
				$attributes .= ' rel="nofollow" ';
			}

			// The (?<!\[) stuff below is to give users an easy way to
			// enter square brackets in their output; things like [[foo]
			// get rendered as [foo]. -rlpowell

			// prepare link for pattern usage
			$link2 = str_replace( "/", "\/", preg_quote( $link ));
			$pattern = "/(?<!\[)\[$link2\|([^\]\|]+)([^\]])*\]/";
			$data = preg_replace( $pattern, "<a $attributes href='$link'>$1</a>", $data );
			$pattern = "/(?<!\[)\[$link2\]/";
			$data = preg_replace( $pattern, "<a $attributes href='$link'>$link</a>", $data );
		}

		// Handle double square brackets.  -rlpowell
		$data = str_replace( "[[", "[", $data );

		// now that all links have been taken care of, we can replace all email addresses with the encoded form
		// this will also encode email addressed that have not been linked using []
		$data = encode_email_addresses( $data );

		if ($gBitSystem->getConfig('wiki_tables') != 'new') {
			// New syntax for tables
			if (preg_match_all("/\|\|(.*)\|\|/", $data, $tables)) {
				$maxcols = 1;

				$cols = array();

				for ($i = 0; $i < count($tables[0]); $i++) {
					$rows = explode('||', $tables[0][$i]);

					$col[$i] = array();

					for ($j = 0; $j < count($rows); $j++) {
						$cols[$i][$j] = explode('|', $rows[$j]);

						if (count($cols[$i][$j]) > $maxcols)
							$maxcols = count($cols[$i][$j]);
					}
				}

				for ($i = 0; $i < count($tables[0]); $i++) {
					$repl = '<table class="table">';

					for ($j = 0; $j < count($cols[$i]); $j++) {
						$ncols = count($cols[$i][$j]);

						if ($ncols == 1 && !$cols[$i][$j][0])
							continue;

						$repl .= '<tr class="'.( ( $j % 2 ) ? 'even' : 'odd' ).'">';

						for ($k = 0; $k < $ncols; $k++) {
							$repl .= '<td ';

							if ($k == $ncols - 1 && $ncols < $maxcols)
								$repl .= ' colspan="' . ($maxcols - $k).'"';

							$repl .= '>' . $cols[$i][$j][$k] . '</td>';
						}

						$repl .= '</tr>';
					}

					$repl .= '</table>';
					$data = str_replace($tables[0][$i], $repl, $data);
				}
			}
		} else {
			// New syntax for tables
			// REWRITE THIS CODE
			if( preg_match_all( "/\|\|(.*?)\|\|/s", $data, $tables ) ) {
				$maxcols = 1;

				$cols = array();

				for( $i = 0; $i < count( $tables[0] ); $i++ ) {
					$rows = preg_split( "/(\n|\<br\/\>)/", $tables[0][$i] );
					$col[$i] = array();

					for( $j = 0; $j < count( $rows ); $j++ ) {
						$rows[$j]     = str_replace( '||', '', $rows[$j] );
						$cols[$i][$j] = explode( '|', $rows[$j] );
						if( count( $cols[$i][$j] ) > $maxcols ) {
							$maxcols = count( $cols[$i][$j] );
						}
					}
				}

				for( $i = 0; $i < count( $tables[0] ); $i++ ) {
					$repl = '<table class="table table-striped">';

					if( preg_match( "#^~#", $cols[$i][0][0] ) && $cols[$i][0][0] = preg_replace( "#^~#", "", $cols[$i][0][0] ) ) {
						$th = TRUE;
					} else {
						$th = FALSE;
					}

					for( $j = 0; $j < count( $cols[$i] ); $j++ ) {
						$ncols = count( $cols[$i][$j] );

						if( $ncols == 1 && !$cols[$i][$j][0] ) {
							continue;
						}

						if( $j == 0 && $th ) {
							$repl .= '<tr>';
						} else {
							$repl .= '<tr class="'.( ( $j % 2 ) ? 'odd' : 'even' ).'">';
						}

						for( $k = 0; $k < $ncols; $k++ ) {
							$thd = ( ( $j == 0 && $th ) ? 'th' : 'td' );
							$repl .= "<$thd";

							if( $k == $ncols - 1 && $ncols < $maxcols ) {
								$repl .= ' colspan="'.( $maxcols - $k ).'"';
							}

							$repl .= ">".( str_replace( "\\n", "<br />", $cols[$i][$j][$k] ) )."</$thd>";
						}

						$repl .= '</tr>';
					}

					$repl .= '</table>';
					$data = str_replace($tables[0][$i], $repl, $data);
				}
			}
		}

		// Now tokenize the expression and process the tokens
		// Use tab and newline as tokenizing characters as well  ////
		$lines = explode("\n", $data);
		$data = '';
		$listbeg = array();
		$divdepth = array();
		$inTable = 0;

		// loop: process all lines
		foreach ($lines as $line) {

			// bitweaver now ignores leading space because it is *VERY* disturbing to unaware users - spiderr
			// unless 'feature_wiki_preserve_leading_blanks is set'.  This is used for sites that have
			// migrated from TikiWiki and have lots of pages whose formatting depends on the presevation of leading spaces
			if (!$gBitSystem->isFeatureActive('wiki_preserve_leading_blanks')) {
				$line = trim( $line );
			}

			// check if we are inside a table, if so, ignore monospaced and do
			// not insert <br/>
			$inTable += substr_count($line, "<table");
			$inTable -= substr_count($line, "</table");

			// If the first character is ' ' and we are not in pre then we are in pre
			// bitweaver now ignores leading space because it is *VERY* disturbing to unaware users - spiderr
			if (substr($line, 0, 1) == ' ' && $gBitSystem->isFeatureActive('wiki_monosp') && $inTable == 0) {
				// This is not list item -- must close lists currently opened
				while (count($listbeg))
				$data .= array_shift($listbeg);

				// If the first character is space then
				// change spaces for &nbsp;
				$line = '<span style="font-family:monospace;">' . str_replace(' ', '&nbsp;', substr($line, 1)). '</span>';
			}

			// Title bars
			$line = preg_replace( "/\-\=([^=]+)\=\-/", "<div class='bitbar'>$1</div><!-- bitremovebr -->", $line );
			// Monospaced text
			$line = preg_replace( "/-\+(.*?)\+-/", "<code>$1</code>", $line );
			// Bold text
			$line = preg_replace( "/__(.*?)__/", "<strong>$1</strong>", $line );
			// Italics
			$line = preg_replace( "/''(.*?)''/", "<em>$1</em>", $line );
			// Definition lists
			$line = preg_replace( "/^;([^:]+):(.+)/", "<dl><dt>$1</dt><dd>$2</dd></dl><!-- bitremovebr -->", $line );

			// This line is parseable then we have to see what we have
			if (substr($line, 0, 3) == '---') {
				// This is not list item -- must close lists currently opened
				while (count($listbeg))
					$data .= array_shift($listbeg);

				$line = '<hr/>';
			} else {
				$litype = substr($line, 0, 1);

				if ($litype == '*' || $litype == '#') {
					$listlevel = $this->howManyAtStart($line, $litype);

					$liclose = '</li>';
					$addremove = 0;

					if ($listlevel < count($listbeg)) {
						while ($listlevel != count($listbeg))
							$data .= array_shift($listbeg);

						if (substr(current($listbeg), 0, 5) != '</li>')
							$liclose = '';
					} elseif ($listlevel > count($listbeg)) {
						$listyle = '';

						while ($listlevel != count($listbeg)) {
							array_unshift($listbeg, ($litype == '*' ? '</ul>' : '</ol>'));

							if ($listlevel == count($listbeg)) {
								$listate = substr($line, $listlevel, 1);

								if (($listate == '+' || $listate == '-') && !($litype == '*' && !strstr(current($listbeg), '</ul>') || $litype == '#' && !strstr(current($listbeg), '</ol>'))) {
									$thisid = 'id' . intval( microtime(true) * 1000000 );
									$data .= '<br /><a id="flipper' . $thisid . '" href="javascript:BitBase.flipWithSign(\'' . $thisid . '\',1)">[' . ($listate == '-' ? '+' : '-') . ']</a>';
									$listyle = ' id="' . $thisid . '" style="display:' . ($listate == '+' ? 'block' : 'none') . ';"';
									$addremove = 1;
								}
							}

							$data .= ($litype == '*' ? "<ul$listyle>" : "<ol$listyle>");
						}

						$liclose = '';
					}

					if ($litype == '*' && !strstr(current($listbeg), '</ul>') || $litype == '#' && !strstr(current($listbeg), '</ol>')) {
						$data .= array_shift($listbeg);

						$listyle = '';
						$listate = substr($line, $listlevel, 1);

						if (($listate == '+' || $listate == '-')) {
							$thisid = 'id' . microtime() * 1000000;

							$data .= '<br /><a id="flipper' . $thisid . '" href="javascript:BitBase.flipWithSign(\'' . $thisid . '\',1)">[' . ($listate == '-' ? '+' : '-') . ']</a>';
							$listyle = ' id="' . $thisid . '" style="display:' . ($listate == '+' ? 'block' : 'none') . ';"';
							$addremove = 1;
						}

						$data .= ($litype == '*' ? "<ul$listyle>" : "<ol$listyle>");
						$liclose = '';
						array_unshift($listbeg, ($litype == '*' ? '</li></ul>' : '</li></ol>'));
					}

					$line = $liclose . '<li>' . substr($line, $listlevel + $addremove);

					if (substr(current($listbeg), 0, 5) != '</li>')
						array_unshift($listbeg, '</li>' . array_shift($listbeg));
				} elseif ($litype == '+') {
					// Must append paragraph for list item of given depth...
					$listlevel = $this->howManyAtStart($line, $litype);

					// Close lists down to requested level
					while ($listlevel < count($listbeg))
						$data .= array_shift($listbeg);

					if (count($listbeg)) {
						if (substr(current($listbeg), 0, 5) != '</li>') {
							array_unshift($listbeg, '</li>' . array_shift($listbeg));

							$liclose = '<li>';
						} else
							$liclose = '<br />';
					} else
						$liclose = '';

					$line = $liclose . substr($line, count($listbeg));
				} else {
					// This is not list item -- must close lists currently opened
					while (count($listbeg))
						$data .= array_shift($listbeg);

					// Get count of (possible) header signs at start
					$hdrlevel = $this->howManyAtStart($line, '!');

					// If 1st char on line is '!' and its count less than 6 (max in HTML)
					if ($litype == '!' && $hdrlevel > 0 && $hdrlevel <= 6) {
						// OK. Parse headers here...
						$aclose = '';
						$edit_link = '';
						$addremove = 0;

						// Close lower level divs if opened
						for (;current($divdepth) >= $hdrlevel; array_shift($divdepth)) {
							$data .= '</div>';
						}

						// May be spesial signs present after '!'s?
						$divstate = substr($line, $hdrlevel, 1);

						if ($divstate == '+' || $divstate == '-') {
							// OK. Must insert flipper after HEADER, and then open new div...
							$thisid = 'id' . microtime() * 1000000;

							$aclose = '<a id="flipper' . $thisid . '" href="javascript:flipWithSign(\'' . $thisid . '\',1)">[' . ($divstate == '-' ? '+' : '-') . ']</a>';
							$aclose .= '<div id="' . $thisid . '" style="display:' . ($divstate == '+' ? 'block' : 'none') . ';">';
							array_unshift($divdepth, $hdrlevel);
							$addremove = 1;
						}

						if( $gBitSystem->isFeatureActive( 'wiki_section_edit' ) && $gBitUser->hasPermission( 'p_wiki_update_page' ) ) {
							if( $hdrlevel == $gBitSystem->getConfig( 'wiki_section_edit' ) ) {
								$edit_url = WIKI_PKG_URL."edit.php?content_id=".$contentId."&amp;section=".$section_count++;
								$edit_link = '<span class="editsection" style="float:right;margin-left:5px;">[<a href="'.$edit_url.'">'.tra( "edit" ).'</a>]</span>';
							}
						}
						$hTagLevel = $hdrlevel + 1; // there should only be 1 <h1> per html document
						$line = $edit_link
							. "<h$hTagLevel>"
							. substr($line, $hdrlevel + $addremove)
							. "</h$hTagLevel>"
							. $aclose
							;
					} elseif (!strcmp($line, "...page...")) {
						// Close lists and divs currently opened
						while (count($listbeg)) {
							$data .= array_shift($listbeg);
						}

						while (count($divdepth)) {
							$data .= '</div>';
							array_shift ($divdepth);
						}

						// Leave line unchanged... index.php will split wiki here
						$line = "...page...";
					} else {
						// Usual paragraph.
						if ($inTable == 0) {
							$line .= '<br />';
						}
					}
				}
			}

			$data .= $line;
		}

		// Close lists may remains opened
		while (count($listbeg)) {
			$data .= array_shift($listbeg);
		}

		// Close header divs may remains opened
		for ($i = 1; $i <= count($divdepth); $i++) {
			$data .= '</div>';
		}

		// Close BiDi DIVs if any
		for ($i = 0; $i < $bidiCount; $i++) {
			$data .= "</div>";
		}

		$data = str_replace( "<!-- bitremovebr --><br />", "", $data );

		return $data;
	}
}

?>
