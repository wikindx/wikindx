<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * URL
 *
 * Common methods for handling URLs.
 *
 * @package wikindx\core\urls
 */
class URL
{
    /** object */
    private $session;

    /**
     * URL
     */
    public function __construct()
    {
        $this->session = FACTORY_SESSION::getInstance();
    }
    /**
     * grab URLs from provided db field value
     *
     * Function does: unserialize(base64_decode($field))
     *
     * @param string $field
     *
     * @return array
     */
    public function getUrls($field)
    {
        $array = unserialize(base64_decode($field));
        if (!is_array($array))
        {
            $array = []; // empty array
        }

        return $array;
    }
    /**
     * reduce the size of long URL to keep web browser display tidy
     *
     * @param string $text
     * @param int|FALSE $limit Default is FALSE
     *
     * @return string
     */
    public function reduceUrl($text, $limit = FALSE)
    {
        if (!$limit)
        {
            $limit = $this->session->getVar("setup_StringLimit");
        }
        if (($limit != -1) && (($count = mb_strlen($text)) > $limit))
        {
            $start = floor(($limit / 2) - 2);
            $length = $count - (2 * $start);
            $text = UTF8::mb_substr_replace($text, " ... ", $start, $length);
        }

        return $text;
    }

    /**
     * Return the base url of the current website seen by the visitor
     *
     * @return string
     */
    public function getBaseUrl()
    {
        $hostName = str_replace(["\\", "//"], ["/", "/"], $_SERVER['PHP_SELF']);
        $hostName = $_SERVER['HTTP_HOST'] . str_replace('/index.php', '', $hostName);

        return $this->getCurrentProtocole() . '://' . $hostName;
    }

    /**
     * Return the base url of the current website seen by the visitor
     *
     * @return string
     */
    public function getCurrentProtocole()
    {
        if (
            (!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https')
            || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
            || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443'))
        {
            $protocole = 'https';
        }
        else
        {
            $protocole = 'http';
        }

        return $protocole;
    }
}
