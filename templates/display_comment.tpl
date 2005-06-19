{strip}
<div style="margin-left: {math equation="level * marginIncrement" level=$comment.level marginIncrement=20}px">

	<div class="post">
		<h3>{$comment.title}</h3>
		<div class="date">{tr}by{/tr} {displayname hash=$comment} {tr}on{/tr} {$comment.last_modified|bit_long_datetime}</div>
		<div class="content">
			{$comment.parsed_data}
		</div>
		<div class="footer">
			<a href="{$comments_return_url}&amp;post_comment_reply_id={$comment.content_id}&amp;post_comment_request=1" rel="nofollow">{biticon ipackage="liberty" iname="reply" iexplain="Reply to this comment"}</a>
			{if $gBitUser->isAdmin() || ($gBitUser && $comment.user_id == $gBitUser->mInfo.user_id)}<a href="{$comments_return_url}&amp;post_comment_id={$comment.comment_id}&amp;post_comment_request=1" rel="nofollow">{biticon ipackage="liberty" iname="edit" iexplain="Edit"}</a>{/if}
			{if $gBitUser->isAdmin()}<a href="{$comments_return_url}&amp;delete_comment_id={$comment.comment_id}" rel="nofollow">{biticon ipackage="liberty" iname="delete" iexplain="Remove"}</a> {/if}
		</div>
	</div><!-- end .commentpost -->

</div><!-- end .left margin -->
{/strip}
