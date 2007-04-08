{strip}
{if $gBitSystem->isFeatureActive( 'liberty_cache' ) && $gContent->isCached()}
	<a title="{tr}Refresh cache{/tr}" href="{$gContent->getDisplayUrl()}&amp;refresh_liberty_cache={$gContent->mContentId}">{biticon ipackage="icons" iname="view-refresh" iexplain="Refresh cache"}</a>
{/if}
{if $gBitUser->hasPermission( 'p_liberty_assign_content_perms' ) and $serviceHash.content_id}
	{if $gContent->mPerms} {* don't think there is a serviceHash way of working out if there are individual permissions set *}
		{assign var=perm_icon value="icons/emblem-readonly"}
	{else}
		{assign var=perm_icon value="icons/emblem-shared"}
	{/if}
	{smartlink ipackage=liberty ifile=content_permissions.php ititle="Assign Permissions" ibiticon=$perm_icon ipackage=liberty ifile="content_permissions.php" content_id=$serviceHash.content_id}
{/if}
{* This should always be last so it is right most in the icons! *}
{if $preview && $closeclick}
	<a onclick="javascript:return cClick();">{biticon ipackage=icons iname=window-close iexplain="Close Popup"}</a>
{/if}
{/strip}
