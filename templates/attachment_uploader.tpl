{strip}
<html>
<head>
{include file="bitpackage:kernel/header_inc.tpl"}
</head>
<body>
{if !empty($errors)}
	<script type="text/javascript">
		function display_upload_errors() {ldelim}
			alert("Error with upload: {$errors}");
		{rdelim}
		addLoadHook(display_upload_errors);
	</script>
{/if}
{include file="bitpackage:liberty/edit_storage_list.tpl"}
{if empty($gContent->mContentId)}
	{foreach from=$gContent->mStorage item=storage key=attachmentId}
		<input type="hidden" name="STORAGE[existing][]" value="{$attachmentId}" />
	{/foreach}
{/if}
</body>
</html>
{/strip}
