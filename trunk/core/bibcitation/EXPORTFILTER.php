<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @copyright Andrea Rossato
 * @copyright Mark Grimshaw-Aagaard <sirfragalot@users.sourceforge.net>
 * @copyright Stéphane Aulery <lkppo@users.sourceforge.net>
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * Format a bibliographic resource for output.
 *
 * @package wikindx\core\bibcitation
 */
class EXPORTFILTER
{
    /** string */
    public $newline;
    /** object */
    private $bibformat;
    /** string */
    private $format;

    /**
     * EXPORTFILTER
     *
     * @param object $ref
     * @param string $output 'html', plain', 'rtf'. Default is 'html'
     */
    public function __construct(&$ref, $output)
    {
        $this->bibformat = &$ref;
        $this->format = $output;
        // New line (used in CITEFORMAT::endnoteProcess)
        // Also, bibliographic/footnote templates may have the special string 'NEWLINE'
        if ($this->format == 'rtf') {
            $this->newLine = "\\par\\qj ";
        } elseif ($this->format == 'html') { // 'html'
            $this->newLine = BR;
        } else {
            $this->newLine = "" . LF;
        }
    }
    /**
     * Format for HTML or RTF/plain?
     *
     * @param string $data Input string
     * @param bool $htmlDone Default FALSE (deprecated)
     *
     * @return string
     */
    public function format($data, $htmlDone = FALSE)
    {
        if (!$data) {
            return $data;
        }
        if ($this->format == 'html') {
            /**
             * Scan for search patterns and highlight accordingly
             */
            /**
             * Temporarily replace any URL - works for just one URL in the output string.
             */
            if (preg_match("/(<a.*>.*<\\/a>)/ui", $data, $match)) {
                $url = preg_quote($match[1], '/');
                $data = preg_replace("/$url/u", "OSBIB__URL__OSBIB", $data);
            } else {
                $url = FALSE;
            }
            $data = str_replace("\"", "&quot;", $data);
            if (!$htmlDone) {
                //				$data = str_replace("<", "&lt;", $data);
//				$data = str_replace(">", "&gt;", $data);
//				$data = preg_replace("/&(?![a-zA-Z0-9#]+?;)/u", "&amp;", $data);
            }
            // This double replace stops stops the search term 'ass' being used to replace the 'ass' in 'class'
            //			$data = $this->bibformat->patterns ?
            //				preg_replace($this->bibformat->patterns, "<span class=\"" . $this->bibformat->patternHighlight . "\">$1</span>", $data) : $data;
            $data = preg_replace("/\\[b\\](.*?)\\[\\/b\\]/uis", "<strong>$1</strong>", $data);
            $data = preg_replace("/\\[i\\](.*?)\\[\\/i\\]/uis", "<em>$1</em>", $data);
            $data = preg_replace("/\\[sup\\](.*?)\\[\\/sup\\]/uis", "<sup>$1</sup>", $data);
            $data = preg_replace("/\\[sub\\](.*?)\\[\\/sub\\]/uis", "<sub>$1</sub>", $data);
            $data = preg_replace("/\\[u\\](.*?)\\[\\/u\\]/uis", "<span style=\"text-decoration: underline;\">$1</span>", $data);
            // Recover any URL
            if ($url) {
                $data = str_replace("OSBIB__URL__OSBIB", $match[1], $data);
            }
            $data = str_replace("WIKINDX_NDASH", "&ndash;", $data);
            $data = str_replace("NEWLINE", $this->newLine, $data);
            if ($this->bibformat->patterns) {
                $data = preg_replace($this->bibformat->patterns, \HTML\span("$1", "highlight"), $data);
                //				$data = preg_replace($this->bibformat->patterns,
//					"W!I!K!I!N!D!X!$1W!I!K!I!N!D!X!", $data);
//				$data = preg_replace("/W!I!K!I!N!D!X!(.*)W!I!K!I!N!D!X!/Uu",
//					"<span class=\"" . $this->bibformat->patternHighlight . "\">$1</span>", $data);
            }
        } elseif ($this->format == 'rtf') {
            $data = preg_replace("/&#(.*?);/u", "\\u$1", $data);
            $data = preg_replace("/\\[b\\](.*?)\\[\\/b\\]/uis", "{{\\b $1}}", $data);
            $data = preg_replace("/\\[i\\](.*?)\\[\\/i\\]/uis", "{{\\i $1}}", $data);
            $data = preg_replace("/\\[u\\](.*?)\\[\\/u\\]/uis", "{{\\ul $1}}", $data);
            $data = preg_replace("/\\[sup\\](.*?)\\[\\/sup\\]/uis", "{{\\super $1}}", $data);
            $data = preg_replace("/\\[sub\\](.*?)\\[\\/sub\\]/uis", "{{\\sub $1}}", $data);
            $data = str_replace("WIKINDX_NDASH", "\\u8212\\'14", $data);
            $data = str_replace("NEWLINE", $this->newLine, $data);
        }
        /**
         * 'noScan' means do nothing (leave BBCodes intact)
         */
        elseif ($this->format == 'noScan') {
            $data = str_replace("WIKINDX_NDASH", "-", $data);
            $data = str_replace("NEWLINE", $this->newLine, $data);

            return $data;
        }
        /**
         * StripBBCode for 'plain'.
         */
        else {
            $data = preg_replace("/\\[.*\\]|\\[\\/.*\\]/Uu", "", $data);
            $data = str_replace("WIKINDX_NDASH", "-", $data);
            $data = str_replace("NEWLINE", $this->newLine, $data);
        }

        return $data;
    }
}
