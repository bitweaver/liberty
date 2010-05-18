<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_liberty/admin/upgrades/Attic/2.1.5.php,v 1.2 2010/05/18 18:41:05 spiderr Exp $
 */
global $gBitInstaller;

$infoHash = array(
	'package'      => LIBERTY_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "This upgrade adds a table for storing area tags for image files.",
	'post_upgrade' => NULL,
);
$gBitInstaller->registerPackageUpgrade( $infoHash, array(

array( 'DATADICT' => array(
	array( 'CREATE' => array(
		'liberty_attachment_tags' => "
			comment_id I4 PRIMARY NOTNULL,
			attachment_id I4 PRIMARY NOTNULL,
			tag_top I4 DEFAULT 0 NOTNULL ,
			tag_left I4 DEFAULT 0 NOTNULL ,
			tag_width I4 DEFAULT 100 NOTNULL ,
			tag_height I4 DEFAULT 100 NOTNULL 
			CONSTRAINT '
				, CONSTRAINT `lib_attachment_tag_id_ref`    FOREIGN KEY (`attachment_id`) REFERENCES `".BIT_DB_PREFIX."liberty_attachments` (`attachment_id`)
				, CONSTRAINT `lib_attachment_tag_cid_ref`  FOREIGN KEY (`comment_id`)  REFERENCES `".BIT_DB_PREFIX."liberty_comments`  (`comment_id`) '
		",
	)),
)),

array( 'PHP' => '
	// make sure plugins are up to date.
	global $gLibertySystem;
	$gLibertySystem->scanAllPlugins();
'
)

));
?>
