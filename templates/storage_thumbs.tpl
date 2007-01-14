{strip}
{if $gContent->mStorage}
	{if !$gBitSystem->isFeatureActive( 'site_help_popup' )}
		{popup_init src="`$smarty.const.UTIL_PKG_URL`javascript/libs/overlib.js"}
	{/if}

	<div class="storage">
		{assign var=thumbsize value=$gBitSystem->getConfig('liberty_auto_display_attachment_thumbs')}
		{if !$thumbsize}
			{assign var=thumbsize value=small}
		{/if}
		{foreach from=$gContent->mStorage item=attachment }
			{capture name="size"}{$attachment.size|display_bytes}{/capture}
			{capture name="popup"}{include file="bitpackage:kernel/popup_box.tpl" content="`$attachment.filename`<br />{tr}Size{/tr}: `$smarty.capture.size`" noclose=true}{/capture}
			<div class="item">
				{if $attachment.thumbnail_url.$thumbsize}
					{if $attachment.source_url}<a href="{$attachment.source_url}">{/if}
						<img class="thumb" src="{$attachment.thumbnail_url.$thumbsize}" alt="{$attachment.filename}" title="{$attachment.filename}" {popup fullhtml=1 center=1 text=$smarty.capture.popup|escape:"javascript"|escape:"html"}/>
					{if $attachment.source_url}</a>{/if}
				{else}
					{tr}No thumbnail for{/tr} {$attachment.source_url}
				{/if}
			</div>
		{/foreach}
	</div>
{/if}
{/strip}
