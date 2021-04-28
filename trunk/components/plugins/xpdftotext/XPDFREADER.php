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
 * XpdfReader parsing functions wrapping XpdfReader tools
 *
 * @package components\plugins\xpdftotext\XpdfReader
 */
namespace XPDFREADER
{
    /**
     * Extract the text content of a PDF with XpdfReader PdftoText tool
     *
     * $options is an array where the key is the name of an option of PdftoText
     * and the key value is its the option value.
     *
     * All options are allowed except the following which are preset for consistency:
     * 
     * -q
     * -enc UTF-8
     * -eol unix
     * -nopgbrk
     *
     * @param string $file Path to a PDF file
     * @param array $options Options of PdftoText tool ; An array key is an option name and its value the option's value
     *
     * @return array [PdftoText_error_code, text extracted]
     */
    function pdftotext(string $file, iterable $options = [])
    {
        // Select a binary for the current OS
        switch (\UTILS\OSName())
        {
            case "windows":
                $bin = "pdftotext-win.exe";
            break;
            case "mac":
                $bin = "pdftotext-mac";
            break;
            default:
                $bin = "pdftotext-lin";
            break;
        }

        $bin = implode(DIRECTORY_SEPARATOR, [__DIR__, "bin", $bin]);

        // Build file paths
        $filein  = $file;
        $fileout = implode(DIRECTORY_SEPARATOR, [
            WIKINDX_DIR_BASE,
            WIKINDX_DIR_CACHE_PLUGINS,
            basename(__DIR__),
            uniqid("corpus_") . ".txt"]
        );

        // Build the command
        $cmd  = ' "' . $bin . '" ';
    
        // Preset options
        $cmd .= " -q ";         // Mute stdout and stderr
        $cmd .= " -enc UTF-8 "; // Get UTF-8 text
        $cmd .= " -eol unix ";  // Get UNIX style EOL

        // Dynamic options
        foreach ($options as $option => $value)
        {
            if (in_array($option, ["opw", "upw"]))
            {
                $value = '"' . $value . '"';
            }
            $cmd .= " -" . $option . " " . $value . " ";
        }

        // File options
        $cmd .= ' "' . $filein  . '" ';
        $cmd .= ' "' . $fileout . '" ';

        // Extraction
        $execerrno = 0;
        $execoutput = [];
        exec($cmd, $execoutput, $execerrno);

        $text = "";
        if (file_exists($fileout))
        {
            $text = file_get_contents($fileout);
            unlink($fileout);
        }

        return [$execerrno, $text];
    }
    
    /**
     * Extract the metadata of a PDF with XpdfReader PdfInfo tool
     *
     * $options is an array where the key is the name of an option of PdfInfo
     * and the key value is its the option value.
     *
     * All options are allowed except the following which are preset for consistency:
     * 
     * -enc UTF-8
     *
     * @param string $file Path to a PDF file
     * @param array $options Options of PdfInfo tool ; An array key is an option name and its value the option's value
     *
     * @return array [PdftoText_error_code, text extracted]
     */
    function pdfinfo(string $file, iterable $options = [])
    {
        // Select a binary for the current OS
        switch (\UTILS\OSName())
        {
            case "windows":
                $bin = "pdfinfo-win.exe";
            break;
            case "mac":
                $bin = "pdfinfo-mac";
            break;
            default:
                $bin = "pdfinfo-lin";
            break;
        }

        $bin = implode(DIRECTORY_SEPARATOR, [__DIR__, "bin", $bin]);

        // Build the command
        $cmd  = ' "' . $bin . '" ';

        // Preset options
        $cmd .= " -enc UTF-8 "; // Get UTF-8 text

        // Dynamic options
        foreach ($options as $option => $value)
        {
            if (in_array($option, ["opw", "upw"]))
            {
                $value = '"' . $value . '"';
            }
            $cmd .= " -" . $option . " " . $value . " ";
        }

        // File options
        $cmd .= ' "' . $file  . '" ';
        // Extraction
        $execerrno = 0;
        $execoutput = [];
        exec($cmd, $execoutput, $execerrno);

        return [$execerrno, implode("\n", $execoutput)];
    }
}
