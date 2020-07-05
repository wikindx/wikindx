<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */
class dbadminerMessages
{
    /** array */
    public $text = [];
    
    public function __construct()
    {
        $domain = mb_strtolower(basename(__DIR__));
        
        $this->text = [
            "menu" => dgettext($domain, "DB Adminer"),
            "heading" => dgettext($domain, "DB Adminer"),
            "openlink" => dgettext($domain, "Open DB Adminer in a new window / tab"),
        ];
    }
}
