{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}
{if isset($pagingList)}
{if $pagingList != false}
<div class="contentPaging">
	{"&nbsp;&nbsp;|&nbsp;&nbsp;"|implode:$pagingList}
</div>
{/if}
{/if}
