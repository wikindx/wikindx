<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

 /**
  * CONSTANTS
  *
  * VARIOUS WIKINDX constants
  *
  * @package wikindx\core\startup
  */
 include_once(__DIR__ . DIRECTORY_SEPARATOR . "CONSTANTS_CONFIG_DEFAULT.php");

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
define('WIKINDX_PUBLIC_VERSION', '6.2.2');
/**
 * WIKINDX internal version information
 *
 * This number MUST be a positive integer (written as a float), and should be
 * incremented by one each time an upgrade need to be triggered. Before the value 6,
 * this number was a float corresponding (or not) to part X.Y of the public version number.
 *
 * @name WIKINDX_INTERNAL_VERSION
 */
define('WIKINDX_INTERNAL_VERSION', 14.0);
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
 * Plugin compatibility -- x.x (usually matching the major WIKINDX version) which must be changed each time plugins require an
 * upgrade to match the WIKINDX code. The plugin's $config->wikindxVersion must be equal to this value for the plugin to be compatible.
 *
 * The check occurs in LOADEXTERNALMODULES.php
 *
 * @name WIKINDX_PLUGIN_VERSION
 */
define('WIKINDX_PLUGIN_VERSION', 7);
/**
 * Minimum required PHP version
 *
 * @name WIKINDX_PHP_VERSION_MIN
 */
define('WIKINDX_PHP_VERSION_MIN', '7.0.0');
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
define('WIKINDX_COMPONENTS_UPDATE_SERVER', 'https://wikindx.sourceforge.io/downloads/components_server.php');
/**
 * Algo used for hashing the packages released by the project
 *
 * @name WIKINDX_COMPONENTS_UPDATE_SERVER
 */
define('WIKINDX_PACKAGE_HASH_ALGO', 'sha256');


// List of components directories
define('WIKINDX_DIR_COMPONENT', 'components');
define('WIKINDX_DIR_COMPONENT_LANGUAGES', WIKINDX_DIR_COMPONENT . DIRECTORY_SEPARATOR . 'languages');
define('WIKINDX_DIR_COMPONENT_PLUGINS', WIKINDX_DIR_COMPONENT . DIRECTORY_SEPARATOR . 'plugins');
define('WIKINDX_DIR_COMPONENT_STYLES', WIKINDX_DIR_COMPONENT . DIRECTORY_SEPARATOR . 'styles');
define('WIKINDX_DIR_COMPONENT_TEMPLATES', WIKINDX_DIR_COMPONENT . DIRECTORY_SEPARATOR . 'templates');
define('WIKINDX_DIR_COMPONENT_VENDOR', WIKINDX_DIR_COMPONENT . DIRECTORY_SEPARATOR . 'vendor');

define('WIKINDX_URL_COMPONENT', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_COMPONENT));
define('WIKINDX_URL_COMPONENT_LANGUAGES', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_COMPONENT_LANGUAGES));
define('WIKINDX_URL_COMPONENT_PLUGINS', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_COMPONENT_PLUGINS));
define('WIKINDX_URL_COMPONENT_STYLES', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_COMPONENT_STYLES));
define('WIKINDX_URL_COMPONENT_TEMPLATES', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_COMPONENT_TEMPLATES));
define('WIKINDX_URL_COMPONENT_VENDOR', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_COMPONENT_VENDOR));


// List of data directories
define('WIKINDX_DIR_DATA', 'data');
define('WIKINDX_DIR_DB_SCHEMA', 'dbschema');
define('WIKINDX_DIR_DATA_ATTACHMENTS', WIKINDX_DIR_DATA . DIRECTORY_SEPARATOR . 'attachments');
define('WIKINDX_DIR_DATA_FILES', WIKINDX_DIR_DATA . DIRECTORY_SEPARATOR . 'files');
define('WIKINDX_DIR_DATA_IMAGES', WIKINDX_DIR_DATA . DIRECTORY_SEPARATOR . 'images');
define('WIKINDX_DIR_DATA_PLUGINS', WIKINDX_DIR_DATA . DIRECTORY_SEPARATOR . 'plugins');

define('WIKINDX_URL_DATA', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_DATA));
define('WIKINDX_URL_DB_SCHEMA', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_DB_SCHEMA));
define('WIKINDX_URL_DATA_ATTACHMENTS', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_DATA_ATTACHMENTS));
define('WIKINDX_URL_DATA_FILES', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_DATA_FILES));
define('WIKINDX_URL_DATA_IMAGES', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_DATA_IMAGES));
define('WIKINDX_URL_DATA_PLUGINS', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_DATA_PLUGINS));


// List of cache directories
define('WIKINDX_DIR_CACHE', 'cache');
define('WIKINDX_DIR_CACHE_ATTACHMENTS', WIKINDX_DIR_CACHE . DIRECTORY_SEPARATOR . 'attachments');
define('WIKINDX_DIR_CACHE_FILES', WIKINDX_DIR_CACHE . DIRECTORY_SEPARATOR . 'files');
define('WIKINDX_DIR_CACHE_LANGUAGES', WIKINDX_DIR_CACHE . DIRECTORY_SEPARATOR . 'languages');
define('WIKINDX_DIR_CACHE_PLUGINS', WIKINDX_DIR_CACHE . DIRECTORY_SEPARATOR . 'plugins');
define('WIKINDX_DIR_CACHE_STYLES', WIKINDX_DIR_CACHE . DIRECTORY_SEPARATOR . 'styles');
define('WIKINDX_DIR_CACHE_TEMPLATES', WIKINDX_DIR_CACHE . DIRECTORY_SEPARATOR . 'templates');

define('WIKINDX_URL_CACHE', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_CACHE));
define('WIKINDX_URL_CACHE_ATTACHMENTS', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_CACHE_ATTACHMENTS));
define('WIKINDX_URL_CACHE_FILES', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_CACHE_FILES));
define('WIKINDX_URL_CACHE_LANGUAGES', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_CACHE_LANGUAGES));
define('WIKINDX_URL_CACHE_PLUGINS', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_CACHE_PLUGINS));
define('WIKINDX_URL_CACHE_STYLES', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_CACHE_STYLES));
define('WIKINDX_URL_CACHE_TEMPLATES', str_replace(DIRECTORY_SEPARATOR, "/", WIKINDX_DIR_CACHE_TEMPLATES));


// List of special files
define('WIKINDX_FILE_REPAIRKIT_DB_SCHEMA', WIKINDX_DIR_DB_SCHEMA . DIRECTORY_SEPARATOR . 'repairkit.schema');


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

define('WIKINDX_LIST_CONFIG_OPTIONS_NAME', [
	'configAuthGate' => 'WIKINDX_AUTHGATE_USE',
	'configAuthGateMessage' => 'WIKINDX_AUTHGATE_MESSAGE',
	'configBypassSmartyCompile' => 'WIKINDX_BYPASS_SMARTY_COMPILATION',
	'configCmsAllow' => 'WIKINDX_CMS_ALLOW',
	'configCmsBibstyle' => 'WIKINDX_CMS_BIBSTYLE',
	'configCmsDbPassword' => 'WIKINDX_CMS_DB_PASSWORD',
	'configCmsDbUser' => 'WIKINDX_CMS_DB_USER',
	'configCmsSql' => 'WIKINDX_CMS_SQL',
	'configContactEmail' => 'WIKINDX_CONTACT_EMAIL',
	'configDeactivateResourceTypes' => 'WIKINDX_DEACTIVATE_RESOURCE_TYPES',
	'configDebugSql' => 'WIKINDX_DEBUG_SQL',
	'configDenyReadOnly' => 'WIKINDX_DENY_READONLY',
	'configDescription' => 'WIKINDX_DESCRIPTION',
	'configDisplayStatistics' => 'WIKINDX_DISPLAY_STATISTICS',
	'configDisplayUserStatistics' => 'WIKINDX_DISPLAY_USER_STATISTICS',
	'configEmailNewRegistrations' => 'WIKINDX_EMAIL_NEW_REGISTRATIONS',
	'configEmailNews' => 'WIKINDX_EMAIL_NEWS',
	'configEmailStatistics' => 'WIKINDX_EMAIL_STATISTICS',
	'configErrorReport' => 'WIKINDX_DEBUG_ERRORS',
	'configFileAttach' => 'WIKINDX_FILE_ATTACH',
	'configFileDeleteSeconds' => 'WIKINDX_FILE_DELETE_SECONDS',
	'configFileViewLoggedOnOnly' => 'WIKINDX_FILE_VIEW_LOGGEDON_ONLY',
	'configGlobalEdit' => 'WIKINDX_GLOBAL_EDIT',
	'configGsAllow' => 'WIKINDX_GS_ALLOW',
	'configGsAttachment' => 'WIKINDX_GS_ATTACHMENT',
	'configImagesAllow' => 'WIKINDX_IMAGES_ALLOW',
	'configImagesMaxSize' => 'WIKINDX_IMAGES_MAXSIZE',
	'configImgHeightLimit' => 'WIKINDX_IMG_HEIGHT_LIMIT',
	'configImgWidthLimit' => 'WIKINDX_IMG_WIDTH_LIMIT',
	'configImportBib' => 'WIKINDX_IMPORT_BIB',
	'configIsTrunk' => 'WIKINDX_IS_TRUNK',
	'configLanguage' => 'WIKINDX_LANGUAGE', // These is also a user variable needed for the logon page where the defaults are required.
	'configLastChanges' => 'WIKINDX_LAST_CHANGES',
	'configLastChangesDayLimit' => 'WIKINDX_LAST_CHANGES_DAY_LIMIT',
	'configLastChangesType' => 'WIKINDX_LAST_CHANGES_TYPE',
	'configLdapDn' => 'WIKINDX_LDAP_DN',
	'configLdapPort' => 'WIKINDX_LDAP_PORT',
	'configLdapProtocolVersion' => 'WIKINDX_LDAP_PROTOCOL_VERSION',
	'configLdapServer' => 'WIKINDX_LDAP_SERVER',
	'configLdapUse' => 'WIKINDX_LDAP_USE',
	'configListLink' => 'WIKINDX_LIST_LINK',
	'configMailBackend' => 'WIKINDX_MAIL_BACKEND',
	'configMailFrom' => 'WIKINDX_MAIL_FROM',
	'configMailReplyTo' => 'WIKINDX_MAIL_REPLYTO',
	'configMailReturnPath' => 'WIKINDX_MAIL_RETURN_PATH',
	'configMailSmPath' => 'WIKINDX_MAIL_SENDMAIL_PATH',
	'configMailSmtpAuth' => 'WIKINDX_MAIL_SMTP_AUTH',
	'configMailSmtpEncrypt' => 'WIKINDX_MAIL_SMTP_ENCRYPT',
	'configMailSmtpPassword' => 'WIKINDX_MAIL_SMTP_PASSWORD',
	'configMailSmtpPersist' => 'WIKINDX_MAIL_SMTP_PERSIST',
	'configMailSmtpPort' => 'WIKINDX_MAIL_SMTP_PORT',
	'configMailSmtpServer' => 'WIKINDX_MAIL_SMTP_SERVER',
	'configMailSmtpUsername' => 'WIKINDX_MAIL_SMTP_USERNAME',
	'configMailUse' => 'WIKINDX_MAIL_USE',
	'configMaxPaste' => 'WIKINDX_MAX_PASTE',
	'configMetadataAllow' => 'WIKINDX_METADATA_ALLOW',
	'configMetadataUserOnly' => 'WIKINDX_METADATA_USERONLY',
	'configMultiUser' => 'WIKINDX_MULTIUSER',
	'configNoSort' => 'WIKINDX_NO_SORT',
	'configNotify' => 'WIKINDX_NOTIFY',
	'configOriginatorEditOnly' => 'WIKINDX_ORIGINATOR_EDIT_ONLY',
	'configPaging' => 'WIKINDX_PAGING', // This is a user variables now found in GLOBALS::getUserVar()
	'configPagingMaxLinks' => 'WIKINDX_PAGING_MAXLINKS', // This is a user variables now found in GLOBALS::getUserVar()
	'configPagingTagCloud' => 'WIKINDX_PAGING_TAG_CLOUD', // This is a user variables now found in GLOBALS::getUserVar()
	'configPasswordSize' => 'WIKINDX_PASSWORD_SIZE',
	'configPasswordStrength' => 'WIKINDX_PASSWORD_STRENGTH',
	'configQuarantine' => 'WIKINDX_QUARANTINE',
	'configReadOnlyAccess' => 'WIKINDX_READ_ONLY_ACCESS',
	'configRestrictUserId' => 'WIKINDX_RESTRICT_USERID',
	'configRssAllow' => 'WIKINDX_RSS_ALLOW',
	'configRssBibstyle' => 'WIKINDX_RSS_BIBSTYLE',
	'configRssDescription' => 'WIKINDX_RSS_DESCRIPTION',
	'configRssDisplay' => 'WIKINDX_RSS_DISPLAY',
	'configRssLimit' => 'WIKINDX_RSS_LIMIT',
	'configRssTitle' => 'WIKINDX_RSS_TITLE',
	'configSearchFilter' => 'WIKINDX_SEARCH_FILTER',
	'configSiteMapAllow' => 'WIKINDX_SITEMAP_ALLOW',
	'configStatisticsCompiled' => 'WIKINDX_STATISTICS_COMPILED',
	'configStringLimit' => 'WIKINDX_STRING_LIMIT', // This is a user variables now found in GLOBALS::getUserVar()
	'configStyle' => 'WIKINDX_STYLE', // This is a user variables now found in GLOBALS::getUserVar()
	'configTagHighColour' => 'WIKINDX_TAG_HIGH_COLOUR',
	'configTagHighFactor' => 'WIKINDX_TAG_HIGH_FACTOR',
	'configTagLowColour' => 'WIKINDX_TAG_LOW_COLOUR',
	'configTagLowFactor' => 'WIKINDX_TAG_LOW_FACTOR',
	'configTemplate' => 'WIKINDX_TEMPLATE', // These is also a user variable needed for the logon page where the defaults are required.
	'configTimezone' => 'WIKINDX_TIMEZONE',
	'configTitle' => 'WIKINDX_TITLE',
	'configUserRegistration' => 'WIKINDX_USER_REGISTRATION',
	'configUserRegistrationModerate' => 'WIKINDX_USER_REGISTRATION_MODERATE',
]);

define('WIKINDX_LIST_CONFIG_OPTIONS_TYPE', [
	'configAuthGate' => 'configBoolean',
	'configAuthGateMessage' => 'configVarchar',
	'configBypassSmartyCompile' => 'configBoolean',
	'configCmsAllow' => 'configBoolean',
	'configCmsBibstyle' => 'configVarchar',
	'configCmsDbPassword' => 'configVarchar',
	'configCmsDbUser' => 'configVarchar',
	'configCmsSql' => 'configBoolean',
	'configContactEmail' => 'configVarchar',
	'configDeactivateResourceTypes' => 'configText',
	'configDebugSql' => 'configBoolean',
	'configDenyReadOnly' => 'configBoolean',
	'configDescription' => 'configText',
	'configDisplayStatistics' => 'configBoolean',
	'configDisplayUserStatistics' => 'configBoolean',
	'configEmailNewRegistrations' => 'configVarchar',
	'configEmailNews' => 'configBoolean',
	'configEmailStatistics' => 'configBoolean',
	'configErrorReport' => 'configBoolean',
	'configFileAttach' => 'configBoolean',
	'configFileDeleteSeconds' => 'configInt',
	'configFileViewLoggedOnOnly' => 'configBoolean',
	'configGlobalEdit' => 'configBoolean',
	'configGsAllow' => 'configBoolean',
	'configGsAttachment' => 'configBoolean',
	'configImagesAllow' => 'configBoolean',
	'configImagesMaxSize' => 'configInt',
	'configImgHeightLimit' => 'configInt',
	'configImgWidthLimit' => 'configInt',
	'configImportBib' => 'configBoolean',
	'configIsTrunk' => 'configBoolean',
	'configLanguage' => 'configVarchar',
	'configLastChanges' => 'configInt',
	'configLastChangesDayLimit' => 'configInt',
	'configLastChangesType' => 'configVarchar',
	'configLdapDn' => 'configVarchar',
	'configLdapPort' => 'configInt',
	'configLdapProtocolVersion' => 'configInt',
	'configLdapServer' => 'configVarchar',
	'configLdapUse' => 'configBoolean',
	'configListLink' => 'configBoolean',
	'configMailBackend' => 'configVarchar',
	'configMailFrom' => 'configVarchar',
	'configMailReplyTo' => 'configVarchar',
	'configMailReturnPath' => 'configVarchar',
	'configMailSmPath' => 'configVarchar',
	'configMailSmtpAuth' => 'configBoolean',
	'configMailSmtpEncrypt' => 'configVarchar',
	'configMailSmtpPassword' => 'configVarchar',
	'configMailSmtpPersist' => 'configBoolean',
	'configMailSmtpPort' => 'configInt',
	'configMailSmtpServer' => 'configVarchar',
	'configMailSmtpUsername' => 'configVarchar',
	'configMailUse' => 'configBoolean',
	'configMaxPaste' => 'configInt',
	'configMetadataAllow' => 'configBoolean',
	'configMetadataUserOnly' => 'configBoolean',
	'configMultiUser' => 'configBoolean',
	'configNoSort' => 'configText',
	'configNotify' => 'configBoolean',
	'configOriginatorEditOnly' => 'configBoolean',
	'configPaging' => 'configInt',
	'configPagingMaxLinks' => 'configInt',
	'configPagingTagCloud' => 'configInt',
	'configPasswordSize' => 'configInt',
	'configPasswordStrength' => 'configVarchar',
	'configQuarantine' => 'configBoolean',
	'configReadOnlyAccess' => 'configBoolean',
	'configRestrictUserId' => 'configInt',
	'configRssAllow' => 'configBoolean',
	'configRssBibstyle' => 'configVarchar',
	'configRssDescription' => 'configVarchar',
	'configRssDisplay' => 'configBoolean',
	'configRssLimit' => 'configInt',
	'configRssTitle' => 'configVarchar',
	'configSearchFilter' => 'configText',
	'configSiteMapAllow' => 'configBoolean',
	'configStatisticsCompiled' => 'configDatetime',
	'configStringLimit' => 'configInt',
	'configStyle' => 'configVarchar',
	'configTagHighColour' => 'configVarchar',
	'configTagHighFactor' => 'configInt',
	'configTagLowColour' => 'configVarchar',
	'configTagLowFactor' => 'configInt',
	'configTemplate' => 'configVarchar',
	'configTimezone' => 'configVarchar',
	'configTitle' => 'configVarchar',
	'configUserRegistration' => 'configBoolean',
	'configUserRegistrationModerate' => 'configBoolean',
]);