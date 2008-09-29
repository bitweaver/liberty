{strip}
<h2>{tr}Assign permissions to{/tr}: {$gContent->getTitle()}</h2>

{if !$contentPerms.assigned}
	{formhelp warning="No Individual permissions set. Global Permissions apply."}
{/if}

{if $contentPerms.groups|count > 10}

	{foreach from=$contentPerms.groups item=group}
		<h3>{tr}Permissions for{/tr}: {$group.group_name}</h3>
		<table class="data">
			<tr>
				<th>{tr}Permission{/tr}</th>
				<th>{tr}Status{/tr}</th>
			</tr>
			{foreach from=$contentPerms.assignable item=perm}
				<tr>
					<td>{$perm.perm_desc} <em>({$perm.perm_name})</em></td>
					{assign var=icon value="icons/media-playback-stop"}
					{assign var=action value="assign"}
					{foreach from=$contentPerms.assigned item=ass}
						{if $ass.group_id == $group.group_id and $ass.perm_name == $perm.perm_name}
							{assign var=icon value="icons/dialog-ok"}
							{assign var=action value="remove"}
						{/if}
					{/foreach}
					<td style="text-align:center">{smartlink ititle=Allow ibiticon=$icon iforce="icon" action=$action content_id=$gContent->mContentId perm=$perm.perm_name group_id=$group.group_id}</td>
				</tr>
			{/foreach}
		</table>
		<br /><hr /><br />
	{/foreach}

{else}

	{form}
		<input type="hidden" name="content_id" value="{$gContent->mContentId}" />
		<input type="hidden" name="action" value="assign" />

		<div class="row">
			{formlabel label="Assign this Permission" for="perm"}
			{forminput}
				<select name="perm" id="perm">
					{foreach from=$contentPerms.assignable item=perm}
						<option value="{$perm.perm_name}">{$perm.perm_desc}</option>
					{/foreach}
				</select>
				{formhelp note=""}
			{/forminput}
		</div>

		<div class="row">
			{formlabel label="To this Group" for="group_id"}
			{forminput}
				<select name="group_id" id="group_id">
					{foreach from=$contentPerms.groups item=group}
						<option value="{$group.group_id}">{$group.group_name}</option>
					{/foreach}
				</select>
				{formhelp note=""}
			{/forminput}
		</div>

		<div class="row submit">
			<input type="submit" name="back" value="{tr}Go back to content{/tr}" />
			<input type="submit" name="assign" value="{tr}Assign Permission{/tr}" />
		</div>
	{/form}

	{if $contentPerms.assigned}
		<br />

		<table class="data">
			<caption>{tr}Permissions assigned to this content{/tr}</caption>
			<tr>
				<th>{smartlink content_id=$gContent->mContentId ititle=Group isort=group_name idefault=1}</th>
				<th>{smartlink content_id=$gContent->mContentId ititle=Permission isort=perm_name}</th>
				<th>{tr}Action{/tr}</th>
			</tr>
			{foreach from=$contentPerms.assigned item=perm}
				<tr class="{cycle values="even,odd"}">
					<td>{$perm.group_name}</td>
					<td>{$perm.perm_desc} <em>({$perm.perm_name})</em></td>
					<td class="alignright">
						{smartlink ititle="Remove Permission" ibiticon="icons/edit-delete" iforce="icon" action=remove content_id=$gContent->mContentId perm=$perm.perm_name group_id=$perm.group_id}
					</td>
				</tr>
			{foreachelse}
				<tr class="norecords">
					<td colspan="3">{tr}No individual permissions, global permissions apply{/tr}</td>
				</tr>
			{/foreach}
		</table>
	{/if}

{/if}
{/strip}
