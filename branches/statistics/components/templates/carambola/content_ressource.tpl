{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 *}

<!-- begin content_ressource.tpl -->
<div class="contentRessource">
<div class="alternate1">
	<div>
	{$resourceSingle.resource}&nbsp;{if isset($resourceSingle.quarantine)}{$resourceSingle.quarantine}{/if}
	{if isset($multiUser)}
	<br><span class="hint">{if isset($resourceSingle.userAdd)}{$resourceSingle.userAdd}{/if}{if isset($resourceSingle.timestampAdd)}&nbsp;({$resourceSingle.timestampAdd}){/if}
	{if isset($resourceSingle.userEdit)}&nbsp;&nbsp;{$resourceSingle.userEdit}{/if}{if isset($resourceSingle.timestampEdit)}&nbsp;({$resourceSingle.timestampEdit}){/if}
	</span>
	{/if}
	</div>
	<div class="fright">{if isset($resourceSingle.links)}{"&nbsp;"|implode:$resourceSingle.links}{/if}</div>
</div>
</div>
<!-- end content_ressource.tpl -->
