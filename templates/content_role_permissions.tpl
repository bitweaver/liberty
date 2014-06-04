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

		{if count($contentPerms.roles) <= 10}
			{assign var=size value="large/"}
		{/if}

		<table class="table data">
			<caption>{tr}Permissions set for this content item{/tr}</caption>
			<tr>
				<th>{tr}Permission{/tr}</th>
				{foreach from=$contentPerms.roles item=role}
				<th onmouseover="BitBase.showById('f{$role.role_id}');BitBase.hideById('a{$role.role_id}')">
					<abbr id="a{$role.role_id}" title="{$role.role_name}">{if count($contentPerms.roles) gt 10}{$role.role_name|truncate:4:false}{else}{$role.role_name}{/if}</abbr>
					<span id="f{$role.role_id}" style="display:none">{$role.role_name}</span>
				</th>
				{/foreach}
			</tr>

			{foreach from=$contentPerms.assignable key=perm item=permInfo name=perms}
				<tr class="{cycle values="odd,even"}">
					<td>{$permInfo.perm_desc}{if $gBitUser->isAdmin()}<br /><em>({$permInfo.perm_name})</em>{/if}</td>
					{foreach from=$contentPerms.roles key=roleId item=roleInfo}
						{assign var=icon value="media-playback-stop"}                      {* default icon *}
						{assign var=action value="assign"}                                 {* default action *}
						{if $roleInfo.perms.$perm}                                        {* global active permissions *}
							{assign var=icon value="dialog-ok"}                            {* default active permission icon *}
							{if $contentPerms.assigned.$roleId.$perm.is_revoked}
								{assign var=icon value="list-remove"}                      {* is_revoked icon *}
								{assign var=action value="remove"}                         {* remove permission if we have a custom one *}
							{elseif $contentPerms.assigned.$roleId.$perm}
								{assign var=icon value="list-add"}                         {* custon permission icon *}
								{assign var=action value="negate"}                         {* remove permission if we have a custom one *}
							{/if}
						{/if}

						<td style="text-align:center">
							{if $gBitThemes->isJavascriptEnabled()}
								<span id="{$perm}{$roleId}">
									<a title="{$contentPerms.roles.$roleId.role_name} :: {$perm}" href="javascript:void(0);" onclick="BitAjax.updater('{$perm}{$roleId}', '{$smarty.const.LIBERTY_PKG_URL}content_role_permissions.php', 'action={$action}&amp;content_id={$gContent->mContentId}&amp;perm={$perm}&amp;role_id={$roleId}')">
										{biticon iname="$size$icon" iexplain="$icon"}
									</a>
								</span>
							{else}
								{smartlink itra=false ititle="$contentPerms.roles.$roleId.role_name :: $perm" ibiticon="$size$icon" action=$action content_id=$gContent->mContentId perm=$perm role_id=$roleId}
							{/if}
						</td>
					{/foreach}
				</tr>
			{/foreach}
		</table>

		<h2>{tr}Legend{/tr}</h2>
		
		<dl>
			<dt>{booticon iname="icon-plus-sign"   iexplain=""} Custom Permission: Always Allow</dt>
			<dt>{booticon iname="icon-minus-sign"   iexplain=""} Custom Permission: Always Deny</dt>
			<dt>{booticon iname="icon-ok"   iexplain=""} Global Permission: Allow</dt>
			<dt>{biticon iname="media-playback-stop" iexplain=""} Global Permission: Deny</dt>
		</dl>
	</div><!-- end .body -->
</div><!-- end .liberty -->
