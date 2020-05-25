{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 *}

<!-- begin header template -->
{if $displayPopUp == false} {* Some wikindx pages are popups needing no titlebar. *}
<!-- TABLE for WIKINDX title and WIKINDX sourceforge link -->
<header>
<div id="title"><h1>{$headTitle}</h1></div>
<div id="logo">
<a href="{$wkx_link}" target="_blank"><img src="{$tplPath}/images/wikindx-logo.png" alt="WIKINDX SourceForge" title="WIKINDX SourceForge"></a>
</div>
</header>
{/if}
<!-- end header template -->