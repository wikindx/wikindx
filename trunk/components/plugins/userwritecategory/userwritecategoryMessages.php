<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

/**
 *	USERWRITECATEGORY plugin -- English messages.
 */
class userwritecategoryMessages
{
	/** array */
    public $text = [];
    
    public function __construct()
    {
        $domain = mb_strtolower(basename(__DIR__));
        
        $this->text = [
            // translators: Menu items
            "uwcSub" => dgettext($domain, "Administer Categories..."),
            "uwcCategories" => dgettext($domain, "Categories"),
            "uwcSubcategories" => dgettext($domain, "Subcategories"),
        ];
    }
}
