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
 * CONSTANTS_CONFIG_DEFAULT
 *
 * Declare the default values of global config options.
 *
 * @package wikindx\core\startup
 */


// Database
define('WIKINDX_DB_DEFAULT', 'wikindx5');
define('WIKINDX_DB_HOST_DEFAULT', 'localhost');
define('WIKINDX_DB_PASSWORD_DEFAULT', 'wikindx');
define('WIKINDX_DB_USER_DEFAULT', 'wikindx');


// System
define('WIKINDX_MAX_EXECUTION_TIMEOUT_DEFAULT', FALSE);
define('WIKINDX_MAX_WRITECHUNK_DEFAULT', 10000);
define('WIKINDX_MEMORY_LIMIT_DEFAULT', FALSE);
define('WIKINDX_PATH_AUTO_DETECTION_DEFAULT', TRUE);
define('WIKINDX_URL_BASE_DEFAULT', FALSE);
define('WIKINDX_BIBUTILS_PATH_DEFAULT', ''); // For writing to the DB
define('WIKINDX_BIBUTILS_UNIXPATH_DEFAULT', '/usr/local/bin/'); // Default path for *NIX, searched if WIKINDX_BIBUTILS_PATH_DEFAULT is ''


// Front page
define('WIKINDX_CONTACT_EMAIL_DEFAULT', '');
define('WIKINDX_DESCRIPTION_DEFAULT', 'My WIKINDX');
define('WIKINDX_LAST_CHANGES_DAY_LIMIT_DEFAULT', 10);
define('WIKINDX_LAST_CHANGES_DEFAULT', 10);
define('WIKINDX_LAST_CHANGES_TYPE_DEFAULT', 'number');


// Resource lists
define('WIKINDX_LIST_LINK_DEFAULT', 0);
define('WIKINDX_NO_SORT_DEFAULT', serialize(['an', 'a', 'the', 'der', 'die', 'das', 'ein', 'eine', 'einer', 'eines', 'le', 'la', 'las', 'il', 'les', 'une', 'un', 'una', 'uno', 'lo', 'los', 'i', 'gli', 'de', 'het', 'um', 'uma', 'o', 'os', 'as', 'den', 'det', 'en', 'et', ]));
define('WIKINDX_PAGING_DEFAULT', 20);
define('WIKINDX_PAGING_MAXLINKS_DEFAULT', 11);
define('WIKINDX_PAGING_TAG_CLOUD_DEFAULT', 100);
define('WIKINDX_SEARCH_FILTER_DEFAULT', serialize(['an', 'a', 'the', 'and', 'to']));


// Images
// Bug(LkpPo), 2021-04-19, The hard upper limit is 1G due to a bug on Windows
// cf. https://bugs.php.net/bug.php?id=79423
// Fixed in PHP 7.3.24 and 7.4.12
define('WIKINDX_IMG_SIZE_UPPER_LIMIT', 200); // 200M, max file size of an 8K 24 bits colors bmp image
define('WIKINDX_IMG_ALLOW_DEFAULT', FALSE);
define('WIKINDX_IMG_HEIGHT_LIMIT_DEFAULT', 400);
define('WIKINDX_IMG_UPLOAD_MAX_SIZE_DEFAULT', 5);
define('WIKINDX_IMG_WIDTH_LIMIT_DEFAULT', 400);


/** Default language */
define('WIKINDX_LANGUAGE_DEFAULT', 'en_GB');
define('WIKINDX_STRING_LIMIT_DEFAULT', 40);
define('WIKINDX_STYLE_DEFAULT', 'apa');
define('WIKINDX_TAG_HIGH_COLOUR_DEFAULT', 'ff0000');
define('WIKINDX_TAG_HIGH_FACTOR_DEFAULT', 200);
define('WIKINDX_TAG_LOW_COLOUR_DEFAULT', 'a0a0a0');
define('WIKINDX_TAG_LOW_FACTOR_DEFAULT', 100);
define('WIKINDX_TEMPLATE_DEFAULT', 'default');


/** Default time zone (UTC) */
define('WIKINDX_TIMEZONE_DEFAULT', 'UTC');
define('WIKINDX_TITLE_DEFAULT', 'WIKINDX');


// References
define('WIKINDX_DEACTIVATE_RESOURCE_TYPES_DEFAULT', serialize([]));


// Users
define('WIKINDX_DENY_READONLY_DEFAULT', FALSE);
define('WIKINDX_DISPLAY_STATISTICS_DEFAULT', FALSE);
define('WIKINDX_DISPLAY_USER_STATISTICS_DEFAULT', FALSE);
define('WIKINDX_EMAIL_NEW_REGISTRATIONS_DEFAULT', '');
define('WIKINDX_GLOBAL_EDIT_DEFAULT', FALSE);
define('WIKINDX_IMPORT_BIB_DEFAULT', FALSE);
define('WIKINDX_METADATA_ALLOW_DEFAULT', TRUE);
define('WIKINDX_METADATA_USERONLY_DEFAULT', FALSE);
define('WIKINDX_MULTIUSER_DEFAULT', FALSE);
define('WIKINDX_ORIGINATOR_EDIT_ONLY_DEFAULT', FALSE);
define('WIKINDX_QUARANTINE_DEFAULT', FALSE);
define('WIKINDX_READ_ONLY_ACCESS_DEFAULT', TRUE);
define('WIKINDX_USER_REGISTRATION_DEFAULT', FALSE);
define('WIKINDX_USER_REGISTRATION_MODERATE_DEFAULT', FALSE);


// Builtin Auth
define('WIKINDX_AUTHGATE_MESSAGE_DEFAULT', '');
define('WIKINDX_AUTHGATE_RESET_DEFAULT', FALSE);
define('WIKINDX_AUTHGATE_USE_DEFAULT', FALSE);
define('WIKINDX_PASSWORD_SIZE_DEFAULT', 6);
define('WIKINDX_PASSWORD_STRENGTH_DEFAULT', 'strong');


// LDAP Auth
define('WIKINDX_LDAP_GROUP_DN_DEFAULT', '');
define('WIKINDX_LDAP_PORT_DEFAULT', 389);
define('WIKINDX_LDAP_SEARCH_METHOD_DEFAULT', 'tree');
define('WIKINDX_LDAP_SEARCH_OPERATOR_DEFAULT', 'and');
define('WIKINDX_LDAP_SERVER_BIND_DOMAIN_DEFAULT', '');
define('WIKINDX_LDAP_SERVER_BIND_DOMAIN_FORMAT_DEFAULT', 'upn');
define('WIKINDX_LDAP_SERVER_BIND_LOGIN_DEFAULT', '');
define('WIKINDX_LDAP_SERVER_BIND_PASSWORD_DEFAULT', '');
define('WIKINDX_LDAP_SERVER_BIND_TYPE_DEFAULT', 'anonymous');
define('WIKINDX_LDAP_SERVER_DEFAULT', 'localhost');
define('WIKINDX_LDAP_SERVER_ENCRYPTION_DEFAULT', 'none');
define('WIKINDX_LDAP_USE_DEFAULT', FALSE);
define('WIKINDX_LDAP_USER_ATTRIBUTE_LOGIN_DEFAULT', 'sAMAccountName');
define('WIKINDX_LDAP_USER_CREATE_DEFAULT', TRUE);
define('WIKINDX_LDAP_USER_OU_DEFAULT', '');


// Mail system
define('WIKINDX_EMAIL_NEWS_DEFAULT', FALSE);
define('WIKINDX_EMAIL_STATISTICS_DEFAULT', FALSE);
define('WIKINDX_MAIL_BACKEND_DEFAULT', 'sendmail');
define('WIKINDX_MAIL_FROM_DEFAULT', '');
define('WIKINDX_MAIL_REPLYTO_DEFAULT', 'noreply@noreply.org');
define('WIKINDX_MAIL_RETURN_PATH_DEFAULT', '');
define('WIKINDX_MAIL_SENDMAIL_PATH_DEFAULT', '/usr/sbin/sendmail');
define('WIKINDX_MAIL_SMTP_AUTH_DEFAULT', FALSE);
define('WIKINDX_MAIL_SMTP_ENCRYPT_DEFAULT', 'none');
define('WIKINDX_MAIL_SMTP_PASSWORD_DEFAULT', '');
define('WIKINDX_MAIL_SMTP_PERSIST_DEFAULT', FALSE);
define('WIKINDX_MAIL_SMTP_PORT_DEFAULT', '25');
define('WIKINDX_MAIL_SMTP_SERVER_DEFAULT', 'localhost');
define('WIKINDX_MAIL_SMTP_USERNAME_DEFAULT', '');
define('WIKINDX_MAIL_USE_DEFAULT', FALSE);
define('WIKINDX_NOTIFY_DEFAULT', FALSE);


// Files
// Bug(LkpPo), 2021-04-19, The hard upper limit is 1G due to a bug on Windows
// cf. https://bugs.php.net/bug.php?id=79423
// Fixed in PHP 7.3.24 and 7.4.12
define('WIKINDX_FILE_ATTACH_SIZE_UPPER_LIMIT', 1024); // 1G = 1024M
define('WIKINDX_FILE_ATTACH_UPLOAD_MAX_SIZE_DEFAULT', 5);
define('WIKINDX_FILE_DELETE_SECONDS_DEFAULT', 3600);
define('WIKINDX_FILE_ATTACH_ALLOW_DEFAULT', FALSE);
define('WIKINDX_FILE_VIEW_LOGGEDON_ONLY_DEFAULT', FALSE);


// RSS feed
define('WIKINDX_RSS_ALLOW_DEFAULT', FALSE);
define('WIKINDX_RSS_BIBSTYLE_DEFAULT', WIKINDX_STYLE_DEFAULT);
define('WIKINDX_RSS_DESCRIPTION_DEFAULT', WIKINDX_DESCRIPTION_DEFAULT);
define('WIKINDX_RSS_DISPLAY_DEFAULT', FALSE);
define('WIKINDX_RSS_LIMIT_DEFAULT', 10);
define('WIKINDX_RSS_TITLE_DEFAULT', WIKINDX_TITLE_DEFAULT);


// CMS API
define('WIKINDX_CMS_ALLOW_DEFAULT', FALSE);
define('WIKINDX_CMS_BIBSTYLE_DEFAULT', WIKINDX_STYLE_DEFAULT);
define('WIKINDX_CMS_DB_PASSWORD_DEFAULT', '');
define('WIKINDX_CMS_DB_USER_DEFAULT', '');
define('WIKINDX_CMS_SQL_DEFAULT', FALSE);


// Google Scholar
define('WIKINDX_GS_ALLOW_DEFAULT', FALSE);
define('WIKINDX_GS_ATTACHMENT_DEFAULT', FALSE);


// Divers
define('WIKINDX_SITEMAP_ALLOW_DEFAULT', FALSE);
define('WIKINDX_RESTRICT_USERID_DEFAULT', 0);
define('WIKINDX_MAX_PASTE_DEFAULT', 10);
define('WIKINDX_RESOURCE_URL_PREFIX_DEFAULT', '');
define('WIKINDX_BROWSER_TAB_ID_DEFAULT', FALSE);
define('WIKINDX_IMPRESSUM_DEFAULT', '');
define('WIKINDX_SESSION_GC_LASTEXEC_TIMESTAMP_DEFAULT', time()); // Now in UTC
define('WIKINDX_SESSION_AUTH_MAXLIFETIME_DEFAULT', 86400); // 24h = 60 * 60 * 24 s, for a reasonable lifetime of a session that spans two working days.
define('WIKINDX_SESSION_NOTAUTH_MAXLIFETIME_DEFAULT', 3600); // 1h = 60 * 60 s, that seems a lot of idle time for reading and searching resources.


// Debugging
/**
 * Default values of config.php file
 * We keep here to check them at load time
 * NB: PHP 7 only can define a constant array
 */
define('WIKINDX_BYPASS_SMARTY_COMPILATION_DEFAULT', FALSE);
define('WIKINDX_DEBUG_ERRORS_DEFAULT', FALSE);
define('WIKINDX_DEBUG_SQL_DEFAULT', FALSE);
define('WIKINDX_IS_TRUNK_DEFAULT', FALSE);


// Others
define('WIKINDX_STATISTICS_COMPILED_DEFAULT', '2018-01-01 01:01:01');
