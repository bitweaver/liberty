{php} include (LIBERTY_PKG_PATH."edit_storage_inc.php"); {/php}
{strip}
{if $gContent->mStorage}
	<div class="row">
		<table class="data" summary="List of attached files">
			{if $attachmentBrowser}
			<caption>{tr}Your Attachments{/tr}</caption>
			{else}
			<caption>{tr}Items {if $libertyUploader && empty($gContent->mContentId)}That Will Be{/if} Attached Directly to this Content{/tr}</caption>
			{/if}
			<tr>
				<th scope="col" title="{tr}Thumbnail{/tr}">{tr}Thumbnail{/tr}</th>
				<th scope="col" title="{tr}File Properties{/tr}">{tr}File Properties{/tr}</th>
				<th scope="col" title="{tr}Inclusion Code{/tr}">{tr}Inclusion Code{/tr}</th>
			</tr>
			<tr>
				<td style="text-align:center;">{formhelp note="click to see large preview"}</td>
				<td style="text-align:center;"></td>
				<td style="text-align:center;">{formhelp note="copy this code into your edit window to embed the image into your text"}</td>
			</tr>

			{foreach from=$gContent->mStorage item=storage key=attachmentId}
				<tr class="{cycle values="odd,even"}">
					<td style="text-align:center;"><a href="{$storage.source_url}"><img src="{$storage.thumbnail_url.avatar}" alt="{$storage.filename}" /></a></td>
					<td>
						Attachment ID: {$attachmentId}
						<br />
						Filename: {$storage.filename}
						<br />
						Actions: 
						{if $gBitUser->isAdmin() || $gBitUser->hasPermission( 'p_liberty_detach_attachment' ) || $storage.user_id == $gBitUser->mUserId}
							{if isset($gContent->mContentId) }
								<a href="{$attachmentActionBaseURL}&amp;content_id={$gContent->mContentId}&amp;detachAttachment={$attachmentId}">{biticon ipackage=icons iname="edit-cut" iexplain="detach"}</a>
							{elseif !$libertyUploader}
								<a href="{$attachmentActionBaseURL}&amp;detachAttachment={$attachmentId}">{biticon ipackage=icons iname="edit-cut" iexplain="detach"}</a>
							{/if}
						{/if}
						{if $gBitUser->isAdmin() ||  $storage.user_id == $gBitUser->mUserId}
							<a href="{$attachmentActionBaseURL}&amp;deleteAttachment={$attachmentId}">{biticon ipackage="icons" iname="edit-delete" iexplain="delete"}</a>
						{/if}
					</td>
					<td style="text-align:center; width:30%">
						{ldelim}attachment id={$attachmentId}{rdelim}
					</td>
				</tr>
			{/foreach}
		</table>
	</div>
{/if}
{/strip}
