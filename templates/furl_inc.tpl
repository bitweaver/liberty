{strip}
{if $gContent && $gBitSystem->isFeatureActive('promotions_furl')}
	{if $gBitSystem->getConfig('promotions_furl_style') == 'icon'}
		<a href="http://www.furl.net/storeIt.jsp?u={$gContent->getDisplayUri()|escape:'url'}&amp;t={$gContent->getTitle()|escape:'url'}&amp;nostatic=1&amp;name=&amp;rx_id=">{biticon ipackage="liberty" iname="furl-wide" iexplain="Furl this!" iforce="icon"}</a>
	{else}
		{biticon ipackage="liberty" iname="furl" iexplain="Furl this!" iforce="icon"}<a href="http://www.furl.net/storeIt.jsp?u={$gContent->getDisplayUri()|escape:'url'}&amp;t={$gContent->getTitle()|escape:'url'}&amp;nostatic=1&amp;name=&amp;rx_id=">{tr}furl this!{/tr}</a>
	{/if}
{/if}
{/strip}