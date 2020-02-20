<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */
class importexportbib_CONFIG
{
	/** float */
    public $wikindxVersion = 6;
}
class importexportbib_EXPORTCONFIG
{
	/** array */
    public $menus = ['plugin1'];
	/** int */
    public $authorize = 1;
}
class importexportbib_IMPORTCONFIG
{
	/** array */
    public $menus = ['plugin1'];
	/** int */
    public $authorize = 2;
    // Path to bibUtils (e.g. '/usr/bin/' for *NIX or
    // "D:/wamp/www/wikindx/bibutils/" for windows).
    // If this is FALSE, the plugin's export function
    // will assume *NIX and look by default in '/usr/local/bin/'.
    // Needs the trailing '/'
    public $bibutilsPath = FALSE;
}
class importexportbib_BIBUTILSCONFIG
{
	/** array */
    public $menus = ['plugin1'];
	/** int */
    public $authorize = 1;
    // Path to bibUtils (e.g. '/usr/bin/' for *NIX or
    // "D:/wamp/www/wikindx/bibutils/" for windows).
    // If this is FALSE, the plugin's export function
    // will assume *NIX and look by default in '/usr/local/bin/'.
    // Needs the trailing '/'
    public $bibutilsPath = FALSE;
}
