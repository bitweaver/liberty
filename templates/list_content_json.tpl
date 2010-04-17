{ldelim}
{if count($listcontent) > 0}
  "Status": {ldelim}
    "code": 200,
    "request": "datasearch"
  {rdelim},
  "Content":[{foreach name=loop key=keyid item=icontent from=$listcontent}
    {ldelim}
		  "content_id":{if $icontent.content_id}{$icontent.content_id}{else}null{/if},
		  "content_type_guid":'{$icontent.content_type_guid}',
		  "content_name":'{$icontent.content_name}',
		  "lat":{if $icontent.lat|is_numeric}{$icontent.lat}{else}null{/if},
		  "lng":{if $icontent.lng|is_numeric}{$icontent.lng}{else}null{/if},
		  "title":'{$icontent.title|addslashes}',
		  "created":{if $icontent.created}{$icontent.created}{else}null{/if},
		  "last_modified":{if $icontent.last_modified}{$icontent.last_modified}{else}null{/if},
		  "modifier_real_name":'{$icontent.modifier_real_name}',
		  "modifier_user_id":{if $icontent.modifier_user_id}{$icontent.modifier_user_id}{else}null{/if},
		  "creator_real_name":'{$icontent.creator_real_name}',
		  "creator_user_id":{if $icontent.creator_user_id}{$icontent.creator_user_id}{else}null{/if},
		  "display_url":'{$icontent.display_url}',
		  "hits":{if $icontent.hits}{$icontent.hits}{else}null{/if},
		  "stars_rating_count":{if $icontent.stars_rating_count}{$icontent.stars_rating_count}{else}null{/if},
		  "stars_rating":{if $icontent.stars_rating}{$icontent.stars_rating}{else}null{/if},
		  "stars_pixels":{if $icontent.stars_pixels}{$icontent.stars_pixels}{else}null{/if},
		  "stars_user_rating":{if $icontent.stars_user_rating}{$icontent.stars_user_rating}{else}null{/if},
		  "stars_user_pixels":{if $icontent.stars_user_pixels}{$icontent.stars_user_pixels}{else}null{/if}
		{rdelim}{if !$smarty.foreach.loop.last},{/if}
	{/foreach}],
	"ListInfo":{ldelim}
		"total_records":{if $listInfo.total_records}{$listInfo.total_records}{else}null{/if},
		"total_pages":{if $listInfo.total_pages}{$listInfo.total_pages}{else}null{/if},
		"current_page":{if $listInfo.current_page}{$listInfo.current_page}{else}null{/if},
		"next_offset":{if $listInfo.next_offset}{$listInfo.next_offset}{else}null{/if},
		"prev_offset":{if $listInfo.prev_offset}{$listInfo.prev_offset}{else}null{/if},
		"offset":{if $listInfo.offset}{$listInfo.offset}{else}null{/if},
		"find":{if $listInfo.find}"{$listInfo.find}"{else}null{/if},
		"sort_mode":{if $listInfo.sort_mode}"{$listInfo.sort_mode}"{else}null{/if},
		"max_records":{if $listInfo.max_records}{$listInfo.max_records}{else}null{/if}
	{rdelim}
{elseif count($listcontent) == 0}
  /*put error code here*/
  "Status": {ldelim}
    "code": 204,
    "request": "datasearch"
  {rdelim}
{/if}
{rdelim}
