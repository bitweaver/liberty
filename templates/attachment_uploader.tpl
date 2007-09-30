{strip}
{if !empty($errors)}
	<script type="text/javascript">
		function display_upload_errors() {ldelim}
			alert("Error with upload: {$errors}");
		{rdelim}
		addLoadHook(display_upload_errors);
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
{/strip}
