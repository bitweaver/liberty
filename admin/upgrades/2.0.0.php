<?php
global $gBitInstaller;

$infoHash = array(
	'package'      => LIBERTY_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "This upgrade replaces unused meta tables with new ones. These meta tables are used to store meta data of uploaded files.",
	'post_upgrade' => NULL,
);

$gBitInstaller->registerPackageUpgrade( $infoHash, array(

array( 'DATADICT' => array(
)),

array( 'PHP' => '
	// make sure plugins are up to date.
	global $gLibertySystem;
	$gLibertySystem->scanAllPlugins();
'
)

));

//$gBitInstaller->registerPackageDependencies( WIKI_PKG_NAME, '1.0.3', array(
//	'kernel'  => '2.1.0',
//));
?>
