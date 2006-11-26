<?php
/**
 * @version  $Revision: 1.16 $
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
// | Author: xing <xing@synapse.plus.com>
// +----------------------------------------------------------------------+
// $Id: data.maketoc.php,v 1.16 2006/11/26 14:51:45 squareing Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATAMAKETOC', 'datamaketoc' );
global $gLibertySystem;
global $gContent;
$pluginParams = array (
	'tag'           => 'maketoc',
	'auto_activate' => TRUE,
	'requires_pair' => FALSE,
	'load_function' => '',
	'title'         => 'Page Table of Contents',
	'help_page'     => 'DataPluginMakeTOC',
	'description'   => tra("Will create a table of contents of the WikiPage based on the headings below."),
	'help_function' => 'data_maketoc_help',
	'syntax'        => "{MAKETOC}",
	'path'          => LIBERTY_PKG_PATH.'plugins/data.maketoc.php',
	'security'      => 'registered',
	'plugin_type'   => DATA_PLUGIN,
	'biticon'       => '{biticon iclass="quicktag icon" ipackage=quicktags iname=maketoc iexplain="Page Table of Contents"}',
	'taginsert'     => '{maketoc}'
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAMAKETOC, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAMAKETOC );

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
		</table>'.
		tra("Example: ").'{MAKETOC maxdepth=3 include=all backtotop=true}';
	return $help;
}

function data_maketoc( $data ) {
	preg_match_all( "/\{maketoc(.*?)\}/", $data, $maketocs );
	// extract the parameters for maketoc
	foreach( $maketocs[1] as $string ) {
		$params[] = parse_xml_attributes( $string );
	}

	// get all headers into an array
	preg_match_all( "/<h(\d)[^>]*>(.*?)<\/h\d>/i", $data, $headers );

	// remove any html tags from the output text and generate link ids
	foreach( $headers[2] as $output ) {
		$outputs[] = preg_replace( "/<[^>]*>/", "", $output );
		$id = substr( preg_replace( "/<[^>]*>|[^\w|\d]*/", "", $output ), 0, 40 );
		$ids[] = !empty( $id ) ? $id : 'id'.microtime() * 1000000;
	}

	// insert the <a name> tags in the right places
	foreach( $headers[0] as $k => $header ) {
		$reconstructed = "<h{$headers[1][$k]} id=\"{$ids[$k]}\">{$headers[2][$k]}</h{$headers[1][$k]}>";
		$data = preg_replace( "/".preg_quote( $header, "/" )."/", $reconstructed, $data );
	}

	if( !empty( $outputs ) ) {
		$tocHash = array(
			'outputs' => $outputs,
			'ids'     => $ids,
			'levels'  => $headers[1],
		);

		// (<br[ |\/]*>){0,1} removes up to one occurance of <br> | <br > | <br /> | <br/> or similar variants
		$sections = preg_split( "/\{maketoc.*?\}(<br[ |\/]*>){0,1}/", $data );
		// first section is before any {maketoc} entry, so we can ignore it
		$ret = array_shift( $sections );

		foreach( $sections as $k => $section ) {
			// count headers in each section that we know where to begin and where to stop
			preg_match_all( "!<h(\d)[^>]*>.*?</h\d>!i", $section, $hs );
			$tocHash['tocCounts'][] = count( $hs[0] );
			$tocHash['tocKey'] = $k;
			$ret .= maketoc_create_list( $tocHash, $params[$k] ).$section;
		}
	}

	return isset( $ret ) ? $ret : $data;
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

	$ignore = 0;
	if( !isset( $pParams['include'] ) || $pParams['include'] != 'all' ) {
		for( $i = 0; $i < $tocKey; $i++ ) {
			$ignore += $pTocHash['tocCounts'][$i];
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
?>
