{strip}
{* don't replicate the surrounding div when inserting ajax content *}
{if !$gBitThemes->isAjaxRequest()}
	<div id="edit_storage_list{if !$uploadTab}_tab{/if}{if $form_id}_{$form_id}{/if}">
{/if}
{if $gContent->mStorage}
	<div class="form-group">
		{formlabel label="Attachments"}
<script>
var attachmentUrls = {ldelim}{rdelim};
</script>
			{foreach from=$gContent->mStorage item=storage key=attachmentId name=atts}
<script>
attachmentUrls["{$storage.attachment_id}"] = {ldelim}{rdelim};
</script>
				<div class="row">
					<div class="col-xs-2">
						{if $storage.is_mime}
							{include file=$gLibertySystem->getMimeTemplate('storage',$storage.attachment_plugin_guid) thumbsize=small preferences=$gContent->mStoragePrefs.$attachmentId attachment=$storage}
						{else}
							{jspopup href=$storage.source_url title=$storage.title|default:$storage.filename notra=1 img=$storage.thumbnail_url.avatar}
							<div class="help-block">
						{tr}ID:{/tr} {$storage.attachment_id}<br>
								{$storage.filename} <span class="date">{$storage.file_size|display_bytes}</span>
</div>
						{/if}
						{if $uploadTab}
							{* these radio buttons can not be displayed twice in the same form due to interference in $_REQUEST *}
							<label><input type="radio" name="liberty_attachments[primary]" value="{$attachmentId}"{if $storage.is_primary eq 'y'} checked="checked"{/if} />&nbsp;{tr}{$primary_label|default:"Primary"}{/tr}</label>
						{/if}
					</div>
					<div class="col-xs-9">
						{forminput}
							<input name="attachment_source_wiki_{$storage.attachment_id}" value="{$storage.wiki_plugin_link}" class="form-control" onClick="this.select();"/>
							{formhelp note="Wiki Code"}
						{/forminput}

						{forminput}
							<input class="form-control attachment-img-{$storage.attachment_id}" name="attachment_source_{$size}_{$storage.attachment_id}" onClick="this.select();"/>
							{formhelp note="HTML `$size`"}
						{/forminput}
<script type="text/javascript">
{foreach name=size key=size from=$storage.thumbnail_url item=url}
window.attachmentUrls["{$storage.attachment_id}"]["{$size}"] = "{$url|escape}";
{/foreach}
{if ( $storage.source_url ) }
window.attachmentUrls["{$storage.attachment_id}"]["original"] = "{$storage.source_url|escape}";
{/if}
</script>

						<div class="input-group">
							<div class="input-group-btn">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="attachment-label-{$storage.attachment_id}">{tr}Size{/tr}...</span> <span class="caret"></span></button>
								<ul class="dropdown-menu">
								{foreach name=size key=size from=$storage.thumbnail_url item=url}
									<li><a href="#" onclick="setThumbnail('{$storage.attachment_id}','{$size}');return false;">{$size|ucwords}</a></li>
								{/foreach}
								{if ( $storage.source_url ) }
									<li><a href="#" onclick="setThumbnail('{$storage.attachment_id}','original');return false;">{tr}Original{/tr}</a></li>
								{/if}
								</ul>
							</div>
							<input type="text" class="form-control attachment-thumbnail-{$storage.attachment_id}" onClick="this.select();">
						</div>
					</div>
					<div class="col-xs-1">
						{if $gBitUser->isAdmin() || ($storage.user_id == $gBitUser->mUserId) }
							{capture name=urlArgs}{$attachmentBaseArgs}content_id={$gContent->mContentId}{if empty($gContent->mContentId)}{foreach from=$gContent->mStorage key=key item=val}&amp;STORAGE[existing][{$val.attachment_id}]={$val.attachment_id}{/foreach}{/if}{/capture}
							{if $libertyUploader || $gBitSystem->getConfig('liberty_attachment_style') == 'ajax'}
								<a href="javascript:void(0);" onclick="
									BitAjax.updater('edit_storage_list_tab_{$form_id}', '{$smarty.const.LIBERTY_PKG_URL}ajax_edit_storage.php', '{$smarty.capture.urlArgs}&amp;deleteAttachment={$attachmentId}&amp;form_id={$form_id}');
									BitAjax.updater('edit_storage_list_{$form_id}', '{$smarty.const.LIBERTY_PKG_URL}ajax_edit_storage.php', '{$smarty.capture.urlArgs}&amp;form_id={$form_id}');">
										{booticon iname="fa-trash" iexplain="delete"}
								</a>
							{else}
								{if $storage.is_mime}
									<a href="{$storage.display_url}">{booticon iname="fa-folder-open" iexplain="View"}</a>
								{/if}
								<a href="{$smarty.server.SCRIPT_NAME}?{$smarty.capture.urlArgs}&amp;deleteAttachment={$attachmentId}">{booticon iname="fa-trash" iexplain="Delete"}</a>
							{/if}
						{/if}
					</div>
				</div>
<script type="text/javascript">
$(document).ready(function(){ldelim}
	setThumbnail('{$storage.attachment_id}','medium');
{rdelim});
</script>
			{/foreach}
			{if $uploadTab}
				<div>
						<label>
							<input type="radio" name="liberty_attachments[primary]" value="none" {if !$gContent->getField('primary_attachment_id')}checked="checked"{/if} /> {tr}No {$primary_label|default:"Primary"}{/tr}
						</label>
				</div>
			{/if}
<script type="text/javascript">{literal}
function setThumbnail(pAttachmentId,pSize) {
    $(".attachment-img-"+pAttachmentId).attr("value",'<img src="'+attachmentUrls[pAttachmentId][pSize]+'">');
	$(".attachment-img-"+pAttachmentId).trigger( "change" )
	$(".attachment-label-"+pAttachmentId).html( pSize );
	$(".attachment-thumbnail-"+pAttachmentId).val( attachmentUrls[pAttachmentId][pSize] );
}
{/literal}</script>

	</div>
{/if}
{if !$gBitThemes->isAjaxRequest()}
	</div>
{/if}
{/strip}
