<?php

// Common Content tables
$tables = array(

'liberty_content_types' => "
	content_type_guid C(16) PRIMARY,
	content_description C(250) NOTNULL,
	maintainer_url C(250),
	handler_class C(128),
	handler_package C(128),
	handler_file C(128)
",

'liberty_content_status' => "
	content_status_id I4 PRIMARY,
	content_status_name C(128) NOTNULL
",

'liberty_content' => "
	content_id I4 PRIMARY,
	user_id I4 NOTNULL,
	modifier_user_id I4 NOTNULL,
	created I8 NOTNULL,
	last_modified I8 NOTNULL,
	content_type_guid C(16) NOTNULL,
	format_guid C(16) NOTNULL,
	content_status_id I4 NOTNULL,
	event_time I8 NOTNULL DEFAULT 0,
	version I4,
	lang_code C(32),
	title C(160),
	ip C(39),
	data X
	CONSTRAINT '
		, CONSTRAINT `liberty_content_status_ref` FOREIGN KEY (`content_status_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content_status`( `content_status_id` )
		, CONSTRAINT `liberty_content_type_ref` FOREIGN KEY (`content_type_guid`) REFERENCES `".BIT_DB_PREFIX."liberty_content_types`( `content_type_guid` )'
",

'liberty_aliases' => "
	content_id I4 PRIMARY,
	alias_title C(190) PRIMARY
	CONSTRAINT '
		, CONSTRAINT liberty_aliases_content_fkey FOREIGN KEY( `content_id` ) REFERENCES `".BIT_DB_PREFIX."liberty_content` ( `content_id` )'
",

'liberty_content_hits' => "
	content_id I4 PRIMARY,
	hits I4 NOTNULL DEFAULT 1,
	last_hit I8 NOTNULL DEFAULT 1
	CONSTRAINT ', CONSTRAINT `liberty_content_hits_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content` ( `content_id` )'
",


'liberty_content_history' => "
	content_id I4 PRIMARY,
	version I4 PRIMARY,
	last_modified I8 NOTNULL,
	format_guid C(16) NOTNULL,
	description C(200),
	user_id C(40),
	ip C(15),
	history_comment C(200),
	data X
	CONSTRAINT ', CONSTRAINT `liberty_history_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` )'
",

// rename log_action to log_message
// rename action_comment to error_message
// error_message 200 chars to 250
// add NOTNULL to last_modified
// remove NOTNULL from content_id
'liberty_action_log' => "
	content_id I4,
	user_id I4 NOTNULL,
	last_modified I8 NOTNULL,
	title C(160),
	ip C(15),
	log_message C(250) NOTNULL,
	error_message C(250)
",

'liberty_copyrights' => "
	copyright_id I4 AUTO PRIMARY,
	page_id I4 NOTNULL,
	title C(200),
	copyright_year I8,
	authors C(200),
	copyright_order I8,
	user_id I4
",

'liberty_content_links' => "
	from_content_id I4,
	to_content_id I4,
	to_title C(160)
	CONSTRAINT ', CONSTRAINT `lib_content_links_from_ref` FOREIGN KEY (`from_content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content` (`content_id`)
				, CONSTRAINT `lib_content_links_to_ref` FOREIGN KEY (`to_content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content` (`content_id`)'
",

'liberty_content_prefs' => "
	content_id I4 PRIMARY,
	pref_name C(40) PRIMARY,
	pref_value C(250)
	CONSTRAINT ', CONSTRAINT `lib_content_prefs_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content` (`content_id`)'
",

'liberty_comments' => "
	comment_id I4 PRIMARY,
	content_id I4 NOTNULL,
	parent_id I4 NOTNULL,
	root_id I4 NOTNULL,
	thread_forward_sequence C(250),
	thread_reverse_sequence C(250),
	anon_name C(64)
	CONSTRAINT ', CONSTRAINT `liberty_comments_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` )
		        , CONSTRAINT `liberty_comments_parent_ref` FOREIGN KEY (`parent_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` )
		        , CONSTRAINT `liberty_comments_root_ref` FOREIGN KEY (`root_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` )'
",

'liberty_link_cache' => "
	cache_id I4 AUTO PRIMARY,
	url C(250),
	data B,
	refresh I8
",

'liberty_attachments' => "
	attachment_id I4 PRIMARY,
	attachment_plugin_guid C(16) NOTNULL,
	content_id I4 NOTNULL,
	foreign_id I4 NOTNULL,
	user_id I4 NOTNULL,
	pos I4,
	hits I4,
	error_code I4,
	caption C(250)
	CONSTRAINT '
		, CONSTRAINT `liberty_attachment_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` )
",

'liberty_files' => "
	file_id I4 PRIMARY,
	user_id I4 NOTNULL,
	storage_path C(250),
	file_size I4,
	mime_type C(64)
",

'liberty_structures' => "
	structure_id I4 AUTO PRIMARY,
	root_structure_id I4 NOTNULL,
	content_id I4 NOTNULL,
	structure_level I1 NOTNULL DEFAULT 1,
	pos I4,
	page_alias C(240),
	parent_id I4
",
//	CONSTRAINT ', CONSTRAINT `liberty_root_structure_id_ref` FOREIGN KEY (`root_structure_id`) REFERENCES `".BIT_DB_PREFIX."liberty_structures`( `structure_id` )'

'liberty_dynamic_variables' => "
	name C(40) PRIMARY,
	data X
",

);

global $gBitInstaller;

foreach( array_keys( $tables ) AS $tableName ) {
	$gBitInstaller->registerSchemaTable( LIBERTY_PKG_NAME, $tableName, $tables[$tableName], TRUE );
}

$gBitInstaller->registerPackageInfo( LIBERTY_PKG_NAME, array(
	'description' => "Liberty is an integral part and manages all content on your site.",
	'license' => '<a href="http://www.gnu.org/licenses/licenses.html#LGPL">LGPL</a>',
) );

// ### Indexes
$indices = array (
	'content_title_idx' => array( 'table' => 'liberty_content', 'cols' => 'title', 'opts' => NULL ),
	'content_user_idx' => array( 'table' => 'liberty_content', 'cols' => 'user_id', 'opts' => NULL ),
	'content_moduser_idx' => array( 'table' => 'liberty_content', 'cols' => 'modifier_user_id', 'opts' => NULL ),
	'content_content_hits_idx' => array( 'table' => 'liberty_content_hits', 'cols' => 'content_id', 'opts' => NULL ),
	'content_status_idx' => array( 'table' => 'liberty_content', 'cols' => 'content_status_id', 'opts' => NULL ),
	'content_alias_title_idx' => array( 'table' => 'liberty_aliases', 'cols' => 'alias_title', 'opts' => NULL ),
	'comments_object_idx' => array( 'table' => 'liberty_comments', 'cols' => 'content_id', 'opts' => NULL ),
	'comments_parent_idx' => array( 'table' => 'liberty_comments', 'cols' => 'parent_id', 'opts' => NULL ),
	'attachments_hits_idx' => array( 'table' => 'liberty_attachments', 'cols' => 'hits', 'opts' => NULL ),
	'attachments_user_id_idx' => array( 'table' => 'liberty_attachments', 'cols' => 'user_id', 'opts' => NULL ),
	'attachments_content_id_idx' => array( 'table' => 'liberty_attachments', 'cols' => 'content_id', 'opts' => NULL ),
	'st_co_foreign_guid_idx' => array( 'table' => 'liberty_attachments', 'cols' => 'content_id, foreign_id, attachment_plugin_guid', 'opts' => array( 'UNIQUE' ) ),
	'structures_root_idx' => array( 'table' => 'liberty_structures', 'cols' => 'root_structure_id', 'opts' => NULL),
	'structures_parent_idx' => array( 'table' => 'liberty_structures', 'cols' => 'parent_id', 'opts' => NULL),
	'structures_content_idx' => array( 'table' => 'liberty_structures', 'cols' => 'content_id', 'opts' => NULL),
	'to_content_id_idx' => array( 'table' => 'liberty_content_links', 'cols' => 'to_content_id', 'opts' => NULL),
	'links_from_content_id_idx' => array( 'table' => 'liberty_content_links', 'cols' => 'from_content_id', 'opts' => NULL),
	'links_title_content_id_idx' => array( 'table' => 'liberty_content_links', 'cols' => 'to_title', 'opts' => NULL)
);
$gBitInstaller->registerSchemaIndexes( LIBERTY_PKG_NAME, $indices );

// ### Sequences
$sequences = array (
	'liberty_content_id_seq' => array( 'start' => 1 ),
	'liberty_comments_id_seq' => array( 'start' => 1 ),
	'liberty_files_id_seq' => array( 'start' => 1 ),
	'liberty_attachments_id_seq' => array( 'start' => 1 ),
	'liberty_structures_id_seq' => array( 'start' => 4 )
);
$gBitInstaller->registerSchemaSequences( LIBERTY_PKG_NAME, $sequences );

// ### Default Preferences
$gBitInstaller->registerPreferences( LIBERTY_PKG_NAME, array(
	//array(LIBERTY_PKG_NAME, 'liberty_cache_images','n'),
	//array(LIBERTY_PKG_NAME, 'liberty_cache_pages','n'),
	// This is dealt with by the installer
	//array(LIBERTY_PKG_NAME, 'default_format','tikiwiki'),
	array(LIBERTY_PKG_NAME, 'liberty_auto_display_attachment_thumbs', 'small'),
	// enable action logging by default
	array(LIBERTY_PKG_NAME, 'liberty_action_log', 'y'),
//	array(LIBERTY_PKG_NAME, 'liberty_attachment_link_format', 'wiki') not needed anymore since we use js in the edit page now (depends on format of content)
) );

$gBitInstaller->registerSchemaDefault( LIBERTY_PKG_NAME, array(
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (-999, 'Deleted')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (-998, 'Spam')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (-201, 'Suspended')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (-100, 'Denied')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (-40, 'Private')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (-30, 'Password Protected')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (-20, 'Group Protected')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (-10, 'Hidden')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (10, 'Draft')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (50, 'Available')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (101, 'Pending Approval')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (102, 'Commercial')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (200, 'Recommended')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (999, 'Copy Protected')",
	"UPDATE `".BIT_DB_PREFIX."liberty_content` SET `content_status_id`=50",
) );


// ### Default UserPermissions
$gBitInstaller->registerUserPermissions( LIBERTY_PKG_NAME, array(
	array('p_liberty_edit_html_style', 'Can include style information in HTML', 'editors', LIBERTY_PKG_NAME),
	array('p_liberty_admin_comments', 'Can administer comments', 'editors', LIBERTY_PKG_NAME),
	array('p_liberty_post_comments', 'Can post new comments', 'registered', LIBERTY_PKG_NAME),
	array('p_liberty_read_comments', 'Can read comments', 'basic', LIBERTY_PKG_NAME),
	array('p_liberty_vote_comments', 'Can vote comments', 'registered', LIBERTY_PKG_NAME),
	array('p_liberty_edit_comments', 'Can edit all comments', 'editors', LIBERTY_PKG_NAME),
	array('p_liberty_attach_attachments', 'Can create content attachments', 'registered', LIBERTY_PKG_NAME),
	array('p_liberty_detach_attachment', 'Can detach content attachments', 'registered', LIBERTY_PKG_NAME),
	array('p_liberty_print', 'Can print content', 'basic', LIBERTY_PKG_NAME),
	array('p_liberty_enter_html', 'Can enter HTML', 'registered', LIBERTY_PKG_NAME),
) );

?>
