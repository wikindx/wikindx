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
 * Collection mapping
 *
 * Provides mappings between resource types and collection types.  Allows for displaying only certain
 * collections appropriate to the resource type when entering a new resource.
 *
 * @package wikindx\core\collection
 */
class COLLECTIONMAP
{
    /** array */
    public $collectionTypes;

    /**
     * COLLECTIONMAP
     */
    public function __construct()
    {
        $this->loadMap();
    }
    /**
     * Load collection map arrays
     */
    private function loadMap()
    {
        $this->collectionTypes = [
            'book_article' => 'book',
            'book_chapter' => 'book',
            'journal_article' => 'journal',
            'newspaper_article' => 'newspaper',
            'magazine_article' => 'magazine',
            'web_article' => 'web',
            'web_encyclopedia_article' => 'web',
            'conference_paper' => 'proceedings',
            'conference_poster' => 'proceedings',
            'proceedings_article' => 'proceedings',
            'thesis' => 'thesis',
            'manuscript' => 'manuscript',
            'music_track' => 'music',
            'miscellaneous_section' => 'miscellaneous',
        ];
        $this->book = [
            'resource' => [
                'field1' => 'seriesTitle',
                'field2' => 'edition',
                'field3' => 'seriesNumber',
                'transTitle' => 'title',
                'transSubtitle' => 'subtitle',
            ],
            'resource_misc' => [
                'publisher' => 'publisher',
                'miscField1' => 'transPublisher',
                'miscField4' => 'numberOfVolumes',
            ],
            'resource_year' => [
                'year1' => 'publicationYear',
                'year2' => 'reprintYear',
                'year3' => 'volumeYear',
                'year4' => 'transPublicationYear',
            ],
            'resource_creator' => [
                'creator2' => 'editor',
                'creator3' => 'translator',
                'creator4' => 'reviser',
                'creator5' => 'seriesEditor',
            ],
            // In addition to the collectionType, title and shortTitle of the collection, all these fields must match in order for a new collection to be judged to be the same as an existing one
            'match' => ['field1', 'field2', 'field3', 'publisher', 'year1'],
            // Minimum fields for display of collection in selection box in addition to title and titleshort
            'display' => ['field2', 'year1'],
        ];
        $this->journal = [
            'resource' => [
                //					'field1'	=>	'journalVolumeNumber',
                //					'field2'	=>	'journalIssueNumber',
            ],
            'resource_misc' => [
                //					'miscField3'	=>	'month',
                //					'miscField6'	=>	'endMonth',
            ],
            'resource_year' => [
                //					'year1'		=>	'publicationYear',
                //					'year3'		=>	'endPublicationYear',
            ],
            'resource_creator' => [
            ],
            // In addition to the collectionType, title and shortTitle of the collection, all these fields must match in order for a new collection to be judged to be the same as an existing one
            //				'match' => array('field1', 'field2', 'year1'),
            'match' => [],
            // Minimum fields for display of collection in selection box in addition to title and titleshort
            //				'display' => array('field1', 'field2', 'year1'),
            'display' => [],
        ];
        $this->newspaper = [
            'resource' => [
                'field2' => 'city',
            ],
            'resource_misc' => [
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
            ],
            // In addition to the collectionType, title and shortTitle of the collection, all these fields must match in order for a new collection to be judged to be the same as an existing one
            'match' => ['field2'],
            // Minimum fields for display of collection in selection box in addition to title and titleshort
            'display' => ['field2'],
        ];
        $this->magazine = [
            'resource' => [
            ],
            'resource_misc' => [
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
            ],
            // In addition to the collectionType, title and shortTitle of the collection, all these fields must match in order for a new collection to be judged to be the same as an existing one
            'match' => [], // empty array required
            // Minimum fields for display of collection in selection box in addition to title and titleshort
            'display' => [], // empty array required
        ];
        $this->proceedings = [
            'resource' => [
                'field1' => 'seriesTitle',
            ],
            'resource_misc' => [
            ],
            'resource_year' => [
                //					'year3'		=>	'conferenceEndYear',
            ],
            'resource_creator' => [
            ],
            // In addition to the collectionType, title and shortTitle of the collection, all these fields must match in order for a new collection to be judged to be the same as an existing one
            'match' => ['field1'],
            // Minimum fields for display of collection in selection box in addition to title and titleshort
            'display' => ['field1'],
        ];
        $this->web = [
            'resource' => [
                //					'field1'	=>	'journalVolumeNumber',
                //					'field2'	=>	'journalIssueNumber',
            ],
            'resource_misc' => [
                'publisher' => 'publisher',
            ],
            'resource_year' => [
                //					'year1'		=>	'publicationYear',
            ],
            'resource_creator' => [
            ],
            // In addition to the collectionType, title and shortTitle of the collection, all these fields must match in order for a new collection to be judged to be the same as an existing one
            //				'match' => array('field1', 'field2', 'year1'),
            'match' => [],
            // Minimum fields for display of collection in selection box in addition to title and titleshort
            //				'display' => array('field1', 'field2', 'year1'),
            'display' => [],
        ];
        $this->web_encyclopedia = [
            'resource' => [
            ],
            'resource_misc' => [
                'publisher' => 'publisher',
            ],
            'resource_year' => [
                //					'year1'		=>	'publicationYear',
            ],
            'resource_creator' => [
            ],
            // In addition to the collectionType, title and shortTitle of the collection, all these fields must match in order for a new collection to be judged to be the same as an existing one
            'match' => [],
            // Minimum fields for display of collection in selection box in addition to title and titleshort
            'display' => [],
        ];
        $this->thesis = [
            'resource' => [
                //					'field3'	=>	'journalVolumeNumber',
                //					'field4'	=>	'journalIssueNumber',
            ],
            'resource_misc' => [
            ],
            'resource_year' => [
                //					'year2'		=>	'publicationYear',
            ],
            'resource_creator' => [
            ],
            // In addition to the collectionType, title and shortTitle of the collection, all these fields must match in order for a new collection to be judged to be the same as an existing one
            //				'match' => array('field3', 'field4', 'year2'),
            'match' => [],
            // Minimum fields for display of collection in selection box in addition to title and titleshort
            //				'display' => array('field3', 'field4', 'year2'),
            'display' => [],
        ];
        $this->music = [
            'resource' => [
                'field2' => 'medium',
            ],
            'resource_misc' => [
                'publisher' => 'publisher',
            ],
            'resource_year' => [
                'year1' => 'publicationYear',
            ],
            'resource_creator' => [
            ],
            // In addition to the collectionType, title and shortTitle of the collection, all these fields must match in order for a new collection to be judged to be the same as an existing one
            'match' => ['publisher', 'year1'],
            // Minimum fields for display of collection in selection box in addition to title and titleshort
            'display' => ['year1'],
        ];
        $this->manuscript = [
            'resource' => [
            ],
            'resource_misc' => [
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
            ],
            // In addition to the collectionType, title and shortTitle of the collection, all these fields must match in order for a new collection to be judged to be the same as an existing one
            'match' => [], // empty array required
            // Minimum fields for display of collection in selection box in addition to title and titleshort
            'display' => [], // empty array required
        ];
        $this->miscellaneous = [
            'resource' => [
                'field2' => 'medium',
            ],
            'resource_misc' => [
                'publisher' => 'publisher',
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
            ],
            // In addition to the collectionType, title and shortTitle of the collection, all these fields must match in order for a new collection to be judged to be the same as an existing one
            'match' => [], // empty array required
            // Minimum fields for display of collection in selection box in addition to title and titleshort
            'display' => [], // empty array required
        ];
    }
}
