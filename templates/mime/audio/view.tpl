{strip}
{if $attachment.media_url}
	<div class="form-group aligncenter">
		{include file="bitpackage:liberty/mime/audio/player.tpl"}
	</div>
{/if}

{if $attachment.meta.title}
	<div class="form-group">
		{formlabel label="Title" for=""}
		{forminput}
			{$attachment.meta.title}
		{/forminput}
	</div>
{/if}

{if $attachment.meta.album}
	<div class="form-group">
		{formlabel label="Album" for=""}
		{forminput}
			{$attachment.meta.album}
		{/forminput}
	</div>
{/if}

{if $attachment.meta.artist}
	<div class="form-group">
		{formlabel label="Artist" for=""}
		{forminput}
			{$attachment.meta.artist}
		{/forminput}
	</div>
{/if}

{if $attachment.meta.year}
	<div class="form-group">
		{formlabel label="Year" for=""}
		{forminput}
			{$attachment.meta.year}
		{/forminput}
	</div>
{/if}

{if $attachment.meta.playtimestring}
	<div class="form-group">
		{formlabel label="Duration" for=""}
		{forminput}
			{$attachment.meta.playtimestring}
		{/forminput}
	</div>
{/if}

{if $attachment.meta.genre}
	<div class="form-group">
		{formlabel label="Genre" for=""}
		{forminput}
			{$attachment.meta.genre}
		{/forminput}
	</div>
{/if}

{include file=bitpackage:liberty/mime_meta_inc.tpl}
{/strip}
