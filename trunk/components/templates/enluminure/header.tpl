{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

<!-- begin header template -->
{if $displayPopUp == false} {* Some wikindx pages are popups needing no titlebar. *}
<div id="navigation">
{if $headTitle}<h1>{$headTitle}</h1>{/if}
<!-- TABLE for WIKINDX title and WIKINDX sourceforge link -->
<table class="logo">
<tbody>
	<tr>
		<td><a href="{$wkx_link}" target="_blank"><img src="{$tplPath}/images/wikindx-logo.png" alt="WIKINDX SourceForge" title="WIKINDX SourceForge"></a></td>
	</tr>
</tbody>
</table>
{/if}
<!-- end header template -->
