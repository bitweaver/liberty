{strip}
<li {if $editingStructure}id="structure-node-{$structure_tree.structure_id}" content_id="{$structure_tree.content_id}" structure_id="{$structure_tree.structure_id}"{/if} {if $structure_tree.structure_id==$smarty.request.structure_id}class="highlight"{/if}>
		{if $editingStructure}
		<div class="inline-block">
			<div class="btn btn-default btn-xs" onclick="deleteStructureNode('{$structure_tree.structure_id}','{$structure_tree.title|escape:javascript}')">{booticon iname="fa-trash" class="icon"}</div>
			<span class="inline-block" style="padding:5px;">{booticon iname="fa-arrows-up-down-left-right" class="structure-sort-handle"}</span>
		</div>
		{/if}
		<a href="{$structure_tree.display_url|default:"`$smarty.const.WIKI_PKG_URL`index.php?structure_id=`$structure_tree.structure_id`"}">{$structure_tree.title|escape}</a>
{/strip}
