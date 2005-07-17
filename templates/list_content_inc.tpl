{strip}
{form legend="Select Content Type"}
	<input type="hidden" name="user_id" value="{$user_id}" />
	<div class="row">
		{formlabel label="Restrict listing" for="content_type"}
		{forminput}
			<select name="content_type" id="content_type" onchange="submit();">
				<option {if !$contentSelect}selected="selected"{/if} value="">All Content</option>
				{foreach from=$contentTypes key=guid item=description}
					<option value="{$guid}" {if $contentSelect eq $guid}selected="selected"{assign var=selectDescription value=$description}{/if}>{$description}</option>
				{/foreach}
			</select>
			<noscript>
				<div><input type="submit" name="content_switch" value="{tr}change content type{/tr}" /></div>
			</noscript>
		{/forminput}

		{forminput}
			<input type="text" name="find_objects" />
			<input type="submit" value="{tr}filter{/tr}" name="search_objects" />
			{formhelp note="You can restrict the content listing to a given content type or apply a filter."}
		{/forminput}
	</div>
{/form}

<table class="data">
	<caption>{tr}Available Content{/tr}</caption>
	<tr>
		<th>{smartlink ititle="Title" isort=title page=$page user_id=$user_id idefault=1}</th>
		<th>{smartlink ititle="Content Type" isort=content_type_guid page=$page user_id=$user_id}</th>
		<th>{tr}Author{/tr}</th>
		<th>{tr}Most Recent Editor{/tr}</th>
		<th>&nbsp;</th>
	</tr>
	{foreach from=$contentList item=item}
		<tr class="{cycle values='odd,even'}">
			<td>{$item.display_link}</td>
			<td>{assign var=content_type_guid value=`$item.content_type_guid`}{$contentTypes.$content_type_guid}</td>
			<td>{displayname real_name=$item.creator_real_name user=$item.creator_user}</td>
			<td>{displayname real_name=$item.modifier_real_name user=$item.modifier_user}</td>
			<td>{$item.last_modified|bit_short_date}</td>
		</tr>
	{/foreach}
</table>

{libertypagination numPages=$numPages page=$curPage sort_mode=$sort_mode content_type=$contentSelect user_id=$user_id}
{/strip}
