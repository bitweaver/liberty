		{if $comments and $gBitSystem->isFeatureActive('comments_display_option_bar')}
			{form action="`$comments_return_url`#editcomments"}
				<input type="hidden" name="post_comment_reply_id" value="{$post_comment_reply_id}" />
				<input type="hidden" name="post_comment_id" value="{$post_comment_id}" />
				<table class="optionbar">
					<caption>{tr}Comments Filter{/tr}</caption>
					<tr>
						<td>
							<label for="comments-maxcomm">{tr}Messages{/tr} </label>
							<select name="comments_maxComments" id="comments-maxcomm">
								{* 1 comment selection is used for directly displaying a single comment via a URL *}
								<option value="1" {if $maxComments eq 1}selected="selected"{/if}>1</option>
								<option value="5" {if $maxComments eq 5}selected="selected"{/if}>5</option>
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
