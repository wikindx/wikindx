<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * CMS hooks
 */
class CMS
{
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $badInput;

    // Constructor
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->config = FACTORY_CONFIG::getInstance();

        if (!WIKINDX_CMS_ALLOW)
        { // deny access
            die("CMS access denied by WIKINDX configuration");
        }

        $this->badInput = FACTORY_BADINPUT::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "cms"));
    }
    /**
     * display pop-up for a single resource
     */
    public function display()
    {
        if (!array_key_exists('id', $this->vars))
        {
            $this->badInput->closeType = 'closeNoMenu';
            $this->badInput->close($this->messages->text('inputError', 'missing'));
        }
        $id = $this->vars['id'];
        $pString = \HTML\p($this->messages->text('cms', 'introduction1'));
        $pString .= \HTML\p($this->getResourceQuery($id));
        $pString .= $this->displayTag();
        $pString .= BR . \HTML\hr() . BR;
        $pString .= \HTML\h($this->messages->text('cms', 'displayIds'), FALSE, 3);
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $links[] = \HTML\a(
            'link',
            $this->messages->text('resources', 'categories'),
            'index.php?action=cms_CMS_CORE&method=display&type=categories&id=' . $id
        );
        $links[] = \HTML\a(
            'link',
            $this->messages->text('resources', 'subcategories'),
            'index.php?action=cms_CMS_CORE&method=display&type=subcategories&id=' . $id
        );
        $links[] = \HTML\a(
            'link',
            $this->messages->text('resources', 'keywords'),
            'index.php?action=cms_CMS_CORE&method=display&type=keywords&id=' . $id
        );
        $links[] = \HTML\a(
            'link',
            $this->messages->text('resources', 'creator'),
            'index.php?action=cms_CMS_CORE&method=display&type=creators&id=' . $id
        );
        $links[] = \HTML\a(
            'link',
            $this->messages->text('resources', 'publisher'),
            'index.php?action=cms_CMS_CORE&method=display&type=publishers&id=' . $id
        );
        $links[] = \HTML\a(
            'link',
            $this->messages->text('resources', 'collection'),
            'index.php?action=cms_CMS_CORE&method=display&type=collections&id=' . $id
        );
        $pString .= \HTML\td(implode("&nbsp;&nbsp;&nbsp;", $links), 'width50percent');
        if (array_key_exists('type', $this->vars))
        {
            \AJAX\loadJavascript([$this->config->WIKINDX_BASE_URL . '/core/modules/list/searchSelect.js']);
            if ($this->vars['type'] == 'categories')
            {
                $category = FACTORY_CATEGORY::getInstance();
                $raw = $category->grabAll();
                if (is_array($raw) && !empty($raw))
                {
                    $jScript = 'index.php?action=cms_CMS_CORE&method=qStringAjax&type=getCategory';
                    $jsonArray = [];
                    $jsonArray[] = [
                        'startFunction' => 'triggerFromMultiSelect',
                        'script' => "$jScript",
                        'triggerField' => 'category',
                        'targetDiv' => 'qString',
                    ];
                    $js = \AJAX\jActionForm('onclick', $jsonArray);
                    foreach ($raw as $key => $value)
                    {
                        $array[$key] = $value . ":&nbsp;&nbsp;" . $key;
                    }
                    $pString .= \HTML\td(\FORM\selectFBoxValue(
                        $this->messages->text('resources', 'categories'),
                        'category',
                        $array,
                        1,
                        FALSE,
                        $js
                    ));
                }
            }
            elseif ($this->vars['type'] == 'subcategories')
            {
                $category = FACTORY_CATEGORY::getInstance();
                $raw = $category->grabSubAll();
                if (is_array($raw) && !empty($raw))
                {
                    $jScript = 'index.php?action=cms_CMS_CORE&method=qStringAjax&type=getSubcategory';
                    $jsonArray = [];
                    $jsonArray[] = [
                        'startFunction' => 'triggerFromMultiSelect',
                        'script' => "$jScript",
                        'triggerField' => 'subcategory',
                        'targetDiv' => 'qString',
                    ];
                    $js = \AJAX\jActionForm('onclick', $jsonArray);
                    foreach ($raw as $key => $value)
                    {
                        $sarray[$key] = $value . ":&nbsp;&nbsp;" . $key;
                    }
                    $pString .= \HTML\td(\FORM\selectFBoxValue(
                        $this->messages->text('resources', 'subcategories'),
                        'subcategory',
                        $sarray,
                        1,
                        FALSE,
                        $js
                    ));
                }
            }
            elseif ($this->vars['type'] == 'keywords')
            {
                $keyword = FACTORY_KEYWORD::getInstance();
                $raw = $keyword->grabAll();
                if (is_array($raw) && !empty($raw))
                {
                    $jScript = 'index.php?action=cms_CMS_CORE&method=qStringAjax&type=getKeyword';
                    $jsonArray = [];
                    $jsonArray[] = [
                        'startFunction' => 'triggerFromMultiSelect',
                        'script' => "$jScript",
                        'triggerField' => 'keyword',
                        'targetDiv' => 'qString',
                    ];
                    $js = \AJAX\jActionForm('onclick', $jsonArray);
                    foreach ($raw as $key => $value)
                    {
                        $array[$key] = $value . ":&nbsp;&nbsp;" . $key;
                    }
                    $pString .= \HTML\td(\FORM\selectFBoxValue(
                        $this->messages->text('resources', 'keywords'),
                        'keyword',
                        $array,
                        1,
                        FALSE,
                        $js
                    ));
                }
            }
            elseif ($this->vars['type'] == 'creators')
            {
                $creator = FACTORY_CREATOR::getInstance();
                $raw = $creator->grabAll();
                if (is_array($raw) && !empty($raw))
                {
                    $jScript = 'index.php?action=cms_CMS_CORE&method=qStringAjax&type=getCreator';
                    $jsonArray = [];
                    $jsonArray[] = [
                        'startFunction' => 'triggerFromMultiSelect',
                        'script' => "$jScript",
                        'triggerField' => 'creator',
                        'targetDiv' => 'qString',
                    ];
                    $js = \AJAX\jActionForm('onclick', $jsonArray);
                    foreach ($raw as $key => $value)
                    {
                        $array[$key] = $value . ":&nbsp;&nbsp;" . $key;
                    }
                    $pString .= \HTML\td(\FORM\selectFBoxValue(
                        $this->messages->text('resources', 'creator'),
                        'creator',
                        $array,
                        1,
                        FALSE,
                        $js
                    ));
                }
            }
            elseif ($this->vars['type'] == 'publishers')
            {
                $publisher = FACTORY_PUBLISHER::getInstance();
                $raw = $publisher->grabAll();
                if (is_array($raw) && !empty($raw))
                {
                    $jScript = 'index.php?action=cms_CMS_CORE&method=qStringAjax&type=getPublisher';
                    $jsonArray = [];
                    $jsonArray[] = [
                        'startFunction' => 'triggerFromMultiSelect',
                        'script' => "$jScript",
                        'triggerField' => 'publisher',
                        'targetDiv' => 'qString',
                    ];
                    $js = \AJAX\jActionForm('onclick', $jsonArray);
                    foreach ($raw as $key => $value)
                    {
                        $array[$key] = $value . ":&nbsp;&nbsp;" . $key;
                    }
                    $pString .= \HTML\td(\FORM\selectFBoxValue(
                        $this->messages->text('resources', 'publisher'),
                        'publisher',
                        $array,
                        1,
                        FALSE,
                        $js
                    ));
                }
            }
            elseif ($this->vars['type'] == 'collections')
            {
                $collection = FACTORY_COLLECTION::getInstance();
                $raw = $collection->grabAll();
                if (is_array($raw) && !empty($raw))
                {
                    $jScript = 'index.php?action=cms_CMS_CORE&method=qStringAjax&type=getCollection';
                    $jsonArray = [];
                    $jsonArray[] = [
                        'startFunction' => 'triggerFromMultiSelect',
                        'script' => "$jScript",
                        'triggerField' => 'collection',
                        'targetDiv' => 'qString',
                    ];
                    $js = \AJAX\jActionForm('onclick', $jsonArray);
                    foreach ($raw as $key => $value)
                    {
                        $array[$key] = $value . ":&nbsp;&nbsp;" . $key;
                    }
                    $pString .= \HTML\td(\FORM\selectFBoxValue(
                        $this->messages->text('resources', 'collection'),
                        'collection',
                        $array,
                        1,
                        FALSE,
                        $js
                    ));
                }
            }
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\HTML\div('qString', '&nbsp;'));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * Display pop-up for a display list
     */
    public function displayList()
    {
        $pString = \HTML\p($this->messages->text('cms', 'introduction2'));
        $sql = $this->session->getVar('sql_ListStmt');
        $pString .= \FORM\textareaReadonly(FALSE, FALSE, base64_encode($sql), 100, 40);
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSENOMENU::getInstance();
    }
    /**
     * AJAX-based DIV content creator for querystrings
     */
    public function qStringAjax()
    {

// if no ajaxReturn, quietly exit
        $div = \HTML\td(\HTML\div('qString', '&nbsp;')); // default
        if (array_key_exists('ajaxReturn', $this->vars))
        {
            $qString = $this->config->WIKINDX_BASE_URL . '/cmsprint.php?action=' . $this->vars['type'] . '&id=' . $this->vars['ajaxReturn'];
            $div = \HTML\td(\HTML\div('qString', $qString));
        }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $div]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Create SQL statement from querystring
     *
     * The following functions deal with external calls to http://..../wikindx4/cmsprint.php.  They are NOT called from within WIKINDX
     */
    public function queryDb()
    {
        if (empty($_GET) || !array_key_exists('type', $_GET))
        {
            die('Missing or incorrect queryString');
        }
        if (($_GET['type'] != 'getRecent') && !array_key_exists('id', $_GET))
        {
            die('Missing or incorrect queryString');
        }
        $db = FACTORY_DB::getInstance();
        if (array_key_exists('bibStyle', $_GET))
        {
            GLOBALS::setUserVar('Style', $_GET['bibStyle']);
        }
        else
        {
            GLOBALS::setUserVar('Style', WIKINDX_CMS_BIBSTYLE);
        }
        if (array_key_exists('language', $_GET))
        {
            GLOBALS::setUserVar('Language', \LOCALES\determine_locale($_GET['language']));
        }
        else
        {
            GLOBALS::setUserVar('Language', \LOCALES\determine_locale());
        }
        $res = FACTORY_RESOURCECOMMON::getInstance();
        $bibStyle = FACTORY_BIBSTYLE::getInstance();
        if (array_key_exists('id', $_GET))
        {
            $ids = UTF8::mb_explode(',', $_GET['id']);
        }
        // Remove WIKINDX-style hyperlink class.
        $pattern = preg_quote("<a class=\"rLink\"");
        // $sqlMethod only used for 'getKeyword', 'getCategory' and 'getCreator'
        if (array_key_exists('sqlMethod', $_GET) && (mb_strtolower($_GET['sqlMethod']) == 'and'))
        {
            $sqlMethod = ' AND ';
            $resourceFields = ['resourceId', 'resourceType', 'resourceTitle', 'resourceSubtitle', 'resourceShortTitle', 'resourceTitleSort', 'resourceTransTitle', 'resourceTransSubtitle', 'resourceTransShortTitle', 'resourceField1', 'resourceField2', 'resourceField3', 'resourceField4', 'resourceField5', 'resourceField6', 'resourceField7', 'resourceField8', 'resourceField9', 'resourceNoSort', 'resourceTransNoSort', 'resourceIsbn', 'resourceBibtexKey', 'resourceDoi'];
        }
        else
        {
            $sqlMethod = ' OR ';
        }
        $order = 'creatorSurname';
        if (array_key_exists('order', $_GET))
        {
            if ($_GET['order'] == 'timestamp')
            {
                $order = 'resourcetimestampTimestamp';
            }
            elseif ($_GET['order'] == 'year')
            {
                $order = 'resourceyearYear1';
            }
            elseif ($_GET['order'] == 'title')
            {
                $order = 'resourceTitleSort';
            }
        }
        if ($_GET['type'] == 'getResource')
        {
            $resultset = $res->getResource($ids, $order);
            while ($row = $db->fetchRow($resultset))
            {
                $items[$row['resourceId']] = preg_replace("/$pattern/u", '<a', $bibStyle->process($row));
            }
        }
        elseif ($_GET['type'] == 'getCategory')
        {
            if ($sqlMethod == ' AND ')
            {
                $index = 1;
                foreach ($ids as $id)
                {
                    $db->leftJoin([['resource_category' => $index]], $index . '.resourcecategoryResourceId', 'resourceId');
                    $db->formatConditions([$index . '.resourcecategoryCategoryId' => $id]);
                    $index++;
                }
            }
            else
            {
                $db->formatConditionsOneField($ids, 'resourcecategoryCategoryId');
                $db->leftJoin('resource_category', 'resourcecategoryResourceId', 'resourceId');
            }
            $resultset = $res->getResource(FALSE, $order);
            while ($row = $db->fetchRow($resultset))
            {
                $items[$row['resourceId']] = preg_replace("/$pattern/u", '<a', $bibStyle->process($row));
            }
        }
        elseif ($_GET['type'] == 'getKeyword')
        {
            if (($sqlMethod == ' AND ') && (count($ids) > 1))
            {
                $index = 1;
                foreach ($ids as $id)
                {
                    $db->leftJoin([['resource_keyword' => $index]], $index . '.resourcekeywordResourceId', 'resourceId');
                    $db->formatConditions([$index . '.resourcekeywordKeywordId' => $id]);
                    $db->formatConditions([$index . '.resourcekeywordResourceId' => ' IS NOT NULL']);
                    $index++;
                }
            }
            else
            {
                $db->formatConditionsOneField($ids, 'resourcekeywordKeywordId');
                $db->leftJoin('resource_keyword', 'resourcekeywordResourceId', 'resourceId');
            }
            $resultset = $res->getResource(FALSE, $order);
            while ($row = $db->fetchRow($resultset))
            {
                $items[$row['resourceId']] = preg_replace("/$pattern/u", '<a', $bibStyle->process($row));
            }
        }
        elseif ($_GET['type'] == 'getCreator')
        {
            if (($sqlMethod == ' AND ') && (count($ids) > 1))
            {
                $index = 1;
                foreach ($ids as $id)
                {
                    $db->leftJoin([['resource_creator' => $index]], $index . '.resourcecreatorResourceId', 'resourceId');
                    $db->formatConditions([$index . '.resourcecreatorCreatorId' => $id]);
                    $index++;
                }
                $db->leftJoin([['resource_creator' => 'c']], 'c.resourcecreatorResourceId', 'resourceId');
                $subQuery = $db->subQuery($db->selectNoExecute(
                    'resource',
                    array_merge(
                        $resourceFields,
                        [['c.resourcecreatorCreatorSurname' => 'creatorSurname'], 'c.resourcecreatorResourceId']
                    ),
                    TRUE,
                    TRUE,
                    TRUE
                ), 't1');
                $resultset = $res->getResource(FALSE, $order, FALSE, FALSE, $subQuery);
            }
            else
            {
                $db->formatConditionsOneField($ids, 'resourcecreatorCreatorId');
                $resultset = $res->getResource(FALSE, $order);
            }
            while ($row = $db->fetchRow($resultset))
            {
                $items[$row['resourceId']] = preg_replace("/$pattern/u", '<a', $bibStyle->process($row));
            }
        }
        elseif ($_GET['type'] == 'getPublisher')
        {
            // 'proceedings_article', 'proceedings', 'book' and 'book_article' resource types have publisher in 'miscField1' (if publisher exists)
            foreach ($ids as $id)
            {
                $conditionArray[] = $db->ifClause(
                    $db->formatFields('resourceType') . $db->equal . $db->tidyInput('proceedings_article') .
                    $db->or .
                    $db->formatFields('resourceType') . $db->equal . $db->tidyInput('proceedings') .
                    $db->or .
                    $db->formatFields('resourceType') . $db->equal . $db->tidyInput('book') .
                    $db->or .
                    $db->formatFields('resourceType') . $db->equal . $db->tidyInput('book_article'),
                    FALSE, // test is in previous statement
                    $db->formatFields('resourcemiscField1') . '=' . $db->tidyInput($id) .
                    $db->or .
                    $db->formatFields('resourcemiscPublisher') . '=' . $db->tidyInput($id),
                    $db->formatFields('resourcemiscPublisher') . '=' . $db->tidyInput($id)
                );
            }
            $db->formatConditions(implode($db->or, $conditionArray));
            $db->leftJoin('resource_misc', 'resourcemiscId', 'resourceId');
            $resultset = $res->getResource(FALSE, $order);
            while ($row = $db->fetchRow($resultset))
            {
                $items[$row['resourceId']] = preg_replace("/$pattern/u", '<a', $bibStyle->process($row));
            }
        }
        elseif ($_GET['type'] == 'getCollection')
        {
            $db->formatConditionsOneField($ids, 'resourcemiscCollection');
            $db->leftJoin('resource_misc', 'resourcemiscId', 'resourceId');
            $resultset = $res->getResource(FALSE, $order);
            while ($row = $db->fetchRow($resultset))
            {
                $items[$row['resourceId']] = preg_replace("/$pattern/u", '<a', $bibStyle->process($row));
            }
        }
        elseif ($_GET['type'] == 'getRecent')
        {
            $stmt = FACTORY_SQLSTATEMENTS::getInstance();
            $limit = array_key_exists('limit', $_GET) ? $_GET['limit'] : 10; // default limit of 10
            if (array_key_exists('days', $_GET))
            {
                $resultset = $db->query($stmt->frontSetDays($_GET['days'], $limit));
            }
            else
            {
                $resultset = $db->query($stmt->frontSetNumber($limit));
            }
            while ($row = $db->fetchRow($resultset))
            {
                $items[$row['resourceId']] = preg_replace("/$pattern/u", '<a', $bibStyle->process($row));
            }
        }
        /*
         * Additions by Andreas Wagner, to provide access to notes, quotes etc.
         * BEGINNING
         *
         */
        elseif ($_GET['type'] == 'getAbstract')
        {
            $db->formatConditionsOneField($ids, 'resourcetextId');
            $db->leftJoin('resource_text', 'resourcetextId', 'resourceId');
            $resultset = $res->getResource(FALSE, $order);
            while ($row = $db->fetchRow($resultset))
            {
                $items[$row['resourceId']] = $row['resourcetextAbstract'];
            }
        }
        elseif ($_GET['type'] == 'getNotes')
        {
            $db->formatConditionsOneField($ids, 'resourcetextId');
            $db->leftJoin('resource_text', 'resourcetextId', 'resourceId');
            $resultset = $res->getResource(FALSE, $order);
            while ($row = $db->fetchRow($resultset))
            {
                $items[$row['resourceId']] = $row['resourcetextNote'];
            }
        }
        elseif ($_GET['type'] == 'getQuote')
        {
            include_once('core/modules/resource/RESOURCEMETA.php');
            $meta = new RESOURCEMETA();
            $db->formatConditionsOneField($ids, 'resourceId');
            $resultset = $res->getResource(FALSE, $order);
            while ($row = $db->fetchRow($resultset))
            {
                $quotes = $meta->viewQuotes($row);
                foreach ($quotes as $key => $quote)
                {
                    if ($key === 'title')
                    {
                        continue;
                    }
                    $tempArray = [];
                    $comments = [];
                    $db->formatConditions(['resourcemetadataId' => $quote['metaId']]);
                    $db->formatConditions(['resourcemetadataPrivate' => 'N']); // only get public comments
                    $db->formatConditions(['resourcemetadataType' => 'qc']);
                    $db->orderBy('resourcemetadataTimestamp', TRUE, FALSE);
                    $resultset2 = $db->select('resource_metadata', ['resourcemetadataText', 'resourcemetadataTimestamp']);
                    while ($row2 = $db->fetchRow($resultset2))
                    {
                        $comments[] = ['comment' => $row2['resourcemetadataText'], 'timestamp' => $row2['resourcemetadataTimestamp']];
                    }
                    if (array_key_exists('quote', $quote))
                    {
                        $tempArray['quote'] = $quote['quote'];
                    }
                    if (array_key_exists('keywords', $quote))
                    {
                        $tempArray['keywords'] = $quote['keywords'];
                    }
                    if (array_key_exists('details', $quote))
                    {
                        $tempArray['details'] = $quote['details'];
                    }
                    $items[$row['resourceId']]['quotes'][] = $tempArray;
                }
            }
        }
        elseif ($_GET['type'] == 'getParaphrase')
        {
            include_once('core/modules/resource/RESOURCEMETA.php');
            $meta = new RESOURCEMETA();
            $db->formatConditionsOneField($ids, 'resourceId');
            $resultset = $res->getResource(FALSE, $order);
            while ($row = $db->fetchRow($resultset))
            {
                $paraphrases = $meta->viewParaphrases($row);
                foreach ($paraphrases as $key => $paraphrase)
                {
                    if ($key === 'title')
                    {
                        continue;
                    }
                    $tempArray = [];
                    $comments = [];
                    $db->formatConditions(['resourcemetadataId' => $paraphrase['metaId']]);
                    $db->formatConditions(['resourcemetadataPrivate' => 'N']); // only get public comments
                    $db->formatConditions(['resourcemetadataaction' => 'pc']);
                    $db->orderBy('resourcemetadataTimestamp', TRUE, FALSE);
                    $resultset2 = $db->select('resource_metadata', ['resourcemetadataText', 'resourcemetadataTimestamp']);
                    while ($row2 = $db->fetchRow($resultset2))
                    {
                        $comments[] = ['comment' => $row2['resourcemetadataText'], 'timestamp' => $row2['resourcemetadataTimestamp']];
                    }
                    if (array_key_exists('paraphrase', $paraphrase))
                    {
                        $tempArray['paraphrase'] = $paraphrase['paraphrase'];
                    }
                    if (array_key_exists('keywords', $paraphrase))
                    {
                        $tempArray['keywords'] = $paraphrase['keywords'];
                    }
                    if (array_key_exists('details', $paraphrase))
                    {
                        $tempArray['details'] = $paraphrase['details'];
                    }
                    $items[$row['resourceId']]['quotes'][] = $tempArray;
                }
            }
        }
        elseif ($_GET['type'] == 'getMusing')
        {
            include_once('core/modules/resource/RESOURCEMETA.php');
            $meta = new RESOURCEMETA();
            $db->formatConditionsOneField($ids, 'resourceId');
            $resultset = $res->getResource(FALSE, $order);
            while ($row = $db->fetchRow($resultset))
            {
                $musings = $meta->viewMusings($row);
                foreach ($musings as $key => $musing)
                {
                    if ($key === 'title')
                    {
                        continue;
                    }
                    $items[$row['resourceId']]['musings'][] = ['musing' => $musing['musing'],
                        'keywords' => $musing['keywords'],
                        'details' => $musing['details'], ];
                }
            }
        }
        /*
         * Additions by Andreas Wagner, to provide access to notes, quotes etc.
         * END
         */
        else
        {
            die('Missing or incorrect queryString');
        }

        if (!empty($items))
        {
            //output = base64_encode(serialize($items));
            $output = serialize($items);
        }
        else
        {
            // no resources found
            $output = '';
        }

        GLOBALS::addTplVar('content', $output);

        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Parse and format a block of text with [cite]...[/cite]tags
     *
     * @return string
     */
    public function parseText()
    {
        // Set bibliographic style
        if (array_key_exists('bibStyle', $_GET))
        {
            GLOBALS::setUserVar('Style', $_GET['bibStyle']);
        }
        else
        {
            GLOBALS::setUserVar('Style', WIKINDX_CMS_BIBSTYLE);
        }
        if (array_key_exists('language', $_GET))
        {
            GLOBALS::setUserVar('Language', \LOCALES\determine_locale($_GET['language']));
        }
        else
        {
            GLOBALS::setUserVar('Language', \LOCALES\determine_locale());
        }
        $cite = FACTORY_CITE::getInstance();
        if (array_key_exists('wikindxLink', $_GET) && $_GET['wikindxLink'] == 1)
        {
            $linkToWikindxResource = TRUE;
        }
        else
        {
            $linkToWikindxResource = FALSE;
        }

        if (!array_key_exists('address', $_GET) || !$_GET['address'])
        {
            die('Missing or incorrect queryString');
        }
        else
        {
            $address = urldecode($_GET['address']);

            if (file_exists($address))
            {
                $text = file_get_contents($address);
            }
            else
            {
                $text = '';
            }

            return $cite->parseCitations($text, 'html', $linkToWikindxResource);
        }
    }
    /**
     * Parse and format a resource list from a SQL statement
     *
     * @return string|FALSE
     */
    public function parseSql()
    {
        // Remove WIKINDX-style hyperlink class.
        $pattern = preg_quote("<a class=\"rLink\"");
        // Set bibliographic style
        if (array_key_exists('bibStyle', $_GET))
        {
            $this->session->setVar('setup_Style', $_GET['bibStyle']);
        }
        else
        {
            $this->session->setVar('setup_Style', WIKINDX_CMS_BIBSTYLE);
        }
        $bibStyle = FACTORY_BIBSTYLE::getInstance();

        if (!array_key_exists('address', $_GET) || !$_GET['address'])
        {
            die('Missing or incorrect queryString');
        }
        else
        {
            $address = urldecode($_GET['address']);

            if (file_exists($address))
            {
                $text = file_get_contents($address);
            }
            else
            {
                $text = '';
            }

            $text = file_get_contents($address);
            $querystring = base64_decode($text);

            $recordset = $this->db->query($querystring);

            while ($row = $this->db->fetchRow($recordset))
            {
                $items[$row['resourceId']] = preg_replace("/$pattern/u", '<a', $bibStyle->process($row));
            }

            if (!empty($items))
            {
                return base64_encode(serialize($items));
            }
            else
            {
                return FALSE; // Else, no resources found
            }
        }
    }
    /**
     ** Generate a CMS querystring for a single resource ID
     *
     * @param mixed $id
     *
     * @return string
     */
    private function getResourceQuery($id)
    {
        return $this->config->WIKINDX_BASE_URL . "/cmsprint.php?action=getResource&id=$id";
    }
    /**
     * Function form for generating CMS tag
     *
     * @return string
     */
    private function displayTag()
    {
        if (array_key_exists('type', $this->vars) && ($this->vars['type'] == 'generateCmsTag'))
        {
            $this->checkInput();
            $tag = $this->generateTag();
            unset($this->vars['type']);
        }
        else
        {
            $tag = FALSE;
        }
        $pString = \HTML\h($this->messages->text('cms', 'generateCmsTag'), FALSE, 3);

        $pString .= \FORM\formHeader("cms_CMS_CORE");
        $pString .= \FORM\hidden('method', 'display');
        $pString .= \FORM\hidden('type', 'generateCmsTag');
        $pString .= \FORM\hidden('id', $this->vars['id']);
        $session = GLOBALS::getUserVar('cmsTagStart');

        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text('cms', 'cmsTagStart'),
            'cmsTagStart',
            $session,
            20
        ));
        $session = array_key_exists('cmsPageStart', $this->vars) ? $this->vars['cmsPageStart'] : FALSE;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text('cms', 'pageStart'),
            'cmsPageStart',
            $session,
            10
        ));
        $session = array_key_exists('cmsPageEnd', $this->vars) ? $this->vars['cmsPageEnd'] : FALSE;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text('cms', 'pageEnd'),
            'cmsPageEnd',
            $session,
            10
        ), FALSE, 'left');
        $session = array_key_exists('cmsPreText', $this->vars) ? $this->vars['cmsPreText'] : FALSE;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text('cms', 'preText'),
            'cmsPreText',
            $session,
            20
        ));
        $session = array_key_exists('cmsPostText', $this->vars) ? $this->vars['cmsPostText'] : FALSE;
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text('cms', 'postText'),
            'cmsPostText',
            $session,
            20
        ));
        $session = GLOBALS::getUserVar('cmsTagEnd');
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text('cms', 'cmsTagEnd'),
            'cmsTagEnd',
            $session,
            20
        ));
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        // Encapsulating TABLE element from template
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(BR . \FORM\formSubmit($this->messages->text("submit", "Submit")));
        if ($tag)
        {
            $pString .= \HTML\td(BR . \FORM\textInput(
                $this->messages->text('cms', 'tag'),
                FALSE,
                $tag,
                50
            ));
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        $pString .= \FORM\formEnd();

        return $pString;
    }
    /**
     * Generate the CMS tag
     *
     * @return string
     */
    private function generateTag()
    {
        $tag = FALSE;
        if ($this->vars['cmsTagStart'])
        {
            $tag = $this->vars['cmsTagStart'];
        }
        $tag .= ':' . $this->vars['id'];
        if ($this->vars['cmsPageStart'])
        {
            $tag .= ':' . trim($this->vars['cmsPageStart']);
            if ($this->vars['cmsPageEnd'])
            {
                $tag .= '-' . trim($this->vars['cmsPageEnd']);
            }
        }
        elseif ($this->vars['cmsPageEnd'])
        { // Assume pageEnd is actually meant to be pageStart
            $tag .= ':' . trim($this->vars['cmsPageEnd']);
            $this->vars['cmsPageStart'] = $this->vars['cmsPageEnd'];
            unset($this->vars['cmsPageEnd']);
        }
        if ($this->vars['cmsPreText'])
        {
            $tag .= '|' . $this->vars['cmsPreText'];
            if ($this->vars['cmsPostText'])
            {
                $tag .= '`' . $this->vars['cmsPostText'];
            }
        }
        elseif ($this->vars['cmsPostText'])
        {
            $tag .= '|`' . $this->vars['cmsPostText'];
        }
        if ($this->vars['cmsTagEnd'])
        {
            $tag .= $this->vars['cmsTagEnd'];
        }

        return $tag;
    }
    /**
     * Check input from generateTag and write database value if required.
     */
    private function checkInput()
    {
        $cmsTagChanged = FALSE;
        if (GLOBALS::getUserVar('cmsTagStart') != $this->vars['cmsTagStart'])
        {
            $cmsTagChanged = TRUE;
            $this->session->setVar('setup_cmsTagStart', $this->vars['cmsTagStart']);
        }
        if (GLOBALS::getUserVar('cmsTagEnd') != $this->vars['cmsTagEnd'])
        {
            $cmsTagChanged = TRUE;
            $this->session->setVar('setup_cmsTagEnd', $this->vars['cmsTagEnd']);
        }
        if ($cmsTagChanged && $this->session->getVar('setup_UserId'))
        {
            $string = base64_encode(serialize([$this->vars['cmsTagStart'], $this->vars['cmsTagEnd']]));
            $this->db->formatConditions(['usersId' => $this->session->getVar('setup_UserId')]);
            $this->db->update('users', ['usersCmsTag' => $string]);
        }
    }
}
