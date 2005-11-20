<?php
/**
 * @version  $Revision: 1.1.1.1.2.12 $
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
// $Id: data.maketoc.php,v 1.1.1.1.2.12 2005/11/20 15:34:34 squareing Exp $

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATAMAKETOC', 'datamaketoc' );
global $gLibertySystem;
global $gContent;
$pluginParams = array ( 'tag' => 'MAKETOC',
						'auto_activate' => TRUE,
						'requires_pair' => FALSE,
						'load_function' => '',
						'title' => 'MakeTOC',
						'help_page' => 'DataPluginMakeTOC',
						'description' => tra("Will create a table of contents of the WikiPage based on the headings below."),
						'help_function' => 'data_maketoc_help',

						'syntax' => "{MAKETOC}",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAMAKETOC, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAMAKETOC );

// Help Function
function data_maketoc_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>maxdepth</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( 'if you specify 3 here, MakeTOC will only parse headings to the h3 level.' ) . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>include</td>'
				.'<td>' . tra( "value") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( 'if you include <strong>' ).'all'.( '</strong>, it will print a list of the full list of contents, regardless of where in the page {maketoc} is.' ) . '</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>backtotop</td>'
				.'<td>' . tra( "boolean") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( 'if you set backtotop <strong>' ).'true'.( '</strong>, it will insert a "back to the top" link.' ) . '</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . '{MAKETOC maxdepth=3 include=all backtotop=true}';
	return $help;
}

function data_maketoc( $data ) {
	preg_match_all( "/\{maketoc(.*?)\}/", $data, $maketocs );
	// extract the parameters for maketoc
	foreach( $maketocs[1] as $string ) {
		$params[] = parse_xml_attributes( $string );
	}

	// get all headers into an array
	preg_match_all( "/<h(\d).*?>(.*?)<\/h.>/i", $data, $headers );

	// remove any html tags from the output text and generate link ids
	foreach( $headers[2] as $output ) {
		$outputs[] = preg_replace( "/<.*?>/", "", $output );
		$anchor = preg_replace( "/<.*?>|[^\w|\d]*/", "", $output );
		$anchors[] = !empty( $anchor) ? $anchor : 'id'.microtime() * 1000000;
	}

	// insert the <a name> tags in the right places
	foreach( $headers[0] as $k => $header ) {
		$anchor = '<a name="'.$anchors[$k].'"></a>';
		$data = preg_replace( "/".preg_quote( $header, "/" )."/", $anchor.$header, $data );
	}

	if( !empty( $outputs ) ) {
		$tocHash = array(
			'outputs' => $outputs,
			'anchors' => $anchors,
			'levels' => $headers[1],
		);

		// (<br[ |\/]*>){0,1} removes up to one occurance of <br> | <br > | <br /> | <br/> or similar variants
		$sections = preg_split( "/\{maketoc.*?\}(<br[ |\/]*>){0,1}/", $data );
		// first section is before any {maketoc} entry, so we can ignore it
		$ret = array_shift( $sections );

		foreach( $sections as $k => $section ) {
			// count headers in each section that we know where to begin and where to stop
			preg_match_all( "/<h(.)>.*?<\/h.>/i", $section, $hs );
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
			$ignore += $tocCounts[$i];
		}
	}

	$list = '';
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
				$list .= '<li><a href="#'.$anchors[$k].'">'.$output.'</a>';
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

	if( isset( $pParams['backtotop'] ) && $pParams['backtotop'] == 'true' ) {
		$toplink = '<a href="#top">'.tra( 'back to top' ).'</a>';
	} else {
		$toplink = '';
	}

	$list = '<div class="maketoc"><h3>'.( !empty( $pParams['title'] ) ? $pParams['title'] : tra( 'Page Contents' ) ).'</h3>'.$list.$toplink.'</div>';

	return $list;
}
?>
