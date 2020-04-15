<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 *	CONFIGDBSTRUCTURE
 *
 *	Map out the structure of the config table
 *
 * @package wikindx\core\startup
 */
class CONFIGDBSTRUCTURE
{
    /** array */
    public $dbStructure;
    /** array */
    public $configToConstant;
    /** object */
    private $db;

    /**
     *	CONFIGDBSTRUCTURE
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $arrayVarchar = [
            'configAuthGateMessage',
            'configCmsBibstyle',
            'configCmsDbPassword',
            'configCmsDbUser',
            'configContactEmail',
            'configEmailNewRegistrations',
            'configLanguage',
            'configLastChangesType',
            'configLdapDn',
            'configLdapServer',
            'configMailBackend',
            'configMailFrom',
            'configMailReplyTo',
            'configMailReturnPath',
            'configMailSmPath',
            'configMailSmtpEncrypt',
            'configMailSmtpPassword',
            'configMailSmtpServer',
            'configMailSmtpUsername',
            'configPasswordStrength',
            'configRssBibstyle',
            'configRssDescription',
            'configRssTitle',
            'configSqlErrorOutput',
            'configStyle',
            'configTagHighColour',
            'configTagLowColour',
            'configTemplate',
            'configTitle',
        ];
        $arrayInt = [
            'configFileDeleteSeconds',
            'configImagesMaxSize',
            'configImgHeightLimit',
            'configImgWidthLimit',
            'configLastChanges',
            'configLastChangesDayLimit',
            'configLdapPort',
            'configLdapProtocolVersion',
            'configMailSmtpPort',
            'configMaxPaste',
            'configPaging',
            'configPagingMaxLinks',
            'configPagingTagCloud',
            'configPasswordSize',
            'configRestrictUserId',
            'configRssLimit',
            'configStringLimit',
            'configTagHighFactor',
            'configTagLowFactor',
        ];
        $arrayBoolean = [
            'configAuthGate',
            'configBypassSmartyCompile',
            'configCmsAllow',
            'configCmsSql',
            'configDebugEmail',
            'configDenyReadOnly',
            'configDisplayStatistics',
            'configDisplayUserStatistics',
            'configEmailNews',
            'configEmailStatistics',
            'configErrorReport',
            'configFileAttach',
            'configFileViewLoggedOnOnly',
            'configGlobalEdit',
            'configGsAllow',
            'configGsAttachment',
            'configImagesAllow',
            'configImportBib',
            'configLdapUse',
            'configListLink',
            'configMailServer',
            'configMailSmtpAuth',
            'configMailSmtpPersist',
            'configMetadataAllow',
            'configMetadataUserOnly',
            'configMultiUser',
            'configNotify',
            'configOriginatorEditOnly',
            'configPrintSql',
            'configQuarantine',
            'configReadOnlyAccess',
            'configRssAllow',
            'configRssDisplay',
            'configSiteMapAllow',
            'configUserRegistration',
            'configUserRegistrationModerate',
        ];
        $arrayDatetime = [
            'configStatisticsCompiled',
        ];
        $arrayText = [
            'configDeactivateResourceTypes',
            'configDescription',
            'configNoSort',
            'configSearchFilter',
            'configTimezone',
        ];

        foreach ($arrayVarchar as $name)
        {
            $this->dbStructure[$name] = 'configVarchar';
        }
        foreach ($arrayInt as $name)
        {
            $this->dbStructure[$name] = 'configInt';
        }
        foreach ($arrayBoolean as $name)
        {
            $this->dbStructure[$name] = 'configBoolean';
        }
        foreach ($arrayDatetime as $name)
        {
            $this->dbStructure[$name] = 'configDatetime';
        }
        foreach ($arrayText as $name)
        {
            $this->dbStructure[$name] = 'configText';
        }

        $this->configToConstant = [
            'configAuthGate' => 'WIKINDX_AUTHGATE_USE',
            'configAuthGateMessage' => 'WIKINDX_AUTHGATE_MESSAGE',
            'configBypassSmartyCompile' => 'WIKINDX_BYPASS_SMARTYCOMPILE',
            'configCmsAllow' => 'WIKINDX_CMS_ALLOW',
            'configCmsBibstyle' => 'WIKINDX_CMS_BIBSTYLE',
            'configCmsDbPassword' => 'WIKINDX_CMS_DB_PASSWORD',
            'configCmsDbUser' => 'WIKINDX_CMS_DB_USER',
            'configCmsSql' => 'WIKINDX_CMS_SQL',
            'configContactEmail' => 'WIKINDX_CONTACT_EMAIL',
            'configDeactivateResourceTypes' => 'WIKINDX_DEACTIVATE_RESOURCE_TYPES',
            'configDenyReadOnly' => 'WIKINDX_DENY_READONLY',
            'configDescription' => 'WIKINDX_DESCRIPTION',
            'configDisplayStatistics' => 'WIKINDX_DISPLAY_STATISTICS',
            'configDisplayUserStatistics' => 'WIKINDX_DISPLAY_USER_STATISTICS',
            'configEmailNewRegistrations' => 'WIKINDX_EMAIL_NEWREGISTRATIONS',
            'configEmailNews' => 'WIKINDX_EMAIL_NEWS',
            'configEmailStatistics' => 'WIKINDX_EMAIL_STATISTICS',
            'configErrorReport' => 'WIKINDX_DEBUG_ERRORS',
            'configFileAttach' => 'WIKINDX_FILE_ATTACH',
            'configFileDeleteSeconds' => 'WIKINDX_FILE_DELETESECONDS',
            'configFileViewLoggedOnOnly' => 'WIKINDX_FILE_VIEWLOGGEDONONLY',
            'configGlobalEdit' => 'WIKINDX_GLOBAL_EDIT',
            'configGsAllow' => 'WIKINDX_GS_ALLOW',
            'configGsAttachment' => 'WIKINDX_GS_ATTACHMENT',
            'configImagesAllow' => 'WIKINDX_IMAGES_ALLOW',
            'configImagesMaxSize' => 'WIKINDX_IMAGES_MAXSIZE',
            'configImgHeightLimit' => 'WIKINDX_IMG_HEIGHTLIMIT',
            'configImgWidthLimit' => 'WIKINDX_IMG_WIDTHLIMIT',
            'configImportBib' => 'WIKINDX_IMPORTBIB',
            'configLanguage' => 'WIKINDX_LANGUAGE', // These is also a user variable needed for the logon page where the defaults are required.
            'configLastChanges' => 'WIKINDX_LASTCHANGES',
            'configLastChangesDayLimit' => 'WIKINDX_LASTCHANGESDAYLIMIT',
            'configLastChangesType' => 'WIKINDX_LASTCHANGESTYPE',
            'configLdapDn' => 'WIKINDX_LDAP_DN',
            'configLdapPort' => 'WIKINDX_LDAP_PORT',
            'configLdapProtocolVersion' => 'WIKINDX_LDAP_PROTOCOL_VERSION',
            'configLdapServer' => 'WIKINDX_LDAP_SERVER',
            'configLdapUse' => 'WIKINDX_LDAP_USE',
            'configListLink' => 'WIKINDX_LISTLINK',
            'configMailBackend' => 'WIKINDX_MAIL_BACKEND',
            'configMailFrom' => 'WIKINDX_MAIL_FROM',
            'configMailReplyTo' => 'WIKINDX_MAIL_REPLYTO',
            'configMailReturnPath' => 'WIKINDX_MAIL_RETURN_PATH',
            'configMailServer' => 'WIKINDX_MAIL_SERVER',
            'configMailSmPath' => 'WIKINDX_MAIL_SMPATH',
            'configMailSmtpAuth' => 'WIKINDX_MAIL_SMTPAUTH',
            'configMailSmtpEncrypt' => 'WIKINDX_MAIL_SMTPENCRYPT',
            'configMailSmtpPassword' => 'WIKINDX_MAIL_SMTPPASSWORD',
            'configMailSmtpPersist' => 'WIKINDX_MAIL_SMTPPERSIST',
            'configMailSmtpPort' => 'WIKINDX_MAIL_SMTPPORT',
            'configMailSmtpServer' => 'WIKINDX_MAIL_SMTPSERVER',
            'configMailSmtpUsername' => 'WIKINDX_MAIL_SMTPUSERNAME',
            'configMaxPaste' => 'WIKINDX_MAXPASTE',
            'configMetadataAllow' => 'WIKINDX_METADATA_ALLOW',
            'configMetadataUserOnly' => 'WIKINDX_METADATA_USERONLY',
            'configMultiUser' => 'WIKINDX_MULTIUSER',
            'configNoSort' => 'WIKINDX_NOSORT',
            'configNotify' => 'WIKINDX_NOTIFY',
            'configOriginatorEditOnly' => 'WIKINDX_ORIGINATOR_EDITONLY',
            'configPaging' => 'WIKINDX_PAGING', // This is a user variables now found in GLOBALS::getUserVar()
            'configPagingMaxLinks' => 'WIKINDX_PAGING_MAXLINKS', // This is a user variables now found in GLOBALS::getUserVar()
            'configPagingTagCloud' => 'WIKINDX_PAGINGTAGCLOUD',
            'configPasswordSize' => 'WIKINDX_PASSWORDSIZE',
            'configPasswordStrength' => 'WIKINDX_PASSWORDSTRENGTH',
            'configPrintSql' => 'WIKINDX_DEBUG_SQL',
            'configQuarantine' => 'WIKINDX_QUARANTINE',
            'configReadOnlyAccess' => 'WIKINDX_READONLYACCESS',
            'configRestrictUserId' => 'WIKINDX_RESTRICT_USERID',
            'configRssAllow' => 'WIKINDX_RSS_ALLOW',
            'configSiteMapAllow' => 'WIKINDX_SITEMAP_ALLOW',
            'configRssBibstyle' => 'WIKINDX_RSS_BIBSTYLE',
            'configRssDescription' => 'WIKINDX_RSS_DESCRIPTION',
            'configRssDisplay' => 'WIKINDX_RSS_DISPLAY',
            'configRssLimit' => 'WIKINDX_RSS_LIMIT',
            'configRssTitle' => 'WIKINDX_RSS_TITLE',
            'configSearchFilter' => 'WIKINDX_SEARCHFILTER',
            'configDebugEmail' => 'WIKINDX_DEBUG_EMAIL',
            'configSqlErrorOutput' => 'WIKINDX_DEBUG_SQLERROROUTPUT',
            'configStatisticsCompiled' => 'WIKINDX_STATISTICSCOMPILED',
            'configStringLimit' => 'WIKINDX_STRINGLIMIT', // This is a user variables now found in GLOBALS::getUserVar()
            'configStyle' => 'WIKINDX_STYLE', // This is a user variables now found in GLOBALS::getUserVar()
            'configTagHighColour' => 'WIKINDX_TAG_HIGH_COLOUR',
            'configTagHighFactor' => 'WIKINDX_TAG_HIGH_FACTOR',
            'configTagLowColour' => 'WIKINDX_TAG_LOW_COLOUR',
            'configTagLowFactor' => 'WIKINDX_TAG_LOW_FACTOR',
            'configTemplate' => 'WIKINDX_TEMPLATE', // These is also a user variable needed for the logon page where the defaults are required.
            'configTimezone' => 'WIKINDX_TIMEZONE',
            'configTitle' => 'WIKINDX_TITLE',
            'configUserRegistration' => 'WIKINDX_USERREGISTRATION',
            'configUserRegistrationModerate' => 'WIKINDX_USERREGISTRATIONMODERATE',
        ];
    }
    /**
     * Get and return one value from the config table.
     *
     * Requested value must be a configName value that is in $this->dbStructure. For other values, use standard $db functions.
     *
     * Result is returned as a number if the value is stored in configInt columns else the return result is a string or a boolean
     *
     * @param string $field – the table column to match the condition.
     *
     * @return String|float|bool
     */
    public function getOne($field)
    {
        $field = (string)$field;
        if (!array_key_exists($field, $this->dbStructure))
        {
            die('Supply a configuration variable to search on');
        }
        $column = $this->dbStructure[$field];
        $this->db->formatConditions(['configName' => $field]);
        $value = $this->db->fetchOne($this->db->select('config', $column));

        return $this->convertVarDB2PHP($column, $value);
    }
    /**
     * Get all data from the config table and return an array of ($field => 'value')
     *
     * @return array
     */
    public function getAllData()
    {
        $field = 'configName';
        $row = [];
        $resultSet = $this->db->select('config', '*');
        while ($coRow = $this->db->fetchRow($resultSet))
        {
            // NB we grab only basic configuration variables – extra rows are added e.g. by localeDescription plugin
            if (array_key_exists($coRow[$field], $this->dbStructure))
            {
                $row[$coRow[$field]] = $this->convertVarDB2PHP($this->dbStructure[$coRow[$field]], $coRow[$this->dbStructure[$coRow[$field]]]);
            }
        }

        return $row;
    }
    /**
     * Get data from the config table for specific variables and return an array of ($field => 'value')
     *
     * @param mixed $match is the name of a variable or an array or variable names : array('var1, 'var2', ...).
     *
     * @return array
     */
    public function getData($match)
    {
        $field = 'configName';
        $row = [];
        if (!is_array($match))
        {
            $match = [$match];
        }
        $this->db->formatConditionsOneField($match, $field);
        $resultSet = $this->db->select('config', '*');
        while ($coRow = $this->db->fetchRow($resultSet))
        {
            // NB we grab only basic configuration variables – extra rows are added e.g. by localeDescription plugin
            if (array_key_exists($coRow[$field], $this->dbStructure))
            {
                $row[$coRow[$field]] = $this->convertVarDB2PHP($this->dbStructure[$coRow[$field]], $coRow[$this->dbStructure[$coRow[$field]]]);
            }
            else
            {
                die("CONFIGDBSTRUCTURE->getData(): bad config option name requested: " . $coRow[$field]);
            }
        }
        
        // During and installation the config table is not initialized before this function is called,
        // so return default values in that case
        if (count($row) == 0)
        {
            foreach($match as $configName)
            {
                $constName = $this->configToConstant[$configName];
                $value = constant($constName . "_DEFAULT");
                
                // Unserialize some options
                if (in_array($configName, ['configNoSort', 'configSearchFilter', 'configDeactivateResourceTypes']))
                {
                    $value = unserialize(base64_decode($value));
                }
                
                $row[$configName] = $value;
            }
        }

        return $row;
    }
    /**
     * Update one value in the config table.
     *
     * @param string $name in the 'configName' column (i.e. which configuration variable to update)
     * @param mixed $value to set
     */
    public function updateOne($name, $value)
    {
        $name = (string)$name;
        if (!array_key_exists($name, $this->dbStructure))
        {
            die('Supply a configuration variable to update');
        }
        $value = $this->convertVarPHP2DB($this->dbStructure[$name], $value);
        $this->db->formatConditions(['configName' => $name]);
        $this->db->update('config', [$this->dbStructure[$name] => $value]);
    }
    /**
     * Convert a value from Wikindx database format to PHP format
     *
     * @param string $configType can be: configVarchar, configInt, configBoolean, configDatetime, or configText
     * @param mixed $value to convert
     *
     * @return mixed The value converted
     */
    private function convertVarDB2PHP($configType, $value)
    {
        switch ($configType)
        {
            // Cast to integer number
            case 'configInt':
                $value = (int)$value;

            break;
            // return boolean (stored as 0 or 1 in the db table)
            case 'configBoolean':
                $value = $value ? TRUE : FALSE;

            break;
        }

        return $value;
    }
    /**
     * Convert a value from PHP format to Wikindx database format
     *
     * @param string $configType can be: configVarchar, configInt, configBoolean, configDatetime, or configText
     * @param mixed $value to convert
     *
     * @return mixed The value converted
     */
    private function convertVarPHP2DB($configType, $value)
    {
        switch ($configType)
        {
            // Cast to integer number
            case 'configInt':
                $value = (string)$value;

            break;
            // return boolean (stored as 0 or 1 in the db table)
            case 'configBoolean':
                $value = $value ? 1 : 0;

            break;
        }

        return $value;
    }
}