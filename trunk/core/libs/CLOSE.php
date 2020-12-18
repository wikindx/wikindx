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
 * Close WIKINDX tidily and print footer.
 *
 * @package wikindx\core\libs\CLOSE
 */
class CLOSE
{
    /** object */
    protected $db;
    /** object */
    protected $template;
    /** object */
    protected $messages;
    /** object */
    protected $session;


    /**
     * CLOSE
     *
     * @param bool $displayHeader default TRUE
     * @param bool $displayFooter default TRUE
     * @param bool $displayMenu default TRUE
     * @param bool $displayPopUp default FALSE
     */
    public function __construct($displayHeader = TRUE, $displayFooter = TRUE, $displayMenu = TRUE, $displayPopUp = FALSE)
    {
        $this->db = FACTORY_DB::getInstance();
        $this->session = FACTORY_SESSION::getInstance();

        $this->messages = FACTORY_MESSAGES::getInstance();
        $styles = \LOADSTYLE\loadDir();

        // Preparation of values
        $numberOfResources = $this->db->selectCountOnly("resource", "resourceId");

        // if this is a logged in user, display the username after the heading
        if ($userId = $this->session->getVar("setup_UserId"))
        {
            $this->db->formatConditions(['usersId' => $userId]);
            $usersUsername = \HTML\nlToHtml($this->db->selectFirstField('users', 'usersUsername'));
        }
        else
        {
            $usersUsername = '--';
        }

        // During setup, there are no default style configured in session
        // So, we take the default style
        $styleId = strtolower(GLOBALS::getUserVar("Style", WIKINDX_STYLE_DEFAULT));
        $styleName = array_key_exists($styleId, $styles) ? $styles[$styleId] : $styles[WIKINDX_STYLE_DEFAULT];

        if ($useBib = GLOBALS::getUserVar('BrowseBibliography'))
        {
            $this->db->formatConditions(['userbibliographyId' => $useBib]);
            $bib = \HTML\nlToHtml($this->db->selectFirstField('user_bibliography', 'userbibliographyTitle'));
        }
        else
        {
            $bib = $this->messages->text("user", "masterBib");
        }

        $footer['wikindxVersion'] = WIKINDX_PUBLIC_VERSION;
        $footer['numResources'] = $this->messages->text("footer", "resources") . "&nbsp;" . $numberOfResources;
        $footer['username'] = $this->messages->text("user", "username") . ":&nbsp;" . $usersUsername;
        $footer['bibliography'] = $this->messages->text("footer", "bib") . "&nbsp;" . $bib;
        $footer['style'] = $this->messages->text("footer", "style") . "&nbsp;" . $styleName;
        $footer['numQueries'] = $this->messages->text("footer", "queries") . "&nbsp;" . GLOBALS::getDbQueries();
        $footer['dbTime'] = $this->messages->text("footer", "dbtime") . "&nbsp;" . '%%DBTIMER%%' . "&nbsp;secs";
        $footer['scriptTime'] = $this->messages->text("footer", "execution") . "&nbsp;" . '%%SCRTIMER%%' . "&nbsp;secs";

        // Assigning values to the template and rendering
        $this->template = FACTORY_TEMPLATE::getInstance();
        $this->template->loadTemplate();

        GLOBALS::addTplVar('displayPopUp', $displayPopUp);
        
        // Extract and fix url separator for HTML rendering
        GLOBALS::addTplVar('tplPath', $this->template->getUrl());
        GLOBALS::addTplVar('lang', \LOCALES\localetoBCP47(\LOCALES\determine_locale()));
        if (defined('WIKINDX_RSS_ALLOW'))
        {
            GLOBALS::addTplVar('displayRss', WIKINDX_RSS_ALLOW);
            GLOBALS::addTplVar('rssTitle', WIKINDX_RSS_TITLE);
            GLOBALS::addTplVar('rssFeed', WIKINDX_URL_BASE . WIKINDX_RSS_PAGE);
        }
        else
        {
            GLOBALS::addTplVar('displayRss', FALSE);
        }
        // HEADERS
        GLOBALS::addTplVar('displayHeader', $displayHeader);

        // Check if this parameter exists because throws an error at install stage
        $title = \HTML\nlToHtml(defined('WIKINDX_TITLE') ? WIKINDX_TITLE : WIKINDX_TITLE_DEFAULT);

        GLOBALS::addTplVar('title', \HTML\stripHtml($title)); // Admins can add HTML formatting in the configure interface.
        GLOBALS::addTplVar('headTitle', $title);

        // Mandatory script for Ajax and core functions
        GLOBALS::addTplVar('scripts', '<script src="' . WIKINDX_URL_BASE . '/core/javascript/coreJavascript.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>');
        GLOBALS::addTplVar('scripts', '<script src="' . WIKINDX_URL_BASE . '/' . WIKINDX_URL_COMPONENT_VENDOR . '/progressbarjs/progressbar.min.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>');
        GLOBALS::addTplVar('scripts', '<script src="' . WIKINDX_URL_BASE . '/' . WIKINDX_URL_COMPONENT_VENDOR . '/jsonjs/json2.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>');
        GLOBALS::addTplVar('scripts', '<script src="' . WIKINDX_URL_BASE . '/core/javascript/ajax.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>');

        // MENU
        GLOBALS::addTplVar('displayMenu', $displayMenu);
        // If the menu is hidden, we can avoid to build it
        if ($displayMenu)
        {
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "navigation", "MENU.php"]));
            $menu = new MENU();
            $menu->menus();
        }

        $this->tidySession();

        // FOOTERS
        GLOBALS::addTplVar('displayFooter', $displayFooter);
        // Although the footer is hidden, we can't avoid to assign its variables
        // because someone can use them at an other place of his custom template
        GLOBALS::addTplVar("footerInfo", $footer);
        GLOBALS::addTplVar('wkx_link', WIKINDX_URL);
        GLOBALS::addTplVar('wkx_appname', 'WIKINDX');
        GLOBALS::addTplVar('wkx_mimetype_rss', WIKINDX_MIMETYPE_RSS);

        // Get the time elapsed before template rendering
        GLOBALS::stopPageTimer();
        $scriptExecutionTimeBeforeRendering = GLOBALS::getPageElapsedTime();

        // RENDER PAGE

        // Get the list of template variables defined in the global store
        $tplKeys = GLOBALS::getTplVarKeys();

        // Merge the list with some mandatories template variable names
        // We need absolutely these variables defined in the template for the placement of plugins.
        // This is one more thing for template designers.
        array_push($tplKeys, 'heading');
        array_push($tplKeys, 'scripts');
        array_push($tplKeys, 'menu');
        array_push($tplKeys, 'help');
        array_push($tplKeys, 'content');
        array_push($tplKeys, 'inline1');
        array_push($tplKeys, 'inline2');
        array_push($tplKeys, 'inline3');
        array_push($tplKeys, 'inline4');

        $debugLogSQLString = '';

        $tplKeys = array_unique($tplKeys);
        // Extract data of all template variables form the global store and give them to the template system
        foreach ($tplKeys as $k)
        {
            $tplVars = GLOBALS::getTplVar($k);
            $s = '';
            $t = NULL; // Type of the variable to give to the template

            foreach ($tplVars as $v)
            {
                // We have to assimile NULL to string because sometimes
                // this value can be inserted unintentionally if a variable is empty.
                // The same error happend with a mixture of string
                // and others type but this is obviously an error
                // and we raise an error in that case.
                if ($t == NULL)
                {
                    $t = (is_string($v) || $v == NULL) ? 's' : 'm';
                }

                if ($t == ((is_string($v) || $v == NULL) ? 's' : 'm'))
                {
                    if ($t == 's')
                    {
                        // Concat all strings data
                        if ($k == 'scripts')
                        {
                            $v .= LF;
                        }
                        $s .= $v;
                    }
                    else
                    {
                        // If multiple no-string data are defined,
                        // only the last defined will be assigned
                        // In this case there should be only one,
                        // but we have to protected againt a mistake
                        // to no break rendering arbitrarily
                        $s = $v;
                    }
                }
                else
                {
                    $errorMessage = "Mixed data type in '$k' template variable";

                    if (WIKINDX_DEBUG_ERRORS)
                    {
                        trigger_error($errorMessage, E_USER_ERROR);
                    }
                    else
                    {
                        $s = $errorMessage;
                    }

                    break;
                }
            }

            // logsql is a specific case : this variable is injected in all template,
            // just before the body end tag when the debug sql mode is ON.
            // We don't want to provide a way to template designer to disable it.
            if ($k != 'logsql')
            {
                $this->template->tpl->assign($k, $s);
            }
            else
            {
                $debugLogSQLString = $s;
            }

            GLOBALS::clearTplVar($k);
        }

        $this->template->tpl->display('display.tpl');

        // Get the time elapsed after template rendering
        // which is also the total time elapsed
        GLOBALS::stopPageTimer();
        $scriptExecutionTimeAfterRendering = GLOBALS::getPageElapsedTime();

        // Time elapsed in db queries
        $dbExecutionTime = GLOBALS::getDbTimeElapsed();

        // Time elapsed in data processing and internal logic (no template rendering and db query)
        $scriptElapsedTime = $scriptExecutionTimeBeforeRendering - $dbExecutionTime;

        // Time elapsed in template rendering
        $templateElapsedTime = $scriptExecutionTimeAfterRendering - $scriptExecutionTimeBeforeRendering;

        // Retrieve page code after rendering and insert public timers
        $outputString = ob_get_clean();
        $outputString = str_replace('%%DBTIMER%%', sprintf('%0.5f', $dbExecutionTime), $outputString);
        $outputString = str_replace('%%SCRTIMER%%', sprintf('%0.5f', $scriptExecutionTimeAfterRendering), $outputString);

        // Insert debug info only if we are on the main page
        if (mb_strripos(WIKINDX_DIR_COMPONENT_PLUGINS . DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_NAME']) === FALSE)
        {
            $debugString = '';
            // Insert SQL log
            if (defined("WIKINDX_DEBUG_SQL") && WIKINDX_DEBUG_SQL && in_array('logsql', $tplKeys))
            {
                $debugString .= $debugLogSQLString;
            }
            // Insert debug timers
            if (defined('WIKINDX_DEBUG_ERRORS') && WIKINDX_DEBUG_ERRORS)
            {
                $lineEnding = BR;
                $debugString .= "<p style='font-family: monospace; font-size: 8pt; text-align: right;'>" . LF;
                $debugString .= "PHP execution time: " . sprintf('%0.5f', $scriptElapsedTime) . " s$lineEnding";
                $debugString .= "SQL execution time: " . sprintf('%0.5f', $dbExecutionTime) . " s$lineEnding";
                $debugString .= "TPL rendering time: " . sprintf('%0.5f', $templateElapsedTime) . " s$lineEnding";
                $debugString .= "Total elapsed time: " . sprintf('%0.5f', $scriptExecutionTimeAfterRendering) . " s$lineEnding";
                $debugString .= "Peak memory usage: " . sprintf('%0.4f', memory_get_peak_usage() / 1048576) . " MB$lineEnding";
                $debugString .= "Memory at close: " . sprintf('%0.4f', memory_get_usage() / 1048576) . " MB";
                $debugString .= "\n</p>\n";
            }

            $debugString .= "</body>";

            $outputString = str_replace('</body>', $debugString, $outputString);
        }

        // Send page code to browser
        echo $outputString;

        // If this function have been called directly (not inherited), we must die
        if (__CLASS__ == get_called_class())
        {
            die;
        }
    }
    /**
     * tidySession
     *
     * A convenient place to clear certain session values which we definitely don't want the next time around
     */
    protected function tidySession()
    {
        $session = FACTORY_SESSION::getInstance();
        // This if TRUE is the last operation made use of LISTCOMMON::display()
        $session->delVar("list_On");
    }
}
/**
 * Close WIKINDX tidily (no menu - used for initial logon screen).
 *
 * @package wikindx\core\display
 */
class CLOSENOMENU extends CLOSE
{
    /**
     * CLOSENOMENU
     *
     * @param bool $displayHeader default TRUE
     * @param bool $displayFooter default TRUE
     * @param bool $displayMenu default FALSE
     * @param bool $displayPopUp default FALSE
     */
    public function __construct($displayHeader = TRUE, $displayFooter = TRUE, $displayMenu = FALSE, $displayPopUp = FALSE)
    {
        parent::__construct($displayHeader, $displayFooter, $displayMenu, $displayPopUp);
        die;
    }
}
/**
 * Close WIKINDX tidily.  Used for javascript pop-ups such as citation that don't require header, images, menus etc.
 *
 * @package wikindx\core\display
 */
class CLOSEPOPUP extends CLOSE
{
    /**
     * CLOSEPOPUP
     *
     * @param bool $displayHeader default TRUE
     * @param bool $displayFooter default FALSE
     * @param bool $displayMenu default FALSE
     * @param bool $displayPopUp default TRUE
     */
    public function __construct($displayHeader = TRUE, $displayFooter = FALSE, $displayMenu = FALSE, $displayPopUp = TRUE)
    {
        parent::__construct($displayHeader, $displayFooter, $displayMenu, $displayPopUp);
        die;
    }
}
/**
 * Close WIKINDX by simply printing GLOBALS::buildTplVarString('content') without any more content.  Typically used with AJAX to print
 * strings to a DIV within the WIKINDX page
 *
 * @package wikindx\core\display
 */
class CLOSERAW extends CLOSE
{
    /**
     * CLOSERAW
     */
    public function __construct()
    {
        // In this mode, we don't use template engine
        $this->tidySession();

        echo GLOBALS::buildTplVarString('content');

        if (ob_get_length() !== FALSE)
        {
            ob_end_flush();
        }
        die; // In this we always die to not had other content by error
    }
}
