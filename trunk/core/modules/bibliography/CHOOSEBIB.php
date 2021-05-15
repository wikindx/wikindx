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
 *	CHOOSEBIB class.
 *
 *	User bibliographies
 */
class CHOOSEBIB
{
    private $db;
    private $vars;
    private $session;
    private $errors;
    private $messages;
    private $success;
    private $common;
    private $homeBib = FALSE;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->common = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
    }
    /**
     * List user's bibliographies with options to use one of them or the WIKINDX master bibliography when listing, searching etc.
     *
     * @param mixed $message
     */
    public function init($message = FALSE)
    {
        $this->db->formatConditions(['usersId' => $this->session->getVar('setup_UserId')]);
        if ($this->db->queryFetchFirstField($this->db->selectNoExecute('users', ['usersHomeBib']))) {
            $this->homeBib = TRUE;
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "bibs"));
        $bibsArray = $this->common->getBibsArray();
        if (empty($bibsArray)) {
            GLOBALS::addTplVar('content', \HTML\p($this->messages->text("misc", "noBibliographies")));

            return;
        }
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
            $message = $this->success->text($this->vars['success']);
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        }
        $pString = $message ? $message : '';
        $pString .= \FORM\formHeader("bibliography_CHOOSEBIB_CORE");
        $pString .= \FORM\hidden("method", "useBib");
        $jsonArray = [];
        $jScript = 'index.php?action=bibliography_CHOOSEBIB_CORE&method=initDetails';
        $jsonArray[] = [
            'startFunction' => 'triggerFromMultiSelect',
            'script' => "$jScript",
            'triggerField' => 'BibId',
            'targetDiv' => 'div',
        ];
        $js = \AJAX\jActionForm('onchange', $jsonArray);
        $selected = array_key_exists('BibId', $this->vars) ? $this->vars['BibId'] : GLOBALS::getUserVar('BrowseBibliography');
        $size = count($bibsArray);
        if ($size > 20) {
            $size = 10;
        }
        $pString .= \HTML\tableStart('');
        $pString .= \HTML\trStart();
        if ($selected) {
            $pString .= \HTML\td(\FORM\selectedBoxValue(FALSE, "BibId", $bibsArray, $selected, $size, FALSE, $js));
        } else {
            $pString .= \HTML\td(\FORM\selectFBoxValue(FALSE, "BibId", $bibsArray, $size, FALSE, $js));
        }
        $pString .= \HTML\td(\HTML\div('div', $this->displayBib($selected)), 'left top width80percent');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\checkbox(
            $this->messages->text('user', 'homeBib'),
            "HomeBib",
            $this->homeBib
        ) . BR .
                \HTML\span(\HTML\aBrowse(
                    'green',
                    '',
                    $this->messages->text("hint", "hint"),
                    '#',
                    "",
                    $this->messages->text("hint", "homeBib")
                ), 'hint'));
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Select")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * AJAX-based DIV content creator for display of bibliography details
     */
    public function initDetails()
    {
        $div = $this->displayBib();
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $div]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Display bibliography details and owner's details
     *
     * @param mixed $bibId
     *
     * @return string
     */
    public function displayBib($bibId = FALSE)
    {
        if (array_key_exists('ajaxReturn', $this->vars))
        {
            $bibId = $this->vars['ajaxReturn'];
        }
        if (!$bibId && array_key_exists('BibId', $this->vars))
        {
            $bibId = $this->vars['BibId'];
        }
        if ($bibId === FALSE)
        {
            $pString = \HTML\p($this->messages->text("user", "masterBib"));
        }
        elseif ($bibId <= 0)
        {
            $pString = '&nbsp;';
        }
        else {
            $this->db->leftJoin(
                'user_bibliography_resource',
                $this->db->formatFields('userbibliographyresourceBibliographyId'),
                $this->db->tidyInput($bibId),
                FALSE
            );
            $this->db->leftJoin('users', 'userbibliographyUserId', 'usersId');
            $this->db->formatConditions(['userbibliographyId' => $bibId]);
            $recordset = $this->db->selectCounts(
                'user_bibliography',
                'userbibliographyresourceBibliographyId',
                ['userbibliographyTitle', 'usersFullname', 'usersUsername', 'userbibliographyDescription', 'userbibliographyUserGroupId']
            );
            $row = $this->db->fetchRow($recordset);
            $text = \HTML\strong($this->messages->text("user", "username") . ":&nbsp;&nbsp;") .
                \HTML\nlToHtml($row['usersUsername']) . BR;
            $text .= \HTML\strong($this->messages->text("user", "fullname") . ":&nbsp;&nbsp;") .
                \HTML\nlToHtml($row['usersFullname']);
            $pString = \HTML\p($text);
            $text = '';
            if ($row['count'])
            {
                $text .= \HTML\strong($this->messages->text("user", "numResources") . ":&nbsp;&nbsp;") .
                    $row['count'] . BR;
            }
            $text .= \HTML\strong($this->messages->text("user", "bibTitle") . ":&nbsp;&nbsp;") .
                \HTML\nlToHtml($row['userbibliographyTitle']) . BR;
            $text .= \HTML\strong($this->messages->text("user", "bibDescription") . ":&nbsp;&nbsp;") .
                \HTML\nlToHtml($row['userbibliographyDescription']);
            if ($row['userbibliographyUserGroupId'])
            { // a group bibliography
                $this->db->formatConditions(['usergroupsId' => $row['userbibliographyUserGroupId']]);
                $userGroup = $this->db->selectFirstField('user_groups', 'usergroupsTitle');
                $text .= BR . \HTML\strong($this->messages->text("user", "group") . ":&nbsp;&nbsp;") .
                    \HTML\nlToHtml($userGroup);
            }
            $pString .= \HTML\p($text);
        }

        return $pString;
    }
    /**
     * Set a bibliography for browsing
     */
    public function useBib()
    {
        $updateArray = [];
        if (array_key_exists('HomeBib', $this->vars))
        {
            $updateArray['usersHomeBib'] = 1;
        }
        else
        {
            $updateArray['usersHomeBib'] = 0;
        }
        // bibId of 0 == master bibliography
        // bibId of < 0 == a label
        if (!array_key_exists('BibId', $this->vars) || !$this->vars['BibId'])
        {
            $this->db->formatConditions(['usersId' => $this->session->getVar('setup_UserId')]);
            $updateArray['usersBrowseBibliography'] = 0;
            $this->db->update('users', $updateArray);
        }
        elseif ($this->vars['BibId'] < 0)
        {
            header("Location: index.php?action=bibliography_CHOOSEBIB_CORE&error=inputError_invalid");
            die;
        }
        else
        {
            GLOBALS::setUserVar('BrowseBibliography', $this->vars['BibId']);
            $updateArray['usersBrowseBibliography'] = $this->vars['BibId'];
            $this->db->formatConditions(['usersId' => $this->session->getVar('setup_UserId')]);
            $this->db->update('users', $updateArray);
        }
        $this->session->delVar("mywikindx_Bibliography_add");
        $this->session->delVar("mywikindx_PagingStart");
        $this->session->delVar("mywikindx_PagingStartAlpha");
        $this->session->delVar("sql_LastMulti");
        header("Location: index.php?action=bibliography_CHOOSEBIB_CORE&success=bibliographySet&BibId=" . $this->vars['BibId']);
        die;
    }
}
