{strip}
<div class="row">
	{formlabel label="Title" for="title"}
	{forminput}
		<input type="text" size="35" id="audio_title" name="plugin[mimeaudio][title]" value="{$attachment.meta.title}" />
	{/forminput}
</div>

<div class="row">
	{formlabel label="Album" for="audio_album"}
	{forminput}
		<input type="text" size="35" id="audio_album" name="plugin[mimeaudio][album]" value="{$attachment.meta.album}" />
	{/forminput}
</div>

<div class="row">
	{formlabel label="Artist" for="audio_artist"}
	{forminput}
		<input type="text" size="35" id="audio_artist" name="plugin[mimeaudio][artist]" value="{$attachment.meta.artist}" />
	{/forminput}
</div>

<div class="row">
	{formlabel label="Year" for="audio_year"}
	{forminput}
		<input type="text" size="35" id="audio_year" name="plugin[mimeaudio][year]" value="{$attachment.meta.year}" />
	{/forminput}
</div>

<div class="row">
	{formlabel label="Genre" for="audio_genre"}
	{forminput}
		<input type="text" size="35" id="audio_genre" name="plugin[mimeaudio][genre]" value="{$attachment.meta.genre}" />
	{/forminput}
</div>
{/strip}
