{strip}
{if $structureInfo.structure_path}
	<div class="structurebar">
		{if $gBitSystem->isFeatureActive( 'wiki_book_show_path' )}
			<div class="path">
				{section loop=$structureInfo.structure_path name=ix}
					{if $structureInfo.structure_path[ix].parent_id} &raquo; {/if}
					<a href="index.php?content_id={$structureInfo.structure_path[ix].content_id}">
						{if $structureInfo.structure_path[ix].page_alias}
							{$structureInfo.structure_path[ix].page_alias|escape}
						{else}
							{$structureInfo.structure_path[ix].title|escape}
						{/if}
					</a>
				{/section}
			</div>
		{/if}

		{if $gBitSystem->isFeatureActive( 'wiki_book_show_navigation' )}
			<div class="navigation">
				<span class="left">
					{if $structureInfo.prev and $structureInfo.prev.structure_id}
						<a href="index.php?structure_id={$structureInfo.prev.structure_id}">
							{if $wikibook_use_icons eq 'y'}
								{biticon ipackage="icons" iname="go-previous" iexplain=Previous}
							{else}
								&laquo;&nbsp;{$structureInfo.prev.title|escape}
							{/if}
						</a>
					{else}&nbsp;{/if}
				</span>

				<span class="right">
					{if $structureInfo.next and $structureInfo.next.structure_id}
						<a href="index.php?structure_id={$structureInfo.next.structure_id}">
							{if $wikibook_use_icons eq 'y'}
								{biticon ipackage="icons" iname="go-next" iexplain=Next}
							{else}
								{$structureInfo.next.title|escape}&nbsp;&raquo;
							{/if}
						</a>
					{else}&nbsp;{/if}
				</span>
			</div><!-- end .navigation -->
		{/if}
		<div class="clear"></div>
	</div><!-- end .structure -->
{/if} {* end structure *}
{/strip}
