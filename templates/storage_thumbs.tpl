{strip}
{if !$gBitSystem->isFeatureActive( 'feature_helppopup' )}
	{popup_init src="`$smarty.const.THEMES_PKG_URL`js/overlib.js"}
{/if}

{if $gContent->mStorage}
	<div class="storage">
		{foreach from=$gContent->mStorage item=attachment }
			{capture name="popup"}
				{include file="bitpackage:kernel/popup_box.tpl" content="<div style='text-align:center'><img src='`$attachment.thumbnail_url.small`' /></div>`$attachment.filename`<br />{tr}Size{/tr}: `$attachment.size` bytes" noclose=true}
			{/capture}
				{$popup}
			<div class="item">
				{if $attachment.thumbnail_url.small}
					{if $attachment.source_url}<a href="{$attachment.source_url}">{/if}
						<img class="thumb" src="{$attachment.thumbnail_url.avatar}" alt="{$attachment.filename}" title="{$attachment.filename}" {popup fullhtml="1" text=$smarty.capture.popup|escape:"javascript"|escape:"html"}/>
					{if $attachment.source_url}</a>{/if}
				{else}
					{tr}No thumbnail for{/tr} {$attachment.source_url}
				{/if}
			</div>
		{/foreach}
	</div>
{/if}
{/strip}
