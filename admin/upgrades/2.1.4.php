<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/admin/upgrades/2.1.4.php,v 1.1 2010/04/17 22:46:09 wjames5 Exp $
 */
global $gBitInstaller;

$infoHash = array(
	'package'      => LIBERTY_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "Update content type guid table to have singular and plural names, deprecate content_description",
	'post_upgrade' => NULL,
);

$gBitInstaller->registerPackageUpgrade( $infoHash, array(

array( 'DATADICT' => array(
	// insert new column
	array( 'ALTER' => array(
		'liberty_content_types' => array(
			'content_name' => array( '`content_name`', 'VARCHAR(250) NOTNULL' ),
			'content_name_plural' => array( '`content_name_plural`', 'VARCHAR(250)' ),
	))),
)),

// copy data into new column
array( 'QUERY' =>
	array(
		'SQL92' => array( "UPDATE `".BIT_DB_PREFIX."liberty_content_types` SET `content_name` = `content_description`" ),
	),
),

/* when we're read we can drop the old content_description column 
array( 'DATADICT' => array(
	// drop old column
	array( 'DROPCOLUMN' => array(
		'liberty_content_types' => array( '`content_description`' ),
	)),
	// reconstruct constraints, sequences and indexes
)),
*/

));
