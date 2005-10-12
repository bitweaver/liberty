{strip}
{if $structureInfo.structure_path}
	<div class="structurebar">
		{if $gBitSystem->isFeatureActive( 'wikibook_show_path' )}
			<span class="path">
				{section loop=$structureInfo.structure_path name=ix}
					{if $structureInfo.structure_path[ix].parent_id} &raquo; {/if}
					<a href="index.php?structure_id={$structureInfo.structure_path[ix].structure_id}">
						{$structureInfo.structure_path[ix].title}
					</a>
				{/section}
			</span>
		{/if}

		{if $gBitSystem->isFeatureActive( 'wikibook_show_navigation' )}
			<span class="navigation">
				<span class="left">
					{if $structureInfo.prev and $structureInfo.prev.structure_id}
						<a href="index.php?structure_id={$structureInfo.prev.structure_id}">
							{if $wikibook_use_icons eq 'y'}
								{biticon ipackage=liberty iname=nav_prev iexplain=Previous}
							{else}
								&laquo;&nbsp;{$structureInfo.prev.title}
							{/if}
						</a>
					{else}&nbsp;{/if}
				</span>

				<span class="right">
					{if $structureInfo.next and $structureInfo.next.structure_id}
						<a href="index.php?structure_id={$structureInfo.next.structure_id}">
							{if $wikibook_use_icons eq 'y'}
								{biticon ipackage=liberty iname=nav_next iexplain=Next}
							{else}
								{$structureInfo.next.title}&nbsp;&raquo;
							{/if}
						</a>
					{else}&nbsp;{/if}
				</span>
			</span><!-- end .navigation -->
		{/if}
		<div class="clear"></div>
	</div><!-- end .structure -->
{/if} {* end structure *}
{/strip}
