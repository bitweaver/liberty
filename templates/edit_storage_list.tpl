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

			{foreach from=$gContent->mStorage item=storage key=attachmentId name=atts}
				<tr class="{cycle values="odd,even"}">
					<td style="text-align:center;">
						{jspopup href=$storage.source_url title=$storage.title|default:$storage.filename notra=1 img=$storage.thumbnail_url.avatar}
						{if $smarty.foreach.atts.first}
							{formhelp note="click to see large preview"}
						{/if}
					</td>
					<td>
						{tr}Attachment ID{/tr}: {$attachmentId} {if $gContent->mInfo.primary_attachment_id eq $attachmentId}({tr}Primary{/tr}){/if} <br />
						{tr}Filename{/tr}: {$storage.filename} <br />
						{tr}Actions{/tr}:
						{if ($gBitUser->isAdmin() || $gBitUser->hasPermission( 'p_liberty_detach_attachment' ) || $storage.user_id == $gBitUser->mUserId) && !empty($gContent->mContentId)}
							{if $attachmentBrowser}
								{if in_array($gContent->mContentId, $storage.attached_to)}
									<a href="javascript:ajax_updater('attbrowser', '{$attachmentActionBaseURL}', 'content_id={$gContent->mContentId}&amp;detachAttachment={$attachmentId}')">{biticon ipackage=icons iname="edit-cut" iexplain="detach"}</a>
								{/if}
							{elseif $libertyUploader or $gBitSystem->getConfig('liberty_attachment_style') == 'ajax'}
								<a href="javascript:ajax_updater('edit_storage_list_div', '{$attachmentActionBaseURL}', 'content_id={$gContent->mContentId}&amp;detachAttachment={$attachmentId}');">{biticon ipackage=icons iname="edit-cut" iexplain="detach"}</a>
							{else}
								<a href="{$attachmentActionBaseURL}&amp;content_id={$gContent->mContentId}&amp;detachAttachment={$attachmentId}">{biticon ipackage=icons iname="edit-cut" iexplain="detach"}</a>
							{/if}
						{/if}
						{if ( $gBitUser->isAdmin() || $storage.user_id == $gBitUser->mUserId ) && !isset($storage.content_id) }
							{if $attachmentBrowser}
								<a href="javascript:ajax_updater('attbrowser', '{$attachmentActionBaseURL}', 'deleteAttachment={$attachmentId}');">{biticon ipackage="icons" iname="edit-delete" iexplain="delete"}</a>
							{elseif $libertyUploader || $gBitSystem->getConfig('liberty_attachment_style') == 'ajax'}
								<a href="javascript:ajax_updater('edit_storage_list_div', '{$attachmentActionBaseURL}', 'deleteAttachment={$attachmentId}');">{biticon ipackage="icons" iname="edit-delete" iexplain="delete"}</a>
							{else}
								<a href="{$attachmentActionBaseURL}&amp;deleteAttachment={$attachmentId}">{biticon ipackage="icons" iname="edit-delete" iexplain="delete"}</a>
							{/if}
						{/if}
					</td>
					<td style="text-align:center; width:30%">
						{$storage.wiki_plugin_link}
						{if $smarty.foreach.atts.first}
							{formhelp note="copy this code into your edit window to embed the file into your text"}
						{/if}
					</td>
				</tr>
			{/foreach}
		</table>
	</div>
{/if}
{/strip}
