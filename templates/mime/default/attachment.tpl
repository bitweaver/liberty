{strip}
{if $wrapper.output == 'desc' || $wrapper.output == 'description'}
	{if $attachment.display_url}<a {$wrapper.href_class} href="{$wrapper.display_url|default:$attachment.display_url}">{/if}
		{$wrapper.description_parsed|default:$attachment.filename|strip_tags}
	{if $attachment.display_url}</a>{/if}
{else}
	<{$wrapper.wrapper|default:'span'} class="{$wrapper.class|default:'att-plugin'}"{if $wrapper.style} style="{$wrapper.style|default:'display:inline-block'}{/if}">
		{if $wrapper.display_url}<a {$wrapper.href_class} href="{$wrapper.display_url}">{/if}
			{if $thumbsize == 'original'}
				<img class="img-responsive" src="{$attachment.source_url}" alt="{$wrapper.alt|default:$wrapper.description|default:$attachment.filename|replace:"\r":""|replace:"\n":" "|escape}" title="{$wrapper.description|default:$wrapper.alt|default:$attachment.filename|replace:"\r":""|replace:"\n":" "|escape}" {if !empty($height) || !empty($width)} style="{if !empty($height)}height:{$height};{/if}{if !empty($width)}width:{$width}{/if}"{/if} />
			{elseif $attachment.thumbnail_url.$thumbsize}
				<img class="img-responsive" src="{$attachment.thumbnail_url.$thumbsize}" alt="{$wrapper.alt|default:$wrapper.description|default:$attachment.filename|replace:"\r":""|replace:"\n":" "|escape}" title="{$wrapper.description|default:$wrapper.alt|default:$attachment.filename|replace:"\r":""|replace:"\n":" "|escape}" {if !empty($height) || !empty($width)} style="{if !empty($height)}height:{$height};{/if}{if !empty($width)}width:{$width}{/if}"{/if}/>
			{/if}
			{if $wrapper.description_parsed}
				<div class="caption">{$wrapper.description_parsed}</div>

			{/if}
		{if $wrapper.display_url}</a>{/if}
	</{$wrapper.wrapper|default:'span'}>
{/if}
{/strip}
