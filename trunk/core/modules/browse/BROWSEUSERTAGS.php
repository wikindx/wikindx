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
 * BROWSEUSERTAGS class
 *
 * Browse user tags as a 'tag cloud'
 */
class BROWSEUSERTAGS
{
    public $category;
    public $sum;
    public $bib;
    private $db;
    private $vars;
    private $common;
    private $messages;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "browse", "BROWSECOMMON.php"]));
        $this->common = new BROWSECOMMON();

        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "browseUserTags"));
    }
    /**
     * display user tags
     */
    public function init()
    {
        $this->sum = $this->userTags = [];
        $this->getUserTags();
        if (empty($this->userTags))
        {
            GLOBALS::addTplVar('content', $this->messages->text("misc", "noUsertags"));

            return;
        }
        natcasesort($this->sum);
        $this->userTags = $this->common->paging($this->userTags);
        $this->common->linksInfo();
        $pString = \HTML\pBrowse($this->process(), "center");
        $this->common->pagingLinks('action=browse_BROWSEUSERTAGS_CORE');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Get user tags from db
     */
    public function getUserTags()
    {
        $this->common->userBibCondition('resourceusertagsResourceId');
        $this->db->formatConditions(['usertagsUserId' => $this->session->getVar("setup_UserId")]);
        $this->db->leftJoin('user_tags', 'usertagsId', 'resourceusertagsTagId');
        $this->db->groupBy('usertagsId');
        $this->db->orderByCollate('usertagsTag');
        $recordset = $this->db->selectCounts('resource_user_tags', 'usertagsId', 'usertagsTag');
        while ($row = $this->db->fetchRow($recordset))
        {
            $this->collate($row);
        }
    }
    /**
     * Add user tags to array and sum totals
     *
     * @param mixed $row
     */
    private function collate($row)
    {
        $this->userTags[$row['usertagsId']] = preg_replace(
            "/{(.*)}/Uu",
            "$1",
            \HTML\nlToHtml($row['usertagsTag'])
        );
        $this->sum[$row['usertagsId']] = $row['count'];
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
        foreach ($this->userTags as $id => $name)
        {
            $colour = $this->common->colourText($lowestSum, $highestSum, $this->sum[$id]);
            $size = $this->common->sizeText($lowestSum, $highestSum, $this->sum[$id]);
            $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=usertagProcess&id=' . $id)) .
                "&nbsp;[" . $this->sum[$id] . "]";
        }

        return implode("&nbsp; ", $links);
    }
}
