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
 * BROWSERTABID
 *
 * Generate the javascript for the BROWSERTABID
 *
 * @package wikindx\core\startup
 */
class BROWSERTABID
{
    /** array */
    private $vars;
    
    public function __construct()
    {
        $this->vars = GLOBALS::getVars();
    }
    /**
     * Generate/return the unique browser tab/window identifier for specific scripts
     */
    public function js()
    {
        $active = ['list_QUICKSEARCH_CORE', 'resource_RESOURCEVIEW_CORE', 'admin_DELETERESOURCE_CORE', 'list_LISTRESOURCES_CORE', 
        	'list_LISTSOMERESOURCES_CORE', 'basket_BASKET_CORE', 'attachments_ATTACHMENTS_CORE', 'resource_RESOURCECATEGORYEDIT_CORE', 
        	'urls_URLS_CORE', 'resource_RESOURCEQUOTE_CORE', 'resource_RESOURCEPARAPHRASE_CORE', 'resource_RESOURCEMUSING_CORE', 
        	'metadata_EDITMETADATA_CORE', 'resource_RESOURCEFORM_CORE', 'list_LISTADDTO_CORE', 'list_SEARCH_CORE', 
        	'admin_QUARANTINE_CORE'];
        $tempSession = [];
        if (WIKINDX_BROWSER_TAB_ID && 
        	((array_key_exists('action', $this->vars) && in_array($this->vars['action'], $active)) || !array_key_exists('action', $this->vars)))
        {
            GLOBALS::addTplVar('scripts', '<script src="' . WIKINDX_URL_BASE . '/core/javascript/gatekeeper.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>');
            if ($_SERVER['REQUEST_METHOD'] == 'POST')
            {
                foreach ($this->vars as $key => $value)
                {
                    if (($key != 'method') && ($key != 'action') && ($key != 'browserTabID') && ($key != 'submit'))
                    {
                        $tempSession[$key] = $value;

                        continue;
                    }
                    $qsArray[] = $key . '=' . $value;
                }
                $qs = '?' . join('&', $qsArray);
            }
            else
            {
                $qs = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : FALSE;
            }
            $url = WIKINDX_URL_BASE . '/index.php' . $qs;
            if (!array_key_exists('browserTabID', $this->vars) || !$this->vars['browserTabID'])
            {
                if (!empty($tempSession))
                {
                    $session = FACTORY_SESSION::getInstance();
                    $session->writeArray($tempSession, 'tempTab');
                }
                // go into gatekeeper for a redirect and addition of a browserTabID to the querystring
                $gatewayString = '<script>redirectSet("' . $url . '", "' . $qs . '")</script>';
            }
            else
            {
                // go into gatekeeper to check if browserTabID is unique to the tab (perhaps user has opened link with browserTabID in a new tab/window)
                $id = $this->vars['browserTabID'];
                $gatewayString = '<script>getBrowserTabID("' . $url . '", "' . $qs . '", "' . $id . '")</script>';
                GLOBALS::setBrowserTabID($id);
            }
            GLOBALS::addTplVar('scripts', "$gatewayString");
        }
    }
}
