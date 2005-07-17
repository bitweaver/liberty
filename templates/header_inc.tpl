{* $Header: /cvsroot/bitweaver/_bit_liberty/templates/header_inc.tpl,v 1.2 2005/07/17 17:36:11 squareing Exp $ *}
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
{/strip}
