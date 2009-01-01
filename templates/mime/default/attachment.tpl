{strip}
{if $wrapper.output == 'desc' || $wrapper.output == 'description'}
	{if $attachment.display_url}<a {$wrapper.href_class} href="{$wrapper.display_url|default:$attachment.display_url}">{/if}
		{$wrapper.description_parsed|default:$attachment.filename}
	{if $attachment.display_url}</a>{/if}
{else}
	<{$wrapper.wrapper|default:'div'} class="{$wrapper.class|default:'att-plugin'}"{if $wrapper.style} style="{$wrapper.style}{/if}">
		{if $wrapper.display_url}<a {$wrapper.href_class} href="{$wrapper.display_url}">{/if}
			{if $thumbsize == 'original'}
				<img class="thumb" src="{$attachment.source_url}" alt="{$wrapper.alt|default:$wrapper.description|default:$attachment.filename|replace:"\r":""|replace:"\n":" "|escape}" title="{$wrapper.description|default:$wrapper.alt|default:$attachment.filename|replace:"\r":""|replace:"\n":" "|escape}" />
			{elseif $attachment.thumbnail_url.$thumbsize}
				<img class="thumb" src="{$attachment.thumbnail_url.$thumbsize}" alt="{$wrapper.alt|default:$wrapper.description|default:$attachment.filename|replace:"\r":""|replace:"\n":" "|escape}" title="{$wrapper.description|default:$wrapper.alt|default:$attachment.filename|replace:"\r":""|replace:"\n":" "|escape}" />
			{/if}
			<br />{$wrapper.description_parsed}
		{if $wrapper.display_url}</a>{/if}
	</{$wrapper.wrapper|default:'div'}>
{/if}
{/strip}
