<?php
/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://www.isc.org/licenses/ ISC License
**********************************************************************************/
/**
*
* WIKINDX CONFIGURATION FILE
*
* NB. BEFORE YOU MAKE CHANGES TO THIS FILE, BACK IT UP!
* NB. BEFORE YOU MAKE CHANGES TO THIS FILE, BACK IT UP!
* NB. BEFORE YOU MAKE CHANGES TO THIS FILE, BACK IT UP!
*
* If you make changes, backup the edited file as future upgrades of WIKINDX might overwrite this file - no questions asked!
*/

/**********************************************************************************/

class CONFIG
{
/*****
* START DATABASE CONFIGURATION
*****/
// NB:
// wikindx supports only MySQL with mysqli PHP driver (WIKINDX_DB_TYPE parameter is deprecated).
//
// The database and permissions for accessing it must be created using your RDBMS client. Wikindx
// will NOT do this for you.  If unsure how to do this, contact your server admin. After you have
// set up an empty database with the correct permissions (GRANT ALL), the first running of Wikindx
// will create the necessary database tables.
//
// WIKINDX uses caching in the database _cache table for lists of creators, keywords etc.  If you have a large
// database, you may get SQL errors as WIKINDX attempts to write these cache data.  You will need to increase
// max allowed packet in my.cnf and restart the MySQL server.
//
// Host on which the relational db management system (i.e. the MySQL server) is running (usually localhost if
// the web files are on the same server as the RDBMS although some web hosting services may specify something like
// localhost:/tmp/mysql5.sock).
// If your DB server is on a non-standard socket (i.e. not port 3306), then you should set something like localhost:xxxx
// where 'xxxx' is the non-standard socket.
public $WIKINDX_DB_HOST = "localhost";
// name of the database which these scripts interface with (case-sensitive):
public $WIKINDX_DB = "wikindx6";
// username and password required to connect to and open the database
// (it is strongly recommended that you change these default values):
public $WIKINDX_DB_USER = "wikindx";
public $WIKINDX_DB_PASSWORD = "wikindx";
/*****
* END DATABASE CONFIGURATION
*****/

/**********************************************************************************/

/*****
* START PATHS CONFIGURATION
*****/
// The auto-detection of the path installation and the base url is an experimental feature
// which you can disable by changing this parameter to FALSE.
// If you deactivate auto-detection you must fill in the option WIKINDX_URL_BASE.
// If you don't define this option, auto-detection is enabled by default.
public $WIKINDX_PATH_AUTO_DETECTION = TRUE;
// If option auto-detection is disabled you must define the base URL for the WIKINDX installation.
// You have to indicate protocol HTTP / HTTPS and remove the terminal /.
// e.g. if wikindx's index.php file is in /wikindx/ under the httpd/ (or similar)
// folder on the www.myserver.com, then set the variable
// to http://www.myserver.com/wikindx
// Otherwise, leave as "".
public $WIKINDX_URL_BASE = "";
/*****
* END PATHS CONFIGURATION
*****/

/**********************************************************************************/

/*****
* START PHP MEMORY AND EXECUTION CONFIGURATION
*****/
// WIKINDX usually runs with the standard PHP memory_limit of 64MB.
// With some PHP configurations, however, this is not enough -- a mysterious blank page is often the result.
// If you are unable to update php.ini's memory_limit yourself, WIKINDX_MEMORY_LIMIT may be set (an integer such as 64 or 128 followed by 'M').
// Despite the PHP manual stating that this may not be set outside of php.ini, it seems to work most of the time.
// It is not, however, guaranteed to do so and editing php.ini is the preferred method particularly if your PHP is in 'safe' mode.
// Use double quotes around the value.
public $WIKINDX_MEMORY_LIMIT = "2048M";
// WIKINDX should run fine with the PHP standard execution timeouts (typically 30 seconds) but,
// in some cases such as database upgrading of a large database on a slow server, you will need to increase the timeout figure.
// If this is FALSE, the value set in php.ini is used.
// Despite the PHP manual stating that this may not be set outside of php.ini, it seems to work most of the time.
// It is not, however, guaranteed to do so and editing php.ini is the preferred method particularly if your PHP is in 'safe' mode.
// The value is in seconds.
// Do NOT use quotes around the value.
public $WIKINDX_MAX_EXECUTION_TIMEOUT = FALSE;
// WIKINDX_MAX_WRITECHUNK concerns how many resources are exported and written to file in one go.
// If your WIKINDX contains several thousands of resources and you wish to export them all (e.g. to bibTeX or Endnote),
// then you may run into memory problems which will manifest as either
// a blank page when you attempt to export or an error report (if you have error reporting turned on).
// WIKINDX_MAX_WRITECHUNK breaks down the SQL querying of resources and subsequent writing of resources to file into manageable chunks.
// As a rough guide, with a WIKINDX_MEMORY_LIMIT of 64MB, WIKINDX_MAX_WRITECHUNK of 700 should work fine and with 64M, 1500 works fine.
// If WIKINDX_MAX_WRITECHUNK is FALSE, the chunk is set to 10,000.
// This can be a tricky figure to set as setting the figure too low increases SQL and PHP execution times significantly.
// Do NOT use quotes around the value.
public $WIKINDX_MAX_WRITECHUNK = FALSE;
/*****
* END PHP MEMORY AND EXECUTION CONFIGURATION
*****/
}
