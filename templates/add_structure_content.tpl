{literal}
<script type="text/javascript">/* <![CDATA[ */
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
/* ]]> */</script>
{/literal}

{strip}

<div id="structureaddresult"></div>

<div class="structurecontent">

	<div class="header">
		<h1>{tr}Structure Content{/tr}</h1>
	</div>

	{form legend="Add Content" id="structureaddform"}
		<input type="hidden" name="structure_id" value="{$structureInfo.structure_id}" />
		<input type="hidden" name="tab" value="content" />

		{if $subtree}
			<div class="row">
				{formlabel label="After page" for="after_ref_id"}
				{forminput}
					<select name="after_ref_id" id="after_ref_id">
						{section name=iy loop=$subtree}
							<option value="{$subtree[iy].structure_id}" {if $insert_after eq $subtree[iy].structure_id}selected="selected"{/if}>{$subtree[iy].pos} - {$subtree[iy].title|escape}, {$insert_after}, {$subtree[iy].structure_id}</option>
						{/section}
					</select>
					{formhelp note="Format: Position in tree - Title of Content, insert after, structure_id"}
				{/forminput}
			</div>
		{/if}

		<div class="row">
			{formlabel label="Search" for="lib-content"}
			{forminput}
				<input autocomplete="off" id="contact_name" name="contact[name]" size="30" type="text" value="" />
				<div class="auto_complete" id="contact_name_auto_complete"></div>
				<script type="text/javascript">new Ajax.Autocompleter('contact_name', 'contact_name_auto_complete', '/presentations/foo.php', {ldelim}{rdelim})</script>
				{formhelp note=""}
			{/forminput}
		</div>

		<div class="row">
			{formlabel label="Content type" for="content_type_guid"}
			{forminput}
				{html_options onchange="submit();" options=$contentTypes name=content_type_guid selected=$contentSelect}
			{/forminput}

			{* forminput}
				{html_options multiple="multiple" id="lib-content" size="12" name="content[]" values=$contentList options=$contentList}
			{/forminput *}

			{forminput}
				<table class="data">
					<thead>
						<tr>
							<th></th>
							<th>{tr}Title{/tr}</th>
							<th>{tr}Type{/tr}</th>
							<th>{tr}Author{/tr}</th>
						</tr>
					</thead>
					<tbody>
						{section loop=$contentListHash name=cx}
							<tr class="item {cycle values="even,odd"}" id="{$contentListHash[cx].content_id}li">
								<td>
									<a style="display:none" id="{$contentListHash[cx].content_id}remove" href="#" onclick="submitStructure($('structureaddform'),{$contentListHash[cx].content_id},'remove')">
										{biticon ipackage="icons" iname="list-add" iexplain="Add"}
									</a>
									&nbsp;
									<a id="{$contentListHash[cx].content_id}add" href="#" onclick="submitStructure($('structureaddform'),{$contentListHash[cx].content_id},'add')">
										{biticon ipackage="icons" iname="list-add" iexplain="Add to structure"}
									</a>
									&nbsp;
									<a target="_new" href="{$contentListHash[cx].display_url}">
										{biticon ipackage="icons" iname="zoom-best-fit" iexplain="View (in new window)"}
									</a>
								</td>
								<td class="title">
									{if $contentListHash[cx].thumbnail_url}
										<img class="thumb" src="{$contentListHash[cx].thumbnail_url}" alt="{tr}Thumbnail{/tr}" />
									{/if}
									{$contentListHash[cx].title}
									<span id="{$contentListHash[cx].content_id}feedback"></span>
								</td>
								<td class="description" id="{$contentListHash[cx].content_id}item">
									{$contentListHash[cx].content_description}
								</td>
								<td class="author">
								 	{displayname hash=$contentListHash[cx]}
								</td>
							</tr>
						{/section}
					</tbody>
				</table>
			{/forminput}
		</div>

		<div class="row submit">
			<input type="submit" onclick="submitStructure(this.form);return false;" name="create" value="{tr}Add Content{/tr}" />
			<input type="submit" name="done" value="{tr}Done{/tr}" />
		</div>
	{/form}
</div>
{/strip}
