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
		  "content_description":'{$icontent.content_description}',
		  "lat":{if $icontent.lat}{$icontent.lat}{else}null{/if},
		  "lng":{if $icontent.lng}{$icontent.lng}{else}null{/if},
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
   {/foreach}]
{elseif count($listcontent) == 0}
  /*put error code here*/
  "Status": {ldelim}
    "code": 204,
    "request": "datasearch"
  {rdelim}
{/if}
{rdelim}
