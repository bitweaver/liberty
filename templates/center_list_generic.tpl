{strip}
<div class="floaticon">{bithelp}</div>

<div class="listing {$contentType|strtolower}">
	<div class="header">
		<h1>{tr}{if $contentType}{$contentType}s{else}Content Listing{/if}{/tr}</h1>
	</div>

	{formfeedback error=$errors}
	
	<div class="body">
		{form class="minifind" legend="find in entries"}
			<input type="hidden" name="sort_mode" value="{$sort_mode}" />
			{booticon iname="fa-magnifying-glass" iexplain="Search"} &nbsp;
			<label>{tr}Title{/tr}:&nbsp;<input size="16" type="text" name="find_title" value="{$find_title|default:$smarty.request.find_title|escape}" /></label> &nbsp;
			<label>{tr}Author{/tr}:&nbsp;<input size="10" type="text" name="find_author" value="{$find_author|default:$smarty.request.find_author|escape}" /></label> &nbsp;
			<label>{tr}Last Editor{/tr}:&nbsp;<input size="10" type="text" name="find_last_editor" value="{$find_last_editor|default:$smarty.request.find_last_editor|escape}" /></label> &nbsp;
			<input type="submit" class="btn btn-default" name="search" value="{tr}Find{/tr}" />&nbsp;
			<input type="button" onclick="location.href='{$smarty.server.SCRIPT_NAME}{if $hidden}?{/if}{foreach from=$hidden item=value key=name}{$name}={$value}&amp;{/foreach}'" value="{tr}Reset{/tr}" />
		{/form}

		{form id="checkform"}
			<ul class="list-inline navbar">
				<li>{smartlink ititle="Title" isort="title" icontrol=$listInfo}</li>
				<li>{smartlink ititle="Last Modified" iorder="desc" idefault=1 isort="last_modified" icontrol=$listInfo}</li>
				<li>{smartlink ititle="Author" isort="creator_user" icontrol=$listInfo}</li>
				<li>{smartlink ititle="Last Editor" isort="modifier_user" icontrol=$listInfo}</li>
				{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='list_sort' serviceHash=$gContent->mInfo}
			</ul>

			<input type="hidden" name="offset" value="{$offset}" />
			<input type="hidden" name="sort_mode" value="{$sort_mode}" />

			<div class="clear"></div>

			<table class="table data">
				<caption>{tr}{if $contentType}{$contentType}s{else}Content{/if} Listing{/tr} <span class="total">[ {$listInfo.total_records} ]</span></caption>
				<tr>
				{counter name=cols start=-1 print=false}
					<th class="width2p">{smartlink ititle="ID" isort=content_id list_page=$listInfo.current_page ihash=$listInfo.ihash}</th>
					{counter name=cols assign=cols print=false}
					<th>{smartlink ititle="Title" isort=title list_page=$listInfo.current_page idefault=1 ihash=$listInfo.ihash}</th>
					{counter name=cols assign=cols print=false}
					<th>{smartlink ititle="Content Type" isort=content_type_guid list_page=$listInfo.current_page ihash=$listInfo.ihash}</th>
					{counter name=cols assign=cols print=false}
					<th>{smartlink ititle="Author" isort=$isort_author list_page=$listInfo.current_page ihash=$listInfo.ihash}</th>
					{counter name=cols assign=cols print=false}
					<th>{smartlink ititle="Most recent editor" isort=$isort_editor list_page=$listInfo.current_page ihash=$listInfo.ihash}</th>
					{counter name=cols assign=cols print=false}
					<th>{smartlink ititle="Last Modified" isort=last_modified list_page=$listInfo.current_page ihash=$listInfo.ihash}</th>
					{counter name=cols assign=cols print=false}
					{if $gBitUser->hasPermission('p_liberty_view_all_status')}
						<th>{smartlink ititle="IP" isort=ip list_page=$listInfo.current_page ihash=$listInfo.ihash}</th>
						{counter name=cols assign=cols print=false}
					{/if}
				</tr>
				{foreach from=$contentList item=item}
					<tr class="{cycle values='odd,even'}">
						<td class="aligncenter">{$item.content_id}</td>
						<td>{$item.display_link}</td>
						<td>{assign var=content_type_guid value=$item.content_type_guid}{$contentTypes.$content_type_guid}</td>
						<td>{displayname real_name=$item.creator_real_name user=$item.creator_user}</td>
						<td>{displayname real_name=$item.modifier_real_name user=$item.modifier_user}</td>
						<td>{$item.last_modified|bit_short_date}</td>
						{if $gBitUser->hasPermission('p_liberty_view_all_status')}
							<td class="aligncenter">{$item.ip}</td>
						{/if}
					</tr>
				{foreachelse}
					<tr class="norecords">
						<td class="aligncenter" colspan="{$cols}">
							{tr}No records found{/tr}
						</td>
					</tr>
				{/foreach}
			</table>
		{/form}

		{pagination}
	</div><!-- end .body -->
</div><!-- end .listing -->
{/strip}
