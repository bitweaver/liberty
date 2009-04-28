{strip}
<div class="listing liberty">
	<div class="header">
		<h1>
			{foreach from=$contentSelect item=item name=loop}
				{tr}{$contentTypes[$item]}{/tr}
				{if !$smarty.foreach.loop.last},&nbsp;{/if}
			{foreachelse}
				{tr}Content{/tr}
			{/foreach}
			&nbsp;{tr}Listing{/tr}
		</h1>
		{if $smarty.request.user_id}
			{tr}User{/tr}: {displayname user_id=$smarty.request.user_id}
		{/if}
	</div>

	<div class="body">
		{include file="bitpackage:liberty/list_content_inc.tpl"}
	</div><!-- end .body -->
</div><!-- end .liberty -->
{/strip}
