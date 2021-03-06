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
 * PAGING Alphabetic paging of lists -- only used when ordering by creator or title or attachment file name
 *
 * @package wikindx\core\lists
 */
class PAGINGALPHA
{
    /** bool */
    public $basket = FALSE;
    /** array */
    public $pagingArray;
    /** int */
    public $sizePA;
    /** int */
    public $total = 0;
    /** int */
    public $start;
    /** string */
    public $queryString;
    /** string */
    public $listType;   // set in LISTCOMMON::pagingStyle
    /** string */
    public $order;   // set in LISTCOMMON::pagingStyle
    /** boolean */
    public $nullFound = FALSE;
    /** boolean */
    public $metadata = FALSE;
    /** object */
    private $db;
    /** array */
    private $vars;
    /** object */
    private $session;
    /** object */
    private $messages;
    /** string */
    private $browserTabID = FALSE;

    /**
     *	PAGING
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->browserTabID = GLOBALS::getBrowserTabID();
        $this->getPagingStart();
    }
    /**
     * grab paging either from default or from session
     *
     * @param array $conditions
     * @param array $joins
     * @param bool $conditionsOneField
     * @param string $subQ Optional SQL subquery for input to COUNT operations - default is FALSE
     * @param string $table – default is 'resource'
     * @param bool $QS From QUICKSEARCH or not - default is FALSE
     */
    public function getPaging($conditions, $joins, $conditionsOneField, $subQ, $table = 'resource', $QS = FALSE)
    {
		if ($this->basket) {
			$pagingTotalVar = "setup_PagingTotalBasket";
			$allIdsVar = "list_AllIdsBasket";
			$alphaLinks = "list_PagingAlphaLinksBasket";
		} else {
			$pagingTotalVar = "setup_PagingTotal";
			$allIdsVar = "list_AllIds";
			$alphaLinks = "list_PagingAlphaLinks";
		}
        if (!$this->total = GLOBALS::getTempStorage($pagingTotalVar))
        {
            $this->total = $this->session->getVar($pagingTotalVar);
        }
        if (!$this->pagingArray = \TEMPSTORAGE\fetchOne($this->db, $this->browserTabID, $alphaLinks))
        {
            $this->pagingArray = $this->session->getVar($alphaLinks);
        }
        if (!is_bool($this->pagingArray))
        {
            $this->sizeOfPA = count($this->pagingArray);
            $this->createLinks();

            return;
        }
        $viewMax = GLOBALS::getUserVar('Paging');
        if ($viewMax <= 0)
        {
            $viewMax = 20; // a cludge
        }
        if ($QS)
        {
            if (!$ids = GLOBALS::getTempStorage($allIdsVar))
            {
                $ids = $this->session->getVar($allIdsVar);
            }
            $conditions[] = $this->db->formatConditionsOneField($ids, 'resourceId', '=', TRUE, FALSE, FALSE, TRUE);
            $joins = [];
            $joins['resource_misc'] = ['resourcemiscId', 'resourceId'];
            $joins['resource_creator'] = ['resourcecreatorResourceId', 'resourceId'];
            $joins['creator'] = ['creatorId', 'resourcecreatorCreatorId'];
            $subQ = FALSE;
        }
        $stmt = $this->db->countAlpha($this->order, $subQ, $conditions, $joins, $conditionsOneField, $table);
        $resultSet = $this->db->query($stmt);
        $total = 0;
        $letterArray = [];
        $numRows = $this->db->numRows($resultSet);
        $index = 0;
        while ($row = $this->db->fetchRow($resultSet))
        {
            $total += $row['count'];
            if ($total <= $viewMax)
            {
                $letterArray[] = $row['page'];

                continue;
            }
            else
            {
                $letterArray[] = $row['page'];
            }
            $this->pagingArray[] = $letterArray;
            $letterArray = [];
            $total = 0;
            ++$index;
        }
        if (($index < $numRows) && !empty($letterArray)) {
            $this->pagingArray[] = $letterArray;
        }
        $this->sizeOfPA = count($this->pagingArray);
        $this->session->setVar($alphaLinks, $this->pagingArray);
		if ($this->browserTabID) {
			GLOBALS::setTempStorage([$alphLinks => $this->pagingArray]);
		}
        $this->createLinks();
    }
    /**
     * Format display information string
     *
     * @param false|string $bibTitle Default is FALSE
     *
     * @return string
     */
    public function linksInfo($bibTitle = FALSE)
    {
        if (!$this->total)
        {
            return $this->messages->text("resources", "noResult");
        }
        $bib = FALSE;
        if (count($this->pagingArray) == 1)
        {
            $num = $this->total;
        }
        else
        {
            $array = $this->pagingArray[$this->start];
            if (count($array) > 1)
            {
                $chars = array_shift($array) . '~' . array_pop($array);
            }
            else
            {
                $chars = array_shift($array);
            }
            $num = "'" . $chars . "'";
        }
        if ($bibTitle)
        {
            $bib = " (" . $this->messages->text("user", "bibliography") . ": " .
            \HTML\nlToHtml($bibTitle) . ")";
        }
        elseif (WIKINDX_MULTIUSER)
        {
            $bib = " (" . $this->messages->text("user", "bibliography") . ": " .
                $this->messages->text("user", "masterBib") . ")";
        }

        return $this->messages->text("hint", "pagingInfo", " $num&nbsp;") .
            $this->messages->text("hint", "pagingInfoOf", $this->total . $bib);
    }
    /**
     * get paging start
     */
    public function getPagingStart()
    {
        $DefaultStart = 0;
        $start = FALSE;
        
        if (array_key_exists('PagingStart', $this->vars))
        {
            $start = filter_var($this->vars['PagingStart'], FILTER_VALIDATE_INT);
        }
        
        if ($start === FALSE)
        {
            if (!$start = GLOBALS::getTempStorage('mywikindx_PagingStart'))
            {
                $start = $this->session->getVar("mywikindx_PagingStart", FALSE);
            }
        }
        
        if ($start === FALSE)
        {
            $start = $DefaultStart;
        }
        
        $this->start = $start;
        $this->session->setVar("mywikindx_PagingStart", $start);
    }
    /**
     * Links on display screen to move to more resources.
     *
     * $this->total is total resources found for this operation.
     * $this->start is where we currently are.
     */
    private function createLinks()
    {
        if (count($this->pagingArray) <= 1)
        {
            return FALSE;
        }
        $BT = $this->browserTabID ? '&browserTabID=' . $this->browserTabID : FALSE;
        $tempArray = $this->pagingArray;
        if (mb_strpos($this->queryString, '?') !== FALSE)
        {
            $rootFile = FALSE;
        }
        else
        {
            $rootFile = 'index.php?';
        }
        foreach ($this->pagingArray as $index => $array)
        {
            $array = array_shift($tempArray);
            if (count($array) > 1)
            {
                $chars = array_shift($array) . '~' . array_pop($array);
            }
            else
            {
                $chars = array_shift($array);
            }
            if ($this->start == $index)
            {
                $links[] = $chars;
            }
            else
            {
                $link = htmlentities($this->queryString . "&PagingStart=$index") . $BT;
                $links[] = \HTML\a("page", "&nbsp;&nbsp;$chars&nbsp;&nbsp;", $rootFile . $link);
            }
        }
        if ($this->session->getVar($this->listType . '_AscDesc') == 'DESC')
        {
            $links = array_reverse($links);
        }
        GLOBALS::setTplVar('pagingList', $links);
    }
}
