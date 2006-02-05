{strip}
{if $comments_style eq 'threaded'}
	<div style="margin-left: {math equation="level * marginIncrement" level=$comment.level marginIncrement=20}px">
{else}
	<div style="margin-left: 0px">
{/if}
	<div class="post">
		<div class="floaticon">
			{if $gBitUser->hasPermission( 'bit_p_post_comments' )}
				<a href="{$comments_return_url}&amp;post_comment_reply_id={$comment.content_id}&amp;post_comment_request=1#editcomments" rel="nofollow">{biticon ipackage="liberty" iname="reply" iexplain="Reply to this comment"}</a>
			{/if}
			{if $gBitUser->isAdmin() || ( $gBitUser && $gBitUser->hasPermission( 'bit_p_edit_own_comments' ) && $comment.user_id == $gBitUser->mInfo.user_id)}
				<a href="{$comments_return_url}&amp;post_comment_id={$comment.comment_id}&amp;post_comment_request=1#editcomments" rel="nofollow">{biticon ipackage="liberty" iname="edit" iexplain="Edit"}</a>
			{/if}
			{if $gBitUser->isAdmin() || ( $gBitUser && $gBitUser->hasPermission( 'bit_p_remove_own_comments' ) && $comment.user_id == $gBitUser->mInfo.user_id)}
				<a href="{$comments_return_url}&amp;delete_comment_id={$comment.comment_id}" rel="nofollow">{biticon ipackage="liberty" iname="delete" iexplain="Remove"}</a>
			{/if}
		</div>

		<h3>{$comment.title}</h3>
		<div class="date">{tr}by{/tr} {displayname hash=$comment}, {$comment.last_modified|bit_long_datetime}</div>
		<div class="content">
			{$comment.parsed_data}
		</div>
	</div><!-- end .post -->
</div><!-- end .left margin -->
{/strip}
