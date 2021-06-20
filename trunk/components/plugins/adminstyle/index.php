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
 * ADMINSTYLE class.
 *
 * Administration of bibliographic styles
 */

/**
 * Import initial configuration and initialize the web server
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "startup", "WEBSERVERCONFIG.php"]));


class adminstyle_MODULE
{
    public $authorize;
    public $menus;
    private $db;
    private $vars;
    private $pluginmessages;
    private $coremessages;
    private $errors;
    private $session;
    private $creators;
    private $styles;
    private $styleMap;
    private $badInput;
    private $parseStyle;

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('adminstyle', 'adminstyleMessages');
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));
        $config = new adminstyle_CONFIG();
        $this->authorize = $config->authorize;
        if ($menuInit)
        {
            $this->makeMenu($config->menus);

            return; // Need do nothing more as this is simply menu initialisation.
        }

        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->authorize))
        { // not authorised
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();

        $this->styleMap = FACTORY_STYLEMAP::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->parseStyle = FACTORY_PARSESTYLE::getInstance();
        $this->styles = LOADSTYLE\loadDir(TRUE);
        $this->creators = ['creator1', 'creator2', 'creator3', 'creator4', 'creator5'];
    }
    /**
     * display options for styles
     *
     * @param false|string $message
     */
    public function display($message = FALSE)
    {
        // Clear previous style in session
        $this->session->clearArray("cite");
        $this->session->clearArray("style");
        $this->session->clearArray("partial");
        $this->session->clearArray("footnote");
        $pString = '';
        if ($message)
        {
            $pString .= HTML\p($message);
        }
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Add a style - display options
     *
     * @param false|string $error
     */
    public function addInit($error = FALSE)
    {
        // Clear previous style in session
        $this->session->clearArray("cite");
        $this->session->clearArray("style");
        $this->session->clearArray("partial");
        $this->session->clearArray("footnote");
        GLOBALS::setTplVar('help', \UTILS\createHelpTopicLink('adminstyle'));
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('addStyle'));
        $pString = '';
        if ($error)
        {
            $pString .= HTML\p($error, "error", "center");
        }
        $pString .= $this->displayStyleForm('add');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Write new style to text file
     */
    public function add()
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('addStyle'));
        if ($error = $this->validateInput('add'))
        {
            $this->badInput->close($error, $this, 'addInit');
        }
        $return = $this->parseStyle->getSource('vars');
        if ($return !== TRUE) {
            $this->badInput->close($return, $this, 'display');
        }
        $return = $this->parseStyle->writeFile();
        if ($return !== TRUE) {
            $this->badInput->close($return, $this, 'display');
        }
        // Remove sessionvars
        $this->session->clearArray("cite");
        $this->session->clearArray("style");
        $this->session->clearArray("footnote");
        $this->session->clearArray("partial");
        
        \UTILS\createComponentMetadataFile("style", mb_strtolower(trim($this->vars['styleShortName'])));
        \UTILS\enableComponent("style", mb_strtolower(trim($this->vars['styleShortName'])));
        
        $pString = \HTML\p($this->pluginmessages->text('successAdd'), 'success');
        // Reload styles list after adding a new
        $this->styles = \LOADSTYLE\loadDir();

        return $this->editInit($pString);
    }
    /**
     * display styles for editing
     *
     * @param false|string $message
     */
    public function editInit($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('editStyle'));
        $pString = '';
        if ($message)
        {
            $pString .= HTML\p($message);
        }
        $pString .= FORM\formHeader("adminstyle_editDisplay");
        $styleFile = $this->session->getVar("editStyleFile");
        if ($styleFile)
        {
            $pString .= FORM\selectedBoxValue(FALSE, "editStyleFile", $this->styles, $styleFile, 20);
        }
        else
        {
            $pString .= FORM\selectFBoxValue(FALSE, "editStyleFile", $this->styles, 20);
        }
        $pString .= BR . FORM\formSubmit($this->coremessages->text("submit", "Edit"));
        $pString .= FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Display a style for editing
     *
     * @param false|string $error
     */
    public function editDisplay($error = FALSE)
    {
        GLOBALS::setTplVar('help', \UTILS\createHelpTopicLink('adminstyle'));
        if (!$error)
        {
            $this->loadEditSession();
        }
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('editStyle'));
        $pString = '';
        if ($error)
        {
            $pString .= HTML\p($error, "error", "center");
        }
        $pString .= $this->displayStyleForm('edit');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Edit style
     */
    public function edit()
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('editStyle'));
        if ($error = $this->validateInput('edit'))
        {
            $this->badInput->close($error, $this, 'editDisplay');
        }
        $dirName = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES, mb_strtolower(trim($this->vars['styleShortName']))]);
        $fileName = implode(DIRECTORY_SEPARATOR, [$dirName, mb_strtolower(trim($this->vars['styleShortName'])) . ".xml"]);
        $return = $this->parseStyle->getSource('vars');
        if ($return !== TRUE) {
            $this->badInput->close($return, $this, 'display');
        }
        $return = $this->parseStyle->writeFile($fileName);
        if ($return !== TRUE) {
            $this->badInput->close($return, $this, 'display');
        }
        
        // Remove sessionvars
        $this->session->clearArray("cite");
        $this->session->clearArray("style");
        $this->session->clearArray("footnote");
        $this->session->clearArray("partial");
        // Delete cache file
        @unlink($dirName);
        $pString = \HTML\p($this->pluginmessages->text('successEdit'), 'success');

        return $this->editInit($pString);
    }
    /**
     * display styles for copying and making a new style
     *
     * @param false|string $error
     */
    public function copyInit($error = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('copyStyle'));
        $pString = FORM\formHeader("adminstyle_copyDisplay");
        $pString .= FORM\selectFBoxValue(FALSE, "editStyleFile", $this->styles, 20);
        $pString .= BR . FORM\formSubmit($this->coremessages->text("submit", "Edit"));
        $pString .= FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Display a style for copying
     *
     * @param false|string $error
     */
    public function copyDisplay($error = FALSE)
    {
        GLOBALS::setTplVar('help', \UTILS\createHelpTopicLink('adminstyle'));
        if (!$error)
        {
            $this->loadEditSession(TRUE);
        }
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('copyStyle'));
        $pString = '';
        if ($error)
        {
            $pString .= HTML\p($error, "error", "center");
        }
        $pString .= $this->displayStyleForm('copy');
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Write new copied style to text file
     *
     * @retunr string
     */
    public function copy()
    {
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('copyStyle'));
        if ($error = $this->validateInput('add'))
        {
            $this->badInput->close($error, $this, 'copyDisplay');
        }
        $return = $this->parseStyle->getSource('vars');
        if ($return !== TRUE) {
            $this->badInput->close($return, $this, 'display');
        }
        $return = $this->parseStyle->writeFile();
        if ($return !== TRUE) {
            $this->badInput->close($return, $this, 'display');
        }
        
        // Remove sessionvars
        $this->session->clearArray("cite");
        $this->session->clearArray("style");
        $this->session->clearArray("footnote");
        $this->session->clearArray("partial");
        
        \UTILS\createComponentMetadataFile("style", mb_strtolower(trim($this->vars['styleShortName'])));
        \UTILS\enableComponent("style", mb_strtolower(trim($this->vars['styleShortName'])));
        
        $pString = \HTML\p($this->pluginmessages->text('successAdd'), 'success');
        // Reload styles list after a duplication
        $this->styles = LOADSTYLE\loadDir();

        return $this->editInit($pString);
    }
    /**
     * Complete the in-text citation preview fields
     *
     * @param false|string $div
     */
    public function previewCite($div = FALSE)
    {
        $pString = HTML\tableStart('generalTable borderStyleSolid');
        $pString .= HTML\trStart();
        if ($div)
        {
            $pString .= HTML\td('&nbsp;');
            $pString .= HTML\trEnd();
            $pString .= HTML\tableEnd();

            return HTML\div($div, $pString);
        }
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "previewcite.php"]));
        $pc = new adminstyle_previewcite();
        $string = $pc->display();
        if ($string === FALSE)
        {
            $string = $this->pluginmessages->text('previewError');
        }
        $pString .= HTML\td($string);
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString = HTML\div($this->vars['div'], $pString);
        GLOBALS::addTplVar('content', AJAX\encode_jArray(['innerHTML' => $pString]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Complete the style preview fields
     *
     * @param false|string $div
     */
    public function previewStyle($div = FALSE)
    {
        $pString = HTML\tableStart('generalTable borderStyleSolid');
        $pString .= HTML\trStart();
        if ($div)
        {
            $pString .= HTML\td('&nbsp;');
            $pString .= HTML\trEnd();
            $pString .= HTML\tableEnd();

            return HTML\div($div, $pString);
        }
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "previewstyle.php"]));
        $ps = new adminstyle_previewstyle();
        $string = $ps->display();
        if ($string === FALSE)
        {
            $string = $this->pluginmessages->text('previewError');
        }
        $pString .= HTML\td($string);
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString = $pString;
        GLOBALS::addTplVar('content', AJAX\encode_jArray(['innerHTML' => $pString]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Make the menus
     *
     * @param array $menuArray
     */
    private function makeMenu($menuArray)
    {
        $this->menus = [
            $menuArray[0] => ['adminstylepluginSub' => [
                $this->pluginmessages->text('pluginSub') => FALSE,
                $this->pluginmessages->text('addStyle') => "addInit",
                $this->pluginmessages->text('copyStyle') => "copyInit",
                $this->pluginmessages->text('editStyle') => "editInit",
            ],
            ],
        ];
    }
    /**
     * Read data from style file and load it into the session
     *
     * @param bool $type
     * @param mixed $copy
     */
    private function loadEditSession($copy = FALSE)
    {
        // Clear previous session data
        $this->session->clearArray("style");
        $this->session->clearArray("cite");
        $this->session->clearArray("footnote");
        $this->session->clearArray("partial");
        $parseXML = FACTORY_PARSEXML::getInstance();
        $resourceTypes = array_keys($this->styleMap->types);
        $this->session->setVar("editStyleFile", $this->vars['editStyleFile']);
        $dir = mb_strtolower($this->vars['editStyleFile']);
        $fileName = $this->vars['editStyleFile'] . ".xml";
        
        $filePath = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES, $dir, $fileName]);
        if (is_readable($filePath))
        {
            $parseXML->extractEntries($filePath);
            $info = $parseXML->info;
            $citation = $parseXML->citation;
            $footnote = $parseXML->footnote;
            $common = $parseXML->common;
            $types = $parseXML->types;
            if (!$copy)
            {
                $this->session->setVar("style_shortName", $this->vars['editStyleFile']);
                $this->session->setVar("style_longName", ($info['description']));
            }
            $this->arrayToTemplate($citation, 'citation');
            $this->arrayToTemplate($footnote, 'footnote');
            $this->arrayToTemplate($common, 'common');
            $this->arrayToTemplate($types, 'style');
            foreach ($resourceTypes as $type)
            {
                $sessionKey = 'style_' . $type;
                if (!empty($this->$type))
                {
                    $this->session->setVar($sessionKey, htmlspecialchars($this->$type));
                }
                if (array_key_exists($type, $this->fallback))
                {
                    $sessionKey .= "_generic";
                    $this->session->setVar($sessionKey, $this->fallback[$type]);
                }
                $partialName = 'partial_' . $type;
                if (isset($this->$partialName) && $this->$partialName)
                {
                    $this->session->setVar($partialName, htmlspecialchars($this->$partialName));
                }
                $partialReplace = 'partial_' . $type . 'Replace';
                if (isset($this->$partialReplace) && $this->$partialReplace)
                {
                    $this->session->setVar($partialReplace, htmlspecialchars($this->$partialReplace));
                }
                else
                {
                    $this->session->delVar($partialReplace);
                }
            }
        }
        else
        {
            if ($copy)
            {
                $this->badInput->close($this->errors->text("file", "read"), $this, 'copyDisplay');
            }
            else
            {
                $this->badInput->close($this->errors->text("file", "read"), $this, 'editDisplay');
            }
        }
    }
    /**
     * Transform XML nodal array to resource type template strings for loading into the style editor
     *
     * @param array $types
     * @param string $templateType
     */
    private function arrayToTemplate($types, $templateType)
    {
        $this->fallback = [];
        foreach ($types as $key => $input)
        {
            $temp = [];
            /**
             * The resource type which will be our array name
             */
            if (($key == 'fallback') && ($templateType == 'style'))
            {
                $this->fallback = $input;

                continue;
            }
            if ($templateType == 'footnote')
            {
                $type = "footnote_" . $key;
            }
            else if ($templateType == 'citation')
            {
                $type = "cite_" . $key;
            }
            else if ($templateType == 'common')
            {
                $type = "style_" . $key;
            }
            else
            {
                $type = $key;
                $this->writeSessionRewriteCreators($type, $input);
            }
            if (is_array($input))
            {
                foreach ($input as $rKey => $value)
                {
                    if ($rKey == 'fallback')
                    {
                        continue;
                    }
                    $temp[$rKey] = $value;
                }
            }
            else {
            	$this->session->setVar($type, htmlspecialchars($input));
            	continue;
            }
            list($tempString, $partial, $partialReplace) = $this->parseStyle->arrayToTemplate($temp);
            $this->$type = $tempString;
            if ($templateType == 'style')
			{
				if (is_array($partial)) {
					list($partial, $null1, $null2) = $this->parseStyle->arrayToTemplate($partial);
				}
				$partialName = 'partial_' . $type;
				$partialReplaceName = 'partial_' . $type . 'Replace';
				$this->$partialName = $partial;
				$this->$partialReplaceName = $partialReplace;
			} else {
                $this->session->setVar($type, htmlspecialchars($tempString));
            }
        }
    }
    /**
     * Add resource-specific rewrite creator fields to session
     *
     * @param string $type
     * @param array $array
     */
    private function writeSessionRewriteCreators($type, $array)
    {
        foreach ($this->creators as $creatorField)
        {
            $name = $creatorField . "_firstString";
            if (array_key_exists($name, $array))
            {
                $sessionKey = 'style_' . $type . "_" . $name;
                $this->session->setVar($sessionKey, htmlspecialchars($array[$name]));
            }
            $name = $creatorField . "_firstString_before";
            if (array_key_exists($name, $array))
            {
                $sessionKey = 'style_' . $type . "_" . $name;
                $this->session->setVar($sessionKey, htmlspecialchars($array[$name]));
            }
            $name = $creatorField . "_remainderString";
            if (array_key_exists($name, $array))
            {
                $sessionKey = 'style_' . $type . "_" . $name;
                $this->session->setVar($sessionKey, htmlspecialchars($array[$name]));
            }
            $name = $creatorField . "_remainderString_before";
            if (array_key_exists($name, $array))
            {
                $sessionKey = 'style_' . $type . "_" . $name;
                $this->session->setVar($sessionKey, htmlspecialchars($array[$name]));
            }
            $name = $creatorField . "_remainderString_each";
            if (array_key_exists($name, $array))
            {
                $sessionKey = 'style_' . $type . "_" . $name;
                $this->session->setVar($sessionKey, htmlspecialchars($array[$name]));
            }
        }
    }
    /**
     * display the citation templating form
     *
     * @param string $type
     *
     * @return string
     */
    private function displayCiteForm($type)
    {
        $pString = HTML\h($this->pluginmessages->text('citationFormat') . " (" .
            $this->pluginmessages->text('citationFormatInText') . ")");
        // 1st., creator style
        $pString .= HTML\tableStart('styleTable borderStyleSolid');
        $pString .= HTML\trStart();
        $exampleName = ["Joe Bloggs", "Bloggs, Joe", "Bloggs Joe",
            $this->pluginmessages->text('lastName'), ];
        $exampleInitials = ["T. U. ", "T.U.", "T U ", "TU"];
        $example = [$this->pluginmessages->text('creatorFirstNameFull'),
            $this->pluginmessages->text('creatorFirstNameInitials'), ];
        $firstStyle = $this->session->getVar("cite_creatorStyle");
        $otherStyle = $this->session->getVar("cite_creatorOtherStyle");
        $initials = $this->session->getVar("cite_creatorInitials");
        $firstName = $this->session->getVar("cite_creatorFirstName");
        $useInitials = $this->session->getVar("cite_useInitials") ? TRUE : FALSE;
        $td = HTML\strong($this->pluginmessages->text('creatorStyle')) . BR .
            FORM\selectedBoxValue(
                $this->pluginmessages->text('creatorFirstStyle'),
                "cite_creatorStyle",
                $exampleName,
                $firstStyle,
                4
            );
        $td .= BR . "&nbsp;" . BR;
        $td .= FORM\selectedBoxValue(
            $this->pluginmessages->text('creatorOthers'),
            "cite_creatorOtherStyle",
            $exampleName,
            $otherStyle,
            4
        );
        $td .= BR . "&nbsp;" . BR;
        $td .= $this->pluginmessages->text('useInitials') . ' ' . FORM\checkbox(
            FALSE,
            "cite_useInitials",
            $useInitials
        );
        $td .= BR . "&nbsp;" . BR;
        $td .= FORM\selectedBoxValue(
            $this->pluginmessages->text('creatorInitials'),
            "cite_creatorInitials",
            $exampleInitials,
            $initials,
            4
        );
        $td .= BR . "&nbsp;" . BR;
        $td .= FORM\selectedBoxValue(
            $this->pluginmessages->text('creatorFirstName'),
            "cite_creatorFirstName",
            $example,
            $firstName,
            2
        );
        $uppercase = $this->session->getVar("cite_creatorUppercase") ?
            TRUE : FALSE;
        $td .= HTML\P(FORM\checkbox(
            $this->pluginmessages->text('uppercaseCreator'),
            "cite_creatorUppercase",
            $uppercase
        ));
        $pString .= HTML\td($td, 'padding5px');
        // Delimiters
        $twoCreatorsSep = stripslashes($this->session->getVar("cite_twoCreatorsSep"));
        $betweenFirst = stripslashes($this->session->getVar("cite_creatorSepFirstBetween"));
        $betweenNext = stripslashes($this->session->getVar("cite_creatorSepNextBetween"));
        $last = stripslashes($this->session->getVar("cite_creatorSepNextLast"));
        $td = HTML\strong($this->pluginmessages->text('creatorSep')) .
            HTML\p($this->pluginmessages->text('ifOnlyTwoCreators') . "&nbsp;" .
            FORM\textInput(FALSE, "cite_twoCreatorsSep", $twoCreatorsSep, 7, 255)) .
            $this->pluginmessages->text('sepCreatorsFirst') . "&nbsp;" .
            FORM\textInput(
                FALSE,
                "cite_creatorSepFirstBetween",
                $betweenFirst,
                7,
                255
            ) . BR .
            HTML\p($this->pluginmessages->text('sepCreatorsNext') . BR .
            $this->pluginmessages->text('creatorSepBetween') . "&nbsp;" .
            FORM\textInput(FALSE, "cite_creatorSepNextBetween", $betweenNext, 7, 255) .
            $this->pluginmessages->text('creatorSepLast') . "&nbsp;" .
            FORM\textInput(FALSE, "cite_creatorSepNextLast", $last, 7, 255));
        $td .= BR . "&nbsp;" . BR;
        // List abbreviation
        $example = [$this->pluginmessages->text('creatorListFull'), $this->pluginmessages->text('creatorListLimit')];
        $list = $this->session->getVar("cite_creatorList");
        $listMore = stripslashes($this->session->getVar("cite_creatorListMore"));
        $listLimit = stripslashes($this->session->getVar("cite_creatorListLimit"));
        $listAbbreviation = stripslashes($this->session->getVar("cite_creatorListAbbreviation"));
        $italic = $this->session->getVar("cite_creatorListAbbreviationItalic") ?
            TRUE : FALSE;
        $td .= HTML\strong($this->pluginmessages->text('creatorList')) .
            HTML\p(FORM\selectedBoxValue(
                FALSE,
                "cite_creatorList",
                $example,
                $list,
                2
            ) . BR .
            $this->pluginmessages->text('creatorListIf') . ' ' .
            FORM\textInput(FALSE, "cite_creatorListMore", $listMore, 3) .
            $this->pluginmessages->text('creatorListOrMore') . ' ' .
            FORM\textInput(FALSE, "cite_creatorListLimit", $listLimit, 3) . BR .
            $this->pluginmessages->text('creatorListAbbreviation') . ' ' .
            FORM\textInput(FALSE, "cite_creatorListAbbreviation", $listAbbreviation, 15) . ' ' .
            FORM\checkbox(FALSE, "cite_creatorListAbbreviationItalic", $italic) . ' ' .
            $this->pluginmessages->text('italics'));
        $list = $this->session->getVar("cite_creatorListSubsequent");
        $listMore = stripslashes($this->session->getVar("cite_creatorListSubsequentMore"));
        $listLimit = stripslashes($this->session->getVar("cite_creatorListSubsequentLimit"));
        $listAbbreviation = stripslashes($this->session->getVar("cite_creatorListSubsequentAbbreviation"));
        $italic = $this->session->getVar("cite_creatorListSubsequentAbbreviationItalic") ?
            TRUE : FALSE;
        $td .= BR . "&nbsp;" . BR;
        $td .= HTML\strong($this->pluginmessages->text('creatorListSubsequent')) .
            HTML\p(FORM\selectedBoxValue(
                FALSE,
                "cite_creatorListSubsequent",
                $example,
                $list,
                2
            ) . BR .
            $this->pluginmessages->text('creatorListIf') . ' ' .
            FORM\textInput(FALSE, "cite_creatorListSubsequentMore", $listMore, 3) .
            $this->pluginmessages->text('creatorListOrMore') . ' ' .
            FORM\textInput(FALSE, "cite_creatorListSubsequentLimit", $listLimit, 3) . BR .
            $this->pluginmessages->text('creatorListAbbreviation') . ' ' .
            FORM\textInput(FALSE, "cite_creatorListSubsequentAbbreviation", $listAbbreviation, 15) . ' ' .
            FORM\checkbox(FALSE, "cite_creatorListSubsequentAbbreviationItalic", $italic) . ' ' .
            $this->pluginmessages->text('italics'));
        $pString .= HTML\td($td, 'padding5px top');
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;
        // Miscellaneous citation formatting
        $pString .= HTML\tableStart('styleTable borderStyleSolid');
        $pString .= HTML\trStart();

        $firstChars = stripslashes($this->session->getVar("cite_firstChars"));
        $template = stripslashes($this->session->getVar("cite_template"));
        $lastChars = stripslashes($this->session->getVar("cite_lastChars"));
        $td = $this->pluginmessages->text('enclosingCharacters') . BR .
            FORM\textInput(FALSE, "cite_firstChars", $firstChars, 3, 255) . ' ... ' .
            FORM\textInput(FALSE, "cite_lastChars", $lastChars, 3, 255);
        $td .= BR . "&nbsp;" . BR;

        $availableFields = implode(', ', $this->styleMap->citation);
        $td .= $this->pluginmessages->text('template') . ' ' .
            FORM\textInput(FALSE, "cite_template", $template, 40, 255) .
            " " . HTML\span('*', 'required') .
            HTML\p(HTML\em($this->pluginmessages->text('availableFields')) .
            BR . $availableFields, "small");

        $replaceYear = stripslashes($this->session->getVar("cite_replaceYear"));
        $td .= HTML\p(FORM\textInput(
            $this->pluginmessages->text('replaceYear'),
            "cite_replaceYear",
            $replaceYear,
            10,
            255
        ));

        $td .= $this->pluginmessages->text('followCreatorTemplate');
        $template = stripslashes($this->session->getVar("cite_followCreatorTemplate"));
        $td .= HTML\p($this->pluginmessages->text('template') . ' ' .
            FORM\textInput(FALSE, "cite_followCreatorTemplate", $template, 40, 255)) .
            HTML\p(HTML\em($this->pluginmessages->text('availableFields')) .
            BR . $availableFields, "small");

        $pageSplit = $this->session->getVar("cite_followCreatorPageSplit") ? TRUE : FALSE;
        $td .= HTML\P($this->pluginmessages->text('followCreatorPageSplit') . "&nbsp;&nbsp;" .
            FORM\checkbox(FALSE, "cite_followCreatorPageSplit", $pageSplit));

        $consecutiveSep = stripslashes($this->session->getVar("cite_consecutiveCitationSep"));
        $td .= HTML\p($this->pluginmessages->text('consecutiveCitationSep') . ' ' .
            FORM\textInput(FALSE, "cite_consecutiveCitationSep", $consecutiveSep, 7));

        // Consecutive citations by same author(s)
        $consecutiveSep = stripslashes($this->session->getVar("cite_consecutiveCreatorSep"));
        $template = stripslashes($this->session->getVar("cite_consecutiveCreatorTemplate"));
        $availableFields = implode(', ', $this->styleMap->citation);
        $td .= HTML\p($this->pluginmessages->text('consecutiveCreator'));
        $td .= $this->pluginmessages->text('template') . ' ' .
            FORM\textInput(FALSE, "cite_consecutiveCreatorTemplate", $template, 40, 255) .
            HTML\p(HTML\em($this->pluginmessages->text('availableFields')) .
            BR . $availableFields, "small");
        $td .= $this->pluginmessages->text('consecutiveCreatorSep') . ' ' .
            FORM\textInput(FALSE, "cite_consecutiveCreatorSep", $consecutiveSep, 7);

        // Subsequent citations by same author(s)
        $template = stripslashes($this->session->getVar("cite_subsequentCreatorTemplate"));
        $td .= HTML\p($this->pluginmessages->text('subsequentCreator'));
        $td .= $this->pluginmessages->text('template') . ' ' .
            FORM\textInput(FALSE, "cite_subsequentCreatorTemplate", $template, 40, 255) .
            HTML\p(HTML\em($this->pluginmessages->text('availableFields')) .
            BR . $availableFields, "small");

        $fields = $this->session->getVar("cite_subsequentFields") ?
            TRUE : FALSE;
        $td .= HTML\P($this->pluginmessages->text('subsequentFields') . "&nbsp;&nbsp;" .
            FORM\checkbox(FALSE, "cite_subsequentFields", $fields));

        $example = [$this->pluginmessages->text('subsequentCreatorRange1'),
            $this->pluginmessages->text('subsequentCreatorRange2'),
            $this->pluginmessages->text('subsequentCreatorRange3'), ];
        $input = $this->session->getVar("cite_subsequentCreatorRange");
        $td .= FORM\selectedBoxValue(
            $this->pluginmessages->text('subsequentCreatorRange'),
            "cite_subsequentCreatorRange",
            $example,
            $input,
            3
        );
        $pString .= HTML\td($td, 'padding5px top');

        $example = ["132-9", "132-39", "132-139"];
        $input = $this->session->getVar("cite_pageFormat");
        $td = FORM\selectedBoxValue(
            $this->pluginmessages->text('pageFormat'),
            "cite_pageFormat",
            $example,
            $input,
            3
        );
        $td .= BR . "&nbsp;" . BR;
        $example = ["1998", "'98", "98"];
        $year = $this->session->getVar("cite_yearFormat");
        $td .= FORM\selectedBoxValue(
            $this->pluginmessages->text('yearFormat'),
            "cite_yearFormat",
            $example,
            $year,
            3
        );
        $td .= BR . "&nbsp;" . BR;
        $example = [$this->pluginmessages->text('titleAsEntered'),
            "WIKINDX bibliographic management system", ];
        $titleCapitalization = $this->session->getVar("cite_titleCapitalization");
        $td .= HTML\p($this->pluginmessages->text('titleCapitalization') . BR .
            FORM\selectedBoxValue(FALSE, "cite_titleCapitalization", $example, $titleCapitalization, 2));
        $separator = $this->session->getVar("cite_titleSubtitleSeparator");
        $td .= HTML\p($this->pluginmessages->text('titleSubtitleSeparator') . ":&nbsp;&nbsp;" .
            FORM\textInput(FALSE, "cite_titleSubtitleSeparator", $separator, 4));

        // Ambiguous citations
        $ambiguous = $this->session->getVar("cite_ambiguous");
        $example = [$this->pluginmessages->text('ambiguousUnchanged'),
            $this->pluginmessages->text('ambiguousYear'), $this->pluginmessages->text('ambiguousTitle'), ];
        $template = stripslashes($this->session->getVar("cite_ambiguousTemplate"));
        $td .= HTML\p(FORM\selectedBoxValue(
            HTML\strong($this->pluginmessages->text('ambiguous')),
            "cite_ambiguous",
            $example,
            $ambiguous,
            3
        ));
        $availableFields = implode(', ', $this->styleMap->citation);
        $td .= $this->pluginmessages->text('template') . ' ' .
            FORM\textInput(FALSE, "cite_ambiguousTemplate", $template, 40, 255) .
            HTML\p(HTML\em($this->pluginmessages->text('availableFields')) .
            BR . $availableFields, "small");

        $removeTitle = $this->session->getVar("cite_removeTitle") ?
            TRUE : FALSE;
        $td .= HTML\p($this->pluginmessages->text('removeTitle') . "&nbsp;&nbsp;" .
            FORM\checkbox(FALSE, "cite_removeTitle", $removeTitle));

        $jsonArray = [];
        $jScript = "index.php?action=adminstyle_previewCite&div=previewCite";
        $jsonArray[] = [
            'startFunction' => 'previewCite',
            'script' => "$jScript",
            'targetDiv' => 'previewCite',
        ];
        $previewImage = AJAX\jActionIcon('view', 'onclick', $jsonArray);
        $td .= HTML\p($this->pluginmessages->text('previewCite') . '&nbsp;&nbsp;' . $previewImage . '&nbsp;&nbsp;' .
            $this->previewCite('previewCite'));

        $pString .= HTML\td($td, 'padding5px width50percent');
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;

        // Endnote style citations
        $pString .= HTML\h($this->pluginmessages->text('citationFormat') . " (" .
            $this->pluginmessages->text('citationFormatEndnote') . ")");
        $pString .= HTML\tableStart('styleTable borderStyleSolid');
        $pString .= HTML\trStart();
        $td = HTML\p(HTML\strong($this->pluginmessages->text('endnoteFormat1')));
        $firstChars = stripslashes($this->session->getVar("cite_firstCharsEndnoteInText"));
        $lastChars = stripslashes($this->session->getVar("cite_lastCharsEndnoteInText"));
        $td .= $this->pluginmessages->text('enclosingCharacters') . BR .
            FORM\textInput(FALSE, "cite_firstCharsEndnoteInText", $firstChars, 3, 255) . ' ... ' .
            FORM\textInput(FALSE, "cite_lastCharsEndnoteInText", $lastChars, 3, 255);
        $td .= BR . "&nbsp;" . BR;

        $template = stripslashes($this->session->getVar("cite_templateEndnoteInText"));
        $availableFields = implode(', ', $this->styleMap->citationEndnoteInText);
        $td .= $this->pluginmessages->text('template') . ' ' .
            FORM\textInput(FALSE, "cite_templateEndnoteInText", $template, 40, 255) .
            " " . HTML\span('*', 'required') .
            HTML\p(HTML\em($this->pluginmessages->text('availableFields')) .
            BR . $availableFields, "small");

        $citeFormat = [$this->pluginmessages->text('normal'),
            $this->pluginmessages->text('superscript'), $this->pluginmessages->text('subscript'), ];
        $input = $this->session->getVar("cite_formatEndnoteInText");
        $td .= HTML\p(FORM\selectedBoxValue(FALSE, "cite_formatEndnoteInText", $citeFormat, $input, 3));

        $consecutiveSep = stripslashes($this->session->getVar("cite_consecutiveCitationEndnoteInTextSep"));
        $td .= HTML\p($this->pluginmessages->text('consecutiveCitationSep') . ' ' .
            FORM\textInput(FALSE, "cite_consecutiveCitationEndnoteInTextSep", $consecutiveSep, 7));

        $endnoteStyleArray = [$this->pluginmessages->text('endnoteStyle1'),
            $this->pluginmessages->text('endnoteStyle2'), $this->pluginmessages->text('endnoteStyle3'), ];
        $endnoteStyle = $this->session->getVar("cite_endnoteStyle");
        $td .= HTML\p(FORM\selectedBoxValue(
            $this->pluginmessages->text('endnoteStyle'),
            "cite_endnoteStyle",
            $endnoteStyleArray,
            $endnoteStyle,
            3
        ));

        $pString .= HTML\td($td, 'padding5px');

        $td = HTML\p(HTML\strong($this->pluginmessages->text('endnoteFormat2')));
        $td .= HTML\p($this->pluginmessages->text('endnoteFieldFormat'), "small");
        $template = stripslashes($this->session->getVar("cite_templateEndnote"));
        $availableFields = implode(', ', $this->styleMap->citationEndnote);
        $td .= $this->pluginmessages->text('template') . ' ' .
            FORM\textInput(FALSE, "cite_templateEndnote", $template, 40, 255) . " " .
            HTML\span('*', 'required') .
            HTML\p(HTML\em($this->pluginmessages->text('availableFields')) .
            BR . $availableFields, "small");

        $availableFields = implode(', ', $this->styleMap->citationEndnote);
        $ibid = stripslashes($this->session->getVar("cite_ibid"));
        $td .= FORM\textInput($this->pluginmessages->text('ibid'), "cite_ibid", $ibid, 40, 255);
        $td .= BR . "&nbsp;" . BR;
        $idem = stripslashes($this->session->getVar("cite_idem"));
        $td .= FORM\textInput($this->pluginmessages->text('idem'), "cite_idem", $idem, 40, 255);
        $td .= BR . "&nbsp;" . BR;
        $opCit = stripslashes($this->session->getVar("cite_opCit"));
        $td .= FORM\textInput($this->pluginmessages->text('opCit'), "cite_opCit", $opCit, 40, 255) .
            HTML\p(HTML\em($this->pluginmessages->text('availableFields')) .
            BR . $availableFields, "small");

        $firstChars = stripslashes($this->session->getVar("cite_firstCharsEndnoteID"));
        $lastChars = stripslashes($this->session->getVar("cite_lastCharsEndnoteID"));
        $td .= HTML\p($this->pluginmessages->text('endnoteIDEnclose') . BR .
            FORM\textInput(FALSE, "cite_firstCharsEndnoteID", $firstChars, 3, 255) . ' ... ' .
            FORM\textInput(FALSE, "cite_lastCharsEndnoteID", $lastChars, 3, 255));

        $input = $this->session->getVar("cite_formatEndnoteID");
        $td .= HTML\p(FORM\selectedBoxValue(FALSE, "cite_formatEndnoteID", $citeFormat, $input, 3));

        $pString .= HTML\td($td, 'padding5px');
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;

        // Creator formatting for footnotes
        $pString .= HTML\h($this->pluginmessages->text('citationFormatFootnote'));
        $pString .= $this->creatorFormatting("footnote", TRUE);

        // bibliography order
        $pString .= HTML\h($this->pluginmessages->text('orderBib1'));
        $pString .= HTML\tableStart('styleTable borderStyleSolid');
        $pString .= HTML\trStart();
        $heading = HTML\p($this->pluginmessages->text('orderBib2'));
        $sameIdOrderBib = $this->session->getVar("cite_sameIdOrderBib") ? TRUE : FALSE;
        $heading .= HTML\p($this->pluginmessages->text('orderBib3') . "&nbsp;&nbsp;" .
            FORM\checkbox(FALSE, "cite_sameIdOrderBib", $sameIdOrderBib));
        $pString .= HTML\td($heading);
        $pString .= HTML\trEnd();
        $pString .= HTML\trStart();
        $pString .= HTML\tdStart();
        $pString .= HTML\tableStart();
        $pString .= HTML\trStart();
        $order1 = $this->session->getVar("cite_order1");
        $order2 = $this->session->getVar("cite_order2");
        $order3 = $this->session->getVar("cite_order3");
        $radio = !$this->session->getVar("cite_order1desc") ?
            $this->pluginmessages->text('ascending') . "&nbsp;&nbsp;" .
            FORM\radioButton(FALSE, "cite_order1desc", 0, TRUE) . BR .
            $this->pluginmessages->text('descending') . "&nbsp;&nbsp;" .
            FORM\radioButton(FALSE, "cite_order1desc", 1) :
            $this->pluginmessages->text('ascending') . "&nbsp;&nbsp;" .
            FORM\radioButton(FALSE, "cite_order1desc", 0) . BR .
            $this->pluginmessages->text('descending') . "&nbsp;&nbsp;" .
            FORM\radioButton(FALSE, "cite_order1desc", 1, TRUE);
        $orderArray = [$this->pluginmessages->text('creator'),
            $this->pluginmessages->text('year'), $this->pluginmessages->text('title'), ];
        $tdString = HTML\td(FORM\selectedBoxValue(
            $this->pluginmessages->text('order1'),
            "cite_order1",
            $orderArray,
            $order1,
            3
        ) . HTML\p($radio), 'padding5px bottom');
        $radio = !$this->session->getVar("cite_order2desc") ?
            $this->pluginmessages->text('ascending') . "&nbsp;&nbsp;" .
            FORM\radioButton(FALSE, "cite_order2desc", 0, TRUE) . BR .
            $this->pluginmessages->text('descending') . "&nbsp;&nbsp;" .
            FORM\radioButton(FALSE, "cite_order2desc", 1) :
            $this->pluginmessages->text('ascending') . "&nbsp;&nbsp;" .
            FORM\radioButton(FALSE, "cite_order2desc", 0) . BR .
            $this->pluginmessages->text('descending') . "&nbsp;&nbsp;" .
            FORM\radioButton(FALSE, "cite_order2desc", 1, TRUE);
        $tdString .= HTML\td(FORM\selectedBoxValue(
            $this->pluginmessages->text('order2'),
            "cite_order2",
            $orderArray,
            $order2,
            3
        ) . HTML\p($radio), 'padding5px bottom');
        $radio = !$this->session->getVar("cite_order3desc") ?
            $this->pluginmessages->text('ascending') . "&nbsp;&nbsp;" .
            FORM\radioButton(FALSE, "cite_order3desc", 0, TRUE) . BR .
            $this->pluginmessages->text('descending') . "&nbsp;&nbsp;" .
            FORM\radioButton(FALSE, "cite_order3desc", 1) :
            $this->pluginmessages->text('ascending') . "&nbsp;&nbsp;" .
            FORM\radioButton(FALSE, "cite_order3desc", 0) . BR .
            $this->pluginmessages->text('descending') . "&nbsp;&nbsp;" .
            FORM\radioButton(FALSE, "cite_order3desc", 1, TRUE);
        $tdString .= HTML\td(FORM\selectedBoxValue(
            $this->pluginmessages->text('order3'),
            "cite_order3",
            $orderArray,
            $order3,
            3
        ) . HTML\p($radio), 'padding5px bottom');
        $pString .= $tdString;
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\tdEnd();
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;

        return $pString;
    }
    /**
     * display the style form for both adding and editing
     *
     * @param string $type
     *
     * @return string
     */
    private function displayStyleForm($type)
    {
        $this->db = FACTORY_DB::getInstance();
        $languages = \LOCALES\getSystemLocales();
        $types = array_keys($this->styleMap->types);
        if ($type == 'add')
        {
            $pString = FORM\formHeader("adminstyle_add");
        }
        elseif ($type == 'edit')
        {
            $pString = FORM\formHeader("adminstyle_edit");
        }
        else
        { // copy
            $pString = FORM\formHeader("adminstyle_copy");
        }
        $pString .= HTML\tableStart();
        $pString .= HTML\trStart();
        $input = stripslashes($this->session->getVar("style_shortName"));
        if ($type == 'add')
        {
            $pString .= HTML\td(FORM\textInput(
                $this->pluginmessages->text('shortName'),
                "styleShortName",
                $input,
                20,
                255
            ) . " " . HTML\span('*', 'required') .
                BR . $this->pluginmessages->text('hint_styleShortName'));
        }
        elseif ($type == 'edit')
        {
            $pString .=
                HTML\td(FORM\hidden("editStyleFile", $this->vars['editStyleFile']) .
                FORM\hidden("styleShortName", $input) . HTML\strong($this->vars['editStyleFile'] . ":&nbsp;&nbsp;"), 'top');
        }
        else
        { // copy
            $pString .= HTML\td(FORM\textInput(
                $this->pluginmessages->text('shortName'),
                "styleShortName",
                $input,
                20,
                255
            ) . " " . HTML\span('*', 'required') .
                BR . $this->pluginmessages->text('hint_styleShortName'));
        }
        $input = stripslashes($this->session->getVar("style_longName"));
        $pString .= HTML\td(FORM\textInput(
            $this->pluginmessages->text('longName'),
            "styleLongName",
            $input,
            50,
            255
        ) . " " . HTML\span('*', 'required'));

        $language = $this->session->getVar("style_localisation");
        if (!$language) {
            $language = WIKINDX_LANGUAGE_DEFAULT;
        }
        if (!array_key_exists($language, $languages)) {
            $language = WIKINDX_LANGUAGE_DEFAULT;
        }
        $pString .= HTML\td(FORM\selectedBoxValue(
            $this->pluginmessages->text('language'),
            "style_localisation",
            $languages,
            $language
        ) . " " . HTML\span('*', 'required'));

        $input = $this->session->getVar("cite_citationStyle");
        $example = [$this->pluginmessages->text('citationFormatInText'), $this->pluginmessages->text('citationFormatEndnote')];
        $pString .= HTML\td(FORM\selectedBoxValue(
            $this->pluginmessages->text('citationFormat'),
            "cite_citationStyle",
            $example,
            $input,
            2
        ) . " " . HTML\span('*', 'required'));

        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\hr();
        $pString .= $this->displayCiteForm('copy');
        $pString .= HTML\hr() . HTML\hr();
        $pString .= HTML\h($this->pluginmessages->text('bibFormat'));

        // Creator formatting for bibliography
        $pString .= $this->creatorFormatting("style");
        // Editor replacements
        $pString .= HTML\tableStart('styleTable borderStyleSolid');
        $pString .= HTML\trStart();
        $switch = $this->session->getVar("style_editorSwitch");
        $editorSwitchIfYes = stripslashes($this->session->getVar("style_editorSwitchIfYes"));
        $example = [$this->pluginmessages->text('no'), $this->pluginmessages->text('yes')];
        $pString .= HTML\td(HTML\strong($this->pluginmessages->text('editorSwitchHead')) . BR .
            FORM\selectedBoxValue(
                $this->pluginmessages->text('editorSwitch'),
                "style_editorSwitch",
                $example,
                $switch,
                2
            ), 'padding5px');
        $pString .= HTML\td(
            FORM\textInput(
                $this->pluginmessages->text('editorSwitchIfYes'),
                "style_editorSwitchIfYes",
                $editorSwitchIfYes,
                30,
                255
            ),
            'padding5px',
            '',
            "bottom"
        );
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;

        // Title capitalization, edition, day and month, runningTime and page formats
        $pString .= HTML\tableStart('styleTable borderStyleSolid');
        $pString .= HTML\trStart();
        $example = [$this->pluginmessages->text('titleAsEntered'), "WIKINDX bibliographic management system"];
        $input = $this->session->getVar("style_titleCapitalization");
        $td = HTML\strong($this->pluginmessages->text('titleCapitalization')) . BR .
            FORM\selectedBoxValue(FALSE, "style_titleCapitalization", $example, $input, 2);
        $input = $this->session->getVar("style_titleSubtitleSeparator");
        $td .= HTML\p($this->pluginmessages->text('titleSubtitleSeparator') . ":&nbsp;&nbsp;" .
            FORM\textInput(FALSE, "style_titleSubtitleSeparator", $input, 4));
        $pString .= HTML\td($td, 'padding5px');
        $example = ["3", "3.", "3rd"];
        $input = $this->session->getVar("style_editionFormat");
        $pString .= HTML\td(HTML\strong($this->pluginmessages->text('editionFormat')) . BR .
            FORM\selectedBoxValue(FALSE, "style_editionFormat", $example, $input, 3), 'padding5px');
        $example = ["132-9", "132-39", "132-139"];
        $input = $this->session->getVar("style_pageFormat");
        $pString .= HTML\td(HTML\strong($this->pluginmessages->text('pageFormat')) . BR .
            FORM\selectedBoxValue(FALSE, "style_pageFormat", $example, $input, 3), 'padding5px');
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;
        $pString .= HTML\tableStart('styleTable borderStyleSolid');
        $pString .= HTML\trStart();
        $example = ["10", "10.", "10th"];
        $input = $this->session->getVar("style_dayFormat");
        $leadingZero = $this->session->getVar("style_dayLeadingZero") ?
            TRUE : FALSE;
        $pString .= HTML\td(HTML\strong($this->pluginmessages->text('dayFormat')) . BR .
            FORM\selectedBoxValue(FALSE, "style_dayFormat", $example, $input, 3) .
            HTML\P(FORM\checkbox(
                $this->pluginmessages->text('dayLeadingZero'),
                "style_dayLeadingZero",
                $leadingZero
            )), 'padding5px');

        $example = ["Feb", "February", $this->pluginmessages->text('userMonthSelect')];
        $input = $this->session->getVar("style_monthFormat");
        $pString .= HTML\td(HTML\strong($this->pluginmessages->text('monthFormat')) . BR .
            FORM\selectedBoxValue(FALSE, "style_monthFormat", $example, $input, 3), 'padding5px');
        $example = ["Day Month", "Month Day"];
        $input = $this->session->getVar("style_dateFormat");
        $pString .= HTML\td(HTML\strong($this->pluginmessages->text('dateFormat')) . BR .
            FORM\selectedBoxValue(FALSE, "style_dateFormat", $example, $input, 2), 'padding5px');

        $input = $this->session->getVar("style_dateMonthNoDay");
        $inputString = stripslashes($this->session->getVar("style_dateMonthNoDayString"));
        $example = [$this->pluginmessages->text('dateMonthNoDay1'),
            $this->pluginmessages->text('dateMonthNoDay2'), ];
        $pString .= HTML\td(FORM\selectedBoxValue(
            $this->pluginmessages->text('dateMonthNoDay'),
            "style_dateMonthNoDay",
            $example,
            $input,
            2
        ) . BR .
            FORM\textInput(FALSE, "style_dateMonthNoDayString", $inputString, 30, 255) . BR .
            HTML\span($this->pluginmessages->text('dateMonthNoDayHint'), 'hint'), 'padding5px');

        $pString .= HTML\trEnd();
        $pString .= HTML\trStart();
        $monthString = '';
        for ($i = 1; $i <= 16; $i++)
        {
            $input = stripslashes($this->session->getVar("style_userMonth_$i"));
            if ($i == 7)
            {
                $monthString .= BR . "$i:&nbsp;&nbsp;" .
                FORM\textInput(FALSE, "style_userMonth_$i", $input, 15, 255);
            }
            elseif ($i >= 13)
            {
                if ($i == 13)
                {
                    $monthString .= BR . $this->pluginmessages->text('userSeasons') . BR;
                    $s = 'Spring';
                }
                elseif ($i == 14)
                {
                    $s = 'Summer';
                }
                elseif ($i == 15)
                {
                    $s = 'Autumn';
                }
                elseif ($i == 16)
                {
                    $s = 'Winter';
                }
                $monthString .= "$s:&nbsp;&nbsp;" . FORM\textInput(FALSE, "style_userMonth_$i", $input, 15, 255);
            }
            else
            {
                $monthString .= "$i:&nbsp;&nbsp;" .
                FORM\textInput(FALSE, "style_userMonth_$i", $input, 15, 255);
            }
        }
        $pString .= HTML\td($this->pluginmessages->text('userMonths') . BR .
            $monthString, 5);
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;

        // Date range formatting
        $pString .= HTML\strong($this->pluginmessages->text('dateRange')) . BR;
        $pString .= HTML\tableStart('styleTable borderStyleSolid');
        $pString .= HTML\trStart();
        $input = stripslashes($this->session->getVar("style_dateRangeDelimit1"));
        $input = stripslashes($this->session->getVar("style_dateRangeDelimit1"));
        $pString .= HTML\td(FORM\textInput(
            $this->pluginmessages->text('dateRangeDelimit1'),
            "style_dateRangeDelimit1",
            $input,
            6,
            255
        ), 'padding5px');
        $input = $this->session->getVar("style_dateRangeDelimit2");
        $pString .= HTML\td(FORM\textInput(
            $this->pluginmessages->text('dateRangeDelimit2'),
            "style_dateRangeDelimit2",
            $input,
            6,
            255
        ), 'padding5px');
        $pString .= HTML\trEnd();
        $pString .= HTML\trStart();
        $input = $this->session->getVar("style_dateRangeSameMonth");
        $example = [$this->pluginmessages->text('dateRangeSameMonth1'),
            $this->pluginmessages->text('dateRangeSameMonth2'), ];
        $pString .= HTML\td(FORM\selectedBoxValue(
            $this->pluginmessages->text('dateRangeSameMonth'),
            "style_dateRangeSameMonth",
            $example,
            $input,
            2
        ), 2);
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;

        $pString .= HTML\tableStart('styleTable borderStyleSolid');
        $pString .= HTML\trStart();
        $example = ["2'45\"", "2:45", "2,45", "2 hours, 45 minutes", "2 hours and 45 minutes", "165 minutes", "165 mins"];
        $input = $this->session->getVar("style_runningTimeFormat");
        $pString .= HTML\td(HTML\strong($this->pluginmessages->text('runningTimeFormat')) . BR .
            FORM\selectedBoxValue(FALSE, "style_runningTimeFormat", $example, $input, 5), 'padding5px');
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= BR . HTML\hr() . BR;

        // print some basic advice
        $pString .= HTML\p(
            $this->pluginmessages->text('templateHelp1') .
            BR . $this->pluginmessages->text('templateHelp2') .
            BR . $this->pluginmessages->text('templateHelp3') .
            BR . $this->pluginmessages->text('templateHelp4') .
            BR . $this->pluginmessages->text('templateHelp5') .
            BR . $this->pluginmessages->text('templateHelp6') .
            BR . $this->pluginmessages->text('templateHelp7'),
            "small"
        );

        $generic = ["genericBook" => $this->pluginmessages->text('genericBook'),
            "genericArticle" => $this->pluginmessages->text('genericArticle'),
            "genericMisc" => $this->pluginmessages->text('genericMisc'), ];
        $availableFieldsCitation = implode(', ', $this->styleMap->citation);
        // Grab any custom fields
        $customFields = [];
        $recordset = $this->db->select('custom', ['customId', 'customLabel']);
        while ($row = $this->db->fetchRow($recordset))
        {
            $customFields[base64_encode('custom_' . $row['customId'])] = 'custom_' . $row['customId'] .
            '&nbsp;(' . HTML\dbToFormTidy($row['customLabel']) . ')';
        }
        // Resource types
        foreach ($types as $key)
        {
            $availableFields = [];
            if (($key == 'genericBook') || ($key == 'genericArticle') || ($key == 'genericMisc'))
            {
                $required = HTML\span('*', 'required');
                $fallback = FALSE;
                $citationString = FALSE;
                $formElementName = FALSE;
            }
            else
            {
                $required = FALSE;
                $formElementName = "style_" . $key . "_generic";
                $input = $this->session->issetVar($formElementName) ? $this->session->getVar($formElementName) : "genericMisc";
                $fallback = FORM\selectedBoxValue(
                    $this->pluginmessages->text('fallback'),
                    $formElementName,
                    $generic,
                    $input,
                    3
                );
                // Replacement citation template for in-text citation for this type
                $citationStringName = "cite_" . $key . "Template";
                $citationNotInBibliography = "cite_" . $key . "_notInBibliography";
                $input = stripslashes($this->session->getVar($citationStringName));
                $notAdd = $this->session->getVar($citationNotInBibliography) ? TRUE : FALSE;
                $checkBox = BR . $this->pluginmessages->text('notInBibliography') .
                "&nbsp;" . FORM\checkbox(FALSE, $citationNotInBibliography, $notAdd);
                $citationString = HTML\p(FORM\textInput(
                    $this->pluginmessages->text('typeReplace'),
                    $citationStringName,
                    $input,
                    60,
                    255
                ) . $checkBox . BR .
                    HTML\em($this->pluginmessages->text('availableFields')) .
                    BR . $availableFieldsCitation, "small");
            }
            $keyName = 'style_' . $key;
            $partialTemplateName = "partial_" . $key;
            $partialReplaceName = "partial_" . $key . "Replace";
            $partialReplace = $this->session->getVar($partialReplaceName) ? TRUE : FALSE;
            $partialReplaceString = $this->pluginmessages->text('partialReplace') . ":&nbsp;&nbsp;" .
                FORM\checkbox(FALSE, $partialReplaceName, $partialReplace);
            $input = stripslashes($this->session->getVar($partialTemplateName));
            $partialTemplate = HTML\p(FORM\textInput(
                $this->pluginmessages->text('partialTemplate'),
                $partialTemplateName,
                $input,
                50,
                255
            ) . BR . $partialReplaceString);
            // Footnote template
            $footnoteTemplateName = "footnote_" . $key;
            $input = stripslashes($this->session->getVar($footnoteTemplateName));
            $footnoteTemplate = BR . FORM\textareaInput(
                $this->pluginmessages->text('footnoteTemplate'),
                $footnoteTemplateName,
                $input,
                80,
                3
            );
            $rewriteCreatorString = $this->rewriteCreators($key, $this->styleMap->$key);
            $pString .= BR . HTML\hr() . BR;
            $pString .= HTML\tableStart();
            $pString .= HTML\trStart();
            $input = stripslashes($this->session->getVar($keyName));
            $heading = HTML\strong($this->coremessages->text("resourceType", $key)) . BR .
                $this->pluginmessages->text('bibTemplate') . $required;
            $pString .= HTML\td(FORM\textareaInput(
                $heading,
                $keyName,
                $input,
                80,
                3
            ) . $footnoteTemplate . $partialTemplate .
                $rewriteCreatorString . $citationString);
            // List available fields for this type
            foreach ($this->styleMap->$key as $value)
            {
                $availableFields[base64_encode($value)] = $value;
            }
            
            // Build a select box of available fields for a bibliography
            $availableFields = array_merge($availableFields, $customFields);
            
            $availableElementName = "style_" . $key . "_availableBib";
            $varsArray = ['"' . $keyName . '"', '"' . $availableElementName . '"'];
            $jsonArray = [];
            $jsonArray[] = [
                'startFunction' => 'transferField',
                'startFunctionVars' => $varsArray,
            ];
            $toLeftImage = AJAX\jActionIcon('toLeft', 'onclick', $jsonArray);
            $availableFieldsBib = HTML\p($toLeftImage . '&nbsp;' . FORM\selectFBoxValue($this->pluginmessages->text('availableFieldsBib'), $availableElementName, $availableFields, 4));
            
            // Build an HTML area for a bibliography preview, a select box for disabling fields in this preview, and js code for refreshing this preview
            $disableFieldsNameStyle = "style_" . $key . "_disableBib";
            $disableFields = $availableFields;
            unset($disableFields[array_search('title', $disableFields)]);
            $disableFields = array_merge(["" => $this->pluginmessages->text('resetFields')], $disableFields);
            
            $divPreviewStyle = $keyName . '_previewStyle';
            $jsonArray = [];
            $jScript = "index.php?action=adminstyle_previewStyle&div=$divPreviewStyle";
            $varsArray = ['"' . $key . '"'];
            $jsonArray[] = [
                'startFunction' => 'previewBibliographyOrFootnote',
                'script' => "$jScript",
                'triggerField' => $disableFieldsNameStyle,
                'targetDiv' => $divPreviewStyle,
                'startFunctionVars' => $varsArray,
            ];
            
            $previewImage = AJAX\jActionIcon('view', 'onclick', $jsonArray);
            $previewStyle = $this->pluginmessages->text('previewStyle') . '&nbsp;&nbsp;' . $previewImage . '&nbsp;&nbsp;' . $this->previewStyle($divPreviewStyle);
            
            $js = AJAX\jActionForm('onclick', $jsonArray);
            $previewStyleDisableForm = FORM\selectedBoxValueMultiple($this->pluginmessages->text('disableFields'), $disableFieldsNameStyle, $disableFields, [], 5, FALSE, $js);
            
            
            // Build a select box of available fields for a footnote
            $availableElementName = "style_" . $key . "_availableFoot";
            $varsArray = ['"' . $footnoteTemplateName . '"', '"' . $availableElementName . '"'];
            $jsonArray = [];
            $jsonArray[] = [
                'startFunction' => 'transferField',
                'startFunctionVars' => $varsArray,
            ];
            $toLeftImage = AJAX\jActionIcon('toLeft', 'onclick', $jsonArray);
            // If 'pages' not in field list, add for field footnotes
            if (array_key_exists('pages', $this->styleMap->{$key}) && array_search('pages', $availableFields) === FALSE)
            {
                $availableFields[base64_encode('pages')] = 'pages';
            }
            
            $availableFieldsFoot = HTML\p($toLeftImage . '&nbsp;' . FORM\selectFBoxValue($this->pluginmessages->text('availableFieldsFoot'), $availableElementName, $availableFields, 4));
            
            // Build an HTML area for a footnote preview, a select box for disabling fields in this preview, and js code for refreshing this preview
            $disableFieldsNameFoot = "style_" . $key . "_disableFoot";
            $disableFields = $availableFields;
            unset($disableFields[array_search('title', $disableFields)]);
            $disableFields = array_merge(["" => $this->pluginmessages->text('resetFields')], $disableFields);
            
            $divPreviewFoot = $keyName . '_previewFootnote';
            $jsonArray = [];
            $jScript = "index.php?action=adminstyle_previewStyle&div=$divPreviewFoot";
            $varsArray = ['"' . $key . '"', TRUE];
            $jsonArray[] = [
                'startFunction' => 'previewBibliographyOrFootnote',
                'script' => "$jScript",
                'triggerField' => $disableFieldsNameFoot,
                'targetDiv' => $divPreviewFoot,
                'startFunctionVars' => $varsArray,
            ];
            
            $previewImage = AJAX\jActionIcon('view', 'onclick', $jsonArray);
            $previewFootnote = $this->pluginmessages->text('previewFoot') . '&nbsp;&nbsp;' . $previewImage . '&nbsp;&nbsp;' . $this->previewStyle($divPreviewFoot);
                
            $js = AJAX\jActionForm('onclick', $jsonArray);
            $previewFootNoteDisableForm = FORM\selectedBoxValueMultiple($this->pluginmessages->text('disableFields'), $disableFieldsNameFoot, $disableFields, [], 5, FALSE, $js);
            
            
            // Embed all of these fields in nested arrays
            $td = HTML\tableStart();
            $td .= HTML\trStart();
            $td .= HTML\td($availableFieldsBib . $availableFieldsFoot . HTML\p($fallback));
            $td .= HTML\trEnd();
            $td .= HTML\trStart();
            $td .= HTML\tdStart();
            $td .= HTML\tableStart();
            $td .= HTML\trStart();
            $td .= HTML\td($previewStyle, 'left top width80percent');
            $td .= HTML\td($previewStyleDisableForm);
            $td .= HTML\trEnd();
                        
            $td .= HTML\trStart();
            $td .= HTML\td(BR . $previewFootnote, 'left top width80percent');
            $td .= HTML\td(BR . $previewFootNoteDisableForm);
            $td .= HTML\trEnd();
            $td .= HTML\tableEnd();
            $td .= HTML\tdEnd();
            $td .= HTML\trEnd();
            $td .= HTML\tableEnd();

            $pString .= HTML\td($td, 'left top width50percent');
            $pString .= HTML\trEnd();
            $pString .= HTML\tableEnd();
        }
        if (($type == 'add') || ($type == 'copy'))
        {
            $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Add")));
        }
        else
        {
            $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Edit")));
        }
        $pString .= FORM\formEnd();
        AJAX\loadJavascript(WIKINDX_URL_BASE . '/' . WIKINDX_URL_COMPONENT_PLUGINS . '/adminstyle/adminstyle.js?ver=' . WIKINDX_PUBLIC_VERSION);

        return $pString;
    }
    /**
     * display creator formatting options for bibliographies and footnotes
     *
     * @param string $prefix
     * @param bool $footnote
     *
     * @return string
     */
    private function creatorFormatting($prefix, $footnote = FALSE)
    {
        // Display general options for creator limits, formats etc.
        // 1st., creator style
        $pString = HTML\tableStart($prefix . 'Table borderStyleSolid');
        $pString .= HTML\trStart();
        $exampleName = ["Joe Bloggs", "Bloggs, Joe", "Bloggs Joe",
            $this->pluginmessages->text('lastName'), ];
        $exampleInitials = ["T. U. ", "T.U.", "T U ", "TU"];
        $example = [$this->pluginmessages->text('creatorFirstNameFull'),
            $this->pluginmessages->text('creatorFirstNameInitials'), ];
        $firstStyle = $this->session->getVar($prefix . "_primaryCreatorFirstStyle");
        $otherStyle = $this->session->getVar($prefix . "_primaryCreatorOtherStyle");
        $initials = $this->session->getVar($prefix . "_primaryCreatorInitials");
        $firstName = $this->session->getVar($prefix . "_primaryCreatorFirstName");
        $td = HTML\strong($this->pluginmessages->text('primaryCreatorStyle')) . BR .
            FORM\selectedBoxValue(
                $this->pluginmessages->text('creatorFirstStyle'),
                $prefix . "_primaryCreatorFirstStyle",
                $exampleName,
                $firstStyle,
                4
            );
        $td .= BR . "&nbsp;" . BR;
        $td .= FORM\selectedBoxValue(
            $this->pluginmessages->text('creatorOthers'),
            $prefix . "_primaryCreatorOtherStyle",
            $exampleName,
            $otherStyle,
            4
        );
        $td .= BR . "&nbsp;" . BR;
        $td .= FORM\selectedBoxValue(
            $this->pluginmessages->text('creatorInitials'),
            $prefix . "_primaryCreatorInitials",
            $exampleInitials,
            $initials,
            4
        );
        $td .= BR . "&nbsp;" . BR;
        $td .= FORM\selectedBoxValue(
            $this->pluginmessages->text('creatorFirstName'),
            $prefix . "_primaryCreatorFirstName",
            $example,
            $firstName,
            2
        );
        $uppercase = $this->session->getVar($prefix . "_primaryCreatorUppercase") ?
            TRUE : FALSE;
        $td .= HTML\P(FORM\checkbox(
            $this->pluginmessages->text('uppercaseCreator'),
            $prefix . "_primaryCreatorUppercase",
            $uppercase
        ));
        $repeat = $this->session->getVar($prefix . "_primaryCreatorRepeat");
        $exampleRepeat = [$this->pluginmessages->text('repeatCreators1'),
            $this->pluginmessages->text('repeatCreators2'),
            $this->pluginmessages->text('repeatCreators3'), ];
        $td .= FORM\selectedBoxValue(
            $this->pluginmessages->text('repeatCreators'),
            $prefix . "_primaryCreatorRepeat",
            $exampleRepeat,
            $repeat,
            3
        ) . BR;
        $repeatString = stripslashes($this->session->getVar($prefix . "_primaryCreatorRepeatString"));
        $td .= FORM\textInput(FALSE, $prefix . "_primaryCreatorRepeatString", $repeatString, 15, 255);
        $pString .= HTML\td($td, 'padding5px');
        //		if (!$footnote)
        //		{
        // Other creators (editors, translators etc.)
        $firstStyle = $this->session->getVar($prefix . "_otherCreatorFirstStyle");
        $otherStyle = $this->session->getVar($prefix . "_otherCreatorOtherStyle");
        $initials = $this->session->getVar($prefix . "_otherCreatorInitials");
        $firstName = $this->session->getVar($prefix . "_otherCreatorFirstName");
        $td = HTML\strong($this->pluginmessages->text('otherCreatorStyle')) . BR .
                FORM\selectedBoxValue(
                    $this->pluginmessages->text('creatorFirstStyle'),
                    $prefix . "_otherCreatorFirstStyle",
                    $exampleName,
                    $firstStyle,
                    4
                );
        $td .= BR . "&nbsp;" . BR;
        $td .= FORM\selectedBoxValue(
            $this->pluginmessages->text('creatorOthers'),
            $prefix . "_otherCreatorOtherStyle",
            $exampleName,
            $otherStyle,
            4
        );
        $td .= BR . "&nbsp;" . BR;
        $td .= FORM\selectedBoxValue(
            $this->pluginmessages->text('creatorInitials'),
            $prefix . "_otherCreatorInitials",
            $exampleInitials,
            $initials,
            4
        );
        $td .= BR . "&nbsp;" . BR;
        $td .= FORM\selectedBoxValue(
            $this->pluginmessages->text('creatorFirstName'),
            $prefix . "_otherCreatorFirstName",
            $example,
            $firstName,
            2
        );
        $uppercase = $this->session->getVar($prefix . "_otherCreatorUppercase") ?
                TRUE : FALSE;
        $td .= HTML\P(FORM\checkbox(
            $this->pluginmessages->text('uppercaseCreator'),
            $prefix . "_otherCreatorUppercase",
            $uppercase
        ));
        $pString .= HTML\td($td, 'padding5px');
        //		}
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;

        // 2nd., creator delimiters
        $pString .= HTML\tableStart($prefix . 'Table borderStyleSolid');
        $pString .= HTML\trStart();
        $twoCreatorsSep = stripslashes($this->session->getVar($prefix . "_primaryTwoCreatorsSep"));
        $betweenFirst = stripslashes($this->session->getVar($prefix . "_primaryCreatorSepFirstBetween"));
        $betweenNext = stripslashes($this->session->getVar($prefix . "_primaryCreatorSepNextBetween"));
        $last = stripslashes($this->session->getVar($prefix . "_primaryCreatorSepNextLast"));
        $pString .= HTML\td(
            HTML\strong($this->pluginmessages->text('primaryCreatorSep')) .
            HTML\p($this->pluginmessages->text('ifOnlyTwoCreators') . "&nbsp;" .
            FORM\textInput(FALSE, $prefix . "_primaryTwoCreatorsSep", $twoCreatorsSep, 7, 255)) .
            $this->pluginmessages->text('sepCreatorsFirst') . "&nbsp;" .
            FORM\textInput(FALSE, $prefix . "_primaryCreatorSepFirstBetween", $betweenFirst, 7, 255) .
            BR . HTML\p($this->pluginmessages->text('sepCreatorsNext') . BR .
            $this->pluginmessages->text('creatorSepBetween') . "&nbsp;" .
            FORM\textInput(FALSE, $prefix . "_primaryCreatorSepNextBetween", $betweenNext, 7, 255) .
            $this->pluginmessages->text('creatorSepLast') . "&nbsp;" .
            FORM\textInput(FALSE, $prefix . "_primaryCreatorSepNextLast", $last, 7, 255)),
            'padding5px',
            '',
            "bottom"
        );
        $twoCreatorsSep = stripslashes($this->session->getVar($prefix . "_otherTwoCreatorsSep"));
        $betweenFirst = stripslashes($this->session->getVar($prefix . "_otherCreatorSepFirstBetween"));
        $betweenNext = stripslashes($this->session->getVar($prefix . "_otherCreatorSepNextBetween"));
        $last = stripslashes($this->session->getVar($prefix . "_otherCreatorSepNextLast"));
        $pString .= HTML\td(
            HTML\strong($this->pluginmessages->text('otherCreatorSep')) .
            HTML\p($this->pluginmessages->text('ifOnlyTwoCreators') . "&nbsp;" .
            FORM\textInput(FALSE, $prefix . "_otherTwoCreatorsSep", $twoCreatorsSep, 7, 255)) .
            $this->pluginmessages->text('sepCreatorsFirst') . "&nbsp;" .
            FORM\textInput(FALSE, $prefix . "_otherCreatorSepFirstBetween", $betweenFirst, 7, 255) .
            HTML\p($this->pluginmessages->text('sepCreatorsNext') . BR .
            $this->pluginmessages->text('creatorSepBetween') . "&nbsp;" .
            FORM\textInput(FALSE, $prefix . "_otherCreatorSepNextBetween", $betweenNext, 7, 255) .
            $this->pluginmessages->text('creatorSepLast') . "&nbsp;" .
            FORM\textInput(FALSE, $prefix . "_otherCreatorSepNextLast", $last, 7, 255)),
            'padding5px',
            '',
            "bottom"
        );
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;

        // 3rd., creator list limits
        $pString .= HTML\tableStart($prefix . 'Table borderStyleSolid');
        $pString .= HTML\trStart();
        $example = [$this->pluginmessages->text('creatorListFull'),
            $this->pluginmessages->text('creatorListLimit'), ];
        $list = $this->session->getVar($prefix . "_primaryCreatorList");
        $listMore = stripslashes($this->session->getVar($prefix . "_primaryCreatorListMore"));
        $listLimit = stripslashes($this->session->getVar($prefix . "_primaryCreatorListLimit"));
        $listAbbreviation = stripslashes($this->session->getVar($prefix . "_primaryCreatorListAbbreviation"));
        $italic = $this->session->getVar($prefix . "_primaryCreatorListAbbreviationItalic") ? TRUE : FALSE;
        $pString .= HTML\td(HTML\strong($this->pluginmessages->text('primaryCreatorList')) . BR .
            FORM\selectedBoxValue(
                FALSE,
                $prefix . "_primaryCreatorList",
                $example,
                $list,
                2
            ) . BR .
            $this->pluginmessages->text('creatorListIf') . ' ' .
            FORM\textInput(FALSE, $prefix . "_primaryCreatorListMore", $listMore, 3) .
            $this->pluginmessages->text('creatorListOrMore') . ' ' .
            FORM\textInput(FALSE, $prefix . "_primaryCreatorListLimit", $listLimit, 3) . BR .
            $this->pluginmessages->text('creatorListAbbreviation') . ' ' .
            FORM\textInput(FALSE, $prefix . "_primaryCreatorListAbbreviation", $listAbbreviation, 15) . ' ' .
            FORM\checkbox(FALSE, $prefix . "_primaryCreatorListAbbreviationItalic", $italic) . ' ' .
            $this->pluginmessages->text('italics'), 'padding5px');
        $list = $this->session->getVar($prefix . "_otherCreatorList");
        $listMore = stripslashes($this->session->getVar($prefix . "_otherCreatorListMore"));
        $listLimit = stripslashes($this->session->getVar($prefix . "_otherCreatorListLimit"));
        $listAbbreviation = stripslashes($this->session->getVar( $prefix . "_otherCreatorListAbbreviation"));
        $italic = $this->session->getVar($prefix . "_otherCreatorListAbbreviationItalic") ? TRUE : FALSE;
        $pString .= HTML\td(HTML\strong($this->pluginmessages->text('otherCreatorList')) . BR .
            FORM\selectedBoxValue(
                FALSE,
                $prefix . "_otherCreatorList",
                $example,
                $list,
                2
            ) . BR .
            $this->pluginmessages->text('creatorListIf') . ' ' .
            FORM\textInput(FALSE, $prefix . "_otherCreatorListMore", $listMore, 3) .
            $this->pluginmessages->text('creatorListOrMore') . ' ' .
            FORM\textInput(FALSE, $prefix . "_otherCreatorListLimit", $listLimit, 3) . BR .
            $this->pluginmessages->text('creatorListAbbreviation') . ' ' .
            FORM\textInput(FALSE, $prefix . "_otherCreatorListAbbreviation", $listAbbreviation, 15) . ' ' .
            FORM\checkbox(FALSE, $prefix . "_otherCreatorListAbbreviationItalic", $italic) . ' ' .
            $this->pluginmessages->text('italics'), 'padding5px');
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= BR . "&nbsp;" . BR;

        return $pString;
    }
    /**
     * Re-write creator(s) portion of templates to handle styles such as DIN 1505
     *
     * @param string $key
     * @param array $availableFields
     *
     * @return string
     */
    private function rewriteCreators($key, $availableFields)
    {
        $heading = HTML\p(HTML\strong($this->pluginmessages->text('rewriteCreator1')), "small");
        foreach ($this->creators as $creatorField)
        {
            if (!array_key_exists($creatorField, $availableFields))
            {
                continue;
            }
            $fields[$creatorField] = $availableFields[$creatorField];
        }
        if (!isset($fields))
        {
            return FALSE;
        }
        $pString = FALSE;
        foreach ($fields as $creatorField => $value)
        {
            $basicField = "style_" . $key . "_" . $creatorField;
            $field = HTML\td(HTML\p(HTML\em($value), "small"), 'padding5px', FALSE, "middle");
            $formString = $basicField . "_firstString";
            $string = stripslashes($this->session->getVar($formString));
            $formCheckbox = $basicField . "_firstString_before";
            $checkbox = $this->session->getVar($formCheckbox) ? TRUE : FALSE;
            $firstCheckbox = BR . $this->pluginmessages->text('rewriteCreator4') .
                "&nbsp;" . FORM\checkbox(FALSE, $formCheckbox, $checkbox);
            $first = HTML\td(HTML\p(FORM\textInput(
                $this->pluginmessages->text('rewriteCreator2'),
                $formString,
                $string,
                20,
                255
            ) . $firstCheckbox, "small"), 'padding5px', FALSE, "bottom");
            $formString = $basicField . "_remainderString";
            $string = stripslashes($this->session->getVar($formString));
            $formCheckbox = $basicField . "_remainderString_before";
            $checkbox = $this->session->getVar($formCheckbox) ? TRUE : FALSE;
            $remainderCheckbox = BR . $this->pluginmessages->text('rewriteCreator4') .
                "&nbsp;" . FORM\checkbox(FALSE, $formCheckbox, $checkbox);
            $formCheckbox = $basicField . "_remainderString_each";
            $checkbox = $this->session->getVar($formCheckbox) ? TRUE : FALSE;
            $remainderCheckbox .= ",&nbsp;&nbsp;&nbsp;" . $this->pluginmessages->text('rewriteCreator5') .
                "&nbsp;" . FORM\checkbox(FALSE, $formCheckbox, $checkbox);
            $remainder = HTML\td(HTML\p(FORM\textInput(
                $this->pluginmessages->text('rewriteCreator3'),
                $formString,
                $string,
                20,
                255
            ) . $remainderCheckbox, "small"), 'padding5px', FALSE, "bottom");
            $pString .= HTML\trStart() . $field . $first . $remainder . HTML\trEnd();
        }

        return $heading . HTML\tableStart('styleTable borderStyleSolid') . $pString . HTML\tableEnd();
    }

    /**
     * validate input
     *
     * @param string $type
     *
     * @return false|string
     */
    private function validateInput($type)
    {
        $error = FALSE;
        if (($type == 'add') || ($type == 'edit'))
        {
            $array = ["style_titleCapitalization", "style_primaryCreatorFirstStyle",
                "style_primaryCreatorOtherStyle", "style_primaryCreatorInitials",
                "style_primaryCreatorFirstName", "style_otherCreatorFirstStyle", "style_dateFormat",
                "style_otherCreatorOtherStyle", "style_otherCreatorInitials", "style_pageFormat",
                "style_otherCreatorFirstName", "style_primaryCreatorList", "style_dayFormat",
                "style_otherCreatorList", "style_monthFormat", "style_editionFormat",
                "style_runningTimeFormat", "style_editorSwitch", "style_primaryCreatorRepeat",
                "style_dateRangeSameMonth", "style_dateMonthNoDay", "style_localisation",
                "cite_creatorStyle", "cite_creatorOtherStyle", "cite_creatorInitials", "cite_creatorFirstName",
                "cite_twoCreatorsSep", "cite_creatorSepFirstBetween", "cite_creatorListSubsequentAbbreviation",
                "cite_creatorSepNextBetween", "cite_creatorSepNextLast",
                "cite_creatorList", "cite_creatorListMore", "cite_creatorListLimit", "cite_creatorListAbbreviation",
                "cite_creatorListSubsequent", "cite_creatorListSubsequentMore", "cite_creatorListSubsequentLimit",
                "cite_template", "cite_templateEndnoteInText", "cite_templateEndnote",
                "cite_consecutiveCitationSep", "cite_yearFormat", "cite_pageFormat",
                "cite_titleCapitalization", "cite_citationStyle", "cite_formatEndnoteInText", "cite_ambiguous",
                "cite_formatEndnoteID", "cite_subsequentCreatorRange",

                "footnote_primaryCreatorFirstStyle",
                "footnote_primaryCreatorOtherStyle", "footnote_primaryCreatorInitials",
                "footnote_primaryCreatorFirstName",
                "footnote_primaryCreatorList",  "footnote_primaryCreatorRepeat",
                /* Probably not required but code left here in case (see creatorsFormatting()) */
                "footnote_otherCreatorFirstStyle", "footnote_otherCreatorFirstName",
                "footnote_otherCreatorOtherStyle", "footnote_otherCreatorInitials", "footnote_otherCreatorList",

            ];

            $this->writeSession($array);
            if (!trim($this->vars['styleShortName']))
            {
                $error = $this->errors->text("inputError", "missing", ':&nbsp' . $this->pluginmessages->text('shortName'));
            }
            else
            {
                $this->session->setVar("style_shortName", trim($this->vars['styleShortName']));
            }
            if (preg_match("/\\s/u", trim($this->vars['styleShortName'])))
            {
                $error = $this->errors->text("inputError", "invalid", ':&nbsp' . $this->pluginmessages->text('shortName'));
            }
            elseif (!trim($this->vars['styleLongName']))
            {
                $error = $this->errors->text("inputError", "missing", ':&nbsp' . $this->pluginmessages->text('longName'));
            }
            elseif (!trim($this->vars['style_genericBook']))
            {
                $error = $this->errors->text("inputError", "missing", ':&nbsp' . $this->pluginmessages->text('genericBook'));
            }
            elseif (!trim($this->vars['style_genericArticle']))
            {
                $error = $this->errors->text("inputError", "missing", ':&nbsp' . $this->pluginmessages->text('genericArticle'));
            }
            elseif (!trim($this->vars['style_genericMisc']))
            {
                $error = $this->errors->text("inputError", "missing", ':&nbsp' . $this->pluginmessages->text('genericMisc'));
            }
            foreach ($array as $input)
            {
                if (!isset($this->vars[$input]))
                {
                    return $this->errors->text("inputError", "missing");
                }
            }
            if ($this->vars['cite_citationStyle'] == 1)
            { // endnotes
                // Must also have a bibliography template for the resource if a footnote template is defined
                if ($this->vars['cite_endnoteStyle'] == 2)
                { // footnotes
                    $types = array_keys($this->styleMap->types);
                    foreach ($types as $key)
                    {
                        $type = 'footnote_' . $key;
                        $input = trim(stripslashes($this->vars[$type]));
                        if ($input && !$this->vars['style_' . $key])
                        {
                            return $this->errors->text("inputError", "missing");
                        }
                    }
                    if (($this->vars['footnote_primaryCreatorList'] == 1) &&
                        (!trim($this->vars['footnote_primaryCreatorListLimit']) ||
                        (!$this->vars['footnote_primaryCreatorListMore'])))
                    {
                        $error = $this->errors->text("inputError", "missing");
                    }
                    elseif (($this->vars['footnote_primaryCreatorList'] == 1) &&
                        (!is_numeric($this->vars['footnote_primaryCreatorListLimit']) ||
                        !is_numeric($this->vars['footnote_primaryCreatorListMore'])))
                    {
                        $error = $this->errors->text("inputError", "nan");
                    }
                    elseif (($this->vars['footnote_otherCreatorList'] == 1) &&
                        (!trim($this->vars['footnote_otherCreatorListLimit']) ||
                        (!$this->vars['footnote_otherCreatorListMore'])))
                    {
                        $error = $this->errors->text("inputError", "missing");
                    }
                    elseif (($this->vars['footnote_otherCreatorList'] == 1) &&
                        (!is_numeric($this->vars['footnote_otherCreatorListLimit']) ||
                        !is_numeric($this->vars['footnote_otherCreatorListMore'])))
                    {
                        $error = $this->errors->text("inputError", "nan");
                    }
                    elseif (($this->vars['footnote_otherCreatorList'] == 1) &&
                        (!is_numeric($this->vars['footnote_otherCreatorListLimit']) ||
                        !is_numeric($this->vars['footnote_otherCreatorListMore'])))
                    {
                        $error = $this->errors->text("inputError", "nan");
                    }
                    elseif (($this->vars['footnote_primaryCreatorRepeat'] == 2) &&
                        !trim($this->vars['footnote_primaryCreatorRepeatString']))
                    {
                        $error = $this->errors->text("inputError", "missing");
                    }
                }
                if (!trim($this->vars["cite_templateEndnoteInText"]))
                {
                    $error = $this->errors->text("inputError", "missing");
                }
                elseif (!trim($this->vars["cite_templateEndnote"]))
                {
                    $error = $this->errors->text("inputError", "missing");
                }
            }
            elseif (!trim($this->vars['cite_template']))
            {
                $error = $this->errors->text("inputError", "missing", 'cite_template');
            }
            // If xxx_creatorList set to 1 (limit), we must have style_xxxCreatorListMore and xxx_CreatorListLimit. The
            // latter two must be numeric.
            if (($this->vars['style_primaryCreatorList'] == 1) &&
                (!trim($this->vars['style_primaryCreatorListLimit']) ||
                (!$this->vars['style_primaryCreatorListMore'])))
            {
                $error = $this->errors->text("inputError", "missing");
            }
            elseif (($this->vars['style_primaryCreatorList'] == 1) &&
                (!is_numeric($this->vars['style_primaryCreatorListLimit']) ||
                !is_numeric($this->vars['style_primaryCreatorListMore'])))
            {
                $error = $this->errors->text("inputError", "nan");
            }
            elseif (($this->vars['style_otherCreatorList'] == 1) &&
                (!trim($this->vars['style_otherCreatorListLimit']) ||
                (!$this->vars['style_otherCreatorListMore'])))
            {
                $error = $this->errors->text("inputError", "missing");
            }
            elseif (($this->vars['cite_creatorList'] == 1) &&
                (!trim($this->vars['cite_creatorListLimit']) ||
                (!$this->vars['cite_creatorListMore'])))
            {
                $error = $this->errors->text("inputError", "missing");
            }
            elseif (($this->vars['cite_creatorList'] == 1) &&
                (!is_numeric($this->vars['cite_creatorListLimit']) ||
                !is_numeric($this->vars['cite_creatorListMore'])))
            {
                $error = $this->errors->text("inputError", "nan");
            }
            elseif (($this->vars['cite_creatorListSubsequent'] == 1) &&
                (!trim($this->vars['cite_creatorListSubsequentLimit']) ||
                (!$this->vars['cite_creatorListSubsequentMore'])))
            {
                $error = $this->errors->text("inputError", "missing");
            }
            elseif (($this->vars['cite_creatorListSubsequent'] == 1) &&
                (!is_numeric($this->vars['cite_creatorListSubsequentLimit']) ||
                !is_numeric($this->vars['cite_creatorListSubsequentMore'])))
            {
                $error = $this->errors->text("inputError", "nan");
            }
            elseif (($this->vars['style_editorSwitch'] == 1) &&
                !trim($this->vars['style_editorSwitchIfYes']))
            {
                $error = $this->errors->text("inputError", "missing");
            }
            elseif (($this->vars['style_primaryCreatorRepeat'] == 2) &&
                !trim($this->vars['style_primaryCreatorRepeatString']))
            {
                $error = $this->errors->text("inputError", "missing");
            }
            elseif ($this->vars['style_monthFormat'] == 2)
            {
                for ($i = 1; $i <= 16; $i++)
                {
                    if (!trim($this->vars["style_userMonth_$i"]))
                    {
                        $error = $this->errors->text("inputError", "missing");
                    }
                }
            }
            // If style_dateMonthNoDay, style_dateMonthNoDayString must have at least 'date' in it
            elseif ($this->vars['style_dateMonthNoDay'])
            {
                if (mb_strstr($this->vars['style_dateMonthNoDayString'], "date") === FALSE)
                {
                    $error = $this->errors->text("inputError", "invalid");
                }
            }
            if (($this->vars["cite_ambiguous"] == 2) &&
                !trim($this->vars["cite_ambiguousTemplate"]))
            {
                $error = $this->errors->text("inputError", "missing");
            }
        }
        if ($type == 'add')
        {
            if (preg_match("/\\s/u", trim($this->vars['styleShortName'])))
            {
                $error = $this->errors->text("inputError", "invalid");
            }
            elseif (array_key_exists(mb_strtoupper(trim($this->vars['styleShortName'])), $this->styles))
            {
                $error = $this->errors->text("inputError", "styleExists");
            }
        }
        elseif ($type == 'editDisplay')
        {
            if (!array_key_exists('editStyleFile', $this->vars))
            {
                $error = $this->errors->text("inputError", "missing");
            }
        }
        if ($error)
        {
            return $error;
        }
        // FALSE means validated input
        return FALSE;
    }
    /**
     * Write session
     *
     * @param array $array
     */
    private function writeSession($array)
    {
        $types = array_keys($this->styleMap->types);
        if (trim($this->vars['styleLongName']))
        {
            $this->session->setVar("style_longName", trim(htmlspecialchars($this->vars['styleLongName'])));
        }
        // other resource types
        foreach ($types as $key)
        {
            // Footnote templates
            $array[] = 'footnote_' . $key;
            // Partial templates
            $array[] = 'partial_' . $key;
            $type = 'style_' . $key;
            if (trim($this->vars[$type]))
            {
                $this->session->setVar($type, trim(htmlspecialchars($this->vars[$type])));
            }
            // Rewrite creator strings
            foreach ($this->creators as $creatorField)
            {
                $basic = $type . "_" . $creatorField;
                $field = $basic . "_firstString";
                if (array_key_exists($field, $this->vars) && trim($this->vars[$field]))
                {
                    $this->session->setVar($field, htmlspecialchars($this->vars[$field]));
                }
                $field = $basic . "_firstString_before";
                if (isset($this->vars[$field]))
                {
                    $this->session->setVar($field, $this->vars[$field]);
                }
                $field = $basic . "_remainderString";
                if (array_key_exists($field, $this->vars) && trim($this->vars[$field]))
                {
                    $this->session->setVar($field, htmlspecialchars($this->vars[$field]));
                }
                $field = $basic . "_remainderString_before";
                if (isset($this->vars[$field]))
                {
                    $this->session->setVar($field, $this->vars[$field]);
                }
                $field = $basic . "_remainderString_each";
                if (isset($this->vars[$field]))
                {
                    $this->session->setVar($field, $this->vars[$field]);
                }
            }
            $field = "cite_" . $key . "_notInBibliography";
            if (isset($this->vars[$field]))
            {
                $this->session->setVar($field, trim($this->vars[$field]));
            }
            $citationStringName = 'cite_' . $key . "Template";
            if (array_key_exists($citationStringName, $this->vars) &&
            ($input = $this->vars[$citationStringName]))
            {
                $this->session->setVar($citationStringName, htmlspecialchars($input));
            }
            // Fallback styles
            if (($key != 'genericBook') && ($key != 'genericArticle') && ($key != 'genericMisc'))
            {
                $name = $type . "_generic";
                $this->session->setVar($name, trim($this->vars[$name]));
            }
        }
        // Other values. $array parameter is required, other optional input is added to the array
        $array[] = "style_primaryCreatorSepBetween";
        $array[] = "style_primaryCreatorSepLast";
        $array[] = "style_otherCreatorSepBetween";
        $array[] = "style_otherCreatorSepLast";
        $array[] = "style_primaryCreatorListMore";
        $array[] = "style_primaryCreatorListLimit";
        $array[] = "style_primaryCreatorListAbbreviation";
        $array[] = "style_otherCreatorListMore";
        $array[] = "style_otherCreatorListLimit";
        $array[] = "style_otherCreatorListAbbreviation";
        $array[] = "style_editorSwitchIfYes";
        $array[] = "style_primaryCreatorUppercase";
        $array[] = "style_otherCreatorUppercase";
        $array[] = "style_primaryTwoCreatorsSep";
        $array[] = "style_primaryCreatorSepFirstBetween";
        $array[] = "style_primaryCreatorSepNextBetween";
        $array[] = "style_primaryCreatorSepNextLast";
        $array[] = "style_otherTwoCreatorsSep";
        $array[] = "style_otherCreatorSepFirstBetween";
        $array[] = "style_otherCreatorSepNextBetween";
        $array[] = "style_otherCreatorSepNextLast";
        $array[] = "style_primaryCreatorRepeatString";
        $array[] = "style_primaryCreatorListAbbreviationItalic";
        $array[] = "style_otherCreatorListAbbreviationItalic";
        $array[] = "style_dateMonthNoDayString";
        $array[] = "style_userMonth_1";
        $array[] = "style_userMonth_2";
        $array[] = "style_userMonth_3";
        $array[] = "style_userMonth_4";
        $array[] = "style_userMonth_5";
        $array[] = "style_userMonth_6";
        $array[] = "style_userMonth_7";
        $array[] = "style_userMonth_8";
        $array[] = "style_userMonth_9";
        $array[] = "style_userMonth_10";
        $array[] = "style_userMonth_11";
        $array[] = "style_userMonth_12";
        $array[] = "style_userMonth_13";
        $array[] = "style_userMonth_14";
        $array[] = "style_userMonth_15";
        $array[] = "style_userMonth_16";
        $array[] = "style_dateRangeDelimit1";
        $array[] = "style_dateRangeDelimit2";
        $array[] = "style_dayLeadingZero";
        $array[] = "cite_useInitials";
        $array[] = "cite_creatorUppercase";
        $array[] = "cite_creatorListAbbreviationItalic";
        $array[] = "cite_creatorListSubsequentAbbreviationItalic";
        $array[] = "cite_ambiguousTemplate";
        $array[] = "cite_ibid";
        $array[] = "cite_idem";
        $array[] = "cite_opCit";
        $array[] = "cite_followCreatorTemplate";
        $array[] = "cite_consecutiveCreatorTemplate";
        $array[] = "cite_consecutiveCreatorSep";
        $array[] = "cite_firstChars";
        $array[] = "cite_lastChars";
        $array[] = "cite_consecutiveCitationEndnoteInTextSep";
        $array[] = "cite_firstCharsEndnoteInText";
        $array[] = "cite_lastCharsEndnoteInText";
        $array[] = "cite_endnoteStyle";
        $array[] = "cite_order1";
        $array[] = "cite_order2";
        $array[] = "cite_order3";
        $array[] = "cite_order1desc";
        $array[] = "cite_order2desc";
        $array[] = "cite_order3desc";
        $array[] = "cite_sameIdOrderBib";
        $array[] = "cite_firstCharsEndnoteID";
        $array[] = "cite_lastCharsEndnoteID";
        $array[] = "cite_followCreatorPageSplit";
        $array[] = "cite_subsequentCreatorTemplate";
        $array[] = "cite_replaceYear";
        $array[] = "cite_removeTitle";
        $array[] = "cite_subsequentFields";
        $array[] = "footnote_primaryCreatorSepBetween";
        $array[] = "footnote_primaryCreatorSepLast";
        $array[] = "footnote_primaryCreatorListMore";
        $array[] = "footnote_primaryCreatorListLimit";
        $array[] = "footnote_primaryCreatorListAbbreviation";
        $array[] = "footnote_primaryCreatorUppercase";
        $array[] = "footnote_primaryTwoCreatorsSep";
        $array[] = "footnote_primaryCreatorSepFirstBetween";
        $array[] = "footnote_primaryCreatorSepNextBetween";
        $array[] = "footnote_primaryCreatorSepNextLast";
        $array[] = "footnote_primaryCreatorRepeatString";
        $array[] = "footnote_primaryCreatorListAbbreviationItalic";
        /* Probably not required but code left here in case (see creatorsFormatting())
        */
        $array[] = "footnote_otherCreatorListAbbreviationItalic";
        $array[] = "footnote_otherTwoCreatorsSep";
        $array[] = "footnote_otherCreatorSepFirstBetween";
        $array[] = "footnote_otherCreatorSepNextBetween";
        $array[] = "footnote_otherCreatorSepNextLast";
        $array[] = "footnote_otherCreatorUppercase";
        $array[] = "footnote_otherCreatorListMore";
        $array[] = "footnote_otherCreatorListLimit";
        $array[] = "footnote_otherCreatorListAbbreviation";
        $array[] = "footnote_otherCreatorSepBetween";
        $array[] = "footnote_otherCreatorSepLast";

        foreach ($array as $input)
        {
            if (isset($this->vars[$input]))
            {
                $this->session->setVar($input, htmlspecialchars($this->vars[$input]));
            }
            else
            {
                $this->session->delVar($input);
            }
        }
    }
}
