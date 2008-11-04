{strip}
<div class="quote">
	{if $quote.cite_url}
		{tr}In{/tr} <a href="{$quote.cite_url}" title="{$quote.title}">{$quote.title}</a> ({$quote.created|reltime:short})
	{/if}
	
	{if $quote.user_url && $quote.user_display_name}
		<a href="{$quote.user_url}" title="{$quote.user_display_name}">{$quote.user_display_name}</a>
	{else}
		{$quote.login}
	{/if}
	
	 {tr}wrote{/tr}:
	<blockquote cite="{$quote.cite_url}"><div>{$quote.ret}</div></blockquote>
</div>
{/strip}
