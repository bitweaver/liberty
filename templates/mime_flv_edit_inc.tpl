{strip}
{math equation="x/y" x=4  y=3  assign=tv}
{math equation="x/y" x=14 y=9  assign=ana}
{math equation="x/y" x=16 y=9  assign=wide}
{math equation="x/y" x=16 y=10 assign=puter}
{math equation="x/y" x=1.85 y=1 assign=cin1}
{math equation="x/y" x=2.39 y=1 assign=cin2}

<div class="row">
	{formlabel label="Set Aspect Ratio" for="aspect"}
	{forminput}
		<select name="plugin[{$attachment.attachment_id}][mimeflv][meta][aspect]" id="aspect">
			<option value="">{tr}Original{/tr}</option>
			<option {if $attachment.meta.aspect == $tv    }selected="selected" {/if}value="{$tv}">4:3 ({tr}TV{/tr})</option>
			<option {if $attachment.meta.aspect == $ana   }selected="selected" {/if}value="{$ana}">14:9 ({tr}Anamorphic{/tr})</option>
			<option {if $attachment.meta.aspect == $wide  }selected="selected" {/if}value="{$wide}">16:9 ({tr}Widescreen{/tr})</option>
			<option {if $attachment.meta.aspect == $cin1  }selected="selected" {/if}value="{$cin1}">1.85 ({tr}Cinema Widescreen{/tr})</option>
			<option {if $attachment.meta.aspect == $cin2  }selected="selected" {/if}value="{$cin2}">2.39 ({tr}Cinema Widescreen{/tr})</option>
			<option {if $attachment.meta.aspect == $puter }selected="selected" {/if}value="{$puter}">16:10 ({tr}Computer Widescreen{/tr})</option>
		</select>
		{formhelp note="Here you can override the initially set aspect ratio. Please note that the displayed aspect aspect ratio might not correspond to the set value."}
	{/forminput}
</div>
{/strip}
