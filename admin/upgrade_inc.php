<?php

global $gBitSystem, $gUpgradeFrom, $gUpgradeTo;

$upgrades = array(

'BONNIE' => array(
	'CLYDE' => array(

// Step 0
array( 'QUERY' =>
array( 'PGSQL' => array(
	"ALTER TABLE `".BIT_DB_PREFIX."tiki_files_pkey` RENAME TO `".BIT_DB_PREFIX."tiki_old_files_pkey`",
)),
),

// Step 1
array( 'DATADICT' => array(
array( 'DROPTABLE' => array(
	'tiki_content'
)),
array( 'RENAMETABLE' => array(
		'tiki_files' => 'tiki_old_files',
)),

array( 'CREATE' => array (
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
		hits I4,
		language C(4),
		title C(160),
		ip C(39),
		data X
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

	'tiki_blobs' => "
		blob_id I4 PRIMARY,
		user_id I4 NOTNULL,
		blob_size I8 NOTNULL,
		blob_name C(250) NOTNULL,
		blob_data_type C(100) NOTNULL,
		blob_data B NOTNULL
	",

	'tiki_files' => "
		file_id I4 PRIMARY,
		user_id I4 NOTNULL,
		storage_path C(250),
		size I4,
		mime_type C(64)
	",
)),
array( 'RENAMECOLUMN' => array(
	'tiki_structures' => array(
		'`page_ref_id`' => 'structure_id I4 AUTO'
	),
	'tiki_link_cache' => array(
		'`cacheId`' => 'cache_id I4 AUTO'
	),
	'tiki_comments' => array(
		'`threadId`' => 'comment_id I4 AUTO',
		'`parentId`' => 'parent_id I4',
	)
)),

array( 'ALTER' => array(
	'tiki_structures' => array(
		'user_id' => array( 'user_id', 'I4' ),
		'content_id' => array( 'content_id', 'I4' ),
		'root_structure_id' => array( 'root_structure_id', 'I4' ),
	),
	'tiki_comments' => array(
		'content_id' => array( 'content_id', 'I4' ), // , 'NOTNULL' ),
	),

)),
)),

// Step 2
array( 'QUERY' =>
array( 'SQL92' => array(
	"UPDATE `".BIT_DB_PREFIX."tiki_structures` SET user_id=1",
	"INSERT INTO `".BIT_DB_PREFIX."tiki_preferences` ( `name`, `value`, `package` ) VALUES ( 'liberty_auto_display_attachment_thumbs', 'y', 'liberty' )",
	)),
/*
array( 'PGSQL' => array(
	"ALTER TABLE `".BIT_DB_PREFIX."tiki_structures` ALTER user_id SET NOT NULL"
	"ALTER TABLE `".BIT_DB_PREFIX."tiki_comments_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."tiki_content`( `content_id` )
	"ALTER TABLE `".BIT_DB_PREFIX."tiki_comments_parent_ref` FOREIGN KEY (`parent_id`) REFERENCES `".BIT_DB_PREFIX."tiki_content`( `content_id` )'
	"ALTER TABLE `".BIT_DB_PREFIX."tiki_content` ADD CONSTRAINT `tiki_content_guid_ref`  FOREIGN KEY (`format_guid`) REFERENCES ".BIT_DB_PREFIX."tiki_plugins( `plugin_guid` )"
	"ALTER TABLE `".BIT_DB_PREFIX."tiki_content` ADD CONSTRAINT `tiki_content_type_ref` FOREIGN KEY (`content_type_guid`) REFERENCES `".BIT_DB_PREFIX."tiki_content_types`( `content_type_guid` )" ),
)),

threadId
object
objectType
parentId
userName
type
points
votes
average
hash
summary
smiley
message_id
in_reply_to
comment_rating

commentDate
hits
data
title
user_ip

*/
),

// STEP 2
array( 'PHP' => '
	global $gBitSystem;
	$gBitSystem->mDb->CreateSequence( "tiki_attachments_id_seq", 1 );
	$gBitSystem->mDb->CreateSequence( "tiki_content_id_seq", 1 );
	$gBitSystem->mDb->CreateSequence( "tiki_files_file_id_seq", 1 );
	$max = $gBitSystem->mDb->getOne( "SELECT MAX(`comment_id`) FROM `'.BIT_DB_PREFIX.'tiki_comments`" );
	$gBitSystem->mDb->CreateSequence( "tiki_comments_comment_id_seq", $max + 1 );
	$max = $gBitSystem->mDb->getOne( "SELECT MAX(`structure_id`) FROM `'.BIT_DB_PREFIX.'tiki_structures`" );
	$gBitSystem->mDb->CreateSequence( "tiki_structures_id_seq", $max + 1 );

	$query = "SELECT `comment_id`, uu.`user_id`, uu.`user_id` AS `modifier_user_id`, `commentDate` AS `created`, `commentDate` AS `last_modified`, `hits`, `data`, `title`, `user_ip` AS `ip`
			  FROM `'.BIT_DB_PREFIX.'tiki_comments` tc INNER JOIN `'.BIT_DB_PREFIX.'users_users` uu ON( tc.`userName`=uu.`login` )";
	if( $rs = $gBitSystem->mDb->query( $query ) ) {
		while( !$rs->EOF ) {
			$commentId = $rs->fields["comment_id"]; unset( $rs->fields["comment_id"] );
			$conId = $gBitSystem->mDb->GenID( "tiki_content_id_seq" );
			$rs->fields["content_id"] = $conId;
			$rs->fields["content_type_guid"] = BITCOMMENT_CONTENT_TYPE_GUID;
			$rs->fields["format_guid"] = PLUGIN_GUID_TIKIWIKI;
			$gBitSystem->mDb->associateInsert( "tiki_content", $rs->fields );
			$gBitSystem->mDb->query( "UPDATE `'.BIT_DB_PREFIX.'tiki_comments` SET `content_id`=? WHERE `comment_id`=?", array( $conId, $commentId ) );
			$rs->MoveNext();
		}
	}


' ),

// STEP 3
array( 'DATADICT' => array(
	array( 'DROPCOLUMN' => array(
		'tiki_comments' => array( '`userName`', '`commentDate`','`hits`','`data`','`title`','`user_ip`' ),
	)),
	array( 'CREATEINDEX' => array(
		'tiki_content_title_idx' => array( 'tiki_content', '`title`', array() ),
		'tiki_content_user_idx' => array( 'tiki_content', '`user_id`', array() ),
		'tiki_content_moduser_idx' => array( 'tiki_content', '`modifier_user_id`', array() ),
		'tiki_content_hits_idx' => array( 'tiki_content', '`hits`', array() ),
		'tiki_comments_content_idx' => array( 'tiki_comments', '`content_id`', array() ),
		'tiki_struct_user_idx' => array( 'tiki_structures', '`user_id`', array() ),
		'tiki_struct_root_idx' => array( 'tiki_structures', '`root_structure_id`', array() ),
		'tiki_struct_content_idx' => array( 'tiki_structures', '`content_id`', array() ),
	)),
)),
	)
)

);

if( isset( $upgrades[$gUpgradeFrom][$gUpgradeTo] ) ) {
	$gBitSystem->registerUpgrade( LIBERTY_PKG_NAME, $upgrades[$gUpgradeFrom][$gUpgradeTo] );
}


?>
