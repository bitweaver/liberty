{strip}
<div class="pagination">
	{if $page gt 1}
		<a href="{$smarty.server.PHP_SELF}?{$pgnName}={$page-1}{$pgnVars}">&laquo;</a>&nbsp;
	{else}
		&nbsp;
	{/if}

	{tr}Page {$page} of {$numPages}{/tr}

	{if $page lt $numPages}
		&nbsp;<a href="{$smarty.server.PHP_SELF}?{$pgnName}={$page+1}{$pgnVars}">&raquo;</a>
	{else}
		&nbsp;
	{/if}

	<br />

	{if $gBitSystemPrefs.direct_pagination eq 'y'}
		{foreach from=$pgnPages item=link}
			{$link}&nbsp;
		{/foreach}
	{else}
		{form id="fPageSelect"}
			<input type="hidden" name="find" value="{$find}" />
			<input type="hidden" name="sort_mode" value="{$sort_mode}" />
			{foreach from=$pgnHidden key=name item=value}
				<input type="hidden" name="{$name}" value="{$value}" />
			{/foreach}
			{tr}Go to page{/tr} <input class="gotopage" type="text" size="3" maxlength="4" name="{$pgnName}" />
		{/form}
	{/if}
</div><!-- end .pagination -->
{/strip}
