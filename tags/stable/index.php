<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * index.php
 *
 * Gateway to the system.  Here, the form variable 'action' is parsed to
 * determine the course of action.
 * All actions and requests pass via this gateway.
 *
 * @package wikindx
 */
 
/**
 * Import initial configuration and initialize the web server
 */
include_once("core/startup/WEBSERVERCONFIG.php");

/**
 *	First pass through authentication.
 */
// return from gatekeep is either TRUE (meaning proceed without change) or FALSE (meaning set $vars['action'] = "front')
// which will print the front page of WIKINDX.
// gatekeep() can itself exit the script (usually to the initial logon prompt when forbidding access)
if ($authorize->gateKeep() === FALSE)
{
    $vars['action'] = 'front';
}
// RSS feed access granted
if (array_key_exists('method', $vars) && ($vars['method'] == 'RSS'))
{
    unset($vars['method']);
}
// User bookmarks can only be added when looking at a single resource's details or resource lists.
// Set default behaviour here to remove 'add bookmark' link from menu
$session = FACTORY_SESSION::getInstance();
$session->setVar('bookmark_DisplayAdd', FALSE);
$session->delVar('bookmark_MultiView');
//print_r($_SESSION);
/**
 * If we get here, we're cleared to proceed.  If there is no $vars['action'], send users to the front of wikindx,
 * otherwise load the requested module.
 * WIKINDX has a modular structure.  If $vars['action'] has 'xxx_XXXXXX_CORE in it, it actions a core/modules/ module,
 * otherwise, we look in the WIKINDX_DIR_COMPONENT_PLUGINS folder for a third-party plug-in.
 */
$actionFound = FALSE;
if (!array_key_exists('action', $vars) || ($vars['action'] == 'front'))
{ // ready for displaying front page
    $message = FALSE;
    if (isset($upgradeCompleted))
    {
        if ($upgradeCompleted === TRUE)
        {
            include_once('core/startup/INSTALLMESSAGES.php');
            $installMessages = new INSTALLMESSAGES;
            $message = \HTML\p($installMessages->text("upgradeDBSuccess"), "success", "center");
            if (WIKINDX_INTERNAL_VERSION >= 5.3)
            {
                $message .= \HTML\p($installMessages->text("upgradeDBv5.3"), "success", "center");
            }
        }
    }
    else
    {
        $upgradeCompleted = FALSE;
    }

    /**
     * Do any housekeeping that is not part of database upgrading
     */
    if (!array_key_exists('action', $vars))
    {
        FACTORY_HOUSEKEEPING::getInstance($upgradeCompleted); // runs on autopilot
    }
    include_once("core/display/FRONT.php");
    $front = new FRONT($message); // __construct() runs on autopilot
    unset($front);
    $actionFound = TRUE;
}
// Display menu or submenu links as hyperlinks
elseif (array_key_exists('action', $vars) && ($vars['action'] == 'noMenu' || $vars['action'] == 'noSubMenu'))
{
    include_once('core/navigation/MENU.php');
    $menu = new MENU();
    $menu->{$vars['action']}();
    $actionFound = TRUE;
    unset($menu);
}
elseif (array_key_exists('action', $vars) && ($vars['action'] == 'skipCaching'))
{
    include_once("core/display/FRONT.php");
    $front = new FRONT(); // __construct() runs on autopilot
    unset($front);
    $actionFound = TRUE;
}
/**
 * Core wikindx modules have the querystring 'action' as <directory>_<FILE>_CORE the assumption being that <directory>
 * is under 'core/modules/'.
 * <FILE> could be of the form <FILE::CLASS> to allow for multiple classes (e.g. inheritance) in the one file.
 * The optional querystring 'method' defines the class method to use -- if not present, the init() class method is the default
 */
else
{
    $split = UTF8::mb_explode("_", $vars["action"]);
    if ((count($split) == 3) && ($split[2] == 'CORE'))
    { // this is a core module
        if (count($fileSplit = UTF8::mb_explode('::', $split[1])) == 2)
        { // file and named class in that file
            $file = "core" . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . $split[0] . DIRECTORY_SEPARATOR . $fileSplit[0] . '.php';
            $class = $fileSplit[1];
        }
        else
        {
            $file = "core" . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . $split[0] . DIRECTORY_SEPARATOR . $split[1] . '.php';
            $class = $split[1];
        }
        if (file_exists($file))
        {
            include_once($file);
            $obj = new $class();
            $method = array_key_exists('method', $vars) ? $vars['method'] : 'init';
            if (method_exists($obj, $method))
            {
                $obj->{$method}();
                $actionFound = TRUE;
                unset($obj);
            }
        }
    }
}

// third-party plug-in?
if (!$actionFound)
{
    // To guarantee that the action label is not the same as a core one, the module folder name is prepended to the
    // given action (e.g. folderName_actionName). We must remove the prepend.
    $split = UTF8::mb_explode("_", $vars["action"]);
    if (count($split) == 2)
    {
        $index = WIKINDX_DIR_COMPONENT_PLUGINS . DIRECTORY_SEPARATOR . $split[0] . '/index.php';
        if (file_exists($index))
        {
            include_once($index);
            // class name must be in the form $dirName . _MODULE
            $class = $split[0] . "_MODULE";
            $obj = new $class(FALSE);
            $method = $split[1];
            if (method_exists($obj, $method))
            {
                $obj->{$method}();
                $actionFound = TRUE;
                unset($obj);
            }
        }
    }
}
if (!$actionFound)
{
    // We've finally come to a dead end.....
    $errors = FACTORY_ERRORS::getInstance();
    GLOBALS::addTplVar('content', $errors->text("inputError", "invalid"));
}
// Store up to 10 of the last REQUEST_URIs
if ($_SERVER['REQUEST_URI'])
{
    $qs = $session->getArray('QueryStrings');
    if (count($qs) > 0)
    {
        if ($_SERVER['REQUEST_URI'] != $qs[0])
        {
            if (count($qs) > 10)
            {
                $qs = array_slice($qs, 0, 9);
                $session->clearArray('QueryStrings');
            }
        }
        array_unshift($qs, $_SERVER['REQUEST_URI']);
    }
    else
    {
        $qs = [];
        $qs[0] = $_SERVER['REQUEST_URI'];
    }
    $session->writeArray($qs, 'QueryStrings');
    $session->saveState('QueryStrings');
}
unset($session);

/**
 *	Close the HTML code by calling the constructor of CLOSE which also
 *	prints the HTTP header, body, menus, footer, flushes the print buffer, closes the database and exits the script.
 */
FACTORY_CLOSE::getInstance();
