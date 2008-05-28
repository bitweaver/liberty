{strip}
{if $attachment.audio_url}
	<div class="row" style="text-align:center;">
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
			<a href="{$attachment.download_url}">{$attachment.filename|escape}</a>
			&nbsp; <small>({$attachment.mime_type})</small>
			{* TODO: get this to work if $gContent->hasEditPermission() && $attachment.flv_url}
				{form ipackage=treasury ifile="plugins/form.flv.php"}
					<input type="hidden" name="content_id" value="{$gContent->mContentId}" />
					<input type="submit" name="remove_original" value="{tr}Remove Original{/tr}" />
					{formhelp note="This will remove the original file from the server. The falsh video will remain and you can still view the video but you cannot download the original anymore."}
				{/form}
			{/if*}
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
