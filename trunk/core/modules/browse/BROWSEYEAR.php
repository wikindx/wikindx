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
 * BROWSEYEAR class
 *
 * Browse publication years as a 'tag cloud'
 */
class BROWSEYEAR
{
    private $db;
    private $vars;
    private $common;
    private $messages;
    private $years = [];
    private $sum;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once("core/browse/BROWSECOMMON.php");
        $this->common = new BROWSECOMMON();
        $this->messages = FACTORY_MESSAGES::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "browseYear"));
    }
    /**
     * init
     */
    public function init()
    {
        $this->sum = [];
        $this->getYears();
        if (empty($this->years)) {
            GLOBALS::addTplVar('content', $this->messages->text("misc", "noResources"));

            return;
        }
        natcasesort($this->sum);
        //		natcasesort($this->years);
        $this->years = $this->common->paging($this->years);
        $this->common->linksInfo();
        $pString = \HTML\pBrowse($this->process(), "center");
        $this->common->pagingLinks('action=browse_BROWSEYEAR_CORE');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Process and display results
     */
    private function process()
    {
        $lowestSum = current($this->sum);
        $highestSum = end($this->sum);
        foreach ($this->years as $id => $name) {
            $colour = $this->common->colourText($lowestSum, $highestSum, $this->sum[$id]);
            $size = $this->common->sizeText($lowestSum, $highestSum, $this->sum[$id]);
            $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=yearProcess&id=' . base64_encode($id))) .
                "&nbsp;[" . $this->sum[$id] . "]";
        }

        return implode("&nbsp; ", $links);
    }
    /**
     * Get years from db
     */
    private function getYears()
    {
        $this->common->userBibCondition('resourceyearId');
        $this->db->leftJoin('resource', 'resourceId', 'resourceyearId');
        $this->db->orderBy('resourceyearYear1');
        $recordset = $this->db->selectCounts('resource_year', 'resourceyearYear1');
        while ($row = $this->db->fetchRow($recordset)) {
            $this->collate($row);
        }
    }
    /**
     * Add years to array and sum totals
     *
     * @param array $row
     */
    private function collate($row)
    {
        if (!$row['resourceyearYear1']) {
            return;
        }
        $this->years[$row['resourceyearYear1']] = \HTML\nlToHtml($row['resourceyearYear1']);
        $this->sum[$row['resourceyearYear1']] = $row['count'];
    }
}
