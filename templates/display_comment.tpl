{strip}
{if $comments_style eq 'threaded' && $comment.level}
	<div class="marginleft20px">
{else}
	<div class="marginleft0">
{/if}
	<div class="post" id="comment_{$comment.content_id}">
		<div class="floaticon">
			{if $gBitUser->hasPermission( 'p_liberty_post_comments' )}
				{if $comments_ajax }
					<a href="javascript:void(0);" onclick="LibertyComment.attachForm('comment_{$comment.content_id}', '{$comment.content_id}', '{$comment.root_id}')">{biticon ipackage="icons" iname="mail-reply-sender" iexplain="Reply to this comment"}</a>
				{else}
					<a href="{$comments_return_url}&amp;post_comment_reply_id={$comment.content_id}&amp;post_comment_request=1#editcomments" rel="nofollow">{biticon ipackage="icons" iname="mail-reply-sender" iexplain="Reply to this comment"}</a>
				{/if}
			{/if}
			{* if $gBitUser->hasPermission('p_liberty_edit_comments') || ($gBitUser && $comment.user_id == $gBitUser->mInfo.user_id && $comment.user_id!=$smarty.const.ANONYMOUS_USER_ID) *}
			{if $comment.is_editable || ($gContent && $gContent->hasUserPermission('p_liberty_edit_comments'))}
				<a href="{$comments_return_url}&amp;post_comment_id={$comment.comment_id}&amp;post_comment_request=1#editcomments" rel="nofollow">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="Edit"}</a>
			{/if}
			{if $gBitUser->hasPermission('p_liberty_admin_comments')}
				<a href="{$comments_return_url}&amp;delete_comment_id={$comment.comment_id}" rel="nofollow">{biticon ipackage="icons" iname="edit-delete" iexplain="Remove"}</a>
			{/if}
		</div>

		<div class="header">
			<h3>{$comment.title|escape}</h3>
			<div class="date">
				{tr}by{/tr} {if $comment.user_id < 0}{$comment.anon_name|escape}{else}{displayname hash=$comment}{/if}, {$comment.last_modified|bit_long_datetime}
			</div>
		</div>
		<div class="content">
			{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='body' serviceHash=$comment}
			{$comment.parsed_data}
		</div>
	</div><!-- end .post -->

	{if $comment.children}
		<div id="comment_{$comment.content_id}_children">
			{foreach key=key item=item from=$comment.children}
				{include file="bitpackage:liberty/display_comment.tpl" comment="$item"}
			{/foreach}
		</div>
	{/if}
	<div id="comment_{$comment.content_id}_footer"></div>
</div><!-- end .left margin -->
{/strip}
