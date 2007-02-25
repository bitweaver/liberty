{strip}
{formfeedback hash=$feedback}
{if !$structureName}
	{assign var=structureName value="Structure"}
{/if}
{jstabs}
	{jstab title="`$structureName` Organization"}
		{include file="bitpackage:liberty/edit_structure_inc.tpl"}
	{/jstab}
{if !$gBitSystem->isFeatureActive( 'wikibook_edit_add_content' )}
	{jstab title="`$structureName` Content"}
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
