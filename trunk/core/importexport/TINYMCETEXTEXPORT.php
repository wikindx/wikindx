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
 * TINYMCETEXTEXPORT
 *
 * @package wikindx\core\importexport
 *
 * Format and export text from TinyMCE to a range of external text formats
 */
abstract class TINYMCETEXTEXPORT
{
    /** sint */
    public $fontIndex = 0;
    /** object */
    protected $session;
    /** object */
    protected $cite;
    /** string */
    protected $lineSpacingIndentQ;
    /** string */
    protected $paperSize;
    /** string */
    protected $footnoteText = FALSE;
    /** string */
    protected $tableStyle = FALSE;
    /** string */
    protected $justify;
    /** string */
    protected $nested = 1;
    /** array */
    protected $fonts = [
        "andale mono",
        "arial",
        "arial black",
        "book antiqua",
        "comic sans ms",
        "courier new",
        "georgia",
        "helvetica",
        "impact",
        "symbol",
        "tahoma",
        "terminal",
        "times new roman",
        "trebuchet ms",
        "verdana",
        "webdings",
        "wingdings",
    ];
    /** array */
    protected $fontSizes = [
        'xx-small' => 16,
        'x-small' => 20,
        'small' => 24,
        'medium' => 28,
        'large' => 36,
        'x-large' => 48,
        'xx-large' => 72,
    ];
    /** array */
    protected $lists;
    /** string */
    protected $indentL;
    /** string */
    protected $indentR;
    /** int */
    protected $tableWidth;
    /** boolean */
    protected $isIE;
    /** string */
    protected $spanParse;
    /** array */
    protected $styleArray;

    /**
     * parse <span>...</span>
     *
     * @param string $text
     * @param array $callbackStyle
     *
     * @return string
     */
    public function parseSpan($text, $callbackStyle)
    {
        $this->spanParse = TRUE;
        $this->isIE = FALSE;
        $text = preg_replace_callback(
            "/<span\\s*style\\s*=\\s*\"(.*?)\">(.*?)<\\/span>/usi",
            $callbackStyle,
            $text
        );
        $text = preg_replace_callback(
            "/\\s*<p\\s*style\\s*=\\s*\"(.*?)\".*?[>]+(.*?)<\\/p>\\s*/usi",
            [$this, "styleCallback"],
            $text
        );
        // Sometimes, not all SPANs are caught - this removes them from the RTF until this problem can be fixed.
        $text = preg_replace("/\\s*<span.*?\\>(.*?)<\\/span>\\s*/usi", '$1', $text);
        if (preg_match("/<span.*?\\>.*?<\\/span>/usi", $text))
        { // deal with nested span tags
            $text = $this->parseSpan($text, $callbackStyle);
        }

        return $text;
    }
    /**
     * Create lists
     *
     * @param string $text
     * @param string $callbackUnorderedList
     * @param string $callbackOrderedList
     *
     * @return string
     */
    public function parseLists($text, $callbackUnorderedList, $callbackOrderedList)
    {
        $text = preg_replace_callback("/\\s*<ul.*>\\s*(.*)\\s*<\\/ul>\\s*/Uusi", $callbackUnorderedList, $text);
        $text = preg_replace_callback("/\\s*<ol.*>\\s*(.*)\\s*<\\/ol>\\s*/Uusi", $callbackOrderedList, $text);
        if (preg_match("/<ul.*?\\>.*?<\\/ul>/usi", $text))
        { // deal with nested lists
            $this->nested++;
            $text = $this->parseLists($text, $callbackUnorderedList, $callbackOrderedList);
            $this->nested--;
        }

        return $text;
    }

    /**
     * Initialize the class
     *
     * @param string $output
     */
    protected function initClass($output)
    {
        $this->session = FACTORY_SESSION::getInstance();
        $this->cite = FACTORY_CITE::getInstance($output);
        $this->styleArray = $this->cite->citeStyle->citeFormat->style;
    }
    /** create header of output file */
    abstract protected function header();
    /** create footer of output file */
    abstract protected function footer();
    /**
     * Parse text and convert
     *
     * @param string $text
     */
    abstract protected function parse($text);
    /**
     * parse <div>...</div>
     *
     * @param string $text
     *
     * @return string
     */
    protected function parseDiv($text)
    {
        $this->spanParse = TRUE;
        $this->isIE = FALSE;
        //		$text = preg_replace_callback("/\s*<div\s*style\s*=\s*\"(.*?)\">(.*?)<\/div>\s*/usi",
        $text = preg_replace_callback(
            "/\\s*<div\\s*style\\s*=\\s*\"(.*?)\".*?[>]+(.*?)<\\/div>\\s*/usi",
            [$this, "styleCallback"],
            $text
        );
        // IE prefers <P align = xxx>...</P> instead of DIV
        $text = preg_replace_callback(
            "/\\s*<p\\s*align\\s*=\\s*\"*(.*?)\"*[>]+(.*?)<\\/p>\\s*/usi",
            [$this, "paraCallback"],
            $text
        );
        $text = preg_replace_callback(
            "/\\s*<p\\s*style\\s*=\\s*\"(.*?)\".*?[>]+(.*?)<\\/p>\\s*/usi",
            [$this, "styleCallback"],
            $text
        );
        // Sometimes, not all DIVs are caught - this removes them from the RTF until this problem can be fixed.
        //		$text = preg_replace("/\s*<div.*?\>(.*?)<\/div>\s*/usi", '$1', $text);
        return $text;
    }
}
