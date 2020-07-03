{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 *}

<table class="contentCustom">
{section loop=$resourceSingle.custom name=cRows}
<tr class="{cycle values="alternate1,alternate2"}">
	<td>
	<strong><span class="small">{$resourceSingle.custom[cRows].title}</span></strong>
	&nbsp;&nbsp;
	{if array_key_exists('editLink', $resourceSingle.custom[cRows])}
		{$resourceSingle.custom[cRows].editLink}
	{/if}
	{if array_key_exists('text', $resourceSingle.custom[cRows])}
	{$resourceSingle.custom[cRows].text}
	{/if}
	{if isset($multiUser)}
	{if array_key_exists('userAdd', $resourceSingle.custom[cRows]) or array_key_exists('userEdit', $resourceSingle.custom[cRows])}
		<br><span class="hint">{if array_key_exists('userAdd', $resourceSingle.custom[cRows])}{$resourceSingle.custom[cRows].userAdd}{/if}{if array_key_exists('userEdit', $resourceSingle.custom[cRows])}&nbsp;&nbsp;{$resourceSingle.custom[cRows].userEdit}{/if}</span>
	{/if}
	{/if}
	</td>
</tr>
{/section}
</table>