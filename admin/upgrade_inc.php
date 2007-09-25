<?php

global $gBitSystem, $gUpgradeFrom, $gUpgradeTo;

$upgrades = array(

	'BWR1' => array(
		'BWR2' => array(
			// STEP 1
array( 'QUERY' =>
	array( 'SQL92' => array(
		"ALTER TABLE `".BIT_DB_PREFIX."tiki_content` ADD `last_hit` INT8 NOT NULL DEFAULT 0",
		"ALTER TABLE `".BIT_DB_PREFIX."tiki_content` ADD `event_time` INT8 NOT NULL DEFAULT 0",
		"UPDATE `".BIT_DB_PREFIX."tiki_content` SET `last_hit` = `last_modified` ,`event_time` = 0",
	)),
),

array( 'DATADICT' => array(
	array( 'RENAMETABLE' => array(
		'tiki_content'                 => 'liberty_content',
		'tiki_content_id_seq'          => 'liberty_content_id_seq',
		'tiki_attachments'             => 'liberty_attachments',
		'tiki_attachments_id_seq'      => 'liberty_attachments_id_seq',
		'tiki_files'                   => 'liberty_files',
		'tiki_files_file_id_seq'       => 'liberty_files_id_seq',
		'tiki_structures'              => 'liberty_structures',
		'tiki_structures_id_seq'       => 'liberty_structures_id_seq',
		'tiki_comments'                => 'liberty_comments',
		'tiki_comments_comment_id_seq' => 'liberty_comment_id_seq',
		'tiki_content_types'           => 'liberty_content_types',
		'tiki_link_cache'              => 'liberty_link_cache',
		'tiki_history'                 => 'liberty_content_history',
		'tiki_actionlog'               => 'liberty_action_log',
		'tiki_copyrights'              => 'liberty_copyrights',
		'tiki_links'                   => 'liberty_content_links',
		'tiki_user_preferences'        => 'liberty_content_prefs',
		'users_object_permissions'     => 'liberty_content_permissions',
	)),
	array( 'CREATE' => array (
		'liberty_content_summaries' => "
			content_id I4 PRIMARY,
			summary X NOTNULL
			CONSTRAINTS ', CONSTRAINT `liberty_content_summaries_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` ) '
		",
		'liberty_content_hits' => "
			content_id I4 PRIMARY,
			hits I4 NOTNULL DEFAULT 1,
			last_hit I8 NOTNULL DEFAULT 1
			CONSTRAINTS ', CONSTRAINT `liberty_content_hits_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` ) '
		",
		'liberty_content_status' => "
			content_status_id I4 PRIMARY KEY,
			content_status_name C(128) NOTNULL
		",
		'liberty_aliases' => "
			alias_title C(250) PRIMARY,
			content_id INT NOTNULL PRIMARY
			CONSTRAINTS ', CONSTRAINT liberty_aliases_content_fkey FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`(`content_id`) '
		",
		'liberty_meta_types' => "
			meta_type_guid C(16) PRIMARY,
			meta_type_title C(250) NOTNULL
		",
		'liberty_meta_data' => "
			meta_key C(250) PRIMARY,
			meta_type_guid C(16),
			meta_title C(250) NOTNULL,
			meta_value_short C(250),
			meta_value_long X
			CONSTRAINT ' , CONSTRAINT `liberty_meta_guid_ref` FOREIGN KEY (`meta_type_guid`) REFERENCES `".BIT_DB_PREFIX."liberty_meta_types` (`meta_type_guid`) '
		",
		'liberty_meta_content_map' => "
			content_id I4 PRIMARY NOTNULL,
			meta_key C(250) PRIMARY NOTNULL,
			CONSTRAINT '
				, CONSTRAINT `liberty_meta_map_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content` (`content_id`)
				, CONSTRAINT `liberty_meta_map_meta_key_ref` FOREIGN KEY (`meta_key`) REFERENCES `".BIT_DB_PREFIX."liberty_meta_data` (`meta_key`)'
		",
		'liberty_content_connection_map' => "
			from_content_id I4 PRIMARY,
			to_content_id I4 PRIMARY,
			item_position F
			CONSTRAINT '
				, CONSTRAINT `liberty_from_content_id_ref` FOREIGN KEY (`from_content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content` (`content_id`)
				, CONSTRAINT `liberty_to_content_id_ref` FOREIGN KEY (`to_content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content` (`content_id`)'
		",
	)),
	array( 'ALTER' => array(
		'liberty_content' => array(
			'lang_code' => array( '`lang_code`', 'VARCHAR(32)' ),
			'content_status_id' => array( '`content_status_id`', 'I4' ),
			// this has moved to liberty_attachments.is_primary
			//'primary_attachment_id' => array( '`primary_attachment_id`', 'I4' ),
		),
		'liberty_action_log' => array(
			'log_action' => array( '`log_message`', 'VARCHAR(250)' ),
			'action_comment' => array( '`error_message`', 'VARCHAR(250)' ),
			'content_id' => array( '`content_id`', 'I4' ),
		),
		'liberty_thumbnail_queue' => array(
			'processor' => array( '`processor`', 'VARCHAR(250)' ),
			'processor_parameters' => array( '`processor_parameters`', 'VARCHAR(250)' ),
		),
		'liberty_content_permissions' => array(
			'object_id' => array( '`content_id`', 'I4' ),
			'is_revoked' => array( '`is_revoked`', 'VARCHAR(1)' ),
		),
	)),
	/* The installer can't add constraints after table creation yet so drop this constraint.
	array( 'SQL' => array(
			   'ALTER TABLE `'.BIT_DB_PREFIX.'liberty_content` ADD CONSTRAINT liberty_content_attachment_ref FOREIGN KEY (primary_attachment_id) REFERENCES `'.BIT_DB_PREFIX.'liberty_attachments` (attachment_id)'
    )),
	*/
)),


// drop the original thumbnail_queue table - comment for now since there is no real harm in leaving this table around for a bit longer
//array( 'DATADICT' => array(
//	array( 'DROPTABLE' => array(
//		'liberty_thumbnail_queue'
//	)),
//)),

// we need to change some column properties in liberty_action_log
array( 'DATADICT' => array(
	array( 'CREATE' => array(
		// liberty_thumbnail_queue is being replaces with this
		'liberty_process_queue' => "
			process_id I4 NOTNULL AUTO,
			content_id I4 NOTNULL,
			queue_date I8 NOTNULL,
			begin_date I8,
			end_date I8,
			process_status C(64),
			log_message X,
			processor C(250),
			processor_parameters X
			CONSTRAINT ', CONSTRAINT `liberty_process_queue` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` ) '
		",
	)),
	// rename original column
	array( 'RENAMECOLUMN' => array(
		'liberty_action_log'  => array(
			'`log_message`'   => "`temp_log` VARCHAR(250)",                // set log_message NOTNULL DEFAULT ''
			'`error_message`' => "`temp_error` VARCHAR(250)",                      // set error_message NOTNULL DEFAULT ''
			'`content_id`'    => "`temp_id` INT NOTNULL",                   // remove NOTNULL from content_id
		),
	)),
	// add new column
	array( 'ALTER' => array(
		'liberty_action_log' => array(
			'log_message'    => array( '`log_message`', "VARCHAR(250)" ),
			'error_message'  => array( '`error_message`', "VARCHAR(250)" ),
			'content_id'     => array( '`content_id`', 'INT' ),
		),
	)),
)),
// copy all the data accross
array( 'QUERY' =>
	array( 'SQL92' => array(
		"UPDATE `".BIT_DB_PREFIX."liberty_action_log` SET `log_message` = `temp_log`",
		// error messages are probably all set to NULL at this point
		"UPDATE `".BIT_DB_PREFIX."liberty_action_log` SET `temp_error` = '' WHERE `temp_error` IS NULL",
		"UPDATE `".BIT_DB_PREFIX."liberty_action_log` SET `error_message` = `temp_error`",
		"UPDATE `".BIT_DB_PREFIX."liberty_action_log` SET `content_id` = `temp_id`",
	)),
),
// drop original column
array( 'DATADICT' => array(
	array( 'DROPCOLUMN' => array(
		'liberty_action_log' => array( '`temp_log`' ),
		'liberty_action_log' => array( '`temp_error`' ),
		'liberty_action_log' => array( '`temp_id`' ),
		'liberty_content_permissions' => array( '`object_type`' ),
	)),
)),

/* this table has been dropped again
// Create attachments map table
array( 'DATADICT' => array(
	array( 'CREATE' => array (
		'liberty_attachments_map' => "
			attachment_id I4 PRIMARY,
			content_id I4 PRIMARY,
			item_position I4
			CONSTRAINT '
				, CONSTRAINT `liberty_attachments_map_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` )
				, CONSTRAINT `liberty_attachments_map_attachment_ref` FOREIGN KEY (`attachment_id`) REFERENCES `".BIT_DB_PREFIX."liberty_attachments`( `attachment_id` ) '
",
	)),
)),
// Copy data from the old attachments system
array( 'QUERY' =>
	array( 'SQL92' => array(
		"INSERT INTO `".BIT_DB_PREFIX."liberty_attachments_map` (attachment_id, content_id) (SELECT attachment_id, content_id FROM `".BIT_DB_PREFIX."liberty_attachments`)",
	)),
),
 */
// drop original column
array( 'DATADICT' => array(
	array( 'DROPCOLUMN' => array(
		'liberty_attachments' => array( '`content_id`' ),
	)),
)),

// move hits and last_hit to a new table
array( 'QUERY' =>
	array( 'SQL92' => array(
		"INSERT INTO `".BIT_DB_PREFIX."liberty_content_hits` ( content_id, hits, last_hit ) SELECT content_id, hits, last_hit from `".BIT_DB_PREFIX."liberty_content`",
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
	)),
),


array( 'DATADICT' => array(
	array( 'DROPCOLUMN' => array(
		'liberty_content' => array( 
			'`last_hit`',
			'`hits`',
		),
	)),
)),


// content links
array( 'QUERY' =>
	array( 'MYSQL' => array(
		"ALTER TABLE `".BIT_DB_PREFIX."liberty_content_links` DROP PRIMARY KEY",
	)),
),

array( 'DATADICT' => array(
	array( 'ALTER' => array(
		'liberty_content_links' => array(
			'to_title' => array( '`to_title`', 'VARCHAR(160)' ),
		),
	)),
	array( 'CREATEINDEX' => array(
		'liberty_content_links_title_idx' => array( 'liberty_content_links', '`to_title`', array() ),
		'process_id_idx' => array( 'liberty_process_queue', 'content_id', array() ),
		'liberty_meta_map_content_idx' => array( 'liberty_meta_content_map', 'content_id', array() ),
		'liberty_meta_map_key_idx' => array( 'liberty_meta_content_map', 'meta_key', array() ),
		'liberty_meta_data_key_idx' => array( 'liberty_meta_data', 'meta_key', array() ),
		'liberty_meta_data_guid_idx' => array( 'liberty_meta_data', 'meta_key', array() ),
		'liberty_meta_data_title_idx' => array( 'liberty_meta_data', 'meta_key', array() ),
		'liberty_meta_data_values_idx' => array( 'liberty_meta_data', 'meta_key', array() ),
	)),
)),

// liberty_content_links php stuff comes in large PHP block below
array( 'QUERY' =>
	array( 'SQL92' => array(
		"UPDATE `".BIT_DB_PREFIX."liberty_content_links` SET to_title = (SELECT title FROM `".BIT_DB_PREFIX."liberty_content` lc WHERE `".BIT_DB_PREFIX."liberty_content_links`.`to_content_id`=lc.`content_id`)",
		"DELETE FROM `".BIT_DB_PREFIX."liberty_content_links` WHERE to_title IS NULL",
#		"ALTER TABLE liberty_content_links ALTER to_content_id DROP NOT NULL",
	)),
),

array( 'QUERY' =>
	array( 'SQL92' => array(
		"UPDATE `".BIT_DB_PREFIX."liberty_content` SET lang_code=language"
	)),
),

array( 'DATADICT' => array(
	array( 'DROPCOLUMN' => array(
		'liberty_content' => array( '`language`' ),
	)),
)),

// generic history for all content
array( 'DATADICT' => array(
	array( 'ALTER' => array (
		'liberty_content_history' => array(
			'content_id' => array( '`content_id`', 'I4' ),
		),
		'liberty_content' => array(
			'version' => array( '`version`', 'I4' ),
		),
	)),
)),

array( 'QUERY' =>
	array( 'SQL92' => array(
		"UPDATE `".BIT_DB_PREFIX."liberty_content_history` SET `content_id`=(SELECT `content_id` FROM `".BIT_DB_PREFIX."tiki_pages` wp WHERE wp.`page_id`=`".BIT_DB_PREFIX."liberty_content_history`.`page_id`)",
		"UPDATE `".BIT_DB_PREFIX."liberty_content` SET version=(SELECT `version` from `".BIT_DB_PREFIX."tiki_pages` wp WHERE wp.`content_id`=`".BIT_DB_PREFIX."liberty_content`.`content_id`)"
	)),
),

array( 'DATADICT' => array(
	array( 'DROPCOLUMN' => array(
		'liberty_content_history' => array( '`page_id`' ),
		'tiki_pages'              => array( '`version`' ),
	)),
)),

// changes for materialized path support for comments
array( 'DATADICT' => array(
	array( 'ALTER' => array (
		'liberty_comments' => array(
			'root_id' => array( 'root_id', 'I4' ),
			'thread_forward_sequence' => '`thread_forward_sequence` C(250)',
			'thread_reverse_sequence' => '`thread_reverse_sequence` C(250)' ,
			// some other unrelated fields
			'anon_name' => '`anon_name` C(64)' ,
		),
	)),

	array( 'CREATEINDEX' => array(
		'thread_forward_idx' => array( 'liberty_comments', '`root_id`,`thread_forward_sequence`', array( 'UNIQUE' ) ),
		'thread_reverse_idx' => array( 'liberty_comments', '`root_id`,`thread_reverse_sequence`', array( 'UNIQUE' ) ),
	)),
)),

// straight from http://www.bitweaver.org/storage/comments_updater.txt -- not tested yet
array( 'PHP' => '
	#require_once( "../bit_setup_inc.php" );
	#require_once( WIKI_PKG_PATH."BitPage.php" );
	require_once( LIBERTY_PKG_PATH."LibertyBase.php");

	global $gQueryUserId;
	require_once( LIBERTY_PKG_PATH."LibertyComment.php" );
	$cmt = new LibertyComment();
	$max_records = 100000;
	$allComments = $cmt->getList(
		array(
			"max_records" => $max_records,
			"created_ge" => 0,
		)
	);

	if(  !empty( $allComments ) ) {

		foreach ($allComments as $comment) {
			//error_log( "x=" . serialize($comment) );
			# exit;
			$comment_id = $comment["comment_id"];
			$parent_content_type = $comment["content_type_guid"];
			$content_title = $comment["content_title"];
			$content_id = $comment["content_id"];
			$parent_id = $comment["parent_id"];
			$created = $comment["created"];
			$last_modified = $comment["last_modified"];
			$title = $comment["title"];
			$user = $comment["creator_user"];
			$user_name = $comment["real_name"];
			$data = $comment["data"];
			$content_type_guid = $comment["content_type_guid"];

			// assume not usable as not converted yet
			$root_id = $comment["root_id"];

			//make guess of parent_id -- refine later
			$root_content_id_of_comment[$content_id] = $parent_id;

			$parent_content_id_of_comment[$content_id] = $parent_id;
			$content_type_guid_of_comment[$content_id] = $content_type_guid;
			$depth_of_comment[$content_id] =  1;
			$comment_id_of_comment[$content_id] = $comment_id;
			// leave alone comments with bogus data in them
			if ($parent_id < 1) {
				error_log("bad parent ID: $content_id for comment id: $comment_id with content id of: $content_id");
				$comment_status[$content_id] = 0;
				}
			if ($parent_id == $content_id) {
				error_log("bad parent ID(loop): $content_id for comment id: $comment_id with content id of: $content_id");
				$comment_status[$content_id] = 0;
				}
			elseif ($content_id < 1) {
				error_log("bad content ID for comment id: $comment_id with content id of: $content_id");
				$comment_status[$content_id] = 0;
				}
			else {
				$comment_status[$content_id] = 1;
				}	
			  //echo "A comment: $comment_id content: $content_id parent: $parent_id root: $root_id title: $title\n";

		}

//		error_log( serialize($content_type_guid_of_comment) );

		//calc comment root and depth
		$loop_done = 0;
		while (!$loop_done) {
			$c = 0;
			foreach ($allComments as $comment) {
				$content_id = $comment["content_id"];
				$comment_id = $comment["comment_id"];
				$parent_id = $comment["parent_id"];
				$title = $comment["title"];
				if (!$comment_status[$content_id]) {
					continue;
					}
				$root_id = $root_content_id_of_comment[$content_id];
				$root_content_type = empty($content_type_guid_of_comment[$root_id]) ? "notcomment" : $content_type_guid_of_comment[$root_id];

				if ($root_content_type == "bitcomment") {
					// its a comment on a comment
					// need to go back one more level
					$root_content_id_of_comment[$content_id] = $parent_content_id_of_comment[$root_id];
					// prevent loops resulting from corrupted comment trees
					if ($depth_of_comment[$content_id] < 20) {
						$depth_of_comment[$content_id]++;
						$c++;
						}
				}
			}
			
			if ($c <= 0) {
				$loop_done = 1;
			}
		}


		error_log("depth set loop done");

		function jc ($a, $b) {
			global $root_table;
			global $depth_of_comment;
			global $comment_id_of_comment;
			global $root_content_id_of_comment;

			$content_id_a = $a["content_id"];
			$content_id_b = $b["content_id"];

			$parent_id_a = $a["parent_id"];
			$parent_id_b = $b["parent_id"];

			$root_a = $root_content_id_of_comment[$content_id_a];
			$root_b = $root_content_id_of_comment[$content_id_b];


			$depth_a = $depth_of_comment[$content_id_a];
			$depth_b = $depth_of_comment[$content_id_b];

			$id_a = $comment_id_of_comment[$content_id_a];
			$id_b = $comment_id_of_comment[$content_id_b];

			$key_a = sprintf("%08d %08d %08d",$root_a,$depth_a,$parent_id_a);
			$key_b = sprintf("%08d %08d %08d",$root_b,$depth_b,$parent_id_b);;

			if ($key_a == $key_b) {
				return 0;
			}

			return ($key_a < $key_b) ? -1: +1;
		}

		usort($allComments, "jc");

		foreach ($allComments as $comment) {
			$content_id = $comment["content_id"];
			$comment_id = $comment["comment_id"];
			if (!$comment_status[$content_id]) {
				continue;
				}
			$parent_content_type = $comment["content_type_guid"];
			$content_title = $comment["content_title"];
			$parent_id = $comment["parent_id"];
			$created = $comment["created"];
			$last_modified = $comment["last_modified"];
			$title = $comment["title"];
			$user = $comment["creator_user"];
			$user_name = $comment["real_name"];
			$data = $comment["data"];

			$root_id = $root_content_id_of_comment[$content_id];
			$depth = $depth_of_comment[$content_id];

			// we used to number sequentially, easier to just use comemnt ID
			#  $root_table_seq[$parent_id . "-" . $depth] =  empty($root_table_seq[$parent_id . "-" . $depth]) ? 1: $root_table_seq[$parent_id . "-" . $depth] + 1; 
			$root_table_seq[$parent_id . "-" . $depth] = $comment_id;

			$root_table_seq3[$content_id] = $root_table_seq[$parent_id . "-" . $depth];  

			  //echo "C comment $comment_id content: $content_id parent: $parent_id root: $root_id depth: $depth title: $title\n";
			  //echo "update bit_liberty_comments set root_id=$root_id where comment_id=$comment_id;\n";
			$sql = "UPDATE `".BIT_DB_PREFIX."liberty_comments` SET `root_id` = ? where `comment_id` = ?";
			echo $sql . "  ($root_id, $comment_id)\n";
			$result = $gBitSystem->mDb->query($sql, array($root_id, $comment_id));
			//echo "result=" . serialize($result) . "\n";

		}


		foreach ($allComments as $comment) {
			$content_id = $comment["content_id"];
			$comment_id = $comment["comment_id"];
			if (!$comment_status[$content_id]) {
				continue;
				}
			$parent_content_type = $comment["content_type_guid"];
			$content_title = $comment["content_title"];
			$parent_id = $comment["parent_id"];
			$created = $comment["created"];
			$last_modified = $comment["last_modified"];
			$title = $comment["title"];
			$user = $comment["creator_user"];
			$user_name = $comment["real_name"];
			$data = $comment["data"];

			$root_id = $root_content_id_of_comment[$content_id];
			$depth = $depth_of_comment[$content_id];

			$seq = sprintf("%09d",$root_table_seq3[$content_id]);

			$x = $parent_id;
			while (!empty($root_table_seq3[$x])) {
				$seq = sprintf("%09d",$root_table_seq3[$x]) . "." . $seq;
				$x = $parent_content_id_of_comment[$x];
			}


			$seq .= ".";
			if (strlen($seq) > 25*10) {
				echo "restricting depth: comment: $comment_id, content_id=$content_id\n";
				$seq = substr($seq,0,24*10) . sprintf("%09d",$comment_id) . ".";
			}


			#  $seq_r .= ".";

			$seq_r = strtr($seq, "0123456789", "9876543210");

			  echo "D comment $comment_id content: $content_id parent: $parent_id root: $root_id depth: $depth title: $title\n";
			  //echo "  seq=$seq=\n";
			  //echo " rseq=$seq_r=\n";

			  //echo "update bit_liberty_comments set thread_forward_sequence=$seq= where comment_id=$comment_id;\n";
			  //echo "update bit_liberty_comments set thread_reverse_sequence=$seq_r= where comment_id=$comment_id;\n";
			$sql = "UPDATE `".BIT_DB_PREFIX."liberty_comments` SET `thread_forward_sequence` = ? where `comment_id` = ?";
			echo $sql . "   ($seq, $comment_id)\n";
			$result = $gBitSystem->mDb->query($sql, array($seq, $comment_id));

			$sql = "UPDATE `".BIT_DB_PREFIX."liberty_comments` SET `thread_reverse_sequence` = ? where `comment_id` = ?";
			echo $sql . "   ($seq_r, $comment_id)\n";
			$result = $gBitSystem->mDb->query($sql, array($seq_r, $comment_id));
		}

		// get content links up to speed
		require_once( LIBERTY_PKG_PATH."plugins/format.tikiwiki.php");

		$bb = new BitBase;
		// remove all existing links
		$query = "DELETE  FROM `".BIT_DB_PREFIX."liberty_content_links` ";
		$result = $bb->mDb->query($query, array());


		// get list of all wiki pages in tikiwiki format
		$ci = 0;
		$query = "SELECT `content_id`, `data` AS `edit`, `title` FROM `".BIT_DB_PREFIX."liberty_content` WHERE `format_guid`=?";
		if( $result = $bb->mDb->query($query, array( "tikiwiki" ) ) ) {
			// generate links for each content item
			while( $row = $result->fetchRow() ) {
				$content_id = $row["content_id"];
#				$tp = new TikiWikiParser();
				$tp = new BitLinks();
				$tp->storeLinks($row);
				$ci++;
			}
		}
	}
'),
array( 'DATADICT' => array(
	array( 'RENAMECOLUMN' => array(
		'liberty_files' => array(
			'`size`' => '`file_size` I4'
		),
		'liberty_structures' => array(
			'`level`' => '`structure_level` I1 NOTNULL DEFAULT 1'
		),
		'liberty_content_history' => array(
			'`comment`' => '`history_comment` C(200)'
		),
		'liberty_action_log' => array(
			'`comment`' => '`action_comment` C(200)'
		),
	)),
)),
array( 'QUERY' =>
	array( 'SQL92' => array(
		"UPDATE `".BIT_DB_PREFIX."liberty_action_log` SET `content_id`=( SELECT `content_id` FROM `".BIT_DB_PREFIX."tiki_pages` tp WHERE tp.`page_id`=`".BIT_DB_PREFIX."liberty_action_log`.`page_id` )"
	)),
),
array( 'DATADICT' => array(
	array( 'DROPCOLUMN' => array(
		'liberty_action_log' => array( '`page_id`' ),
	)),
	array( 'RENAMECOLUMN' => array(
		'liberty_action_log' => array(
			'`action`' => '`log_action` C(255) NOTNULL',
		),
		'liberty_copyrights' => array(
			'`year`' => '`copyright_year` I8',
		),
		'liberty_content_prefs' => array(
			'`value`' => '`pref_value` C(250)',
			'`user_id`'=> 'content_id I4',
		),
	)),
	array( 'CREATEINDEX' => array(
		'liberty_content_prefs_idx' => array( 'liberty_content_prefs', '`content_id`,`pref_name`', array() ),
	)),
)),

array( 'QUERY' =>
	array( 'MYSQL' => array(
		"ALTER TABLE `".BIT_DB_PREFIX."liberty_content_prefs` DROP PRIMARY KEY",
	)),
),


// rename some liberty_content_prefs
array( 'QUERY' =>
	array( 'SQL92' => array(
		"UPDATE `".BIT_DB_PREFIX."liberty_content_prefs` SET `pref_name`='users_country' WHERE `pref_name`='country'",
		"UPDATE `".BIT_DB_PREFIX."liberty_content_prefs` SET `pref_name`='users_homepage' WHERE `pref_name`='homePage'",
		"UPDATE `".BIT_DB_PREFIX."liberty_content_prefs` SET `pref_name`='users_double_click' WHERE `pref_name`='user_dbl'",
		"UPDATE `".BIT_DB_PREFIX."liberty_content_prefs` SET `pref_name`='users_bread_crumb' WHERE `pref_name`='userbreadCrumb'",
		"UPDATE `".BIT_DB_PREFIX."liberty_content_prefs` SET `pref_name`='users_information' WHERE `pref_name`='user_information'",
		"UPDATE `".BIT_DB_PREFIX."liberty_content_prefs` SET `pref_name`='users_email_display' WHERE `pref_name`='email is public'",
		"UPDATE `".BIT_DB_PREFIX."liberty_content_prefs` SET `pref_name`='messages_max_records' WHERE `pref_name`='mess_max_records'",
		"UPDATE `".BIT_DB_PREFIX."liberty_content_prefs` SET `pref_name`='messages_min_priority' WHERE `pref_name`='minPrio'",
		"UPDATE `".BIT_DB_PREFIX."liberty_content_prefs` SET `pref_name`='messages_alert' WHERE `pref_name`='message_alert'",
	)),
),


// here we deal with the liberty_attachments upgrade
/* so far untested and basically serve as notes for what has to be done
 *
 * NOTE: First we need to create new attachments of the duplicates we have that 
 * we have in the liberty_attachments_map to ensure that every attachment only 
 * has one content_id associated with it
 *
 *
 *
 * Now we can start cleaning up the db
array( 'DATADICT' => array(
	array( 'ALTER' => array(
		'liberty_attachments' => array(
			'content_id' => array( '`content_id`', 'I4' ),
			'is_primary' => array( '`is_primary`', 'CHAR(1)' ),
		),
	)),
)),

array( 'QUERY' =>
	array( 'SQL92' => array(
		// the easy stuff first... - if the primary_attachment_id is set in liberty_content we use that to populate the new content_id column
		"UPDATE `".BIT_DB_PREFIX."liberty_attachments` la SET `content_id` = ( SELECT `content_id` FROM `".BIT_DB_PREFIX."liberty_content` WHERE `primary_attachment_id` = la.`attachment_id` )",

		// set is_primary where it applies
		"UPDATE `".BIT_DB_PREFIX."liberty_attachments` la INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON( lc.`primary_attachment_id` = la.`attachment_id` ) SET `is_primary` = 'y'",

		// now we do the generic update
		"UPDATE `".BIT_DB_PREFIX."liberty_attachments` la SET `content_id` = ( SELECT `content_id` FROM `".BIT_DB_PREFIX."liberty_attachments_map` WHERE `attachment_id` = la.`attachment_id` LIMIT 1 ) WHERE la.`content_id` <> NULL",
	)),
),

array( 'DATADICT' => array(
	array( 'DROPTABLE' => array(
		'liberty_attachments_map'
	)),
	array( 'DROPCOLUMN' => array(
		'liberty_content' => array( 'primary_attachment_id' ),
	)),
)),
 */

		),
	),

'BONNIE' => array(
	'BWR1' => array(

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
// requires in PHP need liberty_content_types table 
// since we are going from 1.8 to R2, we can get away with this here.
		'liberty_content_types' => "
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
			'`page_ref_id`' => '`structure_id` I4 AUTO'
		),
		'tiki_link_cache' => array(
			'`cacheId`' => '`cache_id` I4 AUTO'
		),
		'tiki_comments' => array(
			'`threadId`' => '`comment_id` I4 AUTO',
			'`parentId`' => '`parent_id` I4',
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
	"ALTER TABLE `".BIT_DB_PREFIX."tiki_content` ADD CONSTRAINT `tiki_content_type_ref` FOREIGN KEY (`content_type_guid`) REFERENCES `".BIT_DB_PREFIX."liberty_content_types`( `content_type_guid` )" ),
)),
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
