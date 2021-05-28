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
 *	RICHTEXTFORMAT extends TINYMCETEXTEXPORT
 */
include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "modules", "importexport", "TINYMCETEXTEXPORT.php"]));

/**
 * RTF encoding
 *
 * NB - this produces just the bare minimum of RTF code to work in Word and OO.org
 *
 * @package wikindx\core\importexport
 */
class RICHTEXTFORMAT extends TINYMCETEXTEXPORT
{
    /** array */
    public $fontBlocks = [];
    /** array */
    public $fonttbl = [];
    /** string */
    public $fontBlock = FALSE;
    /** string */
    public $colourTable = FALSE;
    /**
     * array
     *
     * default black font and blue for hyperlinks
     */
    public $colourArray = [
        '\red0\green0\blue0',   // Black (text by default)
        '\red0\green0\blue255', // Blue (hyperlinks)
        '\red255\green0\blue0',  // Red
    ];
    /** string */
    public $lineSpacing;
    /** string */
    public $listTable;
    /** array */
    private $vars;
    /** string */
    private $listOverrideTable;
    /** string */
    private $listType;
    /** int */
    private $listId;
    /** int */
    private $listIndex = 2;
    /** int */
    private $listIndent = 720;
    /** int */
    private $listIndentExtra = 360;
    /** int */
    private $quoteNumWords;
    /** boolean */
    private $keepQuoteMarks;
    /** string */
    private $quoteFontSize;
    /** string */
    private $qme;
    /** string */
    private $qms;
    /** array */
    private $footnoteOffsetIds = [];

    /**
     * RICHTEXTFORMAT
     */
    public function __construct()
    {
        parent::initClass('rtf');
        $this->vars = GLOBALS::getVars();
        // set up defaults and parameters
        $this->init();
    }
    /**
     * Set some defaults and create the RTF opening tag
     *
     * @return string
     */
    public function header()
    {
        $this->lineSpacingIndentQ = LF . $this->lineSpacingIndentQ . LF;
        $text = '{\rtf1\ansi\ansicpg1252' . LF . LF;
        // 'letter' is default size for RTF -- presumably, 'legal' has the default width of 'letter'
        // The tables always go beyond the paper width so we set each table width to 96% of standard
        if ($this->paperSize == 'letter')
        {
            $this->tableWidth = floor(8748 * 0.96);
        }
        elseif ($this->paperSize == 'A4')
        {
            $text .= '\paperw11909\paperh16834' . LF . LF;
            $this->tableWidth = floor(8417 * 0.96);
        }
        elseif ($this->paperSize == 'A5')
        {
            $text .= '\paperw8395\paperh11909' . LF . LF;
            $this->tableWidth = floor(4903 * 0.96);
        }
        elseif ($this->paperSize == 'legal')
        {
            $text .= '\paperh20160' . LF . LF;
            $this->tableWidth = floor(8748 * 0.96);
        }
        elseif ($this->paperSize == 'executive')
        {
            $text .= '\paperw10440\paperh15120' . LF . LF;
            $this->tableWidth = floor(6948 * 0.96);
        }

        return $text . LF . LF;
    }
    /**
     * Create the RTF closing tag
     *
     * @return string
     */
    public function footer()
    {
        return LF . '}' . LF;
    }
    /**
     * Parse tinyMCE code to RTF tags
     *
     * @param string $text input text
     *
     * @return string parsed text
     */
    public function parse($text)
    {
        $text = $this->formatText($text);

        // handle <font>...</font> MAY NOT BE NEEDED
        //		$text = $this->parseFont($text, array($this, "styleCallback"));
        // handle <span>...</span>
        $text = $this->parseSpan($text, [$this, "styleCallback"]);
        $text = $this->parseLists($text, [$this, "callbackUnorderedList"], [$this, "callbackOrderedList"]);
        // Handle tables
        $text = $this->createTables($text);
        // Handle images
        $text = preg_replace_callback("/<img.*[>]+/Uusi", [$this, "imageCallback"], $text);
        // URL + emails
        $text = $this->createEmail($text);
        $text = $this->createFancyUrl($text);
        /*
        // Handle <div>...</div>
                $text = $this->rtf->parseDiv($text);
        // Ensure there are no hanging HTML tags (some browsers produce untidy HTML or superfluous HTML)
                $patterns = array(
                    "/<\/div>/iU",
                    "/<\/span>/iUu",
                    "/<\/font>/iUu",
                    );
                $text = preg_replace($patterns, '', $text);
        */
        // indent long quotations
        if (array_key_exists('exportIndentQuoteWords', $this->vars))
        {
            $quoteIndentWords = $this->vars['exportIndentQuoteWords'];
            if (is_numeric($quoteIndentWords))
            {
                settype($quoteIndentWords, "integer");
                if (array_key_exists('exportIndentQuoteFontSize', $this->vars))
                {
                    $indentQuoteFontSize = $this->vars['exportIndentQuoteFontSize'];
                }
                else
                {
                    $indentQuoteFontSize = 8; // font size 16 in RTF
                }
                if (($sizeKey = array_search($indentQuoteFontSize * 2, $this->fontSizes)) !== FALSE)
                {
                    $this->quoteFontSize = $this->fontSizes[$sizeKey];
                }
                else
                {
                    $this->quoteFontSize = $this->fontSizes['large']; // font size 9/18
                }
                if (array_key_exists('exportIndentQuoteMarks', $this->vars))
                {
                    $indentQuoteMarks = TRUE;
                }
                else
                {
                    $indentQuoteMarks = FALSE;
                }
                $text = $this->indentQuotations($text, $quoteIndentWords, $indentQuoteFontSize, $indentQuoteMarks);
                // Perhaps footnote immediately follows (so should be part of indent) -- NB indentQ() method in WPRTF.php
                $text = preg_replace_callback(
                    "/(\\[\\/cite\\][.,:;?!°ø])(\\s*__WIKINDX__NEWLINEPAR__}__WIKINDX__NEWLINEPAR____WIKINDX__QUOTEINDENTREMOVESPACE__\\}*)(\\[footnote\\].*\\[\\/footnote\\])/Uusi",
                    [$this, "callback_footnoteIndent"],
                    $text
                );
                //				$pattern = 	"__WIKINDX__QUOTEINDENTDONE__|__WIKINDX__NEWLINE__|__WIKINDX__QUOTEINDENTREMOVESPACE__(\s*)|__WIKINDX__QUOTEINDENTREMOVESPACE__\}*(\s*)";
                $text = preg_replace("/$pattern/u", LF, $text);
            }
        }
        // Deal with citations
        include_once(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE, "bibcitation", "CITE.php"]));
		$cite = new CITE('rtf');
        $text = $cite->parseCitations($text, 'rtf', FALSE, FALSE, TRUE);
        // handles section breaks
        $text = preg_replace(
            "/(__WIKINDX__NEWLINEPAR__)*__WIKINDX__SECTION__(__WIKINDX__NEWLINEPAR__)*/u",
            "\n\n{\\sect}\n\\sectd" . LF . LF,
            $text
        );
        // Replace temporary newlines
        $text = str_replace('__WIKINDX__NEWLINEPAR__', '\par' . LF, $text);
        //		$text = str_replace("__WIKINDX__NEWLINE__", LF, $text);
        $text = str_replace('&nbsp;', ' ', $text);
        //		$text = str_replace("__WIKINDX__QUOTEINDENTDONE__", LF, $text);
        $pattern = "__WIKINDX__QUOTEINDENTDONE__|__WIKINDX__NEWLINE__|__WIKINDX__QUOTEINDENTREMOVESPACE__(\\s*)";
        $text = preg_replace("/$pattern/u", LF, $text);
        // when user is cut 'n' pasting, superfluous codes are sometimes inserted so remove them
        $text = preg_replace("/[ ]*<span.*[>]+(.*)<\\/span[>]+[ ]*/Uusi", "$1", $text);
        // Deal with footnotes
        if (array_key_exists('exportFontSizeFt', $this->vars))
        {
            $fontSizeFt = $this->vars['exportFontSizeFt'];
        }
        else
        {
            $fontSizeFt = 8; // font size 16 in RTF
        }
        $text = $this->parseFootnotes($text, $this->footnoteOffsetIds, $fontSizeFt);
        if ($this->footnoteText)
        {
            $text .= $this->footnoteText;
        }
        $text = str_replace('__WIKINDX__ENDNOTE__START__', '', $text);
        $text = str_replace('__WIKINDX__ENDNOTE__END__', '', $text);
        $text = str_replace(' } ', ' }', $text);
        // Sometimes (not sure why), single quote gets replaced as &#039; so replace it
        $text = str_replace("&#039;", "'", $text);
        // sometimes, with footnote/endnote styles, __WIKINDX__ gets left behind.  Temp. FIX.
        $text = str_replace("__WIKINDX__\\par\\qj __", "", $text);

        if (count($this->fonttbl) > 0)
        {
            $this->fontBlock = '{\fonttbl' . LF;

            foreach ($this->fonttbl as $index => $font)
            {
                $this->fontBlock .= '{\f' . $index . '\fcharset0 ' . $font . ';}' . LF;
            }

            $this->fontBlock .= '}' . LF . LF;
        }

        $this->colourTable = '{\colortbl;';
        foreach ($this->colourArray as $colour)
        {
            $this->colourTable .= $colour . ';';
        }
        $this->colourTable .= '}' . LF . LF;

        $this->closeListTable();

        $text = $this->utf8_2_rtfansicpg1252($text);
        $textWithImg = '';

        // Insert images
        // Cut the string in smaller pieces to isolate hexfile name from other content
        $tString = preg_split('/(##hex[0-9a-zA-Z]+\.txt##)/u', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        // Write the ressource in the tempfile by chunk
        $k = 0;
        for ($k = 0; $k < count($tString); $k++)
        {
            $c = $tString[$k];

            // Is an image: replace hexfile names by the content of these files
            if (\UTILS\matchPrefix($c, '##hex'))
            {
                $c = str_replace('#', '', $c);
                $f = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE_FILES, $c]);
                if (file_exists($f))
                {
                    $c = file_get_contents($f);
                    @unlink($f);
                }
            }
            $textWithImg .= $c;
        }

        return $textWithImg;
    }
    /**
     * Format text for HTML characters
     *
     * @param string $text
     * @param string $protectCurlyBracket
     *
     * @return string
     */
    public function formatText($text, $protectCurlyBracket = TRUE)
    {
        // Deal with potential RTF control characters first
        if ($protectCurlyBracket)
        {
            // For RTF, escaping special characters
            // used to build control words is recommended in hexa
            $text = str_replace("\\", "\\'5C", $text);
            $text = str_replace('{', "\\'7B", $text);
            $text = str_replace('}', "\\'7D", $text);
        }

        // Replace CR and newline characters and combinations with a single space
        $text = preg_replace("/(\r\n)+|(\n\r)+|\n+|\r+/u", ' ', $text);
        // Simple substitutions - italics and bold sometimes come in as standard HTML tags if they were copy 'n' pasted from elsewhere.
        $pattern = [
            //							"/\s*<br>\s*<\/p>/usi",
            //							"/\s*(.*?)\s*<br>/usi",
            "/<hr>/usi",
            "/<p>\\s*(.*?)\\s*<\\/p>\\s*/usi",
            "/<sup>(.*?)<\\/sup>/usi",
            "/<sub>(.*?)<\\/sub>/usi",
            "/&amp;/usi",
            "/\\s*<br.*?>\\s*/usi",
            "/<em>(.*?)<\\/em>/usi",
            "/<i>(.*?)<\\/i>/usi",
            "/<strong>(.*?)<\\/strong>/usi",
            "/<b>(.*?)<\\/b>/usi",
            "/&lt;/usi",
            "/&gt;/usi",
        ];
        $change = [
            //							"</p>",
            //							"\\s1\\cf1$1__WIKINDX__NEWLINEPAR__", // temporary replace
            "__WIKINDX__SECTION__",
            "{__WIKINDX__NEWLINEPAR____WIKINDX__NEWLINEPAR__$1}", // temporary replace
            "{\\super $1}",
            "{\\sub $1}",
            "&",
            "{__WIKINDX__NEWLINEPAR__}",
            "{\\i $1}",
            "{\\i $1}",
            "{\\b $1}",
            "{\\b $1}",
            "\\u60 ?",
            "\\u62 ?",
        ];

        // Translate HTML elements to wikindx syntax
        $text = preg_replace($pattern, $change, $text);

        // Translate characters encoded with HTML entities to plain UTF8
        return html_entity_decode($text, ENT_NOQUOTES | ENT_HTML5);
    }
    /**
     * Close the list table and append the listOverrideTable string
     */
    public function closeListTable()
    {
        $this->listTable .= '}' . LF . '{\listoverridetable' . LF . $this->listOverrideTable . '}' . LF . LF;
    }
    /**
     * Create fancy url hyperlinks
     *
     * @param string $text
     *
     * @return string
     */
    public function createFancyUrl($text)
    {
        return preg_replace_callback(
            "/<a\\s*.*\\s*href\\s*=\\s*\"http:\\/\\/(.*)\"\\s*.*\\s*>(.*)<\\/a>/Uusi",
            [$this, 'setFancyUrl'],
            $text
        );
    }
    /**
     * Set font types and create top level font blocks
     *
     * @param string $font
     *
     * @return int Font index
     */
    public function createfonttbl($font)
    {
        $fonts = \UTF8\mb_explode(',', $font);
        $font = $fonts[0];

        return $this->setFontBlock($font);
    }
    /**
     * Set font blocks
     *
     * @param string $font
     *
     * @return int Font index
     */
    public function setFontBlock($font)
    {
        $fontIndex = -1;

        // Use lowercase to prevent a double entry for a font in the table
        $font = mb_strtolower($font);

        foreach ($this->fonttbl as $index => $name)
        {
            if ($name == $font)
            {
                $fontIndex = $index;

                break;
            }
        }

        if ($fontIndex == -1)
        {
            $this->fonttbl[$this->fontIndex] = $font;
            $fontIndex = $this->fontIndex;
            ++$this->fontIndex;
        }

        return $fontIndex;
    }
    /**
     * Read an image from either file or URL
     *
     * @param array $matchArray
     *
     * @return string
     */
    public function imageCallback($matchArray)
    {
        if (preg_match("/src=['\"](.*)['\"]/Uusi", $matchArray[0], $array))
        {
            $file = $array[1];
        }
        elseif (array_key_exists(1, $matchArray))
        {
            $file = $matchArray[1];
        }
        else
        {
            return $matchArray[0]; // unable to read file so return link
        }
        
        $webimage = FALSE;

        // If this image is not local, test if it's a remote image
        if (file_exists(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_IMAGES, basename($file)])))
        {
            $file = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_IMAGES, basename($file)]);
        }
        else
        {
            if (!$this->URL_exists($file))
            {
                return $file;
            }
            else
            {
                // Download the file from the web to a temp file with curl
                $dlTempFile = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE_FILES, 'dl' . \UTILS\uuid() . '.img']);

                $fp = fopen($dlTempFile, 'wb');
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $file);
                curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_FILE, $fp);

                if (curl_exec($ch) !== FALSE)
                {
                    // PHP 8.0, LkpPo, 20201126
                    // The curl_close() function no longer has an effect
                    if (version_compare(PHP_VERSION, '8.0.0', '<'))
                    {
                        curl_close($ch);
                    }
                    fclose($fp);
                    $file = $dlTempFile;
                    $webimage = TRUE;
                }
                else
                {
                    // PHP 8.0, LkpPo, 20201126
                    // The curl_close() function no longer has an effect
                    if (version_compare(PHP_VERSION, '8.0.0', '<'))
                    {
                        curl_close($ch);
                    }
                    fclose($fp);
                    @unlink($dlTempFile);

                    return $file;
                }
            }
        }

        // Capture the dimensions expected :
        // We don't need to capture the unit because in HTML5 pixels is mandatory for images attributs.
        // The unit part in the pattern is here to separate the value of legacy units to avoid.
        preg_match("/width=['\"]([0-9.]+)[ceimnptx%]*['\"]/Uusi", $matchArray[0], $array);
        $editW = array_key_exists(1, $array) ? $array[1] : FALSE;

        preg_match("/height=['\"]([0-9.]+)[ceimnptx%]*['\"]/Uusi", $matchArray[0], $array);
        $editH = array_key_exists(1, $array) ? $array[1] : FALSE;

        // Extract the real dimensions of the picture
        list($width, $height, $type) = getimagesize($file);

        // Convert dimensions from pixels to twips (RTF base unit: px = 96/inch and twips = 1440/inch => 1 px = 15 twips = 1440 / 96 twips)
        if ($editH)
        {
            $editH *= 15;
        }
        if ($editW)
        {
            $editW *= 15;
        }
        $width *= 15;
        $height *= 15;

        // Compute the missing expected dimensions in proportion with the real dimensions
        if ($editH === FALSE && $editW === FALSE)
        {
            $editW = $width;
            $editH = $height;
        }
        elseif ($editW !== FALSE)
        {
            $editH = $height * ($editW * 100 / $width) / 100;
        }
        elseif ($editH !== FALSE)
        {
            $editW = $width * ($editH * 100 / $height) / 100;
        }

        // \picwgoal and \pichgoal are long integer (16 bit) with a precision maximum of 32767,
        // so RTF processor will permit a larger image.
        // We recompute the initial image dimensions to not exceed that
        if ($width >= $height && $width > 32767)
        {
            $height = $height * 32767 / $width;
            $width = 32767;
        }
        elseif ($height > $width && $height > 32767)
        {
            $width = $width * 32767 / $height;
            $height = 32767;
        }

        // Indicate the fixed size of the image
        $blipSize = '\picwgoal' . floor($width) . '\pichgoal' . floor($height);
        // Indicate the scale factor used for rendering the image with the desired size from the initial fixed size
        $blipScale = '\picscalex' . floor($editW * 100 / $width) . '\picscaley' . floor($editH * 100 / $height);

        $tempFile = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE_FILES, 'bin' . \UTILS\uuid() . '.png']);
        $hexfile = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE_FILES, 'hex' . \UTILS\uuid() . '.txt']);

        switch ($type) {
            case IMAGETYPE_GIF:
                $blipType = '\pngblip';
                imagepng(imagecreatefromgif($file), $tempFile, 9);
                $file = $tempFile;
            break;
            case IMAGETYPE_JPEG:
                $blipType = '\jpegblip';
            break;
            case IMAGETYPE_PNG:
                $blipType = '\pngblip';
            break;
            case IMAGETYPE_WEBP:
                $blipType = '\pngblip';
                imagepng(imagecreatefromwebp($file), $tempFile, 9);
                $file = $tempFile;
            break;
            default:
                return $matchArray[0]; // unable to read file so return link
            break;
        }
        $this->fbin2fhex($file, $hexfile);
        @unlink($tempFile);

        // Erase the tempfile of the image downloaded form the web
        if ($webimage)
        {
            @unlink($file);
        }

        $out = '{\*\shppict{\pict__WIKINDX__NEWLINE__' . $blipSize . $blipScale . $blipType . '__WIKINDX__NEWLINE__##' . basename($hexfile) . '##}}';

        return $out;
    }
    /**
     * Callback for HTML style elements of <P>
     *
     * @param array $match
     *
     * @return string
     */
    public function paraCallback($match)
    {
        $text = $match[2];

        return $this->paraStyle($match[1], $text) . "__WIKINDX__NEWLINEPAR____WIKINDX__NEWLINEPAR__";
    }
    /**
     * Deal with <P> parameters
     *
     * @param string $param
     * @param string $text
     *
     * @return string
     */
    public function paraStyle($param, $text)
    {
        $text = str_replace(' ', '&nbsp;', $text);
        $justify = $pixelsR = $pixelsL = $indentL = $indentR = FALSE;
        $justify = trim($param);
        // Although it works in OO.org, for Word, justification needs to be outside {...} and therefore not mixed up with bold, italics etc.  so deal with it last.
        if ($justify)
        {
            if (!array_key_exists($justify, $this->justify))
            {
                $justify = $this->justify['justify'];
            }
            else
            {
                $justify = $this->justify[$justify];
            }
            $text = $justify . LF . $text . LF;
        }
        if ($pixelsL)
        {
            if (!array_key_exists($pixelsL, $this->indentL))
            {
                $indentL = $this->indentL[40];
            }
            else
            {
                $indentL = $this->indentL[$pixelsL];
            }
        }
        if ($pixelsR)
        {
            if (!array_key_exists($pixelsR, $this->indentR))
            {
                $indentR = $this->indentR[40];
            }
            else
            {
                $indentR = $this->indentR[$pixelsR];
            }
        }

        // If a text is aligned or indented it have to be enclaused in a paragraph
        if ($indentL || $indentR || $justify)
        {
            $text = $text . LF . '\par' . LF;
        }
        if ($indentL || $indentR)
        {
            $text = $indentL . $indentR . $text . '\li0\ri0' . LF;
        }

        return $text;
    }

    /**
     * UTF-8 to ANSI Windows 1252 strings for RTF only
     *
     * Returns a string encoded accordingly to Rich Text Format (RTF)
     * Specification Version 1.9.1.
     *
     * @see https://www.microsoft.com/en-us/download/details.aspx?id=10725
     *
     * @param string $string UTF-8 encoded string
     *
     * @return string plain ASCII with RTF specific sequences for others characters
     */
    public function utf8_2_rtfansicpg1252($string)
    {
        $s = '';
        $string = preg_split('//u', $string);

        // 1. For each UTF8 character
        foreach ($string as $c)
        {
            // 2. Take it's unicode code point
            $ucodepoint = \UTF8\mb_ord($c);

            // 3. If it's code point maps to ASCII not extended charset

            // NB: We could avoid to reencode characters upper than 127 supported by Windows 1252,
            // but since Windows 1252 is not a subset of UTF8 despite ASCII (not extended) is,
            // we will have to encode again from utf8 to Windows 1252.
            //
            // Warning:
            // ISO-8859-1 is a subset of UTF8 but Windows 1252 is not identical.
            // We can't use it because RTF don't support it. So, we encode to plain ASCII
            // with RTF specific sequences for others characters.
            //
            // NB: Assume \ansi\asincpg1252 RTF headers.
            if ($ucodepoint < 128)
            {
                // 4a. Escape ASCII control characters, except text flow control characters
                if (
                    $ucodepoint > 0 // 0x00
                    && $ucodepoint < 32 // 0x20
                    && (
                        $ucodepoint < 9 // 0x09
                        || $ucodepoint > 13 // 0x0D
                    )
                ) {
                    $s .= "\\'" . mb_strtoupper(mb_substr('0' . dechex($ucodepoint), -2, 2));
                }
                // 4b. Drop NUL characters
                elseif ($ucodepoint == 0)
                {
                    // Do nothing remove it
                    // This character is not expected at this point...
                    // and break the RTF output of some Office Suite
                }
                // 4c. Use this character as is
                else
                {
                    $s .= $c;
                }
            }
            else
            {
                // 5. Otherwise, replace it by it's decimal code point prefixed by \u
                // and followed immediately by equivalent character(s) in ANSI representation if possible.
                // We will not issue the nearest representation in ascii
                // because it is really very complicated and resource consuming
                // whereas it is only a degraded display mechanism for old word processors
                // unable to decode to unicode. Moreover, if an unicode character is best represented by
                // a sequence of several characters, it is necessary to issue additional sequences
                // to declare the number of representative characters. A question mark could do the trick!
                $s .= '\u';
                // 5 bis. Due to the 16 bit accuracy of the integers used by the RTF control words,
                // the code points cannot be represented beyond 32766 and must be replaced by its two's complement
                // in decimal with it's sign (if negative).
                // https://en.wikipedia.org/wiki/Two's_complement
                if ($ucodepoint > 32767)
                {
                    $ucodepoint = $ucodepoint - 65536;
                }
                $s .= $ucodepoint;
                $s .= '?';
            }
        }

        return $s;
    }
    /**
     * Callback for createLists()
     *
     * Unordered lists.
     * NB - IE sometimes closes the <li> tag sometimes doesn't - bravo!
     *
     * @param array $matchArray
     *
     * @return string
     */
    protected function callbackUnorderedList($matchArray)
    {
        $this->listType = $this->lists['bullet'] . '\li' . ($this->listIndent + $this->nested * $this->listIndentExtra);
        $text = $matchArray[1];
        $text = preg_replace(
            "/\\s*<li>\\s*(.*?)\\s*<\\/li>\\s*/usi",
            $this->listType . ' {' . "$1" . '}\par__WIKINDX__NEWLINE__',
            $text
        );
        $text = preg_replace_callback(
            "/\\s*<li\\s*style\\s*=\\s*\"(.*?)\">\\s*(.*?)\\s*<\\/li>\\s*/usi",
            [$this, 'listStyleCallback'],
            $text
        );
        $text = preg_replace(
            "/\\s*<li>\\s*(.*?)\\s*/usi",
            $this->listType . ' {' . "$1" . '}\par__WIKINDX__NEWLINE__',
            $text
        );
        $text = preg_replace_callback(
            "/\\s*<li\\s*style\\s*=\\s*\"(.*?)\">\\s*(.*?)\\s*/usi",
            [$this, 'listStyleCallback'],
            $text
        );

        return '\par' . LF . $text . '\pard' . $this->lineSpacing . '\par' . LF;
    }
    /**
     * Callback for createLists()
     *
     * Ordered Lists.
     * NB - IE sometimes closes the <li> tag sometimes doesn't - bravo!
     *
     * @param array $matchArray
     *
     * @return string
     */
    protected function callbackOrderedList($matchArray)
    {
        // NB quick fix that might produce problems if list text itself includes the list type
        if (mb_strstr($matchArray[0], 'lower-greek') !== FALSE)
        {
            $levelnfc = 60;
        }
        elseif (mb_strstr($matchArray[0], 'lower-alpha') !== FALSE)
        {
            $levelnfc = 4;
        }
        elseif (mb_strstr($matchArray[0], 'upper-alpha') !== FALSE)
        {
            $levelnfc = 3;
        }
        elseif (mb_strstr($matchArray[0], 'lower-roman') !== FALSE)
        {
            $levelnfc = 2;
        }
        elseif (mb_strstr($matchArray[0], 'upper-roman') !== FALSE)
        {
            $levelnfc = 1;
        }
        else
        {
            $levelnfc = 0;
        }
        $this->createListTablesArabic($levelnfc);
        $this->listType = $this->listId . '\fi-360\li' . $this->listIndent;
        $text = $matchArray[1];
        $text = preg_replace(
            "/\\s*<li>\\s*(.*?)\\s*<\\/li>\\s*/usi",
            $this->listType . ' {' . "$1" . '}\par__WIKINDX__NEWLINE__',
            $text
        );
        $text = preg_replace_callback(
            "/\\s*<li\\s*style\\s*=\\s*\"(.*?)\">\\s*(.*?)\\s*<\\/li>\\s*/usi",
            [$this, 'listStyleCallback'],
            $text
        );
        $text = preg_replace(
            "/\\s*<li>\\s*(.*?)\\s*/usi",
            $this->listType . ' {' . "$1" . '}\par__WIKINDX__NEWLINE__',
            $text
        );
        $text = preg_replace_callback(
            "/\\s*<li\\s*style\\s*=\\s*\"(.*?)\">\\s*(.*?)\\s*/usi",
            [$this, 'listStyleCallback'],
            $text
        );

        return '\par' . LF . $text . '\pard' . $this->lineSpacing . '\par' . LF;
    }
    /**
     * Callback for HTML style elements of font and span
     *
     * @param array $match
     *
     * @return string
     */
    protected function styleCallback($match)
    {
        if ($this->spanParse)
        {
            $text = $match[2];
        }
        else
        {
            $text = trim($match[3]);
        }
        $newText = $this->style($match[1], $text);

        return $newText;
    }
    /**
     * Initialize RTF values either as defaults or from user-requested settings
     */
    private function init()
    {
        // Line spacing of main paper body
        if ($this->session->getVar("wp_ExportPaperSpace") == 'oneHalfSpace')
        {
            $this->lineSpacing = '\sl360\slmult1';
        }
        elseif ($this->session->getVar("wp_ExportPaperSpace") == 'doubleSpace')
        {
            $this->lineSpacing = '\sl480\slmult1';
        }
        else
        {
            $this->lineSpacing = '';
        }
        $this->paperSize = $this->session->getVar("wp_ExportPaperSize");
        // Line spacing of indented quotations
        if ($this->session->getVar("wp_ExportSpaceIndentQ") == 'oneHalfSpace')
        {
            $this->lineSpacingIndentQ = '\sl360\slmult1';
        }
        elseif ($this->session->getVar("wp_ExportSpaceIndentQ") == 'doubleSpace')
        {
            $this->lineSpacingIndentQ = '\sl480\slmult1';
        }
        else
        {
            $this->lineSpacingIndentQ = '\sl240\slmult1'; // singlepublic $paperSize = FALSE;
        }

        $this->footnoteText = FALSE;
        $this->tableStyle = FALSE;
        $this->justify = [
            'center' => '\qc',
            'left' => '\ql',
            'right' => '\qr',
            'justify' => '\qj',
        ];
        /**
         * Lists:
         * \\ls0 => a, b ('a')
         * \\ls5 => A, B ('aa')
         * \\ls2 => i, ii ('i')
         * \\ls3 => I, II ('ii')
         * \\ls4 => 1, 2
         * \\ls1 => bullets -- currently, only this one is used from this array.  Arabic numbered lists are handled in the code.
         */
        $this->lists = [
            'aa' => '\ls5\fi-360',
            'a' => '\ls0\fi-360',
            'ii' => '\ls3\fi-360',
            'i' => '\ls2\fi-360',
            '1' => '\ls4\fi-360',
            'bullet' => '\ls1\fi-360',
        ];

        $this->listIndex = 2;

        $fontIndex = $this->createfonttbl('Arial');

        $this->listOverrideTable = '{\listoverride\listid1\listoverridecount0\ls1}' . LF;
        // Default bullets - 'listid1' == bullets, 'listidN' where 'N' is greater than 1 == Arabic
        // $this->listTable is closed in $this->closeListTable() below
        $this->listTable =
            '{\*\listtable ' .
                '{\list\listtemplateid-1' .
                    '{\listlevel\levelnfc23\leveljcn0\levelstartat1\levelfollow0\levelstartat0' .
                    '{\leveltext ' . "\\'01" . '\u8226 ?;}' .
                    '{\levelnumbers;}\li720\fi-360' .
                '}' .
                '\listrestarthdn1\listid1{\listname WKX list style}' .
            '}' . LF;
    }
    /**
     * Callback for adding a footnote to an indented quotation.
     *
     * @param array $matches
     *
     * @return string
     */
    private function callback_footnoteIndent($matches)
    {
        return $matches[1] . $matches[3] . $matches[2];
    }
    /**
     * Create list tables for Arabic numerals
     *
     * @param int $levelnfc Default is 0.
     */
    private function createListTablesArabic($levelnfc = 0)
    {
        $this->listTable .=
            '{\list' .
                '{\listlevel\levelnfc' .
                $levelnfc .
                '\leveljcn0\levelstartat1\levelfollow0' .
                '{\leveltext ' . "\\'02" . "\\'00." . ';}' .
                '{\levelnumbers ' . "\\'01" . ';}' .
                '\li720\fi-360' .
                '}' .
                '\listid' . $this->listIndex .
            '}' . LF;
        $this->listId = '\ls' . $this->listIndex;
        $this->listOverrideTable .= '{\listoverride\listid' . $this->listIndex . '\listoverridecount0' . $this->listId . '}' . LF;
        ++$this->listIndex;
    }
    /**
     * Create email hyperlinks
     *
     * @param string $text
     *
     * @return string
     */
    private function createEmail($text)
    {
        return preg_replace_callback(
            "/<a\\s*href\\s*=\\s*\"mailto:(.*)\"\\s*>(.*)<\\/a>/Uusi",
            [$this, 'setEmail'],
            $text
        );
    }
    /**
     * Callback for createFancyUrl() above
     *
     * @param array $matchArray
     *
     * @return string
     */
    private function setFancyUrl($matchArray)
    {
        $url = trim($matchArray[1]);
        $text = preg_replace("/^__WIKINDX__NEWLINEPAR__|__WIKINDX__NEWLINEPAR__$/u", '', $matchArray[2]);

        return '{\field{\fldinst {HYPERLINK "http://' . $url . '"}}{\fldrslt {\cs1\ul\cf2 ' . $text . '}}}__WIKINDX__NEWLINE__';
    }
    /**
     * Callback for createEmail() above
     *
     * @param array $matchArray
     *
     * @return string
     */
    private function setEmail($matchArray)
    {
        $email = preg_replace("/^__WIKINDX__NEWLINEPAR__|__WIKINDX__NEWLINEPAR__$/u", '', $matchArray[1]);

        return '{\field{\fldinst {HYPERLINK "mailto:' . $email . '"}}{\fldrslt {\cs1\ul\cf2 ' . $email . '}}}__WIKINDX__NEWLINE__';
    }
    /**
     * Find any tables
     *
     * @param string $text
     *
     * @return string
     */
    private function createTables($text)
    {
        return preg_replace_callback(
            "/\\s*<table(.*)>\\s*(.*)\\s*<\\/table>\\s*/Uusi",
            [$this, 'tableFormat'],
            $text
        );
    }
    /**
     * Format Tables
     *
     * We've no way of knowing the browser window size from PHP so we assume a width of 1000 -- if the resultant tableWidth is higher
     * than $this->tableWidth, we limit it to $this->tableWidth.
     * Must count the number of cells in each row and divide them into $tableWidth to get cell width.
     *
     * @param array $matchArray
     *
     * @return string
     */
    private function tableFormat($matchArray)
    {
        $text = preg_replace("/^__WIKINDX__NEWLINEPAR__|__WIKINDX__NEWLINEPAR__$/u", '', $matchArray[2]);
        preg_match("/width.*(\\d+)px/Uusi", $matchArray[1], $array);
        $tableWidth = array_key_exists(1, $array) ? $this->tableWidth * $array[1] / 1000 : $this->tableWidth;
        $tableWidth = $tableWidth > $this->tableWidth ? $this->tableWidth : floor($tableWidth);
        $row = LF . '\trowd\trql\trpaddft3\trpaddt55\trpaddfl3\trpaddl55\trpaddfb3\trpaddb55\trpaddfr3\trpaddr55__WIKINDX__NEWLINE__';
        $cell = '\clbrdrt\brdrs\brdrw1\brdrcf1\clbrdrl\brdrs\brdrw1\brdrcf1\clbrdrb\brdrs\brdrw1\brdrcf1\clbrdrr\brdrs\brdrw1\brdrcf1';
        $finalCell = '\clbrdrt\brdrs\brdrw1\brdrcf1\clbrdrl\brdrs\brdrw1\brdrcf1\clbrdrb\brdrs\brdrw1\brdrcf1\clbrdrr\brdrs\brdrw1\brdrcf1\cellx' . $tableWidth . '__WIKINDX__NEWLINE__';
        preg_match_all("/\\s*<tr.*>\\s*(.*)\\s*\\<\\/tr>\\s*/Uusi", $text, $matches);
        // Count no. table cells in each row
        $output = '';
        foreach ($matches[1] as $data)
        {
            $cellString = '\intbl' . LF . ' ';
            $rowString = $row;
            $numCells = preg_match_all("/\\s*<td(.*)>\\s*(.*)\\s*\\<\\/td>\\s*/Uusi", $data, $cells);
            if (empty($cells))
            {
                return $output;
            }
            $cellText = $cells[2];
            $cellStyle = $cells[1];
            $width = $baseWidth = floor($tableWidth / $numCells);
            $cellNumber = 1;
            foreach ($cellText as $cellData)
            {
                if ($cellNumber != $numCells)
                {
                    $rowString .= $cell . '\cellx' . $width . '__WIKINDX__NEWLINE__';
                }
                else
                {
                    $rowString .= $finalCell;
                }
                $cellData = preg_replace("/^__WIKINDX__NEWLINEPAR__|__WIKINDX__NEWLINEPAR__$/u", '', $cellData);
                if (preg_match("/style\\s*=\\s*\"(.*?)\"/usi", array_shift($cellStyle), $styleMatch))
                {
                    $this->tableStyle = TRUE;
                    $cellData = $this->style($styleMatch[1], $cellData);
                }
                else
                {
                    $cellData .= '\cell';
                    $this->tableStyle = FALSE;
                }
                $cellString .= $cellData . '__WIKINDX__NEWLINE__\s1\cf1';
                ++$cellNumber;
                $width += $baseWidth;
            }
            $output .= $rowString . $cellString . '\row\pard' . $this->lineSpacing . LF . LF;
        }
        $this->tableStyle = FALSE;

        return "{__WIKINDX__NEWLINEPAR____WIKINDX__NEWLINEPAR__}" . $output;
    }
    /**
     * For the cases where a list element has style information
     *
     * @param array $match
     *
     * @return string
     */
    private function listStyleCallback($match)
    {
        $text = trim($match[2]);
        $text = $this->style($match[1], $text);

        return $this->listType . ' {' . $text . '}\par__WIKINDX__NEWLINE__';
    }
    /**
     * Check image URL is valid
     *
     * @param string URL
     * @param mixed $url
     *
     * @return bool
     */
    private function URL_exists($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HEADER, TRUE);    // we want headers
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);    // we don't need the body
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // PHP 8.0, LkpPo, 20201126
        // The curl_close() function no longer has an effect
        if (version_compare(PHP_VERSION, '8.0.0', '<'))
        {
            curl_close($ch);
        }

        return ($httpcode >= 200 && $httpcode < 300);
    }
    /**
     * Convert a binary file to a hexadecimal file encoded with bin2hex
     *
     * Useful for images inlined in RTF.
     *
     * @param array $binfile
     * @param array $hexfile
     *
     * @return string
     */
    private function fbin2fhex($binfile, $hexfile)
    {
        $i = fopen($binfile, "rb");
        $o = fopen($hexfile, "wb");
        do
        {
            $d = fgets($i, 1024);
            if ($d !== FALSE)
            {
                fwrite($o, bin2hex($d));
            }
        } while ($d !== FALSE);
        fclose($o);
        fclose($i);
    }
    /**
     * Convert hexadecimal colours to RTF colours
     *
     * @param string $colour
     *
     * @return string
     */
    private function convertColour($colour)
    {
    	$colour = ltrim($colour, '#');
    	if (!ctype_xdigit($colour)) {
    		return FALSE;
    	}
        $colorVal = hexdec($colour);
        $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
        $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
        $rgbArray['blue'] = 0xFF & $colorVal;
        $colour = '\red' . $rgbArray['red'];
        $colour .= '\green' . $rgbArray['green'];
        $colour .= '\blue' . $rgbArray['blue'];
        if (($index = array_search($colour, $this->colourArray)) !== FALSE)
        {
            $index++;
        }
        else
        {
            $this->colourArray[] = $colour;
            $index = count($this->colourArray);
        }

        return '\s1\cf' . $index;
    }
    /**
     * Deal with style elements for DIV and SPAN
     *
     * @param string $styleString
     * @param string $text
     *
     * @return string
     */
    private function style($styleString, $text)
    {
        $text = str_replace(' ', 'WIKINDX_SPACE', $text);
        if ($this->isIE)
        {
            $params = \UTF8\mb_explode('&nbsp;', $styleString);
        }
        else
        {
            $params = \UTF8\mb_explode(';', $styleString);
        }
        $justify = $indentL = $indentR = FALSE;
        $slashCellAdded = FALSE;
        foreach ($params as $param)
        {
            if (!$param)
            {
                continue;
            }
            if ($this->isIE)
            {
                $splitParam = \UTF8\mb_explode('=', $param);
            }
            else
            {
                $splitParam = \UTF8\mb_explode(':', $param);
            }
            if (!array_key_exists(1, $splitParam))
            { // not recognised - usually result of pasting from Office etc.
                continue;
            }
            $param0 = mb_strtolower(trim($splitParam[0]));
            $param1 = trim(str_replace('"', '', $splitParam[1]));
            if (($param0 == 'font-weight') && ($param1 == 'bold'))
            {
                $text = "{\\b\n$text}";
            }
            elseif (($param0 == 'font-weight') && ($param1 == 'normal'))
            {
                $text = "{$text}";
            }
            elseif (($param0 == 'text-decoration') && ($param1 == 'underline'))
            {
                $text = "{\\ul $text}";
            }
            elseif (($param0 == 'font-style') && ($param1 == 'italic'))
            {
                $text = "{\\i\n$text}";
            }
            elseif ($param0 == 'color')
            {
                $colour = $this->convertColour($param1);
                $text = '__WIKINDX__NEWLINE__{' . "$colour" . "__WIKINDX__NEWLINE__$text}";
            }
            elseif ($param0 == 'font-family')
            {
                $fontIndex = $this->createfonttbl($param1);
                $text = "{\\f$fontIndex $text}__WIKINDX__NEWLINE__";
            }
            elseif ($param0 == 'face')
            {
                $fontIndex = $this->createfonttbl($param1);
                $text = "{\\f$fontIndex $text}__WIKINDX__NEWLINE__";
            }
            elseif ($param0 == 'font-size')
            {
                $fontSize = -1;

                // Use a predefined font size
                if (array_key_exists($param1, $this->fontSizes))
                {
                    $fontSize = $this->fontSizes[$param1];
                }
                else
                {
                    // Or a font size with a unit

                    // Try with points
                    $param1 = trim(str_replace('pt', '', $param1));
                    if (is_numeric($param1))
                    {
                        $fontSize = floor(floatval($param1) * 2);
                    }

                    // Try again with pixels
                    if ($fontSize == -1)
                    {
                        // 1 pt = 1,5 px => 1 half-point = 0,75 px
                        $param1 = trim(str_replace('px', '', $param1));
                        if (is_numeric($param1))
                        {
                            $fontSize = floor(floatval($param1) * 3 / 4);
                        }
                    }
                }

                // Use a font size only if this will not imply a text hidden by mistake
                if ($fontSize > 0)
                {
                    $text = "{\\fs$fontSize $text}__WIKINDX__NEWLINE__";
                }
            }
            elseif ($param0 == 'text-align')
            {
                $justify = $param1;
            }
            elseif ($param0 == 'margin-left')
            {
                if (preg_match("/(\\d+)/u", $param1, $array))
                {
                    $indentL = '\li' . $array[1] * 18;
                }
            }
            elseif ($param0 == 'margin-right')
            {
                if (preg_match("/(\\d+)/u", $param1, $array))
                {
                    $indentR = '\ri' . $array[1] * 18;
                }
            }
        }
        // Although it works in OO.org, for Word, justification needs to be outside {...} and therefore not mixed up with bold, italics etc.  so deal with it last.
        if ($justify)
        {
            if (!array_key_exists($justify, $this->justify))
            {
                $justify = $this->justify['justify'];
            }
            else
            {
                $justify = $this->justify[$justify];
            }
            if ($this->tableStyle)
            {
                $text = '{' . $justify . LF . $text . ' \cell}' . LF;
                $slashCellAdded = TRUE;
            }
            else
            {
                $text = $justify . LF . $text . LF;
            }
        }
        if ($this->tableStyle && !$slashCellAdded)
        {
            $text .= '\cell';
        }

        // If a text is aligned or indented it have to be enclaused in a paragraph
        if ($indentL || $indentR || $justify)
        {
            $text = LF . '{' . $text . '\par}' . LF . LF;
        }
        if ($indentL || $indentR)
        {
            $text = $indentL . $indentR . $text . '\li0\ri0' . LF;
        }
        if ($indentL || $indentR || $justify)
        {
            $text = '\par' . $text;
        }

        $text = str_replace("WIKINDX_SPACE", ' ', $text);

        return $text;
    }
    /**
     * Indent long quotations
     *
     * @param string $text
     * @param int $numWords
     * @param bool
     * @param mixed $keepQuoteMarks
     *
     * @return string
     */
    private function indentQuotations($text, $numWords, $keepQuoteMarks)
    {
        $this->quoteNumWords = $numWords;
        $this->keepQuoteMarks = $keepQuoteMarks;
        // Get quotation marks for this localization
        $wikindxLanguageClass = FACTORY_CONSTANTS::getInstance();
        if (isset($wikindxLanguageClass->startQuotation) && $wikindxLanguageClass->startQuotation)
        {
            $qms = preg_quote($wikindxLanguageClass->startQuotation);
        }
        else
        {
            $qms = '"';
        }
        $this->qms = $qms;
        if (isset($wikindxLanguageClass->endQuotation) && $wikindxLanguageClass->endQuotation)
        {
            $qme = preg_quote($wikindxLanguageClass->endQuotation);
        }
        else
        {
            $qme = '"';
        }
        $this->qme = $qme;

        return preg_replace_callback("/($qms)(.*)($qme)(.*)(\\[cite\\].*\\[\\/cite\\])(.*)(\\[footnote\\].*\\[\\/footnote\\]){0,}([.,:;?!°ø])/Uusi", [$this, "indentQ"], $text);
    }
    /**
     * Callback for indent long quotations
     *
     * @param array $matchArray
     *
     * @return string
     */
    private function indentQ($matchArray)
    {
        // [1] => initial quote marker
        // [2] => quoted text
        // [3] => final quote marker
        // [4] => intervening text (should be a single space)
        // [5] => citation
        // [6] => intervening text
        // [7] => tags such as footnotes etc.
        // [8] => punctuation immediately following citation

        // No indentation if this is part of a footnote...
        if (mb_strpos($matchArray[4], '[/footnote]'))
        {
            return $matchArray[0];
        }
        // are there quotes within the quote?
        if (mb_strpos($matchArray[4], $this->qme))
        {
            $split = \UTF8\mb_explode($this->qme, $matchArray[4]);
            $lastElement = array_pop($split);
            $matchArray[4] = $lastElement;
            $matchArray[2] .= $matchArray[3] . implode($this->qme, $split);
        }
        if (\UTF8\mb_str_word_count($matchArray[2]) >= $this->quoteNumWords)
        {
            if (trim($matchArray[6]))
            { // intervening text
                $trail = trim($matchArray[6]) . $matchArray[7] . $matchArray[8];
                $removeSpace = '';
            }
            else
            {
                $trail = $matchArray[6] . $matchArray[7] . trim($matchArray[8]);
                $removeSpace = '__WIKINDX__QUOTEINDENTREMOVESPACE__';
            }
            $fs = $this->quoteFontSize . $this->lineSpacingIndentQ;
            $start = "\n__WIKINDX__NEWLINEPAR__{\\fs$fs __WIKINDX__NEWLINEPAR__\\li720\\ri720";
            $codaA = $matchArray[4] . $matchArray[5] . $trail .
                "__WIKINDX__NEWLINEPAR__}__WIKINDX__NEWLINEPAR__$removeSpace";
            $codaB =
                "__WIKINDX__NEWLINEPAR__}__WIKINDX__NEWLINEPAR__" . $matchArray[4] . $matchArray[5] .
                $trail . $removeSpace;
            $codaC = $matchArray[4] . $matchArray[5] .
                "__WIKINDX__NEWLINEPAR__}__WIKINDX__NEWLINEPAR__" . $trail . $removeSpace;
            if ($this->keepQuoteMarks)
            {
                if ($matchArray[4] == ' ')
                {
                    if (trim($matchArray[6]))
                    { // intervening text
                        return $start . $this->qms . $matchArray[2] . $this->qme . $codaC;
                    }
                    else
                    {
                        return $start . $this->qms . $matchArray[2] . $this->qme . $codaA;
                    }
                }
                else
                { // intervening text
                    return $start . $this->qms . $matchArray[2] . $this->qme . $codaB;
                }
            }
            else
            {
                if ($matchArray[4] == ' ')
                {
                    if (trim($matchArray[6]))
                    { // intervening text
                        return $start . '__WIKINDX__QUOTEINDENTDONE__' . $matchArray[2] .
                        '__WIKINDX__QUOTEINDENTDONE__' . $codaC;
                    }
                    else
                    {
                        return $start . '__WIKINDX__QUOTEINDENTDONE__' . $matchArray[2] .
                        '__WIKINDX__QUOTEINDENTDONE__' . $codaA;
                    }
                }
                else
                { // intervening text
                    return $start . '__WIKINDX__QUOTEINDENTDONE__' . $matchArray[2] .
                    '__WIKINDX__QUOTEINDENTDONE__' . $codaB;
                }
            }
        }

        return $matchArray[0];
    }
    /**
     * Parse [footnote]...[/footnote]
     *
     * @param string $text
     * @param array $footnoteOffsetIds
     * @param int $fontSize
     *
     * @return string
     */
    private function parseFootnotes($text, $footnoteOffsetIds, $fontSize)
    {
        if (($sizeKey = array_search($fontSize * 2, $this->fontSizes)) !== FALSE)
        {
            $this->fontSizeFt = $this->fontSizes[$sizeKey];
        }
        else
        {
            $this->fontSizeFt = $this->fontSizes['large']; // font size 9/18
        }
        if (!empty($footnoteOffsetIds))
        { // endnotes same IDs and there are existing cite tags being used
            $this->footnoteOffsetIds = $footnoteOffsetIds;
        }

        return preg_replace_callback(
            "/\\[footnote\\](.*)\\[\\/footnote\\]([.,:;?!]*\\s*)/Uusi",
            [$this, "footnotes"],
            $text
        );
    }
    /**
     * Callback for parsing [footnote]...[/footnote]
     *
     * @param array $matchArray
     *
     * @return string
     */
    private function footnotes($matchArray)
    {
        $text = $matchArray[1];
        $id = '\chftn';
        if (!$this->styleArray['citationStyle'])
        { // in-text citations so footnotes here really are footnotes
            $ft = '\footnote';
        }
        else
        { // endnotes and footnotes
            if (!$this->styleArray['endnoteStyle'])
            { // endnotes incrementing
                $ft = '\footnote\ftnalt';
            }
            elseif ($this->styleArray['endnoteStyle'] == 1)
            { // endnotes same IDs
                $ft = '\footnote\ftnalt';
                $id = array_shift($this->footnoteOffsetIds);
            }
            else
            {	// '2' == footnotes incrementing
                $ft = '\footnote';
            }
        }
        $preInText = $this->styleArray['firstCharsEndnoteInText'];
        $postInText = $this->styleArray['lastCharsEndnoteInText'];
        $preId = $this->styleArray['firstCharsEndnoteID'];
        if ($postInText && preg_match("/[.,:;?!]\\us*/", $matchArray[2]))
        {
            $matchArray[2] = '';
        }
        $postId = $this->styleArray['lastCharsEndnoteID'];
        if ($this->styleArray['formatEndnoteInText'] == 1)
        { // superscript
            $preInTextFormat = '{\super';
            $postInTextFormat = '}';
        }
        elseif ($this->styleArray['formatEndnoteInText'] == 2)
        { // subscript
            $preInTextFormat = '{\sub';
            $postInTextFormat = '}';
        }
        else
        {
            $preInTextFormat = '';
            $postInTextFormat = '';
        }
        if ($this->styleArray['formatEndnoteID'] == 1)
        { // superscript
            $preIDFormat = '{\super';
            $postIDFormat = '}';
        }
        elseif ($this->styleArray['formatEndnoteID'] == 2)
        { // subscript
            $preIDFormat = '{\sub';
            $postIDFormat = '}';
        }
        else
        {
            $preIDFormat = '';
            $postIDFormat = '';
        }
        if ($this->session->getVar("wp_ExportIndentFt") == 'indentAll')
        {
            $ftf = '\li720 ';
        }
        elseif ($this->session->getVar("wp_ExportIndentFt") == 'indentFL')
        {
            $ftf = '\fi720 ';
        }
        elseif ($this->session->getVar("wp_ExportIndentFt") == 'indentNotFL')
        {
            $ftf = '\li720\fi-720 ';
        }
        else
        {
            $ftf = '\li1\fi1 ';
        }
        $ftf .= '\fs' . $this->fontSizeFt;
        if ($this->session->getVar("wp_ExportSpaceFt") == 'oneHalfSpace')
        {
            $ftf = '\pard\plain ' . $ftf . '\sl360\slmult1 ';
        }
        elseif ($this->session->getVar("wp_ExportSpaceFt") == 'doubleSpace')
        {
            $ftf = '\pard\plain ' . $ftf . '\sl480\slmult1 ';
        }
        else
        {
            $ftf = '\pard\plain' . $ftf;
        }
        if (($this->styleArray['endnoteStyle'] == 1)
             && array_key_exists('sameIdOrderBib', $this->styleArray))
        { // endnotes same IDs, bibliography order
            $this->footnoteText .=
                $ftf .
                '\qj' .
                $preIDFormat .
                $preId .
                '{\cs2 ' . $id . '}' .
                $postId .
                $postIDFormat .
                $matchArray[1] . '\par' . LF .
                $postInTextFormat . $matchArray[2];

            return LF . $preInTextFormat . '{' . $preInText . $id . $postInText . LF . '}}';
        }

        return
            LF . $preInTextFormat . $preInText .
            '{\cs2 ' . $id . LF .
                '{' .
                    $ft . $ftf .
                    '\qj' .
                    $preIDFormat .
                    $preId .
                    '{\cs2 ' . $id . '}' .
                    $postId .
                    $postIDFormat .
                    '{' . $text . '}' .
                '}' .
            '}' .
            $postInText . LF .
            $postInTextFormat . $matchArray[2];
    }
}
