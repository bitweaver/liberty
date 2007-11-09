{* $Header: /cvsroot/bitweaver/_bit_liberty/templates/header_inc.tpl,v 1.13 2007/11/09 17:23:47 squareing Exp $ *}
{strip}
{if $structureInfo}
	<link rel="index" title="{tr}Contents{/tr}" href="index.php?structure_id={$structureInfo.root_structure_id}" />
	{if $structureInfo.parent.structure_id}
		<link rel="up" title="{tr}Up{/tr}" href="index.php?structure_id={$structureInfo.parent.structure_id}" />
	{/if}
	{if $structureInfo.prev.structure_id}
		<link rel="prev" title="{tr}Previous{/tr}" href="index.php?structure_id={$structureInfo.prev.structure_id}" />
	{/if}
	{if $structureInfo.next.structure_id}
		<link rel="next" title="{tr}Next{/tr}" href="index.php?structure_id={$structureInfo.next.structure_id}" />
	{/if}
{/if}

{* perhaps we can remove this as well at some point and load it using
$gBitThemes->loadJavascript(); *}
{if $comments_ajax}
	<script src="{$smarty.const.LIBERTY_PKG_URL}templates/LibertyComment.js" type="text/javascript"></script>
	<script type="text/javascript">
		LibertyComment.ROOT_ID = {if $gContent->mContentId}{$gContent->mContentId}{elseif $commentsParentId}{$commentsParentId}{else}null{/if}; {* this is the content id - would be better as part of something in kernel but here it is until that day *}
		LibertyComment.ROOT_GUID = "{if $gContent->mContentTypeGuid}{$gContent->mContentTypeGuid}{/if}";
		LibertyComment.SORT_MODE = "{$comments_sort_mode}";
		LibertyComment.BROWSER = "{$gBrowserInfo.browser}";
	</script>
{/if}

{if $loadDynamicTree || $attachments_ajax}
	Deprecated use of $loadDynamicTree or $attachments_ajax<br />
	use: $gBitThemes-&gt;loadJavascript(); instead<br />
{/if}
{/strip}
