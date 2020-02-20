<!-- begin footer template -->
{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 *}

<div id="copyright">
	{* $inline3 is one of 4 containers for inline plugin output *}
	{if isset($inline3)}<div class="inline3">{$inline3}</div>{/if}
	
	{* Optional $footerInfo is an array with members: 'wikindxVersion', 'numResources', 'numQueries', 'scriptTime', 'username', 'style' and 'bibliography' *}
	{assign var=footerInfo value=$footerInfo|default:false}
	<div class="footerInfo"><a class="link" href="{$wkx_link}" target="_blank">{$wkx_title}</a>&nbsp;{if isset($footerInfo)}{"<br>"|implode:$footerInfo}{/if}</div>
	
	{* $inline4 is one of 4 containers for inline plugin output *}
	{if isset($inline4)}<div class="inline4">{$inline4}</div>{/if}
	
	{* START EMAIL CONTACT DISPLAY *}
	{* Display contact email?  Usually for front page only. *}
	{* mailto() is a Smarty function that obfuscates email addresses making them difficult to lift by spiders *}
	
	{if isset($contactEmail)}
	<div class="contactBlock">Contact: {mailto address="$contactEmail" encode="javascript"}</div>
	{/if}
	{* END EMAIL CONTACT DISPLAY *}
</div>
<!-- end footer template -->