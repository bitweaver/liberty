{strip}
<div class="ranking">
	<div class="header">
		<h1>{tr}Rankings{/tr}</h1>
	</div>

	<div class="body">
		{form legend="Ranking Settings"}
			<div class="row">
				{formlabel label="Select Attribute" for="sort_mode"}
				{forminput}
					<select name="sort_mode" id="sort_mode">
						{section name=ix loop=$rankingOptions}
							<option value="{$rankingOptions[ix].value|escape}" {if $smarty.request.sort_mode eq $rankingOptions[ix].value}selected="selected"{/if}>{$rankingOptions[ix].output}</option>
						{/section}
					</select>
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Number of items" for="max_records"}
				{forminput}
					<select name="max_records" id="max_records">
						<option value="10" {if $smarty.request.max_records eq 10}selected="selected"{/if}>{tr}Top 10{/tr}</option>
						<option value="20" {if $smarty.request.max_records eq 20}selected="selected"{/if}>{tr}Top 20{/tr}</option>
						<option value="50" {if $smarty.request.max_records eq 50}selected="selected"{/if}>{tr}Top 50{/tr}</option>
						<option value="100" {if $smarty.request.max_records eq 100}selected="selected"{/if}>{tr}Top 100{/tr}</option>
					</select>
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" name="rank_settings" value="{tr}Apply settings{/tr}" />
			</div>
		{/form}

		<h2>{$rankList.title} &nbsp;&nbsp; <small>[ {$rankList.attribute} ]</small></h2>
		<ol>
			{foreach from=$rankList.data item=item}
				<li class="{cycle values="even,odd"}">
					{if $smarty.request.sort_mode == "last_modified_desc"}
						{$item.display_link}&nbsp;&nbsp;&nbsp;<small>{$item.$attribute|bit_short_datetime}</small>
					{elseif $smarty.request.sort_mode == "top_authors"}
						{displayname hash=$item.login}&nbsp;&nbsp;&nbsp;<small>[ {$item.$attribute|default:"0"} ]</small>
					{else}
						{$item.display_link}&nbsp;&nbsp;&nbsp;<small>[ {$item.$attribute|default:"0"} ]</small>
					{/if}
				</li>
			{foreachelse}
				<li>{tr}No records found{/tr}</li>
			{/foreach}
		</ol>
	</div><!-- end .body -->
</div><!-- end .ranking -->
{/strip}
