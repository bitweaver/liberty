<script type="text/javascript">/*<![CDATA[*/ show_spinner('spinner'); /*]]>*/</script>
<div class="admin liberty">
	<div class="header">
		<h1>{tr}Assign permissions{/tr}</h1>
	</div>

	<div class="body">
		<h2>{tr}Assign permissions to{/tr}: {$gContent->getTitle()}</h2>

		{if !$contentPerms.assigned}
			{formfeedback warning="No Individual permissions set. Global Permissions apply."}
		{/if}

		{if $contentPerms.assigned || $gBitThemes->isJavascriptEnabled()}
			{smartlink ititle="Clear all custom content permissions" action=expunge content_id=$gContent->mContentId}
		{/if}

		{if count($contentPerms.groups) <= 10}
			{assign var=size value="large/"}
		{/if}

		<table class="data">
			<caption>{tr}Permissions set for this content item{/tr}</caption>
			<tr>
				<th>{tr}Permission{/tr}</th>
				{foreach from=$contentPerms.groups item=group}
				<th onmouseover="showById('f{$group.group_id}');hideById('a{$group.group_id}')">
					<abbr id="a{$group.group_id}" title="{$group.group_name}">{if count($contentPerms.groups) gt 10}{$group.group_name|truncate:4:false}{else}{$group.group_name}{/if}</abbr>
					<span id="f{$group.group_id}" style="display:none">{$group.group_name}</span>
				</th>
				{/foreach}
			</tr>

			{foreach from=$contentPerms.assignable key=perm item=permInfo name=perms}
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
							{if $gBitThemes->isJavascriptEnabled()}
								<span id="{$perm}{$groupId}">
									<a title="{$contentPerms.groups.$groupId.group_name} :: {$perm}" href="javascript:ajax_updater('{$perm}{$groupId}', '{$smarty.const.LIBERTY_PKG_URL}content_permissions.php', 'action={$action}&amp;content_id={$gContent->mContentId}&amp;perm={$perm}&amp;group_id={$groupId}')">
										{biticon iname=$size$icon iexplain=""}
									</a>
								</span>
							{else}
								{smartlink itra=false ititle="`$contentPerms.groups.$groupId.group_name` :: $perm" ibiticon=icons/$size$icon action=$action content_id=$gContent->mContentId perm=$perm group_id=$groupId}
							{/if}
						</td>
					{/foreach}
				</tr>
			{/foreach}
		</table>

		{if count($contentPerms.groups) gt 10}
			<h2>{tr}Legend{/tr}</h2>
			<dl>
				{foreach from=$contentPerms.groups item=group}
					<dt>{$group.group_name|truncate:4:false}</dt>
					<dd>{$group.group_name}: {$group.group_desc}</dd>
				{/foreach}
			</dl>
		{/if}
	</div><!-- end .body -->
</div><!-- end .liberty -->
