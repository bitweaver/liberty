{if !$hide || $smarty.request.post_comment_request || $smarty.request.post_comment_preview}
	<a name="editcomments"></a>

	{if $post_comment_preview && !$preview_override}
		<h2>{tr}{$post_title} Preview{/tr}</h2>
			<div class="preview">
				{include file='bitpackage:liberty/display_comment.tpl' comment=$postComment}
			</div><!-- end .preview -->
	{/if}

	{form action="`$comments_return_url`#editcomments"}
		{formfeedback hash=$formfeedback}
		<input type="hidden" name="comments_maxComments" value="{$maxComments}" />
		<input type="hidden" name="comments_style" value="{$comments_style}" />
		<input type="hidden" name="comments_sort_mode" value="{$comments_sort_mode}" />

		{if $smarty.request.post_comment_request || $smarty.request.post_comment_preview}

			{legend legend=$post_title}
					<input type="hidden" name="post_comment_reply_id" value="{$post_comment_reply_id}" />
				    <input type="hidden" name="post_comment_id" value="{$post_comment_id}" />

					<div class="row">
						{formlabel label="Title" for="comments-title"}
						{forminput}
							<input type="text" size="50" name="comment_title" id="comments-title" value="{$postComment.title|escape:html}" />
							{formhelp note=""}
						{/forminput}
					</div>

					{if ! $gBitUser->isRegistered()}
						<div class="row">
							{formlabel label="Name" for="comments-name"}
							{forminput}
								<input type="text" size="50" name="comment_name" id="comments-name" value="{$postComment.anon_name|escape:html}" />
								{formhelp note=""}
							{/forminput}
						</div>
					{/if}

					{assign var=textarea_id value="commentpost"}

					{include file="bitpackage:liberty/edit_format.tpl" gContent=$gComment}

					{if $gBitSystem->isPackageActive( 'smileys' )}
						{include file="bitpackage:smileys/smileys_full.tpl"}
					{/if}

					{if $gBitSystem->isPackageActive( 'quicktags' )}
						{include file="bitpackage:quicktags/quicktags_full.tpl" formId="commentpost"}
					{/if}

					<div class="row">
						{formlabel label="Comment" for="commentpost"}
						{forminput}
							<textarea {spellchecker} id="commentpost" name="comment_data" rows="6" cols="50">{$postComment.data}</textarea>
							{formhelp note="Use [http://www.foo.com] or [http://www.foo.com|description] for links.<br />HTML tags are not allowed inside comments."}
						{/forminput}
					</div>

					<div class="row submit">
						<input type="submit" name="post_comment_preview" value="{tr}Preview{/tr}"/>&nbsp;
						<input type="submit" name="post_comment_submit" value="{tr}Post{/tr}"/>&nbsp;
						<input type="submit" name="post_comment_cancel" value="{tr}Cancel{/tr}"/>
					</div>
				{/legend}
		{elseif $gBitUser->hasPermission( 'p_liberty_post_comments' )}
				<div class="row">
					<input type="submit" name="post_comment_request" value="{tr}Add Comment{/tr}" />
				</div>
		{/if}
	{/form}
{/if}
