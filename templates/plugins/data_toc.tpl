{strip}
{if $subtree}
	<ol class="structure-toc">
		<li>
			{section name=ix loop=$subtree}
				{if $subtree[ix].pos eq ''}
					<em>{$subtree[ix].title|escape} {if $subtree[ix].page_alias}({/if}{$subtree[ix].page_alias}{if $subtree[ix].page_alias}){/if}</em>
				{else}
					{if $subtree[ix].first}<ol class="structure-toc">{else}</li>{/if}
					{if $subtree[ix].last}</ol>{else}
						<li><a href="{$smarty.const.BIT_ROOT_URL}index.php?structure_id={$subtree[ix].structure_id}">{$subtree[ix].title|escape}{if $subtree[ix].page_alias} ({$subtree[ix].page_alias}){/if}</a>
					{/if}
				{/if}
			{/section}
		</li>
	</ol><!-- end outermost .toc -->
{/if}
{/strip}
