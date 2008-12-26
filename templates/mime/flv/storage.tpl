{strip}
<div class="item">
	{if $attachment.media_url}
		{include file="bitpackage:liberty/mime/flv/player.tpl" caller=storage}
	{/if}

	<a href="{$attachment.display_url}">{tr}Full Details{/tr}</a>
</div>
{/strip}
