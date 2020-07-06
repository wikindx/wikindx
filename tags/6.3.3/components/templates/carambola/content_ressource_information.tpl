{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 *}

{if isset($resourceSingle.info)}
<!-- begin content_ressource_information.tpl -->
<div class="contentRessourceInformation">

	<div class="small"><td class="small">{if isset($resourceSingle.info)}{"<br>"|implode:$resourceSingle.info}{/if}</div>
	<div class="small">{if isset($resourceSingle.lists)}{"<br>"|implode:$resourceSingle.lists}{/if}</div>
	{if isset($multiUser)}
	<div class="small" style="text-align:right;">
		{if isset($resourceSingle.accesses)}{$resourceSingle.accesses}{/if}
		{if isset($resourceSingle.viewIndex)}<br>{$resourceSingle.viewIndex}{/if}
		
		{if isset($resourceSingle.download)}
		<br>{$resourceSingle.download}
		{/if}
		
		{if isset($resourceSingle.popIndex)}<br>{$resourceSingle.popIndex}{/if}
		
		{if isset($resourceSingle.maturity)}
		<br>{$resourceSingle.maturity}
		{/if}
	</div>
	{/if}

</div>
<!-- end content_ressource_information.tpl -->
{/if}