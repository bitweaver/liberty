{strip}
{if !empty($errors)}
	<script type="text/javascript">
		alert("Error with upload: {$errors}");
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
{if $gContent->mContentId}
	<input type="hidden" name="new_content_id" id="new_content_id" value="{$gContent->mContentId}" />
{/if}
{/strip}
