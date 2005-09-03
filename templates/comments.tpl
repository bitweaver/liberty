{strip}
<br />
{if $gBitSystem->isFeatureActive( 'comments_display_expanded' )}
	{assign var=initial value=none}{assign var=subsequent value=block}{assign var=iname value=expanded}
{else}
	{assign var=initial value=block}{assign var=subsequent value=none}{assign var=iname value=collapsed}
{/if}


<div class="display comment">
	<div class="header">
		{if !( $post_comment_request || $post_comment_preview )}
			<a name="editcomments"></a>
		{/if}
		<h2>
			<a onclick="icntoggle('bitcomments');">
				{biticon ipackage=liberty iname=$iname id="bitcommentsimg" iexplain=""} {tr}Comments{/tr}
			</a>
		</h2>
	</div>

{/strip}
	<script type="text/javascript">//<![CDATA[
		setfoldericonstate('bitcomments');
		document.write('<div id="bitcomments" style="display:{if $smarty.cookies.bitcomments eq 'o'}{$initial}{else}{$subsequent}{/if};">');
	//]]></script>
{strip}

	<div class="body">
		{formfeedback hash=$formfeedback}

		{if $post_comment_preview}
			<h2>{tr}Comments Preview{/tr}</h2>
			<div class="preview">
				{include file='bitpackage:liberty/display_comment.tpl' comment=$postComment}
			</div><!-- end .preview -->
		{/if}

		{form action="`$comments_return_url`#editcomments"}
			<input type="hidden" name="comments_maxComments" value="{$maxComments}" />
			<input type="hidden" name="comments_style" value="{$comments_style}" />
			<input type="hidden" name="comments_sort_mode" value="{$comments_sort_mode}" />
			{if $post_comment_request || $post_comment_preview}
				<a name="editcomments"></a>
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
					{if $gBitSystem->isPackageActive( 'smileys' )}
						{include file="bitpackage:smileys/smileys_full.tpl"}
					{/if}

					{if $gBitSystem->isPackageActive( 'quicktags' )}
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
						<input type="submit" name="post_comment_preview" value="{tr}Preview{/tr}"/>&nbsp;
						<input type="submit" name="post_comment_submit" value="{tr}Post{/tr}"/>
					</div>
				{/legend}
			{elseif $gBitUser->hasPermission( 'bit_p_post_comments' )}
				<div class="row">
					<input type="submit" name="post_comment_request" value="{tr}Add Comment{/tr}" />
				</div>
			{/if}
		{/form}

		{if $comments and $gBitSystem->isFeatureActive( 'comments_display_option_bar' )}
			{form action="`$comments_return_url`#editcomments"}
				<input type="hidden" name="post_comment_reply_id" value="{$post_comment_reply_id}" />
				<input type="hidden" name="post_comment_id" value="{$post_comment_id}" />
				<table class="optionbar">
					<caption>{tr}Comments Filter{/tr}</caption>
					<tr>
						<td>
							<label for="comments-maxcomm">{tr}Messages{/tr} </label>
							<select name="comments_maxComments" id="comments-maxcomm">
								<option value="10" {if $maxComments eq 10}selected="selected"{/if}>10</option>
								<option value="20" {if $maxComments eq 20}selected="selected"{/if}>20</option>
								<option value="50" {if $maxComments eq 50}selected="selected"{/if}>50</option>
								<option value="100" {if $maxComments eq 100}selected="selected"{/if}>100</option>
								<option value="999999" {if $maxComments eq 999999}selected="selected"{/if}>All</option>
							</select>
						</td>
						<td>
							<label for="comments-style">{tr}Style{/tr} </label>
							<select name="comments_style" id="comments-style">
								<option value="flat" {if $comments_style eq "flat"}selected="selected"{/if}>Flat</option>
								<option value="threaded" {if $comments_style eq "threaded"}selected="selected"{/if}>Threaded</option>
							</select>
						</td>
						<td>
							<label for="comments-sort">{tr}Sort{/tr} </label>
							<select name="comments_sort_mode" id="comments-sort">
								<option value="commentDate_desc" {if $comments_sort_mode eq "commentDate_desc"}selected="selected"{/if}>Newest first</option>
								<option value="commentDate_asc" {if $comments_sort_mode eq "commentDate_asc"}selected="selected"{/if}>Oldest first</option>
							</select> 
						</td>
						<td style="text-align:right"><input type="submit" name="comments_setOptions" value="set" /></td>
					</tr>
				</table>
			{/form}
		{/if}

		{section name=ix loop=$comments}
			{displaycomment comment="$comments[ix]"}
		{/section}

		{libertypagination hash=$commentsPgnHash}
	</div><!-- end .body -->
{/strip}
	<script type="text/javascript">//<![CDATA[
		document.write('<\/div>');
	//]]></script>
</div><!-- end .comment -->
