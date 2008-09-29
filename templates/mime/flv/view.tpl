{strip}
{if $attachment.media_url}
	<div class="row aligncenter">
		{include file="bitpackage:liberty/mime_flv_player_inc.tpl"}
	</div>

	<div class="pagination">
		{tr}View other sizes{/tr}<br />
		&nbsp;&bull;&nbsp;
		<a href="{$attachment.display_url}&size=small">{tr}Small{/tr}</a>&nbsp;&bull;&nbsp;
		<a href="{$attachment.display_url}&size=medium">{tr}Medium{/tr}</a>&nbsp;&bull;&nbsp;
		<a href="{$attachment.display_url}&size=large">{tr}Large{/tr}</a>&nbsp;&bull;&nbsp;
		<a href="{$attachment.display_url}&size=huge">{tr}Huge{/tr}</a>&nbsp;&bull;&nbsp;
		<a href="{$attachment.display_url}&size=original">{tr}Original{/tr}</a>&nbsp;&bull;&nbsp;
	</div>
{elseif $attachment.status.processing}
	<div class="row aligncenter">
		<a href="{$attachment.download_url}">
			{assign var=size value=$gBitSystem->getConfig('treasury_item_view_thumb')}
			<img src="{$attachment.thumbnail_url.$size}{$refresh}" alt="{$gContent->getTitle()}" title="{$gContent->getTitle()}" />
			<br />{$gContent->getTitle()|escape}
		</a>
	</div>
	{formfeedback warning="{tr}The video is being processed. please try to reload in a couple of minutes.{/tr}"}
{elseif $attachment.status.error}
	<div class="row aligncenter">
		<a href="{$attachment.download_url}">
			{assign var=size value=$gBitSystem->getConfig('treasury_item_view_thumb')}
			<img src="{$attachment.thumbnail_url.$size}{$refresh}" alt="{$gContent->getTitle()}" title="{$gContent->getTitle()}" />
			<br />{$gContent->getTitle()|escape}
		</a>
	</div>
	{formfeedback error="{tr}The Video could not be processed. You can upload a different version of the film or simply leave as is.{/tr}"}
{/if}

{if $attachment.meta.duration}
	<div class="row">
		{formlabel label="Duration" for=""}
		{forminput}
			{$attachment.meta.duration|display_duration}
		{/forminput}
	</div>
{/if}

{if $attachment.download_url}
	<div class="row">
		{formlabel label="Filename" for=""}
		{forminput}
			<a href="{$attachment.download_url}">{$attachment.filename|escape}</a>
			&nbsp; <small>({$attachment.mime_type})</small>
		{/forminput}
	</div>

	<div class="row">
		{formlabel label="Filesize" for=""}
		{forminput}
			{$attachment.file_size|display_bytes}
		{/forminput}
	</div>
{/if}

{attachhelp legend=1 hash=$attachment}
{/strip}
