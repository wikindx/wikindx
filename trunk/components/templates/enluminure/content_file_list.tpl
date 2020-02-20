{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 *}

<table class="contentFileList">
{section loop=$fileList name=rows}
<tr class="{cycle values="alternate1,alternate2"}">
	<td>{if isset($fileList[rows])}{$fileList[rows]}{/if}</td>
	<td>{if isset($fileListIds[rows])}{$fileListIds[rows]}{/if}</td>
</tr>
{/section}
</table>