<?php

/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */
class debugtoolsMessages
{
    /** array */
    public $text = [];

    public function __construct()
    {
        $domain = mb_strtolower(basename(__DIR__));
        
        $this->text = [
            "action" => dgettext($domain, "Action"),
            "backup" => dgettext($domain, "Backup"),
            "both" => dgettext($domain, "Both"),
            "catBrowse" => dgettext($domain, "Browse by category: "),
            "core" => dgettext($domain, "Core"),
            "decoded" => dgettext($domain, "Decoded"),
            "delete" => dgettext($domain, "Delete"),
            "deleteAllLink" => dgettext($domain, "Delete all session variables of the current user"),
            "deleteAllSessionVariable" => dgettext($domain, "All user session variables have been deleted"),
            "deleteSessionVariable" => dgettext($domain, "The Session variable have been deleted"),
            "destroyLink" => dgettext($domain, "Destroy the whole session of the current user"),
            "destroySession" => dgettext($domain, "The whole session have been destroyed"),
            "extension" => dngettext($domain, "Extension", "Extensions", 1),
            "field" => dgettext($domain, "Field"),
            "format" => dgettext($domain, "Format<br>Raw/Decoded"),
            "headingConfigApplication" => dgettext($domain, "Application Configuration (in DB)"),
            "headingConfigUser" => dgettext($domain, "User Configuration (in DB)"),
            "headingConstant" => dgettext($domain, "Constants Debug"),
            "headingCookie" => dgettext($domain, "Cookies Debug (\$_COOKIE)"),
            "headingEnvironment" => dgettext($domain, "Environment Debug"),
            "headingExtension" => dgettext($domain, "Extensions Debug"),
            "headingServer" => dgettext($domain, "Server superglobal Debug (\$_SERVER)"),
            "headingSession" => dgettext($domain, "Session Debug (\$_SESSION)"),
            "id" => dgettext($domain, "Id"),
            "keys" => dgettext($domain, "Keys"),
            "yes" => dgettext($domain, "Yes"),
            "no" => dgettext($domain, "No"),
            "optional" => dgettext($domain, "Optional"),
            "mandatory" => dgettext($domain, "Mandatory"),
            "loaded" => dgettext($domain, "Loaded"),
            "menu" => dgettext($domain, "Debug tools..."),
            "menuConfigApplication" => dgettext($domain, "App. Config."),
            "menuConfigUser" => dgettext($domain, "User Config."),
            "menuConstant" => dgettext($domain, "Constants"),
            "menuCookie" => dgettext($domain, "Cookies"),
            "menuEnvironment" => dgettext($domain, "Environment"),
            "menuExtension" => dngettext($domain, "Extension", "Extensions", 2),
            "menuServer" => dgettext($domain, "Server"),
            "menuSession" => dgettext($domain, "Session"),
            "raw" => dgettext($domain, "Raw"),
            "requirements" => dgettext($domain, "Requirements of WIKINDX"),
            "security" => dgettext($domain, "(This value is hidden for security)"),
            "value" => dngettext($domain, "Value", "Values", 1),
            "values" => dngettext($domain, "Value", "Values", 2),
        ];
    }
}
