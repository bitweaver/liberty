{strip}
<ul>
	<li><a class="item" href="{$gBitLoc.KERNEL_PKG_URL}admin/index.php?page=liberty">{tr}Liberty Settings{/tr}</a></li>
	<li><a class="item" href="{$gBitLoc.LIBERTY_PKG_URL}admin/plugins.php">{tr}Plugins{/tr}</a></li>
	{if $gBitSystem->isPackageActive( 'pdf' ) }
		<li><a class="item" href="{$gBitLoc.KERNEL_PKG_URL}admin/index.php?page=pdf">{tr}PDF Settings{/tr}</a></li>
	{/if}
	{if $gBitSystem->isPackageActive( 'quota' ) }
		<li><a class="item" href="{$gBitLoc.KERNEL_PKG_URL}admin/index.php?page=quota">{tr}Quota Settings{/tr}</a></li>
	{/if}
	{if $gBitSystem->isPackageActive( 'quicktags' ) }
		<li><a class="item" href="{$gBitLoc.QUICKTAGS_PKG_URL}admin/index.php">{tr}Quicktags{/tr}</a></li>
	{/if}
	{if $gBitSystem->isPackageActive( 'hotwords' ) }
		<li><a class="item" href="{$gBitLoc.HOTWORDS_PKG_URL}admin/index.php">{tr}Hotwords{/tr}</a></li>
	{/if}
</ul>
{/strip}
