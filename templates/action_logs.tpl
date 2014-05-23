{strip}
<div class="listing liberty">
	<div class="header">
		<h1>{tr}Action Logs{/tr}</h1>
	</div>

	<div class="body">
		{formfeedback hash=$feedback}
		{form}
			{legend legend="Liberty Action Logs"}
				{foreach from=$logSettings key=item item=output}
					<div class="control-group">
						{formlabel label=$output.label for=$item}
						{forminput}
							{if $output.type == 'input'}
								<input type='text' name="{$item}" id="{$item}" value="{$gBitSystem->getConfig($item)}" />
							{else}
								{html_checkboxes name="$item" values="y" checked=$gBitSystem->getConfig($item) labels=false id=$item}
							{/if}
							{formhelp note=$output.note page=$output.page}
						{/forminput}
					</div>
				{/foreach}

				<div class="control-group">
					{formlabel label="Delete Logs" for="log_prune"}
					{forminput}
						<select name="log_prune" id="log_prune">
							<option value=""        >{tr}Don't remove logs{/tr}</option>
							<option value="1"       >{tr}All{/tr}</option>
							<option value="86400"   >{tr}One Day{/tr}</option>
							<option value="604800"  >{tr}One Week{/tr}</option>
							<option value="2419200" >{tr}One Month{/tr}</option>
							<option value="14515200">{tr}Six Months{/tr}</option>
						</select>
						{formhelp note="Delete logs older than the given time span."}
					{/forminput}
				</div>

				<div class="control-group submit">
					<input type="submit" class="btn btn-default" name="apply_settings" value="{tr}Apply Settings{/tr}" />
				</div>
			{/legend}
		{/form}

		{* Need to add some filtering options here *}

		{minifind}

		<table class="table data">
			<caption>{tr}Action Logs{/tr} <span class="total">[ {$listInfo.total_records|default:0} ]</span></caption>
			<tr>
				<th></th>
				<th>{smartlink ititle="ID" isort=content_id page=$listInfo.page}</th>
				<th>{smartlink ititle="Title" isort=title page=$listInfo.page}</th>
				<th>{smartlink ititle="Log Entry" isort=action_log page=$listInfo.page}</th>
				<th>{smartlink ititle="Content Type" isort=content_name page=$listInfo.page}</th>
				<th>{smartlink ititle="Log time" isort=last_modified page=$listInfo.page idefault=1}</th>
				<th>{smartlink ititle="Modified by" isort=user_id page=$listInfo.page} [{smartlink ititle="IP" isort=ip page=$listInfo.page}]</th>
			</tr>
			{foreach from=$actionLogs item=log}
				<tr class="{cycle values="odd,even"}">
					<td>
						{if $log.error_message}
							{biticon ipackage=icons iname="dialog-error" iexplain="Error"}
						{else}
							{biticon ipackage=icons iname="dialog-information" iexplain="Information"}
						{/if}
					</td>
					<td>{if $log.content_id}<a href="{$smarty.const.BIT_ROOT_URL}index.php?content_id={$log.content_id}">{$log.content_id}{/if}</a></td>
					<td>{$log.title|escape}</td>
					<td>
						{$log.log_message|nl2br}
						{$log.error_message|nl2br}
					</td>
					<td>{tr}{$log.content_name}{/tr}</td>
					<td>{$log.last_modified|bit_short_datetime}</td>
					<td>{displayname hash=$log} [{$log.ip}]</td>
				</tr>
			{foreachelse}
				<tr class="norecords">
					<td colspan="8">{tr}No Records Found{/tr}</td>
				</tr>
			{/foreach}
		</table>

		{pagination}

	</div><!-- end .body -->
</div><!-- end .liberty -->
{/strip}
