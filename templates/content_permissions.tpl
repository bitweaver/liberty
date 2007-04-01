{strip}

<div class="admin permission">
	<div class="header">
		<h1>{tr}Assign permissions{/tr}</h1>
	</div>

	<div class="body">
		<h2>{tr}Assign permissions to{/tr}: {$gContent->getTitle()}</h2>

		{if !$contentPerms.assigned}
			{formhelp warning="No Individual permissions set. Global Permissions apply."}
		{/if}

		{if count($contentPerms.groups) lt 8}

			<table class="data">
				<tr>
					<th>{tr}Permission{/tr}</th>
					{foreach from=$contentPerms.groups item=group}
						<th>{$group.group_name}</th>
					{/foreach}
				</tr>
				{foreach from=$contentPerms.assignable item=perm}
					<tr class="{cycle values="odd,even"}">
						<td>{$perm.perm_desc}<br /><em>({$perm.perm_name})</em></td>
						{foreach from=$contentPerms.groups item=group}
							{assign var=icon value="icons/media-playback-stop"}
							{assign var=action value="assign"}
							{foreach from=$contentPerms.assigned item=ass}
								{if $ass.group_id == $group.group_id and $ass.perm_name == $perm.perm_name}
									{assign var=icon value="icons/dialog-ok"}
									{assign var=action value="remove"}
								{/if}
							{/foreach}
							<td style="text-align:center">{smartlink itra=false ititle=$perm.perm_name ibiticon=$icon action=$action content_id=$gContent->mContentId perm=$perm.perm_name group_id=$group.group_id}</td>
						{/foreach}
					</tr>
				{/foreach}
			</table>
			<br /><hr /><br />

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
							<td align="right">
								{smartlink ititle="Remove Permission" ibiticon="icons/edit-delete" action=remove content_id=$gContent->mContentId perm=$perm.perm_name group_id=$perm.group_id}
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
	</div><!-- end .body -->
</div><!-- end .permission -->

{/strip}
