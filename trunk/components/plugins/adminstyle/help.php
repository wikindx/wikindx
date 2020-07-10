<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

/**
 * ADMINSTYLE class -- help file
 */
class adminstyle_help
{
    public function __construct()
    {
        GLOBALS::setTplVar("heading", "");
    }
    /**
     * Print the about page
     */
    public function init()
    {
        $domain = mb_strtolower(basename(__DIR__));
        
        $pString = HTML\p(dgettext($domain, "If you have WIKINDX admin rights, you can create and edit bibliographic styles for on-the-fly formatting when displaying or exporting bibliographic lists."));
        
        // translators: #linkSfWikindx# unchanged. This pattern is replaced by a link
        $pString .= HTML\p(dgettext($domain, "These styles are stored as XML files each within its own directory in the styles/ directory. This directory <strong>must</strong> be writeable by everyone or at least the web server user (usually user 'nobody'). Additionally, when editing an existing style, the XML style file within its named directory in the styles/ directory <strong>must also</strong> be writeable by everyone or the web server user. As new bibliographic styles are created, the WIKINDX team will make these available on the #linkSfWikindx# downloads site as plug-ins. Once you have a downloaded file, simply unzip the contents to the styles/ directory."));
        
        // translators: #linkSfWikindx# unchanged. This pattern is replaced by a link
        $pString .= HTML\p(dgettext($domain, "If you develop new styles yourself, you are strongly encouraged to contact the WIKINDX developers at #linkSfWikindx# to make them available to other users."));
        $pString .= HTML\p(dgettext($domain, "You can create a new style based on an existing one by copying the existing style. To remove a style from the list available to your users, disable that style in the Admin|Components menu."));
        $pString .= HTML\p(dgettext($domain, "Please note, to edit a style, you should do it from the same browser window as you use to view a bibliographic list.  This is because, in order to save processing the style definition file each time you list a bibliography, WIKINDX will check to see if the style definition file has been edited and therefore needs reprocessing. This information is stored in a PHP session variable; each browser window has its own separate set of session variables with no cross-interrogation available. If you edit the style definition file from another browser window then you are unlikely to see changes when you refresh your bibliographic list."));

        $pString .= HTML\hr();

        $pString .= HTML\p(dgettext($domain, "Each style has a set of options that define the heading style of titles, how to display numbers and dates etc. and then a separate style definition for each resource type that WIKINDX handles. The 'Short Name' is used by WIKINDX as both the folder and file name and for this reason should not only be a unique name within styles/, but should also have no spaces or any other characters that may cause confusion with your operating system (i.e. alphanumeric characters only). The 'Long Name' is the description of the style that is displayed to WIKINDX users."));
        $pString .= HTML\p(dgettext($domain, "The 'Editor switch' requires special attention. Some bibliographic styles require that for books and book chapters, where there exists an editor but no author, the position occupied by the author is taken by the editor. If you select 'Yes' here, you should then supply a replacement editor field. Please note that if the switch occurs, the editor(s) formatting will be inherited from the global settings you supplied for the author. See the examples below."));
        $pString .= HTML\p(dgettext($domain, "The three 'generic style' definitions are required and are used to display any resource type for which there does not yet exist a style definition. This allows you to build up your style definitions bit by bit.  Furthermore, some bibliographic styles provide no formatting guidelines for particular types of resource in which case the generic styles will provide some formatting for those resources according to the general guidelines for that bibliographic style. Each resource for which there is no style definition will fall back to the chosen generic style. The generic styles try their best but if formatting is strange for a particular resource type then you should explicitly define a style definition for that type."));
        $pString .= HTML\p(dgettext($domain, "Each style definition has a range of available fields listed to the right of each input box. These fields are <strong>case-sensitive</strong> and need not all be used. However, with some of the more esoteric styles, the more database fields that have been populated for each resource in the WIKINDX, the more likely it is that the formatting will be correct."));

        $pString .= HTML\hr();

        $pString .= HTML\h(dgettext($domain, "SYNTAX"));
        $pString .= HTML\p(dgettext($domain, "The style definition syntax uses a number of rules and special characters:"));
        
        $list = HTML\li(dgettext($domain, "The character '|' separates fields from one another."));
        $list .= HTML\li(dgettext($domain, "If a field does not exist or is blank in the database, none of the definition for that field is printed."));
        $list .= HTML\li(dgettext($domain, "<strong>Field names are case-sensitive</strong>&nbsp;and need not all be used."));
        $list .= HTML\li(dgettext($domain, "Within a field, you can add any punctuation characters or phrases you like before and after the field name."));
        $list .= HTML\li(dgettext($domain, "Any word that you wish to be printed and that is the same (even a partial word) as a field name should be enclosed in backticks '`'."));
        $list .= HTML\li(dgettext($domain, "For creator lists (editors, revisers, directors etc.) and pages, alternative singular and plural text can be specified with '^' (e.g. |^p.^pp.^pages| would print the field 'pages' preceded by 'pp.' if there were multiple pages or 'p.' if not)."));
        $list .= HTML\li(dgettext($domain, "BBCode <code>[u]..[/u]</code>, <code>[i]..[/i]</code>, <code>[b]..[/b]</code>, <code>[sup]..[/sup]</code> and <code>[sub]..[/sub]</code> can be used to specify underline, italics, bold, superscript and subscript."));
        $list .= HTML\li(dgettext($domain, "The character '%' enclosing any text or punctuation <em>before</em> the field name states that that text or those characters will only be printed if the <em>preceeding</em> field exists or is not blank in the database. The character <code>'%'</code> enclosing any text or punctuation <em>after</em> the field name states that that text or those characters will only be printed if the <em>following</em> field exists or is not blank in the database. It is optional to have a second pair in which case the construct should be read <code>'if target field exists, then print this, else, if target field does not exist, print that'</code>.  For example, <code>'%: %'</code> will print <code>': '</code> if the target field exists else nothing if it doesn't while <code>'%: %. %'</code> will print <code>': '</code> if the target field exists else <code>'. '</code> if it does not."));
        $list .= HTML\li(dgettext($domain, "Characters in fields that do not include a field name should be paired with another set and together enclose a group of fields. If these special fields are not paired unintended results may occur. These are intended to be used for enclosing groups of fields in brackets where <em>at least</em> one of the enclosed fields exists or is not blank in the database."));
        $list .= HTML\li(dgettext($domain, "<p>The above two rules can combine to aid in defining particularly complex bibliographic styles (see examples below). The pair </p>
		<p><code>|%, %. %|xxxxx|xxxxx|%: %; %|</code></p>
		<p>states that if at least one of the intervening fields exists, then the comma and colon will be printed; if an intervening field does not exist, then the full stop will be printed <em>only</em> if the <em>preceeding</em> field exists (else nothing will be printed) and the semicolon will be printed <em>only</em> if the <em>following</em> field exists (else nothing will be printed).</p>"));
        $list .= HTML\li(dgettext($domain, "If the final set of characters in the style definition is '|.' for example, the '.' is taken as the ultimate punctuation printed at the very end."));
        $list .= HTML\li(dgettext($domain, "<p>Fields can be printed or not dependent upon the existence of preceding or subsequent fields. For example,</p>
		<p><code>creator. |\$shortTitle. \$title. \$|publicationYear.</code></p>
		<p>would print the shortTitle field if the creator were populated otherwise it prints the title field.</p>
		<p><code>creator. |title. |#ISBN. ##|edition.</code></p>
		<p>prints the ISBN field if edition exists otherwise it prints nothing.</p>"));
        $list .= HTML\li(dgettext($domain, "Newlines may be added with the special string NEWLINE."));
        $pString .= HTML\ol($list);
        
        $pString .= HTML\p(dgettext($domain, "Tip: In most cases, you will find it easiest to attach punctuation and spacing at the end of the preceding field rather than at the start of the following field. This is especially the case with finite punctuation such as full stops."));

        $pString .= HTML\hr();

        $pString .= HTML\h(dgettext($domain, "EXAMPLES"));
        $pString .= "<code>author. |publicationYear. |title. |In [i]book[/i], |edited by editor (^ed^eds^). |publisherLocation%:% |publisherName. |edition ed%,%.% |(Originally published originalPublicationYear) |^p.^pp.^pages|.</code>";
        $pString .= HTML\p(dgettext($domain, "<em>might produce:</em>"));
        $pString .= "<code>de Maus, Mickey. 2004. An amusing diversion. In <em>A History of Cartoons</em>, Donald D. A. F. F. Y. Duck, and Bugs Bunny (eds). London: Animatron Publishing. 10th ed, (Originally published 2000) pp.20-9.</code>";
        $pString .= HTML\p(dgettext($domain, "<em>and, if there were no publisher location or edition entered for that resource and only one page number given, it would produce:</em>"));
        $pString .= "<code>de Maus, Mickey. 2004. An amusing diversion. In <em>A History of Cartoons</em>, Donald D. A. F. F. Y. Duck, and Bugs Bunny (eds). Animatron Publishing. (Originally published 2000) p.20.</code>";
        
        $pString .= HTML\hr();
        
        $pString .= "<code>author. |[i]title[/i]. |(|publisherLocation%: %|publisherName%, %|publicationYear.|) |ISBN|.</code>";
        $pString .= HTML\p(dgettext($domain, "<em>might produce:</em></code>"));
        $pString .= "<code>de Maus, Mickey. <em>A big book</em> (London: Animatron Publishing, 1999.) 1234-09876.</code>";
        $pString .= HTML\p(dgettext($domain, "<em>and, if there were no publisher location or publication year entered for that resource, it would produce:</em><br>"));
        $pString .= "<code>de Maus, Mickey. <em>A big book</em>. (Animatron Publishing.) 1234-09876.</code>";
        
        $pString .= HTML\hr();
        
        $pString .= "<code>author. |publicationYear. |[i]title[/i]. |Edited by editor. |edition ed. |publisherLocation%:%.% |publisherName. |Original `edition`, originalPublicationYear|.</code>";
        $pString .= HTML\p(dgettext($domain, "<em>might produce:</em>"));
        $pString .= "<code>Duck, Donald D. A. F. F. Y. 2004. <em>How to Make it Big in Cartoons</em>. Edited by M. de Maus and Goofy. 3rd ed. Selebi Phikwe: Botswana Books. Original edition, 2003.</code>";
        $pString .= HTML\p(dgettext($domain, "<em>and, if there were no author entered for that resource and the replacement editor field were 'editor ^ed.^eds.^ ', it would produce:</em>"));
        $pString .= "<code>de Maus, Mickey and Goofy eds. 2004. <em>How to Make it Big in Cartoons</em>. 3rd ed. Selebi Phikwe: Botswana Books. Original edition, 2003.</code>";
        
        $pString .= HTML\hr();
        
        $pString .= HTML\p(dgettext($domain, "Consider the following (IEEE-type) generic style definition and what it does with a resource type lacking certain fields:"));
        $pString .= "<code>creator, |\"title,\"| in [i]collection[/i], |editor, ^Ed.^Eds.^, |edition ed|. publisherLocation: |publisherName, |publicationYear, |pp. pages|.</code>";
        $pString .= HTML\p(dgettext($domain, "<em>might produce:</em>"));
        $pString .= "<code>ed Software, \"Mousin' Around,\". Gaborone: Computer Games 'r' Us, 1876.</code>";
        $pString .= HTML\p(dgettext($domain, "<em>and, when applied to a resource type with editor and edition fields:</em>"));
        $pString .= "<code>Donald D. A. F. F. Y. de Duck, \"How to Make it Big in Cartoons,\"Mickey de Maus and Goofy, Eds., 3rd ed. Selebi Phikwe: Botswana Books, 2003.</code>";
        
        $pString .= HTML\p(dgettext($domain, "Clearly there is a problem here, notably at the end of the resource title. The solution is to use rule no. 10 above:"));
        $pString .= "<code>creator, |\"title|%,\" %.\" %|in [i]collection[/i]|%, %editor, ^Ed.^Eds.^|%, %edition ed|%. %|publisherLocation: |publisherName, |publicationYear, |pp. pages|.</code>";
        $pString .= HTML\p(dgettext($domain, "<em>might produce:</em>"));
        $pString .= "<code>ed Software, \"Mousin' Around.\" Gaborone: Computer Games 'r' Us, 1876.</code>";
        $pString .= HTML\p(dgettext($domain, "<em>and:</em>"));
        $pString .= "<code>Donald D. A. F. F. Y. de Duck, \"How to Make it Big in Cartoons,\" Mickey de Maus and Goofy, Eds., 3rd ed. Selebi Phikwe: Botswana Books, 2003.</code>";
        $pString .= HTML\p(dgettext($domain, "Bibliographic styles requiring this complexity are few and far between."));

        $pString .= HTML\hr();

        $pString .= HTML\p(dgettext($domain, "If the value entered for the edition of a resource contains non-numeric characters, then, despite having set the global setting for the edition format to ordinal (3rd. etc.), no conversion will take place."));
        $pString .= HTML\p(dgettext($domain, "The formatting of the names, edition and page numbers and the capitalization of the title depends on the global settings provided for your bibliographic style."));
        
        $linkSfWikindx = HTML\a("link", "WIKINDX Sourceforge Project", "https://sourceforge.net/projects/wikindx/", "_blank");
        $pString = preg_replace("/#linkSfWikindx#/u", $linkSfWikindx, $pString);

        GLOBALS::addTplVar("content", $pString);
        FACTORY_CLOSEPOPUP::getInstance();
    }
}
