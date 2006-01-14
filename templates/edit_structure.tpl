{strip}
{formfeedback hash=$feedback}
{jstabs}
	{jstab title="Edit Structure"}
		{include file="bitpackage:liberty/edit_structure_inc.tpl"}
	{/jstab}
{if !$gBitSystem->isFeatureActive( 'wikibook_edit_add_content' )}
	{jstab title="Structure Content"}
		{include file="bitpackage:liberty/edit_structure_content.tpl"}
	{/jstab}
{/if}
{*	removing alias stuff until we know what to do with it - XING
	{jstab title="Update Alias"}
		{include file="bitpackage:liberty/edit_structure_alias.tpl"}
	{/jstab}
*}
{/jstabs}
{/strip}
