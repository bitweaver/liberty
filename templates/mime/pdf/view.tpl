{strip}
{if $attachment.media_url}
	<div class="form-group">
		<object class="mime-pdf" data="{$attachment.media_url|escape}" type="application/pdf" width="100%" height="800">
			<a href="{$attachment.media_url|escape}">{tr}Download PDF{/tr}</a>
		</object>
	</div>

	{include file="bitpackage:liberty/mime_meta_inc.tpl"}
{else}
	{include file=$gLibertySystem->getMimeTemplate('view', $smarty.const.LIBERTY_DEFAULT_MIME_HANDLER)}
{/if}
{/strip}
