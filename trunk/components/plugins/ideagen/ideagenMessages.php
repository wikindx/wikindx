<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */
class ideagenMessages
{
    /** array */
    public $text = [];

    public function __construct()
    {
        $domain = mb_strtolower(basename(__DIR__));
        
        $this->text = [
            "menu" => dgettext($domain, "IdeaGen"),
            "heading" => dgettext($domain, "Idea Generator"),
            "generate" => dgettext($domain, "Generate"),
            "generateAgain" => dgettext($domain, "Generate again"),
            "description" => dgettext($domain, "Idea Generator randomly selects and displays two items of metadata (quotes, paraphrases, musings, or ideas) in the hope that the chance juxtaposition might be serendipitous."),
            "newIdea" => dgettext($domain, "Create new idea"),
            "idea" => dgettext($domain, "Idea"),
            "musing" => dgettext($domain, "Musing"),
            "paraphrase" => dgettext($domain, "Paraphrase"),
            "quote" => dgettext($domain, "Quote"),
            "addIdea" => dgettext($domain, "Add a new idea"),
            "noMetadata" => dgettext($domain, "In order to work, Idea Generator needs at least 3 items of metadata available to you."),
        ];
    }
}
