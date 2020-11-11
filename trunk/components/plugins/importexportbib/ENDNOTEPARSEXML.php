<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */
class ENDNOTEPARSEXML
{
    public $version8;
    private $entries;

    public function __construct()
    {
        $this->entries = [];
        $this->version8 = FALSE;
    }
    /** This method starts the whole process
     *
     * Ensure there are no returns
     *
     * @param string $file
     *
     * @return array
     */
    public function extractEntries($file)
    {
        $sxi = new SimpleXmlIterator($file, NULL, TRUE);
        $array = $this->sxiToArray($sxi);
        foreach ($array['records'][0]['record'] as $records)
        {
            $this->parse($records);
        }

        return $this->entries;
    }
    /*
     * remove some unwanted elements and store certain parameters
     *
     * @param array $array
     */
    private function parse($array)
    {
        $entries = [];
        // check for v8 first
        foreach ($array as $key => $keyArray)
        {
            // Endnote 8 (which has a _very_ different XML format to previous versions *&^$*&^!) identifies itself as:
            // <source-app name="EndNote" version="8.0">EndNote</source-app>.
            // Need to capture this for ENDNOTEIMPORTSTAGE2
            if (($key == 'source-app') && ($keyArray['version'] >= 8.0))
            {
                $this->version8 = TRUE;

                break;
            }
        }
        if (!$this->version8)
        {
            return;
        }
        foreach ($array as $key => $keyArray)
        {
            if (($key == 'database') || ($key == 'rec-number') || ($key == 'periodical') || ($key == 'full-title') || ($key == 'source-app'))
            {
                continue;
            }
            if ($key == 'ref-type')
            {
                $entries['ref-type-name'] = $keyArray['name'];
            }
            $entries[$key] = $keyArray[0];
        }
        $this->entries[] = $entries;
    }
    /*
     * convert XML to array
     *
     * @param array $sxi
     *
     * @return array
     */
    private function sxiToArray($sxi)
    {
        $a = [];
        for ($sxi->rewind(); $sxi->valid(); $sxi->next())
        {
            $key = $sxi->key();
            if (!array_key_exists($key, $a))
            {
                $a[$key] = [];
            }
            if ($sxi->hasChildren())
            {
                $a[$key][] = $this->sxiToArray($sxi->current());
            }
            else
            {
                $a[$key][] = strval($sxi->current());
            }
            $currentObj = $sxi->{$key}[0]->attributes();
            foreach ($currentObj as $b => $c)
            {
                $a[$key][$b] = (string)$c;
            }
        }

        return $a;
    }
}
