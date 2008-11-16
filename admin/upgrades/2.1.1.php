<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/admin/upgrades/2.1.1.php,v 1.6 2008/11/16 10:09:49 squareing Exp $
 */
global $gBitInstaller;

$infoHash = array(
	'package'      => LIBERTY_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "Minor fix to user_id column type in liberty_content_history.",
	'post_upgrade' => NULL,
);

// all we are doing is change the column type of user_id for liberty_content_history.
// postgresql < 8.2 doesn't allow easy column type changing
// and therefore we need to undergo this annoying dance.
$gBitInstaller->registerPackageUpgrade( $infoHash, array(

array( 'DATADICT' => array(
	// rename original column
	array( 'RENAMECOLUMN' => array(
		'liberty_content_history' => array(
			'`user_id`' => "`temp_column` VARCHAR(40)",
		),
	)),
	// insert new column
	array( 'ALTER' => array(
		'liberty_content_history' => array(
			'user_id' => array( '`user_id`', 'I4' ),
	))),
)),

// copy data into new column
array( 'QUERY' =>
	// postgres > 8.2 needs to have the type cast
	array(
		'PGSQL' => array( "UPDATE `".BIT_DB_PREFIX."liberty_content_history` SET `user_id` = `temp_column`::integer" ),
		'SQL92' => array( "UPDATE `".BIT_DB_PREFIX."liberty_content_history` SET `user_id` = `temp_column`" ),
	),
),

array( 'DATADICT' => array(
	// drop old column
	array( 'DROPCOLUMN' => array(
		'liberty_content_history' => array( '`temp_column`' ),
	)),
	// reconstruct constraints, sequences and indexes
)),

));
?>
