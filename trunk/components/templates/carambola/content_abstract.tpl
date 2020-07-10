{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

<!-- begin content_abstract.tpl -->
<div class="contentAbstract">

		<strong><span class="small">{$resourceSingle.abstract.title}</span></strong>
		{if isset($resourceSingle.abstract.editLink)}&nbsp;&nbsp;{$resourceSingle.abstract.editLink}{/if}{if isset($resourceSingle.abstract.deleteLink)}&nbsp;&nbsp;{$resourceSingle.abstract.deleteLink}{/if}


{if isset($resourceSingle.abstract.abstract)}
<div class="alternate1">

		{$resourceSingle.abstract.abstract}
		{if isset($multiUser)}
		<br><span class="hint">{$resourceSingle.abstract.userAdd}&nbsp;&nbsp;{$resourceSingle.abstract.userEdit}</span>
		{/if}

</div>
{/if}
</div>
<!-- end content_abstract.tpl -->
