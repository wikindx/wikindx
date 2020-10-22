<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */
class localedescriptionMessages
{
    /** array */
    public $text = [];
    
    public function __construct()
    {
        $domain = mb_strtolower(basename(__DIR__));
        
        $this->text = [
            "menu" => dgettext($domain, "Localize Front Page"),
            "heading" => dgettext($domain, "Localize Front Page Description"),
            "text1" => dgettext($domain, "Here, you can set an alternate front page description for each localization you have installed.
						 Alternate descriptions are stored in the config_ database as configDescription_xx_XX. The original 
						 description, set in Admin|Configure, is displayed whenever the user changes to a localization
						 for which there is no alternate front page description."),
            "text2" => dgettext($domain, "If you empty the description and submit, you are removing the database field."),
            "choose" => dgettext($domain, "Choose localization"),
            "original" => dgettext($domain, "Original Admin|Configure description"),
            "missingLanguage" => dgettext($domain, "Missing language"),
            "success" => dgettext($domain, "Successfully edited ###"),
            "onlyEnglish" => dgettext($domain, "Only English is available on this WIKINDX – set the front page description via the Admin|Configure interface"),
        ];
    }
}
