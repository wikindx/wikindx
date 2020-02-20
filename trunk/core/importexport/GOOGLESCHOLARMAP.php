<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * Provides mappings for Google Scholar
 *
 * @package wikindx\core\importexport
 */
class GOOGLESCHOLARMAP
{
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
     * GOOGLESCHOLARMAP
     */
    public function __construct()
    {
        $this->loadMap();
    }
    /**
     * Create arrays based on database table and resource type mapping wikindx resource fields to Google Scholar elements.
     *
     * The arrays are named for WIKINDX resource types.
     * Creators and dates are handled in the PHP code
     */
    public function loadMap()
    {
        // Resource specific mappings. The order here is the display order when editing/creating styles.
        // BOOK
        $this->book['resourceTitle'] = 'citation_title';
        $this->book['resourceIsbn'] = 'citation_isbn';
        // BOOK ARTICLE
        $this->book_article['resourceTitle'] = 'citation_title';
        $this->book_article['collectionTitle'] = 'citation_journal_title';
        $this->book_article['resourccitation_lastpagePageStart'] = 'citation_firstpage';
        $this->book_article['resourccitation_lastpagePageEnd'] = 'citation_lastpage';
        $this->book_article['resourceIsbn'] = 'citation_isbn';
        // BOOK CHAPTER
        $this->book_chapter['resourceTitle'] = 'citation_title';
        $this->book_chapter['collectionTitle'] = 'citation_journal_title';
        $this->book_chapter['resourcepagePageStart'] = 'citation_firstpage';
        $this->book_chapter['resourcepagePageEnd'] = 'citation_lastpage';
        $this->book_chapter['resourceIsbn'] = 'citation_isbn';
        // JOURNAL ARTICLE
        $this->journal_article['resourceTitle'] = 'citation_title';
        $this->journal_article['resourceField1'] = 'citation_volume';
        $this->journal_article['resourceField2'] = 'citation_issue';
        $this->journal_article['collectionTitle'] = 'citation_journal_title';
        $this->journal_article['resourcepagePageStart'] = 'citation_firstpage';
        $this->journal_article['resourcepagePageEnd'] = 'citation_lastpage';
        $this->journal_article['resourceIsbn'] = 'citation_citation_issn';
        // NEWSPAPER ARTICLE
        $this->newspaper_article['resourceTitle'] = 'citation_title';
        $this->newspaper_article['collectionTitle'] = 'citation_journal_title';
        $this->newspaper_article['resourcepagePageStart'] = 'citation_firstpage';
        $this->newspaper_article['resourcepagePageEnd'] = 'citation_lastpage';
        $this->newspaper_article['resourceIsbn'] = 'citation_citation_issn';
        // MAGAZINE ARTICLE
        $this->magazine_article['resourceTitle'] = 'citation_title';
        $this->magazine_article['collectionTitle'] = 'citation_journal_title';
        $this->magazine_article['resourceField4'] = 'citation_volume';
        $this->magazine_article['resourceField3'] = 'citation_issue';
        $this->magazine_article['resourcepagePageStart'] = 'citation_firstpage';
        $this->magazine_article['resourcepagePageEnd'] = 'citation_lastpage';
        $this->magazine_article['resourceIsbn'] = 'citation_issn';
        // PROCEEDINGS ARTICLE
        $this->proceedings_article = $this->basic;
        $this->proceedings_article['resourceTitle'] = 'citation_title';
        $this->proceedings_article['collectionTitle'] = 'citation_conference_title';
        $this->proceedings_article['resourcepagePageStart'] = 'citation_firstpage';
        $this->proceedings_article['resourcepagePageEnd'] = 'citation_lastpage';
        $this->proceedings_article['resourceIsbn'] = 'citation_issn';
        // THESIS
        $this->thesis['resourceTitle'] = 'citation_title';
        $this->thesis['resourceIsbn'] = 'citation_isbn';
        // WEB SITE
        $this->web_site['resourceTitle'] = 'citation_title';
        // WEB ARTICLE
        $this->web_article['resourceTitle'] = 'citation_title';
        $this->web_article['collectionTitle'] = 'citation_journal_title';
        $this->web_article['resourceField1'] = 'citation_volume';
        $this->web_article['resourceField2'] = 'citation_issue';
        $this->web_article['resourcepagePageStart'] = 'citation_firstpage';
        $this->web_article['resourcepagePageEnd'] = 'citation_lastpage';
        // WEB ENCYCLOPEDIA
        $this->web_encyclopedia['resourceTitle'] = 'citation_title';
        // WEB ENCYCLOPEDIA ARTICLE
        $this->web_encyclopedia_article['resourceTitle'] = 'citation_title';
        $this->web_encyclopedia_article['collectionTitle'] = 'citation_journal_title';
        $this->web_encyclopedia_article['resourcepagePageStart'] = 'citation_firstpage';
        $this->web_encyclopedia_article['resourcepagePageEnd'] = 'citation_lastpage';
        // FILM
        $this->film['resourceTitle'] = 'citation_title';
        // BROADCAST
        $this->broadcast['resourceTitle'] = 'citation_title';
        // SOFTWARE
        $this->software['resourceTitle'] = 'citation_title';
        // ARTWORK
        $this->artwork['resourceTitle'] = 'citation_title';
        // AUDIOVISUAL
        $this->audiovisual['resourceTitle'] = 'citation_title';
        // (LEGAL) CASE
        $this->case['resourceTitle'] = 'citation_title';
        $this->case['resourceField4'] = 'citation_journal_title';
        $this->case['resourceIsbn'] = 'citation_isbn';
        // LEGAL RULING/REGULATION
        $this->legal_ruling['resourceTitle'] = 'citation_title';
        $this->legal_ruling['resourccitation_lastpagePageStart'] = 'citation_firstpage';
        $this->legal_ruling['resourccitation_lastpagePageEnd'] = 'citation_lastpage';
        $this->legal_ruling['resourceIsbn'] = 'citation_isbn';
        // (PARLIAMENTARY) BILL
        $this->bill['resourceTitle'] = 'citation_title';
        $this->bill['resourcepagePageStart'] = 'citation_firstpage';
        $this->bill['resourcepagePageEnd'] = 'citation_lastpage';
        // CLASSICAL WORK
        $this->classical['resourceTitle'] = 'citation_title';
        $this->classical['resourceIsbn'] = 'citation_isbn';
        // CONFERENCE PAPER
        $this->conference_paper['resourceTitle'] = 'citation_title';
        $this->conference_paper['collectionTitle'] = 'citation_conference_title';
        $this->conference_paper['resourceIsbn'] = 'citation_isbn';
        // CONFERENCE POSTER
        $this->conference_poster['resourceTitle'] = 'citation_title';
        $this->conference_poster['collectionTitle'] = 'citation_conference_title';
        $this->conference_poster['resourceIsbn'] = 'citation_isbn';
        // MISCELLANEOUS
        $this->miscellaneous['resourceTitle'] = 'citation_title';
        // MISCELLANEOUS SECTION
        $this->miscellaneous_section['resourceTitle'] = 'citation_title';
        $this->miscellaneous_section['collectionTitle'] = 'citation_journal_title';
        $this->miscellaneous_section['resourcepagePageStart'] = 'citation_firstpage';
        $this->miscellaneous_section['resourcepagePageEnd'] = 'citation_lastpage';
        // GOVERNMENT REPORT/DOCUMENTATION
        $this->government_report['resourceTitle'] = 'citation_title';
        $this->government_report['resourcepagePageStart'] = 'citation_firstpage';
        $this->government_report['resourcepagePageEnd'] = 'citation_lastpage';
        $this->government_report['resourceIsbn'] = 'citation_issn';
        // REPORT/DOCUMENTATION
        $this->report['resourcepagePageStart'] = 'citation_firstpage';
        $this->report['resourcepagePageEnd'] = 'citation_lastpage';
        $this->report['resourceIsbn'] = 'citation_issn';
        $this->report['resourceTitle'] = 'citation_title';
        // GOVERNMENT/LEGAL HEARING
        $this->hearing['resourceTitle'] = 'citation_title';
        $this->hearing['resourcepagePageStart'] = 'citation_firstpage';
        $this->hearing['resourcepagePageEnd'] = 'citation_lastpage';
        $this->hearing['resourceIsbn'] = 'citation_issn';
        // ONLINE DATABASE
        $this->database['resourceTitle'] = 'citation_title';
        // MANUSCRIPT
        $this->manuscript['resourceTitle'] = 'citation_title';
        $this->manuscript['collectionTitle'] = 'citation_journal_title';
        $this->manuscript['resourcepagePageStart'] = 'citation_firstpage';
        $this->manuscript['resourcepagePageEnd'] = 'citation_lastpage';
        $this->manuscript['resourceIsbn'] = 'citation_isbn';
        // MAP
        $this->map['resourceTitle'] = 'citation_title';
        $this->map['resourceIsbn'] = 'citation_isbn';
        // CHART
        $this->chart['resourceTitle'] = 'citation_title';
        // STATUTE
        $this->statute['resourcepagePageStart'] = 'citation_firstpage';
        $this->statute['resourcepagePageEnd'] = 'citation_lastpage';
        $this->statute['resourceTitle'] = 'citation_title';
        // PATENT
        $this->patent['resourceTitle'] = 'citation_title';
        // BROCHURE
        $this->brochure['resourceTitle'] = 'citation_title';
        // PERSONAL COMMUNICATION
        $this->personal['resourceTitle'] = 'citation_title';
        // PROCEEDINGS (complete set of)
        $this->proceedings['resourceTitle'] = 'citation_title';
        $this->proceedings['resourceIsbn'] = 'citation_issn';
        // MUSIC ALBUM
        $this->music_album['resourceTitle'] = 'citation_title';
        $this->music_album['resourceIsbn'] = 'citation_isbn';
        // MUSIC TRACK
        $this->music_track['resourceTitle'] = 'citation_title';
        $this->music_track['collectionTitle'] = 'citation_journal_title';
        $this->music_track['resourceIsbn'] = 'citation_isbn';
        // MUSIC SCORE
        $this->music_score['resourceTitle'] = 'citation_title';
        $this->music_score['resourceIsbn'] = 'citation_isbn';
        // UNPUBLISHED WORK
        $this->unpublished['resourceTitle'] = 'citation_title';
    }
}
