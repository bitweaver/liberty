{strip}
<div class="DynamicTree">
	<div class="tree-wrapper1">
		{section name=ix loop=$subtree}
			{if $subtree[ix].pos eq ''}
				<h2>{$subtree[ix].title|escape} {if $subtree[ix].page_alias}({/if}{$subtree[ix].page_alias}{if $subtree[ix].page_alias}){/if}</h2>
			{/if}
		{/section}

		<div class="tree-wrapper2" id="tree">
			{section name=ix loop=$subtree}
				{if $subtree[ix].pos ne ''}
					{if $subtree[ix].first}{else}</div>{/if}
					{if $subtree[ix].last}{else}
						<div class="{if $subtree[ix].has_children}folder{else}doc{/if}">
							<a href="{$smarty.const.WIKI_PKG_URL}index.php?structure_id={$subtree[ix].structure_id}" target="{$subtree[ix].content_id}" title="{$subtree[ix].structure_id}">{$subtree[ix].title|escape}{if $subtree[ix].page_alias} ({$subtree[ix].page_alias}){/if}</a>
					{/if}
				{/if}
			{/section}
		</div>
	</div>

	<ul class="list-inline">
		<li><a id="tree-moveUp"    href="javascript:void(0)">{booticon iname="icon-arrow-up" ipackage="icons" iexplain="Up"}</a></li>
		<li><a id="tree-moveDown"  href="javascript:void(0)">{booticon iname="icon-arrow-down" ipackage="icons" iexplain="Down"}</a></li>
		<li><a id="tree-moveLeft"  href="javascript:void(0)">{booticon iname="icon-arrow-left" ipackage="icons" iexplain="Left"}</a></li>
		<li><a id="tree-moveRight" href="javascript:void(0)">{booticon iname="icon-arrow-right" ipackage="icons" iexplain="Right"}</a></li>
		{if !$no_delete}
			<li><a id="tree-remove"    href="javascript:void(0)">{booticon iname="icon-trash" ipackage="icons" iexplain="Remove"}</a></li>
		{else}
			<input id="tree-remove" type="hidden" value="dummy">
		{/if}
		<li><a id="tree-convert"   href="javascript:void(0)">{booticon ipackage=liberty iname="icon-folder-close" iexplain="Folder"} &larr; &rarr; {booticon iname="icon-file-alt" iexplain="Document"}</a></li>
		<li><small id="tree-tooltip"></small></li>
	</ul>
</div>

{form id="tree-store"}
	<input type="hidden" name="structure_string" id="structure_string" value="" />
	<input type="hidden" name="structure_id" value="{$gStructure->mInfo.structure_id}" />
	<input type="hidden" name="root_structure_id" value="{$gStructure->mInfo.root_structure_id}" />
	<div class="form-group submit">
		<noscript>
			<p class="warning">{tr}The Structure organisation system only works with javascript turned on{/tr}</p>
		</noscript>
		<input type="submit" class="btn btn-default" name="submit_structure" value="Save Changes" />
	</div>
	{formhelp note="To nest items, you first need to convert a page to a folder and then insert at least one item into the new folder before saving."}
	{if !$no_delete}
		{formhelp note="You can only delete a folder when it is empty."}
	{/if}
{/form}
{/strip}

<script type="text/javascript">//<![CDATA[
	var tree = new DynamicTreeBuilder("tree");
	tree.init();
	DynamicTreePlugins.call(tree);
//]]></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/libs/mygosu/actions.js"></script>
