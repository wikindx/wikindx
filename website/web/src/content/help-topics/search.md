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

The documents are analyzed according to their mime-type and then according to their extension if there is any ambiguity.

The `plain/text` mime-type is a generic format that covers a multitude of files.
As the search targets written documents, attachments with the following extension are excluded: CSV, TSV, SYLK.

Then encoding is assumed to be __UTF-8 only__, unless the format specification says otherwise.

Better __PDF__ extraction quality requires the __xpdftotext__ plugin.

### Formats supported by Full Text search

|Extension |Kind of document                    |MIME Type
|----------|------------------------------------|----------------------------------------------------------------------------
|DOC       |Word 97-2003 / DOS Word             |[application/msword](https://www.iana.org/assignments/media-types/application/msword)
|DOCM      |Word 2007-365 document+macro        |[application/vnd.ms-word.document.macroEnabled.12](https://www.iana.org/assignments/media-types/application/vnd.ms-word.document.macroEnabled.12)
|DOCX      |Word 2007-365 document              |[application/vnd.openxmlformats-officedocument.wordprocessingml.document](https://www.iana.org/assignments/media-types/application/vnd.openxmlformats-officedocument.wordprocessingml.document)
|DOT, WPT  |Word 97-2003 / DOS Word Template    |[application/msword](https://www.iana.org/assignments/media-types/application/msword)
|DOTM      |Word 2007-365 template+macro        |[application/vnd.ms-word.template.macroEnabled.12](https://www.iana.org/assignments/media-types/application/vnd.ms-word.template.macroEnabled.12)
|DOTX      |Word 2007-365 template              |[application/vnd.openxmlformats-officedocument.wordprocessingml.template](https://www.iana.org/assignments/media-types/application/vnd.openxmlformats-officedocument.wordprocessingml.template)
|EPUB      |Electronic publication              |[application/epub+zip](https://www.iana.org/assignments/media-types/application/epub+zip)
|FODT      |ODF XML Text Document Flat          |
|HTML, HTML|HyperText Markup Language           |[text/html](https://www.iana.org/assignments/media-types/text/html)
|MHT, MHTML|Multipart HTML                      |[multipart/related](https://www.iana.org/assignments/media-types/multipart/related), multipart/alternative, multipart/x-mimearchive, multipart/mixed, message/rfc822
|ODT       |ODF Text Document                   |[application/vnd.oasis.opendocument.text](https://www.iana.org/assignments/media-types/application/vnd.oasis.opendocument.text)
|OTT       |ODF Text Template                   |[application/vnd.oasis.opendocument.text-template](https://www.iana.org/assignments/media-types/application/vnd.oasis.opendocument.text-template)
|PDF       |Portable Document Format            |[application/pdf](https://www.iana.org/assignments/media-types/application/pdf)
|RTF       |Rich Text Format 1.9.1              |[application/rtf](https://www.iana.org/assignments/media-types/application/rtf) or [text/rtf](https://www.iana.org/assignments/media-types/text/rtf)
|TXT, others|Plain text                         |text/plain
|XHTML     |Extensible HyperText Markup Language|[application/xhtml+xml](https://www.iana.org/assignments/media-types/application/xhtml+xml)
|XML       |Extensible Markup Language          |[application/xml](https://www.iana.org/assignments/media-types/application/xml) or [text/xml](https://www.iana.org/assignments/media-types/text/xml)

### Formats unsupported by Full Text search

Many old or rare office suite formats will not be directly supported. They are appointed to remove any ambiguity.
Consider converting them to DOCX or ODF format before attaching them.

DRM protected ebooks and password protected documents are not supported.

|Extension |Kind of document                    |MIME Type
|----------|------------------------------------|----------------------------------------------------------------------------
|ABW, ZABW |AbiWord Document                    |application/x-abiword
|CWK       |ClarisWorks/AppleWorks Document     |
|FB2       |FictionBook 2.0                     |
|FODP      |ODF Presentation Flat               |
|HWP       |Hangul WP 97                        |
|KWD       |KWord                               |[application/vnd.kde.kword](https://www.iana.org/assignments/media-types/application/vnd.kde.kword)
|LRF       |BroadBand Ebook                     |
|LWP       |Lotus WordPro                       |[application/vnd.lotus-wordpro](https://www.iana.org/assignments/media-types/application/vnd.lotus-wordpro)
|MAN, MDOC |Manpage, mandoc                     |[text/troff](https://www.iana.org/assignments/media-types/text/troff)
|MD        |Markdown                            |[text/markdown](https://www.iana.org/assignments/media-types/text/markdown)
|MW, MCW   |MacWrite Document                   |
|MWD       |Mariner Mac Write Classic           |
|ODP       |ODF Presentation                    |[application/vnd.oasis.opendocument.presentation](https://www.iana.org/assignments/media-types/application/vnd.oasis.opendocument.presentation)
|OTP       |ODF Presentation Template           |[application/vnd.oasis.opendocument.presentation-template](https://www.iana.org/assignments/media-types/application/vnd.oasis.opendocument.presentation-template)
|PAGES     |Apple Pages                         |
|PDB       |PalmDoc                             |
|PDB       |Plucker eBook                       |
|POTM      |PowerPoint 2007-365 Template+macro  |[application/vnd.ms-powerpoint.template.macroEnabled.12](https://www.iana.org/assignments/media-types/application/vnd.ms-powerpoint.template.macroEnabled.12)
|POTX      |PowerPoint 2007-365 Template        |[application/vnd.openxmlformats-officedocument.presentationml.template](https://www.iana.org/assignments/media-types/application/vnd.openxmlformats-officedocument.presentationml.template)
|PPT       |PowerPoint 97-2003                  |[application/vnd.ms-powerpoint](https://www.iana.org/assignments/media-types/application/vnd.ms-powerpoint)
|PPTM      |PowerPoint 2007-365 +macro          |[application/vnd.ms-powerpoint.presentation.macroEnabled.12](https://www.iana.org/assignments/media-types/application/vnd.ms-powerpoint.presentation.macroEnabled.12)
|PPTX      |PowerPoint 2007-365                 |[application/vnd.openxmlformats-officedocument.presentationml.presentation](https://www.iana.org/assignments/media-types/application/vnd.openxmlformats-officedocument.presentationml.presentation)
|PS, EPS, AI |PostScript                        |[application/postscript](https://www.iana.org/assignments/media-types/application/postscript)
|SCD, SLA  |Scribus Document                    |[application/vnd.scribus](https://www.iana.org/assignments/media-types/application/vnd.scribus)
|SDD       |StarOffice presentation             |
|SDW       |StarOffice Document                 |
|STI       |OpenOffice.org 1.0 Presentation Template |
|STW       |OpenOffice.org 1.0 Text Template    |[application/vnd.oasis.opendocument.presentation-template](https://www.iana.org/assignments/media-types/application/vnd.oasis.opendocument.presentation-template)
|SXI       |OpenOffice.org 1.0 Presentation     |
|SXW       |OpenOffice.org 1.0 Text Document    |[application/vnd.oasis.opendocument.presentation](https://www.iana.org/assignments/media-types/application/vnd.oasis.opendocument.presentation)
|TEI       |Text Encoding Initiative            |[application/tei+xml](https://www.iana.org/assignments/media-types/application/tei+xml)
|TEX, LATEX|TeX, LaTeX                          |
|TEXI      |TexInfo File                        |
|TROFF, ROFF|Groff, Roff, Troff                 |[text/troff](https://www.iana.org/assignments/media-types/text/troff)
|UOF, UOT  |Unified Office Text                 |
|UOP       |Unified Office presentation         |
|WML       |Wireless Mark-up Language           |[text/vnd.wap.wml](https://www.iana.org/assignments/media-types/text/vnd.wap.wml)
|WMLC      |Wireless Mark-up Language           |[application/vnd.wap.wmlc](https://www.iana.org/assignments/media-types/application/vnd.wap.wmlc)
|WN        |WriteNow Document                   |
|WPD       |Wordperfect                         |[application/vnd.wordperfect](https://www.iana.org/assignments/media-types/application/vnd.wordperfect) or [application/wordperfect5.1](https://www.iana.org/assignments/media-types/application/wordperfect5.1)
|WPS       |Microsoft Works                     |[application/vnd.ms-works](https://www.iana.org/assignments/media-types/application/vnd.ms-works)
|WRI       |Microsoft Write                     |application/mswrite
|XPS, OXPS |XML Paper Specification             |[application/vnd.ms-xpsdocument](https://www.iana.org/assignments/media-types/application/vnd.ms-xpsdocument)
