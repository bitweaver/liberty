{strip}
{form legend="Select Content Type"}
	<input type="hidden" name="user_id" value="{$user_id}" />
	<div class="row">
		{formlabel label="Restrict listing" for="content_type_guid"}
		{forminput}
			{html_options onchange="submit();" options=$contentTypes name=content_type_guid id=content_type selected=$contentSelect}
			{formhelp note="Limit search by content type"}
			<noscript>
				<div><input type="submit" name="content_switch" value="{tr}change content type{/tr}" /></div>
			</noscript>
		{/forminput}

		{if $gBitSystem->isFeatureActive( 'liberty_display_status' ) && $gBitUser->hasPermission( 'p_liberty_view_all_status' )}
			{forminput}
				{html_options
					options=$content_statuses
					values=$content_statuses
					name=content_status_id
					id=content_status_id
					selected=$smarty.request.content_status_id|default:''}
				{formhelp note="Limit selection to a given status."}
			{/forminput}
		{/if}

		{forminput}
			<input type="text" name="find" value="{$listInfo.find}" />
			<input type="submit" value="{tr}Apply Filter{/tr}" name="search_objects" />
			{formhelp note="You can restrict the content listing to a given content type or apply a filter on content title."}
		{/forminput}
	</div>
{/form}

{* assign the correct sort columns for user name sorting *}
{if $gBitSystem->getConfig( 'users_display_name' ) eq 'login'}
	{assign var=isort_author value=creator_user}
	{assign var=isort_editor value=modifier_user}
{else}
	{assign var=isort_author value=creator_real_name}
	{assign var=isort_editor value=modifier_real_name}
{/if}

<table class="data">
	<caption>{tr}Available Content{/tr} <span class="total">[ {$listInfo.total_records} ]</span></caption>
	<tr>
		<th class="width2p">{smartlink ititle="ID" isort=lc.content_id list_page=$listInfo.current_page ihash=$listInfo.ihash}</th>
		{if $gBitSystem->isFeatureActive( 'liberty_display_status' ) && $gBitUser->hasPermission( 'p_liberty_view_all_status' )}
			<th>{smartlink ititle="Status" isort=content_status_name list_page=$listInfo.current_page ihash=$listInfo.ihash}</th>
		{/if}
		<th>{smartlink ititle="Title" isort=title list_page=$listInfo.current_page idefault=1 ihash=$listInfo.ihash}</th>
		<th>{smartlink ititle="Content Type" isort=content_type_guid list_page=$listInfo.current_page ihash=$listInfo.ihash}</th>
		<th>{smartlink ititle="Author" isort=$isort_author list_page=$listInfo.current_page ihash=$listInfo.ihash}</th>
		<th>{smartlink ititle="Most recent editor" isort=$isort_editor list_page=$listInfo.current_page ihash=$listInfo.ihash}</th>
		<th>{smartlink ititle="Last Modified" isort=last_modified list_page=$listInfo.current_page ihash=$listInfo.ihash}</th>
		{if $gBitUser->hasPermission( 'p_liberty_view_all_status' )}
			<th>{smartlink ititle="IP" isort=ip list_page=$listInfo.current_page ihash=$listInfo.ihash}</th>
		{/if}
	</tr>
	{foreach from=$contentList item=item}
		<tr class="{cycle values='odd,even'}">
			<td class="alignright">{$item.content_id}</td>
			{if $gBitSystem->isFeatureActive( 'liberty_display_status' ) && $gBitUser->hasPermission( 'p_liberty_view_all_status' )}
				<td>{$item.content_status_name}</td>
			{/if}
			<td>{$item.display_link}</td>
			<td>{assign var=content_type_guid value=`$item.content_type_guid`}{$contentTypes.$content_type_guid}</td>
			<td>{displayname real_name=$item.creator_real_name user=$item.creator_user}</td>
			<td class="alignright">{displayname real_name=$item.modifier_real_name user=$item.modifier_user}</td>
			<td class="alignright">{$item.last_modified|bit_short_date}</td>
			{if $gBitUser->hasPermission( 'p_liberty_view_all_status' )}
				<td>{$item.ip}</td>
			{/if}
		</tr>
	{foreachelse}
		<tr class="norecords">
			<td colspan="8">{tr}No records found{/tr}</td>
		</tr>
	{/foreach}
</table>

{pagination content_status_id=$smarty.request.content_status_id}
{/strip}
