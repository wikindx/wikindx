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
 * BROWSECOLLECTION class
 *
 * Browse collections as a 'tag cloud'
 */
class BROWSECOLLECTION
{
    private $db;
    private $vars;
    private $common;
    private $messages;
    private $collections = [];
    private $sum;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "browse", "BROWSECOMMON.php"]));
        $this->common = new BROWSECOMMON();

        $this->messages = FACTORY_MESSAGES::getInstance();

        GLOBALS::setTplVar('heading', $this->messages->text("heading", "browseCollection"));
    }
    /**
     * display types of collections
     */
    public function init()
    {
        $this->db->groupBy('collectionType');
        $recordset = $this->db->select('collection', 'collectionType');
        if (!$this->db->numRows($recordset)) {
            $pString = \HTML\p("&nbsp;") . $this->messages->text("misc", "noCollections");
            GLOBALS::addTplVar('content', $pString);
        }
        // Add 'ALL' to array
        $collections[0] = $this->messages->text("collection", 'all');
        while ($row = $this->db->fetchRow($recordset)) {
            if (!$row['collectionType']) {
                continue;
            }
            $collections[$row['collectionType']] = $this->messages->text("collection", $row['collectionType']);
        }
        $pString = \FORM\formHeader('browse_BROWSECOLLECTION_CORE');
        $pString .= \FORM\hidden('method', 'display');
        $pString .= \FORM\selectFBoxValue(
            $this->messages->text("collection", 'chooseTypeBrowse'),
            "collectionType",
            $collections,
            10
        );
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")), FALSE);
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * display collections
     */
    public function display()
    {
        if (array_key_exists('collectionType', $this->vars)) {
            $collectionType = $this->vars['collectionType'];
        } else {
            $collectionType = "";
        }
        $queryString = 'action=browse_BROWSECOLLECTION_CORE&method=display&collectionType=' . $collectionType;
        $this->sum = $this->collection = [];
        $this->getCollections($collectionType);
        if (empty($this->collections)) {
            $pString = \HTML\p("&nbsp;") . $this->messages->text("misc", "noCollections");
            GLOBALS::addTplVar('content', $pString);
        }
        natcasesort($this->sum);
        //		natcasesort($this->collections);
        $this->collections = $this->common->paging($this->collections);
        $this->common->linksInfo();
        $pString = \HTML\pBrowse($this->process(), "center");
        $this->common->pagingLinks($queryString);
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
        $links = [];
        foreach ($this->collections as $id => $name) {
            $colour = $this->common->colourText($lowestSum, $highestSum, $this->sum[$id]);
            $size = $this->common->sizeText($lowestSum, $highestSum, $this->sum[$id]);
            $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=collectionProcess&id=' . $id)) .
                "&nbsp;[" . $this->sum[$id] . "]";
        }

        return implode("&nbsp; ", $links);
    }
    /**
     * Get collections from db
     *
     * @param mixed $collectionType
     */
    private function getCollections($collectionType)
    {
        $this->common->userBibCondition('resourcemiscId');
        if ($collectionType) {
            $this->db->formatConditions(['collectionType' => $collectionType]);
        }
        $this->db->formatConditions(['collectionId' => ' IS NOT NULL']);
        $this->db->leftJoin('collection', 'collectionId', 'resourcemiscCollection');
        $this->db->groupBy('collectionIdId');
        $this->db->orderBy('collectionTitle');
        $recordset = $this->db->selectCounts(
            'resource_misc',
            'collectionId',
            ['resourcemiscCollection', 'collectionType', 'collectionTitle',
                'collectionTitleShort', ]
        );
        while ($row = $this->db->fetchRow($recordset)) {
            if (array_key_exists($row['resourcemiscCollection'], $this->collections)) {
                continue;
            }
            if (!$row['collectionType']) {
                continue;
            }
            $this->collate($row);
        }
    }
    /**
     *Add collections to array and sum totals
     *
     * @param mixed $row
     */
    private function collate($row)
    {
        $this->sum[$row['resourcemiscCollection']] = $row['count'];
        $short = $row['collectionTitleShort'] ? " [" . $row['collectionTitleShort'] . ']' : FALSE;
        $title = $row['collectionTitle'] . $short;
        $thisType = $this->messages->text("collection", $row['collectionType']);
        if ($thisType) {
            $this->collections[$row['resourcemiscCollection']] =
                preg_replace("/{(.*)}/Uu", "$1", \HTML\nlToHtml($title . " [$thisType]"));
        } else {
            $this->collections[$row['resourcemiscCollection']] = preg_replace("/{(.*)}/Uu", "$1", \HTML\dbToFormTidy($title));
        }
    }
}
