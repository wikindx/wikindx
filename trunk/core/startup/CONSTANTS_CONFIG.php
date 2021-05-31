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
 * CONSTANTS_CONFIG
 *
 * Define a mapping between the name of global options in db and their PHP constant counterpart.
 *
 * "option db name" => ["constname" => "constant option name", "type" => "db column name"]"
 *
 * db column names could be :
 *  - configBoolean
 *  - configDatetime
 *  - configInt
 *  - configText
 *  - configVarchar
 *
 * @name WIKINDX_LIST_CONFIG_OPTIONS
 *
 * @package wikindx\core\startup
 */
define('WIKINDX_LIST_CONFIG_OPTIONS', [
    "configAuthGate"                  => ["constname" => "WIKINDX_AUTHGATE_RESET",                "type" => "configBoolean"],
    "configAuthGateMessage"           => ["constname" => "WIKINDX_AUTHGATE_MESSAGE",              "type" => "configVarchar"],
    "configBinFolderBibutils"         => ["constname" => "WIKINDX_BIN_FOLDER_BIBUTILS",           "type" => "configVarchar"],
    "configBinFolderCatdvi"           => ["constname" => "WIKINDX_BIN_FOLDER_CATDVI",             "type" => "configVarchar"],
    "configBinFolderDjvutxt"          => ["constname" => "WIKINDX_BIN_FOLDER_DJVUTXT",            "type" => "configVarchar"],
    "configBinFolderPs2pdf"           => ["constname" => "WIKINDX_BIN_FOLDER_PS2PDF",             "type" => "configVarchar"],
    "configBrowserTabID"              => ["constname" => "WIKINDX_BROWSER_TAB_ID",                "type" => "configBoolean"],
    "configBypassSmartyCompile"       => ["constname" => "WIKINDX_BYPASS_SMARTY_COMPILATION",     "type" => "configBoolean"],
    "configCategoryEditAllow"         => ["constname" => "WIKINDX_CATEGORYEDIT_ALLOW",      	  "type" => "configBoolean"],
    "configCmsAllow"                  => ["constname" => "WIKINDX_CMS_ALLOW",                     "type" => "configBoolean"],
    "configCmsBibstyle"               => ["constname" => "WIKINDX_CMS_BIBSTYLE",                  "type" => "configVarchar"],
    "configCmsDbPassword"             => ["constname" => "WIKINDX_CMS_DB_PASSWORD",               "type" => "configVarchar"],
    "configCmsDbUser"                 => ["constname" => "WIKINDX_CMS_DB_USER",                   "type" => "configVarchar"],
    "configCmsSql"                    => ["constname" => "WIKINDX_CMS_SQL",                       "type" => "configBoolean"],
    "configContactEmail"              => ["constname" => "WIKINDX_CONTACT_EMAIL",                 "type" => "configVarchar"],
    "configDeactivateResourceTypes"   => ["constname" => "WIKINDX_DEACTIVATE_RESOURCE_TYPES",     "type" => "configText"],
    "configDebugSql"                  => ["constname" => "WIKINDX_DEBUG_SQL",                     "type" => "configBoolean"],
    "configDenyReadOnly"              => ["constname" => "WIKINDX_DENY_READONLY",                 "type" => "configBoolean"],
    "configDescription"               => ["constname" => "WIKINDX_DESCRIPTION",                   "type" => "configText"],
    "configDisplayStatistics"         => ["constname" => "WIKINDX_DISPLAY_STATISTICS",            "type" => "configBoolean"],
    "configDisplayUserStatistics"     => ["constname" => "WIKINDX_DISPLAY_USER_STATISTICS",       "type" => "configBoolean"],
    "configEmailNewRegistrations"     => ["constname" => "WIKINDX_EMAIL_NEW_REGISTRATIONS",       "type" => "configVarchar"],
    "configEmailNews"                 => ["constname" => "WIKINDX_EMAIL_NEWS",                    "type" => "configBoolean"],
    "configEmailStatistics"           => ["constname" => "WIKINDX_EMAIL_STATISTICS",              "type" => "configBoolean"],
    "configErrorReport"               => ["constname" => "WIKINDX_DEBUG_ERRORS",                  "type" => "configBoolean"],
    "configFileAttachAllow"           => ["constname" => "WIKINDX_FILE_ATTACH_ALLOW",             "type" => "configBoolean"],
    "configFileAttachUploadMaxSize"   => ["constname" => "WIKINDX_FILE_ATTACH_UPLOAD_MAX_SIZE",   "type" => "configInt"],
    "configFileDeleteSeconds"         => ["constname" => "WIKINDX_FILE_DELETE_SECONDS",           "type" => "configInt"],
    "configFileViewLoggedOnOnly"      => ["constname" => "WIKINDX_FILE_VIEW_LOGGEDON_ONLY",       "type" => "configBoolean"],
    "configGlobalEdit"                => ["constname" => "WIKINDX_GLOBAL_EDIT",                   "type" => "configBoolean"],
    "configGsAttachment"              => ["constname" => "WIKINDX_GS_ATTACHMENT",                 "type" => "configBoolean"],
    "configGsDisallow"                => ["constname" => "WIKINDX_GS_DISALLOW",                   "type" => "configBoolean"],
    "configImgAllow"                  => ["constname" => "WIKINDX_IMG_ALLOW",                     "type" => "configBoolean"],
    "configImgHeightLimit"            => ["constname" => "WIKINDX_IMG_HEIGHT_LIMIT",              "type" => "configInt"],
    "configImgUploadMaxSize"          => ["constname" => "WIKINDX_IMG_UPLOAD_MAX_SIZE",           "type" => "configInt"],
    "configImgWidthLimit"             => ["constname" => "WIKINDX_IMG_WIDTH_LIMIT",               "type" => "configInt"],
    "configImportBib"                 => ["constname" => "WIKINDX_IMPORT_BIB",                    "type" => "configBoolean"],
    "configImpressum"				  => ["constname" => "WIKINDX_IMPRESSUM",            		  "type" => "configText"],
    "configIsTrunk"                   => ["constname" => "WIKINDX_IS_TRUNK",                      "type" => "configBoolean"],
    "configLanguage"                  => ["constname" => "WIKINDX_LANGUAGE",                      "type" => "configVarchar"],
    "configLastChanges"               => ["constname" => "WIKINDX_LAST_CHANGES",                  "type" => "configInt"],
    "configLastChangesDayLimit"       => ["constname" => "WIKINDX_LAST_CHANGES_DAY_LIMIT",        "type" => "configInt"],
    "configLastChangesType"           => ["constname" => "WIKINDX_LAST_CHANGES_TYPE",             "type" => "configVarchar"],
    "configLdapGroupDn"               => ["constname" => "WIKINDX_LDAP_GROUP_DN",                 "type" => "configVarchar"],
    "configLdapPort"                  => ["constname" => "WIKINDX_LDAP_PORT",                     "type" => "configInt"],
    "configLdapSearchMethod"          => ["constname" => "WIKINDX_LDAP_SEARCH_METHOD",            "type" => "configVarchar"],
    "configLdapSearchOperator"        => ["constname" => "WIKINDX_LDAP_SEARCH_OPERATOR",          "type" => "configVarchar"],
    "configLdapServer"                => ["constname" => "WIKINDX_LDAP_SERVER",                   "type" => "configVarchar"],
    "configLdapServerBindDomain"      => ["constname" => "WIKINDX_LDAP_SERVER_BIND_DOMAIN",       "type" => "configVarchar"],
    "configLdapServerBindDomainFormat"=> ["constname" => "WIKINDX_LDAP_SERVER_BIND_DOMAIN_FORMAT","type" => "configVarchar"],
    "configLdapServerBindLogin"       => ["constname" => "WIKINDX_LDAP_SERVER_BIND_LOGIN",        "type" => "configVarchar"],
    "configLdapServerBindPassword"    => ["constname" => "WIKINDX_LDAP_SERVER_BIND_PASSWORD",     "type" => "configVarchar"],
    "configLdapServerBindType"        => ["constname" => "WIKINDX_LDAP_SERVER_BIND_TYPE",         "type" => "configVarchar"],
    "configLdapServerEncryption"      => ["constname" => "WIKINDX_LDAP_SERVER_ENCRYPTION",        "type" => "configVarchar"],
    "configLdapUse"                   => ["constname" => "WIKINDX_LDAP_USE",                      "type" => "configBoolean"],
    "configLdapUserAttributLogin"     => ["constname" => "WIKINDX_LDAP_USER_ATTRIBUTE_LOGIN",     "type" => "configVarchar"],
    "configLdapUserCreate"            => ["constname" => "WIKINDX_LDAP_USER_CREATE",              "type" => "configBoolean"],
    "configLdapUserOu"                => ["constname" => "WIKINDX_LDAP_USER_OU",                  "type" => "configVarchar"],
    "configListLink"                  => ["constname" => "WIKINDX_LIST_LINK",                     "type" => "configBoolean"],
    "configMailBackend"               => ["constname" => "WIKINDX_MAIL_BACKEND",                  "type" => "configVarchar"],
    "configMailFrom"                  => ["constname" => "WIKINDX_MAIL_FROM",                     "type" => "configVarchar"],
    "configMailReplyTo"               => ["constname" => "WIKINDX_MAIL_REPLYTO",                  "type" => "configVarchar"],
    "configMailReturnPath"            => ["constname" => "WIKINDX_MAIL_RETURN_PATH",              "type" => "configVarchar"],
    "configMailSmPath"                => ["constname" => "WIKINDX_MAIL_SENDMAIL_PATH",            "type" => "configVarchar"],
    "configMailSmtpAuth"              => ["constname" => "WIKINDX_MAIL_SMTP_AUTH",                "type" => "configBoolean"],
    "configMailSmtpEncrypt"           => ["constname" => "WIKINDX_MAIL_SMTP_ENCRYPT",             "type" => "configVarchar"],
    "configMailSmtpPassword"          => ["constname" => "WIKINDX_MAIL_SMTP_PASSWORD",            "type" => "configVarchar"],
    "configMailSmtpPersist"           => ["constname" => "WIKINDX_MAIL_SMTP_PERSIST",             "type" => "configBoolean"],
    "configMailSmtpPort"              => ["constname" => "WIKINDX_MAIL_SMTP_PORT",                "type" => "configInt"],
    "configMailSmtpServer"            => ["constname" => "WIKINDX_MAIL_SMTP_SERVER",              "type" => "configVarchar"],
    "configMailSmtpUsername"          => ["constname" => "WIKINDX_MAIL_SMTP_USERNAME",            "type" => "configVarchar"],
    "configMailUse"                   => ["constname" => "WIKINDX_MAIL_USE",                      "type" => "configBoolean"],
    "configMaxPaste"                  => ["constname" => "WIKINDX_MAX_PASTE",                     "type" => "configInt"],
    "configMetadataAllow"             => ["constname" => "WIKINDX_METADATA_ALLOW",                "type" => "configBoolean"],
    "configMetadataUserOnly"          => ["constname" => "WIKINDX_METADATA_USERONLY",             "type" => "configBoolean"],
    "configMultiUser"                 => ["constname" => "WIKINDX_MULTIUSER",                     "type" => "configBoolean"],
    "configNoSort"                    => ["constname" => "WIKINDX_NO_SORT",                       "type" => "configText"],
    "configNotify"                    => ["constname" => "WIKINDX_NOTIFY",                        "type" => "configBoolean"],
    "configOriginatorEditOnly"        => ["constname" => "WIKINDX_ORIGINATOR_EDIT_ONLY",          "type" => "configBoolean"],
    "configPaging"                    => ["constname" => "WIKINDX_PAGING",                        "type" => "configInt"],
    "configPagingMaxLinks"            => ["constname" => "WIKINDX_PAGING_MAXLINKS",               "type" => "configInt"],
    "configPagingTagCloud"            => ["constname" => "WIKINDX_PAGING_TAG_CLOUD",              "type" => "configInt"],
    "configPasswordSize"              => ["constname" => "WIKINDX_PASSWORD_SIZE",                 "type" => "configInt"],
    "configPasswordStrength"          => ["constname" => "WIKINDX_PASSWORD_STRENGTH",             "type" => "configVarchar"],
    "configQuarantine"                => ["constname" => "WIKINDX_QUARANTINE",                    "type" => "configBoolean"],
    "configReadOnlyAccess"            => ["constname" => "WIKINDX_READ_ONLY_ACCESS",              "type" => "configBoolean"],
    "configResourceUrlPrefix"         => ["constname" => "WIKINDX_RESOURCE_URL_PREFIX",           "type" => "configVarchar"],
    "configRestrictUserId"            => ["constname" => "WIKINDX_RESTRICT_USERID",               "type" => "configInt"],
    "configRssDescription"            => ["constname" => "WIKINDX_RSS_DESCRIPTION",               "type" => "configVarchar"],
    "configRssDisallow"               => ["constname" => "WIKINDX_RSS_DISALLOW",                  "type" => "configBoolean"],
    "configRssDisplayEditedResources" => ["constname" => "WIKINDX_RSS_DISPLAY_EDITED_RESOURCES",  "type" => "configBoolean"],
    "configRssLimit"                  => ["constname" => "WIKINDX_RSS_LIMIT",                     "type" => "configInt"],
    "configRssTitle"                  => ["constname" => "WIKINDX_RSS_TITLE",                     "type" => "configVarchar"],
    "configSearchFilter"              => ["constname" => "WIKINDX_SEARCH_FILTER",                 "type" => "configText"],
    "configSessionAuthMaxlifetime"    => ["constname" => "WIKINDX_SESSION_AUTH_MAXLIFETIME",      "type" => "configInt"],
    "configSessionGCLastExecTimestamp"=> ["constname" => "WIKINDX_SESSION_GC_LASTEXEC_TIMESTAMP", "type" => "configInt"],
    "configSessionNotAuthMaxlifetime" => ["constname" => "WIKINDX_SESSION_NOTAUTH_MAXLIFETIME",   "type" => "configInt"],
    "configSiteMapDisallow"           => ["constname" => "WIKINDX_SITEMAP_DISALLOW",              "type" => "configBoolean"],
    "configStatisticsCompiled"        => ["constname" => "WIKINDX_STATISTICS_COMPILED",           "type" => "configDatetime"],
    "configStringLimit"               => ["constname" => "WIKINDX_STRING_LIMIT",                  "type" => "configInt"],
    "configStyle"                     => ["constname" => "WIKINDX_STYLE",                         "type" => "configVarchar"],
    "configTagHighColour"             => ["constname" => "WIKINDX_TAG_HIGH_COLOUR",               "type" => "configVarchar"],
    "configTagHighFactor"             => ["constname" => "WIKINDX_TAG_HIGH_FACTOR",               "type" => "configInt"],
    "configTagLowColour"              => ["constname" => "WIKINDX_TAG_LOW_COLOUR",                "type" => "configVarchar"],
    "configTagLowFactor"              => ["constname" => "WIKINDX_TAG_LOW_FACTOR",                "type" => "configInt"],
    "configTemplate"                  => ["constname" => "WIKINDX_TEMPLATE",                      "type" => "configVarchar"],
    "configTimezone"                  => ["constname" => "WIKINDX_TIMEZONE",                      "type" => "configVarchar"],
    "configTitle"                     => ["constname" => "WIKINDX_TITLE",                         "type" => "configVarchar"],
    "configUserRegistration"          => ["constname" => "WIKINDX_USER_REGISTRATION",             "type" => "configBoolean"],
    "configUserRegistrationModerate"  => ["constname" => "WIKINDX_USER_REGISTRATION_MODERATE",    "type" => "configBoolean"],
]);
