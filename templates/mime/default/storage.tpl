{strip}
<div class="item">
	{assign var=thumbsize value=$thumbsize|default:'small'}
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
{/strip}
