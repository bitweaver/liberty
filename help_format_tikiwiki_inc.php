<?php
/**
 * help_format_tikiwiki_inc
 *
 * @author   Christian Fowler>
 * @version  $Revision: 1.14 $
 * @package  liberty
 * @subpackage functions
 */

/**
 * required setup
 */
global $gBitSystem, $gBitSmarty;
require_once( BIT_ROOT_PATH.'bit_setup_inc.php' );
require_once( KERNEL_PKG_PATH.'BitCache.php' );
$cache = new BitCache( 'liberty/help' );

// only regenerate this thing if it's not cached yet
$cacheFile = 'tikiwiki';
if( $cache->isCached( $cacheFile, filemtime( __FILE__ ))) {
	$examples = unserialize( $cache->readCacheFile( $cacheFile ));
} else {
	// help for generic options
	$tikiwiki = array(
		'Emphasis' => array(
			'Headings' => array(
				'data' => "! heading 1\n!! heading 2\n!!! heading 3",
				'note' => "Number of ! correponds to heading level.",
			),
			'Italics' => array(
				'data' => "''text''",
				'note' => "Two single quotes not one double quote",
			),
			'Underline' => array(
				'data' => "===text===",
			),
			'Coloured Background' => array(
				'data' => "++yellow:text++",
			),
			'Coloured Text' => array(
				'data' => "~~red:text~~",
			),
			'Bold' => array(
				'data' => "__text__",
			),
			'Centered Text' => array(
				'data' => "::text::",
			),
			'Combined' => array(
				'data' => "::__~~red:++yellow:text++~~__::",
				'note' => "When you combine options make sure you open and close in the opposite order analogous to: {[(text)]}",
			),
		),
		'Lists' => array(
			'Unordered Lists' => array(
				'data' => "* First item\n** First subitem\n** Second subitem\n* Second item",
			),
			'Ordered Lists' => array(
				'data' => "# First item\n## First subitem\n## Second subitem\n# Second item",
			),
			'Definition Lists' => array(
				'data' => ";Term: Definition",
			),
		),
		'Links' => array(
			'Wiki Links' => array(
				'data' => "((Wiki Page))",
				'result' => '<a href="#">Wiki Page</a>',
			),
			'Wiki Links + Description' => array(
				'data' => "((Wiki Page|Page Description))",
				'result' => '<a href="#">Page Description</a>',
			),
			'Wiki Links + Anchor + Description' => array(
				'data' => "((Wiki Page#Anchor|Page Description))",
				'result' => '<a href="#">Page Description</a>',
			),
			'External Link' => array(
				'data' => "[http://www.example.com]",
			),
			'External Link + Description' => array(
				'data' => "[http://www.example.com|Description]",
			),
			'External Link + Anchor + Description' => array(
				'data' => "[http://www.example.com/Page#Anchor|Description]",
			),
		),
		'Miscellaneous' => array(
			'Horizontal Rule' => array(
				'data' => '---',
			),
			'Highlighted Bar' => array(
				'data' => '-=text=-',
			),
			'Highlighted Box' => array(
				'data' => "^text\nmore text^",
			),
			'As is Text' => array(
				'data' => "~np~~~yellow:yellow~~\nand\n__bold__ text~/np~",
				'note' => "This text will not be parsed",
			),
			'Pre Parsed' => array(
				'data' => "~pp~~~yellow:yellow~~\nand\n__bold__ text~/pp~",
				'note' => "This text will be treated like code and will not be altered and will be displayed using a monospace font. The same can be achieved by using &lt;pre&gt;text&lt;/pre&gt;.",
			),
			'Monospaced Text' => array(
				'data' => "-+text+-",
			),
			'Right to Left' => array(
				'data' => "{r2l}this text is from\nright to left\n{l2r}and back to\nleft to right.",
			),
		),
		'Simple Tables' => array(
			'Simple Table' => array(
				'data' => "|| Row1-Col1 | Row1-Col2\nRow2-Col1 | Row2-Col2 ||",
			),
			'With Headers' => array(
				'data' => "||~ Header1 | Header2\nRow1-Col1 | Row1-Col2\nRow2-Col1 | Row2-Col2 ||",
			),
		),
	);

	if( $gBitSystem->getConfig( 'wiki_tables' ) == 'old' ) {
		$tikiwiki['Simple Tables'] = array(
			'Tables' => array(
				'data' => "|| Row1-Col1 | Row1-Col2 || Row2-Col1 | Row2-Col2 ||",
			),
		);
	}

	foreach( array_keys( $tikiwiki ) as $section ) {
		foreach( $tikiwiki[$section] as $title => $example ) {
			if( empty( $example['result'] )) {
				$example['format_guid'] = 'tikiwiki';
				$tikiwiki[$section][$title]['result'] = LibertyContent::parseData( $example );
			}
		}
	}

	// mediawiki type tables
	$mediawiki = array(
		'Example 1' => array(
			'data' =>
'{| border=3
|+A Simple Table
|-
! Col 1 !! Col 2 !! Col 3
|-
| Row1-Col1 || Row1-Col2 || Row1-Col3
|-
| Row2-Col1
| Row2-Col2
| Row2-Col3
|-
| Row3-Col1 || Row3-Col2 || Row3-Col3
|}'
		),
		'Example 2' => array(
			'data' =>
'{| border="2" cellpadding="5"
|+Multiplication table
|-
! X !! 1 !! 2 !! 3
|-
! 1
| 1 || 2 || 3
|-
! 2
| 2 || 4 || 6
|-
! 3
| 3 || 6 || 9
|-
! 4
| 4 || 8 || 12
|-
! 5
| 5 || 10 || 15
|}'
		),
		'Example 3' => array(
			'data' =>
'{|
|+ Table with alternating rows
|- class=odd
| one
| two
|- class=even
| class=highlight | three
| four
|- class=odd
| five
| six
|- class=even
| seven
| eight
|}'
		),
		'Example 4' => array(
			'data' =>
'{| style="background:yellow;color:green"
|+ Table with many colours
|-
| abc
| colspan="2" style="text-align:center;background:lightblue;" | defghi
|- style="background:red;color:white"
| jkl
| mno
| pqr
|-
| style="font-weight:bold" | stu
| style="background:silver" | vwx
| yz
|}'
		),
	);

	// parse tables
	foreach( $mediawiki as $title => $example ) {
		if( empty( $example['result'] )) {
			$example['format_guid'] = 'tikiwiki';
			$mediawiki[$title]['result'] = LibertyContent::parseData( $example );
		}
	}

	$examples['tikiwiki'] = $tikiwiki;
	$examples['mediawiki'] = $mediawiki;
	$cache->writeCacheFile( $cacheFile, serialize( $examples ));
}
$gBitSmarty->assign( 'examples', $examples );
?>
