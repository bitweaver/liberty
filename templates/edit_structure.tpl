{strip}
{formfeedback hash=$feedback}
{if !$structureName}
	{assign var=structureName value="Structure"}
{/if}

{if $gBitThemes->mAjax=='mochikit'}

<div class="row">
	<div class="formlabel">
		{$gContent->getContentTypeDescription()} {tr}Structure{/tr}
<br/><a href="{$smarty.const.LIBERTY_PKG_URL}add_structure_content.php?structure_id={$smarty.request.structure_id}&amp;content_type_guid={$smarty.request.content_type_guid}" title="Add Content to {$gContent->getTitle()}" title="Add Content">Add Content</a>
	</div>
	{forminput}
		{include file="bitpackage:liberty/edit_structure_inc.tpl"}
	{/forminput}
</div>

{else}

{jstabs}
	{jstab title="`$structureName` Organization"}
		{include file="bitpackage:liberty/edit_structure_inc.tpl"}
	{/jstab}
{if !$gBitSystem->isFeatureActive( 'wikibook_edit_add_content' )}
	{jstab title="`$structureName` Content"}
		{include file="bitpackage:liberty/edit_structure_content.tpl"}
	{/jstab}
{/if}
{/jstabs}

{/if}

{/strip}
