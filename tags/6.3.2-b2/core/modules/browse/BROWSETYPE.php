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
 * BROWSETYPE class
 *
 * Browse resourcetypes as a 'tag cloud'
 */
class BROWSETYPE
{
    private $db;
    private $vars;
    private $common;
    private $messages;
    private $types = [];
    private $sum;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once("core/browse/BROWSECOMMON.php");
        $this->common = new BROWSECOMMON();

        $this->messages = FACTORY_MESSAGES::getInstance();

        GLOBALS::setTplVar('heading', $this->messages->text("heading", "browseType"));
    }
    // display types of collections
    public function init()
    {
        $this->sum = [];
        $this->getTypes();
        if (empty($this->types)) {
            GLOBALS::addTplVar('content', $this->messages->text("misc", "noResources"));

            return;
        }
        natcasesort($this->sum);
        //		natcasesort($this->types);
        $this->types = $this->common->paging($this->types);
        $this->common->linksInfo();
        $pString = \HTML\pBrowse($this->process(), "center");
        $this->common->pagingLinks('action=browse_BROWSETYPE_CORE');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Process and display results
     *
     * @return string
     */
    private function process()
    {
        $lowestSum = current($this->sum);
        $highestSum = end($this->sum);
        foreach ($this->types as $id => $name) {
            $colour = $this->common->colourText($lowestSum, $highestSum, $this->sum[$id]);
            $size = $this->common->sizeText($lowestSum, $highestSum, $this->sum[$id]);
            $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=typeProcess&id=' . $id)) .
                "&nbsp;[" . $this->sum[$id] . "]";
        }

        return implode("&nbsp; ", $links);
    }
    /**
     * Get collections from db
     */
    private function getTypes()
    {
        $this->common->userBibCondition('resourceId');
        $this->db->orderByCollate('resourceType', FALSE);
        $recordset = $this->db->selectCounts('resource', 'resourceType');
        while ($row = $this->db->fetchRow($recordset)) {
            $this->collate($row);
        }
    }
    /**
     * Add collections to array and sum totals
     *
     * @param mixed $row
     */
    private function collate($row)
    {
        $this->types[$row['resourceType']] = preg_replace(
            "/{(.*)}/Uu",
            "$1",
            \HTML\nlToHtml($this->messages->text('resourceType', $row['resourceType']))
        );
        $this->sum[$row['resourceType']] = $row['count'];
    }
}
