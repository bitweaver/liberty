{strip}
{* don't replicate the surrounding div when inserting ajax content *}
{if !$gBitThemes->isAjaxRequest()}
	<div id="edit_storage_list{if !$uploadTab}_tab{/if}{if $form_id}_{$form_id}{/if}">
{/if}
{if $gContent->mStorage}
	<div class="row">
		<table class="data" summary="List of attached files">
			<caption>{tr}Items {if $libertyUploader && empty($gContent->mContentId)}That Will Be{/if} Attached Directly to this Content{/tr}</caption>
			<tr>
				<th scope="col" class="width1p"  title="{tr}Attachment ID{/tr}">{tr}Attachment ID{/tr}</th>
				<th scope="col" class="width29p" title="{tr}Thumbnail{/tr}">{tr}Thumbnail{/tr}</th>
				<th scope="col" class="width40p" title="{tr}Inclusion Code{/tr}">{tr}Inclusion Code{/tr}</th>
				<th scope="col" class="width30p" title="{tr}Actions{/tr}">{tr}Actions{/tr}</th>
			</tr>

			{if $uploadTab}
				<tr>
					<td colspan="3">&nbsp;</td>
					<td class="actionicon">
						<label>
							{tr}No {$primary_label|default:"Primary"}{/tr}:&nbsp;
							<input type="radio" name="liberty_attachments[primary]" value="none" {if empty($gContent->mInfo[primary])}checked="checked"{/if} />
						</label>
					</td>
				</tr>
			{/if}

			{foreach from=$gContent->mStorage item=storage key=attachmentId name=atts}
				<tr class="{cycle values="odd,even"}">
					<td>
						{$storage.attachment_id}
					</td>
					<td class="aligncenter">
						{if $storage.is_mime}
							{include file=$gLibertySystem->getMimeTemplate('storage',$storage.attachment_plugin_guid) thumbsize=small preferences=$gContent->mStoragePrefs.$attachmentId attachment=$storage}
						{else}
							{jspopup href=$storage.source_url title=$storage.title|default:$storage.filename notra=1 img=$storage.thumbnail_url.avatar}
							<br />{$storage.filename} <span class="date">{$storage.file_size|display_bytes}</span>
							{if $smarty.foreach.atts.first}
								{formhelp note="click to see large preview"}
							{/if}
						{/if}
					</td>
					<td class="aligncenter">
						{if $gBitThemes->isJavascriptEnabled()}
							<div><a href="javascript:void(0);" onclick="BitBase.toggleElementDisplay('wiki_attachment_code_{$storage.attachment_id}','table-row');">Wiki Code</a></div>
							<div><a href="javascript:void(0);" onclick="BitBase.toggleElementDisplay('html_attachment_code_{$storage.attachment_id}','table-row');">HTML Code</a></div>
						{else}
							{$storage.wiki_plugin_link}
							{if $smarty.foreach.atts.first}
								{formhelp note="copy this code into your edit window to embed the file into your text"}
							{/if}
						{/if}
					</td>
					<td class="actionicon">
						{if $uploadTab}
							{* these radio buttons can not be displayed twice in the same form due to interference in $_REQUEST *}
							<label>{tr}{$primary_label|default:"Primary"}{/tr}:&nbsp;<input type="radio" name="liberty_attachments[primary]" value="{$attachmentId}"{if $storage.is_primary eq 'y'} checked="checked"{/if} /></label>
							<br />
						{/if}
						{if $gBitUser->isAdmin() || ($storage.user_id == $gBitUser->mUserId && $gBitUser->hasPermission('p_liberty_delete_attachments') ) }
							{capture name=urlArgs}{$attachmentBaseArgs}content_id={$gContent->mContentId}{if empty($gContent->mContentId)}{foreach from=$gContent->mStorage key=key item=val}&amp;STORAGE[existing][{$val.attachment_id}]={$val.attachment_id}{/foreach}{/if}{/capture}
							{if $libertyUploader || $gBitSystem->getConfig('liberty_attachment_style') == 'ajax'}
								<a href="javascript:void(0);" onclick="
									BitAjax.updater('edit_storage_list_tab_{$form_id}', '{$smarty.const.LIBERTY_PKG_URL}ajax_edit_storage.php', '{$smarty.capture.urlArgs}&amp;deleteAttachment={$attachmentId}&amp;form_id={$form_id}');
									BitAjax.updater('edit_storage_list_{$form_id}', '{$smarty.const.LIBERTY_PKG_URL}ajax_edit_storage.php', '{$smarty.capture.urlArgs}&amp;form_id={$form_id}');">
										{biticon ipackage="icons" iname="edit-delete" iexplain="delete"}
								</a>
							{else}
								{if $storage.is_mime}
									<a href="{$storage.display_url}">{biticon ipackage="icons" iname="document-open" iexplain="View"}</a>
								{/if}
								<a href="{$smarty.server.PHP_SELF}?{$smarty.capture.urlArgs}&amp;deleteAttachment={$attachmentId}">{biticon ipackage="icons" iname="edit-delete" iexplain="Delete"}</a>
							{/if}
						{/if}
					</td>
				</tr>
				{if $gBitThemes->isJavascriptEnabled()}
					{capture name=inputhelp}
						<tr>
							<td colspan="2">
								{formhelp note="copy this code into your edit window to embed the file into your text"}
							</td>
						</tr>
					{/capture}

					<tr id="wiki_attachment_code_{$storage.attachment_id}" style="display:none;">
						<td colspan=4>
							<table>
								{$smarty.capture.inputhelp}
								<tr>
									<td class="width15p">{tr}Wiki code{/tr}</td>
									<td class="width85p"><input name="attachment_source_wiki_{$storage.attachment_id}" value="{$storage.wiki_plugin_link}" readonly="readonly" class="width100p"/></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr id="html_attachment_code_{$storage.attachment_id}" style="display:none;">
						<td colspan=4>
							<table>
								{$smarty.capture.inputhelp}
								<tr>
									<th class="width15p">{tr}Size{/tr}</th>
									<th class="width85p">{tr}Code{/tr}</th>
								</tr>
								{if $storage.attachment_plugin_guid eq 'mimeimage'}
									{foreach name=size key=size from=$storage.thumbnail_url item=url}
										<tr>
											<td>{$size}</td>
											<td><input name="attachment_source_{$size}_{$storage.attachment_id}" value='&lt;img src="{$url|escape}" /&gt;' readonly="readonly" class="width100p"/></td>
										</tr>
									{/foreach}
									{if ( $storage.source_url ) }
										<tr>
											<td>original</td>
											<td><input name="attachment_source_original_{$storage.attachment_id}" value='&lt;img src="{$storage.source_url|escape}" /&gt;' readonly="readonly" class="width100p"/></td>
										</tr>
									{/if}
								{else}
									<tr>
										<td class="width15p">icon</td>
										<td class="width85p"><input name="attachment_source_icon_{$storage.attachment_id}" value='&lt;a href="{$storage.download_url}" &gt;&lt;img src="{$storage.thumbnail_url.icon}" /&gt;&lt;/a&gt;&lt;br /&gt;{$storage.filename}' readonly="readonly" class="width100p"/></td>
									</tr>
								{/if}
							</table>
						</td>
					</tr>
				{/if}
			{/foreach}
		</table>
	</div>
{/if}
{if !$gBitThemes->isAjaxRequest()}
	</div>
{/if}
{/strip}
