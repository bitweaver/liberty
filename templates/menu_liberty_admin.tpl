{strip}
<li class="dropdown-submenu favorite">
    <a href="#" onclick="return(false);" tabindex="-1" class="sub-menu-root">{tr}{$smarty.const.LIBERTY_PKG_DIR|capitalize}{/tr}</a>
	<ul class="dropdown-menu sub-menu">
		<li><a class="item" href="{$smarty.const.KERNEL_PKG_URL}admin/index.php?page=liberty">{tr}Liberty Settings{/tr}</a></li>
		<li><a class="item" href="{$smarty.const.LIBERTY_PKG_URL}admin/plugins.php">{tr}Plugins{/tr}</a></li>
		<li><a class="item" href="{$smarty.const.LIBERTY_PKG_URL}admin/action_logs.php">{tr}Action Logs{/tr}</a></li>
		<li><a class="item" href="{$smarty.const.LIBERTY_PKG_URL}admin/comments.php">{tr}Comments{/tr}</a></li>
		{if $gBitSystem->isPackageActive( 'pdf' ) }
			<li><a class="item" href="{$smarty.const.KERNEL_PKG_URL}admin/index.php?page=pdf">{tr}PDF Settings{/tr}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'quota' ) }
			<li><a class="item" href="{$smarty.const.KERNEL_PKG_URL}admin/index.php?page=quota">{tr}Quota Settings{/tr}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'quicktags' ) }
			<li><a class="item" href="{$smarty.const.QUICKTAGS_PKG_URL}admin/index.php">{tr}Quicktags{/tr}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'hotwords' ) }
			<li><a class="item" href="{$smarty.const.HOTWORDS_PKG_URL}admin/index.php">{tr}Hotwords{/tr}</a></li>
		{/if}
		<li><a class="item" href="{$smarty.const.BIT_ROOT_URL}liberty/list_content.php">{tr}List All Content{/tr}</a></li>
	</ul>
</li>
{/strip}
