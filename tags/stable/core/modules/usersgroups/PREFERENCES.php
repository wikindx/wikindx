<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
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
    private $config;
    private $db;
    private $vars;
    private $bib;
    private $badInput;
    private $errorString = FALSE;

    public function __construct()
    {
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->config = FACTORY_CONFIG::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->badInput = FACTORY_BADINPUT::getInstance();
    }    /**
     * init
     *
     * @param string|FALSE $message
     */
    public function init($message = FALSE)
    {
    	if (!$message)
    	{
    		if ($message = $this->session->getVar('mywikindx_Message'))
    		{
    			$this->session->delVar('mywikindx_Message');
    		}
    	}
        $pString = $message;
        include_once("core/modules/help/HELPMESSAGES.php");
        $help = new HELPMESSAGES();
        GLOBALS::setTplVar('help', $help->createLink('preferences'));
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
        $template = $this->session->getVar("setup_Template", WIKINDX_TEMPLATE_DEFAULT);
        array_key_exists($template, $templates) ? $template = $template : $template = WIKINDX_TEMPLATE_DEFAULT;
    	$pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "template"),
            "Template",
            $templates,
            $template,
            4
        ) . " " . \HTML\span('*', 'required'));
        
        $menus[0] = $this->messages->text("config", "templateMenu1");
        $menus[1] = $this->messages->text("config", "templateMenu2");
        $menus[2] = $this->messages->text("config", "templateMenu3");
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "templateMenu"),
            "TemplateMenu",
            $menus,
            $this->session->getVar("setup_TemplateMenu"),
            3
        ) . " " . \HTML\span('*', 'required'));
        
        // For the graphical interface, add the "auto" value that allows to say that the language is chosen by the browser.
        $LanguageNeutralChoice = "auto";
        $languages[$LanguageNeutralChoice] = "Auto";
        $languages = array_merge($languages, \LOCALES\getSystemLocales());
        
        // Don't use the session value in that case because the language could have been changed localy by the chooseLanguage plugin
        $userId = $this->session->getVar('setup_UserId');
        $this->db->formatConditions(['usersId' => $userId]);
        $language = $this->db->selectFirstField("users", "usersLanguage");
        array_key_exists($language, $languages) ? $language = $language : $language = $LanguageNeutralChoice;
        
        // Retrieve the language of the user config in session if missing in the db
        if ($language == $LanguageNeutralChoice)
        {
            $language = $this->session->getVar("setup_Language", $LanguageNeutralChoice);
            array_key_exists($language, $languages) ? $language = $language : $language = $LanguageNeutralChoice;
        }
        
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "language"),
            "Language",
            $languages,
            $language
        ) . " " . \HTML\span('*', 'required'));
        
        // Display the user style but change the default selection of the list to the default style when no style is defined or a style not enabled is defined,
        // this avoid a crash when this option is written without value selected.
        $styles = \LOADSTYLE\loadDir();
        $style = $this->session->getVar("setup_Style", WIKINDX_STYLE_DEFAULT);
        array_key_exists($style, $styles) ? $style = $style : $style = WIKINDX_STYLE_DEFAULT;
        $pString .= \HTML\td(\FORM\selectedBoxValue(
            $this->messages->text("config", "style"),
            "Style",
            $styles,
            $style,
            4
        ) . " " . \HTML\span('*', 'required'));
        
        $pString .= \HTML\td('&nbsp;'); // blank 5th column
        $pString .= \HTML\trEnd();
        
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\HTML\hr(), FALSE, 5); // span 5 columns
        $pString .= \HTML\trEnd();
        
        $pString .= \HTML\trStart();
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingLimit"));
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "paging"),
            "Paging",
            $this->session->getVar("setup_Paging"),
            5
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingMaxLinks"));
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "maxPaging"),
            "PagingMaxLinks",
            $this->session->getVar("setup_PagingMaxLinks"),
            5
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        if (!$this->session->getVar("setup_PagingTagCloud"))
        {
            $this->session->setVar("setup_PagingTagCloud", 100);
        }
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingLimit"));
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "pagingTagCloud"),
            "PagingTagCloud",
            $this->session->getVar("setup_PagingTagCloud"),
            5
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        $hint = \HTML\aBrowse('green', '', $this->messages->text("hint", "hint"), '#', "", $this->messages->text("hint", "pagingLimit"));
        $pString .= \HTML\td(\FORM\textInput(
            $this->messages->text("config", "stringLimit"),
            "StringLimit",
            $this->session->getVar("setup_StringLimit"),
            5
        ) . " " . \HTML\span('*', 'required') . BR . \HTML\span($hint, 'hint'));
        $input = $this->session->getVar("setup_ListLink") ? "CHECKED" : FALSE;
        $pString .= \HTML\td(\FORM\checkbox($this->messages->text("config", "ListLink"), "ListLink", $input));
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
        $required = ["Paging", "PagingMaxLinks", "StringLimit", "PagingTagCloud"];
        foreach ($required as $key)
        {
            if (!is_numeric($this->vars[$key]) || !is_int($this->vars[$key] + 0))
            { // cast to number
                $this->badInputLoad($this->errors->text("inputError", "nan", " ($key) "));
            }
            if (!array_key_exists($key, $this->vars) || !$this->vars[$key])
            {
                $this->badInputLoad($this->errors->text("inputError", "missing", " ($key) "));
            }
            if (($key == 'PagingMaxLinks') && ($this->vars[$key] < 4))
            {
                $this->vars[$key] = 11;
            }
            elseif ($this->vars[$key] < 0)
            {
                $this->vars[$key] = -1;
            }
            $array[$key] = $this->vars[$key];
        }
        $required = ["Language", "Template", "Style"];
        foreach ($required as $value)
        {
            if (!array_key_exists($value, $this->vars) || !$this->vars[$value])
            {
                $this->badInputLoad($this->errors->text("inputError", "missing", " ($value) "));
            }
            $array[$value] = $this->vars[$value];
        }
        if (!array_key_exists("TemplateMenu", $this->vars))
        {
            $this->badInputLoad($this->errors->text("inputError", "missing", " (TemplateMenu) "));
        }
        else
        {
            $array['TemplateMenu'] = $this->vars['TemplateMenu'];
        }
        // All input good - write to session
        $this->session->writeArray($array, "setup");
        $this->session->delVar("sql_LastMulti"); // always reset in case of paging changes
        $this->session->delVar("sql_LastIdeaSearch"); // always reset in case of paging changes
        if (array_key_exists("ListLink", $this->vars))
        {
            $this->session->setVar("setup_ListLink", TRUE);
        }
        else
        {
            $this->session->delVar("setup_ListLink");
        }
        $this->session->setVar('mywikindx_Message', $this->success->text("config"));
        // need to use header() to ensure any change in appearance is immediately picked up.
        header("Location: index.php?action=usersgroups_PREFERENCES_CORE&method=init");
    }
    /**
     * Error handling
     */
    private function badInputLoad($error)
    {
       	$this->badInput->close($error, $this, 'init');
    }
}