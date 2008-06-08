{strip}
{if $attachment.video_url}
	<div class="row" style="text-align:center;">
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
	<div class="row" style="text-align:center;">
		<a href="{$attachment.download_url}">
			{assign var=size value=$gBitSystem->getConfig('treasury_item_view_thumb')}
			<img src="{$attachment.thumbnail_url.$size}{$refresh}" alt="{$gContent->getTitle()}" title="{$gContent->getTitle()}" />
			<br />{$gContent->getTitle()|escape}
		</a>
	</div>
	{formfeedback warning="{tr}The video is being processed. please try to reload in a couple of minutes.{/tr}"}
{elseif $attachment.status.error}
	<div class="row" style="text-align:center;">
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
			{* TODO: get this to work if $gContent->hasEditPermission() && $attachment.video_url}
				{form ipackage=treasury ifile="plugins/form.flv.php"}
					<input type="hidden" name="content_id" value="{$gContent->mContentId}" />
					<input type="submit" name="remove_original" value="{tr}Remove Original{/tr}" />
					{formhelp note="This will remove the original file from the server. The falsh video will remain and you can still view the video but you cannot download the original anymore."}
				{/form}
			{/if*}
		{/forminput}
	</div>

	<div class="row">
		{formlabel label="Filesize" for=""}
		{forminput}
			{$attachment.file_size|display_bytes}
		{/forminput}
	</div>
{/if}

{* TODO: get this to work if $gContent->hasEditPermission() && $attachment.video_url}
	<div class="row">
		{formlabel label="New Aspect Ratio" for="aspect"}
		{forminput}
			{form ipackage=treasury ifile="plugins/form.flv.php"}
				<input type="hidden" name="content_id" value="{$gContent->mContentId}" />
				<select name="aspect" id="aspect">
					<option value="">{tr}No Change{/tr}</option>
					<option value="{math equation="x/y" x=4  y=3 }">4:3 ({tr}TV{/tr})</option>
					<option value="{math equation="x/y" x=14 y=9 }">14:9 ({tr}Anamorphic{/tr})</option>
					<option value="{math equation="x/y" x=16 y=9 }">16:9 ({tr}Widescreen{/tr})</option>
					<option value="{math equation="x/y" x=16 y=10}">16:10 ({tr}Computer Widescreen{/tr})</option>
				</select>
				<input type="submit" name="aspect_ratio" value="{tr}Set Aspect{/tr}" />
				{formhelp note="Here you can override the initially set aspect ratio. Please note that the displayed aspect aspect ratio might not correspond to the set value."}
			{/form}
		{/forminput}
	</div>
{/if*}

{attachhelp legend=1 hash=$attachment}
{/strip}
