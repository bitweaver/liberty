{strip}
{if $subtree}
	<ul class="toc">
		<li>
			{section name=ix loop=$subtree}
				{if $subtree[ix].pos eq ''}
					<em>{$subtree[ix].title|escape} {if $subtree[ix].page_alias}({/if}{$subtree[ix].page_alias}{if $subtree[ix].page_alias}){/if}</em>
				{else}
					{if $subtree[ix].first}<ul>{else}</li>{/if}
					{if $subtree[ix].last}</ul>{else}
						<li><strong>{$subtree[ix].pos}</strong> <a href="{$smarty.const.BIT_ROOT_URL}index.php?structure_id={$subtree[ix].structure_id}">{$subtree[ix].title|escape}{if $subtree[ix].page_alias} ({$subtree[ix].page_alias}){/if}</a>
					{/if}
				{/if}
			{/section}
		</li>
	</ul><!-- end outermost .toc -->
{/if}
{/strip}
