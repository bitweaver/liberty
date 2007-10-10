{strip}
<div class="pagination">
	{if $pgnPage gt 1}
		{if $ajaxId}
			<a href="javascript:void(0);" onclick="BitAjax.ajaxUpdater( '{$ajaxId}', '{$smarty.const.LIBERTY_PKG_URL}ajax_attachment_browser.php', '{$pgnName}={$pgnPage-1}{$pgnVars}' );">&laquo;</a>&nbsp;
		{else}
			<a href="{$smarty.server.SCRIPT_URL}?{$pgnName}={$pgnPage-1}{$pgnVars}">&laquo;</a>&nbsp;
		{/if}
	{else}
		&nbsp;&nbsp;
	{/if}

	{tr}Page {$pgnPage} of {$numPages}{/tr}

	{if $pgnPage lt $numPages}
		{if $ajaxId}
			<a href="javascript:void(0);" onclick="BitAjax.ajaxUpdater( '{$ajaxId}', '{$smarty.const.LIBERTY_PKG_URL}ajax_attachment_browser.php', '{$pgnName}={$pgnPage+1}{$pgnVars}' );">&raquo;</a>
		{else}
			&nbsp;<a href="{$smarty.server.SCRIPT_URL}?{$pgnName}={$pgnPage+1}{$pgnVars}">&raquo;</a>
		{/if}
	{else}
		&nbsp;&nbsp;
	{/if}

	<br />

	{* MSIE dies when we use a form in the pagination when doing ajax stuff *}
	{counter start=1 print=0 name=pgcount assign=pgcount}	
	{if $gBitSystem->isFeatureActive( 'site_direct_pagination' ) or $ajaxId}
		{foreach from=$pgnPages item=link}
			{counter print=0 name=pgcount}
			{$link}&nbsp;{if $pgcount > 20}{counter start=1 print=0 name=pgcount assign=pgcount}<br/>{/if}
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
