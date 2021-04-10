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
 * Set a db handler for session storage in wkx_session table
 *
 * The creation of the identifiers is left to PHP.
 *
 * cf. https://www.php.net/manual/en/function.session-set-save-handler.php
 *
 * @package wikindx\core\sessiondbhandler
 */
function wkx_session_set_db_handler()
{
    session_set_save_handler(
        "wkx_session_open",
        "wkx_session_close",
        "wkx_session_read",
        "wkx_session_write",
        "wkx_session_destroy",
        "wkx_session_gc"
    );
}

/**
 * Set the default file handler for session storage
 *
 * @package wikindx\core\sessiondbhandler
 */
function wkx_session_set_file_handler()
{
    session_set_save_handler(new SessionHandler());
}


/*
 * The open callback works like a constructor in classes and is executed when the session is being opened.
 *
 * It is the first callback function executed when the session is started automatically or manually with session_start().
 * Return value is true for success, false for failure.
 *
 * @param string $savePath
 * @param string $sessionName
 *
 * @return bool
 */
function wkx_session_open(string $savePath, string $sessionName) : bool
{
    return TRUE;
}

/*
 * The close callback works like a destructor in classes and is executed after the session write callback has been called.
 *
 * It is also invoked when session_write_close() is called.
 * Return value should be true for success, false for failure.
 *
 * @return bool
 */
function wkx_session_close() : bool
{
    return TRUE;
}

/*
 * The read callback must always return a session encoded (serialized) string, or an empty string if there is no data to read.
 *
 * This callback is called internally by PHP when the session starts or when session_start() is called.
 * Before this callback is invoked PHP will invoke the open callback.
 *
 * The value this callback returns must be in exactly the same serialized format that was originally passed for storage
 * to the write callback. The value returned will be unserialized automatically by PHP and used to populate the $_SESSION superglobal.
 * While the data looks similar to serialize() please note it is a different format which is specified in the session.serialize_handler ini setting. 
 *
 * @param string $sessionId
 *
 * @return string
 */
function wkx_session_read(string $sessionId) : string
{
    $db = FACTORY_DB::getInstance();
    
    $db->formatConditions(["sessionId" => $sessionId]);
    $data = $db->selectFirstField("session", "sessionData");
    
    return is_string($data) ? $data : "";
}

/*
 * The write callback is called when the session needs to be saved and closed.
 *
 * This callback receives the current session ID a serialized version the $_SESSION superglobal.
 * The serialization method used internally by PHP is specified in the session.serialize_handler ini setting.
 *
 * The serialized session data passed to this callback should be stored against the passed session ID.
 * When retrieving this data, the read callback must return the exact value that was originally passed to the write callback.
 *
 * This callback is invoked when PHP shuts down or explicitly when session_write_close() is called.
 * Note that after executing this function PHP will internally execute the close callback.
 *
 * @param string $sessionId
 * @param string $sessionData
 *
 * @return bool
 */
function wkx_session_write(string $sessionId, string $sessionData) : bool
{
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? "";
    $isbot = FALSE;
    
    // Don't store the session data for the most popular Web crawlers
    // cf. https://www.keycdn.com/blog/web-crawlers
    foreach ([
        // Google: https://developers.google.com/search/docs/advanced/crawling/overview-google-crawlers
        "APIs-Google",
        "Googlebot",
        "AdsBot-Google",
        "AdsBot-Google-Mobile",
        "AdsBot-Google-Mobile-Apps",
        "DuplexWeb-Google",
        "FeedFetcher-Google",
        "Google Favicon",
        "Google-Read-Aloud",
        "googleweblight",
        "Mediapartners-Google",
        "Storebot-Google",
        // Bing: https://www.bing.com/webmasters/help/which-crawlers-does-bing-use-8c184ec0
        "adidxbot",
        "bingbot",
        "BingPreview",
        // Yahoo!: https://help.yahoo.com/kb/SLN22600.html
        "Slurp",
        // Apple: https://seoapi.com/applebot/
        "Applebot",
        // DuckDuckGo: https://help.duckduckgo.com/duckduckgo-help-pages/results/duckduckbot/
        "DuckDuckBot",
        // Baidu: http://www.baiduguide.com/baidu-spider/
        "Baiduspider",
        // Yandex: https://yandex.com/support/webmaster/robot-workings/check-yandex-robots.html
        "Yandex",
        // Sogou: https://seoapi.com/sogouwebspider/#utm_source=sogou.dev
        "sogou",
        // Exabot: https://www.exalead.com/search/webmasterguide
        "Exabot",
        // Facebook: https://developers.facebook.com/docs/sharing/webmasters/crawler
        "facebook",
        // Alexa: https://support.alexa.com/hc/en-us/articles/200450194-Alexa-s-Web-and-Site-Audit-Crawlers
        "ia_archiver"
    ] as $botua)
    {
        if (strstr($ua, $botua))
        {
            $isbot = TRUE;
            break;
        }
    }
    
    if (!$isbot)
    {
        $db = FACTORY_DB::getInstance();
        $sql = "
            INSERT INTO " . $db->formatTables("session") . " (sessionId, sessionData)
            VALUES (" . $db->tidyInput($sessionId) . ", " . $db->tidyInput($sessionData) . ")
            ON DUPLICATE KEY UPDATE
                sessionId = " . $db->tidyInput($sessionId) . ",
                sessionData= " . $db->tidyInput($sessionData) . ";
        ";
        
        $db->query($sql);
    }
    
    return TRUE;
}

/*
 * This callback is executed when a session is destroyed with session_destroy() or with session_regenerate_id() with the destroy parameter set to true.
 *
 * Return value should be true for success, false for failure.
 *
 * @param string $sessionId
 *
 * @return bool
 */
function wkx_session_destroy(string $sessionId) : bool
{
    $db = FACTORY_DB::getInstance();
    
    $db->formatConditions(["sessionId" => $sessionId]);
    $db->delete("session");
    
    return TRUE;
}

/*
 * The garbage collector callback is invoked internally by PHP periodically in order to purge old session data.
 *
 * The frequency is controlled by session.gc_probability and session.gc_divisor.
 * The value of lifetime which is passed to this callback can be set in session.gc_maxlifetime.
 * Return value should be true for success, false for failure. 
 *
 * @param int $maxSessionLifetime (in seconds)
 *
 * @return bool
 */
function wkx_session_gc(int $maxSessionLifetime) : bool
{
    $db = FACTORY_DB::getInstance();
    
    $sql = "
        DELETE FROM " . $db->formatTables("session") . "
        WHERE DATE_ADD(sessionLastAccessTimestamp, INTERVAL " . $maxSessionLifetime . " SECOND) < CURRENT_TIMESTAMP();
    ";
    
    $db->query($sql);
    
    return TRUE;
}
