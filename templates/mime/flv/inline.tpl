{strip}
{if $display_type == "attachment_plugin"}
	{if $wrapper.output == 'desc' || $wrapper.output == 'description'}
		{if $attachment.display_url}<a {$wrapper.href_class} href="{$wrapper.display_url|default:$attachment.display_url}">{/if}
			{$wrapper.description|escape|default:$attachment.filename}
		{if $attachment.display_url}</a>{/if}
	{else}
		<{$wrapper.wrapper|default:'div'} class="mimeflv {$wrapper.class|default:'att-plugin'}"{if $wrapper.style} style="{$wrapper.style}{/if}">
			{if $attachment.media_url}
				{include file="bitpackage:liberty/mime_flv_player_inc.tpl"}
			{/if}
			{if $wrapper.display_url}<a {$wrapper.href_class} href="{$wrapper.display_url}">{/if}
				{$wrapper.description|escape|default:"{tr}Full Details{/tr}"}
			{if $wrapper.display_url}</a>{/if}
		</{$wrapper.wrapper|default:'div'}>
	{/if}
{else}
	<div class="item">
		{if $attachment.media_url}
			{include file="bitpackage:liberty/mime_flv_player_inc.tpl"}
		{/if}

		{if $display_type eq "storage_thumbs"}
			<a href="{$attachment.display_url}">{tr}Full Details{/tr}</a>
		{/if}
	</div>
{/if}
{/strip}
