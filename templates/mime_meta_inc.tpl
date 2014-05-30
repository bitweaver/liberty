{strip}
<div class="form-group">
	{formlabel label="Uploaded by" for=""}
	{forminput}
		{displayname user_id=$attachment.user_id}
	{/forminput}
</div>

{if $display && $attachment.display_url}
	<div class="form-group">
		{formlabel label="View" for=""}
		{forminput}
			<a href="{$attachment.display_url}">{$attachment.file_name}</a>
		{/forminput}
	</div>
{elseif $attachment.download_url}
	<div class="form-group">
		{formlabel label="Download" for=""}
		{forminput}
			<a href="{$attachment.download_url}">{$attachment.file_name}</a> <small>({$attachment.mime_type} &bull; {$attachment.file_size|display_bytes})</small>
		{/forminput}
	</div>
{/if}

<div class="form-group">
	{formlabel label="Downloads" for=""}
	{forminput}
		{$attachment.downloads|default:"{tr}none{/tr}"}
	{/forminput}
</div>

<div class="form-group">
	{formlabel label="Last Modified" for=""}
	{forminput}
		{$attachment.last_modified|bit_long_datetime}
	{/forminput}
</div>

{if $gContent->mInfo.hits}
	<div class="form-group">
		{formlabel label="Hits" for=""}
		{forminput}
			{$gContent->mInfo.hits}
		{/forminput}
	</div>
{/if}

{attachhelp legend=1 nohelp=$nohelp hash=$attachment}
{/strip}
