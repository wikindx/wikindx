<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * BROWSEUSER class
 *
 * Browse registered users, departments and institutions as 'tag clouds' where each of those classes has users identified as creators in the wikindx
 */
class BROWSEUSER
{
    public $sum;
    private $db;
    private $vars;
    private $common;
    private $messages;
    private $user;
    private $lowestSum = 1;
    private $highestSum = 0;
    private $commonBib;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once("core/browse/BROWSECOMMON.php");
        $this->common = new BROWSECOMMON();

        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
    }
    /**
     * display system users
     */
    public function user()
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "browseUser"));
        $this->sum = $this->user = $this->sameAs = [];
        $this->db->formatConditions($this->db->formatFields('usersIsCreator') . ' IS NOT NULL');
        $this->getUsers('usersFullname');
        if (empty($this->user))
        {
            GLOBALS::addTplVar('content', $this->messages->text("misc", "noUsers"));

            return;
        }
        natcasesort($this->sum);
        $this->user = $this->common->paging($this->user);
        $this->common->linksInfo();
        $pString = \HTML\pBrowse($this->process('id'), "center");
        $this->common->pagingLinks('action=browse_BROWSEUSER_CORE&method=user');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * display system departments
     */
    public function department()
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "browseDept"));
        $this->sum = $this->user = $this->sameAs = [];
        $this->db->formatConditions($this->db->formatFields('usersIsCreator') . ' IS NOT NULL');
        $this->db->formatConditions($this->db->formatFields('usersDepartment') . ' IS NOT NULL');
        $this->getUsers('usersDepartment');
        if (empty($this->user))
        {
            GLOBALS::addTplVar('content', $this->messages->text("misc", "noUsers"));

            return;
        }
        natcasesort($this->sum);
        $this->user = $this->common->paging($this->user);
        $this->common->linksInfo();
        $pString = \HTML\pBrowse($this->process('department'), "center");
        $this->common->pagingLinks('action=browse_BROWSEUSER_CORE&method=department');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * display system institutions
     */
    public function institution()
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "browseInst"));
        $this->sum = $this->user = $this->sameAs = [];
        $this->db->formatConditions($this->db->formatFields('usersIsCreator') . ' IS NOT NULL');
        $this->db->formatConditions($this->db->formatFields('usersInstitution') . ' IS NOT NULL');
        $this->getUsers('usersInstitution');
        if (empty($this->user))
        {
            GLOBALS::addTplVar('content', $this->messages->text("misc", "noUsers"));

            return;
        }
        natcasesort($this->sum);
        $this->user = $this->common->paging($this->user);
        $this->common->linksInfo();
        $pString = \HTML\pBrowse($this->process('institution'), "center");
        $this->common->pagingLinks('action=browse_BROWSEUSER_CORE&method=institution');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Get system users from db
     */
    public function getUsers($field)
    {
        if ($field == 'usersFullname')
        {
            $allFields = [$field, 'usersUsername', 'resourcecreatorResourceId', 'resourcecreatorCreatorId'];
            $otherFields = [$field, 'usersUsername'];
        }
        else
        {
            $allFields = [$field, 'resourcecreatorResourceId', 'resourcecreatorCreatorId'];
            $otherFields = [$field];
        }
        
        $this->db->leftJoin('resource_creator', 'resourcecreatorCreatorId', 'usersIsCreator');
        $this->commonBib->userBibCondition('resourcecreatorResourceId');
        $subSql = $this->db->selectNoExecute('users', $allFields, TRUE, TRUE, TRUE);
        $subSql = $this->db->subQuery($subSql, 'rc', FALSE);
        
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $this->db->groupBy($allFields, TRUE, $this->db->formatFields('count') . $this->db->greater . $this->db->tidyInput(0));
        $this->db->orderBy($field, TRUE, FALSE);
        $recordset = $this->db->selectCountsNoExecute(FALSE, 'resourcecreatorCreatorId', $otherFields, $subSql, FALSE, TRUE);
        
        while ($row = $this->db->fetchRow($recordset))
        {
            $this->collate($row, $field);
        }
        $this->sumSort = $this->sum;
        sort($this->sumSort, SORT_NUMERIC);
        $this->lowestSum = array_shift($this->sumSort);
        if (empty($this->sumSort))
        { // May be only one entry
            $this->highestSum = $this->lowestSum;
        }
        else
        {
            $this->highestSum = array_pop($this->sumSort);
        }
    }
    /**
     * Add users to array and sum totals
     */
    public function collate($row, $field)
    {
        if ($field == 'usersDepartment')
        {
            $this->user[$row['usersDepartment']] = preg_replace(
                "/{(.*)}/Uu",
                "$1",
                \HTML\dbToFormTidy($row[$field])
            );
            if (!array_key_exists($row['usersDepartment'], $this->sum))
            {
                $this->sum[$row['usersDepartment']] = $row['count'];
            }
            else
            {
                $this->sum[$row['usersDepartment']] += $row['count'];
            }
            $sumIndex = $row['usersDepartment'];
        }
        elseif ($field == 'usersInstitution')
        {
            $this->user[$row['usersInstitution']] = preg_replace(
                "/{(.*)}/Uu",
                "$1",
                \HTML\dbToFormTidy($row[$field])
            );
            if (!array_key_exists($row['usersInstitution'], $this->sum))
            {
                $this->sum[$row['usersInstitution']] = $row['count'];
            }
            else
            {
                $this->sum[$row['usersInstitution']] += $row['count'];
            }
            $sumIndex = $row['usersInstitution'];
        }
        else
        { // creators
            $name = $row[$field] ? \HTML\dbToFormTidy($row[$field]) : \HTML\dbToFormTidy($row['usersUsername']);
            $this->user[$row['resourcecreatorCreatorId']] = preg_replace("/{(.*)}/Uu", "$1", $name);
            $this->sum[$row['resourcecreatorCreatorId']] = $row['count'];
            $sumIndex = $row['resourcecreatorCreatorId'];
        }
        $this->db->formatConditions($this->db->formatFields('creatorSameAs') . $this->db->equal . $this->db->tidyInput($row['resourcecreatorCreatorId']));
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $this->db->groupBy(['resourcecreatorCreatorId', 'resourcecreatorResourceId', 'creatorId'], TRUE, $this->db->formatFields('count') . $this->db->greater . $this->db->tidyInput(0));
        $recordset = $this->db->selectCounts('resource_creator', 'resourcecreatorResourceId', ['creatorId'], FALSE, FALSE, TRUE, TRUE);
        while ($row2 = $this->db->fetchRow($recordset))
        {
            if ($row2['count'])
            {
                $this->sum[$sumIndex] += $row2['count'];
            }
        }
    }
    /**
     * Process and display users results
     *
     * @return string
     */
    private function process($type)
    {
        foreach ($this->user as $id => $name)
        {
            $colour = $this->common->colourText($this->lowestSum, $this->highestSum, $this->sum[$id]);
            $size = $this->common->sizeText($this->lowestSum, $this->highestSum, $this->sum[$id]);
            if ($type == 'institution')
            {
                $encId = base64_encode($id);
                $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities("action=list_LISTSOMERESOURCES_CORE&method=creatorProcess&$type=" . $encId)) . "&nbsp;[" . $this->sum[$id] . "]";
            }
            elseif ($type == 'department')
            {
                $encId = base64_encode($id);
                $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities("action=list_LISTSOMERESOURCES_CORE&method=creatorProcess&$type=" . $encId)) . "&nbsp;[" . $this->sum[$id] . "]";
            }
            else
            {
                $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities("action=list_LISTSOMERESOURCES_CORE&method=creatorProcess&$type=" . $id)) . "&nbsp;[" . $this->sum[$id] . "]";
            }
        }

        return implode("&nbsp; ", $links);
    }
}
