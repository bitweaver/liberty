<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * definitions
 */
define( 'PLUGIN_GUID_DATAPOLL', 'datapoll' );
global $gLibertySystem;
$pluginParams = array ( 'tag' => 'POLL',
						'auto_activate' => TRUE,
						'requires_pair' => FALSE,
						'load_function' => 'data_poll',
						'title' => 'Poll',
						'help_page' => 'DataPluginPoll',
						'description' => tra("This plugin will display the selected Poll and allow users with permission to vote."),
						'help_function' => 'data_poll_help',
						'syntax' => "{POLL id= }",
						'plugin_type' => DATA_PLUGIN
					  );
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAPOLL, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAPOLL);
/**
 * smarty_function_base
 */
require_once( KERNEL_PKG_PATH.'BitBase.php' );

/**
 * smarty_function_poll
 */
function data_poll($data, $params) {
    global $gBitSystem, $gBitSmarty;
    require_once( POLLS_PKG_PATH.'poll_lib.php' );
    if(!isset($polllib)) {
      $polllib = new PollLib();
    }

    extract($params, EXTR_SKIP);

    if (empty($id)) {
      $id = $polllib->get_random_active_poll();
    }
    if($id) {
      $poll_info = $polllib->get_poll($id);
      $polls = $polllib->list_poll_options($id,0,-1,'option_id_asc','');
			$comments_count = 0;
			if ($gBitSystem->getPreference('feature_poll_comments') == 'y') {
                                include_once( LIBERTY_PKG_PATH.'LibertyComment.php' );
				$comments = new LibertyComment();
				$comments_count = $comments->count_comments("poll:".$poll_info["poll_id"]);
			}
			$gBitSmarty->assign('comments', $comments_count);
      $gBitSmarty->assign('poll_info',$poll_info);
      $gBitSmarty->assign('polls',$polls["data"]);
      return $gBitSmarty->fetch('bitpackage:polls/poll.tpl');
    }
}

/* vim: set expandtab: */

?>
