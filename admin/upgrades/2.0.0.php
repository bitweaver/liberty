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
	array( 'DROPTABLE' => array(
		'liberty_meta_content_map',
		'liberty_meta_data',
		'liberty_meta_types',
	)),
	array( 'CREATE' => array(
		'liberty_meta_titles' => "
			meta_title_id I4 PRIMARY,
			meta_title C(250) NOTNULL
		",

		'liberty_meta_types' => "
			meta_type_id I4 PRIMARY,
			meta_type C(250) NOTNULL
		",

		'liberty_attachment_meta_data' => "
			attachment_id I4 PRIMARY NOTNULL,
			meta_type_id I4 PRIMARY NOTNULL,
			meta_title_id I4 PRIMARY NOTNULL,
			meta_value XL
			CONSTRAINT '
				, CONSTRAINT `lib_attachment_meta_id_ref`    FOREIGN KEY (`attachment_id`) REFERENCES `".BIT_DB_PREFIX."liberty_attachments` (`attachment_id`)
				, CONSTRAINT `lib_attachment_meta_type_ref`  FOREIGN KEY (`meta_type_id`)  REFERENCES `".BIT_DB_PREFIX."liberty_meta_types`  (`meta_type_id`)
				, CONSTRAINT `lib_attachment_meta_title_ref` FOREIGN KEY (`meta_title_id`) REFERENCES `".BIT_DB_PREFIX."liberty_meta_titles` (`meta_title_id`) '
		",
		'liberty_attachment_prefs' => "
			attachment_id I4 PRIMARY,
			pref_name C(40) PRIMARY,
			pref_value C(250)
			CONSTRAINT ', CONSTRAINT `lib_att_prefs_content_ref` FOREIGN KEY (`attachment_id`) REFERENCES `".BIT_DB_PREFIX."liberty_attachments` (`attachment_id`)'
		",
	)),
	array( 'CREATEINDEX' => array(
		'lib_attachment_meta_idx'       => array( 'liberty_attachment_meta_data', 'attachment_id', array() ),
		'lib_attachment_meta_type_idx'  => array( 'liberty_attachment_meta_data', 'meta_type_id',  array() ),
		'lib_attachment_meta_title_idx' => array( 'liberty_attachment_meta_data', 'meta_title_id', array() ),
	)),
	array( 'CREATESEQUENCE' => array(
		'liberty_meta_types_id_seq',
		'liberty_meta_titles_id_seq',
	)),
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
