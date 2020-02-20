<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/*****
* SUCCESS class (English)
*
* NOTE TO TRANSLATORS:
*           1/  '###' appearing anywhere in an array value will be replaced by text supplied by the core WIKINDX code.
*			2/  Do not change the key (the first part) of an array element.
*			3/  Ensure that each array element value is enclosed in double quotes "..." and is followed by a comma "...",
*			4/  Both the class name and the constructor name should be changed to match the (case-sensitive) name of
*				the folder your language files are in.  For example, if you are supplying a Klingon translation and
*				your languages/ folder is languages/kn/, the class and constructor name for the file SUCCESS.php
*				must both be SUCCESS_kn.
*
* NOTE TO DEVELOPERS:
*           1/  Any comments not preceeded by '///' at the start of the line will be ignored by the localization module.
*           2/  All comments should be on one line (no line breaks) and must start at the beginning of the line for the localization module to work.
*           3/  Comments must be of the form '/// notes_ This array does this' where 'notes' is the array key that the comment refers to.
*****/
class SUCCESS
{
    private $languageArray = [];
    
	function __construct()
	{
	    $this->languageArray = $this->loadArrays();
	}
/**
* Grab the message
*
* @param string $indexName
* @param string $extra Optional string that replaces '###' in the array element value string. Default is FALSE
* @param boolean $html Optional boolean for HTML printing (TRUE/default) or plain text (FALSE)
*
* @return string
*/
	public function text($indexName, $extra = FALSE, $html = TRUE)
	{
		$message = $this->internaltext($indexName);
		$message = preg_replace("/###/u", str_replace("\\", "\\\\", trim($extra . "")), $message);
		$message = stripslashes($message);
		
		if($html)
			return \HTML\p(UTF8::html_uentity_decode(trim($message)), "success", "center");
		else
			return trim($message);
	}
/**
* Grab the message
*
* @param string $arrayName
* @param string $indexName
*
* @return string
*/
	private function internaltext($indexName)
	{
	    $arrayName = "success";
		
		if(!array_key_exists($indexName, $this->languageArray[$arrayName]))
		{
		    debug_print_backtrace();
			die("<p>Message <strong>$indexName</strong> not found in section $arrayName of " . __CLASS__ . " class.</p>");
		}
		return $this->languageArray[$arrayName][$indexName];
	}
/**
 * Return English messages
 *
 * @return array
 */
	private function loadArrays()
	{
	    $domain = WIKINDX_LANGUAGE_DOMAIN_DEFAULT;
	    
		return array(
		    "success" => array(
				"noteAdd" => dgettext($domain, "Successfully added notes"),
				"noteEdit" => dgettext($domain, "Successfully edited notes"),
				"noteDelete" => dgettext($domain, "Successfully deleted notes"),
				"categoryKeywordAdd" => dgettext($domain, "Successfully added categories and keywords"),
				"categoryKeywordEdit" => dgettext($domain, "Successfully edited categories and keywords"),
				"categoryKeywordDelete" => dgettext($domain, "Successfully deleted categories and keywords"),
				"quoteAdd" => dgettext($domain, "Successfully added quote"),
				"quoteEdit" => dgettext($domain, "Successfully edited quote"),
				"quoteDelete" => dgettext($domain, "Successfully deleted quote"),
				"paraphraseAdd" => dgettext($domain, "Successfully added paraphrase"),
				"paraphraseEdit" => dgettext($domain, "Successfully edited paraphrase"),
				"paraphraseDelete" => dgettext($domain, "Successfully deleted paraphrase"),
				"musingAdd" => dgettext($domain, "Successfully added musing"),
				"musingEdit" => dgettext($domain, "Successfully edited musing"),
				"musingDelete" => dgettext($domain, "Successfully deleted musing"),
				"ideaAdd" => dgettext($domain, "Successfully added idea"),
				"ideaEdit" => dgettext($domain, "Successfully edited idea"),
				"ideaDelete" => dgettext($domain, "Successfully deleted idea"),
				"abstractAdd" => dgettext($domain, "Successfully added abstract"),
				"abstractEdit" => dgettext($domain, "Successfully edited abstract"),
				"abstractDelete" => dgettext($domain, "Successfully deleted abstract"),
				"config" => dgettext($domain, "Successfully configured WIKINDX"),
				"resourceAdd" => dgettext($domain, "Successfully added resource(s)"),
				"resourceEdit" => dgettext($domain, "Successfully edited resource(s)"),
				"resourceDelete" => dgettext($domain, "Successfully deleted resource(s)"),
				"categoryAdd" => dgettext($domain, "Successfully added category(s)"),
				"categoryEdit" => dgettext($domain, "Successfully edited category(s)"),
				"categoryDelete" => dgettext($domain, "Successfully deleted category(s)"),
				"languageAdd" => dgettext($domain, "Successfully added language"),
				"languageEdit" => dgettext($domain, "Successfully edited language"),
				"languageDelete" => dgettext($domain, "Successfully deleted language"),
				"subcategoryAdd" => dgettext($domain, "Successfully added subcategory(s)"),
				"subcategoryEdit" => dgettext($domain, "Successfully edited subcategory(s)"),
				"subcategoryDelete" => dgettext($domain, "Successfully deleted subcategory(s)"),
				"creator" => dgettext($domain, "Successfully edited creator"),
				"publisher" => dgettext($domain, "Successfully edited publisher"),
				"collection" => dgettext($domain, "Successfully edited collection"),
				"bibtexImport" => dgettext($domain, "Successfully uploaded BibTeX bibliography"),
				"endnoteImport" => dgettext($domain, "Successfully uploaded Endnote XML bibliography"),
				"keyword" => dgettext($domain, "Successfully edited keyword"),
				"keywordDelete" => dgettext($domain, "Successfully deleted keyword(s)"),
				"userAdd" => dgettext($domain, "Successfully added user(s)"),
				"userEdit" => dgettext($domain, "Successfully edited user(s)"),
				"userDelete" => dgettext($domain, "Successfully deleted user(s)"),
				"userBlock" => dgettext($domain, "Successfully blocked/unblocked user(s)"),
				"registerEmail" => dgettext($domain, "An email has been sent to you with further instructions"),
				"bibliographySet" => dgettext($domain, "Successfully set bibliography"),
				"bibliographyAdd" => dgettext($domain, "Successfully added bibliography"),
				"bibliographyEdit" => dgettext($domain, "Successfully edited bibliography"),
				"bibliographyDelete" => dgettext($domain, "Successfully deleted bibliography"),
				"usertagAdd" => dgettext($domain, "Successfully added user tag"),
				"usertagEdit" => dgettext($domain, "Successfully edited user tag"),
				"usertagDelete" => dgettext($domain, "Successfully deleted user tag"),
				"addBib" => dgettext($domain, "Successfully added item to bibliography"),
				"deleteFromBib" => dgettext($domain, "Successfully deleted item(s) from bibliography"),
				"notify" => dgettext($domain, "Successfully set email notification"),
				"newsAdd" => dgettext($domain, "Successfully added news"),
				"newsEdit" => dgettext($domain, "Successfully edited news"),
				"newsDelete" => dgettext($domain, "Successfully deleted news"),
				"fieldAdd" => dgettext($domain, "Successfully added custom database field"),
				"fieldEdit" => dgettext($domain, "Successfully edited custom database field"),
				"fieldDelete" => dgettext($domain, "Successfully deleted custom database field"),
				"attachAdd" => dgettext($domain, "Successfully added attachment"),
				"attachEdit" => dgettext($domain, "Successfully edited attachment"),
				"attachDelete" => dgettext($domain, "Successfully deleted attachment"),
				"urlAdd" => dgettext($domain, "Successfully added URL"),
				"urlEdit" => dgettext($domain, "Successfully edited URL"),
				"urlDelete" => dgettext($domain, "Successfully deleted URL"),
				"organized" => dgettext($domain, "Successfully organized resources"),
				"convertType" => dgettext($domain, "Successfully converted resource type"),
				"bookmark" => dgettext($domain, "Successfully added bookmark"),
				"bookmarkDelete" => dgettext($domain, "Successfully deleted bookmark(s)"),
				"keywordMerge" => dgettext($domain, "Successfully merged keywords"),
				"basketAdd" => dgettext($domain, "Successfully added resource to basket"),
				"basketRemove" => dgettext($domain, "Successfully removed resource from basket"),
				"basketDelete" => dgettext($domain, "Successfully deleted basket"),
				"imageDelete" => dgettext($domain, "Successfully deleted images"),
				"forgetUpdate" => dgettext($domain, "Successfully updated the forgotten password system"),
				"maturityIndex" => dgettext($domain, "Successfully set the maturity index"),
				"groupAdd" => dgettext($domain, "Successfully added group"),
				"groupEdit" => dgettext($domain, "Successfully edited group"),
				"groupDelete" => dgettext($domain, "Successfully deleted group"),
/// emailFriend_ User has emailed a friend a link to a resource
				"emailFriend" => dgettext($domain, "Successfully sent email"),
				"creatorMerge" => dgettext($domain, "Successfully merged creators"),
				"creatorGroup" => dgettext($domain, "Successfully grouped creators"),
				"creatorUngroup" => dgettext($domain, "Successfully removed creators from group"),
				"registerRequest" => dgettext($domain, "Your registration request has been emailed to the WIKINDX administrator and you should receive an emailed response soon"),
				"registerRequestManage" => dgettext($domain, "Successfully managed registration requests."),
/// quarantineApprove_ ADMIN has approved a quarantined resource
				"quarantineApprove" => dgettext($domain, "Successfully approved resource"),
				"quarantined" => dgettext($domain, "Resource has been quarantined from public view until approved by an administrator"),
				"plugins" => dgettext($domain, "Successfully configured plugins"),
				"componentSuccess" => dgettext($domain, "Admin action successful. ###"),
				"componentUpToDate" =>"The list is up-to-date.",
			),
		);
	}
}
