<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */
class xpdftotextMessages
{
    /** array */
    public $text = [];

    public function __construct()
    {
        $domain = mb_strtolower(basename(__DIR__));
        
        $this->text = [
            "menu" => dgettext($domain, "XpdftoText"),
            "heading" => dgettext($domain, "XpdftoText tool"),
        ];
    }
}