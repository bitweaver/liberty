<?php
global $gBitSystem, $gBitSmarty;
require_once( '../bit_setup_inc.php' );

// help for generic options
$exampleHash = array(
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
			'data' => "__::~~red:++yellow:text++~~::__",
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
	'Wiki References' => array(
		'Double Brackets' => array(
			'data' => "((Wiki Page))",
			'result' => '<a href="#">Wiki Page</a>',
		),
		'Double Brackets + Description' => array(
			'data' => "((Wiki Page|Page Description))",
			'result' => '<a href="#">Page Description</a>',
		),
	),
	'External Links' => array(
		'External Link' => array(
			'data' => "[http://www.example.com]",
		),
		'External Link + Description' => array(
			'data' => "[http://www.example.com|Description]",
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
			'data' => "~np~~~yellow:yellow~~ and __bold__ text~/np~",
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
	$exampleHash['Simple Tables'] = array(
		'Tables' => array(
			'data' => "|| Row1-Col1 | Row1-Col2 || Row2-Col1 | Row2-Col2 ||",
		),
	);
}

// parse example data and translate strings
foreach( array_keys( $exampleHash ) as $section ) {
	foreach( $exampleHash[$section] as $title => $example ) {
		if( empty( $example['result'] )) {
			$example['format_guid'] = 'tikiwiki';
			$exampleHash[$section][$title]['result'] = LibertyContent::parseData( $example );
		}
	}
}
$gBitSmarty->assign( 'exampleHash', $exampleHash );

// mediawiki type tables
$mediawiki = array(
	'Example 1' => array(
		'data' =>
'{| border=3
|+A Simple Table
|-
! Col 1 !! Col 1 !! Col 3
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
'{| style="background:yellow; color:green"
|+ Table with many colours
|-
| abc
| def
| ghi
|- style="background:red; color:white"
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
$gBitSmarty->assign( 'mediawiki', $mediawiki );
?>
