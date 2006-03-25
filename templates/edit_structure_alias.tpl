{strip}
<div>
	{form legend="Update Page Alias"}
		<input type="hidden" name="structure_id" value="{$structureInfo.structure_id}" />
		<input type="hidden" name="tab" value="alias" />

		<div class="row">
			{formlabel label="Page Alias" for="pageAlias"}
			{forminput}
				<input type="text" name="pageAlias" id="pageAlias" value="{$structureInfo.page_alias}" size="30" maxlength="240"/>
				{formhelp note=""}
			{/forminput}
		</div>

		<div class="row submit">
			<input type="submit" name="create" value="{tr}Update{/tr}"/>
		</div>
	{/form}
</div>

<div class="structuretoc">
	<ul class="toc">
		<li>
			{section name=ix loop=$subtree}
				{if $subtree[ix].pos eq ''}
					{if $structureInfo.structure_id eq $subtree[ix].structure_id}<div class="highlight">{/if}
						<a href="{$smarty.server.PHP_SELF}?structure_id={$subtree[ix].structure_id}&amp;tab=alias">{$subtree[ix].title|escape}</a>
					{if $structureInfo.structure_id eq $subtree[ix].structure_id}</div>{/if}
				{else}
					{if $subtree[ix].first}<ul>{else}</li>{/if}
					{if $subtree[ix].last}</ul>{else}
						<li>
							{if $structureInfo.structure_id eq $subtree[ix].structure_id}<div class="highlight">{/if}
								<strong>{$subtree[ix].pos}</strong> 
								<a href="{$smarty.server.PHP_SELF}?structure_id={$subtree[ix].structure_id}&amp;tab=alias">{$subtree[ix].title|escape}</a>
							{if $structureInfo.structure_id eq $subtree[ix].structure_id}</div>{/if}
					{/if}
				{/if}
			{/section}
		</li>
	</ul><!-- end outermost .toc -->
</div>
<div class="clear"></div>

{/strip}
