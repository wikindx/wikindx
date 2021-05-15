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
 *	TEMPLATE PREVIEW class
 *
 *	Preview bibliographic style templates.
 */
class adminstyle_previewstyle
{
    private $vars;
    private $bibformat;

    public function __construct()
    {
        $this->vars = GLOBALS::getVars();
        $this->bibformat = FACTORY_BIBFORMAT::getInstance();
        $this->bibformat->initialise();
        $this->bibformat->wikindx = TRUE;
        $this->bibformat->preview = TRUE;
    }
    /**
     * display
     *
     * @return string
     */
    public function display()
    {
        $map = FACTORY_STYLEMAP::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "bibcitation", "PARSESTYLE.php"]));
        $adminstyle = new PARSESTYLE();
        $type = base64_decode($this->vars['templateName']);
        $templateString = preg_replace("/%u(\\d+)/u", "&#x$1;", base64_decode($this->vars['templateString']));
        $templateString = str_replace(['__WIKINDX__LESSTHAN__', '__WIKINDX__GREATERTHAN__'], ['&lt;', '&gt;'], $templateString);
        $fallbackString = preg_replace("/%u(\\d+)/u", "&#x$1;", base64_decode($this->vars['fallbackString']));
        $fallbackString = str_replace(['__WIKINDX__LESSTHAN__', '__WIKINDX__GREATERTHAN__'], ['&lt;', '&gt;'], $fallbackString);
        $style = json_decode(base64_decode($this->vars['style']), TRUE);
        $rewriteCreator = json_decode(base64_decode($this->vars['rewriteCreator']), TRUE);
        if (is_array($style))
        {
            foreach ($style as $key => $value)
            {
                // Convert javascript unicode e.g. %u2014 to HTML entities
                $value = preg_replace(
                    "/%u(\\d+)/u",
                    "&#x$1;",
                    str_replace(
                        ['__WIKINDX__SPACE__', '__WIKINDX__LESSTHAN__', '__WIKINDX__GREATERTHAN__'],
                        [' ', '&lt;', '&gt;'],
                        $value
                    )
                );
                $this->bibformat->style[str_replace("style_", "", $key)] = $value;
            }
            
            if (!$templateString && !$fallbackString)
            {
                return FALSE;
            }
            if (!$templateString)
            { // Use fallback style template instead
                $templateString = $fallbackString;
            }
            $templateArray = $adminstyle->parseStringToArray($type, $templateString, $map, TRUE);
            if (!$templateArray)
            {
                return FALSE;
            }
            $typeTemplateSet = $type . 'TemplateSet';
            $this->bibformat->$typeTemplateSet = TRUE;
            $this->bibformat->loadArrays();
            if (array_key_exists('independent', $templateArray))
            {
                $temp = $templateArray['independent'];
                foreach ($temp as $key => $value)
                {
                    $split = \UTF8\mb_explode("_", $key);
                    $independent[$split[1]] = $value;
                }
                $templateArray['independent'] = $independent;
            }
            $this->bibformat->$type = $templateArray;
            $this->loadArrays($type);
            $fields = [];
            if (array_key_exists('ajaxReturn', $this->vars))
            {
                $split = \UTF8\mb_explode(',', base64_decode($this->vars['ajaxReturn']));
                foreach ($split as $field)
                {
                    $fields[] = base64_decode($field);
                }
                foreach ($this->row as $key => $value)
                {
                    if (($key == 'noSort') || ($key == 'title') || ($key == 'subtitle'))
                    {
                        $tempArray[$key] = $value;

                        continue;
                    }
                    // Date
                    elseif ((($key == 'resourcemiscField2') || ($key == 'resourcemiscField3') || ($key == 'resourcemiscField5') ||
                        ($key == 'resourcemiscField6')) && (array_search('date', $fields) !== FALSE))
                    {
                        $tempArray[$key] = $value;

                        continue;
                    }
                    // Running time
                    elseif ((($key == 'resourcemiscField1') || ($key == 'resourcemiscField4')) &&
                        (array_search('runningTime', $fields) !== FALSE))
                    {
                        $tempArray[$key] = $value;

                        continue;
                    }
                    // Pages
                    elseif ((($key == 'pageStart') || ($key == 'pageEnd')) && (array_search('pages', $fields) !== FALSE))
                    {
                        $tempArray[$key] = $value;

                        continue;
                    }
                    // DOI
                    elseif (($key == 'doi') && (array_search('DOI', $fields) !== FALSE))
                    {
                        $tempArray[$key] = $value;

                        continue;
                    }
                    // URL
                    elseif (($key == 'url') && (array_search('URL', $fields) !== FALSE))
                    {
                        $tempArray[$key] = $value;

                        continue;
                    }
                    // ISSN
                    elseif (($key == 'resourceIsbn') && (array_search('ISSN', $fields) !== FALSE))
                    {
                        $tempArray[$key] = $value;

                        continue;
                    }
                    if (!array_key_exists($key, $this->bibformat->styleMap->{$type}))
                    {
                        continue;
                    }
                    if (array_search($this->bibformat->styleMap->{$type}[$key], $fields) !== FALSE)
                    {
                        $tempArray[$key] = $value;
                    }
                }
                if (($type == 'book') || ($type == 'book_article'))
                {
                    $tempArray['transNoSort'] = $this->row['transNoSort'];
                    $tempArray['transSubtitle'] = $this->row['transSubtitle'];
                }
                if (($type == 'book') || ($type == 'thesis'))
                {
                    $tempArray['resourcemiscField6'] = $this->row['numPages'];
                }
                if (($type == 'book_article') || ($type == 'book_chapter'))
                {
                    $tempArray['resourceField6'] = $this->row['numPages'];
                }
                $this->row = $tempArray;
                foreach ($rewriteCreator as $key => $value)
                {
                    $value = str_replace(
                        ['__WIKINDX__SPACE__', '__WIKINDX__LESSTHAN__', '__WIKINDX__GREATERTHAN__'],
                        [' ', '<', '>'],
                        $value
                    );
                    $this->bibformat->{$type}[$key] = $value;
                }
            }
            else
            {
                foreach ($rewriteCreator as $key => $value)
                {
                    $value = str_replace(
                        ['__WIKINDX__SPACE__', '__WIKINDX__LESSTHAN__', '__WIKINDX__GREATERTHAN__'],
                        [' ', '&lt;', '&gt;'],
                        $value
                    );
                    $this->bibformat->{$type}[$key] = $value;
                }
            }
            $pString = $this->process($type, $fields);

            return $pString;
        }
        else
        {
            return "Preview impossible.";
        }
    }
    /**
     * Process the example
     *
     * @param string $type
     * @param array $fields
     *
     * @return string
     */
    public function process($type, $fields)
    {
        // For WIKINDX, if type == book, book_chapter or book article and there exists both 'year1' and 'year2' in $row (entered as
        // publication year and reprint year respectively), then switch these around as 'year1' is
        // entered in the style template as 'originalPublicationYear' and 'year2' should be 'publicationYear'.
        if (($type == 'book') || ($type == 'book_chapter') || ($type == 'book_article'))
        {
            if (array_key_exists('resourceyearYear2', $this->row))
            {
                $year2 = stripslashes($this->row['resourceyearYear2']);
            }
            else
            {
                $year2 = FALSE;
            }
            /*
            if ($year2 && !array_key_exists('resourceyearYear1', $this->row))
            {
                $this->row['resourceyearYear1'] = $year2;
                unset($this->row['resourceyearYear2']);
            }
            */
            if ($year2 && array_key_exists('resourceyearYear1', $this->row))
            {
                $this->row['resourceyearYear2'] = stripslashes($this->row['resourceyearYear1']);
                $this->row['resourceyearYear1'] = $year2;
            }
        }
        $this->row = $this->bibformat->preProcess($type, $this->row);
        // Return $type is the OSBib resource type ($this->book, $this->web_article etc.) as used in STYLEMAP
        $type = $this->bibformat->type;
        // Various types of creator
        for ($index = 1; $index <= 5; $index++)
        {
            $nameType = 'creator' . $index;
            if (array_key_exists($nameType, $this->bibformat->styleMap->$type))
            {
                if (empty($fields) || (array_search($this->bibformat->styleMap->{$type}[$nameType], $fields) !== FALSE))
                {
                    $this->bibformat->formatNames($this->$nameType, $nameType);
                }
            }
        }
        // The title of the resource
        $this->createTitle();
        // edition
        if ($editionKey = array_search('edition', $this->bibformat->styleMap->$type))
        {
            $this->createEdition($editionKey);
        }
        // pageStart and pageEnd
        $this->pages = FALSE; // indicates not yet created pages for articles
        if (array_key_exists('pages', $this->bibformat->styleMap->$type))
        {
            $this->createPages();
        }
        // Date
        if (array_key_exists('date', $this->bibformat->styleMap->$type))
        {
            $this->createDate($type);
        }
        // runningTime for film/broadcast
        if (array_key_exists('runningTime', $this->bibformat->styleMap->$type))
        {
            $this->createRunningTime();
        }
        // web_article URL
        if (array_key_exists('URL', $this->bibformat->styleMap->$type) &&
            ($itemElement = $this->createUrl()))
        {
            $this->bibformat->addItem($itemElement, 'URL', FALSE);
        }
        // DOI
        if (array_key_exists('DOI', $this->bibformat->styleMap->$type) &&
            ($itemElement = $this->createDoi()))
        {
            $this->bibformat->addItem($itemElement, 'DOI', FALSE);
        }
        // the rest...  All other database resource fields that do not require special formatting/conversion.
        $this->bibformat->addAllOtherItems($this->row);
        // We now have an array for this item where the keys match the key names of $this->styleMap->$type
        // where $type is book, journal_article, thesis etc. and are now ready to map this against the defined
        // bibliographic style for each resource ($this->book, $this->book_article etc.).
        // This bibliographic style array not only provides the formatting and punctuation for each field but also
        // provides the order. If a field name does not exist in this style array, we print nothing.
        $pString = $this->bibformat->map();
        // ordinals such as 5$^{th}$
        $pString = preg_replace_callback("/(\\d+)\\$\\^\\{(.*)\\}\\$/u", [$this, "ordinals"], $pString);
        // remove extraneous {...}
        return preg_replace("/{(.*)}/Uu", "$1", $pString);
    }
    /**
     * callback for ordinals above
     *
     * @param array $matches
     *
     * @return string
     */
    public function ordinals($matches)
    {
        return $matches[1] . "<sup>" . $matches[2] . "</sup>";
    }
    /**
     * Create the resource title
     */
    public function createTitle()
    {
        $pString = stripslashes($this->row['noSort']) . ' ' .
            stripslashes($this->row['title']);
        if ($this->row['subtitle'])
        {
            $pString .= $this->bibformat->style['titleSubtitleSeparator'] .
            stripslashes($this->row['subtitle']);
        }
        // anything enclosed in {...} is to be left as is
        $this->bibformat->formatTitle($pString, "{", "}");
        // Title of the original work from which a translation has been made.
        if (array_key_exists('transTitle', $this->row))
        {
            $pString = stripslashes($this->row['transNoSort']) . ' ' .
                stripslashes($this->row['transTitle']);
            if ($this->row['transSubtitle'])
            {
                $pString .= $this->bibformat->style['titleSubtitleSeparator'] .
                stripslashes($this->row['transSubtitle']);
            }
            // anything enclosed in {...} is to be left as is
            $this->bibformat->formatTransTitle($pString, "{", "}");
        }
    }
    /**
     * Create the URL
     *
     * @return string
     */
    public function createUrl()
    {
        if (!array_key_exists('url', $this->row))
        {
            return FALSE;
        }
        $url = htmlspecialchars(stripslashes($this->row['url']));
        unset($this->row['url']);

        return $url;
    }
    /**
     * Create the DOI
     *
     * @return string
     */
    public function createDoi()
    {
        if (!array_key_exists('doi', $this->row))
        {
            return FALSE;
        }
        $doi = htmlspecialchars(stripslashes($this->row['doi']));
        unset($this->row['doi']);

        return $doi;
    }
    /**
     * Create date
     *
     * @param string $type
     */
    public function createDate($type)
    {
        $startDay = isset($this->row['resourcemiscField2']) ? stripslashes($this->row['resourcemiscField2']) : FALSE;
        $startMonth = isset($this->row['resourcemiscField3']) ? stripslashes($this->row['resourcemiscField3']) : FALSE;
        unset($this->row['resourcemiscField2']);
        unset($this->row['resourcemiscField3']);
        $endDay = isset($this->row['resourcemiscField5']) ? stripslashes($this->row['resourcemiscField5']) : FALSE;
        $endMonth = isset($this->row['resourcemiscField6']) ? stripslashes($this->row['resourcemiscField6']) : FALSE;
        unset($this->row['resourcemiscField5']);
        unset($this->row['resourcemiscField6']);
        $startDay = ($startDay == 0) ? FALSE : $startDay;
        $startMonth = ($startMonth == 0) ? FALSE : $startMonth;
        if (!$startMonth)
        {
            return;
        }
        $endDay = ($endDay == 0) ? FALSE : $endDay;
        $endMonth = ($endMonth == 0) ? FALSE : $endMonth;
        if (($type == 'web_article') || ($type == 'web_encyclopedia') ||
            ($type == 'web_site') || ($type == 'web_encyclopedia_article'))
        {
            if ($endDay && !$endMonth)
            {
                $endDay == FALSE;
            }
            elseif ($endMonth)
            {
                $this->bibformat->formatDate($endDay, $endMonth, FALSE, FALSE, TRUE);
                $endDay = $endMonth = FALSE;
            }
        }
        $this->bibformat->formatDate($startDay, $startMonth, $endDay, $endMonth);
    }
    /**
     * Create runningTime for film/broadcast
     */
    public function createRunningTime()
    {
        $minutes = array_key_exists('resourcemiscField1', $this->row) ?
            stripslashes($this->row['resourcemiscField1']) : FALSE;
        $hours = array_key_exists('resourcemiscField4', $this->row) ?
            stripslashes($this->row['resourcemiscField4']) : FALSE;
        if (!$hours && !$minutes)
        {
            return;
        }
        if (!$hours)
        {
            $hours = 0;
        }
        $this->bibformat->formatRunningTime($minutes, $hours);
    }
    /**
     * Create the edition number
     *
     * @param mixed $editionKey
     */
    public function createEdition($editionKey)
    {
        if (!array_key_exists($editionKey, $this->row) || !$this->row[$editionKey])
        {
            return FALSE;
        }
        $edition = stripslashes($this->row[$editionKey]);
        $this->bibformat->formatEdition($edition);
    }
    /**
     * Create page start and page end
     */
    public function createPages()
    {
        // empty field or page format already done
        if (!array_key_exists('pageStart', $this->row) || !$this->row['pageStart'] || $this->pages)
        {
            $this->pages = TRUE;

            return;
        }
        $this->pages = TRUE;
        $start = trim(stripslashes($this->row['pageStart']));
        $end = $this->row['pageEnd'] ? trim(stripslashes($this->row['pageEnd'])) : FALSE;
        $this->bibformat->formatPages($start, $end);
    }
    /**
     * Example values for  resources and creators
     *
     * @param mixed $type
     */
    private function loadArrays($type)
    {
        // Some of these default values may be overridden depending on the resource type.
        // The values here are the keys of resource type arrays in STYLEMAP.php
        $this->row = [
            'noSort' => "The",
            'title' => "{OSBib System}",
            'resourceShortTitle' => "{OSBIBSys}",
            'subtitle' => "Bibliographic formatting as it should be",
            'resourceyearYear1' => "2003", // publicationYear
            'resourceyearYear2' => "2004", // reprintYear
            'resourceyearYear3' => "2001-2003", // volume set publication year(s)
            'resourceyearYear4' => "1905", // transYear
            'pageStart' => "109",
            'pageEnd' => "122",
            'numPages' => "666",
            'resourcemiscField2' => '21', // start day
            'resourcemiscField3' => '8', // start month
            'resourcemiscField4' => '12', // numberOfVolumes
            'resourceField1' => 'The Software Series', // seriesTitle
            'resourceField2' => '3', // edition
            'resourceField3' => '9', // seriesNumber
            'resourceField4' => 'III', // volumeNumber
            'resourceField5' => '35', // umber
            'url' => 'http://bibliophile.sourceforge.net',
            'resourceIsbn' => '0-9876-123456',
            'publisherName' => 'Botswana Books',
            'publisherLocation' => 'Selebi Phikwe',
            'collectionTitle' => 'The Best of Open Source Software',
            'collectionTitleShort' => 'Best_OSS',
            "transTitle" => "{Systema OSBib}",
            "transSubtitle" => "Il Migliore di Migliore",
            'transNoSort' => "La",
            'transPublisherName' => 'Antiquarian Books',
            'transPublisherLocation' => 'London',
            'doi' => 'https://www.doi.org/',
        ];
        $authors = [
            0 => [
                'surname' => 'Grimshaw-Aagaard',
                'firstname' => 'Mark',
                'initials' => 'N',
                'prefix' => '',
            ],
            1 => [
                'surname' => 'Aulery',
                'firstname' => 'Stéphane',
                'initials' => '',
                'prefix' => '',
            ],
            2 => [
                'surname' => 'Boulanger',
                'firstname' => 'Christian',
                'initials' => '',
                'prefix' => '',
            ],
            4 => [
                'surname' => 'Gardey',
                'firstname' => 'Guillaume',
                'initials' => '',
                'prefix' => '',
            ],
        ];
        $editors = [
            0 => [
                'surname' => 'Mouse',
                'firstname' => 'Mickey',
                'initials' => '',
                'prefix' => '',
            ],
            1 => [
                'surname' => 'Duck',
                'firstname' => 'Donald',
                'initials' => 'D D',
                'prefix' => 'de',
            ],
        ];
        $revisers = [
            0 => [
                'surname' => 'Bush',
                'firstname' => 'George',
                'initials' => 'W',
                'prefix' => '',
            ],
        ];
        $translators = [
            0 => [
                'surname' => 'Lenin',
                'firstname' => 'Vladimir',
                'initials' => 'I',
                'prefix' => '',
            ],
        ];
        $seriesEditors = [
            0 => [
                'surname' => 'Freud',
                'firstname' => 'Sigmund',
                'initials' => '',
                'prefix' => '',
            ],
        ];
        $composers = [
            0 => [
                'surname' => 'Mozart',
                'firstname' => 'Wolfgang Amadeus',
                'initials' => '',
                'prefix' => '',
            ],
        ];
        $performers = [
            0 => [
                'surname' => 'Led Zeppelin',
                'firstname' => '',
                'initials' => '',
                'prefix' => '',
            ],
        ];
        $artists = [
            0 => [
                'surname' => 'Vinci',
                'firstname' => 'Leonardo',
                'initials' => '',
                'prefix' => 'da',
            ],
        ];
        $company = [
            0 => [
                'surname' => 'A Really Big Company',
                'firstname' => '',
                'initials' => '',
                'prefix' => '',
            ],
        ];
        $this->creator1 = $authors;
        $this->creator2 = $editors;
        $this->creator3 = $revisers;
        $this->creator4 = $translators;
        $this->creator5 = $seriesEditors;
        // For various types, override default settings above
        if ($type == 'genericMisc')
        {
            $this->row['resourceField2'] = 'software';
            $this->row['subtitle'] = '';
            $this->row['publisherName'] = 'Kalahari Soft';
        }
        elseif ($type == 'book_chapter')
        {
            $this->row['noSort'] = '';
            $this->row['subtitle'] = '';
            $this->row['title'] = '8';
        }
        elseif ($type == 'magazine_article')
        {
            $this->row['noSort'] = '';
            $this->row['subtitle'] = '';
            $this->row['title'] = '{OSS} Between the Sheets';
            $this->row['collectionTitle'] = 'The Scandal Rag';
            $this->row['collectionTitleShort'] = 'RAG';
            $this->row['resourceField2'] = 'interview';
            $this->row['resourceField4'] = 'Winter';
            $this->row['resourcemiscField5'] = '27'; // end day
            $this->row['resourcemiscField6'] = '8'; // end month
        }
        elseif ($type == 'journal_article')
        {
            $this->row['resourceField1'] = '23'; // volume number
            $this->row['resourcemiscField6'] = '9'; // end month
        }
        elseif ($type == 'newspaper_article')
        {
            $this->row['resourceField1'] = 'G2'; // section
            $this->row['resourceField2'] = 'Gabarone';
            $this->row['collectionTitle'] = 'TseTswana Times';
            $this->row['collectionTitleShort'] = 'TsTimes';
        }
        elseif ($type == 'proceedings')
        {
            $this->row['publisherName'] = 'International Association of Open Source Software';
            $this->row['publisherLocation'] = 'Serowe';
            $this->row['resourcemiscField5'] = '3'; // end day
            $this->row['resourcemiscField6'] = '9'; // end month
        }
        elseif ($type == 'conference_paper')
        {
            $this->row['publisherName'] = 'International Association of Open Source Software';
            $this->row['publisherLocation'] = 'Serowe';
        }
        elseif ($type == 'conference_poster')
        {
            $this->row['publisherName'] = 'International Association of Open Source Software';
            $this->row['publisherLocation'] = 'Serowe';
        }
        elseif ($type == 'proceedings_article')
        {
            $this->row['publisherName'] = 'International Association of Open Source Software';
            $this->row['publisherLocation'] = 'Serowe';
            $this->row['resourceField4'] = '12'; // volume No.
            $this->row['resourcemiscField5'] = '3'; // end day
            $this->row['resourcemiscField6'] = '9'; // end month
            $this->row['collectionTitle'] = '7th. International OSS Conference';
            $this->row['collectionTitleShort'] = '7_IntOSS';
        }
        elseif ($type == 'thesis')
        {
            $this->row['resourceField1'] = 'PhD';
            $this->row['resourceField2'] = 'thesis';
            $this->row['resourceField5'] = 'Pie in the Sky'; // Dept.
            $this->row['publisherName'] = 'University of Bums on Seats';
            $this->row['publisherLocation'] = 'Laputia';
            $this->creator1 = [$authors[0]];
            $this->creator2 = [$editors[0]];
        }
        elseif ($type == 'brochure')
        {
            $this->creator1 = $company;
        }
        elseif (($type == 'web_article') || ($type == 'web_encyclopedia') ||
            ($type == 'web_site') || ($type == 'web_encyclopedia_article'))
        {
            $this->row['resourceField1'] = '23';
            $this->row['resourcemiscField5'] = '27'; // publication day
            $this->row['resourcemiscField6'] = '8'; // publication month
        }
        elseif ($type == 'film')
        {
            $this->row['noSort'] = '';
            $this->row['subtitle'] = '';
            $this->row['title'] = 'Kill Will Vol. 3';
            $this->row['publisherName'] = 'Totally Brain Dead Films';
            $this->row['publisherLocation'] = '';
            $this->row['resourceField1'] = 'USA';
            $this->row['resourcemiscField1'] = '59'; // minutes
            $this->row['resourcemiscField4'] = '5'; // hours
        }
        elseif ($type == 'broadcast')
        {
            $this->row['noSort'] = '';
            $this->row['subtitle'] = '';
            $this->row['title'] = 'We put people on TV and humiliate them';
            $this->row['publisherName'] = 'Lowest Common Denominator Productions';
            $this->row['publisherLocation'] = 'USA';
            $this->row['resourcemiscField1'] = '45'; // minutes
            $this->row['resourcemiscField4'] = ''; // hours
        }
        elseif ($type == 'music_album')
        {
            $this->row['noSort'] = '';
            $this->row['subtitle'] = 'Canon & Gigue';
            $this->row['title'] = 'Pachelbel';
            $this->row['isbn'] = '447-285-2';
            $this->row['publisherName'] = 'Archiv';
            $this->row['resourceField2'] = 'CD'; // medium
            $this->row['resourceyearYear1'] = '1982-1983';
        }
        elseif ($type == 'music_track')
        {
            $this->row['noSort'] = '';
            $this->row['subtitle'] = '';
            $this->row['title'] = 'Dazed and Confused';
            $this->row['collectionTitle'] = 'Led Zeppelin 1';
            $this->row['collectionTitleShort'] = 'LZ1';
            $this->row['isbn'] = '7567826322';
            $this->row['publisherName'] = 'Atlantic';
            $this->row['resourceField2'] = 'CD'; // medium
            $this->row['resourceyearYear1'] = '1994';
        }
        elseif ($type == 'music_score')
        {
            $this->row['noSort'] = '';
            $this->row['subtitle'] = '';
            $this->row['title'] = 'Sonata in A Minor';
            $this->row['isbn'] = '3801 05945';
            $this->row['publisherName'] = 'Alfred Publishing';
            $this->row['publisherLocation'] = 'New York';
            $this->row['resourceyearYear1'] = '1994';
        }
        elseif ($type == 'artwork')
        {
            $this->row['noSort'] = '';
            $this->row['subtitle'] = '';
            $this->row['title'] = 'Art? What Art?';
            $this->row['publisherName'] = 'More Money than Sense';
            $this->row['publisherLocation'] = 'New York';
            $this->row['resourceField2'] = 'Movement in protoplasma';
            $this->creator1 = $artists;
        }
        elseif ($type == 'software')
        {
            $this->row['resourceField2'] = 'PHP source code'; // type
            $this->row['resourceField4'] = '1.3'; // version
            $this->row['publisherName'] = 'Kalahari Soft';
            $this->row['publisherLocation'] = 'Maun';
        }
        elseif ($type == 'audiovisual')
        {
            $this->row['noSort'] = '';
            $this->row['subtitle'] = '';
            $this->row['title'] = 'Whispering Sands';
            $this->row['resourceField1'] = 'Chobe ArtWorks Series'; // series title
            $this->row['resourceField2'] = 'video installation'; //medium
            $this->row['resourceField4'] = 'IV'; // series number
            $this->row['publisherName'] = 'Ephemera';
            $this->row['publisherLocation'] = 'Maun';
            $this->creator1 = $artists;
        }
        elseif ($type == 'database')
        {
            $this->row['noSort'] = 'The';
            $this->row['subtitle'] = 'Sotware Listings';
            $this->row['title'] = 'Blue Pages';
            $this->row['publisherName'] = 'Kalahari Soft';
            $this->row['publisherLocation'] = 'Maun';
        }
        elseif ($type == 'government_report')
        {
            $this->row['noSort'] = 'The';
            $this->row['subtitle'] = '';
            $this->row['title'] = 'State of Things to Come';
            $this->row['resourceField1'] = 'Prognostications'; // section
            $this->row['resourceField2'] = 'Pie in the Sky'; // department
            $this->row['publisherName'] = 'United Nations';
        }
        elseif ($type == 'hearing')
        {
            $this->row['resourceField1'] = 'Committee on Unworldly Activities'; // committee
            $this->row['resourceField2'] = 'United Nations'; // legislative body
            $this->row['resourceField3'] = 'Summer'; //session
            $this->row['resourceField4'] = '113'; // document number
            $this->row['resourcemiscField4'] = '27'; // no. of volumes
        }
        elseif ($type == 'statute')
        {
            $this->row['resourceField1'] = '101.43a'; // public law no.
            $this->row['resourceField2'] = 'Lex Hammurabi'; // code
            $this->row['resourceField3'] = 'Autumn'; //session
            $this->row['resourceField4'] = '34-A'; // section
            $this->row['resourceyearYear1'] = '1563 BC';
        }
        elseif ($type == 'legal_ruling')
        {
            $this->row['noSort'] = 'The';
            $this->row['subtitle'] = '';
            $this->row['title'] = 'People v. George';
            $this->row['resourceField1'] = 'Court of Public Law'; // section
            $this->row['resourceField2'] = 'Appellate Decision'; // type
            $this->row['publisherName'] = 'Legal Pulp';
            $this->row['publisherLocation'] = 'Gabarone';
        }
        elseif ($type == 'case')
        {
            $this->row['noSort'] = 'The';
            $this->row['subtitle'] = '';
            $this->row['title'] = 'People v. George';
            $this->row['resourceField1'] = 'Public Law'; // reporter
            $this->row['resourceField4'] = 'XIV'; // reporter volume
            $this->row['publisherName'] = 'Supreme Court';
        }
        elseif ($type == 'bill')
        {
            $this->row['noSort'] = 'The';
            $this->row['subtitle'] = '';
            $this->row['title'] = 'People v. George';
            $this->row['resourceField1'] = 'Court of Public Law'; // section
            $this->row['resourceField2'] = 'Lex Hammurabi'; // code
            $this->row['resourceField4'] = 'Spring'; // session
            $this->row['publisherName'] = 'United Nations';
            $this->row['publisherLocation'] = 'New York';
        }
        elseif ($type == 'patent')
        {
            $this->row['resourceField1'] = 'Journal of Patents'; // publishedSource
            $this->row['resourceField3'] = '289763[e].x-233'; // application no.
            $this->row['resourceField4'] = 'bibliographic software'; // type
            $this->row['resourceField5'] = '5564763[E].X-233'; // int. pat. no.
            $this->row['resourceField6'] = 'OSBib'; // int. title
            $this->row['resourceField7'] = 'software'; // int. class
            $this->row['resourceField8'] = '0-84784-AAH.z'; // pat. no.
            $this->row['resourceField9'] = 'not awarded'; // legal status
            $this->row['publisherName'] = 'Lawyers Inc.'; // assignee
            $this->row['publisherLocation'] = 'New Zealand';
        }
        elseif ($type == 'personal')
        {
            $this->row['noSort'] = '';
            $this->row['subtitle'] = '';
            $this->row['title'] = 'Save up to 80% on Microsoft Products!';
            $this->row['resourceField2'] = 'email'; // type
        }
        elseif ($type == 'unpublished')
        {
            $this->row['resourceField2'] = 'manuscript'; // type
            $this->row['publisherName'] = 'University of Bums on Seats';
            $this->row['publisherLocation'] = 'Laputia';
        }
        elseif ($type == 'classical')
        {
            $this->row['noSort'] = '';
            $this->row['subtitle'] = '';
            $this->row['title'] = 'Sed quis custodiet ipsos custodes?';
            $this->row['resourceField4'] = 'Codex XIX'; // volume
            $this->row['resourceyearYear1'] = '114 BC'; // volume
        }
        elseif ($type == 'manuscript')
        {
            $this->row['resourceField2'] = 'manuscript'; // type
            $this->row['publisherName'] = 'University of Bums on Seats';
            $this->row['publisherLocation'] = 'Laputia';
        }
        elseif ($type == 'map')
        {
            $this->row['noSort'] = '';
            $this->row['subtitle'] = '';
            $this->row['title'] = 'Mappa Mundi';
            $this->row['resourceField1'] = 'Maps of the World'; // series title
            $this->row['resourceField2'] = 'isomorphic projection'; // type
        }
        elseif ($type == 'chart')
        {
            $this->row['noSort'] = '';
            $this->row['subtitle'] = '';
            $this->row['title'] = 'Incidence of Sniffles in the New York Area';
            $this->row['resourceField1'] = 'sniff_1.gif'; // filename
            $this->row['resourceField2'] = 'The GIMP'; // program
            $this->row['resourceField3'] = '800*600'; // size
            $this->row['resourceField4'] = 'GIF'; // type
            $this->row['resourceField5'] = '1.1a'; // version
            $this->row['resourceField6'] = '11'; // number
            $this->row['publisherName'] = 'University of Bums on Seats';
            $this->row['publisherLocation'] = 'Laputia';
        }
        elseif ($type == 'miscellaneous')
        {
            $this->row['noSort'] = '';
            $this->row['subtitle'] = '';
            $this->row['title'] = 'Making Sunlight from Cucumbers';
            $this->row['resourceField2'] = 'thin air'; // medium
            $this->row['publisherName'] = 'University of Bums on Seats';
            $this->row['publisherLocation'] = 'Laputia';
        }
        elseif ($type == 'miscellaneous_section')
        {
            $this->row['noSort'] = '';
            $this->row['subtitle'] = '';
            $this->row['title'] = 'Making Sunlight from Cucumbers';
            $this->row['resourceField2'] = 'hot air'; // medium
            $this->row['publisherName'] = 'University of Bums on Seats';
            $this->row['publisherLocation'] = 'Laputia';
            $this->row['collectionTitle'] = '7th. International OSS Conference';
            $this->row['collectionTitleShort'] = '7_IntOSS';
        }
    }
}
