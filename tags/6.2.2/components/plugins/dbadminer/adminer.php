<?php

/**
 * This class is a wrappper of Adminer main class.
 * cf. https://www.adminer.org/en/plugins/#use
 *
 * Its purpose is preloding the connection params of the Wikindx db except the password and adding some useful plugins.
 *
 * The class FillLoginForm is also overloaded because the original plugin is unfinished (only GET request).
 * cf. https://github.com/arxeiss/Adminer-FillLoginForm/blob/master/fill-login-form.php
 */
function adminer_object()
{
    include __DIR__ . "/../../../config.php";
    $wkx_config = new CONFIG;
    
    include_once __DIR__ . "/adminer-plugin.php";
    
    // autoloader
    foreach (glob("plugins/*.php") as $filename)
    {
        include_once "./$filename";
    }
    
    $plugins = [
        // specify enabled plugins here
        new FillLoginForm("server", $wkx_config->WIKINDX_DB_HOST, $wkx_config->WIKINDX_DB_USER, "", $wkx_config->WIKINDX_DB),
        new AdminerDumpAlter,
        new AdminerDumpBz2,
        new AdminerDumpDate,
        new AdminerDumpJson,
        new AdminerDumpPhp,
        new AdminerDumpXml,
        new AdminerDumpZip,
        new AdminerEditTextarea,
        new AdminerEditCalendar,
        new AdminerEditForeign,
        new AdminerJsonColumn,
        new AdminerTableStructure,
        new AdminerTableIndexesStructure,
        new AdminerVersionNoverify,
    ];
    
    
    class AdminerCustomization extends AdminerPlugin
    {
        public function credentials()
        {
            // server, username and password for connecting to database
            $wkx_config = new CONFIG;

            return [$wkx_config->WIKINDX_DB_HOST, $wkx_config->WIKINDX_DB_USER, $wkx_config->WIKINDX_DB_PASSWORD];
        }
        
        public function database()
        {
            // database name, will be escaped by Adminer
            $wkx_config = new CONFIG;

            return $wkx_config->WIKINDX_DB;
        }
        
        public function login($login, $password)
        {
            // validate user submitted credentials
            $wkx_config = new CONFIG;

            return ($login == $wkx_config->WIKINDX_DB_USER && $password == $wkx_config->WIKINDX_DB_PASSWORD && $_GET["db"] == $wkx_config->WIKINDX_DB);
        }
    }
    
    return new AdminerCustomization($plugins);
}

include_once __DIR__ . "/adminer-core.php";
