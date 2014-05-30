{if $gBitUser->hasPermission('p_liberty_post_comments') || $gBitUser->hasPermission('p_liberty_read_comments')}
{strip}
<div class="display comment">
	{if $comments}
	<div class="header">
		<h2>{tr}Comments{/tr}</h2>
	</div>
	{/if}

	<div class="body"{if !( $post_comment_request || $post_comment_preview )} id="editcomments"{/if}>
		<div id="edit_comments" {if $comments_ajax}style="display:none"{/if}>
			{include file="bitpackage:liberty/comments_post_inc.tpl" post_title="Post Comment"}
		</div>

		{include file="bitpackage:liberty/comments_display_option_bar.tpl"}

		{if $comments_ajax && $gBitUser->hasPermission( 'p_liberty_post_comments' )}
			<div class="form-group">
				<input type="submit" class="btn btn-default" name="post_comment_request" value="{tr}Add Comment{/tr}" onclick="LibertyComment.attachForm('comment_{$gContent->mContentId}', '{$gContent->mContentId}', {if $gContent->mContentId}{$gContent->mContentId}{elseif $commentsParentId}{$commentsParentId}{else}null{/if})"/>
			</div>
		{/if}

		<div id="comment_{$gContent->mContentId}"></div>
			{foreach name=comments_loop key=key item=item from=$comments}
				{displaycomment comment=$item}
			{/foreach}
		<div id="comment_{$gContent->mContentId}_footer"></div>

		{libertypagination ihash=$commentsPgnHash}
	</div><!-- end .body -->
</div><!-- end .comment -->
{/strip}
{/if}
