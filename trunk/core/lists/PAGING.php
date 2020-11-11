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
 * PAGING Numeric paging of lists
 *
 * @package wikindx\core\lists
 */
class PAGING
{
    /** int */
    public $total;
    /** int */
    public $paging;
    /** int */
    public $start;
    /** string */
    public $queryString;
    /** string */
    public $whereStmt = FALSE;
    /** array */
    private $vars;
    /** array */
    private $db;
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
        $this->vars = GLOBALS::getVars();
        $this->db = FACTORY_DB::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->browserTabID = GLOBALS::getBrowserTabID();
    }
    /**
     * grab paging from session
     */
    public function getPaging()
    {
        $this->getPagingStart();
        if (!$this->total = GLOBALS::getTempStorage('setup_PagingTotal'))
        {
            $this->total = $this->session->getVar("setup_PagingTotal");
        }
        $this->paging = GLOBALS::getUserVar('Paging');
        $this->maxLinks = GLOBALS::getUserVar('PagingMaxLinks');
        $this->maxLinksHalf = round($this->maxLinks / 2);
        // Has the paging limit been overriden in user preferences for this selection?
        if ($this->paging == -1)
        {
            return;
        }
        $this->createLinks();
    }
    /**
     * get where to start display from
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
        $displayEnd = $this->start + $this->paging;
        if (($this->paging <= 0) || ($displayEnd > $this->total))
        {
            $displayEnd = $this->total;
        }
        $displayStart = $this->start + 1;
        $bib = FALSE;
        if ($bibTitle)
        {
            $bib = ' (' . $this->messages->text("user", "bibliography") . ': ' .
            \HTML\nlToHtml($bibTitle) . ")";
        }
        elseif (WIKINDX_MULTIUSER)
        {
            $bib = ' (' . $this->messages->text("user", "bibliography") . ': ' .
                $this->messages->text("user", "masterBib") . ")";
        }

        return $this->messages->text("hint", "pagingInfo", " $displayStart - $displayEnd&nbsp;") .
            $this->messages->text("hint", "pagingInfoOf", $this->total . $bib);
    }
    /**
     * Links at on display screen to move to more resources.
     *
     * $this->total is total resources found for this operation.
     * $this->start is where we currently are.
     */
    private function createLinks()
    {
        if (($this->paging <= 0) || ($this->total <= $this->paging))
        {
            return;
        }
        $BT = $this->browserTabID ? '&browserTabID=' . $this->browserTabID : FALSE;
        $end = $advanced = 0;
        $index = $maxLinks = 1;
        $advance = $this->start;
        if (mb_strpos($this->queryString, '?') !== FALSE)
        {
            $rootFile = FALSE;
        }
        else
        {
            $rootFile = 'index.php?';
        }
        while ($advance >= (($this->maxLinksHalf * $this->paging) - $this->paging))
        {
            $end += $this->paging;
            $index += $this->paging;
            $advance -= $this->paging;
            $advanced++;
        }
        if ($advanced)
        {
            $links[] = \HTML\a(
                "page",
                $this->messages->text("resources", "pagingStart"),
                $rootFile . htmlentities($this->queryString . "&PagingStart=0") . $BT
            );
            $maxLinks++;
        }
        while ($index <= $this->total)
        {
            if ($maxLinks++ >= $this->maxLinks)
            {
                break;
            }
            $end += $this->paging;
            if ($end > $this->total)
            {
                $end = $this->total;
            }
            $start = $index - 1;
            $link = htmlentities($this->queryString . "&PagingStart=$start") . $BT;
            $name = $index . " - " . $end;
            if ($this->start == $start)
            {
                $links[] = $name;
            }
            else
            {
                $links[] = \HTML\a("page", $name, $rootFile . $link);
            }
            $index += $this->paging;
        }
        if ($end < $this->total)
        {
            if ($this->start && count($links) == 1)
            {
                $links = [\HTML\a(
                    "page",
                    $this->messages->text("resources", "pagingStart"),
                    $rootFile . htmlentities($this->queryString . "&PagingStart=0") . $BT
                )];
            }
            elseif (count($links) > 1)
            {
                $start = $this->total - ($this->total % $this->paging);
                if ($start == $this->total)
                {
                    $start = $this->total - $this->paging;
                }
                $links[] = \HTML\a(
                    "page",
                    $this->messages->text("resources", "pagingEnd"),
                    $rootFile . htmlentities($this->queryString . "&PagingStart=$start") . $BT
                );
            }
        }
        GLOBALS::setTplVar('pagingList', $links);
    }
}
