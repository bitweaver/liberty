{strip}
{formfeedback hash=$feedback}
{form}
	{legend legend="Liberty Action Logs"}
		{foreach from=$logSettings key=item item=output}
			<div class="row">
				{formlabel label=`$output.label` for=$item}
				{forminput}
					{if $output.type == 'input'}
						<input type='text' name="{$item}" id="{$item}" value="{$gBitSystem->getConfig($item)}" />
					{else}
						{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item}
					{/if}
					{formhelp note=`$output.note` page=`$output.page`}
				{/forminput}
			</div>
		{/foreach}

		<div class="row submit">
			<input type="submit" name="apply_settings" value="{tr}Apply Settings{/tr}" />
		</div>
	{/legend}
{/form}

<h1>Need to add some filtering options here</h1>

{minifind}

<table class="data">
	<caption>{tr}Action Logs{/tr}</caption>
	<tr>
		<th>{smartlink ititle="ID" isort=content_id page=$listInfo.page}</th>
		<th>{smartlink ititle="Title" isort=title page=$listInfo.page}</th>
		<th>{smartlink ititle="Log Entry" isort=action_log page=$listInfo.page}</th>
		<th>{smartlink ititle="Content Type" isort=content_dscription page=$listInfo.page}</th>
		<th>{smartlink ititle="Log time" isort=last_modified page=$listInfo.page idefault=1}</th>
		<th>{smartlink ititle="Modified by" isort=user_id page=$listInfo.page} [{smartlink ititle="IP" isort=ip page=$listInfo.page}]</th>
		<th>{tr}Actions{/tr}</th>
	</tr>
	{foreach from=$actionLogs item=log}
	<tr class="{cycle values="odd,even"}">
		<td>{if $log.content_id}<a href="{$smarty.const.BIT_ROOT_URL}index.php?content_id={$log.content_id}">{$log.content_id}{/if}</a></td>
		<td>{$log.title}</td>
		<td>
			{$log.log_message|nl2br}
			{$log.error_message|nl2br}
		</td>
		<td>{$log.content_description}</td>
		<td>{$log.last_modified|bit_short_datetime}</td>
		<td>{$log.display_name} [{$log.ip}]</td>
		<td class="actionicon">
			<a href="">{biticon ipackage=icons iname=edit-delete iexplain="Remove Entry"}</a>
		</td>
	</tr>
	{/foreach}
</table>

{pagination}

{/strip}
