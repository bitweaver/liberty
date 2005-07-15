{strip}
<ul class="toc">
	<li>
		{section name=ix loop=$subtree}
			{if $subtree[ix].pos eq ''}
				{if $structureInfo.structure_id eq $subtree[ix].structure_id}<div class="highlight">{/if}
					<div style="float:right;">
						<a href="{$gBitLoc.WIKI_PKG_URL}index.php?structure_id={$subtree[ix].structure_id}">{biticon iforce=icon ipackage=liberty iname="view" iexplain="view page"}</a>
						<a href="{$PHP_SELF}?structure_id={$subtree[ix].structure_id}">{biticon iforce=icon ipackage=liberty iname="settings" iexplain="edit book"}</a>
						{biticon iforce=icon ipackage=liberty iname="spacer" iexplain=""}
					</div>

					{$subtree[ix].title} {if $subtree[ix].page_alias}({/if}{$subtree[ix].page_alias}{if $subtree[ix].page_alias}){/if}
					{biticon iforce=icon ipackage=liberty iname="spacer" iexplain=""}
				{if $structureInfo.structure_id eq $subtree[ix].structure_id}</div>{/if}
			{else}
				{if $subtree[ix].first}<ul>{else}</li>{/if}
				{if $subtree[ix].last}</ul>{else}
					<li>
						{if $structureInfo.structure_id eq $subtree[ix].structure_id}<div class="highlight">{/if}
						<div style="float:right;">
							<a href="{$PHP_SELF}?structure_id={$subtree[ix].structure_id}&amp;move_node=1">{biticon iforce=icon ipackage=liberty iname="nav_prev" iexplain="move left"}</a>
							<a href="{$PHP_SELF}?structure_id={$subtree[ix].structure_id}&amp;move_node=2">{biticon iforce=icon ipackage=liberty iname="nav_up" iexplain="move up"}</a>
							<a href="{$PHP_SELF}?structure_id={$subtree[ix].structure_id}&amp;move_node=3">{biticon iforce=icon ipackage=liberty iname="nav_down" iexplain="move down"}</a>
							<a href="{$PHP_SELF}?structure_id={$subtree[ix].structure_id}&amp;move_node=4">{biticon iforce=icon ipackage=liberty iname="nav_next" iexplain="move right"}</a>

							{biticon iforce=icon ipackage=liberty iname="spacer" iexplain=""}
							<a href="{$gBitLoc.WIKI_PKG_URL}index.php?structure_id={$subtree[ix].structure_id}">{biticon iforce=icon ipackage=liberty iname="view" iexplain="view page"}</a>
							<a href="{$PHP_SELF}?structure_id={$subtree[ix].structure_id}&amp;action=edit">{biticon iforce=icon ipackage=liberty iname="settings" iexplain="edit book"}</a>
							<a href="{$PHP_SELF}?structure_id={$subtree[ix].structure_id}&amp;action=remove">{biticon iforce=icon ipackage=liberty iname="delete" iexplain="remove page"}</a>
						</div>
						<strong>{$subtree[ix].pos}</strong> {$subtree[ix].title}{if $subtree[ix].page_alias} ({$subtree[ix].page_alias}){/if}
						{biticon iforce=icon ipackage=liberty iname="spacer" iexplain=""}
						{if $structureInfo.structure_id eq $subtree[ix].structure_id}</div>{/if}
				{/if}
			{/if}
		{/section}
	</li>
</ul><!-- end outermost .toc -->
{/strip}
