{strip}
{if $gContent->mStorage}
	{if !$gBitSystem->isFeatureActive( 'help_popup' )}
		{popup_init src="`$smarty.const.UTIL_PKG_URL`javascript/libs/overlib.js"}
	{/if}

	<div class="storage">
		{foreach from=$gContent->mStorage item=attachment }
			{capture name="size"}{$attachment.size|kbsize}{/capture}
			{capture name="popup"}{include file="bitpackage:kernel/popup_box.tpl" content="`$attachment.filename`<br />{tr}Size{/tr}: `$smarty.capture.size`" noclose=true}{/capture}
			<div class="item">
				{if $attachment.thumbnail_url.small}
					{if $attachment.source_url}<a href="{$attachment.source_url}">{/if}
						<img class="thumb" src="{$attachment.thumbnail_url.avatar}" alt="{$attachment.filename}" title="{$attachment.filename}" {popup fullhtml=1 center=1 text=$smarty.capture.popup|escape:"javascript"|escape:"html"}/>
					{if $attachment.source_url}</a>{/if}
				{else}
					{tr}No thumbnail for{/tr} {$attachment.source_url}
				{/if}
			</div>
		{/foreach}
	</div>
{/if}
{/strip}
