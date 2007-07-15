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
			{assign var=size value="large/"}
		{/if}

		<table class="data">
			<caption>{tr}Permissions set for this content item{/tr}</caption>
			{capture assign=th}
				<tr>
					<th>{tr}Permission{/tr}</th>
					{foreach from=$contentPerms.groups item=group}
					<th onmouseover="showById('f{$group.group_id}');hideById('a{$group.group_id}')">
						<abbr id="a{$group.group_id}" title="{$group.group_name}">{if count($contentPerms.groups) gt 10}{$group.group_name|truncate:4:false}{else}{$group.group_name}{/if}</abbr>
						<span id="f{$group.group_id}" style="display:none">{$group.group_name}</span>
					</th>
					{/foreach}
				</tr>
			{/capture}
			{$th}

			{foreach from=$contentPerms.assignable key=perm item=permInfo name=perms}
			{*
				{if ($smarty.foreach.perms.iteration % 10) eq 0 and ($smarty.foreach.perms.total - $smarty.foreach.perms.iteration) gt 5}{$th}{/if}
				*}
				<tr class="{cycle values="odd,even"}">
					<td>{$permInfo.perm_desc}<br /><em>({$permInfo.perm_name})</em></td>
					{foreach from=$contentPerms.groups key=groupId item=groupInfo}
						{assign var=icon value="media-playback-stop"}                      {* default icon *}
						{assign var=action value="assign"}                                 {* default action *}
						{if $groupInfo.perms.$perm}                                        {* global active permissions *}
							{assign var=icon value="dialog-ok"}                            {* default active permission icon *}
							{if $contentPerms.assigned.$groupId.$perm}
								{assign var=icon value="list-add"}                         {* custon permission icon *}
								{assign var=action value="remove"}                         {* remove permission if we have a custom one *}
							{/if}
							{if $contentPerms.assigned.$groupId.$perm.is_revoked}
								{assign var=icon value="list-remove"}                      {* is_revoked icon *}
							{/if}
						{/if}
						<td style="text-align:center">
							{smartlink itra=false ititle="`$contentPerms.groups.$groupId.group_name` :: $perm" ibiticon=icons/$size$icon action=$action content_id=$gContent->mContentId perm=$perm group_id=$groupId}
						</td>
					{/foreach}
				</tr>
			{/foreach}
		</table>

		{if count($contentPerms.groups) gt 10}
			<h2>{tr}Legend{/tr}</h2>
			<dl>
				{foreach from=$contentPerms.groups item=group}
					<dt>{$group.group_id}</dt>
					<dd>{$group.group_name}: {$group.group_desc}</dd>
				{/foreach}
			</dl>
		{/if}
	</div><!-- end .body -->
</div><!-- end .liberty -->

{/strip}
