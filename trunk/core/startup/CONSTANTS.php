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
  * CONSTANTS
  *
  * VARIOUS WIKINDX constants
  *
  * @package wikindx\core\startup
  */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "CONSTANTS_CONFIG_DEFAULT.php"]));

/**
 * CONSTANTS
 */
define('BR', "<br>");
define('CR', "\r");
define('LF', "\n");
define('TAB', "\t");
/**
 * WIKINDX official/public version information
 *
 * This number is the official release version
 * used by the update server to download the components.
 *
 * It can be of the form X, X.Y, or X.Y.Z (with X, Y, Z positive integers).
 *
 * @name WIKINDX_PUBLIC_VERSION
 */
define('WIKINDX_PUBLIC_VERSION', '6.3.11');
/**
 * WIKINDX internal version information
 *
 * This number MUST be a positive integer (written as a float), and should be
 * incremented by one each time an upgrade need to be triggered. Before the value 6,
 * this number was a float corresponding (or not) to part X.Y of the public version number.
 *
 * @name WIKINDX_INTERNAL_VERSION
 */
define('WIKINDX_INTERNAL_VERSION', 26.0);
/**
 * WIKINDX minimum internal version upgradable
 *
 * The syntax is the same as for WIKINDX_INTERNAL_VERSION.
 *
 * A database with a lower version number cannot be upgraded.
 *
 * @name WIKINDX_INTERNAL_VERSION_UPGRADE_MIN
 */
define('WIKINDX_INTERNAL_VERSION_UPGRADE_MIN', 5.1);
/**
 * Components compatibility
 *
 * This array is used by LOADEXTERNALMODULES class
 * and \UTILS\checkComponentIntegrity() to check components compatibility with the core.
 *
 * Each type of component has its own compatibility version
 * because they do not have the same lifecycle.
 *
 * @name WIKINDX_COMPONENTS_COMPATIBLE_VERSION
 */
define('WIKINDX_COMPONENTS_COMPATIBLE_VERSION', [
    'plugin'    => 9, // Must be an integer
    'style'     => 4, // Must be an integer
    'template'  => 1, // Must be an integer
    'vendor'    => WIKINDX_PUBLIC_VERSION, // Identical to the public version because this type of component is very closely linked to a version of the core
]);
/**
 * Minimum required PHP version
 *
 * @name WIKINDX_PHP_VERSION_MIN
 */
define('WIKINDX_PHP_VERSION_MIN', '7.0.0');
/**
 * Maximum required PHP version
 *
 * @name WIKINDX_PHP_VERSION_MAX
 */
define('WIKINDX_PHP_VERSION_MAX', '7.4.99');
/**
 * Minimum required PHP version
 *
 * @name WIKINDX_MYSQL_VERSION_MIN
 */
define('WIKINDX_MYSQL_VERSION_MIN', '5.7.5');
/**
 * Minimum required PHP version
 *
 * @name WIKINDX_MARIADB_VERSION_MIN
 */
define('WIKINDX_MARIADB_VERSION_MIN', '10.2');
/**
 * WIKINDX copyright
 *
 * @name WIKINDX_COPYRIGHT_YEAR
 */
define('WIKINDX_COPYRIGHT_YEAR', "2003-2020");
/**
 * WIKINDX SF url
 *
 * @name WIKINDX_URL
 */
define('WIKINDX_URL', 'https://wikindx.sourceforge.io');
/**
 * URL of the components update server
 *
 * @name WIKINDX_COMPONENTS_UPDATE_SERVER
 */
define('WIKINDX_COMPONENTS_UPDATE_SERVER', 'https://wikindx.sourceforge.io/cus/index.php');
/**
 * Algo used for hashing the packages released by the project
 *
 * @name WIKINDX_COMPONENTS_UPDATE_SERVER
 */
define('WIKINDX_PACKAGE_HASH_ALGO', 'sha256');

// List of core directories
define('WIKINDX_DIR_BASE', realpath(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..'])));

define('WIKINDX_DIR_CORE', 'core');
define('WIKINDX_DIR_CORE_LANGUAGES', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_CORE, 'languages']));

// List of components directories
define('WIKINDX_DIR_COMPONENT', 'components');
define('WIKINDX_DIR_COMPONENT_PLUGINS', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_COMPONENT, 'plugins']));
define('WIKINDX_DIR_COMPONENT_STYLES', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_COMPONENT, 'styles']));
define('WIKINDX_DIR_COMPONENT_TEMPLATES', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_COMPONENT, 'templates']));
define('WIKINDX_DIR_COMPONENT_VENDOR', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_COMPONENT, 'vendor']));

define('WIKINDX_URL_COMPONENT', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_COMPONENT));
define('WIKINDX_URL_COMPONENT_PLUGINS', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_COMPONENT_PLUGINS));
define('WIKINDX_URL_COMPONENT_STYLES', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_COMPONENT_STYLES));
define('WIKINDX_URL_COMPONENT_TEMPLATES', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_COMPONENT_TEMPLATES));
define('WIKINDX_URL_COMPONENT_VENDOR', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_COMPONENT_VENDOR));


// List of data directories
define('WIKINDX_DIR_DATA', 'data');
define('WIKINDX_DIR_DATA_ATTACHMENTS', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_DATA, 'attachments']));
define('WIKINDX_DIR_DATA_FILES', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_DATA, 'files']));
define('WIKINDX_DIR_DATA_IMAGES', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_DATA, 'images']));
define('WIKINDX_DIR_DATA_PLUGINS', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_DATA, 'plugins']));

define('WIKINDX_URL_DATA', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_DATA));
define('WIKINDX_URL_DATA_ATTACHMENTS', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_DATA_ATTACHMENTS));
define('WIKINDX_URL_DATA_FILES', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_DATA_FILES));
define('WIKINDX_URL_DATA_IMAGES', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_DATA_IMAGES));
define('WIKINDX_URL_DATA_PLUGINS', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_DATA_PLUGINS));


// List of cache directories
define('WIKINDX_DIR_CACHE', 'cache');
define('WIKINDX_DIR_CACHE_ATTACHMENTS', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_CACHE, 'attachments']));
define('WIKINDX_DIR_CACHE_FILES', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_CACHE, 'files']));
define('WIKINDX_DIR_CACHE_LANGUAGES', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_CACHE, 'languages']));
define('WIKINDX_DIR_CACHE_PLUGINS', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_CACHE, 'plugins']));
define('WIKINDX_DIR_CACHE_STYLES', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_CACHE, 'styles']));
define('WIKINDX_DIR_CACHE_TEMPLATES', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_CACHE, 'templates']));

define('WIKINDX_URL_CACHE', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_CACHE));
define('WIKINDX_URL_CACHE_ATTACHMENTS', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_CACHE_ATTACHMENTS));
define('WIKINDX_URL_CACHE_FILES', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_CACHE_FILES));
define('WIKINDX_URL_CACHE_LANGUAGES', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_CACHE_LANGUAGES));
define('WIKINDX_URL_CACHE_PLUGINS', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_CACHE_PLUGINS));
define('WIKINDX_URL_CACHE_STYLES', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_CACHE_STYLES));
define('WIKINDX_URL_CACHE_TEMPLATES', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_CACHE_TEMPLATES));


// List of special files
define('WIKINDX_DIR_DB_DOCS', 'docs');
define('WIKINDX_DIR_DB_SCHEMA', 'dbschema');
define('WIKINDX_FILE_REPAIRKIT_DB_SCHEMA', implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_DB_SCHEMA, 'repairkit.schema']));


/**
 * Default charset
 *
 * @name WIKINDX_CHARSET
 */
define('WIKINDX_CHARSET', 'UTF-8');


// List of mime types used in the code base
define('WIKINDX_MIMETYPE_BIB', 'application/x-bibtex');
define('WIKINDX_MIMETYPE_DOC', 'application/msword');
define('WIKINDX_MIMETYPE_DOCX', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
define('WIKINDX_MIMETYPE_ENDNOTE', 'application/vnd.openxmlformats-officedocument.wordprocessingml.endnotes+xml');
define('WIKINDX_MIMETYPE_HTM', 'text/html');
define('WIKINDX_MIMETYPE_JSON', 'application/json');
define('WIKINDX_MIMETYPE_PDF', 'application/pdf');
define('WIKINDX_MIMETYPE_RIS', 'application/x-research-info-systems');
define('WIKINDX_MIMETYPE_RSS', 'application/rss+xml');
define('WIKINDX_MIMETYPE_RTF', 'application/rtf');
define('WIKINDX_MIMETYPE_TXT', 'text/plain');
define('WIKINDX_MIMETYPE_XML', 'application/xml');
define('WIKINDX_HTTP_CONTENT_TYPE_DEFAULT', WIKINDX_MIMETYPE_HTM);


// Localisation
/** Default language */
define('WIKINDX_LANGUAGE_NAME_DEFAULT', 'English (United Kingdom)');
/** Gettext domain name of the core part */
define('WIKINDX_LANGUAGE_DOMAIN_DEFAULT', 'wikindx');


// LDAP
define('WIKINDX_LDAP_PROTOCOLE_VERSIONS', [2 => '2', 3 => '3']);


// Mail system
define('WIKINDX_PHPMAILER_BACKENDS', ['smtp' => 'SMTP', 'sendmail' => 'Sendmail']);
define('WIKINDX_PHPMAILER_SMTP_ENCRYPT', ['none' => 'none', 'tls' => 'tls', 'ssl' => 'ssl']);


// Divers
define('WIKINDX_DISPLAY_BIBTEX_LINK_DEFAULT', FALSE);
define('WIKINDX_DISPLAY_CMS_LINK_DEFAULT', FALSE);
define('WIKINDX_TAG_FACTOR_MAX', 200);
define('WIKINDX_TAG_FACTOR_MIN', 50);
define('WIKINDX_TAG_FACTOR_STEP', 5);
define('WIKINDX_TEMPLATE_MENU_DEFAULT', 0);
define('WIKINDX_UNIX_PERMS_DEFAULT', 0777);
define('WIKINDX_USE_BIBTEX_KEY_DEFAULT', FALSE);
define('WIKINDX_USE_WIKINDX_KEY_DEFAULT', FALSE);
define('WIKINDX_SUPERADMIN_ID', 1);


// Divers for users config only
define('WIKINDX_USER_PAGING_STYLE_DEFAULT', 'N');
define('WIKINDX_USER_LANGUAGE_DEFAULT', 'auto');


// RSS feed
define('WIKINDX_RSS_PAGE', '/index.php?action=rss_RSS_CORE');


// CMS API
define('WIKINDX_CMS_PAGE', '/index.php?action=cms_CMS_CORE');


/**
 * URL of the website sitemap
 *
 * @name WIKINDX_SITEMAP_PAGE
 */
define('WIKINDX_SITEMAP_PAGE', '/index.php?action=sitemap_SITEMAP_CORE');
/**
 * Max number of url by page in a sitemap (50000 max. allowed in the standard and 10 Mo max.)
 *
 * For a response time and analysis of the response 500 links per page seems reasonable, ie below the second, as the search engines expect..
 *
 * @name WIKINDX_SITEMAP_MAXSIZE
 */
define('WIKINDX_SITEMAP_MAXSIZE', 500);

/**
 * STATISTICS WEIGHTS
 *
 * The two values here are relative to each other and should total 1.0.
 */
define('WIKINDX_POPULARITY_VIEWS_WEIGHT', 0.25);
define('WIKINDX_POPULARITY_DOWNLOADS_WEIGHT', 0.75);

/**
 * OPTIONS DEFINITION
 *
 * "option db name" => ["constname" => "constant option name", "type" => "db column name"]"
 *
 * db column names could be :
 *  - configBoolean
 *  - configDatetime
 *  - configInt
 *  - configText
 *  - configVarchar
 */
define('WIKINDX_LIST_CONFIG_OPTIONS', [
    "configAuthGate"                 => ["constname" => "WIKINDX_AUTHGATE_USE",               "type" => "configBoolean"],
    "configAuthGateMessage"          => ["constname" => "WIKINDX_AUTHGATE_MESSAGE",           "type" => "configVarchar"],
    "configBypassSmartyCompile"      => ["constname" => "WIKINDX_BYPASS_SMARTY_COMPILATION",  "type" => "configBoolean"],
    "configCmsAllow"                 => ["constname" => "WIKINDX_CMS_ALLOW",                  "type" => "configBoolean"],
    "configCmsBibstyle"              => ["constname" => "WIKINDX_CMS_BIBSTYLE",               "type" => "configVarchar"],
    "configCmsDbPassword"            => ["constname" => "WIKINDX_CMS_DB_PASSWORD",            "type" => "configVarchar"],
    "configCmsDbUser"                => ["constname" => "WIKINDX_CMS_DB_USER",                "type" => "configVarchar"],
    "configCmsSql"                   => ["constname" => "WIKINDX_CMS_SQL",                    "type" => "configBoolean"],
    "configContactEmail"             => ["constname" => "WIKINDX_CONTACT_EMAIL",              "type" => "configVarchar"],
    "configDeactivateResourceTypes"  => ["constname" => "WIKINDX_DEACTIVATE_RESOURCE_TYPES",  "type" => "configText"],
    "configDebugSql"                 => ["constname" => "WIKINDX_DEBUG_SQL",                  "type" => "configBoolean"],
    "configDenyReadOnly"             => ["constname" => "WIKINDX_DENY_READONLY",              "type" => "configBoolean"],
    "configDescription"              => ["constname" => "WIKINDX_DESCRIPTION",                "type" => "configText"],
    "configDisplayStatistics"        => ["constname" => "WIKINDX_DISPLAY_STATISTICS",         "type" => "configBoolean"],
    "configDisplayUserStatistics"    => ["constname" => "WIKINDX_DISPLAY_USER_STATISTICS",    "type" => "configBoolean"],
    "configEmailNewRegistrations"    => ["constname" => "WIKINDX_EMAIL_NEW_REGISTRATIONS",    "type" => "configVarchar"],
    "configEmailNews"                => ["constname" => "WIKINDX_EMAIL_NEWS",                 "type" => "configBoolean"],
    "configEmailStatistics"          => ["constname" => "WIKINDX_EMAIL_STATISTICS",           "type" => "configBoolean"],
    "configErrorReport"              => ["constname" => "WIKINDX_DEBUG_ERRORS",               "type" => "configBoolean"],
    "configFileAttach"               => ["constname" => "WIKINDX_FILE_ATTACH",                "type" => "configBoolean"],
    "configFileDeleteSeconds"        => ["constname" => "WIKINDX_FILE_DELETE_SECONDS",        "type" => "configInt"],
    "configFileViewLoggedOnOnly"     => ["constname" => "WIKINDX_FILE_VIEW_LOGGEDON_ONLY",    "type" => "configBoolean"],
    "configGlobalEdit"               => ["constname" => "WIKINDX_GLOBAL_EDIT",                "type" => "configBoolean"],
    "configGsAllow"                  => ["constname" => "WIKINDX_GS_ALLOW",                   "type" => "configBoolean"],
    "configGsAttachment"             => ["constname" => "WIKINDX_GS_ATTACHMENT",              "type" => "configBoolean"],
    "configImagesAllow"              => ["constname" => "WIKINDX_IMAGES_ALLOW",               "type" => "configBoolean"],
    "configImagesMaxSize"            => ["constname" => "WIKINDX_IMAGES_MAXSIZE",             "type" => "configInt"],
    "configImgHeightLimit"           => ["constname" => "WIKINDX_IMG_HEIGHT_LIMIT",           "type" => "configInt"],
    "configImgWidthLimit"            => ["constname" => "WIKINDX_IMG_WIDTH_LIMIT",            "type" => "configInt"],
    "configImportBib"                => ["constname" => "WIKINDX_IMPORT_BIB",                 "type" => "configBoolean"],
    "configIsTrunk"                  => ["constname" => "WIKINDX_IS_TRUNK",                   "type" => "configBoolean"],
    "configLanguage"                 => ["constname" => "WIKINDX_LANGUAGE",                   "type" => "configVarchar"],
    "configLastChanges"              => ["constname" => "WIKINDX_LAST_CHANGES",               "type" => "configInt"],
    "configLastChangesDayLimit"      => ["constname" => "WIKINDX_LAST_CHANGES_DAY_LIMIT",     "type" => "configInt"],
    "configLastChangesType"          => ["constname" => "WIKINDX_LAST_CHANGES_TYPE",          "type" => "configVarchar"],
    "configLdapDn"                   => ["constname" => "WIKINDX_LDAP_DN",                    "type" => "configVarchar"],
    "configLdapPort"                 => ["constname" => "WIKINDX_LDAP_PORT",                  "type" => "configInt"],
    "configLdapProtocolVersion"      => ["constname" => "WIKINDX_LDAP_PROTOCOL_VERSION",      "type" => "configInt"],
    "configLdapServer"               => ["constname" => "WIKINDX_LDAP_SERVER",                "type" => "configVarchar"],
    "configLdapUse"                  => ["constname" => "WIKINDX_LDAP_USE",                   "type" => "configBoolean"],
    "configListLink"                 => ["constname" => "WIKINDX_LIST_LINK",                  "type" => "configBoolean"],
    "configMailBackend"              => ["constname" => "WIKINDX_MAIL_BACKEND",               "type" => "configVarchar"],
    "configMailFrom"                 => ["constname" => "WIKINDX_MAIL_FROM",                  "type" => "configVarchar"],
    "configMailReplyTo"              => ["constname" => "WIKINDX_MAIL_REPLYTO",               "type" => "configVarchar"],
    "configMailReturnPath"           => ["constname" => "WIKINDX_MAIL_RETURN_PATH",           "type" => "configVarchar"],
    "configMailSmPath"               => ["constname" => "WIKINDX_MAIL_SENDMAIL_PATH",         "type" => "configVarchar"],
    "configMailSmtpAuth"             => ["constname" => "WIKINDX_MAIL_SMTP_AUTH",             "type" => "configBoolean"],
    "configMailSmtpEncrypt"          => ["constname" => "WIKINDX_MAIL_SMTP_ENCRYPT",          "type" => "configVarchar"],
    "configMailSmtpPassword"         => ["constname" => "WIKINDX_MAIL_SMTP_PASSWORD",         "type" => "configVarchar"],
    "configMailSmtpPersist"          => ["constname" => "WIKINDX_MAIL_SMTP_PERSIST",          "type" => "configBoolean"],
    "configMailSmtpPort"             => ["constname" => "WIKINDX_MAIL_SMTP_PORT",             "type" => "configInt"],
    "configMailSmtpServer"           => ["constname" => "WIKINDX_MAIL_SMTP_SERVER",           "type" => "configVarchar"],
    "configMailSmtpUsername"         => ["constname" => "WIKINDX_MAIL_SMTP_USERNAME",         "type" => "configVarchar"],
    "configMailUse"                  => ["constname" => "WIKINDX_MAIL_USE",                   "type" => "configBoolean"],
    "configMaxPaste"                 => ["constname" => "WIKINDX_MAX_PASTE",                  "type" => "configInt"],
    "configMetadataAllow"            => ["constname" => "WIKINDX_METADATA_ALLOW",             "type" => "configBoolean"],
    "configMetadataUserOnly"         => ["constname" => "WIKINDX_METADATA_USERONLY",          "type" => "configBoolean"],
    "configMultiUser"                => ["constname" => "WIKINDX_MULTIUSER",                  "type" => "configBoolean"],
    "configNoSort"                   => ["constname" => "WIKINDX_NO_SORT",                    "type" => "configText"],
    "configNotify"                   => ["constname" => "WIKINDX_NOTIFY",                     "type" => "configBoolean"],
    "configOriginatorEditOnly"       => ["constname" => "WIKINDX_ORIGINATOR_EDIT_ONLY",       "type" => "configBoolean"],
    "configPaging"                   => ["constname" => "WIKINDX_PAGING",                     "type" => "configInt"],
    "configPagingMaxLinks"           => ["constname" => "WIKINDX_PAGING_MAXLINKS",            "type" => "configInt"],
    "configPagingTagCloud"           => ["constname" => "WIKINDX_PAGING_TAG_CLOUD",           "type" => "configInt"],
    "configPasswordSize"             => ["constname" => "WIKINDX_PASSWORD_SIZE",              "type" => "configInt"],
    "configPasswordStrength"         => ["constname" => "WIKINDX_PASSWORD_STRENGTH",          "type" => "configVarchar"],
    "configQuarantine"               => ["constname" => "WIKINDX_QUARANTINE",                 "type" => "configBoolean"],
    "configReadOnlyAccess"           => ["constname" => "WIKINDX_READ_ONLY_ACCESS",           "type" => "configBoolean"],
    "configRestrictUserId"           => ["constname" => "WIKINDX_RESTRICT_USERID",            "type" => "configInt"],
    "configRssAllow"                 => ["constname" => "WIKINDX_RSS_ALLOW",                  "type" => "configBoolean"],
    "configRssBibstyle"              => ["constname" => "WIKINDX_RSS_BIBSTYLE",               "type" => "configVarchar"],
    "configRssDescription"           => ["constname" => "WIKINDX_RSS_DESCRIPTION",            "type" => "configVarchar"],
    "configRssDisplay"               => ["constname" => "WIKINDX_RSS_DISPLAY",                "type" => "configBoolean"],
    "configRssLimit"                 => ["constname" => "WIKINDX_RSS_LIMIT",                  "type" => "configInt"],
    "configRssTitle"                 => ["constname" => "WIKINDX_RSS_TITLE",                  "type" => "configVarchar"],
    "configSearchFilter"             => ["constname" => "WIKINDX_SEARCH_FILTER",              "type" => "configText"],
    "configSiteMapAllow"             => ["constname" => "WIKINDX_SITEMAP_ALLOW",              "type" => "configBoolean"],
    "configStatisticsCompiled"       => ["constname" => "WIKINDX_STATISTICS_COMPILED",        "type" => "configDatetime"],
    "configStringLimit"              => ["constname" => "WIKINDX_STRING_LIMIT",               "type" => "configInt"],
    "configStyle"                    => ["constname" => "WIKINDX_STYLE",                      "type" => "configVarchar"],
    "configTagHighColour"            => ["constname" => "WIKINDX_TAG_HIGH_COLOUR",            "type" => "configVarchar"],
    "configTagHighFactor"            => ["constname" => "WIKINDX_TAG_HIGH_FACTOR",            "type" => "configInt"],
    "configTagLowColour"             => ["constname" => "WIKINDX_TAG_LOW_COLOUR",             "type" => "configVarchar"],
    "configTagLowFactor"             => ["constname" => "WIKINDX_TAG_LOW_FACTOR",             "type" => "configInt"],
    "configTemplate"                 => ["constname" => "WIKINDX_TEMPLATE",                   "type" => "configVarchar"],
    "configTimezone"                 => ["constname" => "WIKINDX_TIMEZONE",                   "type" => "configVarchar"],
    "configTitle"                    => ["constname" => "WIKINDX_TITLE",                      "type" => "configVarchar"],
    "configUserRegistration"         => ["constname" => "WIKINDX_USER_REGISTRATION",          "type" => "configBoolean"],
    "configUserRegistrationModerate" => ["constname" => "WIKINDX_USER_REGISTRATION_MODERATE", "type" => "configBoolean"],
]);
