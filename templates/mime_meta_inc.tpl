{strip}
<div class="row">
	{formlabel label="Uploaded by" for=""}
	{forminput}
		{displayname user_id=$attachment.user_id}
	{/forminput}
</div>

{if $display && $attachment.display_url}
	<div class="row">
		{formlabel label="View" for=""}
		{forminput}
			<a href="{$attachment.display_url}">{$attachment.filename}</a>
		{/forminput}
	</div>
{elseif $attachment.download_url}
	<div class="row">
		{formlabel label="Download" for=""}
		{forminput}
			<a href="{$attachment.download_url}">{$attachment.filename}</a> <small>({$attachment.mime_type} &bull; {$attachment.file_size|display_bytes})</small>
		{/forminput}
	</div>
{/if}

<div class="row">
	{formlabel label="Last Modified" for=""}
	{forminput}
		{$attachment.last_modified|bit_long_datetime}
	{/forminput}
</div>

<div class="row">
	{formlabel label="Downloads" for=""}
	{forminput}
		{$attachment.downloads|default:"{tr}none{/tr}"}
	{/forminput}
</div>

{attachhelp legend=1 nohelp=$nohelp hash=$attachment}
{/strip}
