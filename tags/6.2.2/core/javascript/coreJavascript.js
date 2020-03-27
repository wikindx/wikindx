/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
**********************************************************************************/

/**
* Core javascript functions.  Loaded in index.php
*/


/**
* Transfer elements from single source select box to target textbox.
* The index/ID of the transferred option is stored in the object 
* referenced by hiddenId
*
* @param string Target object
* @param string Source object
* @param string Hidden ID object
*/
function coreSelectToTextbox(target, source, hiddenId)
{
	var targetObj = coreGetElementById(target);
	var sourceObj = coreGetElementById(source);
	var hiddenIdObj = coreGetElementById(hiddenId);
	len = sourceObj.options.length;
	for(i = 0; i < len; i++)
	{
		if(sourceObj.options[i].selected)
		{
			split = sourceObj.options[i].value.split('_');
			targetObj.value = coreEntityDecode(coreUtf8.decode(coreDecode_base64(split[1])));
			hiddenIdObj.value = split[0];
			break;
		}
	}
}

/**
* Transfer elements from source select box to target textarea if selected element's text does not exist in target area
*
* @param string Target object
* @param string Source object
*/
function coreSelectToTextarea(target, source)
{
	var targetObj = coreGetElementById(target);
	var tempText = targetObj.value.split(',');
	var len = tempText.length;
	var targetText = new Array;
	var text;
	var selection;
	var split;
	for(var i = 0; i < len; i ++)
	{
		text = coreTrim(tempText[i]);
		if(text)
			targetText.push(text);
	}
	var sourceObj = coreGetElementById(source);
	len = sourceObj.options.length;
	for(i = 0; i < len; i++)
	{
		split = sourceObj.options[i].value.split('_');
		if(split.length == 2)
			selection = coreEntityDecode(coreUtf8.decode(coreDecode_base64(split[1])));
		else
			selection = coreEntityDecode(coreUtf8.decode(coreDecode_base64(sourceObj.options[i].value)));
// When searching for existing word, we ignore case as different words may be indicated by different use of case on the same letters.
// i.e. we trust the user to get it right.
		if(sourceObj.options[i].selected && (coreSearchArray(targetText, selection) == -1))
			targetText.push(selection);
	}
//	var test = targetText;
//	console.log(test.join(','));
	targetObj.value = targetText.join(',');
}

/**
* Transfer elements from source select box to target select box
*/
function coreSelectToSelect(target, source)
{
	var targetObj = coreGetElementById(target);
	var sourceObj = coreGetElementById(source);
	for(i = sourceObj.options.length - 1; i >= 0; i--)
	{
		if(sourceObj.options[i].selected)
		{
			targetObj.options[targetObj.options.length] = new Option(sourceObj.options[i].text, sourceObj.options[i].value);
			sourceObj.remove(i);
		}
	}
	coreSortSelectBox(targetObj);
}
/*
* sort function - ascending (case-insensitive)
*
* @param string record1
* @param string record2
* @return int -1, 0, or 1
*/
function coreSortFuncAsc(record1, record2)
{
	var value1 = record1.optText.toLowerCase();
	var value2 = record2.optText.toLowerCase();
	if (value1 > value2) return(1);
	if (value1 < value2) return(-1);
	return(0);
}

/*
* Sort options in a select box
*
* From: http://www.tek-tips.com/faqs.cfm?fid=5347
*
* @param object select box object
*/
function coreSortSelectBox(selectToSort)
{
	// copy options into an array
	var myOptions = [];
	for (var loop = 0; loop < selectToSort.options.length; loop++) {
		myOptions[loop] = { optText:selectToSort.options[loop].text, optValue:selectToSort.options[loop].value };
	}

	// sort array
	myOptions.sort(coreSortFuncAsc);

	// copy sorted options from array back to select box
	selectToSort.options.length = 0;
	for (var loop = 0; loop < myOptions.length; loop++) {
		var optObj = document.createElement('option');
		optObj.text = myOptions[loop].optText;
		optObj.value = myOptions[loop].optValue;
		selectToSort.options.add(optObj);
	}
}

/**
* Decode HTML entities
*
* @param string Input text
* @return string
*/
function coreEntityDecode(text)
{
	text = text.replace(/&quot;/g, '"');
	text = text.replace(/&#0*38;/g, "'");
	text = text.replace(/&#0*39;/g, "'");
	text = text.replace(/&amp;/g, '&'); // this must be last
	return text;
}

/**
* Encode Base64
*/
var B64_END_OF_INPUT = -1;

var coreBase64Chars = new Array(
    'A','B','C','D','E','F','G','H',
    'I','J','K','L','M','N','O','P',
    'Q','R','S','T','U','V','W','X',
    'Y','Z','a','b','c','d','e','f',
    'g','h','i','j','k','l','m','n',
    'o','p','q','r','s','t','u','v',
    'w','x','y','z','0','1','2','3',
    '4','5','6','7','8','9','+','/'
);
var coreBase64Str;
var coreBase64Count;
function coreSetBase64Str(str)
{
    coreBase64Str = str;
    coreBase64Count = 0;
}
function coreReadBase64()
{    
    if (!coreBase64Str) return B64_END_OF_INPUT;
    if (coreBase64Count >= coreBase64Str.length) return B64_END_OF_INPUT;
    var c = coreBase64Str.charCodeAt(coreBase64Count) & 0xff;
    coreBase64Count++;
    return c;
}
function coreEncode_base64(str)
{
    coreSetBase64Str(str);
    var result = '';
    var inBuffer = new Array(3);
    var lineCount = 0;
    var done = false;
    while (!done && (inBuffer[0] = coreReadBase64()) != B64_END_OF_INPUT)
    {
        inBuffer[1] = coreReadBase64();
        inBuffer[2] = coreReadBase64();
        result += (coreBase64Chars[ inBuffer[0] >> 2 ]);
        if (inBuffer[1] != B64_END_OF_INPUT)
        {
            result += (coreBase64Chars [(( inBuffer[0] << 4 ) & 0x30) | (inBuffer[1] >> 4) ]);
            if (inBuffer[2] != B64_END_OF_INPUT)
            {
                result += (coreBase64Chars [((inBuffer[1] << 2) & 0x3c) | (inBuffer[2] >> 6) ]);
                result += (coreBase64Chars [inBuffer[2] & 0x3F]);
            }
            else
            {
                result += (coreBase64Chars [((inBuffer[1] << 2) & 0x3c)]);
                result += ('=');
                done = true;
            }
        }
        else
        {
            result += (coreBase64Chars [(( inBuffer[0] << 4 ) & 0x30)]);
            result += ('=');
            result += ('=');
            done = true;
        }
        lineCount += 4;
        if (lineCount >= 76)
        {
            result += ('\n');
            lineCount = 0;
        }
    }
    return result;
}
/**
* Decode Base64
*
* @param string Input text
*/
function coreDecode_base64(s)
{
    var e={},i,k,v=[],r='',w=String.fromCharCode;
    var n=[[65,91],[97,123],[48,58],[43,44],[47,48]];

    for(z in n) {
        for(i=n[z][0];i<n[z][1];i++) {
            v.push(w(i));
        }
    }

    for(i=0;i<64;i++) {
        e[v[i]]=i;
    }

    for(i=0;i<s.length;i+=72) {
        var b=0,c,x,l=0,o=s.substring(i,i+72);
        for(x=0;x<o.length;x++) {
            c=e[o.charAt(x)];b=(b<<6)+c;l+=6;
            while(l>=8) {
            var charCode = (b>>>(l-=8))%256;

                if (charCode > 31) {
                    // only add printable chars
                    r+=w(charCode);
                }
            }
        }
    }
    return r;
}

/**
*
*  UTF-8 data encode / decode
*  http://www.webtoolkit.info/
*
* @return string
**/
var coreUtf8 = {
	// public method for url encoding
	encode : function (string) {
		var utftext = "";
		for (var n = 0; n < string.length; n++) 
		{
			var c = string.charCodeAt(n);
			if (c < 128) 
			{
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) 
			{
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else 
			{
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
		}
		return utftext;
	},
	// public method for url decoding
	decode : function (utftext)
	{
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
 
		while ( i < utftext.length )
		{
			c = utftext.charCodeAt(i);
			if (c < 128) 
			{
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) 
			{
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else 
			{
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
		}
		return string;
	}
}

/**
* Get an object for the requested HTML element ID
*
* @param string
* @return element object
*/
function coreGetElementById(id)
{
	return document.getElementById(id);
}
/**
* Get an object for the requested HTML element name
*
* @param string
* @return element object
*/
function coreGetElementByName(name)
{
	return document.getElementsByName(name);
}
/**
* Is input parameter an array? 
*
* @param multiple
* @return false|true
*/
function coreIsArray(input)
{
	return typeof(input) == 'object' && (input instanceof Array);
}
/**
* Is input an integer (positive or negative)?
*
* @param string
* @return boolean
*/
function coreIsInteger(s)
{
	return (s.toString().search(/^-?[0-9]+$/) == 0);
}
/**
* Remove non-printable characters from string
*
* @param string
* @return string
*/
function coreRemoveNonPrintable(str)
{
	var re = /[\0-\x1F\x7F-\x9F\xAD\u0378\u0379\u037F-\u0383\u038B\u038D\u03A2\u0528-\u0530\u0557\u0558\u0560\u0588\u058B-\u058E\u0590\u05C8-\u05CF\u05EB-\u05EF\u05F5-\u0605\u061C\u061D\u06DD\u070E\u070F\u074B\u074C\u07B2-\u07BF\u07FB-\u07FF\u082E\u082F\u083F\u085C\u085D\u085F-\u089F\u08A1\u08AD-\u08E3\u08FF\u0978\u0980\u0984\u098D\u098E\u0991\u0992\u09A9\u09B1\u09B3-\u09B5\u09BA\u09BB\u09C5\u09C6\u09C9\u09CA\u09CF-\u09D6\u09D8-\u09DB\u09DE\u09E4\u09E5\u09FC-\u0A00\u0A04\u0A0B-\u0A0E\u0A11\u0A12\u0A29\u0A31\u0A34\u0A37\u0A3A\u0A3B\u0A3D\u0A43-\u0A46\u0A49\u0A4A\u0A4E-\u0A50\u0A52-\u0A58\u0A5D\u0A5F-\u0A65\u0A76-\u0A80\u0A84\u0A8E\u0A92\u0AA9\u0AB1\u0AB4\u0ABA\u0ABB\u0AC6\u0ACA\u0ACE\u0ACF\u0AD1-\u0ADF\u0AE4\u0AE5\u0AF2-\u0B00\u0B04\u0B0D\u0B0E\u0B11\u0B12\u0B29\u0B31\u0B34\u0B3A\u0B3B\u0B45\u0B46\u0B49\u0B4A\u0B4E-\u0B55\u0B58-\u0B5B\u0B5E\u0B64\u0B65\u0B78-\u0B81\u0B84\u0B8B-\u0B8D\u0B91\u0B96-\u0B98\u0B9B\u0B9D\u0BA0-\u0BA2\u0BA5-\u0BA7\u0BAB-\u0BAD\u0BBA-\u0BBD\u0BC3-\u0BC5\u0BC9\u0BCE\u0BCF\u0BD1-\u0BD6\u0BD8-\u0BE5\u0BFB-\u0C00\u0C04\u0C0D\u0C11\u0C29\u0C34\u0C3A-\u0C3C\u0C45\u0C49\u0C4E-\u0C54\u0C57\u0C5A-\u0C5F\u0C64\u0C65\u0C70-\u0C77\u0C80\u0C81\u0C84\u0C8D\u0C91\u0CA9\u0CB4\u0CBA\u0CBB\u0CC5\u0CC9\u0CCE-\u0CD4\u0CD7-\u0CDD\u0CDF\u0CE4\u0CE5\u0CF0\u0CF3-\u0D01\u0D04\u0D0D\u0D11\u0D3B\u0D3C\u0D45\u0D49\u0D4F-\u0D56\u0D58-\u0D5F\u0D64\u0D65\u0D76-\u0D78\u0D80\u0D81\u0D84\u0D97-\u0D99\u0DB2\u0DBC\u0DBE\u0DBF\u0DC7-\u0DC9\u0DCB-\u0DCE\u0DD5\u0DD7\u0DE0-\u0DF1\u0DF5-\u0E00\u0E3B-\u0E3E\u0E5C-\u0E80\u0E83\u0E85\u0E86\u0E89\u0E8B\u0E8C\u0E8E-\u0E93\u0E98\u0EA0\u0EA4\u0EA6\u0EA8\u0EA9\u0EAC\u0EBA\u0EBE\u0EBF\u0EC5\u0EC7\u0ECE\u0ECF\u0EDA\u0EDB\u0EE0-\u0EFF\u0F48\u0F6D-\u0F70\u0F98\u0FBD\u0FCD\u0FDB-\u0FFF\u10C6\u10C8-\u10CC\u10CE\u10CF\u1249\u124E\u124F\u1257\u1259\u125E\u125F\u1289\u128E\u128F\u12B1\u12B6\u12B7\u12BF\u12C1\u12C6\u12C7\u12D7\u1311\u1316\u1317\u135B\u135C\u137D-\u137F\u139A-\u139F\u13F5-\u13FF\u169D-\u169F\u16F1-\u16FF\u170D\u1715-\u171F\u1737-\u173F\u1754-\u175F\u176D\u1771\u1774-\u177F\u17DE\u17DF\u17EA-\u17EF\u17FA-\u17FF\u180F\u181A-\u181F\u1878-\u187F\u18AB-\u18AF\u18F6-\u18FF\u191D-\u191F\u192C-\u192F\u193C-\u193F\u1941-\u1943\u196E\u196F\u1975-\u197F\u19AC-\u19AF\u19CA-\u19CF\u19DB-\u19DD\u1A1C\u1A1D\u1A5F\u1A7D\u1A7E\u1A8A-\u1A8F\u1A9A-\u1A9F\u1AAE-\u1AFF\u1B4C-\u1B4F\u1B7D-\u1B7F\u1BF4-\u1BFB\u1C38-\u1C3A\u1C4A-\u1C4C\u1C80-\u1CBF\u1CC8-\u1CCF\u1CF7-\u1CFF\u1DE7-\u1DFB\u1F16\u1F17\u1F1E\u1F1F\u1F46\u1F47\u1F4E\u1F4F\u1F58\u1F5A\u1F5C\u1F5E\u1F7E\u1F7F\u1FB5\u1FC5\u1FD4\u1FD5\u1FDC\u1FF0\u1FF1\u1FF5\u1FFF\u200B-\u200F\u202A-\u202E\u2060-\u206F\u2072\u2073\u208F\u209D-\u209F\u20BB-\u20CF\u20F1-\u20FF\u218A-\u218F\u23F4-\u23FF\u2427-\u243F\u244B-\u245F\u2700\u2B4D-\u2B4F\u2B5A-\u2BFF\u2C2F\u2C5F\u2CF4-\u2CF8\u2D26\u2D28-\u2D2C\u2D2E\u2D2F\u2D68-\u2D6E\u2D71-\u2D7E\u2D97-\u2D9F\u2DA7\u2DAF\u2DB7\u2DBF\u2DC7\u2DCF\u2DD7\u2DDF\u2E3C-\u2E7F\u2E9A\u2EF4-\u2EFF\u2FD6-\u2FEF\u2FFC-\u2FFF\u3040\u3097\u3098\u3100-\u3104\u312E-\u3130\u318F\u31BB-\u31BF\u31E4-\u31EF\u321F\u32FF\u4DB6-\u4DBF\u9FCD-\u9FFF\uA48D-\uA48F\uA4C7-\uA4CF\uA62C-\uA63F\uA698-\uA69E\uA6F8-\uA6FF\uA78F\uA794-\uA79F\uA7AB-\uA7F7\uA82C-\uA82F\uA83A-\uA83F\uA878-\uA87F\uA8C5-\uA8CD\uA8DA-\uA8DF\uA8FC-\uA8FF\uA954-\uA95E\uA97D-\uA97F\uA9CE\uA9DA-\uA9DD\uA9E0-\uA9FF\uAA37-\uAA3F\uAA4E\uAA4F\uAA5A\uAA5B\uAA7C-\uAA7F\uAAC3-\uAADA\uAAF7-\uAB00\uAB07\uAB08\uAB0F\uAB10\uAB17-\uAB1F\uAB27\uAB2F-\uABBF\uABEE\uABEF\uABFA-\uABFF\uD7A4-\uD7AF\uD7C7-\uD7CA\uD7FC-\uF8FF\uFA6E\uFA6F\uFADA-\uFAFF\uFB07-\uFB12\uFB18-\uFB1C\uFB37\uFB3D\uFB3F\uFB42\uFB45\uFBC2-\uFBD2\uFD40-\uFD4F\uFD90\uFD91\uFDC8-\uFDEF\uFDFE\uFDFF\uFE1A-\uFE1F\uFE27-\uFE2F\uFE53\uFE67\uFE6C-\uFE6F\uFE75\uFEFD-\uFF00\uFFBF-\uFFC1\uFFC8\uFFC9\uFFD0\uFFD1\uFFD8\uFFD9\uFFDD-\uFFDF\uFFE7\uFFEF-\uFFFB\uFFFE\uFFFF]/g;
	return str.replace(re, "");
}
/**
* IE doesn't like indexOf() so we need this
*
* @param haystack
* @param needle
* @return -1 if not found else the index of the array
*/
function coreSearchArray(haystack, needle)
{
	var len = haystack.length;
	for(var i = 0; i < len; i ++)
	{
		if(needle == haystack[i])
			return i; // found so return array element index
	}
	return -1; // not found
}
/**
* Trim and optional chars from a string
*
* @param string
* @param string option string of characters to trim
* @return string
*/
function coreTrim(str, chars)
{
	return coreLTrim(coreRTrim(str, chars), chars);
}
/**
* Left trim and optional chars from a string
*
* @param string
* @param string option string of characters to trim
* @return string
*/
function coreLTrim(str, chars)
{
	chars = chars || "\\s";
	return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
}
/**
* Right trim and optional chars from a string
*
* @param string
* @param string option string of characters to trim
* @return string
*/
function coreRTrim(str, chars)
{
	chars = chars || "\\s";
	return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
}
/**
* Get browser dimensions.
* Code adapted from http://www.howtocreate.co.uk/tutorials/index.php?tut=0&part=16
*
* @return object browserWidth and browserHeight
*/
function coreBrowserDimensions()
{
	if( typeof( window.innerWidth ) == 'number' )
	{
//Non-IE
		browserWidth = window.innerWidth;
		browserHeight  = window.innerHeight;
	}
	else if( document.documentElement &&
      ( document.documentElement.clientWidth || document.documentElement.clientHeight ) )
	{
//IE 6+ in 'standards compliant mode'
		browserWidth = document.documentElement.clientWidth;
		browserHeight  = document.documentElement.clientHeight;
	}
	else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) )
	{
//IE 4 compatible
		browserWidth = document.body.clientWidth;
		browserHeight  = document.body.clientHeight;
	}
	return {'browserWidth': browserWidth, 'browserHeight': browserHeight};
}
/**
* Open pop-up window
*
* @param string URL
* @param integer Optional Window size:  percentage of browser dimensions. If FALSE, width will be 0.8* and height will be 0.9* of browser return.
* @param float Optional Window width multiplier: width is multiplied by this after working out percentage
*/
function coreOpenPopup(url, percent, widthMult)
{
	percent = percent || false;
	widthMult = widthMult || false;
	var objectReturn = new coreBrowserDimensions();
	if(percent)
	{
		var w = Math.round(objectReturn.browserWidth * percent/100);
		var h = Math.round(objectReturn.browserHeight * percent/100);
	}
	else
	{
		var w = Math.round(objectReturn.browserWidth * 0.8);
		var h = Math.round(objectReturn.browserHeight * 0.9);
	}
	if(widthMult)
		w = w * widthMult;
	window.open(url,'winReadMe','height=' + h + ',width=' + w + ',left=10,top=10,status,scrollbars,resizable,dependent');
}
/**
* Close pop-up
*/
function coreClosePopup()
{
	window.close();
}
/**
* Back button
*/
	function coreGoBack()
	{
		window.history.back();
	}
/**
* Password check
* Adapted from https://www.the-art-of-web.com/javascript/validate-password/
* @param string String to test
* @param string Regexp string
* @return boolean
*/
function checkPassword(str, regexp)
{
	return regexp.test(str);
}

/**
* Password check container called from PHP
* Adapted from https://www.the-art-of-web.com/javascript/validate-password/
* @param object Form object
* @param string Regexp string
* @param int Number of Chars minimum
* @param string Error message for missing input
* @param string Error message for password mismatch
* @param string Error message for invalid password
* @return boolean
*/
function checkForm(form, regexp, noChars, missing, misMatch, invalidPassword)
{
	if(form.username != undefined && form.username.value == "")
	{
		alert(missing);
		form.username.focus();
		return false;
	}
	var str = form.password.value;
	if(str.length < noChars)
	{
		alert(invalidPassword);
		form.password.focus();
		return false;
	}
	if(form.password.value == form.passwordConfirm.value)
	{
		if(!checkPassword(form.password.value, regexp))
		{
			alert(invalidPassword);
			form.password.focus();
			return false;
		}
	}
	else
	{
		alert(misMatch);
		form.password.focus();
		return false;
	}
	return true;
}