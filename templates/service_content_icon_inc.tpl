{strip}
{if !empty($gContent)}
	{if $gBitSystem->isFeatureActive( 'liberty_cache' ) && $gContent && $gContent->isCached( $serviceHash.content_id ) && $gBitUser->hasPermission( 'p_users_view_icons_and_tools' )}
		{assign var=url value=$gContent->getDisplayUrl()|default:$serviceHash.display_url}
		{if (not empty($url)) && strstr($url, "?") }
			{assign var="amp" value="&amp;"}
		{else}
			{assign var="amp" value="?"}
		{/if}
		<a title="{tr}Refresh cache{/tr}" href="{$url}{$amp}refresh_liberty_cache={$serviceHash.content_id}">
			{booticon iname="icon-recycle"  ipackage="icons"  iexplain="Refresh cache"}
		</a>
	{/if}
	{if $gBitUser->hasPermission( 'p_liberty_assign_content_perms' ) and $serviceHash.content_id}
		{if $gContent->hasUserPermissions()}
			{assign var=iconClass value="highlight"}
		{/if}
		{if $role_model }
			{smartlink ipackage=liberty ifile="content_role_permissions.php" ititle="Assign Permissions" booticon="icon-key" class=$iconClass ipackage=liberty ifile="content_permissions.php" content_id="$serviceHash.content_id"}
		{else}
			{smartlink ipackage=liberty ifile="content_permissions.php" ititle="Assign Permissions" booticon="icon-key" class=$iconClass ipackage=liberty ifile="content_permissions.php" content_id="$serviceHash.content_id"}
		{/if}
	{/if}
	{* This should always be last so it is right most in the icons! *}
	{if $preview && $closeclick}
		<a onclick="javascript:return cClick();">{booticon iname="icon-remove"  ipackage=icons  iexplain="Close Popup"}</a>
	{/if}
{/if}
{/strip}
