
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
				<input type="text" name="find_objects" /> 
				{formhelp note=""}
			{/forminput}
		</div>

		<div class="row">
			{forminput}
				{html_options onchange="submit();" options=$contentTypes name=content_type_guid selected=$contentSelect}
			{/forminput}

			{forminput}
				{html_options multiple="multiple" id="lib-content" size="12" name="content[]" values=$contentList options=$contentList}
			{/forminput}
		</div>

		<div id=foosubmit  onclick="submitStructureAdd($('structureaddform'));">foo submit</div>

		<div class="row submit">
			<input type="submit" onclick="submitStructureAdd(this.form);return false;" name="create" value="{tr}Add Content{/tr}" />
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
