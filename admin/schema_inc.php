<?php

// Common Content tables
$tables = array(

'liberty_plugins' => "
  plugin_guid C(16) PRIMARY,
	plugin_type C(16) NOTNULL,
	is_active C(1) NOTNULL DEFAULT 'y',
	plugin_description C(250),
	maintainer_url C(250)
",

'liberty_copyrights' => "
	copyright_id I4 AUTO PRIMARY,
	page_id I4 NOTNULL,
	title C(200),
	year I8,
	authors C(200),
	copyright_order I8,
	user_id I4
",

'liberty_content_history' => "
	page_id I4 PRIMARY,
	version I4 PRIMARY,
	last_modified I8 NOTNULL,
	format_guid C(16) NOTNULL,
	description C(200),
	user_id C(40),
	ip C(15),
	comment C(200),
	data X
	CONSTRAINTS ', CONSTRAINT `tiki_history_page_ref` FOREIGN KEY (`page_id`) REFERENCES `".BIT_DB_PREFIX."wiki_pages`( `page_id` )'
",

'liberty_content_links' => "
	from_content_id I4 PRIMARY,
	to_content_id I4 PRIMARY
",

'liberty_content_types' => "
  content_type_guid C(16) PRIMARY,
  content_description C(250) NOTNULL,
  maintainer_url C(250),
  handler_class C(128),
  handler_package C(128),
  handler_file C(128)
",

'liberty_content' => "
  content_id I4 PRIMARY,
  user_id I4 NOTNULL,
  modifier_user_id I4 NOTNULL,
  created I8 NOTNULL,
  last_modified I8 NOTNULL,
  content_type_guid C(16) NOTNULL,
  format_guid C(16) NOTNULL,
  hits I4 NOTNULL DEFAULT 0,
  last_hit I8 NOTNULL DEFAULT 0,
  event_time I8 NOTNULL DEFAULT 0,
  language C(4),
  title C(160),
  ip C(39),
  data X
  CONSTRAINTS ', CONSTRAINT `liberty_content_type_ref` FOREIGN KEY (`content_type_guid`) REFERENCES `".BIT_DB_PREFIX."liberty_content_types`( `content_type_guid` )
    		  , CONSTRAINT `liberty_content_guid_ref`  FOREIGN KEY (`format_guid`) REFERENCES `".BIT_DB_PREFIX."liberty_plugins`( `plugin_guid` )'
",

'liberty_content_prefs' => "
  content_id I4 PRIMARY,
  name C(40) PRIMARY,
  value C(250)
  CONSTRAINTS ', CONSTRAINT `lib_content_prefs_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content` (`content_id`)'
",

'liberty_comments' => "
  comment_id I4 PRIMARY,
  content_id I4 NOTNULL,
  parent_id I4 NOTNULL
  CONSTRAINTS ', CONSTRAINT `liberty_comments_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` )
              , CONSTRAINT `liberty_comments_parent_ref` FOREIGN KEY (`parent_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` )'
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
  CONSTRAINTS ', CONSTRAINT `liberty_attachment_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` )
  			  , CONSTRAINT `liberty_attachment_type_ref` FOREIGN KEY (`attachment_plugin_guid`) REFERENCES `".BIT_DB_PREFIX."liberty_plugins`( `plugin_guid` )'
",

//'tiki_blobs' => '
//  blob_id I4 PRIMARY,
//  user_id I4 NOTNULL,
//  blob_size I8 NOTNULL,
//  blob_name C(250) NOTNULL,
//  blob_data_type C(100) NOTNULL,
//  blob_data B NOTNULL
//',

'liberty_files' => "
  file_id I4 PRIMARY,
  user_id I4 NOTNULL,
  storage_path C(250),
  size I4,
  mime_type C(64)
",

'liberty_structures' => "
  structure_id I4 AUTO PRIMARY,
  root_structure_id I4 NOTNULL,
  content_id I4 NOTNULL,
  level I1 NOTNULL DEFAULT 1,
  pos I4,
  page_alias C(240),
  parent_id I4
  CONSTRAINTS ', CONSTRAINT `liberty_root_structure_id_ref` FOREIGN KEY (`root_structure_id`) REFERENCES `".BIT_DB_PREFIX."liberty_structures`( `structure_id` )'
",

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
	'version' => '0.1',
	'state' => 'alpha',
	'dependencies' => '',
) );

// ### Indexes
$indices = array (
	'content_title_idx' => array( 'table' => 'liberty_content', 'cols' => 'title', 'opts' => NULL ),
	'content_user_idx' => array( 'table' => 'liberty_content', 'cols' => 'user_id', 'opts' => NULL ),
	'content_moduser_idx' => array( 'table' => 'liberty_content', 'cols' => 'modifier_user_id', 'opts' => NULL ),
	'content_hits_idx' => array( 'table' => 'liberty_content', 'cols' => 'hits', 'opts' => NULL ),
	'comments_object_idx' => array( 'table' => 'liberty_comments', 'cols' => 'content_id', 'opts' => NULL ),
	'comments_parent_idx' => array( 'table' => 'liberty_comments', 'cols' => 'parent_id', 'opts' => NULL ),
	'attachments_hits_idx' => array( 'table' => 'liberty_attachments', 'cols' => 'hits', 'opts' => NULL ),
	'attachments_user_id_idx' => array( 'table' => 'liberty_attachments', 'cols' => 'user_id', 'opts' => NULL ),
	'attachments_content_id_idx' => array( 'table' => 'liberty_attachments', 'cols' => 'content_id', 'opts' => NULL ),
	'st_co_foreign_guid_idx' => array( 'table' => 'liberty_attachments', 'cols' => 'content_id, foreign_id, attachment_plugin_guid', 'opts' => array( 'UNIQUE' ) ),
	'plugins_guid_idx' => array( 'table' => 'liberty_plugins', 'cols' => 'plugin_guid', 'opts' => array( 'UNIQUE' ) ),
	'structures_root_idx' => array( 'table' => 'liberty_structures', 'cols' => 'root_structure_id', 'opts' => NULL),
	'structures_parent_idx' => array( 'table' => 'liberty_structures', 'cols' => 'parent_id', 'opts' => NULL),
	'structures_content_idx' => array( 'table' => 'liberty_structures', 'cols' => 'content_id', 'opts' => NULL )
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
	array(LIBERTY_PKG_NAME, 'cacheimages','n'),
	array(LIBERTY_PKG_NAME, 'cachepages','n'),
	array(LIBERTY_PKG_NAME, 'default_format','tikiwiki'),
	array(LIBERTY_PKG_NAME, 'liberty_auto_display_attachment_thumbs', 'y'),
//	array(LIBERTY_PKG_NAME, 'liberty_attachment_link_format', 'wiki') not needed anymore since we use js in the edit page now (depends on format of content)
) );

// ### Default UserPermissions
$gBitInstaller->registerUserPermissions( LIBERTY_PKG_NAME, array(
	array('bit_p_edit_html_style', 'Can include style information in HTML', 'editors', LIBERTY_PKG_NAME),
	array('bit_p_post_comments', 'Can post new comments', 'registered', LIBERTY_PKG_NAME),
	array('bit_p_read_comments', 'Can read comments', 'basic', LIBERTY_PKG_NAME),
	array('bit_p_remove_comments', 'Can delete comments', 'editors', LIBERTY_PKG_NAME),
	array('bit_p_vote_comments', 'Can vote comments', 'registered', LIBERTY_PKG_NAME),
	array('bit_p_edit_comments', 'Can edit all comments', 'editors', LIBERTY_PKG_NAME),
	array('bit_p_use_content_templates', 'Can use content templates', 'registered', LIBERTY_PKG_NAME),
	array('bit_p_edit_content_templates', 'Can edit content templates', 'editors', LIBERTY_PKG_NAME),
	array('bit_p_content_attachments', 'Can create content attachments', 'registered', LIBERTY_PKG_NAME),
	array('bit_p_detach_attachment', 'Can detach content attachments', 'registered', LIBERTY_PKG_NAME),
	array('bit_p_print', 'Can print content', 'basic', LIBERTY_PKG_NAME),
) );

?>
