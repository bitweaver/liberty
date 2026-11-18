{strip}
{if $attachment.media_url}
	{assign var=id value="audio_`$caller``$attachment.attachment_id`"}
	{if $uploadTab}{assign var=id value="`$id`_tab"}{/if}
	{if $thumbsize eq "small"}
		{assign var=width value=160}
	{else}
		{assign var=width value=400}
	{/if}
	<div class="mime-audio" style="width:{$width}px;max-width:100%">
		{if !$attachment.thumbnail_is_mime && $attachment.thumbnail_url.medium}
			<img src="{$attachment.thumbnail_url.medium|escape}" alt="" style="width:100%;height:auto;display:block" />
		{/if}
		<audio id="{$id}" controls preload="none" style="width:100%">
			<source src="{$attachment.media_url|escape}" type="{if $attachment.media_url|substr:-4|lower eq '.m4a'}audio/mp4{else}audio/mpeg{/if}" />
			<a href="{$attachment.media_url|escape}">{tr}Download audio{/tr}</a>
		</audio>
	</div>
{/if}
{/strip}
