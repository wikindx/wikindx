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

* Partial word searches are the default unless the search term is an exact phrase
* You can use control words as noted below
* The restrictions noted below on searches using the abstract and note fields pertain here


## Advanced Search

* Complex composite search and select operations may be constructed by adding new fields. Some of these fields can be searched for words or phrases, some can be selected within, and some make use of numerical comparison
* The abstract and note fields are searched on in FULLTEXT BOOLEAN mode. This is a quick and efficient method for searching over potentially large text fields but it dosn't support Chinese and Japanese characters. In order to do this, your MySQL server must have the requisite parser: see [Full-Text Restrictions](https://dev.mysql.com/doc/refman/8.0/en/fulltext-restrictions.html) in the MySQL documentation. Wildcard characters will be ignored for these two fields
* Document searches can be performed on resource attachments if they are of type DOC, DOCX, ODT, PDF, RTF, or TXT – searches are carried out on the cached versions of attachments. If you use the first select field to search on attachments any **NOT** in the search field will be ignored. Attachment searches are not filtered for the list of ignored words (see below)
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
