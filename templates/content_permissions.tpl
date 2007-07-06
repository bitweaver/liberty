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
			{smartlink ititle="Remove all custom content permissions" action=expunge content_id=$gContent->mContentId}
		{/if}

		<table class="data">
			<tr>
				<th>{tr}Permission{/tr}</th>
				{foreach from=$contentPerms.groups item=group}
					<th>{$group.group_name}</th>
				{/foreach}
			</tr>

			{foreach from=$contentPerms.assignable key=perm item=permInfo}
				<tr class="{cycle values="odd,even"}">
					<td>{$permInfo.perm_desc}<br /><em>({$permInfo.perm_name})</em></td>
					{foreach from=$contentPerms.groups key=group_id item=groupInfo}
						{assign var=icon value="icons/media-playback-stop"}                {* default icon *}
						{assign var=action value="assign"}                                 {* default action *}
						{if $groupInfo.perms.$perm}                                        {* global active permissions *}
							{assign var=icon value="icons/dialog-ok"}                      {* default active permission icon *}
							{if $contentPerms.assigned.$group_id.$perm}
								{assign var=icon value="icons/list-add"}                   {* custon permission icon *}
								{assign var=action value="remove"}                         {* remove permission if we have a custom one *}
							{/if}
							{if $contentPerms.assigned.$group_id.$perm.is_excluded}
								{assign var=icon value="icons/list-remove"}                {* is_excluded icon *}
							{/if}
						{/if}
						<td style="text-align:center">{smartlink itra=false ititle=$perm ibiticon=$icon action=$action content_id=$gContent->mContentId perm=$perm group_id=$group_id}</td>
					{/foreach}
				</tr>
			{/foreach}
		</table>
	</div><!-- end .body -->
</div><!-- end .liberty -->

{/strip}
