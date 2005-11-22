{strip}
<div class="pagination">
	{if $pgnPage gt 1}
		{if $smarty.request.ajaxid}
			<a href="javascript:sendRequest( '{$smarty.request.ajaxid}','{$pgnName}={$pgnPage-1}{$pgnVars}' )">&laquo;</a>&nbsp;
		{else}
			<a href="{$smarty.server.PHP_SELF}?{$pgnName}={$pgnPage-1}{$pgnVars}">&laquo;</a>&nbsp;
		{/if}
	{else}
		&nbsp;&nbsp;
	{/if}

	{tr}Page {$pgnPage} of {$numPages}{/tr}

	{if $pgnPage lt $numPages}
		{if $smarty.request.ajaxid}
			&nbsp;<a href="javascript:sendRequest( '{$smarty.request.ajaxid}','{$pgnName}={$pgnPage+1}{$pgnVars}' )">&raquo;</a>
		{else}
			&nbsp;<a href="{$smarty.server.PHP_SELF}?{$pgnName}={$pgnPage+1}{$pgnVars}">&raquo;</a>
		{/if}
	{else}
		&nbsp;&nbsp;
	{/if}

	<br />

	{* MSIE dies when we use a form in the pagination when doing ajax stuff *}
	{if $gBitSystem->isFeatureActive( 'direct_pagination' ) or $smarty.request.ajaxid}
		{foreach from=$pgnPages item=link}
			{$link}&nbsp;
		{/foreach}
	{else}
		{form id="fPageSelect"}
			<input type="hidden" name="comments_maxComments" value="{$maxComments}" />
			<input type="hidden" name="comments_style" value="{$comments_style}" />
			<input type="hidden" name="comments_sort_mode" value="{$comments_sort_mode}" />

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
