/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */
 
/**
* Provide word processor statistics
* Author:  Mark Grimshaw-Aagaard
* Version:  1.2
* Date:  2019-12-31
*/

var wordCountFt = 0;
var charCountFtEx = 0;
var charCountFtIn = 0;
var charCountFtAll = 0;
var whitespaceFt = 0;
var punctuationFt = 0;
var wordCountCite = 0;
var charCountCiteEx = 0;
var charCountCiteIn = 0;
var charCountCiteAll = 0;
var whitespaceCite = 0;
var punctuationCite = 0;

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('wikindxWPStats');

	tinymce.create('tinymce.plugins.WikindxWPStatsPlugin', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceWikindxWPStats');
			ed.addCommand('mceWikindxWPStats', function() {
				wikindxWPStats();
			});

			// Register stats button
			ed.addButton('wikindxWPStats', {
				title : 'wikindxWPStats.desc',
				cmd : 'mceWikindxWPStats',
				image : url + '/img/stats.png'
			});

			// Add a node change handler, selects the button in the UI when a image is selected
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('wikindxWPStats', n.nodeName == 'IMG');
			});
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'WikindxWPStats plugin',
				author : 'Mark Grimshaw-Aagaard',
				authorurl : 'https://wikindx.sourceforge.io',
				infourl : '',
				version : "1.1"
			};
		},
	});

	// Register plugin
	tinymce.PluginManager.add('wikindxWPStats', tinymce.plugins.WikindxWPStatsPlugin);
})();


// Provide some basic statistics on what's typed
function wikindxWPStats() {
	var justText;
	var pageCount = false;
	justText = tinyMCE.get('paperText').getContent({format : 'text'});
// trim
	justText = trim(justText);
// Handle paragraphs first - different browsers do different things for HTML in design mode!
// blank paragraphs in FF and IE come in as '<p>&nbsp;</p>' -- remove them in addition to newline characters
// sometimes the blank paragraphs come in as <p><br></p>....
	justText = justText.replace(/\r\n|\n|\r/g, '');
	justText = justText.replace(/\r|\n|<p>&nbsp;<\/p>|<p>\s*<br>\s*<\/p>/ig, '');

// Remove all paragraph tags and prepend &nbsp; to start of paragraph text to ensure that we have a word break here
	justText = justText.replace(/<p.*?>(.*?)<\/p>|(<br>\s*){2}/ig, "__WIKINDX__WORDBREAK__$1");
// remove HTML code
//	justText = justText.replace(/(<([^>]+)>)/ig,"");
	justText = justText.replace(/<.*?>|<\/.*?>|<.*?>(.*?)<\/.*?>/g, "$1");
// user entered extra space is &nbsp; (i.e. more than one space in sequence) - any other space not between user text is 
// browser-specific and related to the HTML code so remove it.
// Replace single whitespace with &nbsp; and remove all other space
	justText = justText.replace(/\s{1,1}/g, "&nbsp;");
	justText = justText.replace(/\s/g, '');
// Ensure we have no __WIKINDX__WORDBREAK__ or &nbsp; at either the beginning or the end of the text
	justText = justText.replace(/^__WIKINDX__WORDBREAK__|__WIKINDX__WORDBREAK__$|^&nbsp;|&nbsp;$|^&nbsp;__WIKINDX__WORDBREAK__|__WIKINDX__WORDBREAK__&nbsp;$/g, '');
// trim again
	justText = trim(justText);
	if(justText == '')
	{
		alert("Nothing to count");
		return;
	}
	var justTextFt = justText;
// Replace all footnote and endnote tags with single space
	justText = justText.replace(/\[footnote\].*?\[\/footnote\]/g, '');
	var justTextCite = justText;
// Remove all citation tags
	justText = justText.replace(/\[cite\].*?\[\/cite\]/g, '');
// We now have text in a format suitable for parsing into words, characters etc.  NB - any string enclosed by &...; is a single character
// Count &nbsp; as whitespace
	var whitespace = justText.match(/&nbsp;|__WIKINDX__WORDBREAK__/g);
	if(whitespace == null)
		whitespace = 0;
	else
		whitespace = whitespace.length;
// Count words - words is loosely defined...
	var wc = justText.split(/&nbsp;{1}|__WIKINDX__WORDBREAK__/g);
	var wordCount = 0;
	for(var i = 0; i < wc.length; i++)
	{
		if(wc[i] != '')
			++wordCount;
	}
	footnotes(justTextFt);
	citations(justTextCite);
// Count no. lines in order to estimate no. pages @ 40 lines/page and 15 words/line.  Create page count if lineCount > 1000
	if(wordCount > 1000)
		pageCount = Math.round(wordCount/(40 * 15));
// remove all &nbsp; whitespace and temporary wordbreak chars
	justText = justText.replace(/&nbsp;|__WIKINDX__WORDBREAK__/g, '');
// now replace all &...; chars with single character `
	justText = justText.replace(/&.*?;/g, '`');
// count punctuation then remove -- keep to grammatical punctuation
	var punctuation = justText.match(/[\.,\:;\?\!`'"]/g);
	if(punctuation == null)
		punctuation = 0;
	else
		punctuation = punctuation.length;
	justText = justText.replace(/[\.,\:;\?\!`'"]/g, '');
// Now count all characters remaining
	var charCountEx = justText.length;
// Total of all characters excluding whitespace but including punctuation
	var charCountIn = charCountEx + punctuation;
// Total of all characters
	var charCountAll = charCountIn + whitespace;
	if(charCountEx == 0)
		alert("Nothing to count");
	else if(pageCount)
		alert("Character Count (excluding punctuation & space) = " + charCountEx + "\n" +
			"Character Count (including punctuation excluding space) = " + charCountIn + "\n" +
			"Character Count (including punctuation & space) = " + charCountAll + "\n" +
			"Word Count (excluding cite tags and footnotes) = " + wordCount + "\n" +			
			"Word Count (excluding cite tags) = " + wordCountFt + "\n" +					
			"Approximate Page Count = " + pageCount + " @40 lines/page, 15 words/line\n" +
			"\nPunctuation is: . , : ; ` ' \" ! ? \n"
			);
	else
	{
		var totalWordCount = wordCount + wordCountFt + wordCountCite;
		var totalCharCountEx = charCountEx + charCountFtEx + charCountCiteEx;
		var totalCharCountIn = charCountIn + charCountFtIn + charCountCiteIn;
		var totalCharCountAll = charCountAll + charCountFtAll + charCountCiteAll;
		alert("Excluding citations and footnotes:\n" + 
			"   Character Count (excluding punctuation & space) = " + charCountEx + "\n" +
			"   Character Count (including punctuation, excluding space) = " + charCountIn + "\n" +
			"   Character Count (including punctuation & space) = " + charCountAll + "\n" +
			"   Word Count = " + wordCount + 
			"\n\n" + 		
			"Footnotes:\n" + 
			"   Character Count (excluding punctuation & space) = " + charCountFtEx + "\n" +
			"   Character Count (including punctuation, excluding space) = " + charCountFtIn + "\n" +
			"   Character Count (including punctuation & space) = " + charCountFtAll + "\n" +			
			"   Word Count = " + wordCountFt + 
			"\n\n" + 		
			"Citations:\n" + 
			"   Character Count (excluding punctuation & space) = " + charCountCiteEx + "\n" +
			"   Character Count (including punctuation, excluding space) = " + charCountCiteIn + "\n" +
			"   Character Count (including punctuation & space) = " + charCountCiteAll + "\n" +			
			"   Word Count = " + wordCountCite + 
			"\n\n" + 		
			"Totals:\n" + 
			"   Character Count (excluding punctuation & space) = " + totalCharCountEx + "\n" +
			"   Character Count (including punctuation, excluding space) = " + totalCharCountIn + "\n" +
			"   Character Count (including punctuation & space) = " + totalCharCountAll + "\n" +			
			"   Word Count = " + totalWordCount + 
			"\n\n" + 	
			"Punctuation is: . , : ; ` ' \" ! ? \n"
			);
	}
}

/**
* Footnotes statistics
*/
function footnotes(justTextFt)
{
	var wc;
	var ftPhrase;
	wordCountFt = 0;
	charCountFtEx = 0;
	charCountFtIn = 0;
	charCountFtAll = 0;
	whitespaceFt = 0;
	punctuationFt = 0;
	var ft = justTextFt.match(/\[footnote\](.*?)\[\/footnote\]/g);
	if(ft == null)
		return;
	for(var i = 0; i < ft.length; i++)
	{
		if(ft[i] != '')
		{
			ftPhrase = ft[i].match(/\[footnote\](.*?)\[\/footnote\]/);
			footnotesChars(ftPhrase[1]);
			wc = ftPhrase[1].split(/&nbsp;{1}|__WIKINDX__WORDBREAK__/g);
			for(var x = 0; x < wc.length; x++)
			{
				if(wc[x] != '')
					++wordCountFt;
			}
		}
	}
}

/**
* Footnotes characters + punctuation
*/
function footnotesChars(text)
{
	var whitespace = text.match(/&nbsp;|__WIKINDX__WORDBREAK__/g);
	if(whitespace == null)
		whitespace = 0;
	else
		whitespaceFt += whitespace.length;
// remove all &nbsp; whitespace and temporary wordbreak chars
	text = text.replace(/&nbsp;|__WIKINDX__WORDBREAK__/g, '');
// now replace all &...; chars with single character `
	text = text.replace(/&.*?;/g, '`');
// count punctuation then remove -- keep to grammatical punctuation
	var punctuation = text.match(/[\.,\:;\?\!`'"]/g);
	if(punctuation == null)
		punctuation = 0;
	else
		punctuationFt += punctuation.length;
	text = text.replace(/[\.,\:;\?\!`'"]/g, '');
// Now count all characters remaining
	charCountFtEx += text.length;
// Total of all characters excluding whitespace but including punctuation
	charCountFtIn = charCountFtEx + punctuationFt;
// Total of all characters
	charCountFtAll = charCountFtIn + whitespaceFt;
}

/**
* Citations statistics
*/
function citations(justTextCite)
{
	var wc;
	var citePhrase;
	wordCountCite = 0;
	charCountCiteEx = 0;
	charCountCiteIn = 0;
	charCountCiteAll = 0;
	whitespaceCite = 0;
	punctuationCite = 0;
	var cite = justTextCite.match(/\[cite\](.*?)\[\/cite\]/g);
	if(cite == null)
		return;
	for(var i = 0; i < cite.length; i++)
	{
		if(cite[i] != '')
		{
			citePhrase = cite[i].match(/\[cite\](.*?)\[\/cite\]/);
			citationsChars(citePhrase[1]);
			wc = citePhrase[1].split(/&nbsp;{1}|__WIKINDX__WORDBREAK__/g);
			for(var x = 0; x < wc.length; x++)
			{
				if(wc[x] != '')
					++wordCountCite;
			}
		}
	}
}

/**
* Citations characters + punctuation
*/
function citationsChars(text)
{
	var whitespace = text.match(/&nbsp;|__WIKINDX__WORDBREAK__/g);
	if(whitespace == null)
		whitespace = 0;
	else
		whitespaceCite += whitespace.length;
// remove all &nbsp; whitespace and temporary wordbreak chars
	text = text.replace(/&nbsp;|__WIKINDX__WORDBREAK__/g, '');
// now replace all &...; chars with single character `
	text = text.replace(/&.*?;/g, '`');
// count punctuation then remove -- keep to grammatical punctuation
	var punctuation = text.match(/[\.,\:;\?\!`'"]/g);
	if(punctuation == null)
		punctuation = 0;
	else
		punctuationCite += punctuation.length;
	text = text.replace(/[\.,\:;\?\!`'"]/g, '');
// Now count all characters remaining
	charCountCiteEx += text.length;
// Total of all characters excluding whitespace but including punctuation
	charCountCiteIn = charCountCiteEx + punctuationCite;
// Total of all characters
	charCountCiteAll = charCountCiteIn + whitespaceCite;
}

// Removes leading and trailing spaces from the passed string. Also removes
// consecutive spaces and replaces it with one space. If something besides
// a string is passed in (null, custom object, etc.) then return the input.
function trim(inputString) {
   if (typeof inputString != "string") return inputString;
   var retValue = inputString;
   var ch = retValue.substring(0, 1);
	
   while (ch == " ") { // Check for spaces at the beginning of the string
      retValue = retValue.substring(1, retValue.length);
      ch = retValue.substring(0, 1);
   }
   ch = retValue.substring(retValue.length - 1, retValue.length);
	
   while (ch == " ") { // Check for spaces at the end of the string
      retValue = retValue.substring(0, retValue.length - 1);
      ch = retValue.substring(retValue.length - 1, retValue.length);
   }
	
	// Note that there are two spaces in the string - look for multiple spaces within the string
   while (retValue.indexOf("  ") != -1) {
		// Again, there are two spaces in each of the strings
      retValue = retValue.substring(0, retValue.indexOf("  ")) + 
		retValue.substring(retValue.indexOf("  ") + 1, retValue.length);
   }
   return retValue; // Return the trimmed string back to the user
}