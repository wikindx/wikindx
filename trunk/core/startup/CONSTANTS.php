<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
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
define('CR',   "\r");
define('LF',   "\n");
define('TAB',  "\t");
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
define('WIKINDX_INTERNAL_VERSION', 12.0);
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
define('WIKINDX_DISPLAY_BIBTEX_LINK_DEFAULT', 'N');
define('WIKINDX_DISPLAY_CMS_LINK_DEFAULT', 'N');
define('WIKINDX_USER_PAGING_STYLE_DEFAULT', 'N');
define('WIKINDX_TAG_FACTOR_MAX', 200);
define('WIKINDX_TAG_FACTOR_MIN', 50);
define('WIKINDX_TAG_FACTOR_STEP', 5);
define('WIKINDX_TEMPLATE_MENU_DEFAULT', 0);
define('WIKINDX_UNIX_PERMS_DEFAULT', 0777);
define('WIKINDX_USE_BIBTEX_KEY_DEFAULT', 'N');
define('WIKINDX_USE_WIKINDX_KEY_DEFAULT', 'N');
define('WIKINDX_SUPERADMIN_ID', 1);


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
