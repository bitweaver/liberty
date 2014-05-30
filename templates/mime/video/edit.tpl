{strip}
{assign var=tv    value=1.33333333333}
{assign var=ana   value=1.55555555556}
{assign var=wide  value=1.77777777778}
{assign var=puter value=1.6}
{assign var=cin1  value=1.85}
{assign var=cin2  value=2.39}

<div class="form-group">
	{formlabel label="Set Aspect Ratio" for="aspect"}
	{forminput}
		<select name="plugin[{$attachment.attachment_id}][mimevideo][meta][aspect]" id="aspect">
			<option value="">{tr}Original{/tr}</option>
			<option {if $attachment.meta.aspect == $tv    }selected="selected" {/if}value="{$tv}">4:3 ({tr}TV{/tr})</option>
			<option {if $attachment.meta.aspect == $ana   }selected="selected" {/if}value="{$ana}">14:9 ({tr}Anamorphic{/tr})</option>
			<option {if $attachment.meta.aspect == $wide  }selected="selected" {/if}value="{$wide}">16:9 ({tr}TV Widescreen{/tr})</option>
			<option {if $attachment.meta.aspect == $cin1  }selected="selected" {/if}value="{$cin1}">1.85 ({tr}Cinema Widescreen{/tr})</option>
			<option {if $attachment.meta.aspect == $cin2  }selected="selected" {/if}value="{$cin2}">2.39 ({tr}Cinema Widescreen{/tr})</option>
			<option {if $attachment.meta.aspect == $puter }selected="selected" {/if}value="{$puter}">16:10 ({tr}Computer Widescreen{/tr})</option>
		</select>
		{formhelp note="Here you can override the initially set aspect ratio. Please note that the displayed aspect aspect ratio might not correspond to the set value."}
	{/forminput}
</div>
{/strip}
