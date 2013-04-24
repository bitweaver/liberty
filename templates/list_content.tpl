{strip}
<div class="listing liberty">
	<header>
{form class="form-search pull-right" action=$smarty.server.REQUEST_URI method="get"}
	<input type="hidden" name="user_id" value="{$smarty.request.user_id}" />
	<input type="hidden" name="sort_mode" value="{$smarty.request.sort_mode}" />

	{html_options onchange="submit();" options=$contentTypes name=content_type_guid id=content_type selected=$contentSelect}

	{if $gBitSystem->isFeatureActive( 'liberty_display_status' ) && $gBitUser->hasPermission( 'p_liberty_view_all_status' )}
			{html_options
				options=$content_statuses
				values=$content_statuses
				name=content_status_id
				id=content_status_id
				selected=$smarty.request.content_status_id|default:''}
			{formhelp note="Limit selection to a given status."}
	{/if}
	 <div class="input-append">
		<input type="text" name="find" value="{$listInfo.find}" class="search-query" placeholder="{tr}Search Within Results{/tr}">
		<input type="submit" class="btn" value="{tr}Search{/tr}" name="search_objects"/>
	</div>
{/form}
		<h1>
			{foreach from=$contentSelect item=item name=loop}
				{tr}{$contentTypes[$item]}{/tr}
				{if !$smarty.foreach.loop.last},&nbsp;{/if}
			{foreachelse}
				{tr}All Content Types{/tr}
			{/foreach}
		</h1>
		{if $smarty.request.user_id}
			{tr}User{/tr}: {displayname user_id=$smarty.request.user_id}
		{/if}
	</header>

	<div class="body">
		{include file="bitpackage:liberty/list_content_inc.tpl"}
	</div><!-- end .body -->
</div><!-- end .liberty -->
{/strip}
