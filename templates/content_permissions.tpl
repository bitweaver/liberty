{strip}

<div class="admin liberty">
	<div class="header">
		<h1>{tr}Assign permissions{/tr}</h1>
	</div>

	<div class="body">
		<h2>{tr}Assign permissions to{/tr}: {$gContent->getTitle()}</h2>

		{if !$contentPerms.assigned}
			{formfeedback warning="No Individual permissions set. Global Permissions apply."}
		{else}
			{smartlink ititle="Clear all custom content permissions" action=expunge content_id=$gContent->mContentId}
		{/if}

		{if count($contentPerms.groups) lt 10}
			<table class="data">
				<caption>{tr}Permissions set for this content item{/tr}</caption>
				<tr>
					<th>{tr}Permission{/tr}</th>
					{foreach from=$contentPerms.groups item=group}
						<th>{$group.group_name}</th>
					{/foreach}
				</tr>

				{foreach from=$contentPerms.assignable key=perm item=permInfo}
					<tr class="{cycle values="odd,even"}">
						<td>{$permInfo.perm_desc}<br /><em>({$permInfo.perm_name})</em></td>
						{foreach from=$contentPerms.groups key=groupId item=groupInfo}
							{assign var=icon value="icons/media-playback-stop"}                {* default icon *}
							{assign var=action value="assign"}                                 {* default action *}
							{if $groupInfo.perms.$perm}                                        {* global active permissions *}
								{assign var=icon value="icons/dialog-ok"}                      {* default active permission icon *}
								{if $contentPerms.assigned.$groupId.$perm}
									{assign var=icon value="icons/list-add"}                   {* custon permission icon *}
									{assign var=action value="remove"}                         {* remove permission if we have a custom one *}
								{/if}
								{if $contentPerms.assigned.$groupId.$perm.is_revoked}
									{assign var=icon value="icons/list-remove"}                {* is_revoked icon *}
								{/if}
							{/if}
							<td style="text-align:center">{smartlink itra=false ititle=$perm ibiticon=$icon action=$action content_id=$gContent->mContentId perm=$perm group_id=$groupId}</td>
						{/foreach}
					</tr>
				{/foreach}
			</table>

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
				<table class="data">
					<caption>{tr}Custom permissions assigned to this content{/tr}</caption>
					<tr>
						<th>{tr}Group{/tr}</th>
						<th>{tr}Permission{/tr}</th>
						<th>{tr}Action{/tr}</th>
					</tr>
					{foreach from=$contentPerms.assigned key=groupId item=groupInfo}
						{foreach from=$groupInfo key=perm item=permInfo name=fgroup}
							<tr class="{cycle values="even,odd"}">
								{if $smarty.foreach.fgroup.first}
									<td rowspan="{$smarty.foreach.fgroup.total}">{$permInfo.group_name}</td>
								{/if}
								<td>
									{if $contentPerms.assigned.$groupId.$perm.is_revoked}
										{biticon iname=list-remove iexplain="Removed Permission"}
									{else}
										{biticon iname=list-add iexplain="Added Permission"}
									{/if} {$permInfo.perm_desc} <em>({$permInfo.perm_name})</em>
								</td>
								<td align="right">
									{smartlink ititle="Remove Permission" ibiticon="icons/edit-delete" action=remove content_id=$gContent->mContentId perm=$perm group_id=$groupId}
								</td>
							</tr>
						{/foreach}
					{foreachelse}
						<tr class="norecords">
							<td colspan="3">{tr}No individual permissions, global permissions apply{/tr}</td>
						</tr>
					{/foreach}
				</table>
			{/if}

			<h2 >{tr}Default Permissions{/tr}</h2>
			<ul>
				{foreach from=$contentPerms.groups key=groupId item=groupInfo}
					<li>{$groupInfo.group_name}
						<ul>
							{foreach from=$groupInfo.perms key=perm item=permInfo}
								{if $contentPerms.assignable.$perm}
									<li>{$permInfo.perm_desc} <em>({$permInfo.perm_name})</em></li>
								{/if}
							{/foreach}
						</ul>
					</li>
				{/foreach}
			</ul>
		{/if}
	</div><!-- end .body -->
</div><!-- end .liberty -->

{/strip}
