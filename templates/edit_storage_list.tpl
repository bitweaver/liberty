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
				<th scope="col" style="width:30%:" title="{tr}Thumbnail{/tr}">{tr}Thumbnail{/tr}</th>
				<th scope="col" style="width:40%:" title="{tr}Inclusion Code{/tr}">{tr}Inclusion Code{/tr}</th>
				<th scope="col" style="width:30%:" title="{tr}Actions{/tr}">{tr}Actions{/tr}</th>
			</tr>

			{foreach from=$gContent->mStorage item=storage key=attachmentId name=atts}
				<tr class="{cycle values="odd,even"}">
					<td style="text-align:center;">
						{jspopup href=$storage.source_url title=$storage.title|default:$storage.filename notra=1 img=$storage.thumbnail_url.avatar}
						<br />{$storage.filename}
						{if $smarty.foreach.atts.first}
							{formhelp note="click to see large preview"}
						{/if}
					</td>
					<td style="text-align:center;">
						{$storage.wiki_plugin_link}
						{if $smarty.foreach.atts.first}
							{formhelp note="copy this code into your edit window to embed the file into your text"}
						{/if}
					</td>
					<td class="actionicon">
						{if $uploadTab}
							{if $gBitUser->isAdmin() || ($storage.user_id == $gBitUser->mUserId && $gBitUser->hasPermission('p_liberty_delete_attachments') ) }
								{if $attachmentBrowser}
									<a href="javascript:ajax_updater('attbrowser', '{$attachmentActionBaseURL}', 'deleteAttachment={$attachmentId}');">{biticon ipackage="icons" iname="edit-delete" iexplain="delete"}</a>
								{elseif $libertyUploader || $gBitSystem->getConfig('liberty_attachment_style') == 'ajax'}
									<a href="javascript:ajax_updater('edit_storage_list_div', '{$attachmentActionBaseURL}', 'deleteAttachment={$attachmentId}');">{biticon ipackage="icons" iname="edit-delete" iexplain="delete"}</a>
								{else}
									<a href="{$attachmentActionBaseURL}&amp;deleteAttachment={$attachmentId}">{biticon ipackage="icons" iname="edit-delete" iexplain="delete"}</a>
								{/if}
							{/if}
						{else}
							{* these radio buttons can not be displayed twice in the same form due to interference in $_REQUEST *}
							<label>{tr}{$primary_label|default:"Primary"}{/tr}: <input type="radio" name="liberty_attachments[primary]" value="{$attachmentId}"{if $storage.is_primary eq 'y'} checked="checked"{/if}/></label>
							<br />
						{/if}
					</td>
				</tr>
			{/foreach}
		</table>
	</div>
{/if}
{/strip}
