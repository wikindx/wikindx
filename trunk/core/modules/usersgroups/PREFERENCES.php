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
 *	PREFERENCES WIKINDX class
 *
 * Give some configuration options to read only users
 */
class PREFERENCES
{
    private $errors;
    private $messages;
    private $success;
    private $session;
    private $db;
    private $vars;
    private $bib;
    private $badInput;
    private $errorString = FALSE;
    private $formData = [];

    public function __construct()
    {
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->badInput = FACTORY_BADINPUT::getInstance();
    }    /**
     * init
     *
     * @param false|string $message
     */
    public function init($message = FALSE)
    {
        if (array_key_exists('success', $this->vars) && $this->vars['success']) {
            $message = $this->success->text($this->vars['success']);
        } elseif (array_key_exists('error', $this->vars) && $this->vars['error']) {
        	$split = explode('_', $this->vars['error']);
            $message = $this->errors->text($split[0], $split[1]);
        }
        $pString = $message;
        GLOBALS::setTplVar('help', \UTILS\createHelpTopicLink('preferences'));
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "preferences"));
        $pString .= $this->display();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Display config options
     *
     * @return string
     */
    public function display()
    {
        $pString = \FORM\formHeader("usersgroups_PREFERENCES_CORE");
        $pString .= \FORM\hidden("method", "edit");
        $pString .= \HTML\tableStart('generalTable borderStyleSolid left');
        $pString .= \HTML\trStart();
        
        // Display the global template but change the default selection of the list to the default template when no template is defined or a template not enabled is defined,
        // this avoid a crash when this option is written without value selected.
        $templates = FACTORY_TEMPLATE::getInstance()->loadDir();
        if (!empty($this->formData))
        {
            $template = $this->formData['Template'];
        }
        else
        {
            $template = GLOBALS::getUserVar("Template", WIKINDX_TEMPLATE_DEFAULT);
        }
        array_key_exists($template, $templates) ? $template = $template : $template = WIKINDX_TEMPLATE_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "template"),
            "Template",
            $templates,
            $template,
            4
        ));
        
        $menus[0] = $this->messages->text("config", "templateMenu1");
        $menus[1] = $this->messages->text("config", "templateMenu2");
        $menus[2] = $this->messages->text("config", "templateMenu3");
        if (!empty($this->formData))
        {
            $menuLevel = $this->formData['TemplateMenu'];
        }
        else
        {
            $menuLevel = $this->session->getVar("setup_TemplateMenu"); // TODO: Why is this not accessible via GLOBALS?
        }
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "templateMenu"),
            "TemplateMenu",
            $menus,
            $menuLevel,
            3
        ));
        
        // For the graphical interface, add the "auto" value that allows to say that the language is chosen by the browser.
        $LanguageNeutralChoice = "auto";
        $languages[$LanguageNeutralChoice] = "Auto";
        $languages = array_merge($languages, \LOCALES\getSystemLocales());
        if (!empty($this->formData))
        {
            $language = $this->formData['Language'];
        }
        else
        {
            $language = GLOBALS::getUserVar('Language', WIKINDX_LANGUAGE_DEFAULT);
        }
        $language = array_key_exists($language, $languages) ? $language : $LanguageNeutralChoice;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "language"),
            "Language",
            $languages,
            $language
        ));
        
        // Display the user style but change the default selection of the list to the default style when
        // no style is defined or a style not enabled is defined,
        // this avoid a crash when this option is written without value selected.
        $styles = \LOADSTYLE\loadDir();
        if (!empty($this->formData))
        {
            $style = $this->formData['Style'];
        }
        else
        {
            $style = GLOBALS::getUserVar("Style", WIKINDX_STYLE_DEFAULT);
        }
        array_key_exists($style, $styles) ? $style = $style : $style = WIKINDX_STYLE_DEFAULT;
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\selectedBoxValue(
            $this->messages->text("config", "style"),
            "Style",
            $styles,
            $style,
            4
        ));
        $pString .= \HTML\td('&nbsp;');
        $pString .= \HTML\trEnd();
        $pString .= \HTML\trEnd();
        
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\HTML\hr(), FALSE, 5); // span 5 columns
        $pString .= \HTML\trEnd();
        
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingLimit"));
        if (!empty($this->formData) && $this->formData['Paging'])
        {
            $paging = $this->formData['Paging'];
        }
        else
        {
            $paging = GLOBALS::getUserVar("Paging", WIKINDX_PAGING_DEFAULT);
        }
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "paging"),
            "Paging",
            $paging,
            5
        ) . BR . \HTML\span($hint, 'hint'));
        if (!empty($this->formData) && $this->formData['PagingMaxLinks'])
        {
            $pagingML = $this->formData['PagingMaxLinks'];
        }
        else
        {
            $pagingML = GLOBALS::getUserVar("PagingMaxLinks", WIKINDX_PAGING_MAXLINKS_DEFAULT);
        }
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingMaxLinks"));
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "maxPaging"),
            "PagingMaxLinks",
            $pagingML,
            5
        ) . BR . \HTML\span($hint, 'hint'));
        if (!empty($this->formData) && $this->formData['PagingTagCloud'])
        {
            $pagingTC = $this->formData['PagingTagCloud'];
        }
        else
        {
            $pagingTC = GLOBALS::getUserVar("PagingTagCloud", WIKINDX_PAGING_TAG_CLOUD_DEFAULT);
        }
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingLimit"));
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "pagingTagCloud"),
            "PagingTagCloud",
            $pagingTC,
            5
        ) . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingLimit"));
        if (!empty($this->formData) && $this->formData['StringLimit'])
        {
            $stringLimit = $this->formData['StringLimit'];
        }
        else
        {
            $stringLimit = GLOBALS::getUserVar("StringLimit", WIKINDX_STRING_LIMIT_DEFAULT);
        }
        $pString .= \HTML\td(\HTML\span('*', 'required') . \FORM\textInput(
            $this->messages->text("config", "stringLimit"),
            "StringLimit",
            $stringLimit,
            5
        ) . BR . \HTML\span($hint, 'hint'));
        if (!empty($this->formData) && $this->formData['ListLink'])
        {
            $check = 'CHECKED';
        }
        elseif (!empty($this->formData))
        {
            $check = FALSE;
        }
        else
        {
            $check = GLOBALS::getUserVar("ListLink", WIKINDX_LIST_LINK_DEFAULT) ? 'CHECKED' : FALSE;
        }
        $check = GLOBALS::getUserVar('ListLink') ? "CHECKED" : FALSE;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "ListLink"), "ListLink", $check));
        $pString .= \HTML\trEnd();
        
        $pString .= \HTML\tableEnd();
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Edit")));
        $pString .= \FORM\formEnd();

        return $pString;
    }
    
    /**
     * Edit
     */
    public function edit()
    {
        $error = '';
        $required = ["Paging", "PagingMaxLinks", "StringLimit", "PagingTagCloud"];
        foreach ($required as $key)
        {
            if (!is_numeric($this->vars[$key]) || !is_int($this->vars[$key] + 0))
            { // cast to number
                $error = $this->errors->text("inputError", "nan", " ($key) ");
            }
            if (!array_key_exists($key, $this->vars) || !$this->vars[$key])
            {
                $error = $this->errors->text("inputError", "missing", " ($key) ");
            }
            elseif (($key == "StringLimit") && ($this->vars[$key] < 10))
            {
                $this->vars[$key] = 10;
            }
            elseif ($this->vars[$key] < 0)
            {
                $this->vars[$key] = -1;
            }
            $this->formData[$key] = $this->vars[$key];
        }
        $required = ["Language", "Template", "Style", "TemplateMenu"];
        foreach ($required as $value)
        {
            if (!array_key_exists($value, $this->vars))
            {
                $error = $this->errors->text("inputError", "missing", " ($value) ");
            }
            $this->formData[$value] = $this->vars[$value];
        }
        // Checkbox
        $this->formData['ListLink'] = array_key_exists('ListLink', $this->vars);
        if ($error)
        {
            $this->badInputLoad($error);
        }
        // write new values to setup session – read only user so session vars required!
        $this->session->writeArray($this->formData, "setup");
        $this->session->delVar("sql_LastMulti"); // always reset in case of paging changes
        $this->session->delVar("sql_LastIdeaSearch"); // always reset in case of paging changes
        $message = rawurlencode($this->success->text("config"));
        header("Location: index.php?action=usersgroups_PREFERENCES_CORE&method=init&success=config");
        die;
    }
    /**
     * Error handling
     *
     * @param mixed $error
     */
    private function badInputLoad($error)
    {
        $this->badInput->close($error, $this, 'init');
    }
}
