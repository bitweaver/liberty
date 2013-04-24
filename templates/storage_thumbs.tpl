{strip}
{if $gContent->mStorage}

{assign var=thumbsize value=$gBitSystem->getConfig('liberty_auto_display_attachment_thumbs')}
{if !$thumbsize}
	{assign var=thumbsize value=small}
{/if}

<div class="storage">
	<table class="table data">
		<caption>
			{tr}Files attached to this page{/tr}
		</caption>
		<thead>
			<tr>
				<th colspan="2">{tr}File{/tr}</th>
				<th>{tr}Type{/tr}</th>
				<th>{tr}Size{/tr}</th>
				<th>{tr}Last Modified{/tr}</th>
				<th>{tr}Uploaded by{/tr}</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$gContent->mStorage item=attachment key=id}
				<tr class="{cycle values="odd,even"}" >
					<td>
						{if $attachment.source_url}<a href="{$attachment.source_url}">{/if}
							<img class="thumb" src="{$attachment.thumbnail_url.$thumbsize}" alt="{$attachment.filename}" title="{$attachment.filename}" />
							{if $attachment.source_url}</a>{/if}
					</td>
					<td>
						{if $attachment.source_url}<a href="{$attachment.source_url}">{/if}
							{$attachment.filename}
						{if $attachment.source_url}</a>{/if}
					</td>
					<td class="attachmenttype">
						{$attachment.mime_type}
					</td>
					<td class="attachmentsize">
						{$attachment.file_size|display_bytes}
					</td>
					<td class="lastmodified">
						{$attachment.last_modified|bit_short_datetime}
					</td>
					<td class="uploadedby">
						{displayname user_id=$attachment.user_id}
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>

{*
		{foreach from=$gContent->mStorage item=attachment key=id}
			{* TODO: this should not be necessary anymore as soon as we've faded out old attachment plugins * }
			{if $attachment.is_mime}
				{include file=$gLibertySystem->getMimeTemplate('storage',$attachment.attachment_plugin_guid) preferences=$gContent->mStoragePrefs.$id}
			{else}
				{* TODO: get rid of old plugin system * }
				{capture name="size"}{$attachment.file_size|display_bytes}{/capture}
				{capture name="popup"}{include file="bitpackage:kernel/popup_box.tpl" content="`$attachment.filename`<br />{tr}Size{/tr}: `$smarty.capture.size`" noclose=true}{/capture}
				{if $attachment.thumbnail_url.$thumbsize}
					{* by setting hideprimary, you can hide the primary thumbnail * }
					{if !$attachment.is_primary or ( $attachment.is_primary and !$hideprimary )}
						<div class="item">
							<h3>
							{if $attachment.source_url}<a href="{$attachment.source_url}">{/if}
								<img class="thumb" src="{$attachment.thumbnail_url.$thumbsize}" alt="{$attachment.filename}" title="{$attachment.filename}" {popup fullhtml=1 center=1 text=$smarty.capture.popup|escape:"javascript"|escape:"html"}/>
								{$attachment.filename}
							{if $attachment.source_url}</a>{/if}
							</h3>
						</div>
					{/if}
				{/if}
			{/if}
		{/foreach}
*}

</div>

{/if}
{/strip}
