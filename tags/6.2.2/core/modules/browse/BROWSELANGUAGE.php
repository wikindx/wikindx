<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * BROWSELANGUAGE class
 *
 * Browse resource languages as a 'tag cloud'
 */
class BROWSELANGUAGE
{
    private $db;
    private $vars;
    private $common;
    private $messages;
    private $languages = [];
    private $sum;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once("core/browse/BROWSECOMMON.php");
        $this->common = new BROWSECOMMON();
        $this->messages = FACTORY_MESSAGES::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "browseLanguage"));
    }
    /**
     * display languages
     */
    public function init()
    {
        $this->sum = [];
        $this->getLanguages();
        if (empty($this->languages))
        {
            GLOBALS::addTplVar('content', $this->messages->text("misc", "noLanguages"));

            return;
        }
        natcasesort($this->sum);
        //		natcasesort($this->types);
        $this->languages = $this->common->paging($this->languages);
        $this->common->linksInfo();
        $pString = \HTML\pBrowse($this->process(), "center");
        $this->common->pagingLinks('action=browse_BROWSELANGUAGE_CORE');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Grab any languages
     */
    private function getLanguages()
    {
        $this->common->userBibCondition('resourcelanguageResourceId');
        $subQ = $this->db->subQuery($this->db->selectCountsNoExecute(
            'resource_language',
            'resourcelanguageLanguageId',
            FALSE,
            FALSE,
            TRUE,
            TRUE
        ), 't');
        $this->db->leftJoin('language', 'languageId', 'resourcelanguageLanguageId');
        $this->db->orderBy('languageLanguage');
        $recordset = $this->db->selectFromSubQuery(FALSE, ['languageId', 'languageLanguage', 'count'], $subQ);
        while ($row = $this->db->fetchRow($recordset))
        {
            $this->collate($row);
        }
    }
    /*
     * Add languages to array and sum totals
     */
    private function collate($row)
    {
        $this->languages[$row['languageId']] = preg_replace(
            "/{(.*)}/Uu",
            "$1",
            \HTML\dbToHtmlTidy($row['languageLanguage'])
        );
        $this->sum[$row['languageId']] = $row['count'];
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
        foreach ($this->languages as $id => $name)
        {
            $colour = $this->common->colourText($lowestSum, $highestSum, $this->sum[$id]);
            $size = $this->common->sizeText($lowestSum, $highestSum, $this->sum[$id]);
            $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=languageProcess&id=' . $id)) .
                "&nbsp;[" . $this->sum[$id] . "]";
        }

        return implode("&nbsp; ", $links);
    }
}
