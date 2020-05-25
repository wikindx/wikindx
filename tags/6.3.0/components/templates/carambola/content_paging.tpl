{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 *}
{if isset($pagingList)}
{if $pagingList != false}
<div class="contentPaging">
	{"&nbsp;&nbsp;|&nbsp;&nbsp;"|implode:$pagingList}
</div>
{/if}
{/if}