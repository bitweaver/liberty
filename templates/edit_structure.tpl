{strip}
{formfeedback hash=$feedback}
{if !$structureName}
	{assign var=structureName value="Structure"}
{/if}

{literal}

{*
<input autocomplete="off" id="contact_name" name="contact[name]" size="30" type="text" value="" />
<div class="auto_complete" id="contact_name_auto_complete"></div>
<script type="text/javascript">new Ajax.Autocompleter('contact_name', 'contact_name_auto_complete', '/presentations/foo.php', {})</script>
*}

{/literal}

{if $gBitSystem->mAjax=='mochikit'}
{literal}
<script type="text/javascript">//<![CDATA[
function submitStructureAdd(pForm) {
	var req = getXMLHttpRequest();
	req.open("POST", {/literal}'{$smarty.const.LIBERTY_PKG_URL}add_structure_content.php'{literal}, true);
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	var data = queryString(pForm)+"&ajax_xml=1";
	var d = sendXMLHttpRequest(req, data);
	d.addBoth( structureAddResult );
	return false;
}

var structureAddResult = function (response) {
	$("structureaddresult").innerHTML = response.responseText;
};

//]]></script>
{/literal}

<div class="row">
	<div class="formlabel">
		{$gContent->getContentTypeDescription()} {tr}Structure{/tr}
	</div>
	{forminput}
<a href="{$smarty.const.LIBERTY_PKG_URL}add_structure_content.php?structure_id={$smarty.request.structure_id}&amp;height=600&amp;width=600&amp;modal=true" title="Add Content to {$gContent->getTitle()}" class="thickbox" title="Add Content">Add Content</a>
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
