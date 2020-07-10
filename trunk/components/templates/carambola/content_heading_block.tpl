{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

<!-- begin content_heading_block.tpl -->
{if isset($resourceSingle.navigation) or isset($resourceList[0].navigation) or isset($resourceListInfo.info) or isset($resourceListSearchForm) or isset($resourceListInfo.reorder)}
<div class="headingBlock">
	
	{* START OPTIONAL RESOURCE NAVIGATION LINKS *}
	{if isset($resourceSingle.navigation)}
	<!-- resourceSingle.navigation -->
	<div class="hint right">{"&nbsp;&nbsp;"|implode:$resourceSingle.navigation}</div>
	{/if}
	{* END OPTIONAL RESOURCE NAVIGATION LINKS *}
	
	{* START OPTIONAL LIST NAVIGATION LINKS *}
	{* Typically used to move onto the next metadata when viewing random metadata.  There is only one resource hence use of [0]. *}
	{if isset($resourceList[0].navigation)}
	<!-- resourceList[0].navigation -->
	<div class="hint right">{"&nbsp;&nbsp;"|implode:$resourceList[0].navigation}</div>
	{/if}
	{* END OPTIONAL LIST NAVIGATION LINKS *}

	{* START OPTIONAL RESOURCE LIST INFORMATION DISPLAY *}
	{if isset($resourceListInfo.info)}
	<!-- resourceListInfo.info -->
	<div class="hint">
		{$resourceListInfo.info}<br>
		{if isset($resourceListInfo.params)}{$resourceListInfo.params}{/if}
		{if isset($resourceListInfo.cms)}{$resourceListInfo.cms}{/if}
	</div>
	{/if}

	{* $resourceListSearchForm is only available when displaying results from Quick Search -- it is 
	a table comprising the quick search form itself. *}
	{if isset($resourceListSearchForm)}
	<!-- resourceListSearchForm -->
	<div class="hint right">{$resourceListSearchForm}</div>
	{/if}
	{* END OPTIONAL RESOURCE LIST INFORMATION DISPLAY *}

</div>
{/if}
<!-- end content_heading_block.tpl -->
