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
		$active = ['list_QUICKSEARCH_CORE'];
		if (WIKINDX_BROWSER_TAG_ID && array_key_exists('action', $this->vars) && in_array($this->vars['action'], $active)) {
			GLOBALS::addTplVar('scripts', '<script src="' . WIKINDX_URL_BASE . '/gatekeeper.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>');
			$script = WIKINDX_URL_BASE . '/gatekeeper.js?ver=' . WIKINDX_PUBLIC_VERSION;
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				unset($this->vars['submit']);
				foreach ($this->vars as $key => $value) {
					$qsArray[] = $key . '=' . $value;
				}
				$qs = '?' . join('&', $qsArray);
			} else {
				$qs = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : FALSE;
			}
			$url = WIKINDX_URL_BASE . '/index.php' . $qs;
			if (!array_key_exists('browserTabID', $this->vars)) {
			// go into gatekeeper for a redirect and addition of a browserTabID to the querystring
				$gatewayString = '<script>redirectSet("' . $url . '", "' . $qs . '")</script>';
			}
			else {
			// go into gatekeeper to check if browserTabID is unique to the tab (perhaps user has opened link with browserTabID in a new tab/window)
				$id = $this->vars['browserTabID'];
				$gatewayString = '<script>getBrowserTabID("' . $url . '", "' . $qs . '", "' . $id . '")</script>';
				GLOBALS::setBrowserTabID($id);
			}
			GLOBALS::addTplVar('scripts', "$gatewayString");
		}
	}
}