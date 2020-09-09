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
 * Miscellaneous LOCALES functions
 *
 * @package wikindx\core\libs\LOCALES
 */
namespace LOCALES
{
    /**
     * Format dates and times for localization
     *
     * Use it for displaying data to the user, not formatting data for the db
     *
     * @param string $datetime comes in from the database in the format 'YYYY-MM-DD HH:MM:SS' e.g. 2013-01-31 15:54:55
     *
     * @return string
     */
    function dateFormat($datetime)
    {
    	// NB: "%c" is not the same as "%x %X" when the locale is not set correctly
    	// We want the numeric format, not literary format
        return date("%x %X", strtotime($timestamp));
    }

    /**
     * Return the list of all locales available on the current OS, defined in cached file /cache/languages/locales_system.json
     *
     * Each entry of the returned array is locale code (format: ll_CC@variant) as key
     * and its name as value [format: language (Country, Variant)].
     *
     * ll is a mandatory language code
     * CC is an optional country code
     * variant is an optional script, money or collation code
     *
     * If the source file is not readeable only the reference language is listed.
     *
     * @param bool $display_code_only Display the code of the locale instead of its localized name (optional, FALSE by default)
     *
     * @return array
     */
    function getSystemLocales($display_code_only = FALSE)
    {
        $fallbackLocList = [WIKINDX_LANGUAGE_DEFAULT => WIKINDX_LANGUAGE_NAME_DEFAULT];
        $msgfallback = "<p>As long as this error will not be corrected, only the <strong>" . $fallbackLocList[WIKINDX_LANGUAGE_DEFAULT] . " (" . WIKINDX_LANGUAGE_DEFAULT . ")</strong> language will be available.</p>";
        
        refreshSystemLocalesCache();
        
        $path_languages_localized = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE_LANGUAGES, "locales_system.json"]);
        if (!file_exists($path_languages_localized)) {
            echo "<p>The <strong>$path_languages_localized</strong> file doesn't exist.</p>" . $msgfallback;

            return $fallbackLocList;
        }
        
        $content_languages_localized = file_get_contents($path_languages_localized);
        if ($content_languages_localized === FALSE) {
            echo "<p>Reading the <strong>$path_languages_localized</strong> file returned an error.</p>" . $msgfallback;

            return $fallbackLocList;
        }
        
        $LocList = @json_decode($content_languages_localized, TRUE);
        if (json_last_error() != JSON_ERROR_NONE) {
            echo "<p>Parsing the <strong>$path_languages_localized</strong> file returned an error:</p><p>" . json_last_error_msg() . ".</p>" . $msgfallback;

            return $fallbackLocList;
        }
        
        if ($display_code_only) {
            foreach ($LocList as $k => $v) {
                $LocList[$k] = $k;
            }
        }
        
        asort($LocList);
        
        return $LocList;
    }

    /**
     * Create a data file containing the list of available locales on the current system
     *
     * The file is stored at /cache/languages/locales_system.json
     *
     * @param bool $force Forces the file overwriting if it already exists (optional, FALSE by default)
     */
    function refreshSystemLocalesCache($force = FALSE)
    {
        $path_locales_sys = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE_LANGUAGES, "locales_system.json"]);
        
        if ($force || !file_exists($path_locales_sys)) {
            file_put_contents($path_locales_sys, json_encode(checkSystemLocales(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * Return a dynamic list of all locales available on the current OS
     *
     * This function can be a bit of a time (1-2 s) running because it tests all possible variants of all known locales.
     *
     * @return array
     */
    function checkSystemLocales()
    {
        $LocList = [];
        
        foreach (getAllLocales() as $code => $name) {
            if (setlocale(LC_ALL, getLocaleGettextAliases($code)) !== FALSE) {
                $LocList[$code] = $name;
            }
        }
        
        ksort($LocList);
        
        return $LocList;
    }

    /**
     * Return all alias of a locale with and without UTF-8 charset for Gettext
     *
     * Although locales codes have a canonical form and the LANG environment variable of operating systems respect it,
     * Gettext doesn't always follow the standard. This function provides all the possible variants known to us on Windows 7,
     * Mac, Debian 10 and OpenBSD 6.6 for one locale code.
     *
     * These forms are provided to test if a locale is available on the current system and are not intended for users.
     *
     * @param string $locale Normalized code of a locale
     *
     * @return string[]
     */
    function getLocaleGettextAliases($locale)
    {
        $mainLocales = [$locale, str_replace("-", "_", $locale), str_replace("_", "-", $locale)];
        
        // This list of special cases was established from testing on various systems
        // Each key is the shortest canonical form encountered and the values are abnormal forms that correspond to it.
        $SpecialLocales = [
            "az_AZ" => ["az_Latn_AZ"], // For Windows
            "be_BY@latin" => ["be_BY"], // For Windows
            "bs_BA" => ["bs_Latn_BA"], // For Windows
            "byn_EZ" => ["byn_ER"], // For Linux: EZ have been superseded by ER, but Gettext on Linux (Debian Buster) is not up to date
            "uz_UZ" => ["uz_Latn_UZ"], // For Windows
            "uz_UZ@cyrillic" => ["uz_Cyrl_UZ"], // For Windows
            "sr_BA@latin" => ["sr_Latn_BA"], // For Windows
            "sr_ME@latin" => ["sr_Latn_ME"], // For Windows
            "sr_RS@latin" => ["sr_Latn_RS"], // For Windows
            "sr_BA" => ["sr_Cyrl_BA"], // For Windows
            "sr_ME" => ["sr_Cyrl_ME"], // For Windows
            "sr_RS" => ["sr_Cyrl_RS"], // For Windows
        ];
        
        if (array_key_exists($locale, $SpecialLocales)) {
            $mainLocales = array_merge($mainLocales, $SpecialLocales[$locale]);
        }
        
        $alias = [];
        
        foreach ($mainLocales as $code) {
            $alias[] = $code . ".utf8";
            $alias[] = $code . ".UTF8";
            $alias[] = $code . ".utf-8";
            $alias[] = $code . ".UTF-8";
            $alias[] = $code;
        }
        
        asort($alias);
        
        return $alias;
    }
    
    /**
     * Return the name of a language defined by its locale code or its ISO 639-1 (alpha-2) code.
     *
     * @see https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
     *
     * @param $codeISO string
     *
     * @return string
     */
    function codeISO639a1toName($codeISO)
    {
        $locNames = getAllLocales();
        
        // Search an exact match
        if (array_key_exists($codeISO, $locNames)) {
            return $locNames[$codeISO];
        } else {
            // Search a match only with the language code
            $lcode = $codeISO . "_";
            
            foreach ($locNames as $k => $lang) {
                if (mb_substr($k, 0, mb_strlen($lcode)) == $lcode) {
                    // Keep only the name of the language in that case
                    return preg_replace("#\\s*\\(.+\\)\\s*#u", "", $lang);
                }
            }
        }
        
        // fallback
        return "";
    }
    /**
     * Load the gettext catalogs of the user's preferred language.
     */
    function load_locales()
    {
        $locale = determine_locale();
        // Some systems need the LANG or LANGUAGE environment variables
        // to be configured before calling setlocale()
        $locale_env = $locale . ".UTF-8";
        putenv("LANG=" . $locale_env);
        putenv("LANGUAGE=" . $locale_env);
        
        // Set the locale for all systems, Windows included!
        setlocale(LC_ALL, getLocaleGettextAliases($locale));
        
        // The locale folder and component id are always in lowercase
        $locale = mb_strtolower($locale);
        
        // Get the list of enabled plugins
        // and load their gettext catalog of the locale requested
        // NB: This can be without effect if the catalogs were already loaded for the current PHP process
        $enabledPlugins = [];
        $componentsInstalled = \UTILS\readComponentsList();
        
        foreach ($componentsInstalled as $cmp) {
            if ($cmp["component_type"] == "plugin" && $cmp["component_status"] == "enabled") {
                $enabledPlugins[] = $cmp["component_id"];
            }
        }
        
        // Try to load a MO catalog only if there is one for one of the derived locale of the requested locale
        $locale_variant = $locale; // ll[_CC][@variant] (unchanged)
        $locale_country = strpos($locale, "@") !== FALSE ? mb_substr($locale, 0, strpos($locale, "@")) : $locale; // ll[_CC]
        $locale_language = strpos($locale, "_") !== FALSE ? mb_substr($locale, 0, strpos($locale, "_")) : $locale; // ll
        $locales = array_unique([$locale_variant, $locale_country, $locale_language]);

        $loading[WIKINDX_LANGUAGE_DOMAIN_DEFAULT] = TRUE;
        foreach ($enabledPlugins as $domain)
        {
            $loading[$domain] = TRUE;
        }

        foreach ($locales as $locale)
        {
            // Load the core translations
            if ($loading[WIKINDX_LANGUAGE_DOMAIN_DEFAULT])
            {
                $dirlocales = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CORE_LANGUAGES]);
                $moFileName = WIKINDX_LANGUAGE_DOMAIN_DEFAULT . ".mo";
                $moFilePath = implode(DIRECTORY_SEPARATOR, [$dirlocales, $locale, "LC_MESSAGES", $moFileName]);
                if (file_exists($moFilePath)) {
                    bindtextdomain(WIKINDX_LANGUAGE_DOMAIN_DEFAULT, $dirlocales);
                    bind_textdomain_codeset(WIKINDX_LANGUAGE_DOMAIN_DEFAULT, WIKINDX_CHARSET);
                    // If a loading success don't try to load on the next pass
                    $loading[WIKINDX_LANGUAGE_DOMAIN_DEFAULT] = FALSE;
                }
            }

            // Load the plugin translations
            foreach ($enabledPlugins as $domain)
            {
                if ($loading[$domain])
                {
                    $dirlocales = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS, $domain, "languages"]);
                    $moFileName = $domain . ".mo";
                    $moFilePath = implode(DIRECTORY_SEPARATOR, [$dirlocales, $locale, "LC_MESSAGES", $moFileName]);
                    if (file_exists($moFilePath)) {
                        bindtextdomain($domain, $dirlocales);
                        bind_textdomain_codeset($domain, WIKINDX_CHARSET);
                        // If a loading success don't try to load on the next pass
                        $loading[$domain] = FALSE;
                    }
                }
            }
        }

        // This function call must always be present even if no catalog has been loaded before
        // to force the binding to take into account the current state otherwise it uses the state
        // of the previous execution on macOS, sometimes.
        textdomain(WIKINDX_LANGUAGE_DOMAIN_DEFAULT);
    }
    /**
     * Determine the user's preferred language.
     *
     * This function builds a language priority stack. The first is the highest priority.
     *
     * If $force_locale is passed, this function will try to use this locale first.
     *
     * $param $force_locale Code of a locale (format: ll[_CC][@variant])
     *
     * @param null|mixed $force_locale (optional, NULL by default)
     *
     * @return string
     */
    function determine_locale($force_locale = NULL)
    {
        $langPriorityStack = [];
        
        // 1. Forcing a language for special case or debugging
        $langPriorityStack[] = $force_locale;
        
        // 2. The preferred language of the user ("auto" disappears in filtering to make room for browser language)
        $session = \FACTORY_SESSION::getInstance();
        $langPriorityStack[] = \GLOBALS::getUserVar("Language", "auto");
        
        // 3. The preferred language of the user's browser
        if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
            $langs = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
            
            // Remove the priority number after ";" and use the locale syntax for the country extension
            array_walk($langs, function (&$lang) {
                $lang = strtr(strtok($lang, ";"), ["-" => "_"]);
            });
            
            $langPriorityStack = array_merge($langPriorityStack, $langs);
        }
        
        // 4. The fallback language
        $langPriorityStack[] = WIKINDX_LANGUAGE_DEFAULT;
        
        // Normalize because browsers don't send a normalised code
        foreach ($langPriorityStack as $k => $v) {
            $langPriorityStack[$k] = strtolower(str_replace("-", "_", $v));
        }
        
        $locales = [];
        foreach (array_keys(getSystemLocales()) as $k => $v) {
            $locales[$v] = strtolower(str_replace("-", "_", $v));
        }
        // Of all the languages of the stack only keeps those that are actually available.
        $langPriorityStack = array_intersect($langPriorityStack, $locales);
        
        // Extract the top priority language
        // NB. The index is not always 0 because the array is not reordered after filtering.
        assert(count($langPriorityStack) > 0);
        foreach ($langPriorityStack as $lang_lowercase) {
            foreach ($locales as $lang => $v) {
                if ($v == $lang_lowercase) {
                    return $lang;
                }
            }
        }
    }

    /**
     * Return the list of all locales defined in /languages/locales.json
     *
     * Each entry of the returned array is locale code (format: ll_CC@variant) as key
     * and its name as value [format: language (Country, Variant)].
     *
     * ll is a mandatory language code
     * CC is an optional country code
     * variant is an optional script, money or collation code
     *
     * If the source file is not readeable only the reference language is listed.
     *
     * @param bool $display_code_only Display the code of the locale instead of its localized name (optional, FALSE by default)
     *
     * @return string[]
     */
    function getAllLocales($display_code_only = FALSE)
    {
        $LocList = [
            "aa_DJ" => "Afar (Djibouti)",
            "aa_ER" => "Afar (Eritrea)",
            "aa_ER@saaho" => "Afar (Eritrea, Saho)",
            "aa_ET" => "Afar (Ethiopia)",
            "af_ZA" => "Afrikaans (Suid-Afrika)",
            "agr_PE" => "Agr (Peru)",
            "ak_GH" => "Akan (Gaana)",
            "am_ET" => "አማርኛ (ኢትዮጵያ)",
            "an_ES" => "Aragonese (Spain)",
            "anp_IN" => "Angika (India)",
            "ar_AE" => "العربية (الإمارات العربية المتحدة)",
            "ar_BH" => "العربية (البحرين)",
            "ar_DZ" => "العربية (الجزائر)",
            "ar_EG" => "العربية (مصر)",
            "ar_IN" => "العربية (الهند)",
            "ar_IQ" => "العربية (العراق)",
            "ar_JO" => "العربية (الأردن)",
            "ar_KW" => "العربية (الكويت)",
            "ar_LB" => "العربية (لبنان)",
            "ar_LY" => "العربية (ليبيا)",
            "ar_MA" => "العربية (المغرب)",
            "ar_OM" => "العربية (عُمان)",
            "ar_QA" => "العربية (قطر)",
            "ar_SA" => "العربية (المملكة العربية السعودية)",
            "ar_SD" => "العربية (السودان)",
            "ar_SS" => "العربية (جنوب السودان)",
            "ar_SY" => "العربية (سوريا)",
            "ar_TN" => "العربية (تونس)",
            "ar_YE" => "العربية (اليمن)",
            "arn_CL" => "Mapuche (Chile)",
            "as_IN" => "অসমীয়া (ভাৰত)",
            "ast_ES" => "Asturianu (España)",
            "ayc_PE" => "Ayc (Peru)",
            "az_AZ" => "Azərbaycan (Azərbaycan)",
            "az_IR" => "Azərbaycan (İran)",
            "ba_RU" => "Bashkir (Russia)",
            "be_BY" => "Беларуская (Беларусь)",
            "be_BY@latin" => "Беларуская (Беларусь, LATIN)",
            "bem_ZM" => "Ichibemba (Zambia)",
            "ber_DZ" => "Ber (Algeria)",
            "ber_MA" => "Ber (Morocco)",
            "bg_BG" => "Български (България)",
            "bhb_IN" => "Bhb (India)",
            "bho_IN" => "Bhojpuri (India)",
            "bho_NP" => "Bhojpuri (Nepal)",
            "bi_VU" => "Bislama (Vanuatu)",
            "bn_BD" => "বাংলা (বাংলাদেশ)",
            "bn_IN" => "বাংলা (ভারত)",
            "bo_CN" => "བོད་སྐད་ (རྒྱ་ནག)",
            "bo_IN" => "བོད་སྐད་ (རྒྱ་གར་)",
            "br_FR" => "Brezhoneg (Frañs)",
            "brx_IN" => "बड़ो (भारत)",
            "bs_BA" => "Bosanski (Bosna i Hercegovina)",
            "byn_ER" => "Blin (Eritrea)",
            "ca_AD" => "Català (Andorra)",
            "ca_ES" => "Català (Espanya)",
            "ca_FR" => "Català (França)",
            "ca_IT" => "Català (Itàlia)",
            "ce_RU" => "Нохчийн (Росси)",
            "chr_US" => "ᏣᎳᎩ (ᏌᏊ ᎢᏳᎾᎵᏍᏔᏅ ᏍᎦᏚᎩ)",
            "cmn_TW" => "Cmn (Taiwan)",
            "co_FR" => "Corsican (France)",
            "crh_UA" => "Crimean Turkish (Ukraine)",
            "cs_CZ" => "Čeština (Česko)",
            "csb_PL" => "Kashubian (Poland)",
            "cv_RU" => "Chuvash (Russia)",
            "cy_GB" => "Cymraeg (Y Deyrnas Unedig)",
            "da_DK" => "Dansk (Danmark)",
            "de_AT" => "Deutsch (Österreich)",
            "de_BE" => "Deutsch (Belgien)",
            "de_CH" => "Deutsch (Schweiz)",
            "de_DE" => "Deutsch (Deutschland)",
            "de_IT" => "Deutsch (Italien)",
            "de_LI" => "Deutsch (Liechtenstein)",
            "de_LU" => "Deutsch (Luxemburg)",
            "doi_IN" => "Dogri (India)",
            "dsb_DE" => "Dolnoserbšćina (Nimska)",
            "dv_MV" => "Divehi (Maldives)",
            "dz_BT" => "རྫོང་ཁ། (འབྲུག།)",
            "el_CY" => "Ελληνικά (Κύπρος)",
            "el_GR" => "Ελληνικά (Ελλάδα)",
            "en_029" => "English (Caribbean)",
            "en_AG" => "English (Antigua & Barbuda)",
            "en_AU" => "English (Australia)",
            "en_BW" => "English (Botswana)",
            "en_BZ" => "English (Belize)",
            "en_CA" => "English (Canada)",
            "en_DK" => "English (Denmark)",
            "en_GB" => "English (United Kingdom)",
            "en_HK" => "English (Hong Kong SAR China)",
            "en_IE" => "English (Ireland)",
            "en_IL" => "English (Israel)",
            "en_IN" => "English (India)",
            "en_JM" => "English (Jamaica)",
            "en_MY" => "English (Malaysia)",
            "en_NG" => "English (Nigeria)",
            "en_NZ" => "English (New Zealand)",
            "en_PH" => "English (Philippines)",
            "en_SC" => "English (Seychelles)",
            "en_SG" => "English (Singapore)",
            "en_TT" => "English (Trinidad & Tobago)",
            "en_US" => "English (United States)",
            "en_ZA" => "English (South Africa)",
            "en_ZM" => "English (Zambia)",
            "en_ZW" => "English (Zimbabwe)",
            "es_AR" => "Español (Argentina)",
            "es_BO" => "Español (Bolivia)",
            "es_CH" => "Español (Suiza)",
            "es_CL" => "Español (Chile)",
            "es_CO" => "Español (Colombia)",
            "es_CR" => "Español (Costa Rica)",
            "es_CU" => "Español (Cuba)",
            "es_DO" => "Español (República Dominicana)",
            "es_EC" => "Español (Ecuador)",
            "es_ES" => "Español (España)",
            "es_GQ" => "Español (Guinea Ecuatorial)",
            "es_GT" => "Español (Guatemala)",
            "es_HN" => "Español (Honduras)",
            "es_MX" => "Español (México)",
            "es_NI" => "Español (Nicaragua)",
            "es_PA" => "Español (Panamá)",
            "es_PE" => "Español (Perú)",
            "es_PR" => "Español (Puerto Rico)",
            "es_PY" => "Español (Paraguay)",
            "es_SV" => "Español (El Salvador)",
            "es_US" => "Español (Estados Unidos)",
            "es_UY" => "Español (Uruguay)",
            "es_VE" => "Español (Venezuela)",
            "et_EE" => "Eesti (Eesti)",
            "eu_ES" => "Euskara (Espainia)",
            "eu_FR" => "Euskara (Frantzia)",
            "fa_IR" => "فارسی (ایران)",
            "ff_SN" => "Pulaar (Senegaal)",
            "fi_FI" => "Suomi (Suomi)",
            "fil_PH" => "Filipino (Pilipinas)",
            "fo_FO" => "Føroyskt (Føroyar)",
            "fr_BE" => "Français (Belgique)",
            "fr_CA" => "Français (Canada)",
            "fr_CH" => "Français (Suisse)",
            "fr_FR" => "Français (France)",
            "fr_LU" => "Français (Luxembourg)",
            "fr_MC" => "Français (Monaco)",
            "fur_IT" => "Furlan (Italie)",
            "fy_DE" => "Frysk (Dútslân)",
            "fy_NL" => "Frysk (Nederlân)",
            "ga_IE" => "Gaeilge (Éire)",
            "gd_GB" => "Gàidhlig (An Rìoghachd Aonaichte)",
            "gez_ER" => "Geez (Eritrea)",
            "gez_ER@abegede" => "Geez (Eritrea, ABEGEDE)",
            "gez_ET" => "Geez (Ethiopia)",
            "gez_ET@abegede" => "Geez (Ethiopia, ABEGEDE)",
            "gl_ES" => "Galego (España)",
            "gsw_FR" => "Schwiizertüütsch (Frankriich)",
            "gu_IN" => "ગુજરાતી (ભારત)",
            "gv_GB" => "Gaelg (Rywvaneth Unys)",
            "ha_NG" => "Hausa (Najeriya)",
            "hak_TW" => "Hakka Chinese (Taiwan)",
            "he_IL" => "עברית (ישראל)",
            "hi_IN" => "हिन्दी (भारत)",
            "hif_FJ" => "Fiji Hindi (Fiji)",
            "hne_IN" => "Hne (India)",
            "hr_BA" => "Hrvatski (Bosna i Hercegovina)",
            "hr_HR" => "Hrvatski (Hrvatska)",
            "hsb_DE" => "Hornjoserbšćina (Němska)",
            "ht_HT" => "Haitian Creole (Haiti)",
            "hu_HU" => "Magyar (Magyarország)",
            "hy_AM" => "Հայերեն (Հայաստան)",
            "ia_FR" => "Interlingua (France)",
            "id_ID" => "Indonesia (Indonesia)",
            "ig_NG" => "Igbo (Naịjịrịa)",
            "ii_CN" => "ꆈꌠꉙ (ꍏꇩ)",
            "ik_CA" => "Inupiaq (Canada)",
            "is_IS" => "Íslenska (Ísland)",
            "it_CH" => "Italiano (Svizzera)",
            "it_IT" => "Italiano (Italia)",
            "iu_CA" => "Inuktitut (Canada)",
            "ja_JP" => "日本語 (日本)",
            "ka_GE" => "Ქართული (საქართველო)",
            "kab_DZ" => "Taqbaylit (Lezzayer)",
            "kk_KZ" => "Қазақ тілі (Қазақстан)",
            "kl_GL" => "Kalaallisut (Kalaallit Nunaat)",
            "km_KH" => "ខ្មែរ (កម្ពុជា)",
            "kn_IN" => "ಕನ್ನಡ (ಭಾರತ)",
            "ko_KR" => "한국어(대한민국)",
            "kok_IN" => "कोंकणी (भारत)",
            "ks_IN" => "کٲشُر (ہِنٛدوستان)",
            "ks_IN@devanagari" => "کٲشُر (ہِنٛدوستان, DEVANAGARI)",
            "ku_TR" => "Kurdish (Turkey)",
            "kw_GB" => "Kernewek (Rywvaneth Unys)",
            "ky_KG" => "Кыргызча (Кыргызстан)",
            "lb_LU" => "Lëtzebuergesch (Lëtzebuerg)",
            "lg_UG" => "Luganda (Yuganda)",
            "li_BE" => "Limburgish (Belgium)",
            "li_NL" => "Limburgish (Netherlands)",
            "lij_IT" => "Ligurian (Italy)",
            "ln_CD" => "Lingála (Republíki ya Kongó Demokratíki)",
            "lo_LA" => "ລາວ (ລາວ)",
            "lt_LT" => "Lietuvių (Lietuva)",
            "lv_LV" => "Latviešu (Latvija)",
            "lzh_TW" => "Literary Chinese (Taiwan)",
            "mag_IN" => "Magahi (India)",
            "mai_IN" => "Maithili (India)",
            "mai_NP" => "Maithili (Nepal)",
            "mfe_MU" => "Kreol morisien (Moris)",
            "mg_MG" => "Malagasy (Madagasikara)",
            "mhr_RU" => "Mhr (Russia)",
            "mi_NZ" => "Maori (New Zealand)",
            "miq_NI" => "Miq (Nicaragua)",
            "mjw_IN" => "Mjw (India)",
            "mk_MK" => "Македонски (Македонија)",
            "ml_IN" => "മലയാളം (ഇന്ത്യ)",
            "mn_MN" => "Монгол (Монгол)",
            "mni_IN" => "Manipuri (India)",
            "moh_CA" => "Mohawk (Canada)",
            "mr_IN" => "मराठी (भारत)",
            "ms_BN" => "Melayu (Brunei)",
            "ms_MY" => "Melayu (Malaysia)",
            "mt_MT" => "Malti (Malta)",
            "my_MM" => "မြန်မာ (မြန်မာ)",
            "nan_TW" => "Min Nan Chinese (Taiwan)",
            "nan_TW@latin" => "Min Nan Chinese (Taiwan, LATIN)",
            "nb_NO" => "Norsk bokmål (Norge)",
            "nds_DE" => "Nds (DE)",
            "nds_NL" => "Nds (NL)",
            "ne_NP" => "नेपाली (नेपाल)",
            "nhn_MX" => "Nhn (Mexico)",
            "niu_NU" => "Niuean (Niue)",
            "niu_NZ" => "Niuean (New Zealand)",
            "nl_AW" => "Nederlands (Aruba)",
            "nl_BE" => "Nederlands (België)",
            "nl_NL" => "Nederlands (Nederland)",
            "nn_NO" => "Nynorsk (Noreg)",
            "no_NO" => "Norsk (Norge)",
            "nr_ZA" => "South Ndebele (South Africa)",
            "nso_ZA" => "Northern Sotho (South Africa)",
            "oc_FR" => "Occitan (France)",
            "om_ET" => "Oromoo (Itoophiyaa)",
            "om_KE" => "Oromoo (Keeniyaa)",
            "or_IN" => "ଓଡ଼ିଆ (ଭାରତ)",
            "os_RU" => "Ирон (Уӕрӕсе)",
            "pa_IN" => "ਪੰਜਾਬੀ (ਭਾਰਤ)",
            "pa_PK" => "پنجابی (پاکستان)",
            "pap_AW" => "Papiamento (Aruba)",
            "pap_CW" => "Papiamento (Curaçao)",
            "pl_PL" => "Polski (Polska)",
            "prs_AF" => "Prs (Afghanistan)",
            "ps_AF" => "پښتو (افغانستان)",
            "pt_BR" => "Português (Brasil)",
            "pt_PT" => "Português (Portugal)",
            "qut_GT" => "Qut (Guatemala)",
            "quz_BO" => "Quz (Bolivia)",
            "quz_EC" => "Quz (Ecuador)",
            "quz_PE" => "Quz (Peru)",
            "raj_IN" => "Rajasthani (India)",
            "rm_CH" => "Rumantsch (Svizra)",
            "ro_RO" => "Română (România)",
            "ru_RU" => "Русский (Россия)",
            "ru_UA" => "Русский (Украина)",
            "rw_RW" => "Kinyarwanda (U Rwanda)",
            "sa_IN" => "Sanskrit (India)",
            "sah_RU" => "Саха тыла (Арассыыйа)",
            "sat_IN" => "Santali (India)",
            "sc_IT" => "Sardinian (Italy)",
            "sd_IN" => "Sindhi (India)",
            "sd_IN@devanagari" => "Sindhi (India, DEVANAGARI)",
            "se_FI" => "Davvisámegiella (Suopma)",
            "se_NO" => "Davvisámegiella (Norga)",
            "se_SE" => "Davvisámegiella (Ruoŧŧa)",
            "sgs_LT" => "Samogitian (Lithuania)",
            "shn_MM" => "Shan (Myanmar [Burma])",
            "shs_CA" => "Shs (Canada)",
            "si_LK" => "සිංහල (ශ්‍රී ලංකාව)",
            "sid_ET" => "Sidamo (Ethiopia)",
            "sk_SK" => "Slovenčina (Slovensko)",
            "sl_SI" => "Slovenščina (Slovenija)",
            "sm_WS" => "Samoan (Samoa)",
            "sma_NO" => "Southern Sami (Norway)",
            "sma_SE" => "Southern Sami (Sweden)",
            "smj_NO" => "Lule Sami (Norway)",
            "smj_SE" => "Lule Sami (Sweden)",
            "smn_FI" => "Anarâškielâ (Suomâ)",
            "sms_FI" => "Skolt Sami (Finland)",
            "so_DJ" => "Soomaali (Jabuuti)",
            "so_ET" => "Soomaali (Itoobiya)",
            "so_KE" => "Soomaali (Kiiniya)",
            "so_SO" => "Soomaali (Soomaaliya)",
            "sq_AL" => "Shqip (Shqipëri)",
            "sq_MK" => "Shqip (Maqedoni)",
            "sr_BA" => "Српски (ћирилица, Босна и Херцеговина)",
            "sr_Latn_BA" => "Srpski (latinica, Bosna i Hercegovina)",
            "sr_Latn_ME" => "Srpski (latinica, Crna Gora)",
            "sr_Latn_RS" => "Srpski (latinica, Srbija)",
            "sr_ME" => "Srpski (Crna Gora)",
            "sr_RS" => "Српски (Србија)",
            "ss_ZA" => "Swati (South Africa)",
            "st_ZA" => "Southern Sotho (South Africa)",
            "sv_FI" => "Svenska (Finland)",
            "sv_SE" => "Svenska (Sverige)",
            "sw_KE" => "Kiswahili (Kenya)",
            "sw_TZ" => "Kiswahili (Tanzania)",
            "syr_SY" => "Syriac (Syria)",
            "szl_PL" => "Silesian (Poland)",
            "ta_IN" => "தமிழ் (இந்தியா)",
            "ta_LK" => "தமிழ் (இலங்கை)",
            "tcy_IN" => "Tulu (India)",
            "te_IN" => "తెలుగు (భారతదేశం)",
            "tg_TJ" => "Тоҷикӣ (Тоҷикистон)",
            "th_TH" => "ไทย (ไทย)",
            "the_NP" => "The (Nepal)",
            "ti_ER" => "ትግርኛ (ኤርትራ)",
            "ti_ET" => "ትግርኛ (ኢትዮጵያ)",
            "tig_ER" => "Tigre (Eritrea)",
            "tk_TM" => "Turkmen (Turkmenistan)",
            "tl_PH" => "Tagalog (Pilipinas)",
            "tn_ZA" => "Tswana (South Africa)",
            "to_TO" => "Lea fakatonga (Tonga)",
            "tpi_PG" => "Tok Pisin (Papua New Guinea)",
            "tr_CY" => "Türkçe (Kıbrıs)",
            "tr_TR" => "Türkçe (Türkiye)",
            "ts_ZA" => "Tsonga (South Africa)",
            "tt_RU" => "Татар (Россия)",
            "tt_RU@iqtelif" => "Татар (Россия, IQTELIF)",
            "ug_CN" => "ئۇيغۇرچە (جۇڭگو)",
            "uk_UA" => "Українська (Україна)",
            "unm_US" => "Unm (United States)",
            "ur_IN" => "اردو (بھارت)",
            "ur_PK" => "اردو (پاکستان)",
            "uz_UZ" => "O‘zbek (Oʻzbekiston)",
            "uz_UZ@cyrillic" => "Ўзбекча (Кирил, Ўзбекистон)",
            "ve_ZA" => "Venda (South Africa)",
            "vi_VN" => "Tiếng Việt (Việt Nam)",
            "wa_BE" => "Walloon (Belgium)",
            "wae_CH" => "Walser (Schwiz)",
            "wal_ET" => "Wolaytta (Ethiopia)",
            "wo_SN" => "Wolof (Senegaal)",
            "xh_ZA" => "Xhosa (South Africa)",
            "yi_US" => "ייִדיש (פֿאַראייניגטע שטאַטן)",
            "yo_NG" => "Èdè Yorùbá (Orílẹ́ède Nàìjíríà)",
            "yue_HK" => "粵語 (中華人民共和國香港特別行政區)",
            "yuw_PG" => "Yuw (Papua New Guinea)",
            "zh_CN" => "中文（中国）",
            "zh_HK" => "中文（中國香港特別行政區）",
            "zh_MO" => "中文（中國澳門特別行政區）",
            "zh_SG" => "中文（新加坡）",
            "zh_TW" => "中文（台灣）",
            "zu_ZA" => "IsiZulu (iNingizimu Afrika)",
        ];
        
        if ($display_code_only) {
            foreach ($LocList as $k => $v) {
                $LocList[$k] = $k;
            }
        }
        
        asort($LocList);
        
        return $LocList;
    }
    /**
     * Return the BCP 47 code that matches the code of a locale.
     *
     * The BCP 47 code is used in the lang attribute of any HTML tag.
     * The list of supported languages is simple enough to avoid having to encounter
     * any particular case.
     *
     * The resolution table was built by hand because there is no systematic
     * correspondence with an ISO language code.
     *
     * @see https://www.w3.org/International/questions/qa-html-language-declarations
     * @see https://www.w3.org/International/questions/qa-choosing-language-tags
     * @see https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
     *
     * @param $locale Code of a locale (format: ll[_CC][@variant])
     *
     * @return string
     */
    function localetoBCP47($locale)
    {
        $bcp47 = [
            "aa_DJ" => "aa",
            "aa_ER" => "aa",
            "aa_ER@saaho" => "aa",
            "aa_ET" => "aa",
            "af_ZA" => "af",
            "agr_PE" => "agr",
            "ak_GH" => "tw",
            "am_ET" => "am",
            "an_ES" => "an",
            "anp_IN" => "anp",
            "ar_AE" => "ssh",
            "ar_BH" => "ar", // Dubious
            "ar_DZ" => "ar", // Dubious
            "ar_EG" => "arz", // Dubious
            "ar_IN" => "ar", // Dubious
            "ar_IQ" => "ar", // Dubious
            "ar_JO" => "ar", // Dubious
            "ar_KW" => "ar", // Dubious
            "ar_LB" => "ar", // Dubious
            "ar_LY" => "ayl", // Dubious
            "ar_MA" => "ary", // Dubious
            "ar_OM" => "acx", // Dubious
            "ar_QA" => "ar", // Dubious
            "ar_SA" => "apd", // Dubious
            "ar_SD" => "pga", // Dubious
            "ar_SS" => "ar", // Dubious
            "ar_SY" => "ar", // Dubious
            "ar_TN" => "aeb", // Dubious
            "ar_YE" => "ayn", // Dubious
            "arn_CL" => "arn",
            "as_IN" => "as",
            "ast_ES" => "ast",
            "ayc_PE" => "ayc",
            "az_AZ" => "azj",
            "az_IR" => "azb",
            "ba_RU" => "ba",
            "be_BY" => "be",
            "be_BY@latin" => "be",
            "bem_ZM" => "bem",
            "ber_DZ" => "ber",
            "ber_MA" => "ber",
            "bg_BG" => "bg",
            "bhb_IN" => "bhb",
            "bho_IN" => "bho",
            "bho_NP" => "bho",
            "bi_VU" => "bi",
            "bn_BD" => "bn",
            "bn_IN" => "bn",
            "bo_CN" => "bo",
            "bo_IN" => "bo",
            "br_FR" => "br",
            "brx_IN" => "brx",
            "bs_BA" => "bs",
            "byn_ER" => "byn",
            "ca_AD" => "ca",
            "ca_ES" => "ca",
            "ca_FR" => "ca",
            "ca_IT" => "ca",
            "ce_RU" => "ce",
            "chr_US" => "chr",
            "cmn_TW" => "cmn",
            "co_FR" => "co",
            "crh_UA" => "crh",
            "cs_CZ" => "cs",
            "csb_PL" => "csb",
            "cv_RU" => "cv",
            "cy_GB" => "cy",
            "da_DK" => "da",
            "de_AT" => "de",
            "de_BE" => "de",
            "de_CH" => "de",
            "de_DE" => "de",
            "de_IT" => "de",
            "de_LI" => "de",
            "de_LU" => "de",
            "doi_IN" => "dgo",
            "dsb_DE" => "dsb",
            "dv_MV" => "dv",
            "dz_BT" => "dz",
            "el_CY" => "el",
            "el_GR" => "el",
            "en_029" => "en",
            "en_AG" => "en",
            "en_AU" => "en",
            "en_BW" => "en",
            "en_BZ" => "en",
            "en_CA" => "en",
            "en_DK" => "en",
            "en_GB" => "en",
            "en_HK" => "en",
            "en_IE" => "en",
            "en_IL" => "en",
            "en_IN" => "en",
            "en_JM" => "en",
            "en_MY" => "en",
            "en_NG" => "en",
            "en_NZ" => "en",
            "en_PH" => "en",
            "en_SC" => "en",
            "en_SG" => "en",
            "en_TT" => "en",
            "en_US" => "en",
            "en_ZA" => "en",
            "en_ZM" => "en",
            "en_ZW" => "en",
            "es_AR" => "es",
            "es_BO" => "es",
            "es_CH" => "es",
            "es_CL" => "es",
            "es_CO" => "es",
            "es_CR" => "es",
            "es_CU" => "es",
            "es_DO" => "es",
            "es_EC" => "es",
            "es_ES" => "es",
            "es_GQ" => "es",
            "es_GT" => "es",
            "es_HN" => "es",
            "es_MX" => "es",
            "es_NI" => "es",
            "es_PA" => "es",
            "es_PE" => "es",
            "es_PR" => "es",
            "es_PY" => "es",
            "es_SV" => "es",
            "es_US" => "es",
            "es_UY" => "es",
            "es_VE" => "es",
            "et_EE" => "et",
            "eu_ES" => "eu",
            "eu_FR" => "eu",
            "fa_IR" => "pes",
            "ff_SN" => "fuc",
            "fi_FI" => "fi",
            "fil_PH" => "fil",
            "fo_FO" => "fo",
            "fr_BE" => "fr",
            "fr_CA" => "fr",
            "fr_CH" => "fr",
            "fr_FR" => "fr",
            "fr_LU" => "fr",
            "fr_MC" => "fr",
            "fur_IT" => "fur",
            "fy_DE" => "fy",
            "fy_NL" => "fy",
            "ga_IE" => "ga",
            "gd_GB" => "gd",
            "gez_ER" => "gez",
            "gez_ER@abegede" => "gez",
            "gez_ET" => "gez",
            "gez_ET@abegede" => "gez",
            "gl_ES" => "gl",
            "gsw_FR" => "gsw",
            "gu_IN" => "gu",
            "gv_GB" => "gv",
            "ha_NG" => "ha",
            "hak_TW" => "hak",
            "he_IL" => "he",
            "hi_IN" => "hi",
            "hif_FJ" => "hif",
            "hne_IN" => "hne",
            "hr_BA" => "hr",
            "hr_HR" => "hr",
            "hsb_DE" => "hsb",
            "ht_HT" => "ht",
            "hu_HU" => "hu",
            "hy_AM" => "hy",
            "ia_FR" => "ia",
            "id_ID" => "id",
            "ig_NG" => "ig",
            "ii_CN" => "ii",
            "ik_CA" => "esk",
            "is_IS" => "is",
            "it_CH" => "it",
            "it_IT" => "it",
            "iu_CA" => "ike",
            "ja_JP" => "ja",
            "ka_GE" => "ka",
            "kab_DZ" => "kab",
            "kk_KZ" => "kk",
            "kl_GL" => "kl",
            "km_KH" => "km",
            "kn_IN" => "kn",
            "ko_KR" => "ko",
            "kok_IN" => "knn",
            "ks_IN" => "ks",
            "ks_IN@devanagari" => "ks",
            "ku_TR" => "ku",
            "kw_GB" => "kw",
            "ky_KG" => "ky",
            "lb_LU" => "lb",
            "lg_UG" => "lg",
            "li_BE" => "li",
            "li_NL" => "li",
            "lij_IT" => "lij",
            "ln_CD" => "ln",
            "lo_LA" => "lo",
            "lt_LT" => "lt",
            "lv_LV" => "lt",
            "lzh_TW" => "lzh",
            "mag_IN" => "mag",
            "mai_IN" => "mai",
            "mai_NP" => "mai",
            "mfe_MU" => "mfe",
            "mg_MG" => "mg",
            "mhr_RU" => "mhr",
            "mi_NZ" => "mi",
            "miq_NI" => "miq",
            "mjw_IN" => "mjw",
            "mk_MK" => "mk",
            "ml_IN" => "ml",
            "mn_MN" => "mn",
            "mni_IN" => "mni",
            "moh_CA" => "moh",
            "mr_IN" => "mr",
            "ms_BN" => "kxd",
            "ms_MY" => "zsm",
            "mt_MT" => "mt",
            "my_MM" => "my",
            "nan_TW" => "nan",
            "nan_TW@latin" => "nan",
            "nb_NO" => "nb",
            "nds_DE" => "nds",
            "nds_NL" => "nds",
            "ne_NP" => "ne",
            "nhn_MX" => "nhn",
            "niu_NU" => "niu",
            "niu_NZ" => "niu",
            "nl_AW" => "nl",
            "nl_BE" => "nl",
            "nl_NL" => "nl",
            "nn_NO" => "nn",
            "no_NO" => "no",
            "nr_ZA" => "nr",
            "nso_ZA" => "nso",
            "oc_FR" => "oc",
            "om_ET" => "hae",
            "om_KE" => "gaz",
            "or_IN" => "or",
            "os_RU" => "os",
            "pa_IN" => "pa",
            "pa_PK" => "pa",
            "pap_AW" => "pap",
            "pap_CW" => "pap",
            "pl_PL" => "pl",
            "prs_AF" => "prs",
            "ps_AF" => "ps",
            "pt_BR" => "pt",
            "pt_PT" => "pt",
            "qut_GT" => "es", // Missing BCP47 code: instead use the official language of Guatemala is Spanish
            "quz_BO" => "quz",
            "quz_EC" => "quz",
            "quz_PE" => "quz",
            "raj_IN" => "raj",
            "rm_CH" => "rm",
            "ro_RO" => "ro",
            "ru_RU" => "ru",
            "ru_UA" => "ru",
            "rw_RW" => "rw",
            "sa_IN" => "sa",
            "sah_RU" => "sah",
            "sat_IN" => "sat",
            "sc_IT" => "sc",
            "sd_IN" => "sd",
            "sd_IN@devanagari" => "sd",
            "se_FI" => "se",
            "se_NO" => "se",
            "se_SE" => "se",
            "sgs_LT" => "sgs",
            "shn_MM" => "shn",
            "shs_CA" => "shs",
            "si_LK" => "si",
            "sid_ET" => "sid",
            "sk_SK" => "sk",
            "sl_SI" => "sl",
            "sm_WS" => "sm",
            "sma_NO" => "sma",
            "sma_SE" => "sma",
            "smj_NO" => "smj",
            "smj_SE" => "smj",
            "smn_FI" => "smn",
            "sms_FI" => "sms",
            "so_DJ" => "so",
            "so_ET" => "so",
            "so_KE" => "so",
            "so_SO" => "so",
            "sq_AL" => "sq",
            "sq_MK" => "sq",
            "sr_BA" => "sr",
            "sr_Latn_BA" => "sr",
            "sr_Latn_ME" => "sr",
            "sr_Latn_RS" => "sr",
            "sr_ME" => "sr",
            "sr_RS" => "sr",
            "ss_ZA" => "ss",
            "st_ZA" => "st",
            "sv_FI" => "sv",
            "sv_SE" => "sv",
            "sw_KE" => "swh",
            "sw_TZ" => "swh",
            "syr_SY" => "syr",
            "szl_PL" => "szl",
            "ta_IN" => "ta",
            "ta_LK" => "ta",
            "tcy_IN" => "tcy",
            "te_IN" => "te",
            "tg_TJ" => "tg",
            "th_TH" => "th",
            "the_NP" => "the",
            "ti_ER" => "ti",
            "ti_ET" => "ti",
            "tig_ER" => "tig",
            "tk_TM" => "tk",
            "tl_PH" => "tl",
            "tn_ZA" => "tn",
            "to_TO" => "to",
            "tpi_PG" => "to",
            "tr_CY" => "tr",
            "tr_TR" => "tr",
            "ts_ZA" => "ts",
            "tt_RU" => "tt",
            "tt_RU@iqtelif" => "tt",
            "ug_CN" => "ug",
            "uk_UA" => "uk",
            "unm_US" => "unm",
            "ur_IN" => "اur",
            "ur_PK" => "ur",
            "uz_UZ" => "uz",
            "uz_UZ@cyrillic" => "uz",
            "ve_ZA" => "ve",
            "vi_VN" => "vi",
            "wa_BE" => "wa",
            "wae_CH" => "wae",
            "wal_ET" => "wal",
            "wo_SN" => "wo",
            "xh_ZA" => "xh",
            "yi_US" => "yi",
            "yo_NG" => "yo",
            "yue_HK" => "yue",
            "yuw_PG" => "yuw",
            "zh_CN" => "zh",
            "zh_HK" => "zh",
            "zh_MO" => "zh",
            "zh_SG" => "zh",
            "zh_TW" => "zh",
            "zu_ZA" => "zu",
        ];
        
        return array_key_exists($locale, $bcp47) ? $bcp47[$locale] : "";
    }
}
