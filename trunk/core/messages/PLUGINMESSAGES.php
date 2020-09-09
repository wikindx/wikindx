<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

/**
* PLUGINMESSAGES
*
* Handle plugin localizations
*
* @package wikindx\core\messages
*
*/
class PLUGINMESSAGES
{
/** array */
private $catalogLanguage = array();

/**
 * PLUGINMESSAGES
 *
 * @param string $pluginDir
 * @param string $pluginFile
 */
	public function __construct($pluginDir, $pluginFile)
	{
		$session = FACTORY_SESSION::getInstance();
		
		$catalogData = array();
		$catalogFile = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS, $pluginDir, $pluginFile . ".php"]);
		$catalogClassName = $pluginFile;
		
		if(file_exists($catalogFile))
		{
			include_once($catalogFile);
			
			$catalogClass = new $catalogClassName;
			$catalogData = $catalogClass->text;
			$catalogClass = NULL;
		}
		
		$this->catalogLanguage = $catalogData;
	}
/**
* Grab the localized message
*
* @param string $indexName
* @param string $extra Optional string that replaces '###' in the array element value string. Default is ""
*
* @return string
*/
	public function text($indexName, $extra = "")
	{
		if(!array_key_exists($indexName, $this->catalogLanguage))
			die("<p>Message <strong>$indexName</strong> not found in translations.</p>");
		
		$message = $this->catalogLanguage[$indexName];
		$message = preg_replace("/###/u", str_replace("\\", "\\\\", trim($extra . "")), $message);
		return UTF8::html_uentity_decode($message);
	}
}
