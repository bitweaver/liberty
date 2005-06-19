{strip}
{if $gContent->mStorage}
	<div class="storage">
		{foreach from=$gContent->mStorage item=attachment }
			<div class="item">
				{if $attachment.thumbnail_url.small}
					{if $attachment.source_url}<a href="{$attachment.source_url}">{/if}
						<img class="thumb" src="{$attachment.thumbnail_url.avatar}" alt="{$attachment.filename}" />
					{if $attachment.source_url}</a>{/if}
				{else}
					{tr}No thumbnail for{/tr} {$attachment.source_url}
				{/if}
			</div>
		{/foreach}
	</div>
{/if}
{/strip}
