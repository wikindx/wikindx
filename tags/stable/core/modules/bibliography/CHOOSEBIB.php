<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
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
    private $badInput;
    private $common;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->common = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
    }
    /**
     * List user's bibliographies with options to use one of them or the WIKINDX master bibliography when listing, searching etc.
     */
    public function init($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "bibs"));
        $bibsArray = $this->common->getBibsArray();
        if (empty($bibsArray))
        {
            GLOBALS::addTplVar('content', \HTML\p($this->messages->text("misc", "noBibliographies")));

            return;
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
        $selected = $this->session->getVar('mywikindx_Bibliography_use');
        $size = count($bibsArray);
        if ($size > 20)
        {
            $size = 10;
        }
        $pString .= \HTML\tableStart('');
        $pString .= \HTML\trStart();
        if ($selected)
        {
            $pString .= \HTML\td(\FORM\selectedBoxValue(FALSE, "BibId", $bibsArray, $selected, $size, FALSE, $js));
        }
        else
        {
            $pString .= \HTML\td(\FORM\selectFBoxValue(FALSE, "BibId", $bibsArray, $size, FALSE, $js));
        }
        $pString .= \HTML\td(\HTML\div('div', $this->displayBib($selected)), 'left top width80percent');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Select")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * AJAX-based DIV content creator for display of bibliography details
     */
    public function initDetails()
    {
        $div = \HTML\div('div', $this->displayBib());
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $div]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Display bibliography details and owner's details
     *
     * @return string
     */
    public function displayBib($bibId = FALSE)
    {
        if (array_key_exists('ajaxReturn', $this->vars))
        {
            $bibId = $this->vars['ajaxReturn'];
        }
        if (($bibId === FALSE) || ($bibId <= 0))
        {
            $pString = \HTML\p($this->messages->text("user", "masterBib"));
        }
        else
        {
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
                \HTML\dbToHtmlTidy($row['usersUsername']) . BR;
            $text .= \HTML\strong($this->messages->text("user", "fullname") . ":&nbsp;&nbsp;") .
                \HTML\dbToHtmlTidy($row['usersFullname']);
            $pString = \HTML\p($text);
            $text = '';
            if ($row['count'])
            {
                $text .= \HTML\strong($this->messages->text("user", "numResources") . ":&nbsp;&nbsp;") .
                    $row['count'] . BR;
            }
            $text .= \HTML\strong($this->messages->text("user", "bibTitle") . ":&nbsp;&nbsp;") .
                \HTML\dbToHtmlTidy($row['userbibliographyTitle']) . BR;
            $text .= \HTML\strong($this->messages->text("user", "bibDescription") . ":&nbsp;&nbsp;") .
                \HTML\dbToHtmlTidy($row['userbibliographyDescription']);
            if ($row['userbibliographyUserGroupId'])
            { // a group bibliography
                $this->db->formatConditions(['usergroupsId' => $row['userbibliographyUserGroupId']]);
                $userGroup = $this->db->selectFirstField('user_groups', 'usergroupsTitle');
                $text .= BR . \HTML\strong($this->messages->text("user", "group") . ":&nbsp;&nbsp;") .
                    \HTML\dbToHtmlTidy($userGroup);
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
        // bibId of 0 == master bibliography
        // bibId of < 0 == a label
        if (!array_key_exists('BibId', $this->vars) || !$this->vars['BibId'])
        {
            $this->session->delVar('mywikindx_Bibliography_use');
        }
        elseif (array_key_exists('BibId', $this->vars) && ($this->vars['BibId'] < 0))
        {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'init');
        }
        else
        {
            $this->session->setVar('mywikindx_Bibliography_use', $this->vars['BibId']);
        }
        $this->session->delVar('mywikindx_Bibliography_add');
        $this->session->delVar('mywikindx_PagingStart');
        $this->session->delVar('mywikindx_PagingStartAlpha');
        $this->session->delVar('sql_LastMulti');
        $this->init($this->success->text("bibliographySet"));
    }
}
