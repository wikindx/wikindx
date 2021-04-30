+++
title = "Search"
date = 2021-02-09T21:30:41+01:00
disableToc = true
+++

There are two types of search available:

## Quick Search

* A set number of database fields are searched:
  - Title
  - Note
  - Abstract
  - Quote
  - Quote comment
  - Paraphrase
  - Paraphrase comment
  - Musing
  - Creator surname
  - Resource keyword
  - User tag
  - Any custom fields
  - Any cached attachments

* Partial word searches are the default unless the search term is an exact phrase
* You can use control words as noted below
* The restrictions noted below on searches using the abstract and note fields pertain here


## Advanced Search

* Complex composite search and select operations may be constructed by adding new fields. Some of these fields can be searched for words or phrases, some can be selected within, and some make use of numerical comparison
* The abstract and note fields are searched on in FULLTEXT BOOLEAN mode. This is a quick and efficient method for searching over potentially large text fields but it dosn't support Chinese and Japanese characters. In order to do this, your MySQL server must have the requisite parser: see [Full-Text Restrictions](https://dev.mysql.com/doc/refman/8.0/en/fulltext-restrictions.html) in the MySQL documentation. Wildcard characters will be ignored for these two fields
* Document searches can be performed on resource attachments (see Types of attachment section below) – searches are carried out on the cached versions of attachments. If you use the first select field to search on attachments any **NOT** in the search field will be ignored. Attachment searches are not filtered for the list of ignored words (see below)
* The **OR**, **AND** and **NOT** radio buttons logically link that set of search parameters to the previous set. For example, five search elements that are sequentially `1 OR 2 AND 3 NOT 4 OR 5` will be grouped as `1 OR (2 AND 3 NOT 4) OR 5`
* The structure and logic of the operation may be viewed before searching by clicking on the _View natural language_ icon
* Multiple selections may be made through various combinations of holding (on Windows and Linux) the Control and Shift keys while clicking (on Apple, the Command and Shift keys). Use the arrows to transfer select options between the select box listing those available to use and the select box listing those that will be used
* The select boxes of selected options make use of the radio buttons **OR** and **AND**. For example (selecting just the Keyword field to search on), with two or more keywords selected and **OR** set, each of the returned resources must contain at least one of those keywords.  With two or more keywords selected and **AND** set, each of the returned resources must contain all those keywords
* Ideas can also be searched but are displayed separately as they are not part of a resource


## Search terms

In both types of search, the following rules hold for the word search phrase:

* You can use the control words **AND**, **OR** and **NOT** and can group words into exact phrases using double quote marks 

* **AND**, **OR** and **NOT** are case-sensitive and function as control words only outside exact phrases
* The wildcard characters **?** (zero or one character) and __*__ (zero or multiple characters) can be used. In an exact phrase, these characters will treated literally
* The use of wildcard characters disables partial word matching
* The wildcard **?** will not match a single UTF-8 character due to the multibyte nature of UTF-8. Use __*__ instead
* Searches are case-insensitive
* A space not in an exact phrase will be treated as **OR**
* All non-alphanumeric characters (such as punctuation) not in an exact phrase will be ignored unless the character is a wildcard
* **OR** words following **AND** or **NOT** will be grouped. You might choose, therefore, to have a string of **OR** words at the start of the phrase. Some examples: 

```
word1 AND word2 OR word3 OR word4 NOT word5 word6

// gives

word1 AND (word2 OR word3 OR word4) NOT (word5 OR word6)
```

```
word1 word2 OR word3 word4 NOT word5 word6 AND word7

// gives

word1 OR word2 OR word3 OR word4 NOT (word5 OR word6) AND word7
```

```
NOT word1 word2 OR word3 OR word4 NOT word5 word6

// gives

NOT (word1 word2 OR word3 OR word4) NOT (word5 OR word6)
```

The administrator can defined a list of words which, if not in an exact phrase, will be ignored.
These are typically conjunctions and direct and indirect articles.


## Attachment cache support

You can attach files of any type to resources. For those that are text-type documents,
a small number can be converted to text and cached for fulltext search from within Advanced Search.
Following is a list of the major text-type document formats and their caching support for fulltext search.

Many old or rare office suite formats will not be directly supported. They are appointed to remove any ambiguity.
Consider converting them to DOCX or ODF format before attaching them.

The documents are analyzed according to their mime-type and then according to their extension if there is any ambiguity.

The `plain/text` mime-type is a generic format that covers a multitude of files.
As the search targets written documents, attachments with the following extension are excluded: CSV, TSV, SILK. Then encoding is assumed to be __UTF-8 only__.

|Extension |Kind of document                    |Fulltext search |MIME Type
|----------|------------------------------------|-------|----------------------------------------------------------------------------
|ABW, ZABW |AbiWord Document                    |No     |application/x-abiword
|CWK       |ClarisWorks/AppleWorks Document     |No     |
|DOC       |Word 97-2003 / DOS Word             |Yes    |[application/msword](https://www.iana.org/assignments/media-types/application/msword)
|DOCM	     |Word 2007-365 document+macro        |Yes    |[application/vnd.ms-word.document.macroEnabled.12](https://www.iana.org/assignments/media-types/application/vnd.ms-word.document.macroEnabled.12)
|DOCX	     |Word 2007-365 document              |Yes    |[application/vnd.openxmlformats-officedocument.wordprocessingml.document]()
|DOT, WPT  |Word 97-2003 / DOS Word Template    |Yes    |[application/msword](https://www.iana.org/assignments/media-types/application/msword)
|DOTM	     |Word 2007-365 template+macro        |Yes    |[application/vnd.ms-word.template.macroEnabled.12](https://www.iana.org/assignments/media-types/application/vnd.ms-word.template.macroEnabled.12)
|DOTX	     |Word 2007-365 template              |Yes    |[application/vnd.openxmlformats-officedocument.wordprocessingml.template]()
|EPUB      |Electronic publication              |No     |application/epub+zip
|FB2       |FictionBook 2.0                     |No     |
|FODP      |ODF Presentation Flat               |No     |
|FODT      |ODF XML Text Document Flat          |No     |
|HTML, HTML|HyperText Markup Language           |No     |[text/html](https://www.iana.org/assignments/media-types/text/html)
|HWP       |Hangul WP 97                        |No     |
|KWD       |KWord                               |No     |[application/vnd.kde.kword](https://www.iana.org/assignments/media-types/application/vnd.kde.kword)
|LRF       |BroadBand Ebook                     |No     |
|LWP       |Lotus WordPro                       |No     |[application/vnd.lotus-wordpro](https://www.iana.org/assignments/media-types/application/vnd.lotus-wordpro)
|MAN, MDOC |Manpage, mandoc                     |No     |[text/troff](https://www.iana.org/assignments/media-types/text/troff)
|MD        |Markdown                            |No     |[text/markdown](https://www.iana.org/assignments/media-types/text/markdown)
|MHT, MHTML|Multipart HTML                      |No     |multipart/related
|MW, MCW   |MacWrite Document                   |No     |
|MWD       |Mariner Mac Write Classic           |No     |
|ODP       |ODF Presentation                    |No     |[application/vnd.oasis.opendocument.presentation](https://www.iana.org/assignments/media-types/application/vnd.oasis.opendocument.presentation)
|ODT       |ODF Text Document                   |Yes    |[application/vnd.oasis.opendocument.text](https://www.iana.org/assignments/media-types/application/vnd.oasis.opendocument.text)
|OTP       |ODF Presentation Template           |No     |[application/vnd.oasis.opendocument.presentation-template](https://www.iana.org/assignments/media-types/application/vnd.oasis.opendocument.presentation-template)
|OTT       |ODF Text Template                   |Yes    |[application/vnd.oasis.opendocument.text-template](https://www.iana.org/assignments/media-types/application/vnd.oasis.opendocument.text-template)
|PAGES     |Apple Pages                         |No     |
|PDB       |PalmDoc                             |No     |
|PDB       |Plucker eBook                       |No     |
|PDF       |Portable Document Format            |Yes    |[application/pdf](https://www.iana.org/assignments/media-types/application/pdf)
|POTM      |PowerPoint 2007-365 Template+macro  |No     |[application/vnd.ms-powerpoint.template.macroEnabled.12](https://www.iana.org/assignments/media-types/application/vnd.ms-powerpoint.template.macroEnabled.12)
|POTX      |PowerPoint 2007-365 Template        |No     |[application/vnd.openxmlformats-officedocument.presentationml.template](https://www.iana.org/assignments/media-types/application/vnd.openxmlformats-officedocument.presentationml.template)
|PPT       |PowerPoint 97-2003                  |No     |[application/vnd.ms-powerpoint](https://www.iana.org/assignments/media-types/application/vnd.ms-powerpoint)
|PPTM      |PowerPoint 2007-365 +macro          |No     |[application/vnd.ms-powerpoint.presentation.macroEnabled.12](https://www.iana.org/assignments/media-types/application/vnd.ms-powerpoint.presentation.macroEnabled.12)
|PPTX      |PowerPoint 2007-365                 |No     |[application/vnd.openxmlformats-officedocument.presentationml.presentation](https://www.iana.org/assignments/media-types/application/vnd.openxmlformats-officedocument.presentationml.presentation)
|PS, EPS, AI |PostScript                        |No     |[application/postscript](https://www.iana.org/assignments/media-types/application/postscript)
|RTF       |Rich Text Format 1.9.1              |Yes    |[application/rtf](https://www.iana.org/assignments/media-types/application/rtf) or [text/rtf](https://www.iana.org/assignments/media-types/text/rtf)
|SCD, SLA  |Scribus Document                    |No     |[application/vnd.scribus](https://www.iana.org/assignments/media-types/application/vnd.scribus)
|SDD       |StarOffice presentation             |No     |
|SDW       |StarOffice Document                 |No     |
|STI       |OpenOffice.org 1.0 Presentation Template |No|
|STW       |OpenOffice.org 1.0 Text Template    |No     |[application/vnd.oasis.opendocument.presentation-template](https://www.iana.org/assignments/media-types/application/vnd.oasis.opendocument.presentation-template)
|SXI       |OpenOffice.org 1.0 Presentation     |No     |
|SXW       |OpenOffice.org 1.0 Text Document    |No     |[application/vnd.oasis.opendocument.presentation](https://www.iana.org/assignments/media-types/application/vnd.oasis.opendocument.presentation)
|TEI       |Text Encoding Initiative            |No     |[application/tei+xml](https://www.iana.org/assignments/media-types/application/tei+xml)
|TEX, LATEX|TeX, LaTeX                          |No     |
|TEXI      |TexInfo File                        |No     |
|TROFF, ROFF|Groff, Roff, Troff                 |No     |[text/troff](https://www.iana.org/assignments/media-types/text/troff)
|TXT, others|Plain text                         |Yes   |text/plain
|UOF, UOT  |Unified Office Text                 |No     |
|UOP       |Unified Office presentation         |No     |
|WML       |Wireless Mark-up Language           |No     |[text/vnd.wap.wml](https://www.iana.org/assignments/media-types/text/vnd.wap.wml)
|WMLC      |Wireless Mark-up Language           |No     |[application/vnd.wap.wmlc](https://www.iana.org/assignments/media-types/application/vnd.wap.wmlc)
|WN        |WriteNow Document                   |No     |
|WPD       |Wordperfect                         |No     |[application/vnd.wordperfect](https://www.iana.org/assignments/media-types/application/vnd.wordperfect) or [application/wordperfect5.1](https://www.iana.org/assignments/media-types/application/wordperfect5.1)
|WPS       |Microsoft Works                     |No     |[application/vnd.ms-works](https://www.iana.org/assignments/media-types/application/vnd.ms-works)
|WRI       |Microsoft Write                     |No     |application/mswrite
|XHTML	  |Extensible HyperText Markup Language|No     |[application/xhtml+xml](https://www.iana.org/assignments/media-types/application/xhtml+xml)
|XPS, OXPS |XML Paper Specification             |No     |[application/vnd.ms-xpsdocument](https://www.iana.org/assignments/media-types/application/vnd.ms-xpsdocument)
