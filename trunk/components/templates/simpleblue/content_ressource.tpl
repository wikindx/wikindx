{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

<table class="contentRessource">
<tr class="alternate1">
	<td>
	{$resourceSingle.resource}&nbsp;{if isset($resourceSingle.quarantine)}{$resourceSingle.quarantine}{/if}
	{if isset($multiUser)}
	<br><span class="hint">{if isset($resourceSingle.userAdd)}{$resourceSingle.userAdd}{/if}{if isset($resourceSingle.timestampAdd)}&nbsp;({$resourceSingle.timestampAdd}){/if}
	{if isset($resourceSingle.userEdit)}&nbsp;&nbsp;{$resourceSingle.userEdit}{/if}{if isset($resourceSingle.timestampEdit)}&nbsp;({$resourceSingle.timestampEdit}){/if}
	</span>
	{/if}
	</td>
	<td style="text-align:right;">{if isset($resourceSingle.links)}{"&nbsp;"|implode:$resourceSingle.links}{/if}</td>
</tr>
</table>
