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
 *	NEWS class.
 *
 *	News items
 */
class NEWS
{
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $success;
    private $session;
    private $gatekeep;
    private $badInput;
    private $newsTimestamp;
    private $languageClass;
    private $formData = [];

    // Constructor
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->languageClass = FACTORY_CONSTANTS::getInstance();
    }
    /**
     * display options
     *
     * @param false|string $message
     */
    public function init($message = FALSE)
    {
        $this->gatekeep->requireSuper = TRUE;
        $this->gatekeep->init();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "news"));
        $news = $this->grabAll();
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
            $message = $this->success->text($this->vars['success']);
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        }
        $pString = $message;
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        // Add
        $td = \FORM\formHeader("news_NEWS_CORE");
        $td .= \FORM\hidden("method", "initAdd");
        $td .= \HTML\p($this->messages->text("misc", "newsAdd"));
        $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Add")));
        $td .= \FORM\formEnd();
        $pString .= \HTML\td($td);
        if (!empty($news))
        {
            // Edit
            $td = \FORM\formHeader('news_NEWS_CORE');
            $td .= \FORM\hidden('method', 'initEdit');
            $td .= \HTML\p($this->messages->text("misc", "newsEdit"));
            $td .= \FORM\selectFBoxValue(FALSE, "editId", $news, 5) . BR . \FORM\formSubmit($this->messages->text("submit", "Edit"));
            $td .= \FORM\formEnd();
            $pString .= \HTML\td($td);
            // Delete
            $td = \FORM\formHeader('news_NEWS_CORE');
            $td .= \FORM\hidden('method', 'deleteConfirm');
            $td .= \HTML\p($this->messages->text("misc", "newsDelete"));
            $td .= \FORM\selectFBoxValueMultiple(FALSE, 'newsDelete', $news, 5) . BR .
                \HTML\span(\HTML\aBrowse(
                    'green',
                    '',
                    $this->messages->text("hint", "hint"),
                    '#',
                    "",
                    $this->messages->text("hint", "multiples")
                ), 'hint');
            $td .= BR . \FORM\formSubmit($this->messages->text("submit", "Delete"));
            $td .= \FORM\formEnd();
            $pString .= \HTML\td($td);
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Add a news item - display options
     *
     * @param false|string $message
     */
    public function initAdd($message = FALSE)
    {
        $this->gatekeep->requireSuper = TRUE;
        $this->gatekeep->init();
        if (array_key_exists('message', $this->vars))
        {
            $message = $this->vars['message'];
        }
        $pString = $message;
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "newsAdd"));
        $pString .= $tinymce->loadMinimalTextarea();
        $pString .= \FORM\formHeader('news_NEWS_CORE');
        $pString .= \FORM\hidden('method', 'add');
        $title = array_key_exists('title', $this->formData) ? $this->formData['title'] : FALSE;
        $pString .= \HTML\span('*', 'required') . \FORM\textInput($this->messages->text("news", "title"), "title", $title, 30, 255);
        $pString .= BR . "&nbsp;" . BR;
        $body = array_key_exists('body', $this->formData) ? $this->formData['body'] : FALSE;
        $pString .= \HTML\span('*', 'required') . \FORM\textAreaInput($this->messages->text("news", "body"), "body", $body, 80, 10);
        $pString .= BR . "&nbsp;" . BR;
        $pString .= \FORM\formSubmit($this->messages->text("submit", "Add"));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Add a news item
     */
    public function add()
    {
        $this->gatekeep->requireSuper = TRUE;
        $this->gatekeep->init();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "newsAdd"));
        if (!array_key_exists('title', $this->vars) || !\UTF8\mb_trim($this->vars['title']) ||
            !array_key_exists('body', $this->vars) || !\UTF8\mb_trim($this->vars['body']))
        {
            $this->formData["title"] = $this->vars['title'];
            $this->formData["body"] = $this->vars['body'];
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'initAdd');
        }
        $title = \UTF8\mb_trim($this->vars['title']);
        $news = \UTF8\mb_trim($this->vars['body']);
        $this->db->insert(
            "news",
            ['newsTitle', 'newsNews', 'newsTimestamp'],
            [$title, $news, $this->db->formatTimestamp()]
        );
        $this->session->setVar("setup_News", TRUE);
        if (WIKINDX_EMAIL_NEWS)
        {
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "email", "EMAIL.php"]));
            $emailClass = new EMAIL();
            if (!$emailClass->news($title, $news))
            {
                $this->badInput->close($this->errors->text("inputError", "mail", GLOBALS::getError()), $this, 'initAdd');
            }
        }

        header("Location: index.php?action=news_NEWS_CORE&method=init&success=newsAdd");
        die;
    }
    /**
     * Ask for confirmation of delete groups
     */
    public function deleteConfirm()
    {
        $this->gatekeep->requireSuper = TRUE;
        $this->gatekeep->init();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "newsDelete"));
        if (!array_key_exists('newsDelete', $this->vars) || empty($this->vars['newsDelete']))
        {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'init');
        }
        $news = $this->grabAll();
        $newsText = "'" . implode("', '", array_keys(array_intersect(array_flip($news), $this->vars['newsDelete']))) . "'";
        $news = html_entity_decode($newsText);
        $pString = \HTML\p($this->messages->text("news", "deleteConfirm", ": $news"));
        $pString .= \FORM\formHeader('news_NEWS_CORE');
        $pString .= \FORM\hidden('method', 'delete');
        foreach ($this->vars['newsDelete'] as $id)
        {
            $pString .= \FORM\hidden("newsDelete_" . $id, $id);
        }
        $pString .= BR . \FORM\formSubmit($this->messages->text("submit", "Confirm"));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Delete group(s)
     */
    public function delete()
    {
        $this->gatekeep->requireSuper = TRUE;
        $this->gatekeep->init();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "newsDelete"));
        foreach ($this->vars as $key => $value)
        {
            if (!preg_match("/newsDelete_(.*)/u", $key, $match))
            {
                continue;
            }
            $input[] = $match[1];
        }
        if (!isset($input))
        {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'init');
        }
        $this->db->formatConditionsOneField($input, 'newsId');
        $this->db->delete('news');
        $news = $this->grabAll();
        if (empty($news))
        {
            $this->session->delVar("setup_News");
        }

        header("Location: index.php?action=news_NEWS_CORE&method=init&success=newsDelete");
        die;
    }
    /**
     * display news item for editing
     *
     * @param false|string $message
     */
    public function initEdit($message = FALSE)
    {
        $this->gatekeep->requireSuper = TRUE;
        $this->gatekeep->init();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "newsEdit"));
        if (array_key_exists('editId', $this->formData))
        {
            $editId = $this->formData['editId'];
        }
        elseif (!array_key_exists("editId", $this->vars) || (!$editId = \UTF8\mb_trim($this->vars["editId"])))
        {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'init');
        }
        if (array_key_exists('message', $this->vars))
        {
            $message = $this->vars['message'];
        }
        $pString = $message;
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $pString .= $tinymce->loadMinimalTextarea();
        $title = $body = '';
        if (array_key_exists('title', $this->formData))
        {
            $title = $this->formData['title'];
        }
        if (array_key_exists('body', $this->formData))
        {
            $body = $this->formData['body'];
        }
        if (!$title && !$body)
        {
            $this->db->formatConditions(['newsId' => $editId]);
            $recordset = $this->db->select('news', ['newsTitle', 'newsNews']);
            $row = $this->db->fetchRow($recordset);
            $title = \HTML\dbToFormTidy($row['newsTitle']);
            $body = \HTML\dbToFormTidy($row['newsNews']);
        }
        $pString .= \FORM\formHeader('news_NEWS_CORE');
        $pString .= \FORM\hidden('method', 'edit');
        $pString .= \FORM\hidden('editId', $editId);
        $pString .= \HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("news", "title"),
            "title",
            $title,
            30,
            255
        );
        $pString .= BR . "&nbsp;" . BR;
        $pString .= \HTML\span('*', 'required') . \FORM\textAreaInput(
            $this->messages->text("news", "body"),
            "body",
            $body,
            80,
            10
        );
        $pString .= BR . "&nbsp;" . BR;
        $pString .= \FORM\formSubmit($this->messages->text("submit", "Edit"));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Edit news item
     */
    public function edit()
    {
        $this->gatekeep->requireSuper = TRUE;
        $this->gatekeep->init();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "newsEdit"));
        if (!array_key_exists("editId", $this->vars) || (!$editId = \UTF8\mb_trim($this->vars["editId"])))
        {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'init');
        }
        if (!array_key_exists('title', $this->vars) || !\UTF8\mb_trim($this->vars['title']) ||
            !array_key_exists('body', $this->vars) || !\UTF8\mb_trim($this->vars['body']))
        {
            $this->formData["editId"] = $this->vars['editId'];
            $this->formData["title"] = $this->vars['title'];
            $this->formData["body"] = $this->vars['body'];
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, 'initEdit');
        }
        $updateArray['newsTitle'] = \UTF8\mb_trim($this->vars['title']);
        $updateArray['newsNews'] = \UTF8\mb_trim($this->vars['body']);
        $updateArray['newsTimestamp'] = $this->db->formatTimestamp();
        $this->db->formatConditions(['newsId' => $editId]);
        $this->db->update('news', $updateArray);
        if (WIKINDX_EMAIL_NEWS)
        {
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "email", "EMAIL.php"]));
            $emailClass = new EMAIL();
            if (!$emailClass->news($updateArray['newsTitle'], $updateArray['newsNews']))
            {
                $this->badInput->close($this->errors->text("inputError", "mail", GLOBALS::getError()), $this, 'initEdit');
            }
        }

        header("Location: index.php?action=news_NEWS_CORE&method=init&success=newsEdit");
        die;
    }
    /**
     *View all available news items
     */
    public function viewNews()
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "news"));
        $news = $this->grabAll();
        $pString = '';
        if (is_array($news))
        {
            foreach ($news as $id => $title)
            {
                $pString .= \HTML\p(\HTML\a(
                    "link",
                    \HTML\nlToHtml($title),
                    "index.php?action=news_NEWS_CORE&method=viewNewsItem&id=" . $id
                ) .
                    '&nbsp;&nbsp;' . \HTML\em($this->newsTimestamp[$id]));
            }
        }
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * View one news item
     */
    public function viewNewsItem()
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "news"));
        if (array_key_exists("id", $this->vars))
        {
            $id = \UTF8\mb_trim($this->vars["id"]);
        }
        else
        {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this, "viewNews");
        }
        $this->db->formatConditions(['newsId' => $id]);
        $recordset = $this->db->select('news', ['newsTimestamp', 'newsTitle', 'newsNews']);
        $row = $this->db->fetchRow($recordset);
        if (method_exists($this->languageClass, "dateFormat"))
        {
            $date = \LOCALES\dateFormat($row['newsTimestamp']);
        }
        else
        {
            $dateSplit = \UTF8\mb_explode(' ', $row['newsTimestamp']);
            $dateSplit = \UTF8\mb_explode('-', $dateSplit[0]);
            $date = date("d/M/Y", mktime(0, 0, 0, $dateSplit[1], $dateSplit[2], $dateSplit[0]));
        }
        $pString = \HTML\p(\HTML\strong(\HTML\nlToHtml($row['newsTitle'])) . BR . $date);
        $pString .= \HTML\p(\HTML\nlToHtml($row['newsNews']));
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Get news titles and id from news.  Return associative array of id => title.
     *
     * @return array
     */
    private function grabAll()
    {
        $this->db->ascDesc = $this->db->desc;
        $this->db->orderBy("newsTimestamp", TRUE, FALSE);
        $recordset = $this->db->select("news", ["newsId", "newsTitle", "newsTimestamp"]);
        while ($row = $this->db->fetchRow($recordset))
        {
            $news[$row['newsId']] = \HTML\dbToFormTidy($row['newsTitle']);
            if (method_exists($this->languageClass, "dateFormat"))
            {
                $this->newsTimestamp[$row['newsId']] = \LOCALES\dateFormat($row['newsTimestamp']);
            }
            else
            {
                $this->newsTimestamp[$row['newsId']] = $row['newsTimestamp'];
            }
        }
        if (isset($news))
        {
            return $news;
        }

        return [];
    }
}
