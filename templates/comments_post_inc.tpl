{if !$hide || $post_comment_request || $smarty.request.post_comment_preview}
	<a name="editcomments"></a>

	{if $post_comment_preview && !$preview_override}
		<h2>{tr}{$post_title} Preview{/tr}</h2>
		<div class="preview">
			{include file='bitpackage:liberty/display_comment.tpl' comment=$postComment}
		</div><!-- end .preview -->
	{/if}

		{form action="`$comments_return_url`#editcomments" id="editcomment-form"}
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

				<div class="row">
					{formlabel label="Title" for="comments-title"}
					{forminput}
						<input type="text" size="50" name="comment_title" id="comments-title" value="{$postComment.title|escape:html}" />
						{formhelp note=""}
					{/forminput}
				</div>
				
				{if !$gBitUser->isRegistered()}
					<div class="row" id="post-login">
						{assign var=loginLabel value=$gBitSystem->mConfig.site_title|default:Site}
						{formlabel label="`$loginLabel` Login" for="login-email"}
						{forminput}
							<div style="display:inline-block">
								<input type="text" size="20" name="login_email" id="login-email" value="{$smarty.request.login_email|escape:html}" />
								<div class="date">{tr}Email{/tr}</div>
							</div>
							<div style="display:inline-block">
								<input type="password" size="8" name="login_password" id="login-password" value="{$smarty.request.login_password|escape:html}" />
								<div class="date">{tr}Password{/tr}</div>
							</div>
					
						{/forminput}
					</div>
					<div class="row" style="display:none" id="post-anon">
						{formlabel label="Your Name" for="comments-name"}
						{forminput}
							<input type="text" size="50" name="comment_name" id="comments-name" value="{$postComment.anon_name|escape:html}" />
							{formhelp note=""}
						{/forminput}
					</div>
					{captcha variant="row" id="post-captcha" style="display:none"}
					<div class="row">
						{forminput}
							<input type="checkbox" name="anon_post" id="anon-post" value="y" onchange="BitBase.toggleElementDisplay('post-login','block');BitBase.toggleElementDisplay('post-anon','block');BitBase.toggleElementDisplay('post-captcha','block');" /> {tr}Anonymous Post{/tr}
							{formhelp note=""}
						{/forminput}
					</div>
				{/if}

				{textarea id="commentpost" name="comment_data" rows=$gBitSystem->getConfig('comments_default_post_lines', 6)}{$postComment.data}{/textarea}

				<div class="row submit"> 
					<input type="submit" name="post_comment_preview" value="{tr}Preview{/tr}" {if $comments_ajax}onclick="LibertyComment.previewComment(); return false;"{/if}/>&nbsp;
					<input type="submit" name="post_comment_submit" value="{tr}Post{/tr}" {if $comments_ajax}onclick="LibertyComment.postComment(); return false;"{/if}/>&nbsp;
					<input type="submit" name="post_comment_cancel" value="{tr}Cancel{/tr}" {if $comments_ajax}onclick="LibertyComment.cancelComment(true); return false;"{/if}/>
				</div>
			{/legend}
		{elseif $gBitUser->hasPermission( 'p_liberty_post_comments' )}
			<div class="row">
				<input type="submit" name="post_comment_request" value="{tr}Add Comment{/tr}" />
			</div>
		{/if}
	{/form}
{/if}
