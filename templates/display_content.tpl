{strip}
<div class="display content">
	{* We want the close click always...*}
	{if !$preview}
		<div class="floaticon">
			{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='icon' serviceHash=$gContent->mInfo}
		</div><!-- end .floaticon -->
	{/if}
{if $gContent->isValid()}
	{if !$preview}
		<div class="header">
			<h1>{$gContent->mInfo.title|default:"No Title"}</h1>
			{* creator_user not yet forced by liberty so check. *}
			{if !$preview && !empty($gContent->mInfo.creator_user)}
				<div class="date">
					{tr}Created by {displayname user=$gContent->mInfo.creator_user user_id=$gContent->mInfo.creator_user_id real_name=$gContent->mInfo.creator_real_name}, Last modification by {displayname user=$gContent->mInfo.modifier_user user_id=$gContent->mInfo.modifier_user_id real_name=$gContent->mInfo.modifier_real_name} on {$gContent->mInfo.last_modified|bit_long_datetime}{/tr}
				</div>
			{/if}
		</div><!-- end .header -->
	{/if}

	<div class="body">
		{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='body' serviceHash=$gContent->mInfo}
		{if !empty($gContent->mInfo.parsed_data)}
			<div class="content">
				{$gContent->mInfo.parsed_data}
			</div><!-- end .content -->
		{/if}
	</div><!-- end .body -->
{if !$preview}
{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='view' serviceHash=$gContent->mInfo}
{/if}
{else}
	<div class=error>No such Content.</div>
{/if}
</div>
{/strip}
