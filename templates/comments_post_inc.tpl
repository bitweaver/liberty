				<a name="editcomments"></a>
				{legend legend="Post Comment"}
					<input type="hidden" name="post_comment_reply_id" value="{$post_comment_reply_id}" />
				    <input type="hidden" name="post_comment_id" value="{$post_comment_id}" />

					<div class="row">
						{formlabel label="Title" for="comments-title"}
						{forminput}
							<input type="text" size="50" name="comment_title" id="comments-title" value="{$postComment.title|escape:html}" />
							{formhelp note=""}
						{/forminput}
					</div>

					{assign var=textarea_id value="commentpost"}
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
						<input type="submit" name="post_comment_submit" value="{tr}Post{/tr}"/>
					</div>
				{/legend}

