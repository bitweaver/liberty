{strip}
{if $errors}
	<script>
		alert("Error with upload: {$errors|addslashes}");
	</script>
{/if}
<div id="result_tab">
	{include file="bitpackage:liberty/edit_storage_list.tpl"}
	{if empty($gContent->mContentId)}
		{foreach from=$gContent->mStorage item=storage key=attachmentId}
			<input type="hidden" name="STORAGE[existing][]" value="{$attachmentId}" />
		{/foreach}
	{/if}
</div>
<div id="result_list">
	{include file="bitpackage:liberty/edit_storage_list.tpl" uploadTab=0}
</div>
<input type="hidden" name="upload_content_id" id="upload_content_id" value="{if $gContent->mContentId}{$gContent->mContentId}{/if}" />
{/strip}
