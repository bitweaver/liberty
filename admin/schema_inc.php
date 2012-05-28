<?php

// Common Content tables
$tables = array(

'liberty_content_types' => "
	content_type_guid C(16) PRIMARY,
	content_name C(250) NOTNULL,
	content_name_plural C(250),
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
	CONSTRAINT ', CONSTRAINT `liberty_content_status_ref` FOREIGN KEY (`content_status_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content_status`( `content_status_id` )
				, CONSTRAINT `liberty_content_type_ref` FOREIGN KEY (`content_type_guid`) REFERENCES `".BIT_DB_PREFIX."liberty_content_types`( `content_type_guid` )'
",

'liberty_aliases' => "
	content_id I4 PRIMARY,
	alias_title C(190) PRIMARY
	CONSTRAINT ', CONSTRAINT liberty_aliases_content_fkey FOREIGN KEY( `content_id` ) REFERENCES `".BIT_DB_PREFIX."liberty_content` ( `content_id` )'
",

'liberty_content_data' => "
	content_id I4 PRIMARY,
	data XL NOTNULL,
	data_type C(32) PRIMARY
	CONSTRAINT ', CONSTRAINT `liberty_content_data_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content` ( `content_id` )'
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
	summary XL,
	user_id I4 NOTNULL,
	ip C(39),
	history_comment C(200),
	data XL
	CONSTRAINT ', CONSTRAINT `liberty_history_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` )'
",

'liberty_action_log' => "
	content_id I4,
	user_id I4 NOTNULL,
	last_modified I8 NOTNULL,
	title C(160),
	ip C(39),
	log_message C(250) NOTNULL DEFAULT '',
	error_message C(250) NOTNULL DEFAULT ''
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
	to_title C(160),
	pos F
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
	content_id I4,
	attachment_plugin_guid C(16) NOTNULL,
	foreign_id I4 NOTNULL,
	user_id I4 NOTNULL,
	is_primary C(1),
	pos I4,
	hits I4,
	error_code I4,
	caption C(250)
	CONSTRAINT ', CONSTRAINT `liberty_attachments_con_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` ) '
",

'liberty_attachment_prefs' => "
	attachment_id I4 PRIMARY,
	pref_name C(40) PRIMARY,
	pref_value C(250)
	CONSTRAINT ', CONSTRAINT `lib_att_prefs_content_ref` FOREIGN KEY (`attachment_id`) REFERENCES `".BIT_DB_PREFIX."liberty_attachments` (`attachment_id`)'
",

'liberty_files' => "
	file_id I4 PRIMARY,
	user_id I4 NOTNULL,
	file_name C(250),
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
	CONSTRAINT ', CONSTRAINT `lib_structures_content_id_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` )'
",
//	CONSTRAINT ', CONSTRAINT `liberty_root_structure_id_ref` FOREIGN KEY (`root_structure_id`) REFERENCES `".BIT_DB_PREFIX."liberty_structures`( `structure_id` )'

'liberty_dynamic_variables' => "
	name C(40) PRIMARY,
	data XL
",

// liberty_thumbnail_queue is being replaces with this
'liberty_process_queue' => "
	process_id I4 NOTNULL AUTO PRIMARY,
	content_id I4 NOTNULL,
	queue_date I8 NOTNULL,
	begin_date I8,
	end_date I8,
	process_status C(64),
	log_message XL,
	processor C(250),
	processor_parameters XL
",
//	CONSTRAINT ' , CONSTRAINT `liberty_process_queue` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` ) '

'liberty_content_permissions' => "
	group_id I4 PRIMARY,
	perm_name C(30) PRIMARY,
	content_id I4 PRIMARY,
	is_revoked C(1)
	CONSTRAINT   ', CONSTRAINT `liberty_content_id_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content` (`content_id`) '
",
/* chicken-and-egg constraint dependencies: Liberty needs user_id, Users needs liberty
                , CONSTRAINT `liberty_content_perm_perm_ref` FOREIGN KEY (`perm_name`) REFERENCES `".BIT_DB_PREFIX."users_permissions` (`perm_name`)
*/

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
	CONSTRAINT ', CONSTRAINT `lib_attachment_meta_id_ref`    FOREIGN KEY (`attachment_id`) REFERENCES `".BIT_DB_PREFIX."liberty_attachments` (`attachment_id`)
				, CONSTRAINT `lib_attachment_meta_type_ref`  FOREIGN KEY (`meta_type_id`)  REFERENCES `".BIT_DB_PREFIX."liberty_meta_types`  (`meta_type_id`)
				, CONSTRAINT `lib_attachment_meta_title_ref` FOREIGN KEY (`meta_title_id`) REFERENCES `".BIT_DB_PREFIX."liberty_meta_titles` (`meta_title_id`) '
",

);

global $gBitInstaller;

foreach( array_keys( $tables ) AS $tableName ) {
	$gBitInstaller->registerSchemaTable( LIBERTY_PKG_NAME, $tableName, $tables[$tableName], TRUE );
}

// Constraints which must be installed after table creation
$constraints = array(
	'liberty_content_permissions' => array('liberty_content_perm_group_ref' => 'FOREIGN KEY (`group_id`) REFERENCES `'.BIT_DB_PREFIX.'users_groups` (`group_id`)'),
	'liberty_process_queue' => array('liberty_process_queue_ref' => 'FOREIGN KEY (`content_id`) REFERENCES `'.BIT_DB_PREFIX.'liberty_content`( `content_id` )')

);
foreach( array_keys($constraints) AS $tableName ) {
	$gBitInstaller->registerSchemaConstraints( LIBERTY_PKG_NAME, $tableName, $constraints[$tableName]);
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
	'st_co_foreign_guid_idx' => array( 'table' => 'liberty_attachments', 'cols' => 'foreign_id, attachment_plugin_guid', 'opts' => array( 'UNIQUE' ) ),
	'structures_root_idx' => array( 'table' => 'liberty_structures', 'cols' => 'root_structure_id', 'opts' => NULL),
	'structures_parent_idx' => array( 'table' => 'liberty_structures', 'cols' => 'parent_id', 'opts' => NULL),
	'structures_content_idx' => array( 'table' => 'liberty_structures', 'cols' => 'content_id', 'opts' => NULL),
	'to_content_id_idx' => array( 'table' => 'liberty_content_links', 'cols' => 'to_content_id', 'opts' => NULL),
	'links_from_content_id_idx' => array( 'table' => 'liberty_content_links', 'cols' => 'from_content_id', 'opts' => NULL),
	'links_title_content_id_idx' => array( 'table' => 'liberty_content_links', 'cols' => 'to_title', 'opts' => NULL),
	'liberty_content_perm_group_idx' =>  array( 'table' => 'liberty_content_permissions', 'cols' => 'group_id', 'opts' => NULL ),
	'liberty_content_perm_perm_idx' => array( 'table' => 'liberty_content_permissions', 'cols' => 'perm_name', 'opts' => NULL ),
	'liberty_content_perm_cont_idx' => array( 'table' => 'liberty_content_permissions', 'cols' => 'content_id', 'opts' => NULL ),
	'process_id_idx' => array( 'table' => 'liberty_process_queue', 'cols' => 'content_id', 'opts' => NULL ),
	'lib_attachment_meta_idx' => array( 'table' => 'liberty_attachment_meta_data', 'cols' => 'attachment_id', 'opts' => NULL ),
	'lib_attachment_meta_type_idx' => array( 'table' => 'liberty_attachment_meta_data', 'cols' => 'meta_type_id', 'opts' => NULL ),
	'lib_attachment_meta_title_idx' => array( 'table' => 'liberty_attachment_meta_data', 'cols' => 'meta_title_id', 'opts' => NULL ),
);
$gBitInstaller->registerSchemaIndexes( LIBERTY_PKG_NAME, $indices );

// ### Sequences
$sequences = array (
	'liberty_content_id_seq'     => array( 'start' => 1 ),
	'liberty_comment_id_seq'     => array( 'start' => 1 ),
	'liberty_files_id_seq'       => array( 'start' => 1 ),
	'liberty_attachments_id_seq' => array( 'start' => 1 ),
	'liberty_structures_id_seq'  => array( 'start' => 4 ),
	'liberty_meta_types_id_seq'  => array( 'start' => 1 ),
	'liberty_meta_titles_id_seq' => array( 'start' => 1 ),
);
$gBitInstaller->registerSchemaSequences( LIBERTY_PKG_NAME, $sequences );

// ### Default Preferences
$gBitInstaller->registerPreferences( LIBERTY_PKG_NAME, array(
	//array(LIBERTY_PKG_NAME, 'liberty_cache_images','n'),
	//array(LIBERTY_PKG_NAME, 'liberty_cache_pages','n'),
	array(LIBERTY_PKG_NAME, 'liberty_auto_display_attachment_thumbs', 'small'),
	// enable action logging by default
	array(LIBERTY_PKG_NAME, 'liberty_action_log', 'y'),
//	array(LIBERTY_PKG_NAME, 'liberty_attachment_link_format', 'wiki') not needed anymore since we use js in the edit page now (depends on format of content)
//	array(LIBERTY_PKG_NAME, 'liberty_attachment_style', 'standard'),
	// The default for new installs is htmlpurifier old stays simple
	array(LIBERTY_PKG_NAME, 'liberty_html_purifier', 'htmlpurifier'),
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
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (-5, 'Draft')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (-1, 'Pending Approval')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (50, 'Available')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (102, 'Commercial')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (200, 'Recommended')",
	"INSERT INTO `".BIT_DB_PREFIX."liberty_content_status` (`content_status_id`,`content_status_name`) VALUES (999, 'Copy Protected')",
	"UPDATE `".BIT_DB_PREFIX."liberty_content` SET `content_status_id`=50",
) );


// ### Default UserPermissions
$gBitInstaller->registerUserPermissions( LIBERTY_PKG_NAME, array(
	array('p_liberty_edit_html_style', 'Can include style information in HTML', 'editors', LIBERTY_PKG_NAME),
	array('p_liberty_list_content', 'Can list content', 'basic', LIBERTY_PKG_NAME),
	array('p_liberty_admin_comments', 'Can administer comments', 'editors', LIBERTY_PKG_NAME),
	array('p_liberty_post_comments', 'Can post new comments', 'registered', LIBERTY_PKG_NAME),
	array('p_liberty_read_comments', 'Can read comments', 'basic', LIBERTY_PKG_NAME),
	array('p_liberty_edit_comments', 'Can edit all comments', 'editors', LIBERTY_PKG_NAME),
	array('p_liberty_attach_attachments', 'Can create content attachments', 'registered', LIBERTY_PKG_NAME),
	array('p_liberty_detach_attachment', 'Can detach content attachments', 'registered', LIBERTY_PKG_NAME),
	array('p_liberty_delete_attachment', 'Can delete content attachments', 'registered', LIBERTY_PKG_NAME),
	array('p_liberty_print', 'Can print content', 'basic', LIBERTY_PKG_NAME),
	array('p_liberty_enter_html', 'Can enter HTML', 'registered', LIBERTY_PKG_NAME),
	array('p_liberty_edit_content_status', 'Can edit the status of content', 'registered', LIBERTY_PKG_NAME),
	array('p_liberty_edit_all_status', 'Can edit the status of content using all status', 'editors', LIBERTY_PKG_NAME),
	array('p_liberty_view_all_status', 'Can view content with any status', 'editors', LIBERTY_PKG_NAME),
	array('p_liberty_edit_content_owner', 'Can edit the owner of content', 'editors', LIBERTY_PKG_NAME),
	array('p_liberty_edit_content_alias', 'Can edit the alternate titles of content', 'editors', LIBERTY_PKG_NAME),
	array('p_liberty_assign_content_perms', 'Can assign individual content permissions', 'editors', LIBERTY_PKG_NAME),
	array('p_liberty_trusted_editor', 'Can make edits to content as a trusted editor', 'editors', LIBERTY_PKG_NAME ),
));

// Package Requirements
$gBitInstaller->registerRequirements( LIBERTY_PKG_NAME, array(
	'users'     => array( 'min' => '2.1.0' ),
	'kernel'    => array( 'min' => '2.0.0' ),
	'themes'    => array( 'min' => '2.0.0' ),
	'languages' => array( 'min' => '2.0.0' ),
	'storage'   => array( 'min' => '0.0.0' ),
));

?>
