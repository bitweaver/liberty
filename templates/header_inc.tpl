{* $Header: /cvsroot/bitweaver/_bit_liberty/templates/header_inc.tpl,v 1.8 2007/06/13 15:19:54 wjames5 Exp $ *}
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
{if $loadDynamicTree}
	<!--[if lte IE 5.0]>
		<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/libs/mygosu/ie5.js"></script>
	<![endif]-->
	<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/libs/mygosu/DynamicTreeBuilder.js"></script>
	<link rel="stylesheet" type="text/css" href="{$smarty.const.UTIL_PKG_URL}javascript/libs/mygosu/DynamicTree.css" />
{/if}
{if $comments_ajax}
	<script src="{$smarty.const.LIBERTY_PKG_URL}templates/LibertyComment.js" type="text/javascript"></script>
	<script type="text/javascript">
		LibertyComment.ROOT_ID = {if $gContent->mContentId}{$gContent->mContentId}{elseif $commentsParentId}{$commentsParentId}{else}null{/if}; {* this is the content id - would be better as part of something in kernel but here it is until that day *}
		LibertyComment.ROOT_GUID = "{if $gContent->mContentTypeGuid}{$gContent->mContentTypeGuid}{/if}";
		LibertyComment.SORT_MODE = "{$comments_sort_mode}";
		LibertyComment.BROWSER = "{$gBrowserInfo.browser}";
	</script>
{/if}
{/strip}