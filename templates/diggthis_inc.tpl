{if $gContent}
	{biticon ipackage="icons" iname="digg" iexplain="digg this!" iforce="icon"}<a href="http://digg.com/submit?phase=2&amp;url={$gContent->getDisplayUri()|escape:'url'}&amp;title={$gContent->getTitle()|escape:'url'}&amp;bodytext={$parsed_data|escape:'url'|truncate:350}">{tr}digg this!{/tr}

{* iframe style that shows counts, perhaps good for inside content block
	<script type="text/javascript"> digg_url = '{$gContent->getDisplayUri()|escape:"quotes"}'; </script>
	<script type="text/javascript"> digg_title = '{$gContent->getTitle()|escape:"quotes"}'; </script>
	<script type="text/javascript"> digg_bodytext = '{$parsed_data|escape:"quotes"|truncate:350}'; </script>
	{if $diggStyle|default:$gBitSystem->getConfig('promotions_diggstyle')}
		<script type="text/javascript"> digg_skin = 'compact'; </script>
	{/if}
	<script src="http://digg.com/tools/diggthis.js" type="text/javascript"></script>
*}

{/if}
