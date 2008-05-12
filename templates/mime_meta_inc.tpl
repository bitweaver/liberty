{strip}
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
