+++
title = "Admin Style"
date = 2021-02-09T21:30:41+01:00
disableToc = true
+++

If you have WIKINDX admin rights, you can create and edit bibliographic styles for on-the-fly formatting when displaying or exporting bibliographic lists.

These styles are stored as XML files each within its own directory in the components/styles/ directory. This directory **must** be writeable by everyone or at least the web server user. Additionally, when editing an existing style, the XML style file **must also** have the same rights.

If you develop new styles yourself, you are strongly encouraged to contact the WIKINDX developers at https://sourceforge.net/projects/wikindx/ to make them available to other users.

You can create a new style based on an existing one by copying the existing style. To remove a style from the list available to your users, disable that style in the Admin|Components menu.

Please note, to edit a style, you should do it from the same browser window as you use to view a bibliographic list.  This is because, in order to save processing the style definition file each time you list a bibliography, WIKINDX will check to see if the style definition file has been edited and therefore needs reprocessing. This information is stored in a PHP session variable; each browser window has its own separate set of session variables with no cross-interrogation available. If you edit the style definition file from another browser window then you are unlikely to see changes when you refresh your bibliographic list.

----

Each style has a set of options that define the heading style of titles, how to display numbers and dates etc. and then a separate style definition for each resource type that WIKINDX handles. The 'Short Name' is used as both the folder and file name and for this reason should not only be a unique name within components/styles/, but should also have no spaces or any other characters that may cause confusion with your operating system (i.e. alphanumeric characters only). The 'Long Name' is the description of the style that is displayed to users.

The **Editor switch** requires special attention. Some bibliographic styles require that for books and book chapters, where there exists an editor but no author, the position occupied by the author is taken by the editor. If you select 'Yes' here, you should then supply a replacement editor field. Please note that if the switch occurs, the editor(s) formatting will be inherited from the global settings you supplied for the author. See the examples below.

The three **generic style** definitions are required and are used to display any resource type for which there does not yet exist a style definition. This allows you to build up your style definitions bit by bit.  Furthermore, some bibliographic styles provide no formatting guidelines for particular types of resource in which case the generic styles will provide some formatting for those resources according to the general guidelines for that bibliographic style. Each resource for which there is no style definition will fall back to the chosen generic style. The generic styles try their best but if formatting is strange for a particular resource type then you should explicitly define a style definition for that type.

Each style definition has a range of available fields listed to the right of each input box. These fields are **case-sensitive** and need not all be used. However, with some of the more esoteric styles, the more database fields that have been populated for each resource in the WIKINDX, the more likely it is that the formatting will be correct.

----

## Syntax

The style definition syntax uses a number of rules and special characters:

* The character **|** separates fields from one another.

* If a field does not exist or is blank in the database, none of the definition for that field is printed.

* **Field names are case-sensitive** and need not all be used.

* Within a field, you can add any punctuation characters or phrases you like before and after the field name.

* Any word that you wish to be printed and that is the same (even a partial word) as a field name should be enclosed in backticks **`**.

* For creator lists (editors, revisers, directors etc.) and pages, alternative singular and plural text can be specified with **^** (e.g. `|^p.^pp.^pages|` would print the field _pages_ preceded by _pp._ if there were multiple pages or _p._ if not).

* BBCode `[u]..[/u]`, `[i]..[/i]`, `[b]..[/b]`, `[sup]..[/sup]` and `[sub]..[/sub]` can be used to specify underline, italics, bold, superscript and subscript.

* The character **%** enclosing any text or punctuation _before_ the field name states that that text or those characters will only be printed if the _preceeding_ field exists or is not blank in the database. The character **%** enclosing any text or punctuation _after_ the field name states that that text or those characters will only be printed if the _following_ field exists or is not blank in the database. It is optional to have a second pair in which case the construct should be read `'if target field exists, then print this, else, if target field does not exist, print that'`.  For example, `'%: %'` will print `': '` if the target field exists else nothing if it doesn't while `'%: %. %'` will print `': '` if the target field exists else `'. '` if it does not.

* Characters in fields that do not include a field name should be paired with another set and together enclose a group of fields. If these special fields are not paired unintended results may occur. These are intended to be used for enclosing groups of fields in brackets where _at least_ one of the enclosed fields exists or is not blank in the database.

* The above two rules can combine to aid in defining particularly complex bibliographic styles (see examples below). The pair `|%, %. %|xxxxx|xxxxx|%: %; %|` states that if at least one of the intervening fields exists, then the comma and colon will be printed; if an intervening field does not exist, then the full stop will be printed _only_ if the _preceeding_ field exists (else nothing will be printed) and the semicolon will be printed _only_ if the _following_ field exists (else nothing will be printed).

* If the final set of characters in the style definition is `|.` for example, the **.** is taken as the ultimate punctuation printed at the very end.

* Fields can be printed or not dependent upon the existence of preceding or subsequent fields. For example,
  - `creator. |$shortTitle. $title. $|publicationYear.` would print the shortTitle field if the creator were populated otherwise it prints the title field.
  - `creator. |title. |#ISBN. ##|edition.` prints the ISBN field if edition exists otherwise it prints nothing.

* Newlines may be added with the special string **NEWLINE**.

Tip: In most cases, you will find it easiest to attach punctuation and spacing at the end of the preceding field rather than at the start of the following field. This is especially the case with finite punctuation such as full stops.

----

## Examples

```
    author. |publicationYear. |title. |In [i]book[/i], |edited by editor (^ed^eds^). |publisherLocation%:% |publisherName. |edition ed%,%.% |(Originally published originalPublicationYear) |^p.^pp.^pages|.
```

_might produce:_

>>> de Maus, Mickey. 2004. An amusing diversion. In _A History of Cartoons_, Donald D. A. F. F. Y. Duck, and Bugs Bunny (eds). London: Animatron Publishing. 10th ed, (Originally published 2000) pp.20-9.

_and, if there were no publisher location or edition entered for that resource and only one page number given, it would produce:_

>>> de Maus, Mickey. 2004. An amusing diversion. In _A History of Cartoons_, Donald D. A. F. F. Y. Duck, and Bugs Bunny (eds). Animatron Publishing. (Originally published 2000) p.20.

----


```
    author. |[i]title[/i]. |(|publisherLocation%: %|publisherName%, %|publicationYear.|) |ISBN|.
```

_might produce:_

>>> de Maus, Mickey. _A big book_ (London: Animatron Publishing, 1999.) 1234-09876.

_and, if there were no publisher location or publication year entered for that resource, it would produce:_

>>> de Maus, Mickey. _A big book_. (Animatron Publishing.) 1234-09876.

----


```
    author. |publicationYear. |[i]title[/i]. |Edited by editor. |edition ed. |publisherLocation%:%.% |publisherName. |Original `edition`, originalPublicationYear|.
```

_might produce:_

>>> Duck, Donald D. A. F. F. Y. 2004. _How to Make it Big in Cartoons_. Edited by M. de Maus and Goofy. 3rd ed. Selebi Phikwe: Botswana Books. Original edition, 2003.

_and, if there were no author entered for that resource and the replacement editor field were `editor ^ed.^eds.^ `, it would produce:_

>>> de Maus, Mickey and Goofy eds. 2004. _How to Make it Big in Cartoons_. 3rd ed. Selebi Phikwe: Botswana Books. Original edition, 2003.

----

Consider the following (IEEE-type) generic style definition and what it does with a resource type lacking certain fields:

```
    creator, |"title,"| in [i]collection[/i], |editor, ^Ed.^Eds.^, |edition ed|. publisherLocation: |publisherName, |publicationYear, |pp. pages|.
```

_might produce:_

>>> ed Software, "Mousin' Around,". Gaborone: Computer Games 'r' Us, 1876.

_and, when applied to a resource type with editor and edition fields:_

>>> Donald D. A. F. F. Y. de Duck, "How to Make it Big in Cartoons,"Mickey de Maus and Goofy, Eds., 3rd ed. Selebi Phikwe: Botswana Books, 2003.

Clearly there is a problem here, notably at the end of the resource title. The solution is to use rule no. 10 above:

```
    creator, |"title|%," %." %|in [i]collection[/i]|%, %editor, ^Ed.^Eds.^|%, %edition ed|%. %|publisherLocation: |publisherName, |publicationYear, |pp. pages|.
```

_might produce:_

>>> ed Software, "Mousin' Around." Gaborone: Computer Games 'r' Us, 1876.

_and:_

>>> Donald D. A. F. F. Y. de Duck, "How to Make it Big in Cartoons," Mickey de Maus and Goofy, Eds., 3rd ed. Selebi Phikwe: Botswana Books, 2003.

Bibliographic styles requiring this complexity are few and far between.

----

If the value entered for the edition of a resource contains non-numeric characters, then, despite having set the global setting for the edition format to ordinal (3rd. etc.), no conversion will take place.

The formatting of the names, edition and page numbers and the capitalization of the title depends on the global settings provided for your bibliographic style.

