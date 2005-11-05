{php} include (LIBERTY_PKG_PATH."edit_storage_inc.php"); {/php}
{strip}
	{foreach from=$gLibertySystem->mPlugins item=plugin key=guid}
		{if $plugin.is_active eq 'y' and $plugin.edit_field and $plugin.plugin_type eq 'storage'}
			<div class="row">
				{formlabel label=`$plugin.edit_label`}
				{forminput}
					{eval var=$plugin.edit_field}
					{formhelp note=`$plugin.edit_help`}
				{/forminput}
			</div>
		{/if}
	{/foreach}

	<h2><a href="javascript:sendRequest( 'attbrowser' );" onclick="javascript:document.getElementById( 'attbrowser' ).innerHTML = '{tr}Loading Attachment Browser...{/tr}'">{tr}Attachment Browser{/tr}</a></h2>
	<noscript><div class="warning">{tr}The Attachment browser only works with javascript enabled{/tr}</div></noscript>
	<div id="attbrowser" class="attbrowser"></div>

	{if $gContent->mStorage}
		<div class="row">
			<table class="data" summary="List of attached files">
				<caption>{tr}Attached Items{/tr}</caption>
				<tr>
					<th scope="col" title="{tr}Thumbnail{/tr}">{tr}Thumbnail{/tr}</th>
					<th scope="col" title="{tr}File Properties{/tr}">{tr}File Properties{/tr}</th>
				</tr>

				{foreach from=$gContent->mStorage item=storage key=attachmentId}
					<tr class="{cycle values="odd,even"}">
						<td style="text-align:center;"><a href="{$storage.source_url}"><img src="{$storage.thumbnail_url.small}" alt="{$storage.filename}" /></a></td>
						<td>
							Attachment ID: {$attachmentId}
							<br />
							Filename: {$storage.filename}
							<br />
							Actions: 
							{if $gBitUser->isAdmin() || $bit_p_detach_attachment || $storage.user_id == $gBitUser->mUserId}
								<a href="{$attachmentActionBaseURL}&amp;detachAttachment={$storage.attachment_id}">{biticon ipackage=liberty iname="detach" iexplain="detach"}</a>
							{/if}
							{if $gBitUser->isAdmin() ||  $storage.user_id == $gBitUser->mUserId}
								<a href="{$attachmentActionBaseURL}&amp;deleteAttachment={$storage.attachment_id}">{biticon ipackage=liberty iname="delete" iexplain="delete"}</a>
							{/if}
						</td>
					</tr>
				{/foreach}
			</table>
		</div>
	{/if}
{/strip}
