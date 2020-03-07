<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */
class ENDNOTECREATORPARSE
{
    /*
     * Create writer arrays from endnote input
     *
     * 'author field can be:
     *	Grimshaw, Mark N.
     *	Smith, Alfred, Jr.
     *	Fisher, J.O.
     *	J O Fisher
     *	Mark M N G Grimshaw
     *	Mark N. Grimshaw
     * The delimiter WIKINDX inserts for XML multiple authors is ' and '
     *
     * @param string $input
     *
     * @return string|FALSE
     */
    public function parse($input)
    {
        $input = trim($input);
        // split on ' and '
        $authorArray = preg_split("/\\s(and)\\s/ui", $input);
        // check if there's anything that looks like et. al
        foreach ($authorArray as $value) {
            $appellation = $prefix = $surname = $firstname = $initials = '';
            $author = UTF8::mb_explode(",", preg_replace("/\\s{2,}/u", ' ', trim($value)));
            $size = count($author);
            // No commas therefore something like Mark Grimshaw-Aagaard, Mark Nicholas Grimshaw, M N Grimshaw, Mark N. Grimshaw
            if ($size == 1) {
                $author = UTF8::mb_explode(" ", $value);
                // last of array is surname (no prefix if entered correctly)
                $surname = array_pop($author);
            }
            // Something like Grimshaw, Mark or Grimshaw, Mark Nicholas  or Grimshaw, M N or Grimshaw, Mark N.
            elseif ($size == 2) {
                // first of array is surname (perhaps with prefix)
                list($surname, $prefix) = $this->grabSurname(array_shift($author));
            }
            // If $size is > 2, we're looking at something like Bush, George W, III
            else {
                // last of array is 'Jr.', 'IV' etc.
                $appellation = array_pop($author);
                // first of array is surname (perhaps with prefix)
                list($surname, $prefix) = $this->grabSurname(array_shift($author));
            }
            $remainder = implode(" ", $author);
            list($firstname, $initials) = $this->grabFirstnameInitials($remainder);
            $surname = $surname . ' ' . $appellation;
            $creators[] = ["firstname" => $firstname, "initials" => $initials, "surname" => $surname, "prefix" => $prefix];
        }
        if (isset($creators)) {
            return $creators;
        } else {
            return FALSE;
        }
    }
    /**
     * grab firstname and initials which may be of form "A.B.C." or "A. B. C. " or " A B C " etc
     *
     * @param string $remainder
     *
     * @return false|string
     */
    private function grabFirstnameInitials($remainder)
    {
        $firstname = $initials = '';
        $array = UTF8::mb_explode(" ", $remainder);
        foreach ($array as $value) {
            if (preg_match("/[a-zA-Z]{2,}/u", trim($value))) {
                $firstnameArray[] = trim($value);
            } else {
                $initialsArray[] = str_replace(".", " ", trim($value));
            }
        }
        if (isset($initialsArray)) {
            foreach ($initialsArray as $initial) {
                $initials .= ' ' . trim($initial);
            }
        }
        if (isset($firstnameArray)) {
            $firstname = implode(" ", $firstnameArray);
        }

        return [$firstname, $initials];
    }
    /**
     * surname may have title such as 'den', 'von', 'de la' etc. - characterised by first character lowercased
     *
     * @param string $input
     *
     * @return array
     */
    private function grabSurname($input)
    {
        $surnameArray = UTF8::mb_explode(" ", $input);
        foreach ($surnameArray as $value) {
            $firstChar = mb_substr($value, 0, 1);
            if ((ord($firstChar) >= 97) && (ord($firstChar) <= 122)) {
                $prefix[] = $value;
            } else {
                $surname[] = $value;
            }
        }
        if (!isset($surname)) { // assume $prefix is actually surname
            $surname = implode(" ", $prefix);
            unset($prefix);
        } else {
            $surname = implode(" ", $surname);
        }
        if (isset($prefix)) {
            $prefix = implode(" ", $prefix);

            return [$surname, $prefix];
        }

        return [$surname, FALSE];
    }
}
