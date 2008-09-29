{strip}
{if $attachment.media_url}
	<div class="row aligncenter">
		{include file="bitpackage:liberty/mime_audio_player_inc.tpl"}
	</div>
{/if}

{if $attachment.meta.title}
	<div class="row">
		{formlabel label="Title" for=""}
		{forminput}
			{$attachment.meta.title}
		{/forminput}
	</div>
{/if}

{if $attachment.meta.album}
	<div class="row">
		{formlabel label="Album" for=""}
		{forminput}
			{$attachment.meta.album}
		{/forminput}
	</div>
{/if}

{if $attachment.meta.artist}
	<div class="row">
		{formlabel label="Artist" for=""}
		{forminput}
			{$attachment.meta.artist}
		{/forminput}
	</div>
{/if}

{if $attachment.meta.year}
	<div class="row">
		{formlabel label="Year" for=""}
		{forminput}
			{$attachment.meta.year}
		{/forminput}
	</div>
{/if}

{if $attachment.meta.playtimestring}
	<div class="row">
		{formlabel label="Duration" for=""}
		{forminput}
			{$attachment.meta.playtimestring}
		{/forminput}
	</div>
{/if}

{if $attachment.meta.genre}
	<div class="row">
		{formlabel label="Genre" for=""}
		{forminput}
			{$attachment.meta.genre}
		{/forminput}
	</div>
{/if}

{if $attachment.download_url}
	<div class="row">
		{formlabel label="Filename" for=""}
		{forminput}
			<a href="{$attachment.download_url}">{$attachment.filename|escape}</a> <small>({$attachment.mime_type})</small>
		{/forminput}
	</div>

	<div class="row">
		{formlabel label="Filesize" for=""}
		{forminput}
			{$attachment.file_size|display_bytes}
		{/forminput}
	</div>
{/if}

{attachhelp legend=1 hash=$attachment}
{/strip}
