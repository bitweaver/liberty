{literal}
<script type="text/javascript">//<![CDATA[
function submitStructure(pForm,pContentId,pMode) {
	var req = getXMLHttpRequest();
	req.open("POST", {/literal}'{$smarty.const.LIBERTY_PKG_URL}add_structure_content.php'{literal}, true);
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	var data = queryString(pForm)+"&content[]="+pContentId+"&ajax_xml=1";
	var d = sendXMLHttpRequest(req, data);
	d.addBoth( structureAddResult );
	return false;
}

var structureAddResult = function (response) {
	responseHash = MochiKit.Async.evalJSONRequest(response);
	MochiKit.Visual.switchOff($(responseHash.content_id+"item"));
	MochiKit.Visual.fade($(responseHash.content_id+"add"));
	$(responseHash.content_id+"feedback").innerHTML = responseHash.feedback;
};

//]]></script>
{/literal}

{strip}

<div id="structureaddresult"></div>

<div class="structurecontent">
	{form legend="Add Content" id="structureaddform"}
		<input type="hidden" name="structure_id" value="{$structureInfo.structure_id}" />
		<input type="hidden" name="tab" value="content" />

		{if $subtree}
			<div class="row">
				{formlabel label="After page" for="after_ref_id"}
				{forminput}
					<select name="after_ref_id" id="after_ref_id">
						{section name=iy loop=$subtree}
							<option value="{$subtree[iy].structure_id}" {if $insert_after eq $subtree[iy].structure_id}selected="selected"{/if}>{$subtree[iy].pos} - {$subtree[iy].title|escape} {$insert_after} {$subtree[iy].structure_id}</option>
						{/section}
					</select>
					{formhelp note=""}
				{/forminput}
			</div>
		{/if}

		<div class="row">
			{formlabel label="Search" for="lib-content"}
			{forminput}
{literal}

<input autocomplete="off" id="contact_name" name="contact[name]" size="30" type="text" value="" />
<div class="auto_complete" id="contact_name_auto_complete"></div>
<script type="text/javascript">new Ajax.Autocompleter('contact_name', 'contact_name_auto_complete', '/presentations/foo.php', {})</script>

{/literal}

				{formhelp note=""}
			{/forminput}
		</div>

		<div class="row">
			{forminput}
				{html_options onchange="submit();" options=$contentTypes name=content_type_guid selected=$contentSelect}
			{/forminput}

			{* forminput}
				{html_options multiple="multiple" id="lib-content" size="12" name="content[]" values=$contentList options=$contentList}
			{/forminput *}

			{forminput}

			<ol class="data" start="{$smarty.request.offset|default:1}">
			{section loop=$contentListHash name=cx}
				<li class="item {cycle values="even,odd"}" id="{$contentListHash[cx].content_id}li">
	<div class="floaticon">
		<a style="display:none" id="{$contentListHash[cx].content_id}remove" href="#" onclick="submitStructure($('structureaddform'),{$contentListHash[cx].content_id},'remove')">{biticon ipackage="icons" iname="list-add" iexplain="Add"}</a>
		<a id="{$contentListHash[cx].content_id}add" href="#" onclick="submitStructure($('structureaddform'),{$contentListHash[cx].content_id},'add')">{biticon ipackage="icons" iname="list-add" iexplain="Add"}</a>
		<a target="_new" href="{$contentListHash[cx].display_url}">{biticon ipackage="icons" iname="zoom-best-fit" iexplain="View"}</a>
	</div>
	<h2>{$contentListHash[cx].title}</h2>
<div id="{$contentListHash[cx].content_id}item">
	{$contentListHash[cx].content_description} {tr}by{/tr} {displayname hash=$contentListHash[cx]}
{if $contentListHash[cx].thumbnail_url}<div><img src="{$contentListHash[cx].thumbnail_url}" class="thumb" /></div>{/if}
</div>
		<span id="{$contentListHash[cx].content_id}feedback"></span>
				</li>
			{/section}
			</ol>

			{/forminput}
		</div>

		<div class="row submit">
			<input type="submit" onclick="submitStructure(this.form);return false;" name="create" value="{tr}Add Content{/tr}" />
			<input type="submit" name="done" value="{tr}Done{/tr}" />
		</div>
	{/form}
</div>

<div class="structuretoc">
	<ul class="toc">
		<li>
		</li>
	</ul><!-- end outermost .toc -->
</div>
<div class="clear"></div>
{/strip}
