{strip}
<div class="structurecontent">
	{form legend="Add Content"}
		<input type="hidden" name="structure_id" value="{$structureInfo.structure_id}" />
		<input type="hidden" name="tab" value="content" />

		{if $subpages}
			<div class="row">
				{formlabel label="After page" for="after_ref_id"}
				{forminput}
					<select name="after_ref_id" id="after_ref_id">
						{section name=iy loop=$subpages}
							<option value="{$subpages[iy].structure_id}" {if $insert_after eq $subpages[iy].structure_id}selected="selected"{/if}>{$subpages[iy].title}</option>
						{/section}
					</select>
					{formhelp note=""}
				{/forminput}
			</div>
		{/if}

		<div class="row">
			{formlabel label="Add" for="content_type"}
			{forminput}
				<select name="content_type" id="content_type" onchange="submit();">
					<option {if !$contentSelect}selected="selected"{/if} value="">All Content</option>
					{foreach from=$contentTypes key=guid item=description}
						<option value="{$guid}" {if $contentSelect == $guid}selected="selected"{assign var=selectDescription value=$description}{/if}>{$description}</option>
					{/foreach}
				</select>
			{/forminput}

			{forminput}
				<select name="content[]" multiple="multiple" size="8">
					{section name=list loop=$listContent}
						{assign var=guid value=$listContent[list].content_type_guid}
						<option value="{$listContent[list].content_id}">{$contentTypes.$guid} {$listContent[list].content_id}{if $listContent[list]}: "{$listContent[list].title|truncate:40:"(...)":true}"{/if}</option>
					{sectionelse}
						<option disabled="disabled">{tr}No {$selectDescription} content found{/tr}</option>
					{/section}
				</select>
			{/forminput}

			{forminput}
				<input type="text" name="find_objects" /> 
				<input type="submit" value="{tr}filter{/tr}" name="search_objects" />
				{formhelp note=""}
			{/forminput}
		</div>

		<div class="row submit">
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
						<a href="{$PHP_SELF}?structure_id={$subtree[ix].structure_id}">{$subtree[ix].title}</a>
					{if $structureInfo.structure_id eq $subtree[ix].structure_id}</div>{/if}
				{else}
					{if $subtree[ix].first}<ul>{else}</li>{/if}
					{if $subtree[ix].last}</ul>{else}
						<li>
							{if $structureInfo.structure_id eq $subtree[ix].structure_id}<div class="highlight">{/if}
								<strong>{$subtree[ix].pos}</strong>&nbsp;
								<a href="{$PHP_SELF}?structure_id={$subtree[ix].structure_id}">{$subtree[ix].title}</a>
							{if $structureInfo.structure_id eq $subtree[ix].structure_id}</div>{/if}
					{/if}
				{/if}
			{/section}
		</li>
	</ul><!-- end outermost .toc -->
</div>
<div class="clear"></div>
{/strip}
