{if !$hide || $post_comment_request || $smarty.request.post_comment_preview}
	<a name="editcomments"></a>

	{if $post_comment_preview && !$preview_override}
		<h2>{tr}{$post_title} Preview{/tr}</h2>
		<div class="preview">
			{include file='bitpackage:liberty/display_comment.tpl' comment=$postComment}
		</div><!-- end .preview -->
	{/if}

	{form enctype="multipart/form-data" action="`$comments_return_url`#editcomments" id="editcomment-form"}
		{formfeedback hash=$formfeedback}


		{if $post_comment_request || $smarty.request.post_comment_preview || $comments_ajax}
			{legend legend=$post_title}
				<input type="hidden" name="post_comment_reply_id" value="{$post_comment_reply_id}" />
				<input type="hidden" name="post_comment_id" value="{$post_comment_id}" />
				<input type="hidden" name="comments_return_url" value="{$comments_return_url}" />

				{* This is a little extra value for the funky case when bw learns browser has js at the same time a preview is asked for
				   This will keep comment in non-js mode until previewing is done. Things get messy without this *}
				{if !$gBitThemes->isJavascriptEnabled() || $no_js_preview == "y"}
					<input type="hidden" name="no_js_preview" value="y" />
				{/if}

				<div class="control-group">
					{formlabel label="Title" for="comments-title"}
					{forminput}
						<input type="text" size="50" name="comment_title" id="comments-title" value="{$postComment.title|escape:html}" />
						{formhelp note=""}
					{/forminput}
				</div>

				{if !$gBitUser->isRegistered()}
					<div class="control-group" id="post-login">
						{formlabel label="Login" for="login-email"}
						{forminput}
							<div style="display:inline-block;padding-right:20px;">
								<input type="text" size="20" name="login_email" id="login-email" value="{$smarty.request.login_email|escape:html}" />
								<div class="label">{tr}Username or Email{/tr}</div>
							</div>
							<div style="display:inline-block">
								<input type="password" size="8" name="login_password" id="login-password" value="{$smarty.request.login_password|escape:html}" />
								<div class="label">{tr}Password{/tr}</div>
							</div>
							<div class="formhelp">{tr}If you are already registered with <strong>{$gBitSystem->mConfig.site_title|default:"this site"}</strong> please enter your login details above.{/tr}</div>
						{/forminput}
					</div>
					<div class="control-group" style="display:none" id="post-anon">
						{formlabel label="Your Name" for="comments-name"}
						{forminput}
							<input type="text" size="50" name="comment_name" id="comments-name" value="{$postComment.anon_name|escape:html}" />
							{formhelp note=""}
						{/forminput}
					</div>
					{captcha variant="row" id="post-captcha" style="display:none"}
					<div class="control-group">
						{forminput}
							<input type="checkbox" name="anon_post" id="anon-post" value="y" onchange="BitBase.toggleElementDisplay('post-login','block');BitBase.toggleElementDisplay('post-anon','block');BitBase.toggleElementDisplay('post-captcha','block');" /> {tr}Anonymous Post{/tr}
							{formhelp note=""}
						{/forminput}
					</div>
				{/if}

				{textarea id="commentpost" name="comment_data" rows=$gBitSystem->getConfig('comments_default_post_lines', 6)}{$postComment.data}{/textarea}

				{* @TODO perm check more accurately should be on root content object *}
				{if $gBitSystem->isFeatureActive( 'comments_allow_attachments' ) && $gBitUser->hasPermission('p_liberty_attach_attachments') }
					{* @TODO make edit_storage_list.tpl work with comments attachments - it is nested in edit_storage.tpl - for now bypass it, remove mime code when edit_storage.tpl can be used directly*}
					{* include file="bitpackage:liberty/edit_storage.tpl" *}
					{if $gLibertySystem->isPluginActive( $smarty.const.LIBERTY_DEFAULT_MIME_HANDLER )}
						{foreach from=$gLibertySystem->getAllMimeTemplates('upload') item=tpl}
							{include file=$tpl}
						{/foreach}
					{/if}
				{/if}

				<div class="control-group submit">
					<input type="submit" name="post_comment_preview" value="{tr}Preview{/tr}" {if $comments_ajax}onclick="LibertyComment.previewComment(); return false;"{/if}/>&nbsp;
					<input type="submit" name="post_comment_submit" value="{tr}Post{/tr}" {if $comments_ajax}onclick="LibertyComment.postComment(); return false;"{/if}/>&nbsp;
					<input type="submit" name="post_comment_cancel" value="{tr}Cancel{/tr}" {if $comments_ajax}onclick="LibertyComment.cancelComment(true); return false;"{/if}/>
				</div>
			{/legend}
		{elseif $gBitUser->hasPermission( 'p_liberty_post_comments' )}
			<div class="control-group">
				<input type="submit" name="post_comment_request" value="{tr}Add Comment{/tr}" />
			</div>
		{/if}
	{/form}
{/if}
