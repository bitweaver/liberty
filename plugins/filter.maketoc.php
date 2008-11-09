<?php
/**
 * @version  $Header: /cvsroot/bitweaver/_bit_liberty/plugins/filter.maketoc.php,v 1.8 2008/11/09 09:08:55 squareing Exp $
 * @package  liberty
 * @subpackage plugins_filter
 */

/**
 * definitions ( guid character limit is 16 chars )
 */
define( 'PLUGIN_GUID_FILTERMAKETOC', 'filtermaketoc' );

global $gLibertySystem;

$pluginParams = array (
	'title'              => 'Table of Contents',
	'description'        => 'When you insert {maketoc} into a wiki page, it will create a nested table of contents based on the headings in that page.',
	'auto_activate'      => TRUE,
	'plugin_type'        => FILTER_PLUGIN,

	// filter functions
	'presplit_function'  => 'maketoc_presplitfilter',
	'postparse_function' => 'maketoc_postparsefilter',

	// these settings are to get the plugin help working on content edit pages
	'tag'                => 'maketoc',
	'help_page'          => 'Maketoc Filter',
	'help_function'      => 'data_maketoc_help',
	'syntax'             => '{maketoc}',
	'biticon'            => '{biticon iclass="quicktag icon" ipackage=quicktags iname=maketoc iexplain="Page Table of Contents"}',
	'taginsert'          => '{maketoc}',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_FILTERMAKETOC, $pluginParams );

function maketoc_presplitfilter( &$pData, &$pFilterHash ) {
	// we remove the maketoc stuff when the data is split. this will simplify output and won't mess with the layout on the articles / blogs front page
	$pData = preg_replace( "/\{maketoc[^\}]*\}\s*\n?/i", "", $pData );
}

function maketoc_postparsefilter( &$pData, &$pFilterHash ) {
	preg_match_all( "/\{maketoc(.*?)\}/", $pData, $maketocs );

	if( !empty( $maketocs[1] )) {
		// extract the parameters for maketoc
		foreach( $maketocs[1] as $string ) {
			$params[] = parse_xml_attributes( $string );
		}

		// get all headers into an array
		preg_match_all( "/<h(\d)[^>]*>(.*?)<\/h\d>/i", $pData, $headers );

		// clumsy way of finding out if index is set. since we can't allow 
		// duplicate settings of index in one page, we either index everything 
		// or nothing.
		foreach( $params as $p ) {
			if( empty( $index )) {
				$index = ( in_array( 'index', array_keys( $p )));
			}
		}

		if( $index ) {
			$counter = array();
			foreach( array_keys( $headers[2] ) as $key ) {
				$level = $headers[1][$key];
				if( empty( $counter[$level] )) {
					$counter[$level] = 1;
				} elseif( $level == $headers[1][$key - 1] ) {
					$counter[$level]++;
				} elseif( $level < $headers[1][$key - 1] ) {
					$counter[$level] = $counter[$level] + 1;
				} else {
					$counter[$level] = 1;
				}

				$index = '';
				foreach( $counter as $k => $c ) {
					if( $k <= $level ) {
						$index .= "$c.";
					}
				}
				$headers[2][$key] = $index.' '.$headers[2][$key];
			}
		}

		// remove any html tags from the output text and generate link ids
		foreach( $headers[2] as $output ) {
			$outputs[] = $temp = preg_replace( "/<[^>]*>/", "", $output );
			$id = substr( preg_replace( "/[^\w|\d]*/", "", $temp ), 0, 40 );
			$ids[] = !empty( $id ) ? $id : 'id'.microtime() * 1000000;
		}

		// insert the <a name> tags in the right places
		foreach( $headers[0] as $k => $header ) {
			$reconstructed = "<h{$headers[1][$k]} id=\"{$ids[$k]}\">{$headers[2][$k]}</h{$headers[1][$k]}>";
			$pData = str_replace( $header, $reconstructed, $pData );
		}

		if( !empty( $outputs ) ) {
			$tocHash = array(
				'outputs' => $outputs,
				'ids'     => $ids,
				'levels'  => $headers[1],
			);

			// (<br[ |\/]*>){0,1} removes up to one occurance of <br> | <br > | <br /> | <br/> or similar variants
			$sections = preg_split( "/\{maketoc.*?\}(<br[ |\/]*>){0,1}/", $pData );
			// first section is before any {maketoc} entry, so we can ignore it
			$ret = '';

			foreach( $sections as $k => $section ) {
				// count headers in each section that we know where to begin and where to stop
				preg_match_all( "!<h(\d)[^>]*>.*?</h\d>!i", $section, $hs );
				$tocHash['header_count'][] = count( $hs[0] );
				$ret .= $section;
				// the last section will create an error if we don't check for available params
				if( isset( $params[$k] )) {
					$ret .= maketoc_create_list( $tocHash, $params[$k] );
				}
			}
		}
	}

	$pData = isset( $ret ) ? $ret : preg_replace( "/\{maketoc[^\}]*\}\s*(<br[^>]*>)*/i", "", $pData );
}

function maketoc_create_list( $pTocHash, $pParams ) {
	extract( $pTocHash , EXTR_SKIP);

	// previous level
	$prev = 0;
	// array that is populated with the items that have to be closed eventually
	$open = array();
	// contains the actual depth we're at
	$depth = 0;
	// maximum header level output uses
	$maxdepth = !empty( $pParams['maxdepth'] ) ? $pParams['maxdepth'] : 6;

	// work out what to print
	$ignore = 0;
	if( !isset( $pParams['include'] ) || $pParams['include'] != 'all' ) {
		for( $i = 0; $i < count( $header_count ); $i++ ) {
			$ignore += $header_count[$i];
		}
	}

	$list = '';

	// create a dropdown that will zap user to selection
	if( !empty( $pParams['type'] ) && $pParams['type'] == 'dropdown' ) {
		$list .= '<form action="'.BIT_ROOT_URL.'">';
		$list .= '<select name="url" id="maketoc" onchange="location.href=form.url.options[form.url.selectedIndex].value">';
		foreach( $outputs as $k => $output ) {
			if( $k >= $ignore ) {
				$list .= '<option value="#'.$ids[$k].'">'.str_pad( '', ( $levels[$k] - 1 ) * 6, '&nbsp;' ).'&bull; '.$output.'</a>';
			}
		}
		$list .= "</select>";
		$list .= '</form>';
	} else {
		// start with the generation of the nested <ul> list
		foreach( $outputs as $k => $output ) {
			if( $k >= $ignore ) {
				$j = 0;

				// open <ul> tags, store them in $open and set $depth
				for( $i = $prev; $i < $levels[$k]; $i++ ) {
					if( $j++ == 0 ) {
						array_unshift( $open, $prev );
					}
					if( $depth < $maxdepth ) {
						$list .= '<ul>';
					}
					$depth++;
				}

				// close the <ul> tags as appropriate and update $open and $depth
				for( $i = $prev; $i > $levels[$k]; $i -= 1 ) {
					// close any <li> tags if needed
					if( $depth == $open[0] ) {
						if( $depth <= $maxdepth ) {
							$list .= '</li>';
						}
						array_shift( $open );
					}
					if( $depth <= $maxdepth ) {
						$list .= '</ul>';
					}
					$depth -= 1;
				}

				// close any <li> items that haven't been dealt with above
				if( $depth == $open[0] ) {
					if( $depth <= $maxdepth ) {
						$list .= '</li>';
					}
					array_shift( $open );
				}

				if( $depth <= $maxdepth ) {
					$list .= '<li><a href="#'.$ids[$k].'">'.$output.'</a>';
				}
				if( $levels[$k] >= @$levels[$k+1] ) {
					if( $depth <= $maxdepth ) {
						$list .= '</li>';
					}
				}
				$prev = $levels[$k];
			}
		}

		// close off any remaning tags
		for( $i = $depth; $i > 0; $i -= 1 ) {
			if( $i == $open[0] ) {
				if( $depth <= $maxdepth ) {
					$list .= '</li>';
				}
				array_shift( $open );
			}
			if( $depth <= $maxdepth ) {
				$list .= '</ul>';
			}
		}
	}

	if( isset( $pParams['backtotop'] ) && $pParams['backtotop'] == 'true' ) {
		$toplink = '<a href="#content">'.tra( 'back to top' ).'</a>';
	} else {
		$toplink = '';
	}


	$width = '';
	if( !empty( $pParams['width'] ) ) {
		$work = $pParams['width'];
		if( preg_match( '/^\d+(\%|em|cm|px|pt)*$/',$work ) ) {
			$width = "style=\"width:$work;\"";
		}
	}

	$class = 'class="maketoc"';
	if( !empty( $pParams['class'] ) ) {
		$class = 'class="'.$pParams['class'].'"';
	}

	$list = "<div $class $width><h3>" .( !empty( $pParams['title'] ) ? $pParams['title'] : tra( 'Page Contents' ) ).'</h3>'.$list.$toplink.'</div>';

	return $list;
}

// Help Function
function data_maketoc_help() {
	$help =
		'<table class="data help">
			<tr>
				<th>'.tra( "Key" ).'</th>
				<th>'.tra( "Type" ).'</th>
				<th>'.tra( "Comments" ).'</th>
			</tr>
			<tr class="odd">
				<td>maxdepth</td>
				<td>'.tra( "numeric").'<br />('.tra("optional").')</td>
				<td>'.tra( 'If you specify 3 here, MakeTOC will only parse headings to the h3 level.' ).'</td>
			</tr>
			<tr class="even">
				<td>include</td>
				<td>'.tra( "string").'<br />('.tra("optional").')</td>
				<td>'.tra( 'If you include <strong>all</strong>, it will print a list of the full list of contents, regardless of where in the page {maketoc} is.' ).'</td>
			</tr>
			<tr class="odd">
				<td>backtotop</td>
				<td>'.tra( "boolean").'<br />('.tra("optional").')</td>
				<td>'.tra( 'If you set backtotop <strong>' ).'true'.( '</strong>, it will insert a "back to the top" link.' ).'</td>
			</tr>
			<tr class="even">
				<td>class</td>
				<td>'.tra( "string").'<br />('.tra("optional").')</td>
				<td>'.tra( 'Override the class of the maketoc div.' ).'</td>
			</tr>
			<tr class="odd">
				<td>width</td>
				<td>'.tra( "string").'<br />('.tra("optional").')</td>
				<td>'.tra( 'Override the width of the maketoc div.' ).'</td>
			</tr>
			<tr class="even">
				<td>type</td>
				<td>'.tra( "key words").'<br />('.tra("optional").')</td>
				<td>'.tra( 'Setting this to dropdown will create a dropdown instead of the default nested list of headings.' ).'</td>
			</tr>
			<tr class="odd">
				<td>index</td>
				<td>'.tra( "boolean").'<br />('.tra("optional").')</td>
				<td>'.tra( 'Add index numbers to your headers and the page contents.' ).'</td>
			</tr>
		</table>'.
		tra("Example: ").'{maketoc maxdepth=3 include=all backtotop=true index=true}';
	return $help;
}
?>
