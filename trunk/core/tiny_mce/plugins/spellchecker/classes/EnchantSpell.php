<?php
/**
 * $Id: editor_plugin_src.js 201 2007-02-12 15:56:56Z spocke $
 *
 * This class was contributed by Michel Weimerskirch.
 *
 * @package MCManager.includes
 *
 * @author Moxiecode
 * @copyright Copyright © 2004-2007, Moxiecode Systems AB, All rights reserved.
 */
class EnchantSpell extends SpellChecker
{
    /**
     * Spellchecks an array of words.
     *
     * @param string $lang Selected language code (like en_US or de_DE). Shortcodes like "en" and "de" work with enchant >= 1.4.1
     * @param array $words Array of words to check.
     *
     * @return array of misspelled words.
     */
    public function &checkWords($lang, $words)
    {
        $r = enchant_broker_init();

        if (enchant_broker_dict_exists($r, $lang))
        {
            $d = enchant_broker_request_dict($r, $lang);

            $returnData = [];
            foreach ($words as $key => $value)
            {
                $correct = enchant_dict_check($d, $value);
                if (!$correct)
                {
                    $returnData[] = trim($value);
                }
            }

            return $returnData;
            // PHP 8.0 fix, LkpPo, 20201126
            // enchant_broker_free() and enchant_broker_free_dict() are deprecated; unset the object instead.
            //enchant_broker_free_dict($d);
            unset($d);
        }
        else
        {
        }
        // PHP 8.0 fix, LkpPo, 20201126
        // enchant_broker_free() and enchant_broker_free_dict() are deprecated; unset the object instead.
        //enchant_broker_free($d);
        unset($r);
    }

    /**
     * Returns suggestions for a specific word.
     *
     * @param string $lang Selected language code (like en_US or de_DE). Shortcodes like "en" and "de" work with enchant >= 1.4.1
     * @param string $word Specific word to get suggestions for.
     *
     * @return array of suggestions for the specified word.
     */
    public function &getSuggestions($lang, $word)
    {
        $r = enchant_broker_init();

        if (enchant_broker_dict_exists($r, $lang))
        {
            $d = enchant_broker_request_dict($r, $lang);
            $suggs = enchant_dict_suggest($d, $word);

            // enchant_dict_suggest() sometimes returns NULL
            if (!is_array($suggs))
            {
                $suggs = [];
            }

            // PHP 8.0 fix, LkpPo, 20201126
            // enchant_broker_free() and enchant_broker_free_dict() are deprecated; unset the object instead.
            //enchant_broker_free_dict($d);
            unset($d);
        }
        else
        {
            $suggs = [];
        }

        // PHP 8.0 fix, LkpPo, 20201126
        // enchant_broker_free() and enchant_broker_free_dict() are deprecated; unset the object instead.
        //enchant_broker_free($d);
        unset($r);

        return $suggs;
    }
}
