{assign var=serviceLocTpls value=$gLibertySystem->getServiceValues("content_`$serviceLocation`_tpl")}
{if $serviceLocTpls and ( $serviceLocation == 'nav' or $serviceLocation == 'view' )}
<div class="services-{$serviceLocation}">
{/if}
{foreach from=$serviceLocTpls key=serviceName item=template}
	{include file=$template serviceHash=$serviceHash}
{/foreach}
{if $serviceLocTpls and ( $serviceLocation == 'nav' or $serviceLocation == 'view' )}
	</div>
{/if}
