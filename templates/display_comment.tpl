{strip}
{if $comments_style eq 'threaded' && $comment.level}
	<div class="threaded">
{else}
	<div class="">
{/if}
	<div class="post" id="comment_{$comment.content_id}">
		<div class="floaticon">
			{if $gBitUser->hasPermission( 'p_liberty_post_comments' )}
				{if $comments_ajax }
					<a href="javascript:void(0);" onclick="LibertyComment.attachForm('comment_{$comment.content_id}', '{$comment.content_id}', '{$comment.root_id}')">{booticon iname="icon-envelope-alt"  ipackage="icons"  iexplain="Reply to this comment"}</a>
				{else}
					{booticon iname="icon-envelope-alt"  class="icon" ipackage="icons"  iexplain="Reply to this comment" onclick="window.location='`$comments_return_url`&post_comment_reply_id=`$comment.content_id`&post_comment_request=1#editcomments';" }
				{/if}
			{/if}
			{if $comment.is_editable}
				<a href="{$comments_return_url}&amp;post_comment_id={$comment.comment_id}&amp;post_comment_request=1#editcomments" rel="nofollow">{booticon iname="icon-edit" ipackage="icons" iexplain="Edit"}</a>
			{/if}
			{if $gBitUser->hasPermission('p_liberty_admin_comments')}
				<a href="{$comments_return_url}&amp;delete_comment_id={$comment.comment_id}" rel="nofollow">{booticon iname="icon-trash" ipackage="icons" iexplain="Remove"}</a>
			{/if}
		</div>

		<div class="header">
			<h3>{$comment.title|escape}</h3>
			<div class="date">
				{tr}by{/tr} {if $comment.user_id < 0}{$comment.anon_name|escape}{else}{displayname hash=$comment}{/if}, {$comment.last_modified|reltime}
			</div>
		</div>
		<div class="content">
			{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='comment' serviceHash=$comment}
			{$comment.parsed_data}
			{if $gBitSystem->isFeatureActive( 'comments_allow_attachments' )}
				{include file="bitpackage:liberty/list_comment_files_inc.tpl" storageHash=$comment.storage}
			{/if}
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
