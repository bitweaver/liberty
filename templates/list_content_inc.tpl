{strip}
{form legend="Select Content Type"}
	<input type="hidden" name="user_id" value="{$user_id}" />
	<div class="row">
		{formlabel label="Restrict listing" for="content_type_guid"}
		{forminput}
			{html_options onchange="submit();" options=$contentTypes name=content_type_guid id=content_type selected=$contentSelect}
			<noscript>
				<div><input type="submit" name="content_switch" value="{tr}change content type{/tr}" /></div>
			</noscript>
		{/forminput}

		{forminput}
			<input type="text" name="find" value="{$listInfo.find}" />
			<input type="submit" value="{tr}Apply Filter{/tr}" name="search_objects" />
			{formhelp note="You can restrict the content listing to a given content type or apply a filter."}
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
		<th style="width:2%;">{smartlink ititle="ID" isort=content_id list_page=$listInfo.current_page user_id=$user_id content_type_guid=$content_type_guids find=$listInfo.find}</th>
		<th>{smartlink ititle="Title" isort=title list_page=$listInfo.current_page user_id=$user_id content_type_guid=$content_type_guids find=$listInfo.find idefault=1}</th>
		<th>{smartlink ititle="Content Type" isort=content_type_guid list_page=$listInfo.current_page user_id=$user_id content_type_guid=$content_type_guids find=$listInfo.find}</th>
		<th>{smartlink ititle="Author" isort=$isort_author list_page=$listInfo.current_page user_id=$user_id content_type_guid=$content_type_guids find=$listInfo.find}</th>
		<th>{smartlink ititle="Most recent editor" isort=$isort_editor list_page=$listInfo.current_page user_id=$user_id content_type_guid=$content_type_guids find=$listInfo.find}</th>
		<th>{smartlink ititle="Last Modified" isort=last_modified list_page=$listInfo.current_page user_id=$user_id content_type_guid=$content_type_guids find=$listInfo.find}</th>
		<th>{smartlink ititle="IP" isort=ip list_page=$listInfo.current_page user_id=$user_id content_type_guid=$content_type_guids find=$listInfo.find}</th>
	</tr>
	{foreach from=$contentList item=item}
		<tr class="{cycle values='odd,even'}">
			<td style="text-align:right;">{$item.content_id}</td>
			<td>{$item.display_link}</td>
			<td>{assign var=content_type_guid value=`$item.content_type_guid`}{$contentTypes.$content_type_guid}</td>
			<td>{displayname real_name=$item.creator_real_name user=$item.creator_user}</td>
			<td style="text-align:right;">{displayname real_name=$item.modifier_real_name user=$item.modifier_user}</td>
			<td style="text-align:right;">{$item.last_modified|bit_short_date}</td>
			<td>{$item.ip}</td>
		</tr>
	{/foreach}
</table>

{pagination}
{/strip}
