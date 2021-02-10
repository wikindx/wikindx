+++
title = "CMS API/Access"
date = 2021-01-30T00:08:41+01:00
weight = 5
chapter = true
#pre = "<b>1. </b>"
+++

               --o Content Management System hooks o--

 ---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---

WIKINDX v5, 2016 -- Added language parameter.
WIKINDX v4, 2011 -- Part 2 added below.

This is for users wishing to display various output from a WIKINDX
database into a CMS (Content Management System). It requires some
minimal configuration in the WIKINDX admin panel.

The following is required in the php.ini of the CMS system if you use   
file_get_contents() as in the examples below: allow_url_fopen = On      

If you cannot do this, you will need to use the PHP extension           
CURL as a replacement for file_get_contents(URL). Some help in          
using CURL for use with WIKINDX's CMS hooks may be found at:            
http://www.phpwcms.de/forum/viewtopic.php?t=15067                       

This README is in three parts. PART 1 deals with the display of single
or multiple resources from a WIKINDX in a CMS. PART 2 deals with sending
an encoded complex SQL SELECT statement to WIKINDX from the CMS. PART 3
deals with the parsing and formatting of citations in a block of text
sent from the CMS to the WIKINDX.


/////*****//////////*****//////////*****//////////*****/////

						PART 1

  DISPLAYING SINGLE OR MULTIPLE WIKINDX RESOURCES IN A CMS
  
/////*****//////////*****//////////*****//////////*****/////

In all cases, the return from WIKINDX comprises one or more resources
matching the queryString parameter you have set.

*************************
  INTERROGATING WIKINDX
*************************

NB.

1/ The following assumes the path to the WIKINDX is                     
http://localhost/wikindx/ -- if not, adjust the path accordingly.      

2/ 'action' and 'id' are required and must have valid values. 'action'
may be 'getResource', 'getCategory', 'getKeyword', 'getCreator',
'getPublisher', 'getCollection', 'getAbstract', 'getNotes', 'getQuote', 
'getParaphrase', 'getMusing', 'getRecent'  ('id' is not required
for this last one). The 'id' you wish to use may be found directly
in WIKINDX (if you've set your WIKINDX Preferences to view the CMS
pop-up window) or by browsing the MySQL database using a client such as
PhpMyAdmin.

3/ 'limit' is optional. e.g. '&limit=10' in the queryString will limit
the results of any list to 10. The default is unlimited. 'limit' will be
ignored if action = 'getResource'.

4/ 'order' is optional. e.g. '&order=year' in the queryString will
display a list ordered by publication year. Possible values are
'creator', 'year', 'title', and 'timestamp'. The default is 'creator'.

5/ When using 'getKeyword', 'getCategory' or 'getCreator', you may have
a further parameter of '&sqlMethod=and' which allows you to return
results, for example, where each resource belongs to all requested
categories. By default, the resources in the result match any one of the
requested categories. Ditto for keywords and creators.

6/ You can select the bibliographic style used by adding, for example,
the following to the querystring '&bibStyle=HARVARD'. Without this, the
default bibliographic style set in admin panel will be used.

7/ You can select the language used by adding, for example,
the following to the querystring '&language=en'. Without this, the
preferred language (provided by \UTILS\determine_locale()) will be used.
The language set is the name of a folder in components/languages/

8/ When getting quotes and paraphrases, only public comments are returned
and only public musings are returned when getting musings.

EXAMPLES:

To display a single resource ('id' == resource ID):
   $string = file_get_contents('http://localhost/wikindx/cmsprint.php?action=getResource&id=242');

To display a category list ('id' == category ID):
   $string = file_get_contents('http://localhost/wikindx/cmsprint.php?action=getCategory&id=4');

To display a subcategory list ('id' == subcategory ID):
   $string = file_get_contents('http://localhost/wikindx/cmsprint.php?action=getSubcategory&id=4');

To display a keyword list ('id' == keyword ID):
   $string = file_get_contents('http://localhost/wikindx/cmsprint.php?action=getKeyword&id=32');

To display a creator list ('id' == creator ID):
   $string = file_get_contents('http://localhost/wikindx/cmsprint.php?action=getCreator&id=143');

To display a publisher list ('id' == publisher ID):
   $string = file_get_contents('http://localhost/wikindx/cmsprint.php?action=getPublisher&id=42');

To display a collection list ('id' == collection ID):
   $string = file_get_contents('http://localhost/wikindx/cmsprint.php?action=getCollection&id=13');
   
To display an abstract ('id' == resource ID):
   $string = file_get_contents('http://localhost/wikindx/cmsprint.php?action=getAbstract&id=242');
   
To display all quotes and comments for a resource ('id' == resource ID):
   $string = file_get_contents('http://localhost/wikindx/cmsprint.php?action=getQuote&id=242');

To display a list of any of the above, use a comma-separated list of the appropriate ID numbers.  For example:
   $string = file_get_contents('http://localhost/wikindx/cmsprint.php?action=getResource&id=13,54,2,103,4');

To display up to 5 resources by two particular creators (where they are co-authors), ordered by publication year:
   $string = file_get_contents('http://localhost/wikindx/cmsprint.php?action=getCreator&id=4,5&limit=5&order=year&sqlMethod=and');

Additionally, you may also use the following ('order' will be ignored and, if no 'limit', the default is 10 resources returned):              

To display the 10 most recently added resources:
   $string = file_get_contents('http://localhost/wikindx/cmsprint.php?action=getRecent&limit=10');

To display at most 15 resources added within the last 5 days:
   $string = file_get_contents('http://localhost/wikindx/cmsprint.php?action=getRecent&limit=15&days=5');


***********************
  OUTPUT FROM WIKINDX  
***********************

The output from cmsprint.php ($string above) is a string representing
a base64_encoded, serialized PHP array (unless no resources have been
found in which case $string will be FALSE). To access this array, you
will need to use the following:

   if($string)
      $array = unserialize(base64_decode($string));

The contents of $array may be like the following example:

	[13] => Back, M., &amp; Des, D. (1996). Micro-narratives in sound design: Context and caricature in waveform manipulation. Retrieved March 12, 2004, from <a href="http://www2.hku.nl/~audiogam/ag/articles/micronaratives.htm" target="_blank">http://www2.hku.nl/~aud&nbsp;...&nbsp;cles/micronaratives.htm</a>.
	[54] => Carr, D. (2003). Play dead: Genre and affect in silent hill and planescape torment. <em>Game Studies</em><em>, 3</em>(1). Retrieved September 16, 2003, from <a href="http://www.gamestudies.org/0301/carr/" target="_blank">http://www.gamestudies.org/0301/carr/</a>. 
	[2] => Chion, M. (1992). Wasted words. In R. Altman (Ed.), <em>Sound Theory Sound Practice</em> (pp. 104&ndash;110). New York: Routledge.
	[103] => Chion, M. (1994). <em>Audio-vision: Sound on screen</em> (C. Gorbman, Trans.) New York: Columbia University Press.
	[4] => Truppin, A. (1992). And then there was sound: The films of Andrei Tarkovsky. In R. Altman (Ed.), <em>Sound Theory Sound Practice</em> (pp. 235&ndash;248). New York: Routledge.

Note that, depending upon the bibliographic style chosen, the results
may contain HTML tags and entities (as in the above example). The key of
each array member is the resource ID.

You can then loop through this array in order to format and print the
individual items as you wish. An example might be:

   foreach($array as $resourceId => $item)
      print $item . "<br>";

The output for a command such as 
$string = file_get_contents('http://localhost/wikindx/cmsprint.php?action=getQuote&id=242');
, after processing as above, may be an array 
such as:

	[242] => Array
			(
				[quotes] => Array
					(
						[0] => Array 
						(
							[quote] => Quote 1 
							[keywords] => 
							[details] => p.1 
							[comments] => Array ( )
						)
						[1] => Array
						(
							[quote] => Quote 2 
							[keywords] => Array
							(
								[0] => 04 - Alpes-de-Haute-Provence
							)
							[details] => p.2 
							[comments] => Array
							(
								[0] => Array
								(
									[comment] => Comment 2 
									[timestamp] => 2013-01-19 09:54:15
								)
							)
						)
					)
				)
			) 
	
/////*****//////////*****//////////*****//////////*****/////

						PART 2

  DISPLAYING MULTIPLE WIKINDX RESOURCES IN A CMS FROM SQL
  
/////*****//////////*****//////////*****//////////*****/////


If the WIKINDX admin allows it, you can send a complex 
SQL statement to WIKINDX and receive an array of resources in return.   
This array is in the same format as described in PART 1. The SQL        
statement is a base64-encoded string that can be copied and pasted from 
the WIKINDX 'cms' link if you have enabled this link in your WIKINDX    
preferences. The 'cms' link is displayed when viewing WIKINDX lists.    

1/ Formulate your WIKINDX list first.

2/ Click on the 'cms' link.

3/ Write the provided string to a temporary file on your web server.

4/ Contact the WIKINDX and give it the address where $text is stored.

5/ Process $string as an array in your CMS using
unserialize(base64_decode($string)).

You can select the bibliographic style used by adding, for example, the
following to the querystring '&bibStyle=HARVARD'. Without this, the
default bibliographic style set in admin panel will be used. This is
the only extra parameter accepted.

Example (in your CMS code and assuming both CMS and WIKINDX are on localhost):

   $string = file_get_contents(http://localhost/wikindx/cmsprint.php?action=parseSql&address=http://localhost/cms/temp/tempfile.txt);
   if($string)
   {
      $array = unserialize(base64_decode($string));
      print_r($array);
   }
   else
      print 'No resources';

/////*****//////////*****//////////*****//////////*****/////

						PART 3
					
   PARSING AND FORMATTING CITATIONS IN TEXT USING WIKINDX
					
/////*****//////////*****//////////*****//////////*****/////


WIKINDX may be used to format a series of citations within a block of
CMS text that uses the CMS replacement tags. The CMS replacement tag may
be automatically generated for pasting into the CMS text by using the
WIKINDX CMS pop-up window if you have set the CMS hyperlink to display
in your WIKINDX preferences. The citation style, by default, uses the
style set in WIKINDX admin panel. At the end of this file
is example PHP code for formatting citations in a block of text in the
CMS phpWcms.

If your CMS text block containing the replacement tags is:

Chion states that:  "Blah, blah, blah" {ADD_BIB_ITEM:3:314} but in a later article corrects this by saying:  "Bleah, bleah, bleah" {ADD_BIB_ITEM:89:22-23}.

the output from WIKINDX (assuming the current style is APA) may be:

Chion (1999) states that:  "Blah, blah, blah" (p.314) but in a later article corrects this by saying:  "Bleah, bleah, bleah" (Chion 2001, 22-23).

The replacement tag may have further parameters: it is recommended to
use the CMS replacement tag generator in WIKINDX for greatest accuracy.

There are several steps in creating the replacement tag code in your
CMS, sending the text block to WIKINDX and receiving it back:

1/ For efficiency, first check to see if there are any replacement tags
in the text block and, if not, return immediately. For example:

   if(strpos($text, "{ADD_BIB_ITEM:") === FALSE)
      return;

2/ Use php_replace() to reformat your particular CMS replacement        
tags to a format WIKINDX will recognize (using [cite]...[/cite]).       
For example, with the CMS replacement tags in the example above,        
you should use: $text = preg_replace("/\{ADD_BIB_ITEM:(.*)\}/Ui",       
"[cite]$1[/cite]", $text);                                              

Using the example above, this code will produce:

Chion states that:  "Blah, blah, blah" [cite]3:314[/cite] but in a later article corrects this by saying:  "Bleah, bleah, bleah" [cite]89:22-23[/cite].

3/ Write the new $text to a temporary file on your web server.

4/ Contact the WIKINDX and give it the address where $text is stored.
For example (assuming both CMS and WIKINDX are on localhost):

   $string = file_get_contents(http://localhost/wikindx/cmsprint.php?action=parseText&wikindxLink=1&address=http://localhost/cms/temp/tempfile.txt);

5/ Print $string back to your CMS.


Note the following:

1/ The querystring parameter 'wikindxLink=1' is optional and, if
set, the formatted citations in $string will be hyperlinked to the
appropriate WIKINDX resource (best used where CMS and WIKINDX are on the
same host).

2/ You can select the bibliographic style used by adding, for example,
the following to the querystring '&bibStyle=HARVARD'. Without this, the
default bibliographic style set in admin panel will be used.

3/ $string will contain not only the formatted text block but will
also contain an appended bibliography of the citations in the text.
Where the current style is an in-text citation style such as APA,
this bibliography is separated by '<br><br>' -- if your CMS uses
similar HTML code for line breaks and you wish to be able to split this
bibliography from the text for some reason, you should replace all
occurrences of '<br>' in $text with a unique string prior to sending
it to the WIKINDX and then split the result on '<br><br>' before
reverting that unique string back to '<br>' before finally printing
the result back to the CMS. If the current style is a style using
footnotes such as Chicago, the WIKINDX result contains the formatted
text, the required footnote citations and the appended bibliography.
Splitting the WIKINDX result on '<br><br>' will produce an array
of three members, the first being the text block, the second being the
footnotes and the third being the bibliography. For example, you might
use the following code:

$text = preg_replace("/\{ADD_BIB_ITEM:(.*)\}/Ui", "[cite]$1[/cite]", $text);
$text = str_replace('<br>', 'IAMAUNIQUESTRING', $text);
<... write $text to cms/temp/tempfile.txt ...>
$string = file_get_contents("http://localhost/wikindx/cmsprint.php?action=parseText&address=http://localhost/cms/temp/tempfile.txt");
$split = preg_split("/(<br \/>){2,2}/i", $string);
$text = str_replace('IAMAUNIQUESTRING', '<br>', $split[0]);
$footnotes = $split[1];
$bibliography = $split[2];


The following is example code (kindly provided by John Weare) for
formatting citations in a block of text in the CMS phpWcms:

>>>>>>>>>>>>>>>>>>>>>>>>>>>>
<?php
// Example PHP code developed to produce WIKINDX citations in phpWcms.
// This code assumes adding additional css stylesheet definitions
// citation, notes_wrapper, notes_container, bib_wrapper, and bib_container<br>
// be sure to chmod temp/ directory to 777<br>
// {ADD_BIB_ITEM:xxx} inserts inline citations from wikindx where xxx is the WIKINDX resource ID
// {SHOW_FULL_BIB} outputs full bibliography<br>
// PLEASE NOTE!! <!--MAIN_START//--> AND <!--MAIN_END//--> opening and closing tags need to be in page source
// sustitute instance of http://www.domain.com/ with actual domain or localhost
function citeText($contentString)
{
	// Temp file must be written with unique filename espeically
	// in a multi-user environment; here we use the session ID
	session_start();
	$session_handle = session_id();
	// directory where temporary file will be stored - chmod 777
	$tempFileSubDirectory = 'temp/';
	$tempFilePathName = $tempFileSubDirectory . sha1($session_handle) . ".txt";
	$oldPathFileName = $tempFilePathName;
	// *******************
	$serverPathToWiki = "http://www.domain.com/wikindx/";
	$serverPathToTempFile = "http://www.domain.com/" . $tempFilePathName;
	// ******************* create handle
 	if (!$handle = fopen($tempFilePathName, 'w')) {
         echo "Cannot open file ($tempFilePathName)"; // or handle your error
         exit;
    }
	// The return from WIKINDX separates components with <br> so replace
	// instances of that in the source text with a temporary string which
	// will be re-replaced later on.
	$contentString = str_replace('<br>', '_TEMP_', $contentString);
	// Convert this CMS' replacement tag structure with the [cite] tags that WIKINDX recognises. 
	$contentString = preg_replace("/\{ADD_BIB_ITEM:(.*)\}/Uiu", "[cite]$1[/cite]", $contentString);
	if (fwrite($handle, $contentString) === FALSE) {
		echo "Cannot write to file ($filename)"; // or handle your error
		exit;
	}
	fclose($handle);
	// **************************************
	$wikiSendString = $serverPathToWiki."cmsprint.php?action=parseText&address=" . $serverPathToTempFile;
	$wikiOutputString = file_get_contents($wikiSendString);
	// **************************************
	// Split WIKINDX return into component parts. If using a footnote-style citation,
	// such as chicago, $split will have three members (cited text, footnotes,
	// bibliography), otherwise $split will comprise two members (cited text
	// and bibliography).  The HTML tags such as 'div' are purely examples as
	// to how to markup the WIKINDX return.
	$split = preg_split("/(<br \/>){2,2}/iu", $wikiOutputString);
	$citedText = str_replace('_TEMP_', '<br>', $split[0]);
	$citedText = stripslashes($citedText);
	$footnotes = $bibliography = FALSE;
	// separate footnotes
	if (count($split) == 3) {
		$footnotes =  str_replace('<br>', '</p><p>', $split[1]);
		$footnotes = "<div id=\"notes_wrapper\"><div id=\"notes_container\"><p>" . $footnotes . "</div></div>";
		$bibliography = str_replace('<br>', '</p><p>', $split[2]);
		$bibliography = "<div id=\"bib_wrapper\"><div id=\"bib_container\"><p></p><p></p><p>" . $bibliography . "</div></div>";
	} else {
		$bibliography = str_replace('<br>', '</p><p>', $split[1]);
		$bibliography = "<div id=\"bib_wrapper\"><div id=\"bib_container\"><p>" . $bibliography . "</div></div>";
	}
	// remove temp file
	unlink($oldPathFileName);
	
	// phpWcms tag indicating whether and where to print the full bibliography.
	// Otherwise, you could use $citedText . 'br /' . $bibliography.
	if (!(strpos($citedText, '{SHOW_FULL_BIB}') === false)) {
		$citedText = str_replace('{SHOW_FULL_BIB}', '', $citedText);
		return $citedText . $footnotes ."<div style=\"height:18px;\">&nbsp;</div>" . $bibliography;
	} else {
		return $citedText . $footnotes;
	}
}

	// ADD_BIB_ITEM replacement.
	// <!--MAIN_START//--> and <!--MAIN_END//--> are used by phpWcms to indicate
	// the printed text component of the HTML page; we extract and parse its contents
	// for greater efficiency. $content['all'] is the text block we are parsing.
if (!(strpos($content["all"], '{ADD_BIB_ITEM:') === false)) {
	$lPattern = preg_quote("<!--MAIN_START//-->");
	$rPattern = preg_quote("<!--MAIN_END//-->");
	$content['all'] = preg_replace("#($lPattern)(.*)($rPattern)#seu", "'\\1' . citeText('\\2') . '\\3'", $content['all']);
} 
?>
<<<<<<<<<<<<<<<<<

---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---:::---


--
Mark Grimshaw-Aagaard
The WIKINDX Team 2020
sirfragalot@users.sourceforge.net
