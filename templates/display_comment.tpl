{strip}
{if $comments_style eq 'threaded' && $comment.level}
	<div style="margin-left: {math equation="level * marginIncrement" level=$comment.level marginIncrement=20}px">
{else}
	<div style="margin-left: 0px">
{/if}
	<div class="post" id="comment_{$comment.content_id}">
		<div class="floaticon">
			{if $gBitUser->hasPermission( 'p_liberty_post_comments' )}
				<a href="{$comments_return_url}&amp;post_comment_reply_id={$comment.content_id}&amp;post_comment_request=1#editcomments" rel="nofollow">{biticon ipackage="icons" iname="mail-reply-sender" iexplain="Reply to this comment"}</a>
			{/if}
			{if $gBitUser->hasPermission('p_liberty_edit_comments') || ($gBitUser && $comment.user_id == $gBitUser->mInfo.user_id && $comment.user_id!=$smarty.const.ANONYMOUS_USER_ID)}
				<a href="{$comments_return_url}&amp;post_comment_id={$comment.comment_id}&amp;post_comment_request=1#editcomments" rel="nofollow">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="Edit"}</a>
			{/if}
			{if $gBitUser->hasPermission('p_liberty_admin_comments')}
				<a href="{$comments_return_url}&amp;delete_comment_id={$comment.comment_id}" rel="nofollow">{biticon ipackage="icons" iname="edit-delete" iexplain="Remove"}</a>
			{/if}
		</div>

		<h3>{$comment.title|escape}</h3>
		<div class="date">{tr}by{/tr} {if $comment.user_id < 0}{$comment.anon_name|escape}{else}{displayname hash=$comment}{/if}, {$comment.last_modified|bit_long_datetime}</div>
		<div class="content">
			{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='body' serviceHash=$comment}
			{$comment.parsed_data}
		</div>
	</div><!-- end .post -->
</div><!-- end .left margin -->
{/strip}
