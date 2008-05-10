{strip}
<div class="row" style="text-align:center;">
	{assign var=size value=$smarty.request.size|default:medium}
	<img title="" alt="" src="{$attachment.thumbnail_url.$size}" />
</div>

<div class="pagination">
	{tr}View other sizes{/tr}<br />
	{foreach name=size key=size from=$attachment.thumbnail_url item=url}
		<a href="{$attachment.display_url|escape}&amp;size={$size}">{tr}{$size}{/tr}</a>
		{if !$smarty.foreach.size.last}&nbsp;&bull;&nbsp;{/if}
	{/foreach}
</div>

<div class="row">
	{formlabel label="Uploaded by" for=""}
	{forminput}
		{displayname user_id=$attachment.user_id}
	{/forminput}
</div>

{if $attachment.download_url}
	<div class="row">
		{formlabel label="Download" for=""}
		{forminput}
			<a href="{$attachment.download_url}">{$attachment.filename}</a> <small>({$attachment.file_size|display_bytes})</small>
		{/forminput}
	</div>
{/if}

<div class="row">
	{formlabel label="Last Modified" for=""}
	{forminput}
		{$attachment.last_modified|bit_long_datetime}
	{/forminput}
</div>

{attachhelp legend=1 hash=$attachment}
{/strip}
