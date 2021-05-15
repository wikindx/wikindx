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
 * BROWSECITED class
 *
 * Browse creators cited in the wikindx as a 'tag cloud'
 */
class BROWSECITED
{
    private $db;
    private $vars;
    private $common;
    private $messages;
    private $sum;
    private $surname;
    private $citedResourceIds;
    private $prefix;
    private $citeIds;
    private $citeList;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "browse", "BROWSECOMMON.php"]));
        $this->common = new BROWSECOMMON();

        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "browseCited"));
    }
    /**
     * display citations
     */
    public function init()
    {
        $this->sum = $this->surname = $this->prefix = $this->citedResourceIds = $this->citeIds = $this->citeList = [];
        $this->getCitations();
        if (empty($this->citedResourceIds) || empty($this->sum))
        {
            GLOBALS::addTplVar('content', $this->messages->text("misc", "noCitations"));

            return;
        }
        $this->getCreators();
        $this->surname = $this->common->paging($this->surname);
        foreach ($this->surname as $id => $surname)
        {
            if (!empty($this->prefix) && array_key_exists($id, $this->prefix))
            {
                $this->surname[$id] = $this->prefix[$id] . ' ' . $surname;
            }
        }
        $this->common->linksInfo();
        $pString = $this->process();
        if (!$pString)
        {
            GLOBALS::addTplVar('content', $this->messages->text("misc", "noCitations")); // only resources without creators could be cited.
            
            return;
        }
        $pString = \HTML\pBrowse($pString, "center");
        $this->common->pagingLinks('action=browse_BROWSECITED_CORE');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Get resource ids containing citations in their metadata
     */
    private function getCitations()
    {
        $search = '[cite]';
        // Abstract
        $this->common->userBibCondition('resourcetextId');
        $matchAgainst = $this->db->fulltextSearch(['resourcetextAbstract'], $search);
        $this->db->formatConditions($matchAgainst);
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute(
            'resource_text',
            [['resourcetextId' => 'rId'], ['resourcetextAbstract' => 'text']]
        ));
        //Note
        $this->common->userBibCondition('resourcetextId');
        $matchAgainst = $this->db->fulltextSearch(['resourcetextNote'], $search);
        $this->db->formatConditions($matchAgainst);
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute(
            'resource_text',
            [['resourcetextId' => 'rId'], ['resourcetextNote' => 'text']]
        ));
        // metadata
        $this->common->userBibCondition('resourcemetadataResourceId');
        $matchAgainst = $this->db->fulltextSearch(['resourcemetadataText'], $search);
        $this->db->formatConditions($matchAgainst);
        if ($this->session->getVar("setup_Write"))
        {
            $this->db->formatConditionsOneField(['q', 'p', 'qc', 'pc', 'm', 'i'], 'resourcemetadataType');
        }
        else
        {
            $this->db->formatConditionsOneField(['q', 'p'], 'resourcemetadataType');
        }
        $unions[] = $this->db->queryNoExecute($this->db->selectNoExecute(
            'resource_metadata',
            [['resourcemetadataResourceId' => 'rId'], ['resourcemetadataText' => 'text']]
        ));
        $resultset = $this->db->query($this->db->selectNoExecuteFromSubQuery(
            FALSE,
            ['rId', 'text'],
            $this->db->subQuery($this->db->union($unions), 'u')
        ));
        while ($row = $this->db->fetchRow($resultset))
        {
            $ids = [];
            preg_match_all('/\[cite\](\d+)/u', $row['text'], $match);
            foreach ($match[1] as $id)
            {
                if (!array_key_exists($id, $this->citeIds))
                {
                    $this->citeIds[$id][] = $row['rId'];
                }
                elseif (array_search($row['rId'], $this->citeIds[$id]) === FALSE)
                {
                    $this->citeIds[$id][] = $row['rId'];
                }
                if (array_search($id, $ids) !== FALSE)
                { // already cited in this resource
                    continue;
                }
                if (!array_key_exists($id, $this->citedResourceIds))
                {
                    $this->citedResourceIds[$id] = 1;
                }
                else
                {
                    $this->citedResourceIds[$id]++;
                }
                $ids[] = $id;
            }
        }
    }
    /**
     * Process and display results
     *
     * @return string
     */
    private function process()
    {
        if (empty($this->sum))
        {
            return FALSE;
        }
        $sum = $this->sum;
        sort($sum);
        $highestSum = $sum[count($sum) - 1];
        $lowestSum = $sum[0];
        foreach ($this->surname as $id => $name)
        {
            $colour = $this->common->colourText($lowestSum, $highestSum, $this->sum[$id]);
            $size = $this->common->sizeText($lowestSum, $highestSum, $this->sum[$id]);
            $citeIds = implode(',', array_unique($this->citeList[$id]));
            $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=citeProcessCreator&id=' . $citeIds)) .
                "&nbsp;[" . $this->sum[$id] . "]";
        }

        return implode("&nbsp; ", $links);
    }
    /**
     * Get creators from db -- only main authors
     */
    private function getCreators()
    {
        $condition = $this->db->formatFields('resourcecreatorResourceId') . $this->db->equal .
            implode($this->db->or . $this->db->formatFields('resourcecreatorResourceId') .
            $this->db->equal, array_keys($this->citedResourceIds));
        $this->db->formatConditions($condition);
        $this->db->formatConditions($this->db->formatFields('creatorId') . 'IS NOT NULL');
        $this->db->formatConditions(['resourcecreatorRole' => 1]);
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $this->db->orderBy('creatorSurname');
        $recordset = $this->db->select(
            'resource_creator',
            ['resourcecreatorResourceId', 'creatorId', 'creatorPrefix', 'creatorSurname'],
            TRUE
        );
        while ($row = $this->db->fetchRow($recordset))
        {
            $this->collate($row);
        }
    }
    /**
     * Add creators to array and sum totals
     *
     * @param mixed $row
     */
    private function collate($row)
    {
        $this->surname[$row['creatorId']] = preg_replace("/{(.*)}/Uu", "$1", stripslashes($row['creatorSurname']));
        $this->prefix[$row['creatorId']] = preg_replace("/{(.*)}/Uu", "$1", stripslashes($row['creatorPrefix']));
        if (array_key_exists($row['creatorId'], $this->citeList))
        {
            $this->citeList[$row['creatorId']] =
            array_merge($this->citeList[$row['creatorId']], $this->citeIds[$row['resourcecreatorResourceId']]);
        }
        else
        {
            $this->citeList[$row['creatorId']] = $this->citeIds[$row['resourcecreatorResourceId']];
        }
        $this->citeList[$row['creatorId']] = array_unique($this->citeList[$row['creatorId']]);
        $this->sum[$row['creatorId']] = count($this->citeList[$row['creatorId']]);
    }
}
