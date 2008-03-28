{if $gContent}
{strip}
<div class="display {$contentType}">
	{if $gContent->mInfo.title}
		<div class="header"><h1>{$gContent->mInfo.title|escape}</h1></div>
	{/if}

	<div class="content">
		{$gContent->mInfo.parsed_data}
	</div>
</div>
{/strip}
{/if}
