<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 *	SOUNDEXPLORER plugin -- English messages.
 */
class soundexplorerMessages
{
    /** array */
    public $text = [];
    
    public function __construct()
    {
        $domain = mb_strtolower(basename(__DIR__));
        
        $this->text = [
            /**
             * Menu items
             */
            "se" => dgettext($domain, "Sound Explorer"),
            "seExplain" => dgettext($domain, "Sound Explorer will store searches then, at any time in the future, play a sound if a resource matching
				a past search is found in a current list operation. This works on the principle of a serendipitous conjunction of past searches
				with a current search result, hopefully allowing you to make new creative connections by prodding at the boundaries of your
				current conceptual space.  But it is also useful in a multi-user
				WIKINDX where it functions like a 'tripwire' signal -- the sound will play if a new resource or metadata has been added by another
				user that matches a stored search (if the resource is being displayed in a list). It is currently an experimental
				prototype and has been tested in the Firefox, Safari, and Chrome web browsers. Other browsers are untested."),
            "seOn" => dgettext($domain, "Sound Explorer On"),
            "seOff" => dgettext($domain, "Sound Explorer Off"),
            "seLabel" => dgettext($domain, "Search Label"),
            "seSound" => dgettext($domain, "Sound"),
            "seSearchStatus" => dgettext($domain, "Status"),
            "seEnabled" => dgettext($domain, "Enabled"),
            "seDisabled" => dgettext($domain, "Disabled"),
            "seNewSearch" => dgettext($domain, "New search"),
            "seSearchDelete" => dgettext($domain, "Delete"),
            "seSearchNote" => dgettext($domain, "Notes"),
            "seMatchedSearches" => dgettext($domain, "Matched searches for current list"),
            "seToggleSuccess" => dgettext($domain, "Sound Explorer successfully configured"),
            "seSearchSubmit" => dgettext($domain, "Store the Sound Explorer search"),
            "seStoreSuccess" => dgettext($domain, "Sound Explorer search successfully stored"),
            "seDeleteSuccess" => dgettext($domain, "Sound Explorer search successfully deleted"),
        ];
    }
}
