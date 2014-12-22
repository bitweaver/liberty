{strip}
<div class="form-group">
	{formlabel label="Title" for="title"}
	{forminput}
		<input type="text" size="35" id="audio_title" name="plugin[{$attachment.attachment_id}][mimeaudio][meta][title]" value="{$attachment.meta.title}" />
	{/forminput}
</div>

<div class="form-group">
	{formlabel label="Album" for="audio_album"}
	{forminput}
		<input type="text" size="35" id="audio_album" name="plugin[{$attachment.attachment_id}][mimeaudio][meta][album]" value="{$attachment.meta.album}" />
	{/forminput}
</div>

<div class="form-group">
	{formlabel label="Artist" for="audio_artist"}
	{forminput}
		<input type="text" size="35" id="audio_artist" name="plugin[{$attachment.attachment_id}][mimeaudio][meta][artist]" value="{$attachment.meta.artist}" />
	{/forminput}
</div>

<div class="form-group">
	{formlabel label="Year" for="audio_year"}
	{forminput}
		<input type="text" size="35" id="audio_year" name="plugin[{$attachment.attachment_id}][mimeaudio][meta][year]" value="{$attachment.meta.year}" />
	{/forminput}
</div>

<div class="form-group">
	{formlabel label="Genre" for="audio_genre"}
	{forminput}
		<input type="text" size="35" id="audio_genre" name="plugin[{$attachment.attachment_id}][mimeaudio][meta][genre]" value="{$attachment.meta.genre}" />
	{/forminput}
</div>

{if $attachment.source_file}
	<div class="form-group">
		{forminput label="checkbox"}
			<input type="checkbox" id="remove_original" name="plugin[{$attachment.attachment_id}][mimeaudio][remove_original]" value="y" />Remove Original
			{formhelp note="This will permanently remove the original file from the server. This will still allow you to listen to the audio file but not download it."}
		{/forminput}
	</div>
{/if}

{/strip}
