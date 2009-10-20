{strip}
{assign var=serviceLocTpls value=$gLibertySystem->getServiceValues("content_`$serviceLocation`_tpl")}
{capture assign=liberty_service_content}{strip}
	{foreach from=$serviceLocTpls key=serviceName item=template}
		{include file=$template serviceHash=$serviceHash}
	{/foreach}
{/strip}{/capture}
{if !empty($liberty_service_content)}
	{if $serviceLocTpls and ( $serviceLocation == 'nav' or $serviceLocation == 'view' )}
		<div class="services-{$serviceLocation}">
	{/if}
	{$liberty_service_content}
	{if $serviceLocTpls and ( $serviceLocation == 'nav' or $serviceLocation == 'view' )}
		</div>
	{/if}
{/if}
{/strip}
