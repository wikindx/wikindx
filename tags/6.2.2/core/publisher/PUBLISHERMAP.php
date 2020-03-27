<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 *	PUBLISHERMAP
 *
 *	Provides mappings between resource types and publisher types.
 * Allows for displaying only certain
 *	publishers appropriate to the resource type when entering a new resource.
 *
 * @package wikindx\core\publisher
 */
class PUBLISHERMAP
{
    /** array */
    public $publisherTypes;

    /**
     *	PUBLISHERMAP
     */
    public function __construct()
    {
        $this->loadMap();
    }
    /**
     * Load publisher arrays
     */
    private function loadMap()
    {
        $this->publisherTypes = [
            'book' => 'book',
            'book_article' => 'book',
            'book_chapter' => 'book',
            'software' => 'book',
            'artwork' => 'book',
            'audiovisual' => 'book',
            'report' => 'book',
            'database' => 'book',
            'web_article' => 'book',
            'web_encyclopedia' => 'book',
            'web_encyclopedia_article' => 'book',
            'thesis' => 'institution',
            'unpublished' => 'institution',
            'broadcast' => 'distributor',
            'film' => 'distributor',
            'case' => 'legal',
            'legal_ruling' => 'legal',
            'bill' => 'legal',
            'government_report' => 'legal',
            'hearing' => 'legal',
            'patent' => 'legal',
            'conference_paper' => 'conference',
            'proceedings_article' => 'conference',
            'proceedings' => 'conference',
            'map' => 'chart',
            'chart' => 'chart',
            'music_album' => 'music',
            'music_track' => 'music',
            'music_score' => 'music',
        ];
    }
}
