<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * CITE class.
 *
 * Citation handling. This is the main file to handle citations.
 *
 * @package wikindx\core\bibcitation
 */
class CITE
{
    /** object */
    public $citeStyle;

    /**
     * CITE
     *
     * @param string $output 'html', plain', 'rtf'. Default is 'html'
     */
    public function __construct($output = 'html')
    {
        $this->citeStyle = FACTORY_CITESTYLE::getInstance($output);
    }
    /**
     * parse [cite]...[/cite] and format citations
     *
     * @param string $text Input text
     * @param string $output 'html', plain', 'rtf'
     * @param bool $citeLink Link external citations in the returned citation. Default is TRUE
     * @param bool $rtfBibExport If TRUE, we're exporting to RTF. Default is FALSE
     * @param bool $suwpExport If TRUE, we're exporting to the word processor plug-in. Default is FALSE
     *
     * @return string parsed text
     */
    public function parseCitations($text, $output, $citeLink = TRUE, $rtfBibExport = FALSE, $suwpExport = FALSE)
    {
        // If no citations, return doing nothing
        if (mb_strpos(mb_strtolower($text), "[cite]") === FALSE)
        {
            return $text;
        }
        $this->citeStyle->output = $output;
        $this->citeStyle->rtfBibExport = $rtfBibExport;
        if ($suwpExport)
        {
            // Exporting from the SUWP
            $this->citeStyle->citeFormat->suwpExport = TRUE;
            if ($this->citeStyle->citeFormat->style['citationStyle'] && ($output == 'rtf'))
            {
                $text = preg_replace_callback(
                    "/(\\[footnote])(.*)(\\[\\/footnote\\])/Uus",
                    [$this, 'footnoteCallback'],
                    $text
                );
            }
            if ($this->citeStyle->citeFormat->style['endnoteStyle'] == 1)
            {
                $this->citeStyle->citeFormat->citeOffsets = preg_split(
                    "/\\[cite\\]/Uuis",
                    $text,
                    -1,
                    PREG_SPLIT_OFFSET_CAPTURE
                );
                array_shift($this->citeStyle->citeFormat->citeOffsets);
                $this->citeStyle->citeFormat->footnoteOffsets = preg_split(
                    "/\\[footnote\\]/Uuis",
                    $text,
                    -1,
                    PREG_SPLIT_OFFSET_CAPTURE
                );
                array_shift($this->citeStyle->citeFormat->footnoteOffsets);
            }
        }

        return $this->citeStyle->start($text, $citeLink);
    }
    /**
     * footnoteCallback
     *
     * @param array $matches
     *
     * @return string
     */
    private function footnoteCallback($matches)
    {
        if (preg_match("/(.*)\\[cite\\].*\\[\\/cite\\](.*)/Uus", $matches[2]))
        {
            return $matches[1] . preg_replace_callback(
                "/\\s*\\[cite].*\\[\\/cite\\](.*)/Uus",
                [$this, 'footnoteCiteCallback'],
                $matches[2]
            ) . $matches[3];
        }
        else
        {
            return $matches[1] . $matches[2] . $matches[3];
        }
    }
    /**
     * footnoteCiteCallback
     *
     * @param array $matches
     *
     * @return string
     */
    private function footnoteCiteCallback($matches)
    {
        return $matches[1];
    }
}
