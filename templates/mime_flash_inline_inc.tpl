<div class="item">
	{* this should really get a max h or w and base size on the bounding box -or- even better would be to thumbnail swfs for list views*}
	{assign var=height value=$attachment.preferences.height}
	{assign var=width value=$attachment.preferences.width}
	{assign var=size value=$smarty.request.size|default:medium}
	{assign var=swfwidth value=$gThumbSizes.$thumbsize.width}
	{math assign=multiplier equation="$swfwidth/$width"}
	{math assign=swfheight equation="round($height*$multiplier)"}
	<object width="{$swfwidth}" height="{$swfheight}">
		<param name="movie" value="{$attachment.source_url}" />
		<embed src="{$attachment.source_url}" width="{$swfwidth}" height="{$swfheight}">
		</embed>
		<img src="{$smarty.const.TREASURY_PKG_URL}templates/noflash.container.gif" width="200" height="100" alt="" />
	</object>
	<br />
	<a href="{$attachment.display_url}">View SWF</a>
</div>
