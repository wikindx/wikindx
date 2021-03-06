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
 * Various WIKINDX constants
 *
 * @package wikindx\core\startup
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "CONSTANTS_CONFIG_DEFAULT.php"]));
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "CONSTANTS_OPTIONS.php"]));
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "CONSTANTS_OPTIONS_DEFAULT.php"]));

/**
 * CONSTANTS
 */
define('BR', "<br>");
define('CR', "\r");
define('LF', "\n");
define('CRLF', "\r\n");
define('TAB', "\t");
/**
 * WIKINDX official/public version information
 *
 * This number is the official release version
 * used by the update server to download the components.
 *
 * It can be of the form X, X.Y, or X.Y.Z (with X, Y, Z positive integers).
 *
 * @global string WIKINDX_PUBLIC_VERSION
 */
define('WIKINDX_PUBLIC_VERSION', '6.4.10');
/**
 * WIKINDX internal version information
 *
 * This number MUST be a positive integer (written as a float), and should be
 * incremented by one each time an upgrade need to be triggered. Before the value 6,
 * this number was a float corresponding (or not) to part X.Y of the public version number.
 *
 * @global float WIKINDX_INTERNAL_VERSION
 */
define('WIKINDX_INTERNAL_VERSION', 71.0);
/**
 * WIKINDX minimum internal version upgradable
 *
 * The syntax is the same as for WIKINDX_INTERNAL_VERSION.
 *
 * A database with a lower version number cannot be upgraded.
 *
 * @global float WIKINDX_INTERNAL_VERSION_UPGRADE_MIN
 */
define('WIKINDX_INTERNAL_VERSION_UPGRADE_MIN', 5.1);
/**
 * Components compatibility
 *
 * This array is used by LOADPLUGINS class
 * and \UTILS\checkComponentIntegrity() to check components compatibility with the core.
 *
 * Each type of component has its own compatibility version
 * because they do not have the same lifecycle.
 *
 * @global int[] WIKINDX_COMPONENTS_COMPATIBLE_VERSION
 */
define('WIKINDX_COMPONENTS_COMPATIBLE_VERSION', [
    'plugin'    => 13, // Must be an integer
    'style'     =>  6, // Must be an integer
    'template'  =>  1, // Must be an integer
    'vendor'    =>  WIKINDX_PUBLIC_VERSION, // Identical to the public version because this type of component is very closely linked to a version of the core
]);
/**
 * Integer. For office add-ins. In each add-in (Word, Google Docs, LibreOffice etc.) the compatibility variable must equal this.
 *
 * @global int WIKINDX_OFFICE_VERSION */
define('WIKINDX_OFFICE_VERSION', 2);
/**
 * Minimum required PHP version
 *
 * @global string WIKINDX_PHP_VERSION_MIN
 */
define('WIKINDX_PHP_VERSION_MIN', '7.3.0');
/**
 * Maximum required PHP version
 *
 * @global string WIKINDX_PHP_VERSION_MAX
 */
define('WIKINDX_PHP_VERSION_MAX', '7.4.99');
/**
 * Minimum required PHP version
 *
 * @global string WIKINDX_MYSQL_VERSION_MIN
 */
define('WIKINDX_MYSQL_VERSION_MIN', '5.7.5');
/**
 * Minimum required PHP version
 *
 * @global string WIKINDX_MARIADB_VERSION_MIN
 */
define('WIKINDX_MARIADB_VERSION_MIN', '10.2');
/**
 * WIKINDX copyright
 *
 * @global string WIKINDX_COPYRIGHT_YEAR
 */
define('WIKINDX_COPYRIGHT_YEAR', "2003-2021");
/**
 * WIKINDX release date
 *
 * @global string WIKINDX_RELEASE_DATE
 */
define('WIKINDX_RELEASE_DATE', "2021-07-07");
/**
 * WIKINDX release timestamp
 *
 * The release script use it to change the date of files and make archives reproducible.
 *
 * @global int WIKINDX_RELEASE_TIMESTAMP
 */
define('WIKINDX_RELEASE_TIMESTAMP', (new DateTime(WIKINDX_RELEASE_DATE))->getTimestamp());
/**
 * WIKINDX SF url
 *
 * @global WIKINDX_URL
 */
define('WIKINDX_URL', 'https://wikindx.sourceforge.io');
/**
 * URL of the components update server
 *
 * @global string WIKINDX_COMPONENTS_UPDATE_SERVER
 */
define('WIKINDX_COMPONENTS_UPDATE_SERVER', 'https://wikindx.sourceforge.io/cus/index.php');

/**
 * URL of the Help Topics on the website
 *
 * @global string WIKINDX_URL_HELP_TOPICS
 */
define('WIKINDX_URL_HELP_TOPICS', 'https://wikindx.sourceforge.io/web/' . WIKINDX_PUBLIC_VERSION . '/help-topics');
/**
 * Algo used for hashing the packages released by the project
 *
 * @global string WIKINDX_COMPONENTS_UPDATE_SERVER
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
 * @global string WIKINDX_CHARSET
 */
define('WIKINDX_CHARSET', 'UTF-8');

// List of mime types used in the code base
define('WIKINDX_MIMETYPE_ABW',     'application/x-abiword');
define('WIKINDX_MIMETYPE_ATOM',    'application/atom+xml');
define('WIKINDX_MIMETYPE_BIB',     'application/x-bibtex');
define('WIKINDX_MIMETYPE_CSV',     'text/csv');
define('WIKINDX_MIMETYPE_DJV',     'image/vnd.djvu');
define('WIKINDX_MIMETYPE_DOC',     'application/msword');
define('WIKINDX_MIMETYPE_DOCM',    'application/vnd.ms-word.document.macroEnabled.12');
define('WIKINDX_MIMETYPE_DOCX',    'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
define('WIKINDX_MIMETYPE_DOTM',    'application/vnd.ms-word.template.macroEnabled.12');
define('WIKINDX_MIMETYPE_DOTX',    'application/vnd.openxmlformats-officedocument.wordprocessingml.template');
define('WIKINDX_MIMETYPE_DVI',     'application/x-dvi');
define('WIKINDX_MIMETYPE_ENDNOTE', 'application/vnd.openxmlformats-officedocument.wordprocessingml.endnotes+xml');
define('WIKINDX_MIMETYPE_EPUB',    'application/epub+zip');
define('WIKINDX_MIMETYPE_FB',      'application/x-fictionbook'); // Private mimetype
define('WIKINDX_MIMETYPE_HTML',    'text/html');
define('WIKINDX_MIMETYPE_JSON',    'application/json');
define('WIKINDX_MIMETYPE_KWD',     'application/vnd.kde.kword');
define('WIKINDX_MIMETYPE_LWP',     'application/vnd.lotus-wordpro');
define('WIKINDX_MIMETYPE_MD',      'text/markdown');
define('WIKINDX_MIMETYPE_MHT_ALT', 'message/alternative'); // Emails
define('WIKINDX_MIMETYPE_MHT_APP', 'application/x-mimearchive');
define('WIKINDX_MIMETYPE_MHT_MIX', 'message/mixed'); // Emails
define('WIKINDX_MIMETYPE_MHT_MUL', 'multipart/related');
define('WIKINDX_MIMETYPE_MHT_RFC', 'message/rfc822');
define('WIKINDX_MIMETYPE_ODP',     'application/vnd.oasis.opendocument.presentation');
define('WIKINDX_MIMETYPE_ODT',     'application/vnd.oasis.opendocument.text');
define('WIKINDX_MIMETYPE_OTP',     'application/vnd.oasis.opendocument.presentation-template');
define('WIKINDX_MIMETYPE_OTT',     'application/vnd.oasis.opendocument.text-template');
define('WIKINDX_MIMETYPE_OXPS',    'application/oxps');
define('WIKINDX_MIMETYPE_PDF',     'application/pdf');
define('WIKINDX_MIMETYPE_POTM',    'application/vnd.ms-powerpoint.template.macroEnabled.12');
define('WIKINDX_MIMETYPE_POTX',    'application/vnd.openxmlformats-officedocument.presentationml.template');
define('WIKINDX_MIMETYPE_PPT',     'application/vnd.ms-powerpoint');
define('WIKINDX_MIMETYPE_PPTM',    'application/vnd.ms-powerpoint.presentation.macroEnabled.12');
define('WIKINDX_MIMETYPE_PPTX',    'application/vnd.openxmlformats-officedocument.presentationml.presentation');
define('WIKINDX_MIMETYPE_PS',      'application/postscript');
define('WIKINDX_MIMETYPE_RIS',     'application/x-research-info-systems');
define('WIKINDX_MIMETYPE_RSS',     'application/rss+xml');
define('WIKINDX_MIMETYPE_RTF_APP', 'application/rtf');
define('WIKINDX_MIMETYPE_RTF_TEXT','text/rtf');
define('WIKINDX_MIMETYPE_SCRIBUS', 'application/vnd.scribus');
define('WIKINDX_MIMETYPE_STI',     'application/vnd.sun.xml.impress.template');
define('WIKINDX_MIMETYPE_STW',     'application/vnd.sun.xml.writer.template');
define('WIKINDX_MIMETYPE_SXI',     'application/vnd.sun.xml.impress');
define('WIKINDX_MIMETYPE_SXW',     'application/vnd.sun.xml.writer');
define('WIKINDX_MIMETYPE_TEI',     'application/tei+xml');
define('WIKINDX_MIMETYPE_TROFF',   'text/troff');
define('WIKINDX_MIMETYPE_TXT',     'text/plain');
define('WIKINDX_MIMETYPE_WML',     'text/vnd.wap.wml');
define('WIKINDX_MIMETYPE_WMLC',    'application/vnd.wap.wmlc');
define('WIKINDX_MIMETYPE_WPD',     'application/vnd.wordperfect');
define('WIKINDX_MIMETYPE_WPD51',   'application/wordperfect5.1');
define('WIKINDX_MIMETYPE_WPS',     'application/vnd.ms-works');
define('WIKINDX_MIMETYPE_WRI',     'application/mswrite');
define('WIKINDX_MIMETYPE_XHTML',   'application/xml+html');
define('WIKINDX_MIMETYPE_XML_APP', 'application/xml');
define('WIKINDX_MIMETYPE_XML_TEXT','text/xml');
define('WIKINDX_MIMETYPE_XPDF',    'application/x-pdf');
define('WIKINDX_MIMETYPE_XPS',     'application/vnd.ms-xpsdocument');
define('WIKINDX_HTTP_CONTENT_TYPE_DEFAULT', WIKINDX_MIMETYPE_HTML);


// Localisation
/** Default language */
define('WIKINDX_LANGUAGE_NAME_DEFAULT', 'English (United Kingdom)');
/** Gettext domain name of the core part */
define('WIKINDX_LANGUAGE_DOMAIN_DEFAULT', 'wikindx');


// LDAP
// cf. https://chrisbeams.wordpress.com/2009/05/10/active-directory-samaccounttype/
// 268435456  SAM_GROUP_OBJECT
// 268435457  SAM_NON_SECURITY_GROUP_OBJECT
// 536870912  SAM_ALIAS_OBJECT
// 536870913  SAM_NON_SECURITY_ALIAS_OBJECT
// 805306368  SAM_NORMAL_USER_ACCOUNT
// 805306369  SAM_MACHINE_ACCOUNT
// 805306370  SAM_TRUST_ACCOUNT
// 1073741824 SAM_APP_BASIC_GROUP
// 1073741825 SAM_APP_QUERY_GROUP
// 2147483647 SAM_ACCOUNT_TYPE_MAX
define('WIKINDX_LDAP_USER_TYPE_FILTER', '(sAMAccountType=805306368)');
define('WIKINDX_LDAP_GROUP_TYPE_FILTER', '(|(sAMAccountType=268435456)(sAMAccountType=268435457))');
define('WIKINDX_LDAP_DEBUG_LEVEL', 7); // 7 = max level
define('WIKINDX_LDAP_SERVER_NETWORK_TIMEOUT', 10); // In seconds
define('WIKINDX_LDAP_SERVER_RESPONSE_TIMEOUT', 15); // In seconds
define('WIKINDX_LDAP_USE_REFERRALS', 0); // 1/0 = On/off
define('WIKINDX_LDAP_SERVER_ENCRYPTION_LIST', ['none' => 'none', 'ssl' => 'ssl', 'starttls' => 'starttls']);
define('WIKINDX_LDAP_SERVER_BIND_TYPE_LIST', ['anonymous' => 'anonymous', 'binduser' => 'binduser', 'user' => 'user']);
define('WIKINDX_LDAP_SEARCH_METHOD_LIST', ['list' => 'list', 'tree' => 'tree']);
define('WIKINDX_LDAP_SEARCH_OPERATOR_LIST', ['or' => 'or', 'and' => 'and']);
define('WIKINDX_LDAP_SERVER_BIND_DOMAIN_FORMAT_LIST', ['none' => 'none', 'sam' => 'sam', 'upn' => 'upn']);
define('WIKINDX_LDAP_USER_ATTRIBUTE_EMAIL', 'mail');
define('WIKINDX_LDAP_USER_ATTRIBUTE_FULLNAME', 'displayname');
define('WIKINDX_LDAP_USER_ATTRIBUTE_LOGIN_LIST', ['CN' => 'CN', 'sAMAccountName' => 'sAMAccountName', 'uid' => 'uid', 'userPrincipalName' => 'userPrincipalName']);


// Mail system
define('WIKINDX_PHPMAILER_BACKENDS', ['smtp' => 'SMTP', 'sendmail' => 'Sendmail']);
define('WIKINDX_PHPMAILER_SMTP_ENCRYPT', ['none' => 'none', 'tls' => 'tls', 'ssl' => 'ssl']);


// Divers
define('WIKINDX_BROWSEBIBLIOGRAPHY_DEFAULT', FALSE);
define('WIKINDX_CMS_TAG_DEFAULT', FALSE);
define('WIKINDX_DISPLAY_BIBTEX_LINK_DEFAULT', FALSE);
define('WIKINDX_DISPLAY_CMS_LINK_DEFAULT', FALSE);
define('WIKINDX_HOMEBIB_DEFAULT', FALSE);
define('WIKINDX_SUPERADMIN_ID', 1);
define('WIKINDX_TAG_FACTOR_MAX', 200);
define('WIKINDX_TAG_FACTOR_MIN', 50);
define('WIKINDX_TAG_FACTOR_STEP', 5);
define('WIKINDX_TEMPLATE_MENU_DEFAULT', 0);
define('WIKINDX_UNIX_PERMS_DEFAULT', 0777);
define('WIKINDX_USE_BIBTEX_KEY_DEFAULT', FALSE);
define('WIKINDX_USE_WIKINDX_KEY_DEFAULT', FALSE);
define('WIKINDX_DISPLAY_RESOURCE_STATISTICS_DEFAULT', FALSE);
define('WIKINDX_SESSION_NAME', 'WKXSESSID');
define('WIKINDX_SESSION_GC_FREQUENCY', 900); // 15 min = 60 * 15, because the PCI DSS 3.1 recommend it and some sysadmin will ask for it.
define('WIKINDX_SESSION_MAXLIFETIME_UPPER_LIMIT', 86400); // 24h = 60 * 60 * 24 s, for a reasonable lifetime of a session that spans two working days.

/**
 * MySQL GLOBAL max_allowed_packet option
 *
 * Support the largest fields size used (LONGTEXT)
 *
 * According to https://dev.mysql.com/doc/refman/8.0/en/blob.html,
 * max_allowed_packet need to be as large as the largest type of column stored
 * and we use LONGTEXT so we need the maximum value allowed 1G (in the absence of 4G).
 *
 * @see https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html#sysvar_max_allowed_packet
 *
 * @global int WIKINDX_DB_MAX_ALLOWED_PACKET
 */
define('WIKINDX_DB_MAX_ALLOWED_PACKET', 1073741824);

/**
 * MySQL SESSION group_concat_max_len option
 *
 * Avoid truncation on search operations
 *
 * According to https://dev.mysql.com/doc/refman/8.0/en/aggregate-functions.html#function_group-concat,
 * group_concat_max_len is constrained by the value of max_allowed_packet.
 *
 * @see https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html#sysvar_max_allowed_packet
 *
 * @global int WIKINDX_DB_GROUP_CONCAT_MAX_LEN
 */
define('WIKINDX_DB_GROUP_CONCAT_MAX_LEN', WIKINDX_DB_MAX_ALLOWED_PACKET);

/**
 * MySQL SESSION sql_mode option
 *
 * Set the strictest SQL mode to avoid errors
 *
 * @see https://mariadb.com/kb/en/sql-mode/#traditional
 *
 * @global string WIKINDX_DB_SQL_MODE
 */
define('WIKINDX_DB_SQL_MODE', 'TRADITIONAL');

/**
 * MySQL engine option
 *
 * Default storage engine
 *
 * @see https://dev.mysql.com/doc/refman/8.0/en/innodb-introduction.html
 *
 * @global string WIKINDX_DB_ENGINE
 */
define('WIKINDX_DB_ENGINE', 'InnoDB');

/**
 * MySQL CHARSET/NAMES option
 *
 * Full UTF-8 support (4 bytes)
 *
 * @see https://mariadb.com/kb/en/supported-character-sets-and-collations/
 *
 * @global string WIKINDX_DB_CHARSET
 */
define('WIKINDX_DB_CHARSET', 'utf8mb4');

/**
 * MySQL COLLATE/COLLATION option
 *
 * Set the strictest SQL mode to avoid errors
 *
 * @see https://mariadb.com/kb/en/setting-character-sets-and-collations/
 *
 * @global string WIKINDX_DB_COLLATION
 */
define('WIKINDX_DB_COLLATION', 'utf8mb4_unicode_520_ci');


// Divers for users config only
define('WIKINDX_USER_PAGING_STYLE_DEFAULT', 'N');
define('WIKINDX_USER_LANGUAGE_DEFAULT', 'auto');


// Syndication Feed (RSS/ATOM)
define('WIKINDX_RSS_PAGE',  '/index.php?action=rss_RSS_CORE&amp;method=rss20');
define('WIKINDX_ATOM_PAGE', '/index.php?action=rss_RSS_CORE&amp;method=atom10');


// CMS API
define('WIKINDX_CMS_PAGE', '/index.php?action=cms_CMS_CORE');


/**
 * URL of the website sitemap
 *
 * @global string WIKINDX_SITEMAP_PAGE
 */
define('WIKINDX_SITEMAP_PAGE', '/index.php?action=sitemap_SITEMAP_CORE');
/**
 * Max number of url by page in a sitemap (50000 max. allowed in the standard and 10 Mo max.)
 *
 * For a response time and analysis of the response 500 links per page seems reasonable, ie below the second, as the search engines expect..
 *
 * @global int WIKINDX_SITEMAP_MAX_SIZE
 */
define('WIKINDX_SITEMAP_MAX_SIZE', 500);

/**
 * STATISTICS WEIGHTS
 *
 * The two values here are relative to each other and should total 1.0.
 */
define('WIKINDX_POPULARITY_VIEWS_WEIGHT', 0.25);
define('WIKINDX_POPULARITY_DOWNLOADS_WEIGHT', 0.75);


// Bug(LkpPo), 2021-04-19, The hard upper limit is 1G due to a bug on Windows
// cf. https://bugs.php.net/bug.php?id=79423
// Fixed in PHP 7.3.24 and 7.4.12
define('WIKINDX_FILE_ATTACH_SIZE_UPPER_LIMIT', 1024); // 1G = 1024M


// Bug(LkpPo), 2021-04-19, The hard upper limit is 1G due to a bug on Windows
// cf. https://bugs.php.net/bug.php?id=79423
// Fixed in PHP 7.3.24 and 7.4.12
define('WIKINDX_IMG_SIZE_UPPER_LIMIT', 200); // 200M, max file size of an 8K 24 bits colors bmp image
