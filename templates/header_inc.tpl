{* $Header: /cvsroot/bitweaver/_bit_liberty/templates/header_inc.tpl,v 1.5 2006/02/10 10:35:55 squareing Exp $ *}
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
{/strip}
