{literal}
<script type="text/javascript">
function addStructure(pContentId) {
	var data = $("#structureaddform").serialize()+"&content[]="+pContentId+"&ajax_xml=1&action=add";
	var ajax = new BitBase.SimpleAjax();
	var donefn = function (r) {
		var responseHash = BitBase.evalJSON( r.responseText );

		var row = document.getElementById( responseHash.content_id+"feedback" );
		row.innerHTML = responseHash.feedback;

		BitBase.showById( responseHash.content_id+"remove" );
		BitBase.fade( responseHash.content_id+"add" );
	};
	ajax.connect( "{/literal}{$smarty.const.LIBERTY_PKG_URL}structure_add_content.php{literal}", data, donefn, "GET" );
	return false;
}

</script>
{/literal}

{strip}

<div id="structureaddresult"></div>

<div class="edit">

	<div class="header">
		<h1>{$gContent->getTitle()|escape} {tr}Table of Contents{/tr}</h1>
	</div>

	{form legend="Add Content" id="structureaddform"}
		<input type="hidden" name="structure_id" value="{$structureInfo.structure_id}" />
		<input type="hidden" name="tab" value="content" />

		<div class="row">
		{if $subtree}
			<div class="col-sm-4">
			<div class="form-group">
				{formlabel label="After page" for="after_ref_id"}
				{forminput}
					<select class="form-control" name="after_ref_id" id="after_ref_id">
						{section name=iy loop=$subtree}
							<option value="{$subtree[iy].structure_id}" {if $insert_after eq $subtree[iy].structure_id}selected="selected"{/if}>{$subtree[iy].pos} - {$subtree[iy].title|escape}</option>
						{/section}
					</select>
					{formhelp note="Format: Position in tree - Title of Content, insert after, structure_id"}
				{/forminput}
			</div>
			</div>
		{/if}
			<div class="col-sm-3">
		{minifind}

		{* disable until it can be sorted }
		<div class="form-group">
			{formlabel label="Search" for="lib-content"}
			{forminput}
				<input class="form-control" autocomplete="off" id="contact_name" name="contact[name]" type="text" value="" />
				<div class="auto_complete" id="contact_name_auto_complete"></div>
				<script type="text/javascript">new Ajax.Autocompleter('contact_name', 'contact_name_auto_complete', '/presentations/foo.php', {ldelim}{rdelim})</script>
				{formhelp note=""}
			{/forminput}
		</div>
		{ *}
			</div>
			<div class="col-sm-3">
				<div class="form-group">
					{formlabel label="Content Type" for="content_type_guid"}
					{forminput}
						{html_options class="form-control" onchange="submit();" options=$contentTypes name=content_type_guid selected=$contentSelect}
					{/forminput}

					{* forminput}
						{html_options class="form-control" multiple="multiple" id="lib-content" size="12" name="content[]" values=$contentList options=$contentList}
					{/forminput *}
				</div>
			</div>
			<div class="col-sm-1">
				<a class="btn btn-primary" href="{$smarty.const.BIT_ROOT_URL}index.php?structure_id={$gStructure->mStructureId}">Done</a>
			</div>
		</div>

		<table class="table data">
		{foreach from=$contentListHash item=contentHash}
			{assign var=inStructureId value=$gStructure->isInStructure($contentHash.content_id)}
			<tr>
				<td class="text-center">{if $contentHash.thumbnail_url}<img class="img-responsive" src="{$contentHash.thumbnail_url}" alt="{tr}Thumbnail{/tr}" />{/if}</td>
				<td class="item {cycle values="even,odd"}" id="{$contentHash.content_id}li">

					<div class="title">
						{$contentHash.title}
						<div class="help-block">
						<a target="_new" href="{$contentHash.display_url}">
							{booticon ipackage="icons" iname="icon-zoom-in" iexplain="View (in new window)"}
						</a>
{$contentHash.content_name}</div>
					</div>
				</td>
				<td>
					<div class="author">
						{displayname hash=$contentHash}
					</div>
				</td>
				<td>
					{if $inStructureId}
					<div class="icon" {if empty($inStructureId)}style="display:none"{/if} id="{$contentHash.content_id}remove" onclick="removeStructure({$inStructureId})">
						<button class="btn btn-default btn-xs" title="Remove from structure">{tr}Remove{/tr}</button>
					</div>
					{else}
					<div class="icon" id="{$contentHash.content_id}add" onclick="addStructure({$contentHash.content_id})">
						<button class="btn btn-default btn-xs" title="Add to structure">{tr}Add{/tr}</button>
					</div>
					{/if} 
				</td>
			</tr>
		{/foreach}
		</table>

		<div class="form-group submit">
			<input type="submit" class="btn btn-default" onclick="submitStructure(this.form);return false;" name="create" value="{tr}Add Content{/tr}" />
			<input type="submit" class="btn btn-default" name="done" value="{tr}Done{/tr}" />
		</div>
	{/form}
</div>
{/strip}
