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
            "text1" => dgettext($domain, "Here, you can set an alternate front page description for each localization you have installed. Alternate descriptions are stored in the plugin_localedescription db table as one row by translation. The original description, set in Admin|Configure, is displayed whenever there are no translation for the prefered locale of a user. You can translate for a very specific locale or a family of languages. For example French in general, or only French (Canada) for Canadian. In the list a locale preceded by an asterisk has a translation."),
            "text2" => dgettext($domain, "If you empty the description and submit, you are removing the database field."),
            "choose" => dgettext($domain, "Choose localization"),
            "original" => dgettext($domain, "Original Admin|Configure description"),
            "missingLanguage" => dgettext($domain, "Missing language"),
            "success" => dgettext($domain, "Successfully edited ###"),
            "noLocaleAvailable" => dgettext($domain, "Your system does not offer an alternative locale to ###. You need to install more before you can translate."),
        ];
    }
}
