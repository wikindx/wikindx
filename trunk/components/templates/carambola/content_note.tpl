{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

<div class="contentNote">
<strong>
		<span class="small">{$resourceSingle.note.title}
		</span></strong>{if isset($resourceSingle.note.editLink)}&nbsp;&nbsp;{$resourceSingle.note.editLink}{/if}{if isset($resourceSingle.note.deleteLink)}&nbsp;&nbsp;{$resourceSingle.note.deleteLink}{/if}

{if isset($resourceSingle.note.note)}
	<div class="alternate1">

	{$resourceSingle.note.note}
		{if isset($multiUser)}
		<br><span class="hint">{$resourceSingle.note.userAdd}&nbsp;&nbsp;{$resourceSingle.note.userEdit}</span>
		{/if}

</div>
{/if}
</div>
