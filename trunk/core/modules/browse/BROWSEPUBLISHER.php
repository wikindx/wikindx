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
 * BROWSEPUBLISHER class
 *
 * Browse publishers as a 'tag cloud'
 */
class BROWSEPUBLISHER
{
    private $db;
    private $vars;
    private $common;
    private $messages;
    private $publisher = [];
    private $sum;
    private $miscField1;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "browse", "BROWSECOMMON.php"]));
        $this->common = new BROWSECOMMON();

        $this->messages = FACTORY_MESSAGES::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "browsePublisher"));
    }
    /**
     * display publishers
     */
    public function init()
    {
        $publisherType = $this->vars['PublisherType'];
        $queryString = 'action=browse_BROWSEPUBLISHER_CORE&method=init&PublisherType=' . $publisherType;
        $this->sum = $this->publisher = [];
        $this->getPublishers($publisherType);
        if (empty($this->publisher)) {
            GLOBALS::addTplVar('content', $this->messages->text("misc", "noPublisher"));

            return;
        }
        natcasesort($this->sum);
        //		natcasesort($this->publisher);
        $this->publisher = $this->common->paging($this->publisher);
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
    public function process()
    {
        $lowestSum = current($this->sum);
        $highestSum = end($this->sum);
        foreach ($this->publisher as $id => $name) {
            $colour = $this->common->colourText($lowestSum, $highestSum, $this->sum[$id]);
            $size = $this->common->sizeText($lowestSum, $highestSum, $this->sum[$id]);
            if (array_key_exists($id, $this->miscField1)) {
                $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=specialPublisherProcess&id=' . $id)) .
                "&nbsp;[" . $this->sum[$id] . "]";
            } else {
                $links[] = \HTML\aBrowse($colour, $size, $name, 'index.php?' .
                htmlentities('action=list_LISTSOMERESOURCES_CORE&method=publisherProcess&id=' . $id)) .
                "&nbsp;[" . $this->sum[$id] . "]";
            }
        }

        return implode("&nbsp; ", $links);
    }
    /**
     * Get publishers from db
     *
     * @param mixed $type
     */
    public function getPublishers($type)
    {
        $this->miscField1 = [];
        $this->db->leftJoin('resource', 'resourceId', 'resourcemiscId');
        $this->common->userBibCondition('resourcemiscId');
        $this->db->formatConditions(['resourcemiscPublisher' => ' IS NOT NULL']);
        $fields[] = $this->db->formatFields([['resourcemiscPublisher' => 'pId']]);
        $fields[] = $this->db->formatFields('resourceType');
        $fields[] = $this->db->tidyInput('N') . $this->db->alias . $this->db->formatFields('special');
        $union[] = $this->db->selectNoExecute('resource_misc', implode(', ', $fields), FALSE, FALSE, TRUE);
        $fields = [];
        $fields[] = $this->db->formatFields([['resourcemiscField1' => 'pId']]);
        $fields[] = $this->db->formatFields('resourceType');
        $fields[] = $this->db->tidyInput('Y') . $this->db->alias . $this->db->formatFields('special');
        $this->db->formatConditions(['resourcemiscField1' => ' IS NOT NULL']);
        $this->db->formatConditions($this->db->formatFields('resourcemiscField1') . $this->db->notEqual .
            $this->db->formatFields('resourcemiscPublisher'));
        $this->db->leftJoin('resource', 'resourceId', 'resourcemiscId');
        $this->common->userBibCondition('resourcemiscId');
        $union[] = $this->db->selectNoExecute('resource_misc', implode(', ', $fields), FALSE, FALSE, TRUE);
        $subQ = $this->db->subQuery($this->db->union($union, TRUE), 't');
        if ($type) {
            $this->db->formatConditions(['publisherType' => $type]);
        }
        $this->db->leftJoin('publisher', 'publisherId', 'pId');
        $this->db->orderBy('publisherName');
        $this->db->orderBy('publisherLocation');
        $recordset = $this->db->selectCounts(
            FALSE,
            'publisherId',
            ['resourceType', 'publisherName', 'publisherLocation', 'special'],
            $subQ
        );
        while ($row = $this->db->fetchRow($recordset)) {
            if (array_key_exists($row['publisherId'], $this->publisher)) {
                continue;
            }
            $this->collate($row, FALSE);
        }
    }
    /**
     * Add publishers to array and sum totals
     *
     * @param mixed $row
     */
    public function collate($row)
    {
        $this->sum[$row['publisherId']] = $row['count'];
        if (array_key_exists('publisherName', $row) && array_key_exists('publisherLocation', $row)
            && $row['publisherName'] && $row['publisherLocation']) {
            $this->publisher[$row['publisherId']] = stripslashes($row['publisherName']) .
            '&nbsp;(' . stripslashes($row['publisherLocation']) . ')';
        } elseif (array_key_exists('publisherLocation', $row) && $row['publisherLocation']) {
            $this->publisher[$row['publisherId']] = '(' . stripslashes($row['publisherLocation']) . ')';
        } else {
            $this->publisher[$row['publisherId']] = preg_replace("/{(.*)}/Uu", "$1", stripslashes($row['publisherName']));
        }
        // For proceedings_article and proceedings, publisher is stored in miscField1 while for books, transPublisher stored in miscField1.
        if ((($row['resourceType'] == 'proceedings_article') || ($row['resourceType'] == 'proceedings')
         || ($row['resourceType'] == 'book') || ($row['resourceType'] == 'book_article') || ($row['resourceType'] == 'book_chapter'))
        && ($row['special'] == 'Y')) {
            $this->miscField1[$row['publisherId']] = TRUE;
        }
    }
}
