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
 * Collection mapping for the collectionDefault column in the collections table
 *
 *	Sets default settings for the collection that are common to all resources of that collection.
 *
 * @package wikindx\core\collection
 */
class COLLECTIONDEFAULTMAP
{
    /** array */
    //public $collectionTypes;

    /**
     * COLLECTIONDEFAULTMAP
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
            'proceedings_article' => 'proceedings',
            'manuscript' => 'manuscript',
            'thesis' => 'thesis',
            'music_track' => 'music',
            'miscellaneous_section' => 'miscellaneous',
        ];

        /**
         * Within each collection array, the outer array refers to the database table, the inner array has the key for the table column and a value
         * to be found in the resources array of MESSAGES_xx.php
         */
        $this->book = [
            'resource' => [
                'Field1' => 'seriesTitle',
                'Field2' => 'edition',
                'Field3' => 'seriesNumber',
                'Field4' => 'bookVolumeNumber',
                'TransTitle' => 'title',
                'TransSubtitle' => 'subtitle',
                'TransShortTitle' => 'shortTitle',
                'TransNoSort' => FALSE,
                'Isbn' => 'isbn',
                'Doi' => 'doi',
            ],
            'resource_misc' => [
                'Publisher' => 'publisher',
                'Field1' => 'transPublisher',
                'Field4' => 'numberOfVolumes',
                'PeerReviewed' => 'peerReviewed',
            ],
            'resource_year' => [
                'Year1' => 'publicationYear',
                'Year2' => 'reprintYear',
                'Year3' => 'volumeYear',
                'Year4' => 'transPublicationYear',
            ],
            'resource_creator' => [
                '2' => 'editor',
                '3' => 'translator',
                '4' => 'reviser',
                '5' => 'seriesEditor',
            ],
        ];
        $this->journal = [
            'resource' => [
                'Isbn' => 'isbn',
            ],
            'resource_misc' => [
                'Publisher' => 'publisher',
                'PeerReviewed' => 'peerReviewed',
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
            ],
        ];
        $this->newspaper = [
            'resource' => [
                'Field2' => 'city',
                'Isbn' => 'isbn',
            ],
            'resource_misc' => [
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
            ],
        ];
        $this->magazine = [
            'resource' => [
                'Isbn' => 'isbn',
            ],
            'resource_misc' => [
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
            ],
        ];
        $this->proceedings = [
            'resource' => [
                'Field1' => 'seriesTitle',
                'Field3' => 'seriesNumber',
                'Field4' => 'proceedingsVolumeNumber',
                'Isbn' => 'isbn',
            ],
            'resource_misc' => [
                'Field2' => 'startDay',
                'Field5' => 'endDay',
                'Field3' => 'startMonth',
                'Field6' => 'endMonth',
                'PeerReviewed' => 'peerReviewed',
                'Publisher' => 'organizerId',
                'Field1' => 'publisherId',
            ],
            'resource_year' => [
                'Year1' => 'publicationYear',
                'Year2' => 'startYear',
                'Year3' => 'endYear',
            ],
            'resource_creator' => [
                '2' => 'editor',
            ],
        ];
        $this->web = [
            'resource' => [
                'Isbn' => 'isbn',
            ],
            'resource_misc' => [
                'Publisher' => 'publisher',
                'PeerReviewed' => 'peerReviewed',
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
                '2' => 'editor',
            ],
        ];
        $this->music = [
            'resource' => [
                'Field2' => 'medium',
                'Isbn' => 'isbn',
            ],
            'resource_misc' => [
                'Publisher' => 'publisher',
            ],
            'resource_year' => [
                'Year1' => 'publicationYear',
            ],
            'resource_creator' => [
            ],
        ];
        $this->thesis = [
            'resource' => [
                'Isbn' => 'isbn',
            ],
            'resource_misc' => [
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
            ],
        ];
        $this->manuscript = [
            'resource' => [
                'Isbn' => 'isbn',
            ],
            'resource_misc' => [
            ],
            'resource_year' => [
            ],
            'resource_creator' => [
            ],
        ];
        $this->miscellaneous = [
            'resource' => [
                'Field2' => 'medium',
                'Isbn' => 'isbn',
            ],
            'resource_misc' => [
                'Publisher' => 'publisher',
                'PeerReviewed' => 'peerReviewed',
            ],
            'resource_year' => [
                'Year1' => 'publicationYear',
            ],
            'resource_creator' => [
            ],
        ];
    }
}
