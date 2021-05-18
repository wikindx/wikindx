<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */
class xpdftotextMessages
{
    /** array */
    public $text = [];

    public function __construct()
    {
        $domain = mb_strtolower(basename(__DIR__));
        
        $this->text = [
            "menu" => dgettext($domain, "XpdftoText"),
            "heading" => dgettext($domain, "XpdftoText tool"),
            "convertedFileHeading" => dgettext($domain, "Converted file"),
            "submissionFormHeading" => dgettext($domain, "Submission form"),
            "introduction" => dgettext($domain, "This form allows you to submit a PDF file to the pdftotext tool to extract its text manually. It offers advanced options that are not available from the attachment caching feature. After a successful conversion a file containing the extracted text can be downloaded above this form."),
            "file" => dgettext($domain, "File"),
            "firstPage" => dgettext($domain, "First page to convert"),
            "lastPage" => dgettext($domain, "Last page to convert"),
            "mode" => dgettext($domain, "Mode"),
            "characterPitch" => dgettext($domain, "Character pitch (in points)"),
            "lineSpacing" => dgettext($domain, "Line spacing (in points)"),
            "clip" => dgettext($domain, "Divert clipped text after covered content"),
            "nodiag" => dgettext($domain, "No diagonal text"),
            "nopgbrk" => dgettext($domain, "Don’t insert page breaks"),
            "marginl" => dgettext($domain, "Margin left (in points)"),
            "marginr" => dgettext($domain, "Margin right (in points)"),
            "margint" => dgettext($domain, "Margin top (in points)"),
            "marginb" => dgettext($domain, "Margin bottom (in points)"),
            "opw" => dgettext($domain, "Owner password"),
            "upw" => dgettext($domain, "User password"),
            "chmodInvit" => dgettext($domain, "Some binaries of XpdftoText plugin need the executable bit. Please add it with chmod. The following commands should do the trick:"),
        ];
    }
}
