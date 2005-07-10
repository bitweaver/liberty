{strip}

{jstabs}
	{jstab title="Edit Structure"}

		{*
		 * before editing and committing this template, please make sure it is XHMTL standard compliant on W3C
		 * this file is a real *itch to fix if messed up - XING
		*}

		<ul class="toc">
			<li>
				{section name=ix loop=$subtree}
					{if $subtree[ix].pos eq ''}
						{if $structureInfo.structure_id eq $subtree[ix].structure_id}<div class="highlight">{/if}
							{biticon iforce=icon ipackage=liberty iname="spacer" iexplain="" style="float:right"}
							<a href="{$PHP_SELF}?structure_id={$subtree[ix].structure_id}">{biticon iforce=icon ipackage=liberty iname="settings" iexplain="edit book" style="float:right"}</a>
							<a href="{$gBitLoc.WIKI_PKG_URL}index.php?structure_id={$subtree[ix].structure_id}">{biticon iforce=icon ipackage=liberty iname="view" iexplain="view page" style="float:right"}</a>

							{$subtree[ix].title} {if $subtree[ix].page_alias}({/if}{$subtree[ix].page_alias}{if $subtree[ix].page_alias}){/if}
							{biticon iforce=icon ipackage=liberty iname="spacer" iexplain=""}
						{if $structureInfo.structure_id eq $subtree[ix].structure_id}</div>{/if}
					{else}
						{if $subtree[ix].first}<ul>{else}</li>{/if}
						{if $subtree[ix].last}</ul>{else}
							<li>
								{if $structureInfo.structure_id eq $subtree[ix].structure_id}<div class="highlight">{/if}
									<a href="{$PHP_SELF}?structure_id={$subtree[ix].structure_id}&amp;action=remove">{biticon iforce=icon ipackage=liberty iname="delete" iexplain="remove page" style="float:right"}</a>
									<a href="{$PHP_SELF}?structure_id={$subtree[ix].structure_id}&amp;action=edit">{biticon iforce=icon ipackage=liberty iname="settings" iexplain="edit book" style="float:right"}</a>
									<a href="{$gBitLoc.WIKI_PKG_URL}index.php?structure_id={$subtree[ix].structure_id}">{biticon iforce=icon ipackage=liberty iname="view" iexplain="view page" style="float:right"}</a>
									{biticon iforce=icon ipackage=liberty iname="spacer" iexplain="" style="float:right"}

									<a href="{$PHP_SELF}?structure_id={$subtree[ix].structure_id}&amp;move_node=4">{biticon iforce=icon ipackage=liberty iname="nav_next" iexplain="move right" style="float:right"}</a>
									<a href="{$PHP_SELF}?structure_id={$subtree[ix].structure_id}&amp;move_node=3">{biticon iforce=icon ipackage=liberty iname="nav_down" iexplain="move down" style="float:right"}</a>
									<a href="{$PHP_SELF}?structure_id={$subtree[ix].structure_id}&amp;move_node=2">{biticon iforce=icon ipackage=liberty iname="nav_up" iexplain="move up" style="float:right"}</a>
									<a href="{$PHP_SELF}?structure_id={$subtree[ix].structure_id}&amp;move_node=1">{biticon iforce=icon ipackage=liberty iname="nav_prev" iexplain="move left" style="float:right"}</a>

									<strong>{$subtree[ix].pos}</strong> {$subtree[ix].title}{if $subtree[ix].page_alias} ({$subtree[ix].page_alias}){/if}
									{biticon iforce=icon ipackage=liberty iname="spacer" iexplain=""}
								{if $structureInfo.structure_id eq $subtree[ix].structure_id}</div>{/if}
						{/if}
					{/if}
				{/section}
			</li>
		</ul><!-- end outermost .toc -->
	{/jstab}

	{jstab title="Structure Content"}
		{include file="bitpackage:liberty/edit_structure_content.tpl"}
	{/jstab}

{*	removing alias stuff until we know what to do with it - XING
	{jstab title="Update Alias"}
		{include file="bitpackage:liberty/edit_structure_alias.tpl"}
	{/jstab}
*}
{/jstabs}
{/strip}
