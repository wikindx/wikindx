/**
 * charmap.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

tinyMCEPopup.requireLangPack();

var charmap = [
	['&nbsp;',    ' ',  true, 'no-break space'],
	['&amp;',     '&',   true, 'ampersand'],
	['&quot;',    '"',   true, 'quotation mark'],
// finance
	['&cent;',    '¢',  true, 'cent sign'],
	['&euro;',    '€', true, 'euro sign'],
	['&pound;',   '£',  true, 'pound sign'],
	['&yen;',     '¥',  true, 'yen sign'],
// signs
	['&copy;',    '©',  true, 'copyright sign'],
	['&reg;',     '®',  true, 'registered sign'],
	['&trade;',   '™', true, 'trade mark sign'],
	['&permil;',  '‰', true, 'per mille sign'],
	['&micro;',   'µ',  true, 'micro sign'],
	['&middot;',  '·',  true, 'middle dot'],
	['&bull;',    '•', true, 'bullet'],
	['&hellip;',  '…', true, 'three dot leader'],
	['&prime;',   '′', true, 'minutes / feet'],
	['&Prime;',   '″', true, 'seconds / inches'],
	['&sect;',    '§',  true, 'section sign'],
	['&para;',    '¶',  true, 'paragraph sign'],
	['&szlig;',   'ß',  true, 'sharp s / ess-zed'],
// quotations
	['&lsaquo;',  '‹', true, 'single left-pointing angle quotation mark'],
	['&rsaquo;',  '›', true, 'single right-pointing angle quotation mark'],
	['&laquo;',   '«',  true, 'left pointing guillemet'],
	['&raquo;',   '»',  true, 'right pointing guillemet'],
	['&lsquo;',   '‘', true, 'left single quotation mark'],
	['&rsquo;',   '’', true, 'right single quotation mark'],
	['&ldquo;',   '“', true, 'left double quotation mark'],
	['&rdquo;',   '”', true, 'right double quotation mark'],
	['&sbquo;',   '‚', true, 'single low-9 quotation mark'],
	['&bdquo;',   '„', true, 'double low-9 quotation mark'],
	['&lt;',      '<',   true, 'less-than sign'],
	['&gt;',      '>',   true, 'greater-than sign'],
	['&le;',      '≤', true, 'less-than or equal to'],
	['&ge;',      '≥', true, 'greater-than or equal to'],
	['&ndash;',   '–', true, 'en dash'],
	['&mdash;',   '—', true, 'em dash'],
	['&macr;',    '¯',  true, 'macron'],
	['&oline;',   '‾', true, 'overline'],
	['&curren;',  '¤',  true, 'currency sign'],
	['&brvbar;',  '¦',  true, 'broken bar'],
	['&uml;',     '¨',  true, 'diaeresis'],
	['&iexcl;',   '¡',  true, 'inverted exclamation mark'],
	['&iquest;',  '¿',  true, 'turned question mark'],
	['&circ;',    'ˆ',  true, 'circumflex accent'],
	['&tilde;',   '˜',  true, 'small tilde'],
	['&deg;',     '°',  true, 'degree sign'],
	['&minus;',   '−', true, 'minus sign'],
	['&plusmn;',  '±',  true, 'plus-minus sign'],
	['&divide;',  '÷',  true, 'division sign'],
	['&frasl;',   '⁄', true, 'fraction slash'],
	['&times;',   '×',  true, 'multiplication sign'],
	['&sup1;',    '¹',  true, 'superscript one'],
	['&sup2;',    '²',  true, 'superscript two'],
	['&sup3;',    '³',  true, 'superscript three'],
	['&frac14;',  '¼',  true, 'fraction one quarter'],
	['&frac12;',  '½',  true, 'fraction one half'],
	['&frac34;',  '¾',  true, 'fraction three quarters'],
// math / logical
	['&fnof;',    'ƒ',  true, 'function / florin'],
	['&int;',     '∫', true, 'integral'],
	['&sum;',     '∑', true, 'n-ary sumation'],
	['&infin;',   '∞', true, 'infinity'],
	['&radic;',   '√', true, 'square root'],
	['&sim;',     '∼', false,'similar to'],
	['&cong;',    '≅', false,'approximately equal to'],
	['&asymp;',   '≈', true, 'almost equal to'],
	['&ne;',      '≠', true, 'not equal to'],
	['&equiv;',   '≡', true, 'identical to'],
	['&isin;',    '∈', false,'element of'],
	['&notin;',   '∉', false,'not an element of'],
	['&ni;',      '∋', false,'contains as member'],
	['&prod;',    '∏', true, 'n-ary product'],
	['&and;',     '∧', false,'logical and'],
	['&or;',      '∨', false,'logical or'],
	['&not;',     '¬',  true, 'not sign'],
	['&cap;',     '∩', true, 'intersection'],
	['&cup;',     '∪', false,'union'],
	['&part;',    '∂', true, 'partial differential'],
	['&forall;',  '∀', false,'for all'],
	['&exist;',   '∃', false,'there exists'],
	['&empty;',   '∅', false,'diameter'],
	['&nabla;',   '∇', false,'backward difference'],
	['&lowast;',  '∗', false,'asterisk operator'],
	['&prop;',    '∝', false,'proportional to'],
	['&ang;',     '∠', false,'angle'],
// undefined
	['&acute;',   '´',  true, 'acute accent'],
	['&cedil;',   '¸',  true, 'cedilla'],
	['&ordf;',    'ª',  true, 'feminine ordinal indicator'],
	['&ordm;',    'º',  true, 'masculine ordinal indicator'],
	['&dagger;',  '†', true, 'dagger'],
	['&Dagger;',  '‡', true, 'double dagger'],
// alphabetical special chars
	['&Agrave;',  'À',  true, 'A - grave'],
	['&Aacute;',  'Á',  true, 'A - acute'],
	['&Acirc;',   'Â',  true, 'A - circumflex'],
	['&Atilde;',  'Ã',  true, 'A - tilde'],
	['&Auml;',    'Ä',  true, 'A - diaeresis'],
	['&Aring;',   'Å',  true, 'A - ring above'],
	['&AElig;',   'Æ',  true, 'ligature AE'],
	['&Ccedil;',  'Ç',  true, 'C - cedilla'],
	['&Egrave;',  'È',  true, 'E - grave'],
	['&Eacute;',  'É',  true, 'E - acute'],
	['&Ecirc;',   'Ê',  true, 'E - circumflex'],
	['&Euml;',    'Ë',  true, 'E - diaeresis'],
	['&Igrave;',  'Ì',  true, 'I - grave'],
	['&Iacute;',  'Í',  true, 'I - acute'],
	['&Icirc;',   'Î',  true, 'I - circumflex'],
	['&Iuml;',    'Ï',  true, 'I - diaeresis'],
	['&ETH;',     'Ð',  true, 'ETH'],
	['&Ntilde;',  'Ñ',  true, 'N - tilde'],
	['&Ograve;',  'Ò',  true, 'O - grave'],
	['&Oacute;',  'Ó',  true, 'O - acute'],
	['&Ocirc;',   'Ô',  true, 'O - circumflex'],
	['&Otilde;',  'Õ',  true, 'O - tilde'],
	['&Ouml;',    'Ö',  true, 'O - diaeresis'],
	['&Oslash;',  'Ø',  true, 'O - slash'],
	['&OElig;',   'Œ',  true, 'ligature OE'],
	['&Scaron;',  'Š',  true, 'S - caron'],
	['&Ugrave;',  'Ù',  true, 'U - grave'],
	['&Uacute;',  'Ú',  true, 'U - acute'],
	['&Ucirc;',   'Û',  true, 'U - circumflex'],
	['&Uuml;',    'Ü',  true, 'U - diaeresis'],
	['&Yacute;',  'Ý',  true, 'Y - acute'],
	['&Yuml;',    'Ÿ',  true, 'Y - diaeresis'],
	['&THORN;',   'Þ',  true, 'THORN'],
	['&agrave;',  'à',  true, 'a - grave'],
	['&aacute;',  'á',  true, 'a - acute'],
	['&acirc;',   'â',  true, 'a - circumflex'],
	['&atilde;',  'ã',  true, 'a - tilde'],
	['&auml;',    'ä',  true, 'a - diaeresis'],
	['&aring;',   'å',  true, 'a - ring above'],
	['&aelig;',   'æ',  true, 'ligature ae'],
	['&ccedil;',  'ç',  true, 'c - cedilla'],
	['&egrave;',  'è',  true, 'e - grave'],
	['&eacute;',  'é',  true, 'e - acute'],
	['&ecirc;',   'ê',  true, 'e - circumflex'],
	['&euml;',    'ë',  true, 'e - diaeresis'],
	['&igrave;',  'ì',  true, 'i - grave'],
	['&iacute;',  'í',  true, 'i - acute'],
	['&icirc;',   'î',  true, 'i - circumflex'],
	['&iuml;',    'ï',  true, 'i - diaeresis'],
	['&eth;',     'ð',  true, 'eth'],
	['&ntilde;',  'ñ',  true, 'n - tilde'],
	['&ograve;',  'ò',  true, 'o - grave'],
	['&oacute;',  'ó',  true, 'o - acute'],
	['&ocirc;',   'ô',  true, 'o - circumflex'],
	['&otilde;',  'õ',  true, 'o - tilde'],
	['&ouml;',    'ö',  true, 'o - diaeresis'],
	['&oslash;',  'ø',  true, 'o slash'],
	['&oelig;',   'œ',  true, 'ligature oe'],
	['&scaron;',  'š',  true, 's - caron'],
	['&ugrave;',  'ù',  true, 'u - grave'],
	['&uacute;',  'ú',  true, 'u - acute'],
	['&ucirc;',   'û',  true, 'u - circumflex'],
	['&uuml;',    'ü',  true, 'u - diaeresis'],
	['&yacute;',  'ý',  true, 'y - acute'],
	['&thorn;',   'þ',  true, 'thorn'],
	['&yuml;',    'ÿ',  true, 'y - diaeresis'],
	['&Alpha;',   'Α',  true, 'Alpha'],
	['&Beta;',    'Β',  true, 'Beta'],
	['&Gamma;',   'Γ',  true, 'Gamma'],
	['&Delta;',   'Δ',  true, 'Delta'],
	['&Epsilon;', 'Ε',  true, 'Epsilon'],
	['&Zeta;',    'Ζ',  true, 'Zeta'],
	['&Eta;',     'Η',  true, 'Eta'],
	['&Theta;',   'Θ',  true, 'Theta'],
	['&Iota;',    'Ι',  true, 'Iota'],
	['&Kappa;',   'Κ',  true, 'Kappa'],
	['&Lambda;',  'Λ',  true, 'Lambda'],
	['&Mu;',      'Μ',  true, 'Mu'],
	['&Nu;',      'Ν',  true, 'Nu'],
	['&Xi;',      'Ξ',  true, 'Xi'],
	['&Omicron;', 'Ο',  true, 'Omicron'],
	['&Pi;',      'Π',  true, 'Pi'],
	['&Rho;',     'Ρ',  true, 'Rho'],
	['&Sigma;',   'Σ',  true, 'Sigma'],
	['&Tau;',     'Τ',  true, 'Tau'],
	['&Upsilon;', 'Υ',  true, 'Upsilon'],
	['&Phi;',     'Φ',  true, 'Phi'],
	['&Chi;',     'Χ',  true, 'Chi'],
	['&Psi;',     'Ψ',  true, 'Psi'],
	['&Omega;',   'Ω',  true, 'Omega'],
	['&alpha;',   'α',  true, 'alpha'],
	['&beta;',    'β',  true, 'beta'],
	['&gamma;',   'γ',  true, 'gamma'],
	['&delta;',   'δ',  true, 'delta'],
	['&epsilon;', 'ε',  true, 'epsilon'],
	['&zeta;',    'ζ',  true, 'zeta'],
	['&eta;',     'η',  true, 'eta'],
	['&theta;',   'θ',  true, 'theta'],
	['&iota;',    'ι',  true, 'iota'],
	['&kappa;',   'κ',  true, 'kappa'],
	['&lambda;',  'λ',  true, 'lambda'],
	['&mu;',      'μ',  true, 'mu'],
	['&nu;',      'ν',  true, 'nu'],
	['&xi;',      'ξ',  true, 'xi'],
	['&omicron;', 'ο',  true, 'omicron'],
	['&pi;',      'π',  true, 'pi'],
	['&rho;',     'ρ',  true, 'rho'],
	['&sigmaf;',  'ς',  true, 'final sigma'],
	['&sigma;',   'σ',  true, 'sigma'],
	['&tau;',     'τ',  true, 'tau'],
	['&upsilon;', 'υ',  true, 'upsilon'],
	['&phi;',     'φ',  true, 'phi'],
	['&chi;',     'χ',  true, 'chi'],
	['&psi;',     'ψ',  true, 'psi'],
	['&omega;',   'ω',  true, 'omega'],
// symbols
	['&alefsym;', 'ℵ', false,'alef symbol'],
	['&piv;',     'ϖ',  false,'pi symbol'],
	['&real;',    'ℜ', false,'real part symbol'],
	['&thetasym;','ϑ',  false,'theta symbol'],
	['&upsih;',   'ϒ',  false,'upsilon - hook symbol'],
	['&weierp;',  '℘', false,'Weierstrass p'],
	['&image;',   'ℑ', false,'imaginary part'],
// arrows
	['&larr;',    '←', true, 'leftwards arrow'],
	['&uarr;',    '↑', true, 'upwards arrow'],
	['&rarr;',    '→', true, 'rightwards arrow'],
	['&darr;',    '↓', true, 'downwards arrow'],
	['&harr;',    '↔', true, 'left right arrow'],
	['&crarr;',   '↵', false,'carriage return'],
	['&lArr;',    '⇐', false,'leftwards double arrow'],
	['&uArr;',    '⇑', false,'upwards double arrow'],
	['&rArr;',    '⇒', false,'rightwards double arrow'],
	['&dArr;',    '⇓', false,'downwards double arrow'],
	['&hArr;',    '⇔', false,'left right double arrow'],
	['&there4;',  '∴', false,'therefore'],
	['&sub;',     '⊂', false,'subset of'],
	['&sup;',     '⊃', false,'superset of'],
	['&nsub;',    '⊄', false,'not a subset of'],
	['&sube;',    '⊆', false,'subset of or equal to'],
	['&supe;',    '⊇', false,'superset of or equal to'],
	['&oplus;',   '⊕', false,'circled plus'],
	['&otimes;',  '⊗', false,'circled times'],
	['&perp;',    '⊥', false,'perpendicular'],
	['&sdot;',    '⋅', false,'dot operator'],
	['&lceil;',   '⌈', false,'left ceiling'],
	['&rceil;',   '⌉', false,'right ceiling'],
	['&lfloor;',  '⌊', false,'left floor'],
	['&rfloor;',  '⌋', false,'right floor'],
	['&lang;',    '〈', false,'left-pointing angle bracket'],
	['&rang;',    '〉', false,'right-pointing angle bracket'],
	['&loz;',     '◊', true, 'lozenge'],
	['&spades;',  '♠', true, 'black spade suit'],
	['&clubs;',   '♣', true, 'black club suit'],
	['&hearts;',  '♥', true, 'black heart suit'],
	['&diams;',   '♦', true, 'black diamond suit'],
	['&ensp;',    ' ', false,'en space'],
	['&emsp;',    ' ', false,'em space'],
	['&thinsp;',  ' ', false,'thin space'],
	['&zwnj;',    '‌', false,'zero width non-joiner'],
	['&zwj;',     '‍', false,'zero width joiner'],
	['&lrm;',     '‎', false,'left-to-right mark'],
	['&rlm;',     '‏', false,'right-to-left mark'],
	['&shy;',     '­',  false,'soft hyphen']
];

tinyMCEPopup.onInit.add(function() {
	tinyMCEPopup.dom.setHTML('charmapView', renderCharMapHTML());
	addKeyboardNavigation();
});

function addKeyboardNavigation(){
	var tableElm, cells, settings;

	cells = tinyMCEPopup.dom.select("a.charmaplink", "charmapgroup");

	settings ={
		root: "charmapgroup",
		items: cells
	};
	cells[0].tabindex=0;
	tinyMCEPopup.dom.addClass(cells[0], "mceFocus");
	if (tinymce.isGecko) {
		cells[0].focus();		
	} else {
		setTimeout(function(){
			cells[0].focus();
		}, 100);
	}
	tinyMCEPopup.editor.windowManager.createInstance('tinymce.ui.KeyboardNavigation', settings, tinyMCEPopup.dom);
}

function renderCharMapHTML() {
	var charsPerRow = 20, tdWidth=20, tdHeight=20, i;
	var html = '<div id="charmapgroup" aria-labelledby="charmap_label" tabindex="0" role="listbox">'+
	'<table role="presentation" border="0" cellspacing="1" cellpadding="0" width="' + (tdWidth*charsPerRow) + 
	'"><tr height="' + tdHeight + '">';
	var cols=-1;

	for (i=0; i<charmap.length; i++) {
		var previewCharFn;

		if (charmap[i][2]==true) {
			cols++;
			previewCharFn = 'previewChar(\'' + charmap[i][1].substring(1,charmap[i][1].length) + '\',\'' + charmap[i][0].substring(1,charmap[i][0].length) + '\',\'' + charmap[i][3] + '\');';
			html += ''
				+ '<td class="charmap">'
				+ '<a class="charmaplink" role="button" onmouseover="'+previewCharFn+'" onfocus="'+previewCharFn+'" href="javascript:void(0)" onclick="insertChar(\'' + charmap[i][1].substring(2,charmap[i][1].length-1) + '\');" onclick="return false;" onmousedown="return false;" title="' + charmap[i][3] + ' '+ tinyMCEPopup.editor.translate("advanced_dlg.charmap_usage")+'">'
				+ charmap[i][1]
				+ '</a></td>';
			if ((cols+1) % charsPerRow == 0)
				html += '</tr><tr height="' + tdHeight + '">';
		}
	 }

	if (cols % charsPerRow > 0) {
		var padd = charsPerRow - (cols % charsPerRow);
		for (var i=0; i<padd-1; i++)
			html += '<td width="' + tdWidth + '" height="' + tdHeight + '" class="charmap">&nbsp;</td>';
	}

	html += '</tr></table></div>';
	html = html.replace(/<tr height="20"><\/tr>/g, '');

	return html;
}

function insertChar(chr) {
	tinyMCEPopup.execCommand('mceInsertContent', false, '&#' + chr + ';');

	// Refocus in window
	if (tinyMCEPopup.isWindow)
		window.focus();

	tinyMCEPopup.editor.focus();
	tinyMCEPopup.close();
}

function previewChar(codeA, codeB, codeN) {
	var elmA = document.getElementById('codeA');
	var elmB = document.getElementById('codeB');
	var elmV = document.getElementById('codeV');
	var elmN = document.getElementById('codeN');

	if (codeA=='#160;') {
		elmV.innerHTML = '__';
	} else {
		elmV.innerHTML = '&' + codeA;
	}

	elmB.innerHTML = '&amp;' + codeA;
	elmA.innerHTML = '&amp;' + codeB;
	elmN.innerHTML = codeN;
}
