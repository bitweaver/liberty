{strip}
{if $gContent && $gBitSystem->isFeatureActive('promotions_stumbleupon')}
	{if $gBitSystem->getConfig('promotions_stumbleupon_style') == 'icon'}
		<a href="http://www.stumbleupon.com/submit?url={$gContent->getDisplayUri()|escape:'url'}&amp;title={$gContent->getTitle()|escape:'url'}&amp;bodytext={$parsed_data|escape:'url'|truncate:350}" />{biticon ipackage="liberty" iname="su-wide" iexplain="stumbleupon this!" iforce="icon"}</a>
	{else}
		{biticon ipackage="liberty" iname="su" iexplain="stumbleupon this!" iforce="icon"}<a href="http://www.stumbleupon.com/submit?url={$gContent->getDisplayUri()|escape:'url'}&amp;title={$gContent->getTitle()|escape:'url'}&amp;bodytext={$parsed_data|escape:'url'|truncate:350}" />{tr}stumbleupon!{/tr}</a>
	{/if}
{/if}
{/strip}