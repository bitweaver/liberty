{php} include (LIBERTY_PKG_PATH."edit_storage_inc.php"); {/php}
{strip}
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
