{strip}
<div class="item">
	{if $attachment.media_url}
		{include file="bitpackage:liberty/mime/audio/player.tpl" caller=storage}
	{/if}

	{if $attachment.meta.title}{$attachment.meta.title}<br />{/if}
	<a href="{$attachment.display_url}">{tr}Full Details{/tr}</a>
</div>
{/strip}
