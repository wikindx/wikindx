<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */
 
/**
  * importAmazon class.
  *
  * Import a resource from Amazon using Amazon's web services.
  * You will need both an Amazon access key and a secret access key from:
  * http://www.amazon.com/gp/browse.html?node=3435361
  *
  * You will need to enter the access key into the variable $this->accessKey below
  * and you will need to enter the secret access key into the variable $this->secretAccessKey below
  * and you will need to enter the associate tag into the variable $this->associateTag below
  *
  * Uses PHP code freely adapted from Wolfgang Plaschg's BibWiki:
  * http://wolfgang.plaschg.net/bibwiki/
  */

/**
 * Import initial configuration and initialize the web server
 */
include_once("core/startup/WEBSERVERCONFIG.php");


class importamazon_MODULE
{
    public $authorize;
    public $menus;
    private $pluginmessages;
    private $coremessages;
    private $db;
    private $vars;
    private $badInput;
    private $creator;
    private $accessKey;
    private $secretAccessKey;
    private $associateTag;
    private $resourceAutoId;
    private $creatorIds = [];
    private $config;
    private $session;

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        include_once("core/messages/PLUGINMESSAGES.php");
        $this->pluginmessages = new PLUGINMESSAGES('importamazon', 'importamazonMessages');
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "config.php");
        $this->config = new importamazon_CONFIG();
        $this->authorize = $this->config->authorize;
        if ($menuInit)
        { // portion of constructor used for menu initialisation
            $this->makeMenu($this->config->menus);

            return; // need do nothing more.
        }
        $this->session = FACTORY_SESSION::getInstance();

        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->authorize))
        { // not authorised
            FACTORY_CLOSENOMENU::getInstance(); // die
        }

        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();


        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->creator = FACTORY_CREATOR::getInstance();
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('heading'));
    }
    /**
     * This is the initial method called from the menu item.
     *
     * check we have write access to the wikindx then return the category options.
     */
    public function init()
    {
        return $this->display();
    }
    /**
     * display options for conversions
     *
     * @param string|FALSE $message
     * @param bool $hidden
     */
    public function display($message = FALSE, $hidden = FALSE)
    {
        if (!$this->config->accessKey || ($this->config->accessKey == ''))
        {
            GLOBALS::addTplVar('content', HTML\p($this->pluginmessages->text('noAccessKey')));
        }
        elseif (!$this->config->secretAccessKey || ($this->config->secretAccessKey == ''))
        {
            GLOBALS::addTplVar('content', HTML\p($this->pluginmessages->text('noSecretAccessKey')));
        }
        else
        {
            if ($message)
            {
                $pString = $message;
            }
            else
            {
                $pString = '';
            }
            $pString .= FORM\formHeader("importamazon_input");
            $pString .= HTML\p($this->pluginmessages->text('region'));
            if ($hidden)
            {
                $pString .= $hidden;
            }
            if ($hidden && array_key_exists('url', $this->vars))
            {
                $pString .= BR . BR .
                    HTML\p(FORM\textInput($this->pluginmessages->text("url"), "url", $this->vars['url'], 100) .
                    BR . HTML\span($this->pluginmessages->text('urlHint'), 'hint'));
            }
            else
            {
                $pString .= HTML\p(FORM\textInput($this->pluginmessages->text("url"), "url", FALSE, 100) .
                    BR . HTML\span($this->pluginmessages->text('urlHint'), 'hint'));
            }
            $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Submit")));
            $pString .= FORM\formEnd();
            GLOBALS::addTplVar('content', $pString);
        }
    }
    /**
     * Get and parse URL input
     */
    public function input()
    {
        if (!$url = trim($this->vars['url']))
        {
            $this->badInput->close(HTML\p($this->pluginmessages->text('noInput'), 'error', 'center'), $this, 'display');
        }
        $amazonUrl = $isbn = FALSE;
        if (preg_match('/\d{5,}[A-Z]?/u', $url, $matches))
        {
            $isbn = $matches[0];
        }
        $components = UTF8::mb_explode('/', $url);
        foreach ($components as $component)
        {
            if (mb_strpos($component, 'www.') === 0)
            {
                $amazonUrl = mb_substr($component, 4);

                break;
            }
        }
        if (!$amazonUrl)
        {
            $this->badInput->close(HTML\p($this->pluginmessages->text('invalidURL1'), 'error', 'center'), $this, 'display');
        }
        if (!$isbn)
        {
            $this->badInput->close(HTML\p($this->pluginmessages->text('invalidURL2'), 'error', 'center'), $this, 'display');
        }
        $this->convertAmazonSource($isbn);
        // If we reach here, we've successfully input the title
        $message = HTML\p($this->pluginmessages->text('success'), 'success', 'center');

        return $this->display($message);
    }
    /**
     * Make the menus
     *
     * @param array $menuArray
     */
    private function makeMenu($menuArray)
    {
        $this->menus = [$menuArray[0] => [$this->pluginmessages->text('importAmazon') => "init"]];
    }
    /**
     * Check for duplicate title
     *
     * @param mixed $input
     *
     * @return bool
     */
    private function checkDuplication($input)
    {
        $noSort = $subTitle = FALSE;
        $title = str_replace(['{', '}'], '', $input['title']);
        if (array_key_exists('subtitle', $input))
        {
            $subTitle = str_replace(['{', '}'], '', $input['subtitle']);
            $this->db->formatConditions($this->db->replace($this->db->replace('resourceSubtitle', '{', ''), '}', '', FALSE) .
                $this->db->like(FALSE, $subTitle, FALSE));
        }
        else
        {
            $this->db->formatConditions(['resourceSubtitle' => ' IS NULL']);
        }
        if (array_key_exists('noSort', $input))
        {
            $noSort = str_replace(['{', '}'], '', $input['noSort']);
            $this->db->formatConditions($this->db->replace($this->db->replace('resourceNoSort', '{', ''), '}', '', FALSE) .
                $this->db->like(FALSE, $noSort, FALSE));
        }
        else
        {
            $this->db->formatConditions(['resourceNoSort' => ' IS NULL']);
        }
        $this->db->formatConditions(['resourceType' => 'book']);
        $this->db->formatConditions($this->db->replace($this->db->replace('resourceTitle', '{', ''), '}', '', FALSE) .
            $this->db->equal . $this->db->tidyInput($title));
        $resultset = $this->db->select('resource', $this->db->formatFields('resourceId') . ', ' .
            $this->db->replace($this->db->replace('resourceTitle', '{', ''), '}', '', FALSE) . ', ' .
            $this->db->replace($this->db->replace('resourceSubtitle', '{', ''), '}', '', FALSE) . ', ' .
            $this->db->replace($this->db->replace('resourceNoSort', '{', ''), '}', '', FALSE), TRUE, FALSE);
        if ($this->db->numRows($resultset))
        {
            $res = FACTORY_RESOURCECOMMON::getInstance();
            $bibStyle = FACTORY_BIBSTYLE::getInstance();
            $pString = HTML\p($this->pluginmessages->text('resourceExists'), 'error', 'center');
            $row = $this->db->fetchRow($resultset);
            $resultset = $res->getResource($row['resourceId']);
            $row = $this->db->fetchRow($resultset);
            $pString .= HTML\p($bibStyle->process($row), 'error', 'center');
            $this->badInput->close($pString);

            return FALSE;
        }

        return TRUE;
    }
    /**
     * write WKX_resource and grab lastautoID
     *
     * @param array $input
     * @param string $isbn
     */
    private function writeResourceTable($input, $isbn)
    {
        $fields[] = "resourceType";
        $values[] = 'book';
        $fields[] = "resourceTitle";
        $values[] = $input['title'];
        $fields[] = "resourceTitleSort";
        $values[] = $input['resourceTitleSort'];
        if (array_key_exists('noSort', $input))
        {
            $fields[] = "resourceNoSort";
            $values[] = $input['noSort'];
        }
        // subtitle
        if (array_key_exists('subtitle', $input))
        {
            $fields[] = "resourceSubtitle";
            $values[] = $input['subtitle'];
        }
        // ISBN
        $fields[] = "resourceIsbn";
        $values[] = $isbn;
        $this->db->insert('resource', $fields, $values);
        $this->resourceAutoId = $this->db->lastAutoId();
    }
    /**
     * parse author names
     *
     * @param array $input
     *
     * @return array
     */
    private function parseAuthor($input)
    {
        $surname = $von = $firstname = FALSE;
        $author = UTF8::mb_explode(" ", $input);
        if (count($author) == 1)
        {
            $surname = $author[0];
        }
        else
        {
            $tempFirst = [];
            $case = $this->getStringCase($author[0]);
            while ((($case == "upper") || ($case == "none")) && (count($author) > 0))
            {
                $tempFirst[] = array_shift($author);
                if (!empty($author))
                {
                    $case = $this->getStringCase($author[0]);
                }
            }

            list($von, $surname) = $this->getVonLast($author);
            if ($surname == "")
            {
                $surname = array_pop($tempFirst);
            }
            $firstname = implode(" ", $tempFirst);
        }

        return ['surname' => $surname, 'prefix' => $von, 'firstname' => $firstname];
    }
    /**
     * Gets the "von" and "last" part from the author array
     *
     * @param string $author
     *
     * @return array
     */
    private function getVonLast($author)
    {
        $surname = $von = "";
        $tempVon = [];
        $count = 0;
        $bVon = FALSE;
        foreach ($author as $part)
        {
            $case = $this->getStringCase($part);
            if ($count == 0)
            {
                if ($case == "lower")
                {
                    $bVon = TRUE;
                    if ($case == "none")
                    {
                        $count--;
                    }
                }
            }

            if ($bVon)
            {
                $tempVon[] = $part;
            }
            else
            {
                $surname = $surname . " " . $part;
            }

            $count++;
        }

        if (count($tempVon) > 0)
        {
            //find the first lowercase von starting from the end
            for ($i = (count($tempVon) - 1); $i > 0; $i--)
            {
                if ($this->getStringCase($tempVon[$i]) == "lower")
                {
                    break;
                }
                else
                {
                    $surname = array_pop($tempVon) . " " . $surname;
                }
            }

            if ($surname == "")
            { // von part was all lower chars, the last entry is surname
                $surname = array_pop($tempVon);
            }

            $von = implode(" ", $tempVon);
        }

        return [trim($von), trim($surname)];
    }
    /** returns the case of a string
     *
     * Case determination:
     * non-alphabetic chars are caseless
     * the first alphabetic char determines case
     * if a string is caseless, it is grouped to its neighbour string.
     *
     * @param string $string
     *
     * @return string
     */
    private function getStringCase($string)
    {
        $caseChar = "";
        $string = preg_replace("/\\d/u", "", $string);
        if (preg_match("/\\w/u", $string, $caseChar))
        {
            if (is_array($caseChar))
            {
                $caseChar = $caseChar[0];
            }
            if (preg_match("/[a-z]/u", $caseChar))
            {
                return "lower";
            }
            elseif (preg_match("/[A-Z]/u", $caseChar))
            {
                return "upper";
            }
            else
            {
                return "none";
            }
        }
        else
        {
            return "none";
        }
    }
    /**
     * Write WKX_creator
     *
     * @param array $string
     */
    private function writeCreatorTable($author)
    {
        if ($author['surname'])
        {
            $fields[] = "creatorSurname";
            $values[] = $this->trimString($author['surname']);
        }
        if ($author['firstname'])
        {
            $fields[] = "creatorFirstname";
            $values[] = $this->trimString($author['firstname']);
        }
        if ($author['prefix'])
        {
            $fields[] = "creatorPrefix";
            $values[] = $this->trimString($author['prefix']);
        }
        if (isset($fields))
        {
            if ($id = $this->creator->checkExists($author['surname'], $author['firstname'], '', $author['prefix']))
            {
                $this->creatorIds[] = $id;

                return;
            }
        }
        $this->db->insert('creator', $fields, $values);
        $this->creatorIds[] = $this->db->lastAutoId();
    }
    /**
     * write to WKX_resource_creator
     */
    private function writeResourceCreatorTable()
    {
        $mainSurname = FALSE;
        $order = 1;
        foreach ($this->creatorIds as $creatorId)
        {
            if (!$mainSurname)
            {
                $this->db->formatConditions(['creatorId' => $creatorId]);
                $mainSurname = $this->db->selectFirstField('creator', 'creatorSurname');
                $mainId = $creatorId;
            }
            $writeArray = [];
            $writeArray['resourcecreatorCreatorId'] = $creatorId;
            $writeArray['resourcecreatorResourceId'] = $this->resourceAutoId;
            $writeArray['resourcecreatorCreatorMain'] = $mainId;
            // remove all punctuation (keep apostrophe and dash for names such as Grimshaw-Aagaard and D'Eath)
            $writeArray['resourcecreatorCreatorSurname'] = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s\-\']/u', '', $mainSurname));
            $writeArray['resourcecreatorOrder'] = $order;
            $writeArray['resourcecreatorRole'] = '1';
            $this->db->insert('resource_creator', array_keys($writeArray), array_values($writeArray));
            ++$order;
        }
    }
    /**
     * write to WKX_publisher
     *
     * @param array $name
     */
    private function writePublisherTable($name)
    {
        // Check publisher doesn't already exist
        $publisher = FACTORY_PUBLISHER::getInstance();
        if ($publisherId = $publisher->checkExists($name, ''))
        {
            return $publisherId;
        }
        //  Doesn't exist, so write
        $fields[] = "publisherName";
        $values[] = $name;
        $fields[] = "publisherType";
        $values[] = 'book';
        $this->db->insert('publisher', $fields, $values);
        $this->db->deleteCache('cacheResourcePublishers');
        $this->db->deleteCache('cacheMetadataPublishers');

        return $this->db->lastAutoId();
    }
    /**
     * Write the bibtexKey field
     */
    private function writeBibtexKey()
    {
        $bibConfig = FACTORY_BIBTEXCONFIG::getInstance();
        $bibConfig->bibtex();
        $bibtexKeys = [];
        $recordset = $this->db->select('resource', 'resourceBibtexKey');
        while ($row = $this->db->fetchRow($recordset))
        {
            $bibtexKeys[] = $row['resourceBibtexKey'];
        }
        $letters = range('a', 'z');
        $sizeof = count($letters);
        $this->db->formatConditions(['resourceyearId' => $this->resourceAutoId]);
        $recordset = $this->db->select(['resource_year'], 'resourceyearYear1');
        $row = $this->db->fetchRow($recordset);
        if ($row['resourceyearYear1'])
        {
            $year = $row['resourceyearYear1'];
        }
        else
        {
            $year = FALSE;
        }
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorMain');
        $this->db->formatConditions(['resourcecreatorResourceId' => $this->resourceAutoId]);
        $this->db->formatConditions(['resourcecreatorOrder' => '1']);
        $this->db->ascDesc = $this->db->asc;
        $this->db->orderBy('resourcecreatorRole');
        $this->db->limit(1, 0); // pick just the first one
        $recordset = $this->db->select(['resource_creator'], ['creatorSurname', 'creatorPrefix']);
        $row = $this->db->fetchRow($recordset);
        $keyMade = FALSE;
        if ((!is_array($row) || !array_key_exists('creatorSurname', $row) || !$row['creatorSurname']))
        { // anonymous
            $base = 'anon' . $year;
        }
        else
        {
            $prefix = '';
            if ($row['creatorPrefix'])
            {
                $prefix = utf8_decode($row['creatorPrefix']);
                foreach ($bibConfig->bibtexSpChPlain as $key => $value)
                {
                    $char = preg_quote(UTF8::mb_chr($key), '/');
                    $prefix = preg_replace("/$char/u", "$value", $prefix);
                }
                $prefix = preg_replace("/\\W/u", '', $prefix);
            }
            $surname = utf8_decode($row['creatorSurname']);
            foreach ($bibConfig->bibtexSpChPlain as $key => $value)
            {
                $char = preg_quote(UTF8::mb_chr($key), '/');
                $surname = preg_replace("/$char/u", "$value", $surname);
            }
            $surname = preg_replace("/\\W/u", '', $surname);
            $base = $prefix . $surname . $year;
        }
        $bibtexKey = $base;
        for ($i = 0; $i < $sizeof; $i++)
        {
            if (array_search($bibtexKey, $bibtexKeys) === FALSE)
            {
                $keyMade = TRUE;

                break;
            }
            $bibtexKey = $base . $letters[$i];
        }
        if (!$keyMade)
        {
            $bibtexKey = $base . '.' . $this->resourceAutoId; // last resort
        }
        $this->db->formatConditions(['resourceId' => $this->resourceAutoId]);
        $this->db->update('resource', ['resourceBibtexKey' => $bibtexKey]);
    }
    /**
     *  The following PHP code freely taken and adapted from BibWiki
     *
     * @param mixed $isbn
     */
    private function convertAmazonSource($isbn)
    {
        # Assemble the REST request URL.
        # Ex: http://webservices.amazon.com/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=087VQWVFFHRTJC4Y89G2&Operation=ItemLookup&ItemId=349602495X&ResponseGroup=ItemAttributes&Version=2005-10-13

        // For ItemLookup semantic, cf. https://docs.aws.amazon.com/AWSECommerceService/latest/DG/ItemLookup.html
        // For Common Request Parameters, cf. https://docs.aws.amazon.com/AWSECommerceService/latest/DG/CommonRequestParameters.html
        $requestParams = [
            "Service" => "AWSECommerceService",
            "AWSAccessKeyId" => $this->config->accessKey,
            "AssociateTag" => $this->config->associateTag,
            "ContentType" => "text/xml",
            "Operation" => "ItemLookup",
            "ItemId" => $isbn,
            "ResponseGroup" => "ItemAttributes",
            // Use a fixed version of the API because we don't know
            // when it will change in the future
            "Version" => "2013-08-01",
            "Timestamp" => gmdate("Y-m-d\\TH:i:s\\Z"),
        ];

        $URL = $this->getRequest($this->config->productAdvertisingAPIEndpoint, $requestParams, $this->config->secretAccessKey);
        $noSortFound = FALSE;
        // Disable warning about HTTP error because Amazon uses it with ordinary error messages
        $content = file_get_contents($URL, FALSE, stream_context_create(['http' => ['ignore_errors' => TRUE]]));
        if ($content === FALSE)
        {
            $content = '';
        }

        // resource type -- must be book
        if (preg_match_all('/<ProductGroup>\s*(.*)\s*<\/ProductGroup>/iuU', $content, $matches))
        {
            if ($matches[1][0] != 'Book')
            {
                $this->badInput->close(HTML\p($this->pluginmessages->text('notBook'), 'error', 'center'), $this, 'display');
            }
        }
        if (preg_match_all('/<Title>\s*(.*)\s*<\/Title>/iuU', $content, $matches))
        {
            $title = UTF8::mb_explode(".", $matches[1][0], 2);
            $title = $this->trimString($title[0]);
            $titleArray = UTF8::mb_explode(":", $title, 2);
            if (array_key_exists(1, $titleArray))
            {
                $array['subtitle'] = $this->trimString($titleArray[1]);
            }
            $array['title'] = $title = $resourceTitleSort = $this->trimString($titleArray[0]);
            foreach (WIKINDX_NO_SORT as $pattern)
            {
                if (preg_match("/^($pattern)\\s(.*)|^\\{($pattern)\\s(.*)/ui", $array['title'], $matches))
                {
                    if (array_key_exists(3, $matches))
                    { // found second set of matches
                        $resourceTitleSort = $this->trimString($matches[4]);
                        $array['title'] = '{' . $resourceTitleSort;
                        $array['noSort'] = $this->trimString($matches[3]);
                    }
                    else
                    {
                        $array['title'] = $resourceTitleSort = $this->trimString($matches[2]);
                        $array['noSort'] = $this->trimString($matches[1]);
                    }

                    break;
                }
            }
            if (array_key_exists('subtitle', $array) && $array['subtitle'])
            {
                $resourceTitleSort .= ' ' . $array['subtitle'];
            }
            $array['resourceTitleSort'] = str_replace(['{', '}'], '', \HTML\stripHtml($resourceTitleSort));
            // If $this->vars['requestUrl'] == $request, we have already checked for duplication and so are proceeding to input regardless
            if (!array_key_exists('requestUrl', $this->vars) || ($this->vars['requestUrl'] != $URL))
            {
                $this->checkDuplication($array);
            }
            $this->writeResourceTable($array, $isbn);
        }
        if (!$this->resourceAutoId)
        {
            $this->badInput->close(HTML\p($this->pluginmessages->text('failure', $content), 'error', 'center'), $this, 'display');
        }
        if (preg_match_all('/<Author>\s*(.*)\s*<\/Author>/iuU', $content, $matches))
        {
            $matches = $matches[1];
            foreach ($matches as $author)
            {
                $authors = $this->parseAuthor($author);
                $this->writeCreatorTable($authors);
            }
            if (!empty($this->creatorIds))
            {
                $this->writeResourceCreatorTable();
                // remove cache files for creators
                $this->db->deleteCache('cacheResourceCreators');
                $this->db->deleteCache('cacheMetadataCreators');
            }
        }
        if (preg_match_all('/<Publisher>\s*(.*)\s*<\/Publisher>/iuU', $content, $matches))
        {
            if ($matches[1][0])
            {
                $publisherId = $this->writePublisherTable($this->trimString($matches[1][0]));
            }
        }
        $fields = $values = [];
        if (preg_match_all('/<PublicationDate>\s*(\d{4,}).*<\/PublicationDate>/iuU', $content, $matches))
        {
            if ($matches[1][0])
            {
                $fields[] = 'resourceyearYear1';
                $values[] = $matches[1][0];
                $fields[] = "resourceyearId";
                $values[] = $this->resourceAutoId;
                $this->db->insert('resource_year', $fields, $values);
            }
        }
        // Write WKX_resource_misc
        $fields = $values = [];
        $userId = $this->session->getVar("setup_UserId");
        if ($userId)
        {
            $fields[] = "resourcemiscAddUserIdResource";
            $values[] = $userId;
        }
        if (isset($publisherId))
        {
            $fields[] = "resourcemiscPublisher";
            $values[] = $publisherId;
        }
        $fields[] = "resourcemiscId";
        $values[] = $this->resourceAutoId;
        $this->db->insert('resource_misc', $fields, $values);
        // Create the bibTex key
        $this->writeBibtexKey();
        $this->db->insert(
            'resource_category',
            ['resourcecategoryResourceId', 'resourcecategoryCategoryId'],
            [$this->resourceAutoId, '1']
        ); // General category
        // Update summary table and insert timestamp values
        $totalResources = 1 + $this->db->selectFirstField('database_summary', 'databasesummaryTotalResources');
        $this->db->update('database_summary', ['databasesummaryTotalResources' => $totalResources]);
        $fields = $values = [];
        $fields[] = "resourcetimestampId";
        $values[] = $this->resourceAutoId;
        $fields[] = 'resourcetimestampTimestamp';
        $values[] = $this->db->formatTimestamp();
        $fields[] = 'resourcetimestampTimestampAdd';
        $values[] = $this->db->formatTimestamp();
        $this->db->insert('resource_timestamp', $fields, $values);
        $this->db->insert('statistics', ['statisticsResourceId'], [$this->resourceAutoId]);
    }
    /**
     * trimString
     *
     * @param string $input
     *
     * @return string
     */
    private function trimString($input)
    {
        return html_entity_decode(trim($input), FALSE, 'UTF-8');
    }
    /**
     * getRequest
     *
     * @param string $ServiceURL
     * @param string $ServiceParams
     * @param string $secretKey
     *
     * @return string
     */
    private function getRequest($ServiceURL, $ServiceParams, $secretKey)
    {
        // For signature algo, cf. https://docs.aws.amazon.com/AWSECommerceService/latest/DG/HMACSignatures.html

        // Get host and url
        $url = parse_url($ServiceURL);

        // Sort paramters
        ksort($ServiceParams);

        // Build the request
        $request = [];
        foreach ($ServiceParams as $key => $value)
        {
            $key = rawurlencode($key);
            $value = rawurlencode($value);
            $request[] = $key . "=" . $value;
        }

        $RESTString =
		    "GET" . LF .
		    $url['host'] . LF .
		    $url['path'] . LF .
		    implode("&", $request);

        $request[] = "Signature=" . urlencode(base64_encode(hash_hmac('sha256', $RESTString, $secretKey, TRUE)));

        return $url["scheme"] . "://" . $url['host'] . $url['path'] . "?" . implode("&", $request);
    }
}
