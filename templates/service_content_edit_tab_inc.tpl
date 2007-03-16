
{php}
global $gContent;
//vd( $gContent );
{/php}

{if $gBitUser->hasPermission('p_liberty_assign_content_perms')}
{jstab title="Permissions"}

{include file="bitpackage:liberty/content_permissions_inc.tpl"}

{/jstab}
{/if}
