{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

<table class="contentNote">
<tr>
	<td><strong><span class="small">{$resourceSingle.note.title}</span></strong>{if isset($resourceSingle.note.editLink)}&nbsp;&nbsp;{$resourceSingle.note.editLink}{/if}{if isset($resourceSingle.note.deleteLink)}&nbsp;&nbsp;{$resourceSingle.note.deleteLink}{/if}</td>
</tr>

{if isset($resourceSingle.note.note)}
	<tr class="alternate1">
	<td>
	{$resourceSingle.note.note}
		{if isset($multiUser)}
		<br><span class="hint">{$resourceSingle.note.userAdd}&nbsp;&nbsp;{$resourceSingle.note.userEdit}</span>
		{/if}
	</td>
</tr>
{/if}
</table>
