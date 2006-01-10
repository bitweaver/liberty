{* $Header: /cvsroot/bitweaver/_bit_liberty/templates/header_inc.tpl,v 1.1.2.2 2006/01/10 19:10:57 squareing Exp $ *}
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
	<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/jscompressor.php?jsfile=libs/mygosu/ie5.js"></script>
	<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/jscompressor.php?jsfile=libs/mygosu/DynamicTreeBuilder.js"></script>
	<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/jscompressor.php?jsfile=libs/mygosu/plugins.js"></script>
	<link rel="stylesheet" type="text/css" href="{$smarty.const.UTIL_PKG_URL}javascript/libs/mygosu/DynamicTree.css" />
{/if}
{/strip}
