<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
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
            "description" => dgettext($domain, "Idea Generator randomly selects and displays two items of metadata (quotes, paraphrases, musings, or ideas) in the hope that the chance juxtaposition might be serendipitous. The greater the number of metadata, the greater the variety of juxtaposition. You can narrow the selection process down by selecting each item from within a range of resource or metadata keywords."),
            "newIdea" => dgettext($domain, "Create new idea"),
            "idea" => dgettext($domain, "Idea"),
            "musing" => dgettext($domain, "Musing"),
            "paraphrase" => dgettext($domain, "Paraphrase"),
            "quote" => dgettext($domain, "Quote"),
            "paraphraseComment" => dgettext($domain, "Paraphrase Comment"),
            "quoteComment" => dgettext($domain, "Quote Comment"),
            "addIdea" => dgettext($domain, "Add a new idea"),
            "noMetadata" => dgettext($domain, "In order to work, Idea Generator needs at least 3 items of metadata available to you."),
            "item1" => dgettext($domain, "Item 1"),
            "item2" => dgettext($domain, "Item 2"),
            "itemResourceKW" => dgettext($domain, "Resource keywords"),
            "itemMetadataKW" => dgettext($domain, "Metadata keywords"),
            "noPair" => dgettext($domain, "Unable to generate random pair. Please try again or change the item options."),
        ];
    }
}
