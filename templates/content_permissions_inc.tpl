<h2>{tr}Assign permissions to{/tr}: {$gContent->getTitle()}</h2>

{form legend="Content Permissions"}
	<input type="hidden" name="content_id" value="{$gContent->mContentId}" />

	<div class="row">
		{formlabel label="Assign this Permission" for="perm"}
		{forminput}
			<select name="perm" id="perm">
				{foreach from=$assignPerms item=perm}
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
				{foreach from=$userGroups item=group}
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

<br />

<table class="data">
	<caption>{tr}Permissions assigned to this content{/tr}</caption>
	<tr>
		<th>{tr}Group{/tr}</th>
		<th>{tr}Permission{/tr}</th>
		<th>{tr}Action{/tr}</th>
	</tr>
	{foreach from=$gContent->mPerms item=perm}
		<tr class="{cycle values="even,odd"}">
			<td>{$perm.group_name}</td>
			<td>
				{$perm.perm_name}
				<br />
				{$perm.perm_desc}
			</td>
			<td align="right">
				{smartlink ititle="Remove Permission" ibiticon="liberty/delete" action=remove content_id=$gContent->mContentId perm=$perm.perm_name group_id=$perm.group_id}
			</td>
		</tr>
	{foreachelse}
		<tr class="norecords">
			<td colspan="3">{tr}No individual permissions, global permissions apply{/tr}</td>
		</tr>
	{/foreach}
</table>

{* probably not needed - xing
<br /><hr /><br />

<h2>{tr}Permission explanation{/tr}</h2>
{foreach from=$assignPerms item=perm}
	<dl class="help">
		<dt>{$perm.perm_name}</dt>
		<dd>{$perm.perm_desc}</dd>
	</dl>
{/foreach}
*}
