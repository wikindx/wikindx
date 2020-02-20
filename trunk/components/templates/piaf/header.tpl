{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 *}

<!-- begin header template -->
{if $displayPopUp == false} {* Some wikindx pages are popups needing no titlebar. *}
<!-- TABLE for WIKINDX title and WIKINDX sourceforge link -->
<table>
<tbody>
<tr>
	<!-- <td><h1>{$headTitle}</h1></td>  -->
	<td class="logo"><a href="{$wkx_link}" target="_blank"><img src="{$tplPath}/images/wikindx-logo.png" style="text-align:left;" width="200px" alt="WIKINDX SourceForge" title="WIKINDX SourceForge"></a></td> 
	<td style="text-align:right;"><h1>{$headTitle}</h1></td>
</tr>
</tbody>
</table>
{/if}
<!-- end header template -->