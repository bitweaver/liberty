{strip}
{if $area == "storage_thumbs"}
	<div class="item">
		{if $attachment.thumbnail_url.$thumbsize}
			{if !$attachment.is_primary or ( $attachment.is_primary and !$hideprimary )}
				<a href="{$attachment.display_url}">
					<img class="thumb" src="{$attachment.thumbnail_url.$thumbsize}" alt="{$attachment.filename}" title="{$attachment.filename}"/>
				</a>
			{/if}
		{else}
			{tr}No thumbnail for {$attachment.source_url}{/tr}
		{/if}
	</div>
{else}
	{if $wrapper.output == 'desc' || $wrapper.output == 'description'}
		{if $attachment.display_url}<a {$wrapper.href_class} href="{$wrapper.display_url|default:$attachment.display_url}">{/if}
			{$wrapper.description|escape|default:$attachment.filename}
		{if $attachment.display_url}</a>{/if}
	{else}
		<{$wrapper.wrapper|default:'div'} class="{$wrapper.class|default:'att-plugin'}"{if $wrapper.style} style="{$wrapper.style}{/if}">
			{if $wrapper.display_url}<a {$wrapper.href_class} href="{$wrapper.display_url}">{/if}
				{if $attachment.thumbnail_url.$thumbsize}
					<img class="thumb" src="{$attachment.thumbnail_url.$thumbsize}" alt="{$attachment.filename}" title="{$attachment.filename}"/>
				{/if}
				<br />{$wrapper.description|escape}
			{if $wrapper.display_url}</a>{/if}
		</{$wrapper.wrapper|default:'div'}>
	{/if}
{/if}
{/strip}
