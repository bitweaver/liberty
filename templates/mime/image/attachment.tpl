{strip}
{if $attachment.thumbnail_url.panorama}
	{if $wrapper.output == 'desc' || $wrapper.output == 'description'}
		{if $attachment.display_url}<a {$wrapper.href_class} href="{$wrapper.display_url|default:$attachment.display_url}">{/if}
			{$wrapper.description_parsed|default:$attachment.filename}
		{if $attachment.display_url}</a>{/if}
	{else}
		<{$wrapper.wrapper|default:'div'} class="{$wrapper.class|default:'att-plugin'}"{if $wrapper.style} style="{$wrapper.style}{/if}">
			{include file="bitpackage:liberty/mime/image/player.tpl"}
			{if $wrapper.display_url}<a {$wrapper.href_class} href="{$wrapper.display_url}">{/if}
				{$wrapper.description_parsed}<br />
			{if $wrapper.display_url}</a>{/if}
		</{$wrapper.wrapper|default:'div'}>
	{/if}
{else}
	{include file=$gLibertySystem->getMimeTemplate('attachment', $smarty.const.LIBERTY_DEFAULT_MIME_HANDLER)}
{/if}
{/strip}
