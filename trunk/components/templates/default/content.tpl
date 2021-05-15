{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

<!-- begin content template -->
{**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 *}

{* Don't edit these assign settings *}

{assign var=resourceList value=$resourceList|default:false}
{assign var=resourceSingle value=$resourceSingle|default:false}
{assign var=resourceListInfo value=$resourceListInfo|default:false}
{assign var=resourceSearchForm value=$resourceSearchForm|default:false}
{assign var=multiUser value=$multiUser|default:false}

{* $content is a general catch-all variable used for the majority of WIKINDX's output *}

{if isset($heading)}
{if $heading != ''}
<h3 class="pageHeading">{$heading}</h3>
{/if}
{/if}

{if $displayPopUp == false}
<!-- Content heading -->
{include file='content_heading_block.tpl'}
{/if}

{* START GENERAL CONTENT DISPLAY *}
<!-- Content -->
{include file='content_content.tpl'}
{* END GENERAL CONTENT DISPLAY *}


{* START RESOURCE LISTS DISPLAY *}
{*
This IF statement is used when there are lists of resources to print out to the browser.
$resourceList contains lists of resources (populated through list, search and select functions in WIKINDX)
and is an array of arrays.  The .user, .popIndex, .viewIndex, .downloads and .maturity elements exist only in WIKINDX's multiuser
mode (and here, the template also decides not to show .timestamp when in single user mode).
The .links element is an array of hyperlink icons ('view', 'edit, 'delete', 'bibtex' and 'cms') in addition 
to the element 'checkbox'. Not all elements need exist so you should test for them unless you are merely 
going to implode or cycle over them.
The .metadata element is an array of quotes, paraphrases and musings for the resource that is available when browsing metadata keywords.
*}
{if isset($resourceList)}
{if isset($resourceListInfo.select) or isset($resourceListInfo.reorder)}
{$resourceListInfo.selectformheader}



<table class="resourceListBlock">
<tr>
	{if isset($resourceListInfo.reorder)}
	<!-- resourceListInfo.reorder -->
	<td class="hint left">{$resourceListInfo.reorder}</td>
	{/if}
	{if isset($resourceListInfo.select)}
	<!-- resourceListInfo.select -->
	<td class="hint right">{$resourceListInfo.select}</td>
	{/if}
</tr>
</table>
{/if}

{* When displaying a list of resources to cite in one of the WYSIWYG textareas (e.g. quote), various fields can be filled in by the user.
These include 'pageStart', 'pageEnd', 'preText', 'postText' and 'cite' (submit button) *}
{assign var=citeFields value=$citeFields|default:false}
{if isset($citeFields)}
	{if isset($citeFields.formheader)}
	{$citeFields.formheader}
	{/if}
{/if}


<table class="resourceListBlock">
{section loop=$resourceList name=rows}
	<tr class="{cycle values="alternate1,alternate2"}">
		<td class="left">{if array_key_exists('resource', $resourceList[rows])}{$resourceList[rows].resource}{/if}{if array_key_exists('quarantine', $resourceList[rows])}&nbsp;{$resourceList[rows].quarantine}{/if}</td>
		<td class="right width15percent">
		{if array_key_exists('links', $resourceList[rows])}
		{"&nbsp;"|implode:$resourceList[rows].links}
		{else}
		&nbsp;
		{/if}
		</td>
	</tr>
	
	{if isset($multiUser)}
	<tr class="{cycle values="alternate1,alternate2"}">
		<td class="hint left">{if array_key_exists('user', $resourceList[rows])}{$resourceList[rows].user}{/if} {if array_key_exists('timestamp', $resourceList[rows])}{$resourceList[rows].timestamp}{/if}</td>
		<td class="hint right">
		{if array_key_exists('maturity', $resourceList[rows]) and array_key_exists('popIndex', $resourceList[rows])}
			{$resourceList[rows].popIndex}<br>{$resourceList[rows].maturity}
		{elseif array_key_exists('popIndex', $resourceList[rows])}
			{$resourceList[rows].popIndex}
		{elseif array_key_exists('maturity', $resourceList[rows])}
			{$resourceList[rows].maturity}
		{/if}
		</td>
	</tr>
	{/if}
	
	{if array_key_exists('metadata', $resourceList[rows])}
	{section loop=$resourceList[rows].metadata name=mRows}
		<tr class="{cycle values="alternate3,alternate4"}">
			<td class="right" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$resourceList[rows].metadata[mRows]}</td>
		</tr>
	{/section}
	{/if}
{/section}
</table>

{* When deleting a list of resources, there is also a form submit button *}
{if isset($submit)}
<div class="resourceListSubmitBlock">{$submit}</div>
{/if}

{if isset($resourceListInfo.select) or isset($resourceListInfo.reorder)}
{$resourceListInfo.selectformfooter}
{/if}


{* When displaying a list of resources to cite in one of the WYSIWYG textareas (e.g. quote), various fields can be filled in by the user.
These include 'pageStart', 'pageEnd', 'preText', 'postText' and 'cite' (submit button) *}
{assign var=citeFields value=$citeFields|default:false}
{if isset($citeFields)}
<!-- Cite fields -->
{include file='content_cite_fields.tpl'}
	{if isset($citeFields.formfooter)}
	{$citeFields.formfooter}
	{/if}
{/if}
{/if}
{* END RESOURCE LISTS DISPLAY *}


{* START FILE LISTS DISPLAY *}
{*
This IF statement is used when there are lists of files or attachment links to print out to the browser.
$fileList contains lists of hyperlinked files or attachments.
$fileListIds contains hyperlinked resourceView IDs
*}
{assign var=fileList value=$fileList|default:false}
{if isset($fileList)}
{if $fileList != false}
<!-- File list -->
{include file='content_file_list.tpl'}
{/if}
{/if}
{* END FILE LISTS DISPLAY *}


{* START SINGLE RESOURCE DISPLAY *}
{*
This IF statement is used to print out to the browser a single resource's details.
$resourceSingle contains lists of the resource's elements.
The .message element indicates success, errors etc. of previous operations
The .navigation element is an array of .back and .forward elements -- both, just one 
or neither may exist.
The .userAdd, .userEdit, .timestampAdd, .timestampEdit, and .maturity 
elements exist only in WIKINDX's multiuser mode.
.popIndex, .viewIndex, .download and .accesses are in both multiuser and single user modes.
The .info element is an array of .email, .type, .language, .isbn, .keyid, .basket and .doi (the .email link 
is only available if the wikindx is set up for emailing in config.php).
The .links element is an array of hyperlink icons ('edit, 'delete', 'bibtex' and 'cms').
The .lists element is an array containing .categories, .subcategories, .keywords, .usertags, .creators, .publisher,
.collection, .bibliographies and .cited elements.
The .attachments element is an array of .title, .editLink, .embargoed and .attachments (an array of attachments).
The .urls element is an array of .title, .editLink and .urls (an array of URLs).
The .abstract element is an array containing .title, .userAdd, .userEdit, .editLink, .deleteLink and .abstract.
The .note element is an array containing .title, .userAdd, .userEdit, .editLink, .deleteLink and .note.
Not all elements need exist so you should test for them unless you are merely 
going to implode or cycle over them.
*}
{if isset($resourceSingle)}
	<!-- message -->
	{if isset($resourceSingle.message)}
	{$resourceSingle.message}
	{/if}
	
	{if isset($resourceSingle.resource)}
	<!-- Resource -->
	{include file='content_ressource.tpl'}
	{/if}
	
	<!-- Resource Information -->
	{include file='content_ressource_information.tpl'}
	
	{if isset($resourceSingle.attachments) or isset($resourceSingle.urls)}
	<!-- Attachments and URLs -->
	{include file='content_attachments.tpl'}
	{/if}
	
	
	{* The '.custom' element is a numbered array and contains .title, .text, .editLink, .userAdd and .userEdit elements. *}
	{if isset($resourceSingle.custom)}
	<!-- Custom fields -->
	{include file='content_custom.tpl'}
	{/if}
	
	
	{if isset($resourceSingle.abstract)}
	<!-- Abstract -->
	{include file='content_abstract.tpl'}
	{/if}
	
	
	{if isset($resourceSingle.note)}
	<!-- Note -->
	{include file='content_note.tpl'}
	{/if}
	
	
	{*
	The .quotes elements is an array containing .title, .editLink, .details (page, paragraph etc.) and 'quotes' elements.
	The 'quotes' element is a numbered array and contains .quote, .editLink, .userAdd, .keywordTitle, .keywords, .commentTitle and .comments elements.
	The .keywords element is an array consisting of hyperlinked keywords.
	The .comments element is an array and contains .comment, .timestamp and .userAdd elements.
	*}
	{if isset($resourceSingle.quotes)}
	<!-- Quotes -->
	{include file='content_quotes.tpl'}
	{/if}
	
	
	{*
	The .paraphrases elements is an array containing .title, .editLink, .details (page, paragraph etc.) and 'paraphrases' elements.
	The 'paraphrases' element is a numbered array and contains .paraphrase, .editLink, .userAdd, .keywordTitle, .keywords, .commentTitle and .comments elements.
	The .keywords element is an array consisting of hyperlinked keywords.
	The .comments element is an array and contains .comment, .timestamp and .userAdd elements.
	*}
	{if isset($resourceSingle.paraphrases)}
	<!-- Paraphrases -->
	{include file='content_paraphrases.tpl'}
	{/if}
	
	
	{*
	The .musings elements is an array containing .title, .editLink, .details (page, paragraph etc.) and 'musings' elements.
	The 'musings' element is a numbered array and contains .musing, .editLink, .timestamp and .userAdd elements.
	*}
	{if isset($resourceSingle.musings)}
	<!-- Musings -->
	{include file='content_musings.tpl'}
	{/if}
{/if}
{* END SINGLE RESOURCE DISPLAY *}


{* START METADATA ENTRY DISPLAY (NEW/EDIT QUOTES, PARAPHRASES, MUSINGS AND COMMENTS) *}
{*
The .metadata element is an array and contains .hidden, .original, .keyword, .metadataTitle, .metadata, .commentTitle, .comment, .otherComments, .locations and .form elements.
The .hidden element contains form headers and javascript for the WYSIWYG editor and MUST be printed FIRST.
The .keyword element contains the optional keyword form elements string
The .metadataTitle, .metadata, .commentTitle and .comment elements are optional and relate to the WYSIWYG areas
The .otherComments element is a numbered array and contains .comment, .userAdd and .timestamp elements for other users' comments if any
The .locations element is a string of 'pages', 'paragraph', 'section' and 'chapter' elements
The .form element contains .private and .submit elements
*}
{assign var=metadata value=$metadata|default:false}
{if isset($metadata)}
{if $metadata != false}
{include file='content_metadata.tpl'}
{/if}
{/if}

{* Load the ideas template file if $ideaTemplate == TRUE *}
{assign var=ideaTemplate value=$ideaTemplate|default:false}
{if isset($ideaTemplate)}
{if $ideaTemplate != false}
{include file='content_ideas.tpl'}
{/if}
{/if}
{* END METADATA ENTRY DISPLAY *}

{assign var=pagingList value=$pagingList|default:false}
{* START PAGING LINKS DISPLAY *}
{* For long lists of resources of tag cloud links, an optional list of paging links is created. *}
{if isset($pagingList)}
{if $pagingList != false}
<!-- Paging -->
{include file='content_paging.tpl'}
{/if}
{/if}
<!-- end content template -->
