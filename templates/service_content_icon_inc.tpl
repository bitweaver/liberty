{if $gBitSystem->isFeatureActive( 'liberty_cache' ) && $gContent->isCached()}
	<a title="{tr}Refresh cache{/tr}" href="{$gContent->getDisplayUrl()}&amp;refresh_liberty_cache={$gContent->mContentId}">{biticon ipackage="icons" iname="view-refresh" iexplain="Refresh cache"}</a>
{/if}
