<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

// Last update : 2018-08-19
// http://www.unicode.org/versions/Unicode11.0.0

// This array is a unchanged copy of the left tab at:
// https://www.unicode.org/charts/beta/nameslist/
// It's the official definition of Unicode Nameslist Charts
//
// Each array defines a characters set which fields are:
//
// fcp: unicode code point of the left bound
// lcp: unicode code point of the right bound
//  id: unicode name

$tableChars = [
    ['fcp' => '0000', 'id' => 'C0 Controls and Basic Latin', 'lcp' => '007F'],
    ['fcp' => '0080', 'id' => 'C1 Controls and Latin-1 Supplement', 'lcp' => '00FF'],
    ['fcp' => '0100', 'id' => 'Latin Extended-A', 'lcp' => '017F'],
    ['fcp' => '0180', 'id' => 'Latin Extended-B', 'lcp' => '024F'],
    ['fcp' => '0250', 'id' => 'IPA Extensions', 'lcp' => '02AF'],
    ['fcp' => '02B0', 'id' => 'Spacing Modifier Letters', 'lcp' => '02FF'],
    ['fcp' => '0300', 'id' => 'Combining Diacritical Marks', 'lcp' => '036F'],
    ['fcp' => '0370', 'id' => 'Greek and Coptic', 'lcp' => '03FF'],
    ['fcp' => '0400', 'id' => 'Cyrillic', 'lcp' => '04FF'],
    ['fcp' => '0500', 'id' => 'Cyrillic Supplement', 'lcp' => '052F'],
    ['fcp' => '0530', 'id' => 'Armenian', 'lcp' => '058F'],
    ['fcp' => '0590', 'id' => 'Hebrew', 'lcp' => '05FF'],
    ['fcp' => '0600', 'id' => 'Arabic', 'lcp' => '06FF'],
    ['fcp' => '0700', 'id' => 'Syriac', 'lcp' => '074F'],
    ['fcp' => '0750', 'id' => 'Arabic Supplement', 'lcp' => '077F'],
    ['fcp' => '0780', 'id' => 'Thaana', 'lcp' => '07BF'],
    ['fcp' => '07C0', 'id' => 'NKo', 'lcp' => '07FF'],
    ['fcp' => '0800', 'id' => 'Samaritan', 'lcp' => '083F'],
    ['fcp' => '0840', 'id' => 'Mandaic', 'lcp' => '085F'],
    ['fcp' => '0860', 'id' => 'Syriac Supplement', 'lcp' => '086F'],
    ['fcp' => '08A0', 'id' => 'Arabic Extended-A', 'lcp' => '08FF'],
    ['fcp' => '0900', 'id' => 'Devanagari', 'lcp' => '097F'],
    ['fcp' => '0980', 'id' => 'Bengali', 'lcp' => '09FF'],
    ['fcp' => '0A00', 'id' => 'Gurmukhi', 'lcp' => '0A7F'],
    ['fcp' => '0A80', 'id' => 'Gujarati', 'lcp' => '0AFF'],
    ['fcp' => '0B00', 'id' => 'Oriya', 'lcp' => '0B7F'],
    ['fcp' => '0B80', 'id' => 'Tamil', 'lcp' => '0BFF'],
    ['fcp' => '0C00', 'id' => 'Telugu', 'lcp' => '0C7F'],
    ['fcp' => '0C80', 'id' => 'Kannada', 'lcp' => '0CFF'],
    ['fcp' => '0D00', 'id' => 'Malayalam', 'lcp' => '0D7F'],
    ['fcp' => '0D80', 'id' => 'Sinhala', 'lcp' => '0DFF'],
    ['fcp' => '0E00', 'id' => 'Thai', 'lcp' => '0E7F'],
    ['fcp' => '0E80', 'id' => 'Lao', 'lcp' => '0EFF'],
    ['fcp' => '0F00', 'id' => 'Tibetan', 'lcp' => '0FFF'],
    ['fcp' => '1000', 'id' => 'Myanmar', 'lcp' => '109F'],
    ['fcp' => '10A0', 'id' => 'Georgian', 'lcp' => '10FF'],
    ['fcp' => '1100', 'id' => 'Hangul Jamo', 'lcp' => '11FF'],
    ['fcp' => '1200', 'id' => 'Ethiopic', 'lcp' => '137F'],
    ['fcp' => '1380', 'id' => 'Ethiopic Supplement', 'lcp' => '139F'],
    ['fcp' => '13A0', 'id' => 'Cherokee', 'lcp' => '13FF'],
    ['fcp' => '1400', 'id' => 'Unified Canadian Aboriginal Syllabics', 'lcp' => '167F'],
    ['fcp' => '1680', 'id' => 'Ogham', 'lcp' => '169F'],
    ['fcp' => '16A0', 'id' => 'Runic', 'lcp' => '16FF'],
    ['fcp' => '1700', 'id' => 'Tagalog', 'lcp' => '171F'],
    ['fcp' => '1720', 'id' => 'Hanunoo', 'lcp' => '173F'],
    ['fcp' => '1740', 'id' => 'Buhid', 'lcp' => '175F'],
    ['fcp' => '1760', 'id' => 'Tagbanwa', 'lcp' => '177F'],
    ['fcp' => '1780', 'id' => 'Khmer', 'lcp' => '17FF'],
    ['fcp' => '1800', 'id' => 'Mongolian', 'lcp' => '18AF'],
    ['fcp' => '18B0', 'id' => 'Unified Canadian Aboriginal Syllabics Extended', 'lcp' => '18FF'],
    ['fcp' => '1900', 'id' => 'Limbu', 'lcp' => '194F'],
    ['fcp' => '1950', 'id' => 'Tai Le', 'lcp' => '197F'],
    ['fcp' => '1980', 'id' => 'New Tai Lue', 'lcp' => '19DF'],
    ['fcp' => '19E0', 'id' => 'Khmer Symbols', 'lcp' => '19FF'],
    ['fcp' => '1A00', 'id' => 'Buginese', 'lcp' => '1A1F'],
    ['fcp' => '1A20', 'id' => 'Tai Tham', 'lcp' => '1AAF'],
    ['fcp' => '1AB0', 'id' => 'Combining Diacritical Marks Extended', 'lcp' => '1AFF'],
    ['fcp' => '1B00', 'id' => 'Balinese', 'lcp' => '1B7F'],
    ['fcp' => '1B80', 'id' => 'Sundanese', 'lcp' => '1BBF'],
    ['fcp' => '1BC0', 'id' => 'Batak', 'lcp' => '1BFF'],
    ['fcp' => '1C00', 'id' => 'Lepcha', 'lcp' => '1C4F'],
    ['fcp' => '1C50', 'id' => 'Ol Chiki', 'lcp' => '1C7F'],
    ['fcp' => '1C80', 'id' => 'Cyrillic Extended-C', 'lcp' => '1C8F'],
    ['fcp' => '1C90', 'id' => 'Georgian Extended', 'lcp' => '1CBF'],
    ['fcp' => '1CC0', 'id' => 'Sundanese Supplement', 'lcp' => '1CCF'],
    ['fcp' => '1CD0', 'id' => 'Vedic Extensions', 'lcp' => '1CFF'],
    ['fcp' => '1D00', 'id' => 'Phonetic Extensions', 'lcp' => '1D7F'],
    ['fcp' => '1D80', 'id' => 'Phonetic Extensions Supplement', 'lcp' => '1DBF'],
    ['fcp' => '1DC0', 'id' => 'Combining Diacritical Marks Supplement', 'lcp' => '1DFF'],
    ['fcp' => '1E00', 'id' => 'Latin Extended Additional', 'lcp' => '1EFF'],
    ['fcp' => '1F00', 'id' => 'Greek Extended', 'lcp' => '1FFF'],
    ['fcp' => '2000', 'id' => 'General Punctuation', 'lcp' => '206F'],
    ['fcp' => '2070', 'id' => 'Superscripts and Subscripts', 'lcp' => '209F'],
    ['fcp' => '20A0', 'id' => 'Currency Symbols', 'lcp' => '20CF'],
    ['fcp' => '20D0', 'id' => 'Combining Diacritical Marks for Symbols', 'lcp' => '20FF'],
    ['fcp' => '2100', 'id' => 'Letterlike Symbols', 'lcp' => '214F'],
    ['fcp' => '2150', 'id' => 'Number Forms', 'lcp' => '218F'],
    ['fcp' => '2190', 'id' => 'Arrows', 'lcp' => '21FF'],
    ['fcp' => '2200', 'id' => 'Mathematical Operators', 'lcp' => '22FF'],
    ['fcp' => '2300', 'id' => 'Miscellaneous Technical', 'lcp' => '23FF'],
    ['fcp' => '2400', 'id' => 'Control Pictures', 'lcp' => '243F'],
    ['fcp' => '2440', 'id' => 'Optical Character Recognition', 'lcp' => '245F'],
    ['fcp' => '2460', 'id' => 'Enclosed Alphanumerics', 'lcp' => '24FF'],
    ['fcp' => '2500', 'id' => 'Box Drawing', 'lcp' => '257F'],
    ['fcp' => '2580', 'id' => 'Block Elements', 'lcp' => '259F'],
    ['fcp' => '25A0', 'id' => 'Geometric Shapes', 'lcp' => '25FF'],
    ['fcp' => '2600', 'id' => 'Miscellaneous Symbols', 'lcp' => '26FF'],
    ['fcp' => '2700', 'id' => 'Dingbats', 'lcp' => '27BF'],
    ['fcp' => '27C0', 'id' => 'Miscellaneous Mathematical Symbols-A', 'lcp' => '27EF'],
    ['fcp' => '27F0', 'id' => 'Supplemental Arrows-A', 'lcp' => '27FF'],
    ['fcp' => '2800', 'id' => 'Braille Patterns', 'lcp' => '28FF'],
    ['fcp' => '2900', 'id' => 'Supplemental Arrows-B', 'lcp' => '297F'],
    ['fcp' => '2980', 'id' => 'Miscellaneous Mathematical Symbols-B', 'lcp' => '29FF'],
    ['fcp' => '2A00', 'id' => 'Supplemental Mathematical Operators', 'lcp' => '2AFF'],
    ['fcp' => '2B00', 'id' => 'Miscellaneous Symbols and Arrows', 'lcp' => '2BFF'],
    ['fcp' => '2C00', 'id' => 'Glagolitic', 'lcp' => '2C5F'],
    ['fcp' => '2C60', 'id' => 'Latin Extended-C', 'lcp' => '2C7F'],
    ['fcp' => '2C80', 'id' => 'Coptic', 'lcp' => '2CFF'],
    ['fcp' => '2D00', 'id' => 'Georgian Supplement', 'lcp' => '2D2F'],
    ['fcp' => '2D30', 'id' => 'Tifinagh', 'lcp' => '2D7F'],
    ['fcp' => '2D80', 'id' => 'Ethiopic Extended', 'lcp' => '2DDF'],
    ['fcp' => '2DE0', 'id' => 'Cyrillic Extended-A', 'lcp' => '2DFF'],
    ['fcp' => '2E00', 'id' => 'Supplemental Punctuation', 'lcp' => '2E7F'],
    ['fcp' => '2E80', 'id' => 'CJK Radicals Supplement', 'lcp' => '2EFF'],
    ['fcp' => '2F00', 'id' => 'Kangxi Radicals', 'lcp' => '2FDF'],
    ['fcp' => '2FF0', 'id' => 'Ideographic Description Characters', 'lcp' => '2FFF'],
    ['fcp' => '3000', 'id' => 'CJK Symbols and Punctuation', 'lcp' => '303F'],
    ['fcp' => '3040', 'id' => 'Hiragana', 'lcp' => '309F'],
    ['fcp' => '30A0', 'id' => 'Katakana', 'lcp' => '30FF'],
    ['fcp' => '3100', 'id' => 'Bopomofo', 'lcp' => '312F'],
    ['fcp' => '3130', 'id' => 'Hangul Compatibility Jamo', 'lcp' => '318F'],
    ['fcp' => '3190', 'id' => 'Kanbun', 'lcp' => '319F'],
    ['fcp' => '31A0', 'id' => 'Bopomofo Extended', 'lcp' => '31BF'],
    ['fcp' => '31C0', 'id' => 'CJK Strokes', 'lcp' => '31EF'],
    ['fcp' => '31F0', 'id' => 'Katakana Phonetic Extensions', 'lcp' => '31FF'],
    ['fcp' => '3200', 'id' => 'Enclosed CJK Letters and Months', 'lcp' => '32FF'],
    ['fcp' => '3300', 'id' => 'CJK Compatibility', 'lcp' => '33FF'],
    ['fcp' => '3400', 'id' => 'CJK Unified Ideographs Extension A', 'lcp' => '4DB5'],
    ['fcp' => '4DC0', 'id' => 'Yijing Hexagram Symbols', 'lcp' => '4DFF'],
    ['fcp' => '4E00', 'id' => 'CJK Unified Ideographs', 'lcp' => '9FEF'],
    ['fcp' => 'A000', 'id' => 'Yi Syllables', 'lcp' => 'A48F'],
    ['fcp' => 'A490', 'id' => 'Yi Radicals', 'lcp' => 'A4CF'],
    ['fcp' => 'A4D0', 'id' => 'Lisu', 'lcp' => 'A4FF'],
    ['fcp' => 'A500', 'id' => 'Vai', 'lcp' => 'A63F'],
    ['fcp' => 'A640', 'id' => 'Cyrillic Extended-B', 'lcp' => 'A69F'],
    ['fcp' => 'A6A0', 'id' => 'Bamum', 'lcp' => 'A6FF'],
    ['fcp' => 'A700', 'id' => 'Modifier Tone Letters', 'lcp' => 'A71F'],
    ['fcp' => 'A720', 'id' => 'Latin Extended-D', 'lcp' => 'A7FF'],
    ['fcp' => 'A800', 'id' => 'Syloti Nagri', 'lcp' => 'A82F'],
    ['fcp' => 'A830', 'id' => 'Common Indic Number Forms', 'lcp' => 'A83F'],
    ['fcp' => 'A840', 'id' => 'Phags-pa', 'lcp' => 'A87F'],
    ['fcp' => 'A880', 'id' => 'Saurashtra', 'lcp' => 'A8DF'],
    ['fcp' => 'A8E0', 'id' => 'Devanagari Extended', 'lcp' => 'A8FF'],
    ['fcp' => 'A900', 'id' => 'Kayah Li', 'lcp' => 'A92F'],
    ['fcp' => 'A930', 'id' => 'Rejang', 'lcp' => 'A95F'],
    ['fcp' => 'A960', 'id' => 'Hangul Jamo Extended-A', 'lcp' => 'A97F'],
    ['fcp' => 'A980', 'id' => 'Javanese', 'lcp' => 'A9DF'],
    ['fcp' => 'A9E0', 'id' => 'Myanmar Extended-B', 'lcp' => 'A9FF'],
    ['fcp' => 'AA00', 'id' => 'Cham', 'lcp' => 'AA5F'],
    ['fcp' => 'AA60', 'id' => 'Myanmar Extended-A', 'lcp' => 'AA7F'],
    ['fcp' => 'AA80', 'id' => 'Tai Viet', 'lcp' => 'AADF'],
    ['fcp' => 'AAE0', 'id' => 'Meetei Mayek Extensions', 'lcp' => 'AAFF'],
    ['fcp' => 'AB00', 'id' => 'Ethiopic Extended-A', 'lcp' => 'AB2F'],
    ['fcp' => 'AB30', 'id' => 'Latin Extended-E', 'lcp' => 'AB6F'],
    ['fcp' => 'AB70', 'id' => 'Cherokee Supplement', 'lcp' => 'ABBF'],
    ['fcp' => 'ABC0', 'id' => 'Meetei Mayek', 'lcp' => 'ABFF'],
    ['fcp' => 'AC00', 'id' => 'Hangul Syllables', 'lcp' => 'D7A3'],
    ['fcp' => 'D7B0', 'id' => 'Hangul Jamo Extended-B', 'lcp' => 'D7FF'],
    ['fcp' => 'D800', 'id' => 'High Surrogates', 'lcp' => 'DB7F'],
    ['fcp' => 'DB80', 'id' => 'High Private Use Surrogates', 'lcp' => 'DBFF'],
    ['fcp' => 'DC00', 'id' => 'Low Surrogates', 'lcp' => 'DFFF'],
    ['fcp' => 'E000', 'id' => 'Private Use Area', 'lcp' => 'F8FF'],
    ['fcp' => 'F900', 'id' => 'CJK Compatibility Ideographs', 'lcp' => 'FAFF'],
    ['fcp' => 'FB00', 'id' => 'Alphabetic Presentation Forms', 'lcp' => 'FB4F'],
    ['fcp' => 'FB50', 'id' => 'Arabic Presentation Forms-A', 'lcp' => 'FDFF'],
    ['fcp' => 'FE00', 'id' => 'Variation Selectors', 'lcp' => 'FE0F'],
    ['fcp' => 'FE10', 'id' => 'Vertical Forms', 'lcp' => 'FE1F'],
    ['fcp' => 'FE20', 'id' => 'Combining Half Marks', 'lcp' => 'FE2F'],
    ['fcp' => 'FE30', 'id' => 'CJK Compatibility Forms', 'lcp' => 'FE4F'],
    ['fcp' => 'FE50', 'id' => 'Small Form Variants', 'lcp' => 'FE6F'],
    ['fcp' => 'FE70', 'id' => 'Arabic Presentation Forms-B', 'lcp' => 'FEFF'],
    ['fcp' => 'FF00', 'id' => 'Halfwidth and Fullwidth Forms', 'lcp' => 'FFEF'],
    ['fcp' => 'FFF0', 'id' => 'Specials', 'lcp' => 'FFFF'],
    ['fcp' => '10000', 'id' => 'Linear B Syllabary', 'lcp' => '1007F'],
    ['fcp' => '10080', 'id' => 'Linear B Ideograms', 'lcp' => '100FF'],
    ['fcp' => '10100', 'id' => 'Aegean Numbers', 'lcp' => '1013F'],
    ['fcp' => '10140', 'id' => 'Ancient Greek Numbers', 'lcp' => '1018F'],
    ['fcp' => '10190', 'id' => 'Ancient Symbols', 'lcp' => '101CF'],
    ['fcp' => '101D0', 'id' => 'Phaistos Disc', 'lcp' => '101FF'],
    ['fcp' => '10280', 'id' => 'Lycian', 'lcp' => '1029F'],
    ['fcp' => '102A0', 'id' => 'Carian', 'lcp' => '102DF'],
    ['fcp' => '102E0', 'id' => 'Coptic Epact Numbers', 'lcp' => '102FF'],
    ['fcp' => '10300', 'id' => 'Old Italic', 'lcp' => '1032F'],
    ['fcp' => '10330', 'id' => 'Gothic', 'lcp' => '1034F'],
    ['fcp' => '10350', 'id' => 'Old Permic', 'lcp' => '1037F'],
    ['fcp' => '10380', 'id' => 'Ugaritic', 'lcp' => '1039F'],
    ['fcp' => '103A0', 'id' => 'Old Persian', 'lcp' => '103DF'],
    ['fcp' => '10400', 'id' => 'Deseret', 'lcp' => '1044F'],
    ['fcp' => '10450', 'id' => 'Shavian', 'lcp' => '1047F'],
    ['fcp' => '10480', 'id' => 'Osmanya', 'lcp' => '104AF'],
    ['fcp' => '104B0', 'id' => 'Osage', 'lcp' => '104FF'],
    ['fcp' => '10500', 'id' => 'Elbasan', 'lcp' => '1052F'],
    ['fcp' => '10530', 'id' => 'Caucasian Albanian', 'lcp' => '1056F'],
    ['fcp' => '10600', 'id' => 'Linear A', 'lcp' => '1077F'],
    ['fcp' => '10800', 'id' => 'Cypriot Syllabary', 'lcp' => '1083F'],
    ['fcp' => '10840', 'id' => 'Imperial Aramaic', 'lcp' => '1085F'],
    ['fcp' => '10860', 'id' => 'Palmyrene', 'lcp' => '1087F'],
    ['fcp' => '10880', 'id' => 'Nabataean', 'lcp' => '108AF'],
    ['fcp' => '108E0', 'id' => 'Hatran', 'lcp' => '108FF'],
    ['fcp' => '10900', 'id' => 'Phoenician', 'lcp' => '1091F'],
    ['fcp' => '10920', 'id' => 'Lydian', 'lcp' => '1093F'],
    ['fcp' => '10980', 'id' => 'Meroitic Hieroglyphs', 'lcp' => '1099F'],
    ['fcp' => '109A0', 'id' => 'Meroitic Cursive', 'lcp' => '109FF'],
    ['fcp' => '10A00', 'id' => 'Kharoshthi', 'lcp' => '10A5F'],
    ['fcp' => '10A60', 'id' => 'Old South Arabian', 'lcp' => '10A7F'],
    ['fcp' => '10A80', 'id' => 'Old North Arabian', 'lcp' => '10A9F'],
    ['fcp' => '10AC0', 'id' => 'Manichaean', 'lcp' => '10AFF'],
    ['fcp' => '10B00', 'id' => 'Avestan', 'lcp' => '10B3F'],
    ['fcp' => '10B40', 'id' => 'Inscriptional Parthian', 'lcp' => '10B5F'],
    ['fcp' => '10B60', 'id' => 'Inscriptional Pahlavi', 'lcp' => '10B7F'],
    ['fcp' => '10B80', 'id' => 'Psalter Pahlavi', 'lcp' => '10BAF'],
    ['fcp' => '10C00', 'id' => 'Old Turkic', 'lcp' => '10C4F'],
    ['fcp' => '10C80', 'id' => 'Old Hungarian', 'lcp' => '10CFF'],
    ['fcp' => '10D00', 'id' => 'Hanifi Rohingya', 'lcp' => '10D3F'],
    ['fcp' => '10E60', 'id' => 'Rumi Numeral Symbols', 'lcp' => '10E7F'],
    ['fcp' => '10F00', 'id' => 'Old Sogdian', 'lcp' => '10F2F'],
    ['fcp' => '10F30', 'id' => 'Sogdian', 'lcp' => '10F6F'],
    ['fcp' => '11000', 'id' => 'Brahmi', 'lcp' => '1107F'],
    ['fcp' => '11080', 'id' => 'Kaithi', 'lcp' => '110CF'],
    ['fcp' => '110D0', 'id' => 'Sora Sompeng', 'lcp' => '110FF'],
    ['fcp' => '11100', 'id' => 'Chakma', 'lcp' => '1114F'],
    ['fcp' => '11150', 'id' => 'Mahajani', 'lcp' => '1117F'],
    ['fcp' => '11180', 'id' => 'Sharada', 'lcp' => '111DF'],
    ['fcp' => '111E0', 'id' => 'Sinhala Archaic Numbers', 'lcp' => '111FF'],
    ['fcp' => '11200', 'id' => 'Khojki', 'lcp' => '1124F'],
    ['fcp' => '11280', 'id' => 'Multani', 'lcp' => '112AF'],
    ['fcp' => '112B0', 'id' => 'Khudawadi', 'lcp' => '112FF'],
    ['fcp' => '11300', 'id' => 'Grantha', 'lcp' => '1137F'],
    ['fcp' => '11400', 'id' => 'Newa', 'lcp' => '1147F'],
    ['fcp' => '11480', 'id' => 'Tirhuta', 'lcp' => '114DF'],
    ['fcp' => '11580', 'id' => 'Siddham', 'lcp' => '115FF'],
    ['fcp' => '11600', 'id' => 'Modi', 'lcp' => '1165F'],
    ['fcp' => '11660', 'id' => 'Mongolian Supplement', 'lcp' => '1167F'],
    ['fcp' => '11680', 'id' => 'Takri', 'lcp' => '116CF'],
    ['fcp' => '11700', 'id' => 'Ahom', 'lcp' => '1173F'],
    ['fcp' => '11800', 'id' => 'Dogra', 'lcp' => '1184F'],
    ['fcp' => '118A0', 'id' => 'Warang Citi', 'lcp' => '118FF'],
    ['fcp' => '11A00', 'id' => 'Zanabazar Square', 'lcp' => '11A4F'],
    ['fcp' => '11A50', 'id' => 'Soyombo', 'lcp' => '11AAF'],
    ['fcp' => '11AC0', 'id' => 'Pau Cin Hau', 'lcp' => '11AFF'],
    ['fcp' => '11C00', 'id' => 'Bhaiksuki', 'lcp' => '11C6F'],
    ['fcp' => '11C70', 'id' => 'Marchen', 'lcp' => '11CBF'],
    ['fcp' => '11D00', 'id' => 'Masaram Gondi', 'lcp' => '11D5F'],
    ['fcp' => '11D60', 'id' => 'Gunjala Gondi', 'lcp' => '11DAF'],
    ['fcp' => '11EE0', 'id' => 'Makasar', 'lcp' => '11EFF'],
    ['fcp' => '12000', 'id' => 'Cuneiform', 'lcp' => '123FF'],
    ['fcp' => '12400', 'id' => 'Cuneiform Numbers and Punctuation', 'lcp' => '1247F'],
    ['fcp' => '12480', 'id' => 'Early Dynastic Cuneiform', 'lcp' => '1254F'],
    ['fcp' => '13000', 'id' => 'Egyptian Hieroglyphs', 'lcp' => '1342F'],
    ['fcp' => '14400', 'id' => 'Anatolian Hieroglyphs', 'lcp' => '1467F'],
    ['fcp' => '16800', 'id' => 'Bamum Supplement', 'lcp' => '16A3F'],
    ['fcp' => '16A40', 'id' => 'Mro', 'lcp' => '16A6F'],
    ['fcp' => '16AD0', 'id' => 'Bassa Vah', 'lcp' => '16AFF'],
    ['fcp' => '16B00', 'id' => 'Pahawh Hmong', 'lcp' => '16B8F'],
    ['fcp' => '16E40', 'id' => 'Medefaidrin', 'lcp' => '16E9F'],
    ['fcp' => '16F00', 'id' => 'Miao', 'lcp' => '16F9F'],
    ['fcp' => '16FE0', 'id' => 'Ideographic Symbols and Punctuation', 'lcp' => '16FFF'],
    ['fcp' => '17000', 'id' => 'Tangut', 'lcp' => '187F1'],
    ['fcp' => '18800', 'id' => 'Tangut Components', 'lcp' => '18AFF'],
    ['fcp' => '1B000', 'id' => 'Kana Supplement', 'lcp' => '1B0FF'],
    ['fcp' => '1B100', 'id' => 'Kana Extended-A', 'lcp' => '1B12F'],
    ['fcp' => '1B170', 'id' => 'Nushu', 'lcp' => '1B2FF'],
    ['fcp' => '1BC00', 'id' => 'Duployan', 'lcp' => '1BC9F'],
    ['fcp' => '1BCA0', 'id' => 'Shorthand Format Controls', 'lcp' => '1BCAF'],
    ['fcp' => '1D000', 'id' => 'Byzantine Musical Symbols', 'lcp' => '1D0FF'],
    ['fcp' => '1D100', 'id' => 'Musical Symbols', 'lcp' => '1D1FF'],
    ['fcp' => '1D200', 'id' => 'Ancient Greek Musical Notation', 'lcp' => '1D24F'],
    ['fcp' => '1D2E0', 'id' => 'Mayan Numerals', 'lcp' => '1D2FF'],
    ['fcp' => '1D300', 'id' => 'Tai Xuan Jing Symbols', 'lcp' => '1D35F'],
    ['fcp' => '1D360', 'id' => 'Counting Rod Numerals', 'lcp' => '1D37F'],
    ['fcp' => '1D400', 'id' => 'Mathematical Alphanumeric Symbols', 'lcp' => '1D7FF'],
    ['fcp' => '1D800', 'id' => 'Sutton SignWriting', 'lcp' => '1DAAF'],
    ['fcp' => '1E000', 'id' => 'Glagolitic Supplement', 'lcp' => '1E02F'],
    ['fcp' => '1E800', 'id' => 'Mende Kikakui', 'lcp' => '1E8DF'],
    ['fcp' => '1E900', 'id' => 'Adlam', 'lcp' => '1E95F'],
    ['fcp' => '1EC70', 'id' => 'Indic Siyaq Numbers', 'lcp' => '1ECBF'],
    ['fcp' => '1EE00', 'id' => 'Arabic Mathematical Alphabetic Symbols', 'lcp' => '1EEFF'],
    ['fcp' => '1F000', 'id' => 'Mahjong Tiles', 'lcp' => '1F02F'],
    ['fcp' => '1F030', 'id' => 'Domino Tiles', 'lcp' => '1F09F'],
    ['fcp' => '1F0A0', 'id' => 'Playing Cards', 'lcp' => '1F0FF'],
    ['fcp' => '1F100', 'id' => 'Enclosed Alphanumeric Supplement', 'lcp' => '1F1FF'],
    ['fcp' => '1F200', 'id' => 'Enclosed Ideographic Supplement', 'lcp' => '1F2FF'],
    ['fcp' => '1F300', 'id' => 'Miscellaneous Symbols and Pictographs', 'lcp' => '1F5FF'],
    ['fcp' => '1F600', 'id' => 'Emoticons', 'lcp' => '1F64F'],
    ['fcp' => '1F650', 'id' => 'Ornamental Dingbats', 'lcp' => '1F67F'],
    ['fcp' => '1F680', 'id' => 'Transport and Map Symbols', 'lcp' => '1F6FF'],
    ['fcp' => '1F700', 'id' => 'Alchemical Symbols', 'lcp' => '1F77F'],
    ['fcp' => '1F780', 'id' => 'Geometric Shapes Extended', 'lcp' => '1F7FF'],
    ['fcp' => '1F800', 'id' => 'Supplemental Arrows-C', 'lcp' => '1F8FF'],
    ['fcp' => '1F900', 'id' => 'Supplemental Symbols and Pictographs', 'lcp' => '1F9FF'],
    ['fcp' => '1FA00', 'id' => 'Chess Symbols', 'lcp' => '1FA6F'],
    ['fcp' => '1FF80', 'id' => 'Unassigned', 'lcp' => '1FFFF'],
    ['fcp' => '20000', 'id' => 'CJK Unified Ideographs Extension B', 'lcp' => '2A6D6'],
    ['fcp' => '2A700', 'id' => 'CJK Unified Ideographs Extension C', 'lcp' => '2B734'],
    ['fcp' => '2B740', 'id' => 'CJK Unified Ideographs Extension D', 'lcp' => '2B81D'],
    ['fcp' => '2B820', 'id' => 'CJK Unified Ideographs Extension E', 'lcp' => '2CEA1'],
    ['fcp' => '2CEB0', 'id' => 'CJK Unified Ideographs Extension F', 'lcp' => '2EBE0'],
    ['fcp' => '2F800', 'id' => 'CJK Compatibility Ideographs Supplement', 'lcp' => '2FA1F'],
    ['fcp' => '2FF80', 'id' => 'Unassigned', 'lcp' => '2FFFF'],
    ['fcp' => '3FF80', 'id' => 'Unassigned', 'lcp' => '3FFFF'],
    ['fcp' => '4FF80', 'id' => 'Unassigned', 'lcp' => '4FFFF'],
    ['fcp' => '5FF80', 'id' => 'Unassigned', 'lcp' => '5FFFF'],
    ['fcp' => '6FF80', 'id' => 'Unassigned', 'lcp' => '6FFFF'],
    ['fcp' => '7FF80', 'id' => 'Unassigned', 'lcp' => '7FFFF'],
    ['fcp' => '8FF80', 'id' => 'Unassigned', 'lcp' => '8FFFF'],
    ['fcp' => '9FF80', 'id' => 'Unassigned', 'lcp' => '9FFFF'],
    ['fcp' => 'AFF80', 'id' => 'Unassigned', 'lcp' => 'AFFFF'],
    ['fcp' => 'BFF80', 'id' => 'Unassigned', 'lcp' => 'BFFFF'],
    ['fcp' => 'CFF80', 'id' => 'Unassigned', 'lcp' => 'CFFFF'],
    ['fcp' => 'DFF80', 'id' => 'Unassigned', 'lcp' => 'DFFFF'],
    ['fcp' => 'E0000', 'id' => 'Tags', 'lcp' => 'E007F'],
    ['fcp' => 'E0100', 'id' => 'Variation Selectors Supplement', 'lcp' => 'E01EF'],
    ['fcp' => 'EFF80', 'id' => 'Unassigned', 'lcp' => 'EFFFF'],
    ['fcp' => 'FFF80', 'id' => 'Supplementary Private Use Area-A', 'lcp' => 'FFFFF'],
    ['fcp' => '10FF80', 'id' => 'Supplementary Private Use Area-B', 'lcp' => '10FFFF'],
];


// We define an array of disabled characters sets
// Key:   name of a characters set
// Value: reason of deactivation
$tableDeactivation = [
    'Samaritan' => 'Not working with Windows default font stack (21/05/2017)',
    'Mandaic' => 'Not working with Windows default font stack (21/05/2017)',
    'Syriac Supplement' => 'Unicode 10',
    'Arabic Extended-A' => 'Not working with Windows default font stack (21/05/2017)',
    'Myanmar' => 'Not working with Windows default font stack (21/05/2017)',
    'Tagalog' => 'Not working with Windows default font stack (21/05/2017)',
    'Hanunoo' => 'Not working with Windows default font stack (21/05/2017)',
    'Buhid' => 'Not working with Windows default font stack (21/05/2017)',
    'Tagbanwa' => 'Not working with Windows default font stack (21/05/2017)',
    'Unified Canadian Aboriginal Syllabics Extended' => 'Not working with Windows default font stack (21/05/2017)',
    'Buginese' => 'Not working with Windows default font stack (21/05/2017)',
    'Tai Tham' => 'Not working with Windows default font stack (21/05/2017)',
    'Combining Diacritical Marks Extended' => 'Unicode 8',
    'Balinese' => 'Not working with Windows default font stack (21/05/2017)',
    'Sundanese' => 'Not working with Windows default font stack (21/05/2017)',
    'Batak' => 'Not working with Windows default font stack (21/05/2017)',
    'Lepcha' => 'Not working with Windows default font stack (21/05/2017)',
    'Ol Chiki' => 'Not working with Windows default font stack (21/05/2017)',
    'Cyrillic Extended-C' => 'Unicode 9',
    'Georgian Extended' => 'Unicode 11',
    'Sundanese Supplement' => 'Not working with Windows default font stack (21/05/2017)',
    'Vedic Extensions' => 'Not working with Windows default font stack (21/05/2017)',
    'Coptic' => 'Not working with Windows default font stack (21/05/2017)',
    'Georgian Supplement' => 'Not working with Windows default font stack (21/05/2017)',
    'Cyrillic Extended-A' => 'Not working with Windows default font stack (21/05/2017)',
    'Lisu' => 'Not working with Windows default font stack (21/05/2017)',
    'Bamum' => 'Not working with Windows default font stack (21/05/2017)',
    'Syloti Nagri' => 'Not working with Windows default font stack (21/05/2017)',
    'Common Indic Number Forms' => 'Not working with Windows default font stack (21/05/2017)',
    'Saurashtra' => 'Not working with Windows default font stack (21/05/2017)',
    'Devanagari Extended' => 'Not working with Windows default font stack (21/05/2017)',
    'Kayah Li' => 'Not working with Windows default font stack (21/05/2017)',
    'Rejang' => 'Not working with Windows default font stack (21/05/2017)',
    'Hangul Jamo Extended-A' => 'Not working with Windows default font stack (21/05/2017)',
    'Javanese' => 'Not working with Windows default font stack (21/05/2017)',
    'Myanmar Extended-B' => 'Unicode 7',
    'Cham' => 'Not working with Windows default font stack (21/05/2017)',
    'Myanmar Extended-A' => 'Not working with Windows default font stack (21/05/2017)',
    'Tai Viet' => 'Not working with Windows default font stack (21/05/2017)',
    'Meetei Mayek Extensions' => 'Not working with Windows default font stack (21/05/2017)',
    'Ethiopic Extended-A' => 'Not working with Windows default font stack (21/05/2017)',
    'Latin Extended-E' => 'Unicode 7',
    'Cherokee Supplement' => 'Unicode 8',
    'Meetei Mayek' => 'Not working with Windows default font stack (21/05/2017)',
    'Hangul Jamo Extended-B' => 'Not working with Windows default font stack (21/05/2017)',
    'High Surrogates' => '',
    'High Private Use Surrogates' => '',
    'Low Surrogates' => '',
    'Private Use Area' => '',
    'Variation Selectors' => '',
    'Specials' => '',
    'Linear B Syllabary' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Linear B Ideograms' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Aegean Numbers' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Ancient Greek Numbers' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Ancient Symbols' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Phaistos Disc' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Lycian' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Carian' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Coptic Epact Numbers' => 'Unicode 7 / Unsupported by MySql',
    'Old Italic' => 'Unsupported by MySql',
    'Gothic' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Old Permic' => 'Unicode 7 / Unsupported by MySql',
    'Ugaritic' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Old Persian' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Deseret' => 'Unsupported by MySql',
    'Shavian' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Osmanya' => 'Unsupported by MySql',
    'Osage' => 'Unicode 9 / Unsupported by MySql',
    'Elbasan' => 'Unicode 7 / Unsupported by MySql',
    'Caucasian Albanian' => 'Unicode 7 / Unsupported by MySql',
    'Linear A' => 'Unicode 7 / Unsupported by MySql',
    'Cypriot Syllabary' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Imperial Aramaic' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Palmyrene' => 'Unicode 7 / Unsupported by MySql',
    'Nabataean' => 'Unicode 7 / Unsupported by MySql',
    'Hatran' => 'Unicode 8 / Unsupported by MySql',
    'Phoenician' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Lydian' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Meroitic Hieroglyphs' => 'Unsupported by MySql',
    'Meroitic Cursive' => 'Unsupported by MySql',
    'Kharoshthi' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Old South Arabian' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Old North Arabian' => 'Unicode 7 / Unsupported by MySql',
    'Manichaean' => 'Unicode 7 / Unsupported by MySql',
    'Avestan' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Inscriptional Parthian' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Inscriptional Pahlavi' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Psalter Pahlavi' => 'Unicode 7 / Unsupported by MySql',
    'Old Turkic' => 'Unsupported by MySql25/nov./2018 16:04 / Unsupported by MySql',
    'Old Hungarian' => 'Unicode 8 / Unsupported by MySql',
    'Hanifi Rohingya' => 'Unicode 11 / Unsupported by MySql',
    'Rumi Numeral Symbols' => 'Unicode 11 / Unsupported by MySql',
    'Old Sogdian' => 'Unicode 11 / Unsupported by MySql',
    'Sogdian' => 'Unicode 11 / Unsupported by MySql',
    'Brahmi' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Kaithi' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Sora Sompeng' => 'Unsupported by MySql',
    'Chakma' => 'Unsupported by MySql',
    'Mahajani' => 'Unicode 7 / Unsupported by MySql',
    'Sharada' => 'Unsupported by MySql',
    'Sinhala Archaic Numbers' => 'Unicode 7 / Unsupported by MySql',
    'Khojki' => 'Unicode 7 / Unsupported by MySql',
    'Multani' => 'Unicode 8 / Unsupported by MySql',
    'Khudawadi' => 'Unicode 7 / Unsupported by MySql',
    'Grantha' => 'Unicode 7 / Unsupported by MySql',
    'Newa' => 'Unicode 9 / Unsupported by MySql',
    'Tirhuta' => 'Unicode 7 / Unsupported by MySql',
    'Siddham' => 'Unicode 7 / Unsupported by MySql',
    'Modi' => 'Unicode 7 / Unsupported by MySql',
    'Mongolian Supplement' => 'Unicode 9 / Unsupported by MySql',
    'Takri' => 'Unsupported by MySql',
    'Ahom' => 'Unicode 8 / Unsupported by MySql',
    'Dogra' => 'Unicode 11 / Unsupported by MySql',
    'Warang Citi' => 'Unicode 7 / Unsupported by MySql',
    'Zanabazar Square' => 'Unicode 10 / Unsupported by MySql',
    'Soyombo' => 'Unicode 10 / Unsupported by MySql',
    'Pau Cin Hau' => 'Unicode 7 / Unsupported by MySql',
    'Bhaiksuki' => 'Unicode 9 / Unsupported by MySql',
    'Marchen' => 'Unicode 9 / Unsupported by MySql',
    'Masaram Gondi' => 'Unicode 10 / Unsupported by MySql',
    'Gunjala Gondi' => 'Unicode 11 / Unsupported by MySql',
    'Makasar' => 'Unicode 11 / Unsupported by MySql',
    'Cuneiform' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Cuneiform Numbers and Punctuation' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Early Dynastic Cuneiform' => 'Unicode 8 / Unsupported by MySql',
    'Egyptian Hieroglyphs' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Anatolian Hieroglyphs' => 'Unicode 8 / Unsupported by MySql',
    'Bamum Supplement' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Mro' => 'Unicode 7 / Unsupported by MySql',
    'Bassa Vah' => 'Unicode 7 / Unsupported by MySql',
    'Pahawh Hmong' => 'Unicode 7 / Unsupported by MySql',
    'Medefaidrin' => 'Unicode 11 / Unsupported by MySql',
    'Miao' => 'Unicode 6 / Unsupported by MySql',
    'Ideographic Symbols and Punctuation' => 'Unicode 9 & 10 / Unsupported by MySql',
    'Tangut' => 'Unicode 9 & 10 / Unsupported by MySql',
    'Tangut Components' => 'Unicode 9 / Unsupported by MySql',
    'Kana Supplement' => 'Unsupported by MySql',
    'Kana Extended-A' => 'Unicode 10 / Unsupported by MySql',
    'Nushu' => 'Unicode 10 / Unsupported by MySql',
    'Duployan' => 'Unicode 7 / Unsupported by MySql',
    'Shorthand Format Controls' => 'Unicode 7 / Unsupported by MySql',
    'Byzantine Musical Symbols' => 'Unsupported by MySql',
    'Musical Symbols' => 'Unsupported by MySql',
    'Ancient Greek Musical Notation' => 'Unsupported by MySql',
    'Mayan Numerals' => 'Unsupported by MySql',
    'Tai Xuan Jing Symbols' => 'Unsupported by MySql',
    'Counting Rod Numerals' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Mathematical Alphanumeric Symbols' => 'Unsupported by MySql',
    'Sutton SignWriting' => 'Unicode 8 / Unsupported by MySql',
    'Glagolitic Supplement' => 'Unicode 9 / Unsupported by MySql',
    'Mende Kikakui' => 'Unicode 7 / Unsupported by MySql',
    'Adlam' => 'Unicode 9 / Unsupported by MySql',
    'Indic Siyaq Numbers' => 'Unsupported by MySql',
    'Arabic Mathematical Alphabetic Symbols' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Mahjong Tiles' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Domino Tiles' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Playing Cards' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Enclosed Alphanumeric Supplement' => 'Unsupported by MySql',
    'Enclosed Ideographic Supplement' => 'Unsupported by MySql',
    'Miscellaneous Symbols and Pictographs' => 'Unsupported by MySql',
    'Emoticons' => 'Unsupported by MySql',
    'Ornamental Dingbats' => 'Unicode 7 / Unsupported by MySql',
    'Transport and Map Symbols' => 'Unsupported by MySql',
    'Alchemical Symbols' => 'Not working with Windows default font stack (21/05/2017) / Unsupported by MySql',
    'Geometric Shapes Extended' => 'Unicode 7 / Unsupported by MySql',
    'Supplemental Arrows-C' => 'Unicode 7 / Unsupported by MySql',
    'Supplemental Symbols and Pictographs' => 'Unicode 8 / Unsupported by MySql',
    'Chess Symbols' => 'Unsupported by MySql',
    'CJK Unified Ideographs Extension B' => 'Unsupported by MySql',
    'CJK Unified Ideographs Extension C' => 'Unsupported by MySql',
    'CJK Unified Ideographs Extension D' => 'Unsupported by MySql',
    'CJK Unified Ideographs Extension E' => 'Unicode 8 / Unsupported by MySql',
    'CJK Unified Ideographs Extension F' => 'Unicode 10 / Unsupported by MySql',
    'CJK Compatibility Ideographs Supplement' => 'Unicode 10 / Unsupported by MySql',
    'Tags' => 'Unsupported by MySql',
    'Unassigned' => 'Not useful / Unsupported by MySql',
    'Variation Selectors Supplement' => 'Unsupported by MySql',
    'Supplementary Private Use Area-A' => 'Not useful in this context / Unsupported by MySql',
    'Supplementary Private Use Area-B' => 'Not useful in this context / Unsupported by MySql',
];

// Remove disabled characters sets
foreach ($tableDeactivation as $did => $reason)
{
    // The array is traversed in reverse order to not cause
    // a crash when deleting entries during the course
    for ($kc = count($tableChars) - 1; $kc >= 0; $kc--)
    {
        if ($tableChars[$kc]['id'] == $did)
        {
            array_splice($tableChars, $kc, 1);
        }
    }
}


// We define an array of diacritics characters sets
// Key: name of a characters set
$tableDiacritics = [
    'Combining Diacritical Marks',
];

// Add boolean "diac" field to diacritics characters sets
// It help to display them in a specific way
for ($k = 0; $k < count($tableChars); $k++)
{
    foreach ($tableDiacritics as $did)
    {
        $tableChars[$k]['diac'] = ($tableChars[$k]['id'] == $did);
    }
}


// Avoid control characters because this could do weird things
// by rewriting left and right bound of the basic latin table
// Controls characters only are at position 0000 to 001F and 007F
for ($k = 0; $k < count($tableChars); $k++)
{
    if ($tableChars[$k]['id'] == 'C0 Controls and Basic Latin')
    {
        $tableChars[$k]['fcp'] = '0020';
        $tableChars[$k]['lcp'] = '007E';
    }
}
