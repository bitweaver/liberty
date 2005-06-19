{php}
include (LIBERTY_PKG_PATH."edit_storage_inc.php");
{/php}
{strip}
{if $gContent->mStorage}
	<div class="row">
		<table class="data">
			<caption>{tr}Insert attached items{/tr}</caption>
			<tr>
				<th style="width:30%">Filename</th>
				<th style="width:70%">Attachment Tag</th>
			</tr>
			{foreach from=$gContent->mStorage item=storage key=attachmentId}
				<tr class="{cycle values="odd,even"}">
					<td>{$storage.filename}</td>
					<td style="text-align:right;">
						<a title="{$storage.wiki_plugin_link}" href="javascript:insertAt('{$textarea_id}','{$storage.wiki_plugin_link|escape:"javascript"}');">{$storage.wiki_plugin_link}</a>
					</td>
				</tr>
			{/foreach}
			{if $textarea_id}
				<tr><td colspan="2">{tr}Click on the Attachment Tag to insert it in the textarea{/tr}</td></tr>
			{/if}
		</table>
	</div>
{/if}
{/strip}
