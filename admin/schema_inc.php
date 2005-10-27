<?php

// Common Content tables
$tables = array(

'tiki_plugins' => "
  plugin_guid C(16) PRIMARY,
  plugin_type C(16) NOTNULL,
  is_active C(1) NOTNULL DEFAULT 'y',
  plugin_description C(250),
  maintainer_url C(250)
",

'tiki_content_types' => "
  content_type_guid C(16) PRIMARY,
  content_description C(250) NOTNULL,
  maintainer_url C(250),
  handler_class C(128),
  handler_package C(128),
  handler_file C(128)
",

'tiki_content' => "
  content_id I4 PRIMARY,
  user_id I4 NOTNULL,
  modifier_user_id I4 NOTNULL,
  created I8 NOTNULL,
  last_modified I8 NOTNULL,
  content_type_guid C(16) NOTNULL,
  format_guid C(16) NOTNULL,
  hits I4 NOTNULL DEFAULT 0,
  language C(4),
  title C(160),
  ip C(39),
  data X
  CONSTRAINTS ', CONSTRAINT `tiki_content_type_ref` FOREIGN KEY (`content_type_guid`) REFERENCES `".BIT_DB_PREFIX."tiki_content_types`( `content_type_guid` )
    		  , CONSTRAINT `tiki_content_guid_ref`  FOREIGN KEY (`format_guid`) REFERENCES `".BIT_DB_PREFIX."tiki_plugins`( `plugin_guid` )'
",

'tiki_comments' => "
  comment_id I4 PRIMARY,
  content_id I4 NOTNULL,
  parent_id I4 NOTNULL
  CONSTRAINTS ', CONSTRAINT `tiki_comments_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."tiki_content`( `content_id` )
              , CONSTRAINT `tiki_comments_parent_ref` FOREIGN KEY (`parent_id`) REFERENCES `".BIT_DB_PREFIX."tiki_content`( `content_id` )'
",

'tiki_link_cache' => "
  cache_id I4 AUTO PRIMARY,
  url C(250),
  data B,
  refresh I8
",

'tiki_attachments' => "
  attachment_id I4 PRIMARY,
  attachment_plugin_guid C(16) NOTNULL,
  content_id I4 NOTNULL,
  foreign_id I4 NOTNULL,
  user_id I4 NOTNULL,
  pos I4,
  hits I4,
  error_code I4,
  caption C(250)
  CONSTRAINTS ', CONSTRAINT `tiki_attachment_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."tiki_content`( `content_id` )
  			  , CONSTRAINT `tiki_attachment_type_ref` FOREIGN KEY (`attachment_plugin_guid`) REFERENCES `".BIT_DB_PREFIX."tiki_plugins`( `plugin_guid` )'
",

'tiki_blobs' => '
  blob_id I4 PRIMARY,
  user_id I4 NOTNULL,
  blob_size I8 NOTNULL,
  blob_name C(250) NOTNULL,
  blob_data_type C(100) NOTNULL,
  blob_data B NOTNULL
',

'tiki_files' => "
  file_id I4 PRIMARY,
  user_id I4 NOTNULL,
  storage_path C(250),
  size I4,
  mime_type C(64)
",

'tiki_structures' => "
  structure_id I4 AUTO PRIMARY,
  root_structure_id I4 NOTNULL,
  content_id I4 NOTNULL,
  level I1 NOTNULL DEFAULT 1,
  pos I4,
  page_alias C(240),
  parent_id I4
  CONSTRAINTS ', CONSTRAINT `tiki_root_structure_id_ref` FOREIGN KEY (`root_structure_id`) REFERENCES `".BIT_DB_PREFIX."tiki_structures`( `structure_id` )'
"

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
	'tiki_content_title_idx' => array( 'table' => 'tiki_content', 'cols' => 'title', 'opts' => NULL ),
	'tiki_content_user_idx' => array( 'table' => 'tiki_content', 'cols' => 'user_id', 'opts' => NULL ),
	'tiki_content_moduser_idx' => array( 'table' => 'tiki_content', 'cols' => 'modifier_user_id', 'opts' => NULL ),
	'tiki_content_hits_idx' => array( 'table' => 'tiki_content', 'cols' => 'hits', 'opts' => NULL ),
	'tiki_comments_object_idx' => array( 'table' => 'tiki_comments', 'cols' => 'content_id', 'opts' => NULL ),
	'tiki_comments_parent_idx' => array( 'table' => 'tiki_comments', 'cols' => 'parent_id', 'opts' => NULL ),
	'tiki_attachments_hits_idx' => array( 'table' => 'tiki_attachments', 'cols' => 'hits', 'opts' => NULL ),
	'tiki_attachments_user_id_idx' => array( 'table' => 'tiki_attachments', 'cols' => 'user_id', 'opts' => NULL ),
	'tiki_attachments_content_id_idx' => array( 'table' => 'tiki_attachments', 'cols' => 'content_id', 'opts' => NULL ),
	'tiki_st_co_foreign_guid_idx' => array( 'table' => 'tiki_attachments', 'cols' => 'content_id, foreign_id, attachment_plugin_guid', 'opts' => array( 'UNIQUE' ) ),
	'tiki_plugins_guid_idx' => array( 'table' => 'tiki_plugins', 'cols' => 'plugin_guid', 'opts' => array( 'UNIQUE' ) ),
	'tiki_structures_root_idx' => array( 'table' => 'tiki_structures', 'cols' => 'root_structure_id', 'opts' => NULL),
	'tiki_structures_parent_idx' => array( 'table' => 'tiki_structures', 'cols' => 'parent_id', 'opts' => NULL),
	'tiki_structures_content_idx' => array( 'table' => 'tiki_structures', 'cols' => 'content_id', 'opts' => NULL )
);
$gBitInstaller->registerSchemaIndexes( LIBERTY_PKG_NAME, $indices );

// ### Sequences
$sequences = array (
	'tiki_content_id_seq' => array( 'start' => 1 ),
	'tiki_comments_comment_id_seq' => array( 'start' => 1 ),
	'tiki_files_file_id_seq' => array( 'start' => 1 ),
	'tiki_attachments_id_seq' => array( 'start' => 1 ),
	'tiki_structures_id_seq' => array( 'start' => 4 )
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
