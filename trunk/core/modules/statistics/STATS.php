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
 * STATISTICS class
 */
class STATS
{
    private $db;
    private $vars;
    private $success;
    private $messages;
    private $errors;
    private $session;
    private $resourceMap;
    private $indexes = [];
    private $sum = [];
    private $totalResources;
    private $collectedSurnames = [];
    private $initials = [];
    private $prefix = [];
    private $sameAs = [];
    private $miscField1 = [];

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->resourceMap = FACTORY_RESOURCEMAP::getInstance();
        $type = '';
        if ($this->vars['method'] == 'totals')
        {
            $type = $this->messages->text("menu", "statisticsTotals");
        }
        elseif ($this->vars['method'] == 'users')
        {
            $type = $this->messages->text("menu", "statisticsUsers");
        }
        elseif ($this->vars['method'] == 'keywords')
        {
            $type = $this->messages->text("menu", "statisticsKeywords");
        }
        elseif ($this->vars['method'] == 'years')
        {
            $type = $this->messages->text("menu", "statisticsYears");
        }
        elseif ($this->vars['method'] == 'allCreators')
        {
            $type = $this->messages->text("menu", "statisticsAllCreators");
        }
        elseif ($this->vars['method'] == 'mainCreators')
        {
            $type = $this->messages->text("menu", "statisticsMainCreators");
        }
        elseif ($this->vars['method'] == 'publishers')
        {
            $type = $this->messages->text("menu", "statisticsPublishers");
        }
        elseif ($this->vars['method'] == 'collections')
        {
            $type = $this->messages->text("menu", "statisticsCollections");
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "statistics") . ' - ' . $type);
    }
    /**
     * Totals
     */
    public function totals()
    {
        $pString = $this->getTotals();
        $pString .= $this->resourceAccesses();
        $pString .= $this->resourceDates();
        if ($this->session->getVar("setup_Write"))
        {
            $pString .= $this->userData();
        }
        $pString .= $this->resourceTypes();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Users stats
     */
    public function users()
    {
        if (!$this->session->getVar("setup_Write") && !WIKINDX_DISPLAY_USER_STATISTICS)
        {
            $authorize = FACTORY_AUTHORIZE::getInstance();
			$authorize->initLogon();
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        $icons = FACTORY_LOADICONS::getInstance();
        list($users, $resources, $quotes, $paraphrases, $musings) = $this->getUsers();
        if (empty($users))
        {
            GLOBALS::addTplVar('content', $this->messages->text("statistics", "noUserStats"));

            return;
        }
        if (!array_key_exists('function', $this->vars))
        {
            arsort($resources);
            $sort = $resources;
        }
        else
        {
            switch ($this->vars['function']) {
                case 'resBottom':
                        arsort($resources);
                        $sort = $resources;

                        break;
                case 'quoTop':
                        asort($quotes);
                        $sort = $quotes;

                        break;
                case 'quoBottom':
                        arsort($quotes);
                        $sort = $quotes;

                        break;
                case 'parTop':
                        asort($paraphrases);
                        $sort = $paraphrases;

                        break;
                case 'parBottom':
                        arsort($paraphrases);
                        $sort = $paraphrases;

                        break;
                case 'musTop':
                        asort($musings);
                        $sort = $musings;

                        break;
                case 'musBottom':
                        arsort($musings);
                        $sort = $musings;

                        break;
                default:
                        asort($resources);
                        $sort = $resources;

                        break;
            }
        }
        
        $totR = $this->db->selectCountOnly("resource", "resourceId");
        
        $this->db->formatConditions(['resourcemetadataType' => 'q']);
        $totQ = $this->db->selectCountOnly("resource_metadata", "resourcemetadataId");
        
        $this->db->formatConditions(['resourcemetadataType' => 'p']);
        $totP = $this->db->selectCountOnly("resource_metadata", "resourcemetadataId");
        
        $this->db->formatConditions(['resourcemetadataType' => 'm']);
        $totM = $this->db->selectCountOnly("resource_metadata", "resourcemetadataId");
        
        $pString = \HTML\p($this->messages->text("statistics", "userStats"));
        $pString .= \HTML\tableStart('generalTable borderStyleSolid');
        $pString .= \HTML\trStart();
        $pString .= \HTML\td('&nbsp;');
        $toTop = \HTML\a(
            $icons->getClass("toTop"),
            $icons->getHTML("toTop"),
            'index.php?action=statistics_STATS_CORE' . htmlentities('&method=users&function=resTop')
        );
        $toBottom = \HTML\a(
            $icons->getClass("toBottom"),
            $icons->getHTML("toBottom"),
            'index.php?action=statistics_STATS_CORE' . htmlentities('&method=users&function=resBottom')
        );
        $pString .= \HTML\td(\HTML\strong($this->messages->text("statistics", "userResources")) .
            '&nbsp;' . $toTop . '&nbsp;' . $toBottom);
        $toTop = \HTML\a(
            $icons->getClass("toTop"),
            $icons->getHTML("toTop"),
            'index.php?action=statistics_STATS_CORE' . htmlentities('&method=users&function=quoTop')
        );
        $toBottom = \HTML\a(
            $icons->getClass("toBottom"),
            $icons->getHTML("toBottom"),
            'index.php?action=statistics_STATS_CORE' . htmlentities('&method=users&function=quoBottom')
        );
        $pString .= \HTML\td(\HTML\strong($this->messages->text("statistics", "userQuotes")) .
            '&nbsp;' . $toTop . '&nbsp;' . $toBottom);
        $toTop = \HTML\a(
            $icons->getClass("toTop"),
            $icons->getHTML("toTop"),
            'index.php?action=statistics_STATS_CORE' . htmlentities('&method=users&function=parTop')
        );
        $toBottom = \HTML\a(
            $icons->getClass("toBottom"),
            $icons->getHTML("toBottom"),
            'index.php?action=statistics_STATS_CORE' . htmlentities('&method=users&function=parBottom')
        );
        $pString .= \HTML\td(\HTML\strong($this->messages->text("statistics", "userParaphrases")) .
            '&nbsp;' . $toTop . '&nbsp;' . $toBottom);
        $toTop = \HTML\a(
            $icons->getClass("toTop"),
            $icons->getHTML("toTop"),
            'index.php?action=statistics_STATS_CORE' . htmlentities('&method=users&function=musTop')
        );
        $toBottom = \HTML\a(
            $icons->getClass("toBottom"),
            $icons->getHTML("toBottom"),
            'index.php?action=statistics_STATS_CORE' . htmlentities('&method=users&function=musBottom')
        );
        $pString .= \HTML\td(\HTML\strong($this->messages->text("statistics", "userMusings")) .
            '&nbsp;' . $toTop . '&nbsp;' . $toBottom);
        $pString .= \HTML\trEnd();
        foreach ($sort as $userId => $null)
        {
            $pString .= \HTML\trStart();
            $name = $users[$userId]['usersFullname'] ? $users[$userId]['usersFullname'] : $users[$userId]['usersUsername'];
            $pString .= \HTML\td(\HTML\strong($name));
            $numR = array_key_exists($userId, $resources) ? $resources[$userId] : 0;
            $numRLink = $numR ? \HTML\a('link', $numR, "index.php?action=list_LISTSOMERESOURCES_CORE" .
                htmlentities("&method=userResourceProcess&id=" . $userId)) : $numR;
            $pString .= \HTML\td($numRLink . '/' . $totR . ' (' . round(100 * ($numR / ($totR != 0 ? $totR : 1)), 2) . '%)');
            $numQ = array_key_exists($userId, $quotes) ? $quotes[$userId] : 0;
            $numQLink = $numQ ? \HTML\a('link', $numQ, "index.php?action=list_LISTSOMERESOURCES_CORE" .
                htmlentities("&method=userQuoteProcess&id=" . $userId)) : $numQ;
            $pString .= \HTML\td($numQLink . '/' . $totQ . ' (' . round(100 * ($numQ / ($totQ != 0 ? $totQ : 1)), 2) . '%)');
            $numP = array_key_exists($userId, $paraphrases) ? $paraphrases[$userId] : 0;
            $numPLink = $numP ? \HTML\a('link', $numP, "index.php?action=list_LISTSOMERESOURCES_CORE" .
                htmlentities("&method=userParaphraseProcess&id=" . $userId)) : $numP;
            $pString .= \HTML\td($numPLink . '/' . $totP . ' (' . round(100 * ($numP / ($totP != 0 ? $totP : 1)), 2) . '%)');
            $numM = array_key_exists($userId, $musings) ? $musings[$userId] : 0;
            $numMLink = $numM ? \HTML\a('link', $numM, "index.php?action=list_LISTSOMERESOURCES_CORE" .
                htmlentities("&method=userMusingProcess&id=" . $userId)) : $numM;
            $pString .= \HTML\td($numMLink . '/' . $totM . ' (' . round(100 * ($numM / ($totM != 0 ? $totM : 1)), 2) . '%)');
            $pString .= \HTML\trEnd();
        }
        $pString .= \HTML\tableEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Set the maturity index for a resource
     */
    public function setMaturityIndex()
    {
        $navigate = FACTORY_NAVIGATE::getInstance();
        $gatekeep = FACTORY_GATEKEEP::getInstance();
        $gatekeep->init();
        if (!array_key_exists('resourceId', $this->vars) || !array_key_exists('maturityIndex', $this->vars) ||
        !is_numeric($this->vars['maturityIndex']))
        {
        	$navigate->resource($this->vars['resourceId'], "inputError_invalid", TRUE);
        	FACTORY_CLOSE::getInstance();
        }
        $mIndex = round(\UTF8\mb_trim($this->vars['maturityIndex']), 1);
        if ($mIndex > 10)
        {
            $mIndex = 10;
        }
        elseif ($mIndex < 0)
        {
            $mIndex = 0;
        }
        $this->db->formatConditions(['resourcemiscId' => $this->vars['resourceId']]);
        $this->db->updateSingle(
            'resource_misc',
            $this->db->formatFields('resourcemiscMaturityIndex') . "=" . $this->db->tidyInput($mIndex)
        );
        $navigate->resource($this->vars['resourceId'], 'maturityIndex', $error);
        FACTORY_CLOSE::getInstance();
    }
    /**
     * Get stats for keywords
     */
    public function keywords()
    {
        if (!$this->session->getVar("setup_Write") && !WIKINDX_DISPLAY_STATISTICS)
        {
            $authorize = FACTORY_AUTHORIZE::getInstance();
			$authorize->initLogon();
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        // Use an unlimited memmory temporarily,
        // because the recordset can be really huge
        // Memory is reset automatically at the next script.
        // large numbers of indexes can use memory . . .
        ini_set('memory_limit', '-1');
        $this->getTotalsB();
        $this->getKeywords();
        arsort($this->sum);
        $pString = HTML\tableStart('generalTable borderStyleSolid');
        $maxNo = FALSE;
        foreach ($this->sum as $id => $value)
        {
            if (!$value)
            {
                continue;
            }
            if (!$maxNo)
            {
                $maxNo = $value; // first in row ordered DESC
            }
            $pString .= \HTML\trStart();
            $pString .= \HTML\td(\HTML\a(
                'link',
                $this->indexes[$id],
                'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=keywordProcess&id=' . $id)
            ));
            $pString .= $this->greenBar($value, $maxNo, $id);
            $pString .= \HTML\trEnd();
        }
        $pString .= \HTML\p(HTML\tableEnd());
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Get stats for publication years
     */
    public function years()
    {
        if (!$this->session->getVar("setup_Write") && !WIKINDX_DISPLAY_STATISTICS)
        {
            $authorize = FACTORY_AUTHORIZE::getInstance();
			$authorize->initLogon();
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        // Use an unlimited memmory temporarily,
        // because the recordset can be really huge
        // Memory is reset automatically at the next script.
        // large numbers of indexes can use memory . . .
        ini_set('memory_limit', '-1');
        $this->getTotalsB();
        $this->getYears();
        $pString = HTML\tableStart('generalTable borderStyleSolid');
        arsort($this->sum);
        $maxNo = FALSE;
        foreach ($this->sum as $id => $value)
        {
            if (!$value)
            {
                continue;
            }
            if (!$maxNo)
            {
                $maxNo = $value; // first in row ordered DESC
            }
            $pString .= \HTML\trStart();
            $pString .= \HTML\td(\HTML\a(
                'link',
                $this->indexes[$id],
                'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=yearProcess&id=' . base64_encode($id))
            ));
            $pString .= $this->greenBar($value, $maxNo, $id);
            $pString .= \HTML\trEnd();
        }
        $pString .= \HTML\p(HTML\tableEnd());
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Get stats for all creators
     */
    public function allCreators()
    {
        if (!$this->session->getVar("setup_Write") && !WIKINDX_DISPLAY_STATISTICS)
        {
            $authorize = FACTORY_AUTHORIZE::getInstance();
			$authorize->initLogon();
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        // Use an unlimited memmory temporarily,
        // because the recordset can be really huge
        // Memory is reset automatically at the next script.
        // large numbers of indexes can use memory . . .
        ini_set('memory_limit', '-1');
        $this->getTotalsB();
        $this->getCreators(FALSE);
        arsort($this->sum);
        $pString = HTML\tableStart('generalTable borderStyleSolid');
        $maxNo = FALSE;
        foreach ($this->sum as $id => $value)
        {
            if (!$value)
            {
                continue;
            }
            if (!$maxNo)
            {
                $maxNo = $value; // first in row ordered DESC
            }
            $name = $this->indexes[$id];
            if (array_key_exists($id, $this->initials))
            {
                $name .= $this->initials[$id];
            }
            $pString .= \HTML\trStart();
            $pString .= \HTML\td(\HTML\a(
                'link',
                $name,
                'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=creatorProcess&id=' . $id)
            ));
            $pString .= $this->greenBar($value, $maxNo, $id);
            $pString .= \HTML\trEnd();
        }
        $pString .= \HTML\p(HTML\tableEnd());
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Get stats for main creators
     */
    public function mainCreators()
    {
        if (!$this->session->getVar("setup_Write") && !WIKINDX_DISPLAY_STATISTICS)
        {
            $authorize = FACTORY_AUTHORIZE::getInstance();
			$authorize->initLogon();
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        // Use an unlimited memmory temporarily,
        // because the recordset can be really huge
        // Memory is reset automatically at the next script.
        // large numbers of indexes can use memory . . .
        ini_set('memory_limit', '-1');
        $this->getTotalsB();
        $this->getCreators(TRUE);
        arsort($this->sum);
        $pString = HTML\tableStart('generalTable borderStyleSolid');
        $maxNo = FALSE;
        foreach ($this->sum as $id => $value)
        {
            if (!$value)
            {
                continue;
            }
            if (!$maxNo)
            {
                $maxNo = $value; // first in row ordered DESC
            }
            $name = $this->indexes[$id];
            if (array_key_exists($id, $this->initials))
            {
                $name .= $this->initials[$id];
            }
            $pString .= \HTML\trStart();
            $pString .= \HTML\td(\HTML\a(
                'link',
                $name,
                'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=creatorProcess&id=' . $id)
            ));
            $pString .= $this->greenBar($value, $maxNo, $id);
            $pString .= \HTML\trEnd();
        }
        $pString .= \HTML\p(HTML\tableEnd());
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Get stats for collections
     */
    public function collections()
    {
        if (!$this->session->getVar("setup_Write") && !WIKINDX_DISPLAY_STATISTICS)
        {
            $authorize = FACTORY_AUTHORIZE::getInstance();
			$authorize->initLogon();
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        // Use an unlimited memmory temporarily,
        // because the recordset can be really huge
        // Memory is reset automatically at the next script.
        // large numbers of indexes can use memory . . .
        ini_set('memory_limit', '-1');
        $this->getTotalsB();
        $this->getCollections();
        arsort($this->sum);
        $pString = HTML\tableStart('generalTable borderStyleSolid');
        $maxNo = FALSE;
        foreach ($this->sum as $id => $value)
        {
            if (!$value)
            {
                continue;
            }
            if (!$maxNo)
            {
                $maxNo = $value; // first in row ordered DESC
            }
            $pString .= \HTML\trStart();
            $pString .= \HTML\td(\HTML\a(
                'link',
                $this->indexes[$id],
                'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=collectionProcess&id=' . $id)
            ));
            $pString .= $this->greenBar($value, $maxNo, $id);
            $pString .= \HTML\trEnd();
        }
        $pString .= \HTML\p(HTML\tableEnd());
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Get stats for cpublishers
     */
    public function publishers()
    {
        if (!$this->session->getVar("setup_Write") && !WIKINDX_DISPLAY_STATISTICS)
        {
            $authorize = FACTORY_AUTHORIZE::getInstance();
			$authorize->initLogon();
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        // Use an unlimited memmory temporarily,
        // because the recordset can be really huge
        // Memory is reset automatically at the next script.
        // large numbers of indexes can use memory . . .
        ini_set('memory_limit', '-1');
        $this->getTotalsB();
        $this->getPublishers();
        arsort($this->sum);
        $pString = HTML\tableStart('generalTable borderStyleSolid');
        $maxNo = FALSE;
        foreach ($this->sum as $id => $value)
        {
            if (!$value)
            {
                continue;
            }
            if (!$maxNo)
            {
                $maxNo = $value; // first in row ordered DESC
            }
            $pString .= \HTML\trStart();
            if (array_key_exists($id, $this->miscField1))
            {
                $pString .= \HTML\td(\HTML\a('link', $this->indexes[$id], 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=specialPublisherProcess&id=' . $id)));
            }
            else
            {
                $pString .= \HTML\td(\HTML\a('link', $this->indexes[$id], 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=publisherProcess&id=' . $id)));
            }
            $pString .= $this->greenBar($value, $maxNo, $id);
            $pString .= \HTML\trEnd();
        }
        $pString .= \HTML\p(HTML\tableEnd());
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Get users' data
     *
     * @return array key is usersId in both arrays
     */
    private function getUsers()
    {
        $users = $resources = $quotes = $paraphrases = $musings = [];
        $recordset = $this->db->select('users', ['usersId', 'usersUsername', 'usersFullname']);
        while ($rowUsers = $this->db->fetchRow($recordset))
        {
            $users[$rowUsers['usersId']]['usersUsername'] = $rowUsers['usersUsername'];
            $users[$rowUsers['usersId']]['usersFullname'] = $rowUsers['usersFullname'];
            $this->db->formatConditions(['resourcemiscAddUserIdResource' => $rowUsers['usersId']]);
            $recRes = $this->db->selectCounts('resource_misc', 'resourcemiscAddUserIdResource');
            while ($rowRes = $this->db->fetchRow($recRes))
            {
                $resources[$rowUsers['usersId']] = $rowRes['count'];
            }
            $this->db->formatConditions(['resourcemetadataAddUserId' => $rowUsers['usersId']]);
            $this->db->formatConditions(['resourcemetadataType' => 'q']);
            $recQuo = $this->db->selectCounts('resource_metadata', 'resourcemetadataAddUserId');
            while ($rowQuo = $this->db->fetchRow($recQuo))
            {
                $quotes[$rowUsers['usersId']] = $rowQuo['count'];
            }
            $this->db->formatConditions(['resourcemetadataAddUserId' => $rowUsers['usersId']]);
            $this->db->formatConditions(['resourcemetadataType' => 'p']);
            $recPar = $this->db->selectCounts('resource_metadata', 'resourcemetadataAddUserId');
            while ($rowPar = $this->db->fetchRow($recPar))
            {
                $paraphrases[$rowUsers['usersId']] = $rowPar['count'];
            }
            $this->db->formatConditions(['resourcemetadataPrivate' => 'N']);
            $this->db->formatConditions(['resourcemetadataAddUserId' => $rowUsers['usersId']]);
            $this->db->formatConditions(['resourcemetadataType' => 'm']);
            $recMus = $this->db->selectCounts('resource_metadata', 'resourcemetadataAddUserId');
            while ($rowMus = $this->db->fetchRow($recMus))
            {
                $musings[$rowUsers['usersId']] = $rowMus['count'];
            }
            if (!array_key_exists($rowUsers['usersId'], $resources))
            {
                $resources[$rowUsers['usersId']] = 0;
            }
            if (!array_key_exists($rowUsers['usersId'], $quotes))
            {
                $quotes[$rowUsers['usersId']] = 0;
            }
            if (!array_key_exists($rowUsers['usersId'], $paraphrases))
            {
                $paraphrases[$rowUsers['usersId']] = 0;
            }
            if (!array_key_exists($rowUsers['usersId'], $musings))
            {
                $musings[$rowUsers['usersId']] = 0;
            }
        }

        return [$users, $resources, $quotes, $paraphrases, $musings];
    }
    /**
     * getTotals
     *
     * @return string
     */
    private function getTotals()
    {
        $nbTotalResources = $this->db->selectCountOnly("resource", "resourceId");
        $this->totalResources = $nbTotalResources;
        
        $this->db->formatConditions(['resourcemetadataType' => 'q']);
        $nbTotalQuotes = $this->db->selectCountOnly("resource_metadata", "resourcemetadataId");
        
        $this->db->formatConditions(['resourcemetadataType' => 'p']);
        $nbTotalParaphrases = $this->db->selectCountOnly("resource_metadata", "resourcemetadataId");
        
        $this->db->formatConditions(['resourcemetadataType' => 'm']);
        $nbTotalMusings = $this->db->selectCountOnly("resource_metadata", "resourcemetadataId");
        
        $string = BR . $this->messages->text("statistics", "totalResources") .
            "&nbsp;&nbsp;" . \HTML\em($nbTotalResources);
        
        if (WIKINDX_METADATA_ALLOW)
        {
            $string .= BR . $this->messages->text("statistics", "totalQuotes") .
                "&nbsp;&nbsp;" . \HTML\em($nbTotalQuotes);
            $string .= BR . $this->messages->text("statistics", "totalParaphrases") .
                "&nbsp;&nbsp;" . \HTML\em($nbTotalParaphrases);
            $string .= BR . $this->messages->text("statistics", "totalMusings") .
                "&nbsp;&nbsp;" . \HTML\em($nbTotalMusings);
        }

        return \HTML\p($string);
    }
    /**
     * resourceAccesses
     *
     * @return string
     */
    private function resourceAccesses()
    {
        $row = $this->db->selectMax('statistics_resource_views', 'statisticsresourceviewsCount');
        $string = $this->messages->text("statistics", "maxAccesses") .
            "&nbsp;&nbsp;" . \HTML\em($row['statisticsresourceviewsCount']);
        $recordset = $this->db->selectMin('statistics_resource_views', 'statisticsresourceviewsCount');
        $row = $this->db->fetchRow($recordset);
        $string .= BR . $this->messages->text("statistics", "minAccesses") .
            "&nbsp;&nbsp;" . \HTML\em($row['statisticsresourceviewsCount']);

        return \HTML\p($string);
    }
    /**
     * resourceDates
     *
     * @return string
     */
    private function resourceDates()
    {
        $recordset = $this->db->selectMin('resource_timestamp', 'resourcetimestampTimestampAdd');
        $row = $this->db->fetchRow($recordset);
        $string = $this->messages->text("statistics", "firstAdded") .
            "&nbsp;&nbsp;" . \HTML\em(\LOCALES\dateFormatFromString($row['resourcetimestampTimestampAdd']));
        $row = $this->db->selectMax('resource_timestamp', 'resourcetimestampTimestampAdd');
        $string .= BR . $this->messages->text("statistics", "lastAdded") .
        "&nbsp;&nbsp;" . \HTML\em(\LOCALES\dateFormatFromString($row['resourcetimestampTimestampAdd']));
        $average = $this->db->selectAverageDate('resource_timestamp', 'resourcetimestampTimestampAdd');
        $average = \LOCALES\dateFormatFromString($average);
        $string .= BR . $this->messages->text("statistics", "meanAddedResource") .
            "&nbsp;&nbsp;" . \HTML\em($average);

        return \HTML\p($string);
    }
    /**
     * userData
     *
     * @return string
     */
    private function userData()
    {
        $string = "";
        // Resources
        $this->db->leftJoin('users', 'usersId', 'resourcemiscAddUserIdResource');
        $recordset = $this->db->selectCountMax(
            'resource_misc',
            ['resourcemiscAddUserIdResource', 'usersUsername', 'usersFullname']
        );
        $row = $this->db->fetchRow($recordset);
        if (is_array($row))
        {
            $user = \HTML\a(
                'link',
                $this->getUsername($row),
                "index.php?action=list_LISTSOMERESOURCES_CORE" . htmlentities("&method=userResourceProcess&id=" . $row['resourcemiscAddUserIdResource'])
            );
            $string .= $this->messages->text("statistics", "userResourceTotal") .
                "&nbsp;&nbsp;" . \HTML\em($row['count'] . "&nbsp;($user)");
        }
        if (!WIKINDX_METADATA_ALLOW)
        {
            return \HTML\p($string);
        }
        // Quotes
        $this->db->leftJoin('users', 'usersId', 'resourcemetadataAddUserId');
        $this->db->formatConditions(['resourcemetadataType' => 'q']);
        $recordset = $this->db->selectCountMax('resource_metadata', ['resourcemetadataAddUserId', 'usersUsername', 'usersFullname']);
        $row = $this->db->fetchRow($recordset);
        if (is_array($row))
        {
            $user = \HTML\a(
                'link',
                $this->getUsername($row),
                "index.php?action=list_LISTSOMERESOURCES_CORE" . htmlentities("&method=userQuoteProcess&id=" . $row['resourcemetadataAddUserId'])
            );
            $string .= BR . $this->messages->text("statistics", "userQuoteTotal") .
                "&nbsp;&nbsp;" . \HTML\em($row['count'] . "&nbsp;($user)");
        }
        // Paraphrases
        $this->db->leftJoin('users', 'usersId', 'resourcemetadataAddUserId');
        $this->db->formatConditions(['resourcemetadataType' => 'p']);
        $recordset = $this->db->selectCountMax(
            'resource_metadata',
            ['resourcemetadataAddUserId', 'usersUsername', 'usersFullname']
        );
        $row = $this->db->fetchRow($recordset);
        if (is_array($row))
        {
            $user = \HTML\a(
                'link',
                $this->getUsername($row),
                "index.php?action=list_LISTSOMERESOURCES_CORE" . htmlentities("&method=userParaphraseProcess&id=" . $row['resourcemetadataAddUserId'])
            );
            $string .= BR . $this->messages->text("statistics", "userParaphraseTotal") .
                "&nbsp;&nbsp;" . \HTML\em($row['count'] . "&nbsp;($user)");
        }
        // Public musings
        $this->db->formatConditions(['resourcemetadataPrivate' => 'N']);
        $this->db->formatConditions(['resourcemetadataType' => 'm']);
        $this->db->leftJoin('users', 'usersId', 'resourcemetadataAddUserId');
        $recordset = $this->db->selectCountMax(
            'resource_metadata',
            ['resourcemetadataAddUserId', 'usersUsername', 'usersFullname']
        );
        $row = $this->db->fetchRow($recordset);
        if (is_array($row))
        {
            $user = \HTML\a(
                'link',
                $this->getUsername($row),
                "index.php?action=list_LISTSOMERESOURCES_CORE" . htmlentities("&method=userMusingProcess&id=" . $row['resourcemetadataAddUserId'])
            );
            $string .= BR . $this->messages->text("statistics", "userMusingTotal") .
                "&nbsp;&nbsp;" . \HTML\em($row['count'] . "&nbsp;($user)");
        }

        return \HTML\p($string);
    }
    /**
     * getUsername
     *
     * @param mixed $row
     *
     * @return string
     */
    private function getUsername(&$row)
    {
        if (!is_array($row))
        {
            return $this->messages->text("user", "unknown");
        }
        elseif ($row['usersFullname'])
        {
            return $row['usersFullname'];
        }
        elseif ($row['usersUsername'])
        {
            return $row['usersUsername'];
        }
        else
        {
            return $this->messages->text("user", "unknown");
        }
    }
    /**
     * resourceTypes
     *
     * @return string
     */
    private function resourceTypes()
    {
        $pString = \HTML\strong($this->messages->text("statistics", "resourceTypes"));
        $pString .= \HTML\tableStart('left');
        $this->db->ascDesc = $this->db->desc;
        $this->db->orderBy('count', TRUE, FALSE);
        $recordset = $this->db->selectCounts('resource', 'resourceType');
        $maxNo = FALSE;
        while ($row = $this->db->fetchRow($recordset))
        {
            if (!$maxNo)
            {
                $maxNo = $row['count']; // first in row ordered DESC
            }
            $pString .= \HTML\trStart();
            $pString .= \HTML\td(\HTML\a(
                'link',
                $this->messages->text("resourceType", $row['resourceType']),
                'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=typeProcess&id=' . $row['resourceType'])
            ));
            // 100% width of bar == arbitrary 500 px
            $percentage = round((($row['count'] / $this->totalResources) * 100), 1);
            $width = round((($row['count'] / $maxNo) * 500), 0);
            $bar = \HTML\img("core/modules/statistics/green.gif", $width, 15, $row['resourceType']);
            $pString .= \HTML\td($bar . \HTML\em("&nbsp;&nbsp;" . $row['count'] . "&nbsp;($percentage%)"));
            $pString .= \HTML\trEnd();
        }
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * Get publishers from db
     */
    private function getPublishers()
    {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "browse", "BROWSECOMMON.php"]));
        $common = new BROWSECOMMON();
        $this->miscField1 = [];
        $this->db->leftJoin('resource', 'resourceId', 'resourcemiscId');
        $common->userBibCondition('resourcemiscId');
        $this->db->formatConditions(['resourcemiscPublisher' => ' IS NOT NULL']);
        $fields[] = $this->db->formatFields([['resourcemiscPublisher' => 'pId']]);
        $fields[] = $this->db->formatFields('resourceType');
        $fields[] = $this->db->tidyInput('N') . $this->db->alias . $this->db->formatFields('special');
        $union[] = $this->db->selectNoExecute('resource_misc', implode(', ', $fields), FALSE, FALSE, TRUE);
        $fields = [];
        $fields[] = $this->db->formatFields([['resourcemiscField1' => 'pId']]);
        $fields[] = $this->db->formatFields('resourceType');
        $fields[] = $this->db->tidyInput('Y') . $this->db->alias . $this->db->formatFields('special');
        $this->db->formatConditions(['resourcemiscField1' => ' IS NOT NULL']);
        $this->db->formatConditions($this->db->formatFields('resourcemiscField1') . $this->db->notEqual .
            $this->db->formatFields('resourcemiscPublisher'));
        $this->db->leftJoin('resource', 'resourceId', 'resourcemiscId');
        $common->userBibCondition('resourcemiscId');
        $union[] = $this->db->selectNoExecute('resource_misc', implode(', ', $fields), FALSE, FALSE, TRUE);
        $subQ = $this->db->subQuery($this->db->union($union, TRUE), 't');
        $this->db->leftJoin('publisher', 'publisherId', 'pId');
        $this->db->orderBy('publisherName');
        $this->db->orderBy('publisherLocation');
        $recordset = $this->db->selectCounts(
            FALSE,
            'publisherId',
            ['resourceType', 'publisherName', 'publisherLocation', 'special'],
            $subQ
        );
        while ($row = $this->db->fetchRow($recordset))
        {
            if (array_key_exists($row['publisherId'], $this->indexes))
            {
                continue;
            }
            $this->collateP($row, FALSE);
        }
    }
    /**
     * Add publishers to array and sum totals
     *
     * @param array $row
     */
    private function collateP($row)
    {
        $this->sum[$row['publisherId']] = $row['count'];
        if (array_key_exists('publisherName', $row) && array_key_exists('publisherLocation', $row)
            && $row['publisherName'] && $row['publisherLocation'])
        {
            $this->indexes[$row['publisherId']] = stripslashes($row['publisherName']) .
            '&nbsp;(' . stripslashes($row['publisherLocation']) . ')';
        }
        elseif (array_key_exists('publisherLocation', $row) && $row['publisherLocation'])
        {
            $this->indexes[$row['publisherId']] = '(' . stripslashes($row['publisherLocation']) . ')';
        }
        else
        {
            $this->indexes[$row['publisherId']] = preg_replace("/{(.*)}/Uu", "$1", stripslashes($row['publisherName']));
        }
        // For proceedings_article and proceedings, publisher is stored in miscField1 while for books, transPublisher stored in miscField1.
        if ((($row['resourceType'] == 'proceedings_article') || ($row['resourceType'] == 'proceedings')
         || ($row['resourceType'] == 'book') || ($row['resourceType'] == 'book_article') || ($row['resourceType'] == 'book_chapter'))
        && ($row['special'] == 'Y'))
        {
            $this->miscField1[$row['publisherId']] = TRUE;
        }
    }
    /**
     * Get collections from db
     */
    private function getCollections()
    {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "browse", "BROWSECOMMON.php"]));
        $common = new BROWSECOMMON();
        $common->userBibCondition('resourcemiscId');
        $this->db->formatConditions(['collectionId' => ' IS NOT NULL']);
        $this->db->leftJoin('collection', 'collectionId', 'resourcemiscCollection');
        $this->db->groupBy('collectionIdId');
        $this->db->orderBy('collectionTitle');
        $recordset = $this->db->selectCounts(
            'resource_misc',
            'collectionId',
            ['resourcemiscCollection', 'collectionType', 'collectionTitle', 'collectionTitleShort']
        );
        while ($row = $this->db->fetchRow($recordset))
        {
            if (array_key_exists($row['resourcemiscCollection'], $this->indexes))
            {
                continue;
            }
            if (!$row['collectionType'])
            {
                continue;
            }
            $this->collateColl($row);
        }
    }
    /**
     * Add collections to array and sum totals
     *
     * @param array $row
     */
    private function collateColl($row)
    {
        $this->sum[$row['resourcemiscCollection']] = $row['count'];
        $short = $row['collectionTitleShort'] ? " [" . $row['collectionTitleShort'] . ']' : FALSE;
        $title = $row['collectionTitle'] . $short;
        $this->indexes[$row['resourcemiscCollection']] = preg_replace("/{(.*)}/Uu", "$1", \HTML\dbToFormTidy($title));
    }
    /**
     * Get creators from db with occurrences in resources
     *
     * @param mixed $main
     */
    private function getCreators($main)
    {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "browse", "BROWSECOMMON.php"]));
        $common = new BROWSECOMMON();
        $common->userBibCondition('resourcecreatorResourceId');
        $this->db->formatConditions(['resourcecreatorCreatorId' => ' IS NOT NULL']);
        if ($main)
        { // get only main creators
            $this->db->formatConditions(['resourcecreatorRole' => '1']);
        }
        $subSql = $this->db->selectNoExecute('resource_creator', ['resourcecreatorResourceId', 'resourcecreatorCreatorId'], TRUE, TRUE, TRUE);
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $this->db->groupBy(['resourcecreatorCreatorId', 'creatorPrefix', 'creatorSurname',
            'creatorSameAs', 'creatorInitials', 'creatorFirstname', ], TRUE, $this->db->count('resourcecreatorCreatorId') .
            $this->db->greater . $this->db->tidyInput(0));
        $this->db->orderBy('creatorSurname');
        $recordset = $this->db->selectCounts(FALSE, 'resourcecreatorCreatorId', ['creatorPrefix', 'creatorSurname',
            'creatorSameAs', 'creatorInitials', 'creatorFirstname', ], $this->db->subQuery($subSql, 'rc', FALSE), FALSE);
        while ($row = $this->db->fetchRow($recordset))
        {
            $this->collateC($row);
        }
        if (!empty($this->sameAs))
        {
            foreach ($this->sameAs as $id => $sameAsId)
            {
                if (!array_key_exists($sameAsId, $this->indexes))
                {
                    $this->db->formatConditions(['creatorId' => $sameAsId]);
                    $row = $this->db->selectFirstRow('creator', ['creatorPrefix', 'creatorSurname']);
                    $surname = preg_replace("/{(.*)}/Uu", "$1", stripslashes($row['creatorSurname']));
                    $prefix = preg_replace("/{(.*)}/Uu", "$1", stripslashes($row['creatorPrefix']));
                    $this->indexes[$sameAsId] = $surname;
                    $this->prefix[$sameAsId] = $prefix;
                    $this->sum[$sameAsId] = 0;
                }
                $this->sum[$sameAsId] += $this->sum[$id];
            }
            foreach ($this->sameAs as $id => $sameAsId)
            {
                $this->sum[$id] = $this->sum[$sameAsId];
            }
        }
    }
    /**
     * Add creators to array and sum totals
     *
     * @param array $row
     */
    private function collateC($row)
    {
        if (!trim($row['creatorSurname']))
        {
            return;
        }
        $surname = preg_replace("/{(.*)}/Uu", "$1", stripslashes($row['creatorSurname']));
        $prefix = preg_replace("/{(.*)}/Uu", "$1", stripslashes($row['creatorPrefix']));
        if (!array_key_exists($prefix . $surname, $this->collectedSurnames))
        {
            $this->collectedSurnames[$prefix . $surname] = 1;
        }
        else
        {
            $this->collectedSurnames[$prefix . $surname]++;
        }
        if ($row['creatorFirstname'] || $row['creatorInitials'])
        {
            $firstname = FALSE;
            if ($row['creatorFirstname'])
            {
                $split = preg_split('/(?<!^)(?!$)/u', preg_replace("/{(.*)}/Uu", "$1", stripslashes($row['creatorFirstname'])));
                $firstname = $split[0] . '.';
            }
            if ($row['creatorInitials'])
            {
                $this->initials[$row['resourcecreatorCreatorId']] = ', ' . $firstname .
                    str_replace(' ', '.', preg_replace("/{(.*)}/Uu", "$1", stripslashes($row['creatorInitials']))) . '.';
            }
            else
            {
                $this->initials[$row['resourcecreatorCreatorId']] = ', ' . $firstname;
            }
        }
        $this->indexes[$row['resourcecreatorCreatorId']] = $surname;
        $this->prefix[$row['resourcecreatorCreatorId']] = $prefix;
        $this->sum[$row['resourcecreatorCreatorId']] = $row['count'];
        if ($row['creatorSameAs'])
        {
            $this->sameAs[$row['resourcecreatorCreatorId']] = $row['creatorSameAs'];
        }
    }
    /**
     * Get resource keywords from db
     */
    private function getKeywords()
    {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "browse", "BROWSECOMMON.php"]));
        $common = new BROWSECOMMON();
        $common->userBibCondition('resourcekeywordResourceId');
        $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
        $this->db->formatConditions($this->db->formatFields('resourcekeywordResourceId') . ' IS NOT NULL');
        $this->db->formatConditions($this->db->formatFields('keywordKeyword') . ' IS NOT NULL');
        $this->db->groupBy('resourcekeywordKeywordId');
        $this->db->orderBy('keywordKeyword');
        $recordset = $this->db->selectCounts('resource_keyword', 'resourcekeywordKeywordId', ['keywordKeyword', 'keywordGlossary']);
        while ($row = $this->db->fetchRow($recordset))
        {
            $this->collateK($row);
        }
    }
    /**
     * Add keywords to indexes array and sum totals
     *
     * @param array $row
     */
    private function collateK($row)
    {
        if (!array_key_exists($row['resourcekeywordKeywordId'], $this->indexes))
        {
            $this->indexes[$row['resourcekeywordKeywordId']] = preg_replace(
                "/{(.*)}/Uu",
                "$1",
                \HTML\nlToHtml($row['keywordKeyword'])
            );
            if ($row['keywordGlossary'])
            {
                $this->glossary[$row['resourcekeywordKeywordId']] = \HTML\dbToHtmlPopupTidy($row['keywordGlossary']);
            }
        }
        if (!array_key_exists($row['resourcekeywordKeywordId'], $this->sum))
        {
            $this->sum[$row['resourcekeywordKeywordId']] = $row['count'];
        }
        else
        {
            $this->sum[$row['resourcekeywordKeywordId']] += $row['count'];
        }
    }
    /**
     * Get years from db
     */
    private function getYears()
    {
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "browse", "BROWSECOMMON.php"]));
        $common = new BROWSECOMMON();
        $common->userBibCondition('resourceyearId');
        $this->db->leftJoin('resource', 'resourceId', 'resourceyearId');
        $this->db->orderBy('resourceyearYear1');
        $recordset = $this->db->selectCounts('resource_year', 'resourceyearYear1');
        while ($row = $this->db->fetchRow($recordset))
        {
            $this->collateY($row);
        }
    }
    /**
     * Add years to array and sum totals
     *
     * @param array $row
     */
    private function collateY($row)
    {
        if (!$row['resourceyearYear1'])
        {
            return;
        }
        $this->indexes[$row['resourceyearYear1']] = \HTML\nlToHtml($row['resourceyearYear1']);
        $this->sum[$row['resourceyearYear1']] = $row['count'];
    }
    /**
     * Create green bar for this table row
     *
     * @param mixed $value
     * @param mixed $maxNo
     * @param mixed $id
     *
     * @return string
     */
    private function greenBar($value, $maxNo, $id)
    {
        // 100% width of bar == arbitrary 500 px
        $percentage = round((($value / $this->totalResources) * 100), 1);
        $width = round((($value / $maxNo) * 500), 0);
        $bar = \HTML\img("core/modules/statistics/green.gif", $width, 15, $this->indexes[$id]);

        return \HTML\td($bar . \HTML\em("&nbsp;&nbsp;" . $value . "&nbsp;($percentage%)"));
    }
    /**
     * Get total resources
     */
    private function getTotalsB()
    {
        $this->totalResources = $this->db->selectCountOnly("resource", "resourceId");
    }
}
