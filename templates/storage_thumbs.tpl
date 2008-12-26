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
		{foreach from=$gContent->mStorage item=attachment key=id}
			{* TODO: this should not be necessary anymore as soon as we've faded out old attachment plugins *}
			{if $attachment.is_mime}
				{include file=$gLibertySystem->getMimeTemplate('storage',$attachment.attachment_plugin_guid) preferences=$gContent->mStoragePrefs.$id}
			{else}
				{* TODO: get rid of old plugin system *}
				{capture name="size"}{$attachment.file_size|display_bytes}{/capture}
				{capture name="popup"}{include file="bitpackage:kernel/popup_box.tpl" content="`$attachment.filename`<br />{tr}Size{/tr}: `$smarty.capture.size`" noclose=true}{/capture}
				<div class="item">
					{if $attachment.thumbnail_url.$thumbsize}
						{* by setting hideprimary, you can hide the primary thumbnail *}
						{if !$attachment.is_primary or ( $attachment.is_primary and !$hideprimary )}
							{if $attachment.source_url}<a href="{$attachment.source_url}">{/if}
								<img class="thumb" src="{$attachment.thumbnail_url.$thumbsize}" alt="{$attachment.filename}" title="{$attachment.filename}" {popup fullhtml=1 center=1 text=$smarty.capture.popup|escape:"javascript"|escape:"html"}/>
							{if $attachment.source_url}</a>{/if}
						{/if}
					{else}
						{tr}No thumbnail for{/tr} {$attachment.source_url}
					{/if}
				</div>
			{/if}
		{/foreach}
	</div>
{/if}
{/strip}
