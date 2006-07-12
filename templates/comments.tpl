{strip}
<br />
<div class="display comment">
	<div class="header">
		{if !( $smarty.request.post_comment_request || $post_comment_preview )}
			<a name="editcomments"></a>
		{/if}
		<h2>{tr}Comments{/tr}</h2>
	</div>

	<div class="body">
		{formfeedback hash=$formfeedback}

		{include file="bitpackage:liberty/comments_post_inc.tpl" post_title="Post Comment"}

		{include file="bitpackage:liberty/comments_display_option_bar.tpl"}

		{section name=ix loop=$comments}
			{displaycomment comment="$comments[ix]"}
		{/section}

		{libertypagination hash=$commentsPgnHash}
	</div><!-- end .body -->
</div><!-- end .comment -->
{/strip}
