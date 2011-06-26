<?php
/**
 * @version $Header$
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
	// rename original column
	array( 'RENAMECOLUMN' => array(
		'liberty_files' => array(
			'`storage_path`' => "`file_name` VARCHAR(250)",
		),
	)),
)),

));
