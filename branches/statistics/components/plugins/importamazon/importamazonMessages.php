<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */
class importamazonMessages
{
	/** array */
    public $text = [];
    
    public function __construct()
    {
        $domain = mb_strtolower(basename(__DIR__));
        
        $this->text = [
            "importAmazon" => dgettext($domain, "Amazon Import"),
            "heading" => dgettext($domain, "Amazon Import"),
            "url" => dgettext($domain, "Enter the Amazon URL for the item you wish to import"),
            "urlHint" => dgettext($domain, "https://...."),
            "success" => dgettext($domain, "Successfully added resource"),
            "noAccessKey" => dgettext($domain, "Missing Amazon access key.  Please read plugins/importamazon/README.txt"),
            "noSecretAccessKey" => dgettext($domain, "Missing Amazon secret access key.  Please read plugins/importamazon/README.txt"),
            "noInput" => dgettext($domain, "Missing input"),
            "invalidURL1" => dgettext($domain, "Invalid Amazon URL (unable to strip domain name)"),
            "invalidURL2" => dgettext($domain, "Invalid Amazon URL (unable to strip ISBN)"),
            "notBook" => dgettext($domain, "The resource is not a book"),
            "resourceExists" => dgettext($domain, "That title already exists."),
            "failure" => dgettext($domain, "Import failed. Amazon reports: ###"),
            // You can only import from one Amazon region. Change this to match the region you added in config.php $productAdvertisingAPIEndpoint
            "region" => dgettext($domain, "You can only import from the British Amazon website (amazon.co.uk)"),
        ];
    }
}
