{strip}
<br />

<div class="display comment">
	<div class="header">
		{if !( $post_comment_request || $post_comment_preview )}
			<a name="bitcomments"></a>
		{/if}
		<h2>{tr}Comments{/tr}</h2>
	</div>

	<div class="body">
		{formfeedback hash=$formfeedback}

		{section name=ix loop=$comments}
			{displaycomment comment="$comments[ix]"}
		{/section}

		{libertypagination hash=$commentsPgnHash}

		{if $post_comment_preview}
			<h2>{tr}Comments Preview{/tr}</h2>
			<div class="preview">
				{include file='bitpackage:liberty/display_comment.tpl' comment=$postComment}
			</div><!-- end .preview -->
		{/if}

		{form action="`$comments_return_url`#bitcomments"}
			{if $post_comment_request || $post_comment_preview}
				<a name="bitcomments"></a>
				{legend legend="Post Comment"}
					<input type="hidden" name="post_comment_reply_id" value="{$post_comment_reply_id}" />
				    <input type="hidden" name="post_comment_id" value="{$post_comment_id}" />

					<div class="row">
						{formlabel label="Title" for="comments-title"}
						{forminput}
							<input type="text" size="50" name="comment_title" id="comments-title" value="{$postComment.title}" />
							{formhelp note=""}
						{/forminput}
					</div>

					{assign var=textarea_id value="commentpost"}
					{if $gBitSystemPrefs.package_smileys eq 'y'}
						{include file="bitpackage:smileys/smileys_full.tpl"}
					{/if}

					{if $gBitSystemPrefs.package_quicktags eq 'y'}
						{include file="bitpackage:quicktags/quicktags_full.tpl" formId="commentpost"}
					{/if}

					<div class="row">
						{formlabel label="Comment" for="commentpost"}
						{forminput}
							<textarea id="commentpost" name="comment_data" rows="10" cols="80">{$postComment.data}</textarea>
							{formhelp note="Use [http://www.foo.com] or [http://www.foo.com|description] for links.<br />HTML tags are not allowed inside comments."}
						{/forminput}
					</div>

					<div class="row submit">
						<input type="submit" name="post_comment_preview" value="{tr}preview{/tr}"/>&nbsp;
						<input type="submit" name="post_comment_submit" value="{tr}post{/tr}"/>
					</div>
				{/legend}
			{elseif $gBitUser->hasPermission( 'bit_p_post_comments' )}
				<div class="row">
					<input type="submit" name="post_comment_request" value="{tr}Add Comment{/tr}" />
				</div>
			{/if}
		{/form}
	</div><!-- end .body -->
</div><!-- end .comment -->
{/strip}
