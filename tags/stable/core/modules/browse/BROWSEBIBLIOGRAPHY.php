<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * BROWSEBIBLIOGRAPHY class
 *
 * Browse categories as a 'tag cloud'
 */
class BROWSEBIBLIOGRAPHY
{
    private $db;
    private $vars;
    private $common;
    private $messages;
    private $sum;
    private $bibs;
    private $commonBib;
    private $gatekeep;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once("core/browse/BROWSECOMMON.php");
        $this->common = new BROWSECOMMON();

        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "browseBibliography"));
    }
    /**
     * display bibliographies
     */
    public function init()
    {
        $this->gatekeep->init(); // No Read-only access allowed
        $this->sum = $this->bibs = [];
        $this->getBibliographies();
        if (empty($this->bibs))
        {
            GLOBALS::addTplVar('content', $this->messages->text("misc", "noBibliographies"));

            return;
        }
        natcasesort($this->sum);
        $this->bibs = $this->common->paging($this->bibs);
        $this->common->linksInfo();
        $pString = \HTML\pBrowse($this->process(), "center");
        $this->common->pagingLinks('action=browse_BROWSEBIBLIOGRAPHY_CORE');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Get bibliogaphries from db
     */
    public function getBibliographies()
    {
        $array = $this->commonBib->getUserBibs();
        foreach ($array as $id => $title)
        {
            $this->collate($id, $title);
        }
        $array = $this->commonBib->getGroupBibs();
        foreach ($array as $id => $title)
        {
            $this->collate($id, $title);
        }
    }
    /**
     * Add bibliographies to array and sum totals
     */
    private function collate($id, $title)
    {
        $this->db->formatConditions(['userbibliographyresourceBibliographyId' => $id]);
        $recordset = $this->db->selectCountDistinctField('user_bibliography_resource', 'userbibliographyresourceId');
        $count = $this->db->fetchOne($recordset);
        if ($count)
        {
            $this->bibs[$id] = preg_replace("/{(.*)}/Uu", "$1", \HTML\dbToHtmlTidy($title));
            $this->sum[$id] = $count;
        }
    }
    /**
     * Process and display results
     *
     * @retrun string
     */
    private function process()
    {
        $lowestSum = current($this->sum);
        $highestSum = end($this->sum);
        foreach ($this->bibs as $id => $name)
        {
            $colour = $this->common->colourText($lowestSum, $highestSum, $this->sum[$id]);
            $size = $this->common->sizeText($lowestSum, $highestSum, $this->sum[$id]);
            $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=bibliographyProcess&id=' . $id)) .
                "&nbsp;[" . $this->sum[$id] . "]";
        }

        return implode("&nbsp; ", $links);
    }
}
