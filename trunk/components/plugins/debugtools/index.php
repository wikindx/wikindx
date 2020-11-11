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
  * debugTools class.
  *
  * Session debug tools
  */

/**
 * Import initial configuration and initialize the web server
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "startup", "WEBSERVERCONFIG.php"]));


class debugtools_MODULE
{
    public $authorize;
    public $menus;
    private $pluginmessages;
    private $coremessages;
    private $errors;
    private $config;
    private $session;
    private $vars;

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('debugtools', 'debugtoolsMessages');
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));
        $this->config = new debugtools_CONFIG();
        $this->session = FACTORY_SESSION::getInstance();
        $this->authorize = $this->config->authorize;
        if ($menuInit)
        {
            $this->makeMenu($this->config->menus);

            return; // Need do nothing more as this is simply menu initialisation.
        }

        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->authorize))
        { // not authorised
            FACTORY_CLOSENOMENU::getInstance(); // die
        }

        $this->vars = GLOBALS::getVars();
    }
    /**
     * This is the initial method called from the menu item
     */
    public function init()
    {
        return $this->displaySession();
    }
    /**
     * Is this string a SQL query?
     *
     * @param string $Text
     *
     * @return bool
     */
    public function isSQLStatement($Text)
    {
        $SQLkeyWords = [
            'ANALYZE ',
            'SELECT ',
            'DISTINCT ',
            'UPDATE ',
            'EXECUTE ',
            'INSERT ',
            'DELETE ',
            'SET ',
            'UNION ',
            'SHOW ',
            'ALTER ',
            'CREATE ',
        ];
        
        foreach ($SQLkeyWords as $keyWord)
        {
            if (stripos($Text, $keyWord) !== FALSE)
            {
                return TRUE;
            }
        }
        
        return FALSE;
    }
    /**
     * Display the session variables
     *
     * @param false|string $message
     */
    public function displaySession($message = FALSE)
    {
        GLOBALS::setTplVar("heading", $this->pluginmessages->text("headingSession"));
        
        if ($message)
        {
            $pString = $message;
        }
        else
        {
            $pString = "";
        }
        
        $deleteAllLink = HTML\a(
            "link",
            $this->pluginmessages->text("deleteAllLink"),
            "index.php?action=debugtools_deleteAllSessionVariable"
        );
        
        $destroyLink = HTML\a(
            "link",
            $this->pluginmessages->text("destroyLink"),
            "index.php?action=debugtools_destroySession"
        );
        
        $pString .= HTML\p($deleteAllLink);
        $pString .= HTML\p($destroyLink);
        
        $pString .= HTML\tableStart("generalTable borderStyleSolid");
        
        $pString .= HTML\tHeadStart("bold alternate2");
        $pString .= HTML\trStart();
        $pString .= HTML\td($this->pluginmessages->text("action"), "padding5px");
        $pString .= HTML\td($this->pluginmessages->text("keys"), "padding5px");
        $pString .= HTML\td($this->pluginmessages->text("format"), "padding5px");
        $pString .= HTML\td($this->pluginmessages->text("values"), "padding5px");
        $pString .= HTML\trEnd();
        $pString .= HTML\theadEnd();
        
        $tmpSession = $_SESSION;
        ksort($tmpSession, SORT_NATURAL | SORT_FLAG_CASE);
        $i = 0;
        
        $pString .= HTML\tbodyStart();
        
        foreach ($tmpSession as $k => $v)
        {
            if (is_array($v) || is_object($v))
            {
                $v = print_r($v, TRUE);
            }
            $vd = $this->dumpEncodedData2Text($v);
            
            $deleteLink = HTML\a(
                "link",
                $this->pluginmessages->text("delete"),
                "index.php?action=debugtools_deleteSessionVariable" . htmlentities("&variable=" . $k)
            );
            
            if ($v == $vd)
            {
                $pString .= HTML\trStart("alternate" . (1 + $i % 2));
                $pString .= HTML\td($deleteLink, "middle padding5px");
                $pString .= HTML\td($k, "middle padding5px");
                $pString .= HTML\td($this->pluginmessages->text("both"), "middle padding5px");
                if ($this->isSQLStatement($v))
                {
                    $pString .= HTML\td(FORM\textareaInput("", "", $v, 100, 10), "middle padding5px");
                }
                else
                {
                    $pString .= HTML\td("<pre>" . $v . "</pre>", "middle padding5px");
                }
                $pString .= HTML\trEnd();
            }
            else
            {
                // Display raw data
                $pString .= HTML\trStart("alternate" . (1 + $i % 2));
                $pString .= HTML\td($deleteLink, "middle padding5px");
                $pString .= HTML\td($k, "middle padding5px");
                $pString .= HTML\td($this->pluginmessages->text("raw"), "middle padding5px");
                if ($this->isSQLStatement($v))
                {
                    $pString .= HTML\td(FORM\textareaInput("", "", $v, 100, 10), "middle padding5px");
                }
                else
                {
                    $pString .= HTML\td("<pre>" . print_r($v, TRUE) . "</pre>", "middle padding5px");
                }
                $pString .= HTML\trEnd();

                // Display format data
                $pString .= HTML\trStart("alternate" . (1 + $i % 2));
                $pString .= HTML\td("&nbsp;", "middle padding5px");
                $pString .= HTML\td("&nbsp;", "middle padding5px");
                $pString .= HTML\td($this->pluginmessages->text("decoded"), "middle padding5px");
                if ($this->isSQLStatement($vd))
                {
                    $pString .= HTML\td(FORM\textareaInput("", "", $vd, 100, 10), "middle padding5px");
                }
                else
                {
                    $pString .= HTML\td("<pre>" . $vd . "</pre>", "middle padding5px");
                }
                $pString .= HTML\trEnd();
            }
            
            $i++;
        }
        
        $pString .= HTML\tbodyEnd();
        $pString .= HTML\tableEnd();
        
        GLOBALS::addTplVar("content", $pString);
    }
    /**
     * Destroy un session variable
     *
     * @param false|string $message
     * @param false|string $errorMethod
     */
    public function deleteSessionVariable($message = FALSE, $errorMethod = FALSE)
    {
        $this->session->delVar($this->vars['variable']);
        $this->displaySession(HTML\p($this->pluginmessages->text("deleteSessionVariable"), 'success'));
    }
    /**
     * Destroy all session variables
     *
     * @param false|string $message
     * @param false|string $errorMethod
     */
    public function deleteAllSessionVariable($message = FALSE, $errorMethod = FALSE)
    {
        $this->session->clearSessionData();
        $this->displaySession(HTML\p($this->pluginmessages->text("deleteAllSessionVariable"), 'success'));
    }
    /**
     * Destroy the current session
     *
     * @param false|string $message
     * @param false|string $errorMethod
     */
    public function destroySession($message = FALSE, $errorMethod = FALSE)
    {
        $this->session->destroy();
        $this->displaySession(HTML\p($this->pluginmessages->text("destroySession"), 'success'));
    }
    /**
     * Display constants
     *
     * @param false|string $message
     */
    public function displayConstants($message = FALSE)
    {
        GLOBALS::setTplVar("heading", $this->pluginmessages->text("headingConstant"));
        
        if ($message)
        {
            $pString = $message;
        }
        else
        {
            $pString = "";
        }
        
        $i = 0;
        
        $categories = get_defined_constants(TRUE);
        
        $nav = HTML\aName("topnav", "&nbsp;");
        $nav .= $this->pluginmessages->text("catBrowse");
        
        foreach (array_keys($categories) as $category)
        {
            $aLink[] = HTML\a(
                "link",
                $category,
                "#$category"
            );
        }
        
        $nav .= implode(", ", $aLink) . ".";
        
        $pString .= HTML\p($nav);
        
        foreach ($categories as $category => $constants)
        {
            ksort($constants, SORT_NATURAL | SORT_FLAG_CASE);
        
            
            $pString .= HTML\aName($category, "&nbsp;");
            
            $pString .= HTML\tableStart("generalTable borderStyleSolid");
            
            $pString .= HTML\tHeadStart("bold");
            
            $pString .= HTML\trStart();
            $pString .= HTML\td($category . " " . HTML\a("link", "&uarr;", "#topnav"), "smallcaps alternate3 middle center padding5px", "2");
            $pString .= HTML\trEnd();
                
            $pString .= HTML\trStart();
            $pString .= HTML\td($this->pluginmessages->text("keys"), "alternate2 padding5px");
            $pString .= HTML\td($this->pluginmessages->text("values"), "alternate2 padding5px");
            $pString .= HTML\trEnd();
                
            $pString .= HTML\theadEnd();
            
            $pString .= HTML\tbodyStart();
            
            foreach ($constants as $key => $value)
            {
                $value = "<pre>" . $this->dumpEncodedData2Text($value) . "</pre>";
                    
                $pString .= HTML\trStart("alternate" . (1 + $i % 2));
                $pString .= HTML\td($key, "middle padding5px");
                $pString .= HTML\td($value, "middle padding5px");
                $pString .= HTML\trEnd();
                
                $i++;
            }
            
            $pString .= HTML\tbodyEnd();
            $pString .= HTML\tableEnd();
            $pString .= HTML\p("&nbsp;");
        }
        
        
        GLOBALS::addTplVar("content", $pString);
    }
    /**
     * Display available PHP extensions
     *
     * @param false|string $message
     */
    public function displayExtensions($message = FALSE)
    {
        GLOBALS::setTplVar("heading", $this->pluginmessages->text("headingExtension"));
        
        if ($message)
        {
            $pString = $message;
        }
        else
        {
            $pString = "";
        }
        
        $i = 0;
        
        $extensions = get_loaded_extensions();
        $extensions = array_flip($extensions);
        ksort($extensions, SORT_NATURAL | SORT_FLAG_CASE);
        
        foreach ($extensions as $k => $v)
        {
            $extensions[$k] = ["required" => "--", "loaded" => $this->pluginmessages->text("yes")];
        }

        $extRequirements = [];
        foreach (\UTILS\listCoreMandatoryPHPExtensions() as $ext)
        {
            $extRequirements[$ext] = "mandatory";
        }
        foreach (\UTILS\listCoreOptionalPHPExtensions() as $ext)
        {
            $extRequirements[$ext] = "optional";
        }
        
        foreach ($extRequirements as $extension => $status)
        {
            $status = $this->pluginmessages->text($status);
            
            if (array_key_exists($extension, $extensions))
            {
                $extensions[$extension]["required"] = $status;
            }
            else
            {
                $extensions[$extension] = ["required" => $status, "loaded" => $this->pluginmessages->text("no")];
            }
        }
        
        
        
        $pString .= HTML\tableStart("generalTable borderStyleSolid");
        
        $pString .= HTML\tHeadStart("bold");
        
        $pString .= HTML\trStart();
        $pString .= HTML\td($this->pluginmessages->text("extension"), "alternate2 padding5px");
        $pString .= HTML\td($this->pluginmessages->text("requirements"), "alternate2 padding5px");
        $pString .= HTML\td($this->pluginmessages->text("loaded"), "alternate2 padding5px");
        $pString .= HTML\trEnd();
            
        $pString .= HTML\theadEnd();
        $pString .= HTML\tbodyStart();
        
        foreach ($extensions as $extension => $v)
        {
            $pString .= HTML\trStart("alternate" . (1 + $i % 2));
            $pString .= HTML\td($extension, "middle padding5px");
            $pString .= HTML\td($v["required"], "middle padding5px");
            $pString .= HTML\td($v["loaded"], "middle padding5px");
                    
            $pString .= HTML\trEnd();
            
            $i++;
        }
        
        $pString .= HTML\tbodyEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\p("&nbsp;");
        
        
        GLOBALS::addTplVar("content", $pString);
    }
    /**
     * Display cookies
     *
     * @param false|string $message
     */
    public function displayCookies($message = FALSE)
    {
        GLOBALS::setTplVar("heading", $this->pluginmessages->text("headingCookie"));
        
        if ($message)
        {
            $pString = $message;
        }
        else
        {
            $pString = "";
        }
        
        $i = 0;
        
        $pString .= HTML\tableStart("generalTable borderStyleSolid");
        
        $pString .= HTML\tHeadStart("bold");
        
        $pString .= HTML\trStart();
        $pString .= HTML\td($this->pluginmessages->text("keys"), "alternate2 padding5px");
        $pString .= HTML\td($this->pluginmessages->text("values"), "alternate2 padding5px");
        $pString .= HTML\trEnd();
            
        $pString .= HTML\theadEnd();
        $pString .= HTML\tbodyStart();
        
        if (isset($_COOKIE))
        {
            $cookies = $_COOKIE;
        }
        else
        {
            $cookies = [];
        }
        
        foreach ($cookies as $k => $v)
        {
            $pString .= HTML\trStart("alternate" . (1 + $i % 2));
            $pString .= HTML\td($k, "middle padding5px");
            if ($k != 'PHPSESSID')
            {
                $pString .= HTML\td('<pre>' . $this->dumpEncodedData2Text($v) . '</pre>', "middle padding5px");
            }
            else
            {
                $pString .= HTML\td($v, "middle padding5px");
            }
            $pString .= HTML\trEnd();
        }
        
        $pString .= HTML\tbodyEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\p("&nbsp;");
        
        
        GLOBALS::addTplVar("content", $pString);
    }
    /**
     * Display server variables
     *
     * @param false|string $message
     */
    public function displayServer($message = FALSE)
    {
        GLOBALS::setTplVar("heading", $this->pluginmessages->text("headingServer"));
        
        if ($message)
        {
            $pString = $message;
        }
        else
        {
            $pString = "";
        }
        
        $i = 0;
        
        $pString .= HTML\tableStart("generalTable borderStyleSolid");
        
        $pString .= HTML\tHeadStart("bold");
        
        $pString .= HTML\trStart();
        $pString .= HTML\td($this->pluginmessages->text("keys"), "alternate2 padding5px");
        $pString .= HTML\td($this->pluginmessages->text("values"), "alternate2 padding5px");
        $pString .= HTML\trEnd();
            
        $pString .= HTML\theadEnd();
        $pString .= HTML\tbodyStart();
        
        $servers = $_SERVER;
        ksort($servers, SORT_NATURAL | SORT_FLAG_CASE);
        
        foreach ($servers as $k => $v)
        {
            $pString .= HTML\trStart("alternate" . (1 + $i % 2));
            $pString .= HTML\td($k, "middle padding5px");
            $pString .= HTML\td($v, "middle padding5px");
            $pString .= HTML\trEnd();
            
            $i++;
        }
        
        $pString .= HTML\tbodyEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\p("&nbsp;");
        
        
        GLOBALS::addTplVar("content", $pString);
    }
    /**
     * Display environment variables
     *
     * @param false|string $message
     */
    public function displayEnvironment($message = FALSE)
    {
        GLOBALS::setTplVar("heading", $this->pluginmessages->text("headingEnvironment"));
        
        if ($message)
        {
            $pString = $message;
        }
        else
        {
            $pString = "";
        }
        
        $i = 0;
        
        $pString .= HTML\tableStart("generalTable borderStyleSolid");
        
        $pString .= HTML\tHeadStart("bold");
        
        $pString .= HTML\trStart();
        $pString .= HTML\td($this->pluginmessages->text("keys"), "alternate2 padding5px");
        $pString .= HTML\td($this->pluginmessages->text("values"), "alternate2 padding5px");
        $pString .= HTML\trEnd();
            
        $pString .= HTML\theadEnd();
        $pString .= HTML\tbodyStart();
        
        $envConfig = (array) new CONFIG();
        ksort($envConfig, SORT_NATURAL | SORT_FLAG_CASE);
        
        foreach ($envConfig as $k => $v)
        {
            $pString .= HTML\trStart("alternate" . (1 + $i % 2));
            $pString .= HTML\td($k, "middle padding5px");
            $pString .= HTML\td("<pre>" . $this->dumpEncodedData2Text($v) . "</pre>", "middle padding5px");
            $pString .= HTML\trEnd();
            
            $i++;
        }
        
        $pString .= HTML\tbodyEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\p("&nbsp;");
        
        
        GLOBALS::addTplVar("content", $pString);
    }
    /**
     * Display variables of the application configuration
     *
     * @param false|string $message
     */
    public function displayConfigApplication($message = FALSE)
    {
        $db = FACTORY_DB::getInstance();
        
        GLOBALS::setTplVar("heading", $this->pluginmessages->text("headingConfigApplication"));
        
        if ($message)
        {
            $pString = $message;
        }
        else
        {
            $pString = "";
        }
        
        $i = 0;
        
        $pString .= HTML\tableStart("generalTable borderStyleSolid");
        
        $pString .= HTML\tHeadStart("bold");
        
        $pString .= HTML\trStart();
        $pString .= HTML\td($this->pluginmessages->text("id"), "alternate2 padding5px");
        $pString .= HTML\td($this->pluginmessages->text("field"), "alternate2 padding5px");
        $pString .= HTML\td($this->pluginmessages->text("value"), "alternate2 padding5px");
        $pString .= HTML\trEnd();
            
        $pString .= HTML\theadEnd();
        $pString .= HTML\tbodyStart();
        
        $db->orderBy("configName");
        $resultSet = $db->select("config", "*");
        
        while ($row = $db->fetchRow($resultSet))
        {
            $pString .= HTML\trStart("alternate" . (1 + $i % 2));
            
            $tmpId = "";
            $tmpName = "";
            $tmpValue = "";
            
            foreach ($row as $k => $v)
            {
                if ($k == "configId")
                {
                    $tmpId = $v;
                }
                elseif ($k == "configName")
                {
                    $tmpName = $v;
                }
                elseif ($v != NULL)
                {
                    $tmpValue = $v;

                    break;
                }
            }

            $tmpValue = $this->dumpEncodedData2Text($tmpValue);
            if (in_array($tmpName, ["configMailSmtpPassword", "configCmsDbPassword"]))
            {
                $tmpValue = str_repeat("*", strlen($tmpValue)) . ' ' . $this->pluginmessages->text("security");
            }
            $tmpValue = "<pre>" . $tmpValue . "</pre>";
            
            $pString .= HTML\td($tmpId, "middle padding5px");
            $pString .= HTML\td($tmpName, "middle padding5px");
            $pString .= HTML\td($tmpValue, "middle padding5px");
            
            $pString .= HTML\trEnd();
            $i++;
        }
        
        $pString .= HTML\tbodyEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\p("&nbsp;");
        
        
        GLOBALS::addTplVar("content", $pString);
    }
    /**
     * Display variables of the user configuration
     *
     * @param false|string $message
     */
    public function displayConfigUser($message = FALSE)
    {
        $db = FACTORY_DB::getInstance();
        
        GLOBALS::setTplVar("heading", $this->pluginmessages->text("headingConfigUser"));
        
        if ($message)
        {
            $pString = $message;
        }
        else
        {
            $pString = "";
        }
        
        $i = 0;
        
        $pString .= HTML\tableStart("generalTable borderStyleSolid");
        
        $pString .= HTML\tHeadStart("bold");
        
        $pString .= HTML\trStart();
        $pString .= HTML\td($this->pluginmessages->text("field"), "alternate2 padding5px");
        $pString .= HTML\td($this->pluginmessages->text("value"), "alternate2 padding5px");
        $pString .= HTML\trEnd();
            
        $pString .= HTML\theadEnd();
        $pString .= HTML\tbodyStart();
        
        $userId = $this->session->getVar("setup_UserId");
        $db->formatConditions(['usersId' => $userId]);
        $resultSet = $db->select("users", "*");
        
        while ($row = $db->fetchRow($resultSet))
        {
            foreach ($row as $k => $v)
            {
                $v = $this->dumpEncodedData2Text($v);
                
                if ($k == "usersPassword")
                {
                    $v = str_repeat("*", strlen($v)) . ' ' . $this->pluginmessages->text("security");
                }

                $v = "<pre>" . $v . "</pre>";
                
                $pString .= HTML\trStart("alternate" . (1 + $i % 2));
                $pString .= HTML\td($k, "middle padding5px");
                $pString .= HTML\td($v, "middle padding5px");
                $pString .= HTML\trEnd();
                
                $i++;
            }
        }
        
        $pString .= HTML\tbodyEnd();
        $pString .= HTML\tableEnd();
        $pString .= HTML\p("&nbsp;");
        
        GLOBALS::addTplVar("content", $pString);
    }
    /**
     * Make the menus
     *
     * @param array $menuArray
     */
    private function makeMenu($menuArray)
    {
        $this->menus = [
            $menuArray[0] => ['debugtoolspluginSub' => [
                $this->pluginmessages->text('menu') => FALSE,
            ],
            ],
        ];
        
        $this->menus[$menuArray[0]]['debugtoolspluginSub'][$this->pluginmessages->text('menuConfigApplication')] = "displayConfigApplication";
        $this->menus[$menuArray[0]]['debugtoolspluginSub'][$this->pluginmessages->text('menuConfigUser')] = "displayConfigUser";
        $this->menus[$menuArray[0]]['debugtoolspluginSub'][$this->pluginmessages->text('menuConstant')] = "displayConstants";
        $this->menus[$menuArray[0]]['debugtoolspluginSub'][$this->pluginmessages->text('menuCookie')] = "displayCookies";
        $this->menus[$menuArray[0]]['debugtoolspluginSub'][$this->pluginmessages->text('menuEnvironment')] = "displayEnvironment";
        $this->menus[$menuArray[0]]['debugtoolspluginSub'][$this->pluginmessages->text('menuExtension')] = "displayExtensions";
        $this->menus[$menuArray[0]]['debugtoolspluginSub'][$this->pluginmessages->text('menuServer')] = "displayServer";
        $this->menus[$menuArray[0]]['debugtoolspluginSub'][$this->pluginmessages->text('menuSession')] = "displaySession";
    }
    /**
     * Decode an object or an array serialized and encoded in base64, and other data type
     *
     * Return a human-readable string representing $encodedData. If the decoding fails $encodedData is returned.
     *
     * @param mixed $encodedData
     *
     * @return mixed
     */
    private function dumpEncodedData2Text($encodedData)
    {
        $array_base64_pattern = "/^YTo[A-Za-z0-9+\\/=]+/u";
        $object_base64_pattern = "/^Tzo[A-Za-z0-9+\\/=]+/u";
        $array_serialized_pattern = '/^a:\d+:{.+/u';
        $object_serialized_pattern = '/^O:\d+:".+/u';

        $tmp = $encodedData;

        switch (gettype($tmp)) {
            case "boolean":
                return $tmp ? "TRUE" : "FALSE";

            break;
            case "integer":
                return print_r($tmp, TRUE);

            break;
            case "double":
                return print_r($tmp, TRUE);

            break;
            case "string":
                if (preg_match($array_base64_pattern, $tmp) > 0 || preg_match($object_base64_pattern, $tmp) > 0)
                {
                    $tmp1 = @base64_decode($tmp);
                    if ($tmp1 !== FALSE)
                    {
                        $tmp = $tmp1;
                    }
                }
                if (preg_match($array_serialized_pattern, $tmp) > 0 || preg_match($object_serialized_pattern, $tmp) > 0)
                {
                    $tmp1 = @unserialize($tmp);
                    if ($tmp1 !== FALSE)
                    {
                        $tmp = $this->dumpEncodedData2Text($tmp1);
                    }
                }

                return $tmp;

            break;
            case "array":
                // When it's an array, try to decode recursively its values
                foreach ($tmp as $k => $v)
                {
                    $tmp[$k] = $this->dumpEncodedData2Text($v);
                }

                return print_r($tmp, TRUE);

            break;
            case "object":
                return print_r($tmp, TRUE);

            break;
            case "resource":
                return print_r($tmp, TRUE) . " resource";

            break;
            case "resource(closed)":
                return print_r($tmp, TRUE) . " resource(closed)";

            break;
            case "NULL":
                return "NULL";

            break;
            case "unknown type":
                return $tmp;

                break;
            default:
                return $tmp;

            break;
        }
    }
}
