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
 * BROWSECREATOR class
 *
 * Browse creators as a 'tag cloud'
 */
class BROWSECREATOR
{
    private $db;
    private $vars;
    private $common;
    private $messages;
    private $sum;
    private $surname;
    private $prefix;
    private $collectedSurnames = [];
    private $initials;
    private $sameAs;
    private $alias;
    private $lowestSum = 1;
    private $highestSum = 0;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "browse", "BROWSECOMMON.php"]));
        $this->common = new BROWSECOMMON();
        $this->messages = FACTORY_MESSAGES::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "browseCreator"));
    }
    /**
     * display creators
     */
    public function init()
    {
        $this->sum = $this->surname = $this->prefix = $this->sameAs = $this->alias = [];
        $this->getCreators();
        if (empty($this->surname)) {
            GLOBALS::addTplVar('content', $this->messages->text("misc", "noCreators"));

            return;
        }
        $this->surname = $this->common->paging($this->surname);
        foreach ($this->surname as $id => $surname) {
            if (!empty($this->prefix) && array_key_exists($id, $this->prefix)) {
                $this->surname[$id] = $this->prefix[$id] . ' ' . $surname;
                $findName = $this->prefix[$id] . $surname;
            } else {
                $findName = $surname;
            }
            if (!array_key_exists($findName, $this->collectedSurnames) || ($this->collectedSurnames[$findName] == 1)) {
                unset($this->initials[$id]);
            }
        }
        $this->common->linksInfo();
        $pString = \HTML\pBrowse($this->process(), "center");
        $this->common->pagingLinks('action=browse_BROWSECREATOR_CORE');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Process and display results
     *
     * @return string
     */
    private function process()
    {
        $links = [];
        foreach ($this->surname as $id => $name) {
            if (is_array($this->initials)) {
                if (array_key_exists($id, $this->initials)) {
                    $name .= $this->initials[$id];
                }
            }
            $colour = $this->common->colourText($this->lowestSum, $this->highestSum, $this->sum[$id]);
            $size = $this->common->sizeText($this->lowestSum, $this->highestSum, $this->sum[$id]);
            array_key_exists($id, $this->alias) ? $alias = $this->messages->text('creators', 'alias', implode(', ', $this->alias[$id])) : $alias = FALSE;
            $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=creatorProcess&id=' . $id), FALSE, $alias) .
                "&nbsp;[" . $this->sum[$id] . "]";
        }

        return implode("&nbsp; ", $links);
    }
    /**
     * Get creators from db with occurrences in resources
     */
    private function getCreators()
    {
        $this->common->userBibCondition('resourcecreatorResourceId');
        $this->db->formatConditions(['resourcecreatorCreatorId' => ' IS NOT NULL']);
        $subSql = $this->db->selectNoExecute('resource_creator', ['resourcecreatorResourceId', 'resourcecreatorCreatorId'], TRUE, TRUE, TRUE);
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorId');
        $this->db->groupBy(['resourcecreatorCreatorId', 'creatorPrefix', 'creatorSurname',
            'creatorSameAs', 'creatorInitials', 'creatorFirstname', ], TRUE, $this->db->count('resourcecreatorCreatorId') .
            $this->db->greater . $this->db->tidyInput(0));
        $this->db->orderBy('creatorSurname');
        $recordset = $this->db->selectCounts(FALSE, 'resourcecreatorCreatorId', ['creatorPrefix', 'creatorSurname',
            'creatorSameAs', 'creatorInitials', 'creatorFirstname', ], $this->db->subQuery($subSql, 'rc', FALSE), FALSE);
        while ($row = $this->db->fetchRow($recordset)) {
            $this->collate($row);
        }
        foreach ($this->sameAs as $id => $sameAsId) {
            if (!array_key_exists($sameAsId, $this->surname)) {
                continue;
            }
            if (!array_key_exists($sameAsId, $this->prefix)) {
                $this->prefix[$sameAsId] = '';
            }
            $this->db->formatConditions(['creatorId' => $id]);
            $row = $this->db->selectFirstRow('creator', ['creatorPrefix', 'creatorSurname']);
            $surname = preg_replace("/{(.*)}/Uu", "$1", stripslashes($row['creatorSurname']));
            $prefix = preg_replace("/{(.*)}/Uu", "$1", stripslashes($row['creatorPrefix']));
            if ($prefix !== FALSE) {
                $prefix .= ' ';
            }
            $name = $prefix . $surname;
            if ($this->prefix[$sameAsId] . ' ' . $this->surname[$sameAsId] == $name) {
                continue;
            }
            if (!array_key_exists($sameAsId, $this->alias)) {
                $this->alias[$sameAsId][] = $name;
            } elseif (array_search($name, $this->alias[$sameAsId]) === FALSE) {
                $this->alias[$sameAsId][] = $name;
            }
        }
        $this->sumSort = $this->sum;
        sort($this->sumSort, SORT_NUMERIC);
        $this->lowestSum = array_shift($this->sumSort);
        $this->highestSum = array_pop($this->sumSort);
    }
    /**
     * Add creators to array and sum totals
     *
     * @param array $row
     */
    private function collate($row)
    {
        if (!trim($row['creatorSurname'])) {
            return;
        }
        if ($row['creatorSameAs']) {
            if (!array_key_exists($row['creatorSameAs'], $this->sum)) {
                $this->sum[$row['creatorSameAs']] = 0;
            }
            $this->sum[$row['creatorSameAs']] += $row['count'];
            $this->sameAs[$row['resourcecreatorCreatorId']] = $row['creatorSameAs'];

            return;
        }
        $surname = preg_replace("/{(.*)}/Uu", "$1", stripslashes($row['creatorSurname']));
        $prefix = preg_replace("/{(.*)}/Uu", "$1", stripslashes($row['creatorPrefix']));
        if (!array_key_exists($prefix . $surname, $this->collectedSurnames)) {
            $this->collectedSurnames[$prefix . $surname] = 1;
        } else {
            $this->collectedSurnames[$prefix . $surname]++;
        }
        if ($row['creatorFirstname'] || $row['creatorInitials']) {
            $firstname = FALSE;
            if ($row['creatorFirstname']) {
                $split = preg_split('/(?<!^)(?!$)/u', preg_replace("/{(.*)}/Uu", "$1", stripslashes($row['creatorFirstname'])));
                $firstname = $split[0] . '.';
            }
            if ($row['creatorInitials']) {
                $this->initials[$row['resourcecreatorCreatorId']] = ', ' . $firstname .
                    str_replace(' ', '.', preg_replace("/{(.*)}/Uu", "$1", stripslashes($row['creatorInitials']))) . '.';
            } else {
                $this->initials[$row['resourcecreatorCreatorId']] = ', ' . $firstname;
            }
        }
        $this->surname[$row['resourcecreatorCreatorId']] = $surname;
        $this->prefix[$row['resourcecreatorCreatorId']] = $prefix;
        if (!array_key_exists($row['resourcecreatorCreatorId'], $this->sum)) {
            $this->sum[$row['resourcecreatorCreatorId']] = 0;
        }
        $this->sum[$row['resourcecreatorCreatorId']] += $row['count'];
    }
}
