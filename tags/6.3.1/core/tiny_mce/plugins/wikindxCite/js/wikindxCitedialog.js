tinyMCEPopup.requireLangPack();

var citedialog = {
	init : function() {
	},

	insert : function() {
		// Insert the contents from the input into the document
		citeOutput = getCiteString();
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, citeOutput);
		tinyMCEPopup.close();
	}
};

function getCiteString(){
 var radio = document.forms['citeForm']['cite'];
 var item = 0;
 var citation = '';
 if (typeof radio.length == "undefined")
   var citeSelect = document.getElementById('cite').value;
else
{
while (item < radio.length) {
	if (radio[item].checked) {
	var citeSelect = radio[item].value;
	break;
		}
		item++;
	}
}
var split = citeSelect.split('_');
if(split.length == 2)
	citation = decode_base64(split[1]);
citeSelect = split[0];
 var pageStart = document.forms['citeForm'].elements['pageStart'];
 var pageEnd = document.forms['citeForm'].elements['pageEnd'];
 var preText = document.forms['citeForm'].elements['preText'];
 var postText = document.forms['citeForm'].elements['postText'];
 var citeString = citation + '[cite]' + citeSelect; // resource Id
 if (pageStart.value.length > 0){ // page start exists
  citeString += ':' + pageStart.value;
  if (pageEnd.value.length > 0){ // page end exists
   citeString += '-' + pageEnd.value;
  }
 }
 else if (pageEnd.value.length > 0){ // no page start, but page end exists
  citeString += ':' + pageEnd.value;
 }
 if (preText.value.length > 0){ // preText exists
  citeString += '|' + preText.value;
  if (postText.value.length > 0){ // postText exists
   citeString += '`' + postText.value;
  }
 }
  else if (postText.value.length > 0){ // postText exists
   citeString += '|`' + postText.value;
  }
 citeString += '[/cite]';
 return citeString;
}

function decode_base64(s)
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


