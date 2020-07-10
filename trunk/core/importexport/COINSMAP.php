<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

/**
 * Provides COINS mappings
 *
 *	Provides mappings for COINS (Firefox, Navigator etc.)
 *
 * @package wikindx\core\importexport
 */
class COINSMAP
{
    /** string */
    public $coinsBase;
    /** array */
    public $types;
    /** array */
    public $genres;
    /** array */
    public $basic;
    /** array */
    public $book;
    /** array */
    public $book_article;
    /** array */
    public $book_chapter;
    /** array */
    public $journal_article;
    /** array */
    public $newspaper_article;
    /** array */
    public $magazine_article;
    /** array */
    public $proceedings_article;
    /** array */
    public $thesis;
    /** array */
    public $web_site;
    /** array */
    public $web_article;
    /** array */
    public $web_encyclopaedia;
    /** array */
    public $web_encyclopaedia_article;
    /** array */
    public $film;
    /** array */
    public $broadcast;
    /** array */
    public $software;
    /** array */
    public $artwork;
    /** array */
    public $audivisual;
    /** array */
    public $case;
    /** array */
    public $legal_ruling;
    /** array */
    public $bill;
    /** array */
    public $classical;
    /** array */
    public $conference_paper;
    /** array */
    public $miscellaneous;
    /** array */
    public $miscellaneous_section;
    /** array */
    public $government_report;
    /** array */
    public $report;
    /** array */
    public $hearing;
    /** array */
    public $database;
    /** array */
    public $manuscript;
    /** array */
    public $map;
    /** array */
    public $chart;
    /** array */
    public $statute;
    /** array */
    public $patent;
    /** array */
    public $personal;
    /** array */
    public $proceedings;
    /** array */
    public $music_album;
    /** array */
    public $music_track;
    /** array */
    public $music_score;
    /** array */
    public $unpublished;
    /** array */
    public $brochure;

    /**
     * COINSMAP
     */
    public function __construct()
    {
        $this->loadMap();
    }
    /**
     * Create arrays based on database table and resource type mapping wikindx resource fields to Coins elements.
     * The arrays are named for WIKINDX resource types.
     *
     * Creators and dates are handled in the PHP code
     */
    public function loadMap()
    {
        $this->coinsBase = 'class="Z3988" title="ctx_ver=Z39.88-2004&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3A';
        // WIKINDX resource types (keys) and their COINS equivalent.
        // NB COins/OpenURL is extremely limited in its types (as of v1.0), for unsupported types, use 'book' and genre == 'unknown'.
        $this->types = [
            // Edit values if necessary
            'book' => 'book',
            'book_article' => 'book',
            'book_chapter' => 'book',
            'journal_article' => 'journal',
            'newspaper_article' => 'journal',
            'magazine_article' => 'journal',
            'proceedings' => 'book',
            'conference_paper' => 'book',
            'conference_poster' => 'book',
            'proceedings_article' => 'journal',
            'thesis' => 'dissertation',
            'web_site' => 'journal',
            'web_article' => 'journal',
            'web_encyclopedia' => 'journal',
            'web_encyclopedia_article' => 'journal',
            'film' => 'book',
            'broadcast' => 'book',
            'music_album' => 'book',
            'music_track' => 'book',
            'music_score' => 'book',
            'artwork' => 'book',
            'software' => 'book',
            'audiovisual' => 'book',
            'database' => 'book',
            'government_report' => 'book',
            'report' => 'book',
            'hearing' => 'book',
            'statute' => 'book',
            'legal_ruling' => 'book',
            'case' => 'book',
            'bill' => 'book',
            'patent' => 'patent',
            'brochure' => 'book',
            'personal' => 'book',
            'unpublished' => 'book',
            'classical' => 'book',
            'manuscript' => 'book',
            'map' => 'book',
            'chart' => 'book',
            'miscellaneous' => 'book',
            'miscellaneous_section' => 'book',
        ];
        // Some wikindx types have no Coins genre
        $this->genres = [
            'book' => 'book',
            'book_article' => 'bookitem',
            'book_chapter' => 'bookitem',
            'journal_article' => 'article',
            'newspaper_article' => 'article',
            'magazine_article' => 'article',
            'proceedings' => 'conference',
            'conference_paper' => 'proceeding',
            'conference_poster' => 'proceeding',
            'proceedings_article' => 'proceeding',
            'thesis' => FALSE,
            'web_site' => 'article',
            'web_article' => 'article',
            'web_encyclopedia' => 'journal',
            'web_encyclopedia_article' => 'article',
            'film' => 'unknown',
            'broadcast' => 'unknown',
            'music_album' => 'book',
            'music_track' => 'bookitem',
            'music_score' => 'unknown',
            'artwork' => 'unknown',
            'software' => 'unknown',
            'audiovisual' => 'unknown',
            'database' => 'unknown',
            'government_report' => 'report',
            'report' => 'report',
            'hearing' => 'unknown',
            'statute' => 'unknown',
            'legal_ruling' => 'unknown',
            'case' => 'unknown',
            'bill' => 'unknown',
            'patent' => FALSE,
            'brochure' => 'unknown',
            'personal' => 'unknown',
            'unpublished' => 'unknown',
            'classical' => 'unknown',
            'manuscript' => 'unknown',
            'map' => 'unknown',
            'chart' => 'unknown',
            'miscellaneous' => 'unknown',
            'miscellaneous_section' => 'unknown',
        ];
        /**
         * Basic array of elements common to all types - change the key to map the database field that stores that value.
         */
        $this->basic = [
        ];
        // Resource specific mappings. The order here is the display order when editing/creating styles.
        // BOOK
        $this->book = $this->basic;
        $this->book['resourceTitle'] = 'btitle';
        $this->book['resourceField1'] = 'series';
        $this->book['resourceField2'] = 'edition';
        $this->book['publisherName'] = 'pub';
        $this->book['publisherLocation'] = 'place';
        $this->book['resourceIsbn'] = 'resourceIsbn';
        $this->book['resourceField6'] = 'tpages';
        // BOOK ARTICLE
        $this->book_article = $this->basic;
        $this->book_article['resourceTitle'] = 'atitle';
        $this->book_article['resourceField1'] = 'series';
        $this->book_article['resourceField2'] = 'edition';
        $this->book_article['publisherName'] = 'pub';
        $this->book_article['publisherLocation'] = 'place';
        $this->book_article['collectionTitle'] = 'btitle';
        $this->book_article['resourcepagePageStart'] = 'spage';
        $this->book_article['resourcepagePageEnd'] = 'epage';
        $this->book_article['resourceIsbn'] = 'resourceIsbn';
        // BOOK CHAPTER
        $this->book_chapter = $this->basic;
        $this->book_chapter['resourceTitle'] = 'atitle';
        $this->book_chapter['resourceField1'] = 'series';
        $this->book_chapter['resourceField2'] = 'edition';
        $this->book_chapter['publisherName'] = 'pub';
        $this->book_chapter['publisherLocation'] = 'place';
        $this->book_chapter['collectionTitle'] = 'btitle';
        $this->book_chapter['resourcepagePageStart'] = 'spage';
        $this->book_chapter['resourcepagePageEnd'] = 'epage';
        $this->book_chapter['resourceIsbn'] = 'resourceIsbn';
        // JOURNAL ARTICLE
        $this->journal_article = $this->basic;
        $this->journal_article['resourceTitle'] = 'atitle';
        $this->journal_article['resourceField1'] = 'volume';
        $this->journal_article['resourceField2'] = 'issue';
        $this->journal_article['collectionTitle'] = 'jtitle';
        $this->journal_article['resourcepagePageStart'] = 'spage';
        $this->journal_article['resourcepagePageEnd'] = 'epage';
        $this->journal_article['resourceIsbn'] = 'issn';
        $this->journal_article['resourceShortTitle'] = 'stitle';
        // NEWSPAPER ARTICLE
        $this->newspaper_article = $this->basic;
        $this->newspaper_article['resourceTitle'] = 'atitle';
        $this->newspaper_article['collectionTitle'] = 'jtitle';
        $this->newspaper_article['collectionTitleShort'] = 'stitle';
        $this->newspaper_article['resourcepagePageStart'] = 'spage';
        $this->newspaper_article['resourcepagePageEnd'] = 'epage';
        $this->newspaper_article['resourceIsbn'] = 'issn';
        // MAGAZINE ARTICLE
        $this->magazine_article = $this->basic;
        $this->magazine_article['resourceTitle'] = 'atitle';
        $this->magazine_article['collectionTitle'] = 'jtitle';
        $this->magazine_article['collectionTitleShort'] = 'stitle';
        $this->magazine_article['resourceField4'] = 'volume';
        $this->magazine_article['resourceField3'] = 'issue';
        $this->magazine_article['resourcepagePageStart'] = 'spage';
        $this->magazine_article['resourcepagePageEnd'] = 'epage';
        $this->magazine_article['resourceIsbn'] = 'issn';
        // PROCEEDINGS ARTICLE
        $this->proceedings_article = $this->basic;
        $this->proceedings_article['resourceTitle'] = 'atitle';
        $this->proceedings_article['collectionTitle'] = 'jtitle';
        $this->proceedings_article['publisherName'] = 'pub';
        $this->proceedings_article['publisherLocation'] = 'place';
        $this->proceedings_article['resourcepagePageStart'] = 'spage';
        $this->proceedings_article['resourcepagePageEnd'] = 'epage';
        $this->proceedings_article['resourceIsbn'] = 'issn';
        // THESIS
        $this->thesis = $this->basic;
        $this->thesis['resourceTitle'] = 'resourceTitle';
        $this->thesis['resourceField1'] = 'degree'; // 'Master's', 'PhD', 'Doctoral', 'Diploma' etc.
        $this->thesis['publisherName'] = 'inst';
        $this->thesis['publisherLocation'] = 'co';
        $this->thesis['resourceIsbn'] = 'resourceIsbn';
        $this->thesis['resourceField6'] = 'tpages';
        // WEB SITE
        $this->web_site = $this->basic;
        $this->web_site['resourceTitle'] = 'atitle';
        // WEB ARTICLE
        $this->web_article = $this->basic;
        $this->web_article['resourceTitle'] = 'atitle';
        $this->web_article['collectionTitle'] = 'jtitle';
        $this->web_article['collectionTitleShort'] = 'stitle';
        $this->web_article['resourceField1'] = 'volume';
        $this->web_article['resourceField2'] = 'issue';
        $this->web_article['resourcepagePageStart'] = 'spage';
        $this->web_article['resourcepagePageEnd'] = 'epage';
        $this->web_article['publisherName'] = 'pub';
        $this->web_article['publisherLocation'] = 'place';
        // WEB ENCYCLOPEDIA
        $this->web_encyclopedia = $this->basic;
        $this->web_encyclopedia['resourceTitle'] = 'jtitle';
        $this->web_encyclopedia['publisherName'] = 'pub';
        $this->web_encyclopedia['publisherLocation'] = 'place';
        // WEB ENCYCLOPEDIA ARTICLE
        $this->web_encyclopedia_article = $this->basic;
        $this->web_encyclopedia_article['resourceTitle'] = 'atitle';
        $this->web_encyclopedia_article['publisherName'] = 'pub';
        $this->web_encyclopedia_article['publisherLocation'] = 'place';
        $this->web_encyclopedia_article['collectionTitle'] = 'jtitle';
        $this->web_encyclopedia_article['collectionTitleShort'] = 'stitle';
        $this->web_encyclopedia_article['resourcepagePageStart'] = 'spage';
        $this->web_encyclopedia_article['resourcepagePageEnd'] = 'epage';
        // FILM
        $this->film = $this->basic;
        $this->film['resourceTitle'] = 'btitle';
        // BROADCAST
        $this->broadcast = $this->basic;
        $this->broadcast['resourceTitle'] = 'btitle';
        $this->broadcast['publisherName'] = 'pub';
        $this->broadcast['publisherLocation'] = 'place';
        // SOFTWARE
        $this->software = $this->basic;
        $this->software['resourceTitle'] = 'btitle';
        $this->software['publisherName'] = 'pub';
        $this->software['publisherLocation'] = 'place';
        // ARTWORK
        $this->artwork = $this->basic;
        $this->artwork['resourceTitle'] = 'btitle';
        $this->artwork['publisherName'] = 'pub';
        $this->artwork['publisherLocation'] = 'place';
        // AUDIOVISUAL
        $this->audiovisual = $this->basic;
        $this->audiovisual['resourceTitle'] = 'btitle';
        $this->audiovisual['resourceField1'] = 'series';
        $this->audiovisual['publisherName'] = 'pub';
        $this->audiovisual['publisherLocation'] = 'place';
        // (LEGAL) CASE
        $this->case = $this->basic;
        $this->case['resourceTitle'] = 'atitle';
        $this->case['resourceField4'] = 'btitle';
        $this->case['publisherName'] = 'pub';
        $this->case['resourceIsbn'] = 'resourceIsbn';
        // LEGAL RULING/REGULATION
        $this->legal_ruling = $this->basic;
        $this->legal_ruling['resourceTitle'] = 'btitle';
        $this->legal_ruling['resourceField3'] = 'edition';
        $this->legal_ruling['publisherName'] = 'pub';
        $this->legal_ruling['publisherLocation'] = 'place';
        $this->legal_ruling['resourcepagePageStart'] = 'spage';
        $this->legal_ruling['resourcepagePageEnd'] = 'epage';
        $this->legal_ruling['resourceIsbn'] = 'resourceIsbn';
        // (PARLIAMENTARY) BILL
        $this->bill = $this->basic;
        $this->bill['resourceTitle'] = 'btitle';
        $this->bill['resourceField4'] = 'edition';
        $this->bill['publisherName'] = 'pub';
        $this->bill['publisherLocation'] = 'place';
        $this->bill['resourcepagePageStart'] = 'spage';
        $this->bill['resourcepagePageEnd'] = 'epage';
        // CLASSICAL WORK
        $this->classical = $this->basic;
        $this->classical['resourceTitle'] = 'btitle';
        $this->classical['resourceField4'] = 'edition';
        $this->classical['resourceIsbn'] = 'resourceIsbn';
        // CONFERENCE PAPER
        $this->conference_paper = $this->basic;
        $this->conference_paper['resourceTitle'] = 'atitle';
        $this->conference_paper['collectionTitle'] = 'btitle';
        $this->conference_paper['collectionTitleShort'] = 'stitle';
        $this->conference_paper['publisherName'] = 'pub';
        $this->conference_paper['publisherLocation'] = 'place';
        $this->conference_paper['resourceIsbn'] = 'resourceIsbn';
        // CONFERENCE POSTER
        $this->conference_poster = $this->basic;
        $this->conference_poster['resourceTitle'] = 'atitle';
        $this->conference_poster['collectionTitle'] = 'btitle';
        $this->conference_poster['collectionTitleShort'] = 'stitle';
        $this->conference_poster['publisherName'] = 'pub';
        $this->conference_poster['publisherLocation'] = 'place';
        $this->conference_poster['resourceIsbn'] = 'resourceIsbn';
        // MISCELLANEOUS
        $this->miscellaneous = $this->basic;
        $this->miscellaneous['resourceTitle'] = 'btitle';
        $this->miscellaneous['publisherName'] = 'pub';
        $this->miscellaneous['publisherLocation'] = 'place';
        // MISCELLANEOUS SECTION
        $this->miscellaneous_section = $this->basic;
        $this->miscellaneous_section['resourceTitle'] = 'atitle';
        $this->miscellaneous_section['publisherName'] = 'pub';
        $this->miscellaneous_section['publisherLocation'] = 'place';
        $this->miscellaneous_section['collectionTitle'] = 'btitle';
        $this->miscellaneous_section['collectionTitleShort'] = 'stitle';
        $this->miscellaneous_section['resourcepagePageStart'] = 'spage';
        $this->miscellaneous_section['resourcepagePageEnd'] = 'epage';
        // GOVERNMENT REPORT/DOCUMENTATION
        $this->government_report = $this->basic;
        $this->government_report['resourceTitle'] = 'btitle';
        $this->government_report['resourceField4'] = 'series';
        $this->government_report['resourceField3'] = 'edition';
        $this->government_report['publisherName'] = 'publ';
        $this->government_report['publisherLocation'] = 'place';
        $this->government_report['resourcepagePageStart'] = 'spage';
        $this->government_report['resourcepagePageEnd'] = 'epage';
        $this->government_report['resourceIsbn'] = 'issn';
        // REPORT/DOCUMENTATION
        $this->report = $this->basic;
        $this->report['resourceField1'] = 'series';
        $this->report['resourceField5'] = 'edition';
        $this->report['publisherName'] = 'pub';
        $this->report['publisherLocation'] = 'place';
        $this->report['resourcepagePageStart'] = 'spage';
        $this->report['resourcepagePageEnd'] = 'epage';
        $this->report['resourceIsbn'] = 'issn';
        $this->report['resourceTitle'] = 'btitle';
        // GOVERNMENT/LEGAL HEARING
        $this->hearing = $this->basic;
        $this->hearing['resourceTitle'] = 'btitle';
        $this->hearing['resourceField1'] = 'series';
        $this->hearing['resourceField4'] = 'edition';
        $this->hearing['publisherName'] = 'pub';
        $this->hearing['publisherLocation'] = 'place';
        $this->hearing['resourcepagePageStart'] = 'spage';
        $this->hearing['resourcepagePageEnd'] = 'epage';
        $this->hearing['resourceIsbn'] = 'issn';
        // ONLINE DATABASE
        $this->database = $this->basic;
        $this->database['resourceTitle'] = 'btitle';
        $this->database['publisherName'] = 'pub';
        $this->database['publisherLocation'] = 'place';
        // MANUSCRIPT
        $this->manuscript = $this->basic;
        $this->manuscript['resourceTitle'] = 'atitle';
        $this->manuscript['collectionTitle'] = 'btitle';
        $this->manuscript['collectionTitleShort'] = 'stitle';
        $this->manuscript['resourceField3'] = 'edition';
        $this->manuscript['resourcepagePageStart'] = 'spage';
        $this->manuscript['resourcepagePageEnd'] = 'epage';
        $this->manuscript['resourceIsbn'] = 'resourceIsbn';
        // MAP
        $this->map = $this->basic;
        $this->map['resourceTitle'] = 'btitle';
        $this->map['resourceField1'] = 'series';
        $this->map['resourceField3'] = 'edition';
        $this->map['publisherName'] = 'pub';
        $this->map['publisherLocation'] = 'place';
        $this->map['resourceIsbn'] = 'resourceIsbn';
        // CHART
        $this->chart = $this->basic;
        $this->chart['resourceTitle'] = 'btitle';
        $this->chart['publisherName'] = 'pub';
        $this->chart['publisherLocation'] = 'place';
        // STATUTE
        $this->statute = $this->basic;
        $this->statute['resourceField2'] = 'series';
        $this->statute['resourceField5'] = 'edition';
        $this->statute['resourcepagePageStart'] = 'spage';
        $this->statute['resourcepagePageEnd'] = 'epage';
        $this->statute['resourceTitle'] = 'btitle';
        // PATENT
        $this->patent = $this->basic;
        $this->patent['resourceTitle'] = 'resourceTitle';
        $this->patent['resourceField8'] = 'number';
        $this->patent['resourceField3'] = 'applnumber';
        $this->patent['resourceField4'] = 'kind';
        $this->patent['publisherName'] = 'assignee';
        $this->patent['publisherLocation'] = 'co';
        // BROCHURE
        $this->brochure = $this->basic;
        $this->brochure['resourceTitle'] = 'btitle';
        // PERSONAL COMMUNICATION
        $this->personal = $this->basic;
        $this->personal['resourceTitle'] = 'btitle';
        // PROCEEDINGS (complete set of)
        $this->proceedings = $this->basic;
        $this->proceedings['resourceTitle'] = 'btitle';
        $this->proceedings['publisherName'] = 'pub';
        $this->proceedings['publisherLocation'] = 'place';
        $this->proceedings['resourceIsbn'] = 'issn';
        // MUSIC ALBUM
        $this->music_album = $this->basic;
        $this->music_album['resourceTitle'] = 'btitle';
        $this->music_album['publisherName'] = 'pub';
        $this->music_album['resourceIsbn'] = 'resourceIsbn';
        // MUSIC TRACK
        $this->music_track = $this->basic;
        $this->music_track['resourceTitle'] = 'atitle';
        $this->music_track['collectionTitle'] = 'btitle';
        $this->music_track['collectionTitleShort'] = 'stitle';
        $this->music_track['publisherName'] = 'pub';
        $this->music_track['resourceIsbn'] = 'resourceIsbn';
        // MUSIC SCORE
        $this->music_score = $this->basic;
        $this->music_score['resourceTitle'] = 'btitle';
        $this->music_score['resourceField3'] = 'edition';
        $this->music_score['publisherName'] = 'pub';
        $this->music_score['publisherLocation'] = 'place';
        $this->music_score['resourceIsbn'] = 'resourceIsbn';
        // UNPUBLISHED WORK
        $this->unpublished = $this->basic;
        $this->unpublished['resourceTitle'] = 'btitle';
        $this->unpublished['publisherName'] = 'pub';
        $this->unpublished['publisherLocation'] = 'place';
    }
}
