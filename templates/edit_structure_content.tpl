{strip}

<div class="header">
	<h1>{tr}Structure Content{/tr}</h1>
</div>

<div class="structurecontent">
	{form legend="Add Content"}
		<input type="hidden" name="structure_id" value="{$structureInfo.structure_id}" />
		<input type="hidden" name="tab" value="content" />

		{if $subpages}
			<div class="control-group">
				{formlabel label="After page" for="after_ref_id"}
				{forminput}
					<select name="after_ref_id" id="after_ref_id">
						{section name=iy loop=$subpages}
							<option value="{$subpages[iy].structure_id}" {if $insert_after eq $subpages[iy].structure_id}selected="selected"{/if}>{$subpages[iy].title|escape}</option>
						{/section}
					</select>
					{formhelp note=""}
				{/forminput}
			</div>
		{/if}

		<div class="control-group">
			{formlabel label="Content" for="lib-content"}
			{forminput}
				{html_options onchange="submit();" options=$contentTypes name=content_type_guid selected=$contentSelect}
			{/forminput}

			{forminput}
				{html_options multiple="multiple" id="lib-content" size="12" name="content[]" values=$contentList options=$contentList}
			{/forminput}

			{forminput}
				<input type="text" name="find" /> 
				<input type="submit" value="{tr}Apply filter{/tr}" name="search_objects" />
				{formhelp note=""}
			{/forminput}
		</div>

		<div class="control-group submit">
			<input type="submit" name="create" value="{tr}Add Content{/tr}" />
		</div>
	{/form}
</div>

<div class="structuretoc">
	<ul class="toc">
		<li>
			{section name=ix loop=$subtree}
				{if $subtree[ix].pos eq ''}
					{if $structureInfo.structure_id eq $subtree[ix].structure_id}<div class="highlight">{/if}
						<a href="{$smarty.server.SCRIPT_NAME}?structure_id={$subtree[ix].structure_id}">{$subtree[ix].title|escape}</a>
					{if $structureInfo.structure_id eq $subtree[ix].structure_id}</div>{/if}
				{else}
					{if $subtree[ix].first}<ul>{else}</li>{/if}
					{if $subtree[ix].last}</ul>{else}
						<li>
							{if $structureInfo.structure_id eq $subtree[ix].structure_id}<div class="highlight">{/if}
								<strong>{$subtree[ix].pos}</strong>&nbsp;
								<a href="{$smarty.server.SCRIPT_NAME}?structure_id={$subtree[ix].structure_id}">{$subtree[ix].title|escape}</a>
							{if $structureInfo.structure_id eq $subtree[ix].structure_id}</div>{/if}
					{/if}
				{/if}
			{/section}
		</li>
	</ul><!-- end outermost .toc -->
</div>
<div class="clear"></div>
{/strip}
