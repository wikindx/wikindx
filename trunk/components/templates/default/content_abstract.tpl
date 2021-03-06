{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

<table class="contentAbstract">
<tr>
	<td>
		<strong><span class="small">{$resourceSingle.abstract.title}</span></strong>
		{if isset($resourceSingle.abstract.editLink)}{$resourceSingle.abstract.editLink}&nbsp;&nbsp;{/if}{if isset($resourceSingle.abstract.deleteLink)}&nbsp;&nbsp;{$resourceSingle.abstract.deleteLink}{/if}
	</td>
</tr>

{if isset($resourceSingle.abstract.abstract)}
<tr class="alternate1">
	<td>
		{$resourceSingle.abstract.abstract}
		{if isset($multiUser)}
		<br><span class="hint">{$resourceSingle.abstract.userAdd}&nbsp;&nbsp;{$resourceSingle.abstract.userEdit}</span>
		{/if}
	</td>
</tr>
{/if}
</table>
