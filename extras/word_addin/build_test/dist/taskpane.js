!function(e){var t={};function n(i){if(t[i])return t[i].exports;var l=t[i]={i:i,l:!1,exports:{}};return e[i].call(l.exports,l,l.exports,n),l.l=!0,l.exports}n.m=e,n.c=t,n.d=function(e,t,i){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:i})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var i=Object.create(null);if(n.r(i),Object.defineProperty(i,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var l in e)n.d(i,l,function(t){return e[t]}.bind(null,l));return i},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=312)}({307:function(e,t,n){e.exports=n.p+"c53ce4f2ee4d1f3c1f4158766525bc0f.png"},308:function(e,t,n){e.exports=n.p+"6eb4b2f6b5b0a3799c71986453d1c449.png"},309:function(e,t,n){e.exports=n.p+"d1e1978e7a8e1ee13dd25a81ddcacff7.png"},312:function(e,t,n){"use strict";n.r(t);n(307),n(308),n(309);function i(e){document.getElementById("wikindx-display-results").style.display="none",document.getElementById("wikindx-finalize").style.display="none",document.getElementById("wikindx-success").style.display="none",document.getElementById("wikindx-finalize-working").style.display="none",document.getElementById("wikindx-search-working").style.display="none";var t=document.getElementById("wikindx-error");t.innerHTML=e,t.style.display="block",document.getElementById("wikindx-messages").style.display="block"}function l(e){document.getElementById("wikindx-display-results").style.display="none",document.getElementById("wikindx-finalize").style.display="none",document.getElementById("wikindx-error").style.display="none";var t=document.getElementById("wikindx-success");t.innerHTML=e,t.style.display="block",document.getElementById("wikindx-messages").style.display="block"}var o,d=[];function r(e){d=[];for(var t=0;t<e.length;t++)"none"!=document.getElementById(e[t]).style.display&&(d.push(e[t]),document.getElementById(e[t]).style.display="none")}function c(){for(var e=0;e<d.length;e++)document.getElementById(d[e]).style.display="block"}function s(){document.getElementById("wikindx-display-results").style.display="none",document.getElementById("wikindx-messages").style.display="none",document.getElementById("wikindx-search-working").style.display="none",document.getElementById("wikindx-search-completed").style.display="none"}var a,u,m,y="ERROR: XMLHTTP error – could not connect to the WIKINDX.  <br/><br/>There could be any number of reasons for this including an incorrect WIKINDX URL, an incompatibility between this add-in and the WIKINDX, the WIKINDX admin has not enabled read-only access, a network error . . .",g=null;function f(e){return a=e,!0}function p(e){var t,n=e.length,i="";for(t=0;t<n;t++)i+='<option value="'+e[t][0]+'">'+e[t][1]+"</option>",t||(u=e[t][0],m=e[t][1]);document.getElementById("wikindx-url").innerHTML=i,document.getElementById("wikindx-url-visit").href=u}function k(){return!0!==x(document.getElementById("wikindx-url").value)?(i(y),!1):(l("Yes, I am alive and kicking. Try searching me . . ."),!0)}function x(e){return e||(e=document.getElementById("wikindx-url").value),a=e+"office.php?method=heartbeat",w(),null==g?i(y):"access denied"!=g||i("The WIKINDX admin has not enabled read-only access.")}function w(){g=null,o.open("POST",a,!1),o.setRequestHeader("Content-type","application/x-www-form-urlencoded"),o.onerror=function(){return i(y),!1},o.send()}function I(){try{return new XMLHttpRequest}catch(e){}try{return new ActiveXObject("Msxml2.XMLHTTP")}catch(e){}try{return new ActiveXObject("Microsoft.XMLHTTP")}catch(e){}throw new Error("Could not create HTTP request object.")}var h=[];function E(){var e=x(!1);if(!0!==e)return i(e),!1;var t=document.getElementById("wikindx-styles"),n=document.getElementById("wikindx-styleSelectBox");if(function(){var e=document.getElementById("wikindx-url");a=e.value+"office.php?method=getStyles",w();var t=JSON.parse(window.localStorage.getItem("wikindx-localStorage"));u=e.value;for(var n=0;n<t.length;n++)if(t[n][0]==u){m=t[n][1];break}document.getElementById("wikindx-url-visit").href=u}(),null==g)return i(y),t.style.display="none",!1;var l,o=g.length;if(!o)return i("ERROR: Either no styles are defined on the selected WIKINDX or there are no available in-text citation styles."),t.style.display="none",!1;var d="";for(l=0;l<o;l++)d+='<option value="'+g[l].styleShort+'">'+g[l].styleLong+"</option>";return n.innerHTML=d,s(),t.style.display="block",!0}function B(){var e,t,n,l=[],o=new Object,d="",r=!1;for(e=0;e<h.length;e++){var c=[];if(f(h[e][0]+"office.php?method=getStyles"),w(),null==g)return i(y),!1;if(n=g.length)if(r=!0,e){for(t=0;t<n;t++)c.push(g[t].styleShort),o[g[t].styleShort]=g[t].styleLong;l=l.filter((function(e){return c.includes(e)}))}else for(t=0;t<n;t++)l.push(g[t].styleShort),o[g[t].styleShort]=g[t].styleLong}if(!r)return i("ERROR: No available in-text citation styles found in any of the wikindices used in the document."),!1;if(!l.length)for(h.sort((function(e,t){return t[1]-e[1]})),f(h[0][0]+"office.php?method=getStyles"),w(),l=[],o=[],n=g.length,t=0;t<n;t++)l.push(g[t].styleShort),o[g[t].styleShort]=g[t].styleLong;for(e=0;e<l.length;e++)d+='<option value="'+l[e]+'">'+o[l[e]]+"</option>";return document.getElementById("wikindx-finalize-styleSelectBox").innerHTML=d,!0}function b(e){for(var t=h.length,n=0;n<t;n++)if(e==h[n][0])return void h[n][1]++;h.push([e,1])}var v="ERROR: Missing URL or name input.";function S(e){for(var t=["wikindx-url-entry","wikindx-url-edit","wikindx-urls-remove","wikindx-urls-preferred"],n=0;n<t.length;n++)t[n]!=e&&(document.getElementById(t[n]).style.display="none");r(["wikindx-search-parameters","wikindx-display-results"]),document.getElementById("wikindx-messages").style.display="none",document.getElementById(e).style.display="block",document.getElementById("wikindx-url-management").style.display="block"}function R(){document.getElementById("wikindx-url-management-subtitle").innerHTML="Edit WIKINDX",document.getElementById("wikindx-edit-url").value=u,document.getElementById("wikindx-edit-name").value=m,S("wikindx-url-edit")}function O(){var e=document.getElementById("wikindx-edit-url").value.trim(),t=document.getElementById("wikindx-edit-name").value.trim();if(e)if(t){if("/"!=e.slice(-1)&&(e+="/"),e==u&&t==m)return d.push("wikindx-search-parameters"),c(),document.getElementById("wikindx-url-management").style.display="none",void l("Edited WIKINDX URL.");var n=x(e);if(!0!==n)return i(n),!1;for(var o=JSON.parse(window.localStorage.getItem("wikindx-localStorage")),r=0;r<o.length;r++)if(u==o[r][0]){var s=r;break}for(r=0;r<o.length;r++)if(r!=s){if(e==o[r][0])return void i("ERROR: Duplicate URL input.");if(t==o[r][1])return void i("ERROR: Duplicate name input.")}o[s][0]=e,o[s][1]=t,window.localStorage.setItem("wikindx-localStorage",JSON.stringify(o)),p(o),d.push("wikindx-search-parameters"),c(),document.getElementById("wikindx-url-management").style.display="none",l("Edited WIKINDX URL.")}else i(v);else i(v)}function N(){document.getElementById("wikindx-url-management-subtitle").innerHTML="Add WIKINDX",S("wikindx-url-entry")}function D(){var e=document.getElementById("wikindx-new-url").value.trim(),t=document.getElementById("wikindx-new-url-name").value.trim();if(e)if(t){"/"!=e.slice(-1)&&(e+="/");var n=x(e);if(!0!==n)return i(n),!1;if(null==window.localStorage.getItem("wikindx-localStorage"))var o=[[e,t]];else{var r,s=(o=JSON.parse(window.localStorage.getItem("wikindx-localStorage"))).length;for(r=0;r<s;r++){if(e==o[r][0])return void i("ERROR: Duplicate URL input.");if(t==o[r][1])return void i("ERROR: Duplicate name input.")}o.push([e,t])}++K>1&&(document.getElementById("wikindx-url-preferred").style.display="block"),window.localStorage.setItem("wikindx-localStorage",JSON.stringify(o)),p(o),d.push("wikindx-search-parameters"),d.push("wikindx-action"),c(),document.getElementById("wikindx-url-management").style.display="none",E(),l("Stored new WIKINDX: "+t+" ("+e+")")}else i(v);else i(v)}function T(){var e,t,n=JSON.parse(window.localStorage.getItem("wikindx-localStorage")),i=n.length,l="",o="checked";for(e=0;e<i;e++)e&&(o=""),l+='<input type="radio" id="'+(t=n[e][1])+'" name="wikindx-preferred" value="'+t+'"'+o+'><label for="'+t+'"> '+n[e][1]+": "+n[e][0]+"</label><br/>";l+='<button class="button" id="wikindx-url-prefer" alt="Set preferred WIKINDX" title="Set preferred WIKINDX">Store</button>',l+='<button class="button" id="wikindx-close-url-preferred" alt="Close" title="Close">Close</button>',document.getElementById("wikindx-url-management-subtitle").innerHTML="Preferred WIKINDX",document.getElementById("wikindx-urls-preferred").innerHTML=l,S("wikindx-urls-preferred"),document.getElementById("wikindx-url-prefer").onclick=z,document.getElementById("wikindx-close-url-preferred").onclick=W}function z(){var e=JSON.parse(window.localStorage.getItem("wikindx-localStorage")),t=document.querySelector('input[name="wikindx-preferred"]:checked').value,n=[],i=[];if(t!=e[0][1]){i=e[0];for(var o=1;o<e.length;o++)t==e[o][1]?n[0]=e[o]:n[o]=e[o];n.push(i),window.localStorage.setItem("wikindx-localStorage",JSON.stringify(n)),p(n),E()}c(),document.getElementById("wikindx-url-management").style.display="none",document.getElementById("wikindx-search-parameters").style.display="block",l("Preference stored.")}function L(){var e,t,n=JSON.parse(window.localStorage.getItem("wikindx-localStorage")),i=n.length,l="";for(e=0;e<i;e++)l+='<input type="checkbox" id="'+(t=n[e][0]+"--WIKINDX--"+n[e][1])+'" name="'+t+'"><label for="'+t+'"> '+n[e][1]+": "+n[e][0]+"</label><br/>";l+='<button class="button" id="wikindx-url-remove" alt="Delete URLs" title="Delete URLs">Delete URLs</button>',l+='<button class="button" id="wikindx-close-url-remove" alt="Close" title="Close">Close</button>',document.getElementById("wikindx-url-management-subtitle").innerHTML="Delete WIKINDX",document.getElementById("wikindx-urls-remove").innerHTML=l,S("wikindx-urls-remove"),document.getElementById("wikindx-url-remove").onclick=C,document.getElementById("wikindx-close-url-remove").onclick=W}function C(){var e,t,n=JSON.parse(window.localStorage.getItem("wikindx-localStorage")),i=n.length,o=0,d=[],r=[];for(e=0;e<i;e++)t=n[e][0]+"--WIKINDX--"+n[e][1],0==document.getElementById(t).checked?(d=t.split("--WIKINDX--"),r.push([d[0],d[1]])):++o;if(o){if(K<2&&(document.getElementById("wikindx-url-preferred").style.display="none"),o==i)return window.localStorage.removeItem("wikindx-localStorage"),document.getElementById("wikindx-about-begin").style.display="block",document.getElementById("wikindx-urls-remove").style.display="none",document.getElementById("wikindx-action").style.display="none",N(),document.getElementById("wikindx-close-url-entry").style.display="none",void l("Deleted all WIKINDX URLs.");window.localStorage.setItem("wikindx-localStorage",JSON.stringify(r)),p(r),E(),document.getElementById("wikindx-url-management").style.display="none",c(),l("Deleted WIKINDX URL(s).")}else L()}function W(){c(),document.getElementById("wikindx-url-management").style.display="none",document.getElementById("wikindx-messages").style.display="none",document.getElementById("wikindx-search-parameters").style.display="block"}var H,X,J,M,U,K=0;function _(e){return P()?(d.length?c():(document.getElementById("wikindx-about-begin").style.display="none",document.getElementById("wikindx-url-management").style.display="none",document.getElementById("wikindx-search-parameters").style.display="block",E()),!0):(document.getElementById("wikindx-search-parameters").style.display="none",document.getElementById("wikindx-close-url-entry").style.display="none",e?(document.getElementById("wikindx-url-management-subtitle").innerHTML="Add WIKINDX",S("wikindx-url-entry"),document.getElementById("wikindx-about").style.display="none"):(document.getElementById("wikindx-url-management").style.display="none",document.getElementById("wikindx-about").style.display="block",document.getElementById("wikindx-about-begin").style.display="block",document.getElementById("wikindx-display-about").src="../../assets/lightbulb.png"),!1)}function P(){if(null==window.localStorage.getItem("wikindx-localStorage"))return!1;var e=JSON.parse(window.localStorage.getItem("wikindx-localStorage"));return(K=e.length)>1&&(document.getElementById("wikindx-url-preferred").style.display="block"),p(e),!0}function j(){"none"==document.getElementById("wikindx-about").style.display?(P()?document.getElementById("wikindx-about-begin").style.display="none":document.getElementById("wikindx-about-begin").style.display="block",document.getElementById("wikindx-about").style.display="block",r(["wikindx-url-management","wikindx-messages","wikindx-display-results","wikindx-search-parameters","wikindx-finalize","wikindx-action-title-references","wikindx-action-title-citations","wikindx-action-title-finalize"]),document.getElementById("wikindx-display-about").src="../../assets/lightbulb.png"):(document.getElementById("wikindx-about").style.display="none",document.getElementById("wikindx-display-about").src="../../assets/lightbulb_off.png",c(),_(!0))}function A(){"none"==document.getElementById("wikindx-references-help").style.display?(document.getElementById("wikindx-citations-help").style.display="none",document.getElementById("wikindx-finalize-help").style.display="none",document.getElementById("wikindx-display-citations-help").src="../../assets/lightbulb_off.png",document.getElementById("wikindx-display-finalize-help").src="../../assets/lightbulb_off.png",document.getElementById("wikindx-references-help").style.display="block",document.getElementById("wikindx-display-references-help").src="../../assets/lightbulb.png"):(document.getElementById("wikindx-references-help").style.display="none",document.getElementById("wikindx-display-references-help").src="../../assets/lightbulb_off.png")}function q(){"none"==document.getElementById("wikindx-citations-help").style.display?(document.getElementById("wikindx-references-help").style.display="none",document.getElementById("wikindx-finalize-help").style.display="none",document.getElementById("wikindx-display-references-help").src="../../assets/lightbulb_off.png",document.getElementById("wikindx-display-finalize-help").src="../../assets/lightbulb_off.png",document.getElementById("wikindx-citations-help").style.display="block",document.getElementById("wikindx-display-citations-help").src="../../assets/lightbulb.png"):(document.getElementById("wikindx-citations-help").style.display="none",document.getElementById("wikindx-display-citations-help").src="../../assets/lightbulb_off.png")}function Y(){"none"==document.getElementById("wikindx-finalize-help").style.display?(document.getElementById("wikindx-citations-help").style.display="none",document.getElementById("wikindx-references-help").style.display="none",document.getElementById("wikindx-display-citations-help").src="../../assets/lightbulb_off.png",document.getElementById("wikindx-display-references-help").src="../../assets/lightbulb_off.png",document.getElementById("wikindx-finalize-help").style.display="block",document.getElementById("wikindx-display-finalize-help").src="../../assets/lightbulb.png"):(document.getElementById("wikindx-finalize-help").style.display="none",document.getElementById("wikindx-display-finalize-help").src="../../assets/lightbulb_off.png")}function F(e,t,n,i,l,o,d){try{var r=e[o](d),c=r.value}catch(e){return void n(e)}r.done?t(c):Promise.resolve(c).then(i,l)}function G(e){return function(){var t=this,n=arguments;return new Promise((function(i,l){var o=e.apply(t,n);function d(e){F(o,i,l,d,r,"next",e)}function r(e){F(o,i,l,d,r,"throw",e)}d(void 0)}))}}var Q,V=[],Z=[],$=[],ee=new Object,te=new Object;function ne(){var e=!1;h=[],Word.run(function(){var t=G(regeneratorRuntime.mark((function t(n){var l,o,d;return regeneratorRuntime.wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return l=n.document.contentControls.load("items"),t.next=3,n.sync();case 3:d=0;case 4:if(!(d<l.items.length)){t.next=19;break}if(!((o=l.items[d].tag.split("-")).length>1&&"wikindx"===o[0]&&""===l.items[d].text.trim())){t.next=9;break}return l.items[d].delete(),t.abrupt("continue",16);case 9:if(!(o.length<3||"wikindx"!=o[0])){t.next=11;break}return t.abrupt("continue",16);case 11:if("id"==o[1]){t.next=13;break}return t.abrupt("continue",16);case 13:e=!0,b(JSON.parse(atob(o[2]))[0]);case 16:d++,t.next=4;break;case 19:if(e){t.next=22;break}return i("You have not inserted any references or citations yet so there is nothing to finalize."),t.abrupt("return",!1);case 22:B()&&(document.getElementById("wikindx-finalize").style.display="block");case 23:case"end":return t.stop()}}),t)})));return function(e){return t.apply(this,arguments)}}()).catch((function(e){if(console.log("Error: "+e),e instanceof OfficeExtension.Error)return console.log("Debug info: "+JSON.stringify(e.debugInfo)),i(JSON.stringify(e)),!1}))}function ie(){var e,t,n,l,o,d;M=!1,U="",V=[],Z=[],$=[],ee=new Object,te=new Object,document.getElementById("wikindx-finalize-working").style.display="block",document.getElementById("wikindx-finalize-completed").style.display="none",Word.run(function(){var i=G(regeneratorRuntime.mark((function i(r){var c,s,a,u,m,y,g,f;return regeneratorRuntime.wrap((function(i){for(;;)switch(i.prev=i.next){case 0:return d=r.document.contentControls.load("items"),i.next=3,r.sync();case 3:for(le(d),t=Object.keys(ee),Q=!1,t.length>1&&(Q=!0,c=document.getElementById("wikindx-finalize-order").value,e=c.split("_"),H=e[0],X=e[1]),n=0;n<t.length;n++)re(r,t[n],JSON.stringify(ee[t[n]]));return i.next=10,r.sync();case 10:for(l=0;l<Z.length;l++)for(s=Z[l],o=0;o<s.cc.items.length;o++)a=s.cc.items[o],u=s.text,a.insertHtml(u,"Replace");return i.next=13,r.sync();case 13:for(t=Object.keys(te),n=0;n<t.length;n++)de(r,t[n],JSON.stringify(te[t[n]]));return i.next=17,r.sync();case 17:for(l=0;l<Z.length;l++)for(m=Z[l],o=0;o<m.cc.items.length;o++)y=m.cc.items[o],g=m.text,y.insertHtml(g,"Replace");if(oe(),!M){i.next=27;break}return(d=r.document.contentControls.getByTag("wikindx-bibliography")).load("items"),i.next=24,r.sync();case 24:d.items[0].insertHtml(U,"Replace"),i.next=38;break;case 27:r.document.body.paragraphs.getLast().select("End"),(f=r.document.getSelection()).insertBreak("Line","After"),f.insertBreak("Line","After"),r.document.body.paragraphs.getLast().select("End"),f=r.document.getSelection(),(d=f.insertContentControl()).color="orange",d.tag="wikindx-bibliography",d.title="Bibliography",d.insertHtml(U,"End");case 38:case"end":return i.stop()}}),i)})));return function(e){return i.apply(this,arguments)}}()).then((function(){document.getElementById("wikindx-finalize-working").style.display="none",document.getElementById("wikindx-finalize-completed").style.display="block"})).catch((function(e){if(console.log("Error: "+e),e instanceof OfficeExtension.Error)return console.log("Debug info: "+JSON.stringify(e.debugInfo)),i(JSON.stringify(e)),!1}))}function le(e){var t,n,i,l=[];for(t=0;t<e.items.length;t++)"wikindx-bibliography"!=e.items[t].tag?(l=e.items[t].tag.split("-")).length<3||"wikindx"!=l[0]||"id"==l[1]&&(n=JSON.parse(atob(l[2])),J=n[1],3==n.length&&(i=n[2],n[0]in te?te[n[0]].includes(i)||te[n[0]].push(i):te[n[0]]=[i]),n[0]in ee?ee[n[0]].includes(J)||ee[n[0]].push(J):ee[n[0]]=[J]):M=!0}function oe(){var e,t;if(Q)for("creator"==H?$.sort((function(e,t){return e.creator.localeCompare(t.creator)||e.year-t.year||e.title.localeCompare(t.title)})):"title"==H?$.sort((function(e,t){return e.title.localeCompare(t.title)||e.creator.localeCompare(t.creator)||e.year-t.year})):$.sort((function(e,t){return e.year-t.year||e.creator.localeCompare(t.creator)||e.title.localeCompare(t.title)})),"DESC"==X&&$.reverse(),e=0;e<$.length;e++)t=$[e].index,U+="<p>"+V[t]+"</p>";else for(e=0;e<V.length;e++)U+="<p>"+V[e]+"</p>"}function de(e,t,n){var i,l,o,d,r,c;for(d=t,r=n,c=document.getElementById("wikindx-finalize-styleSelectBox"),a=d+"office.php?method=getCiteCCs&style="+encodeURI(c.value)+"&ids="+encodeURI(r),w(),Z=[],o=0;o<g.length;o++){i="wikindx-id-"+btoa(JSON.stringify([t,g[o].id,g[o].metaId])),(l=e.document.contentControls.getByTag(i)).load("items");var s={cc:l,text:g[o].inTextReference};Z.push(s)}}function re(e,t,n){var i,l,o,d,r,c,s,u;for(r=t,c=n,s=document.getElementById("wikindx-finalize-order"),u=document.getElementById("wikindx-finalize-styleSelectBox"),a=r+"office.php?method=getBib&style="+encodeURI(u.value)+"&searchParams="+encodeURI(s.value)+"&ids="+encodeURI(c),w(),d=0;d<g.length;d++){l="wikindx-id-"+btoa(JSON.stringify([t,g[d].id])),(o=e.document.contentControls.getByTag(l)).load("items");var m={cc:o,text:g[d].inTextReference};Z.push(m),V.includes(g[d].bibEntry)||(V.push(g[d].bibEntry),Q&&(i=V.indexOf(g[d].bibEntry),$.push({index:i,creator:g[d].creatorOrder,title:g[d].titleOrder,year:g[d].yearOrder})))}}function ce(e,t,n,i,l,o,d){try{var r=e[o](d),c=r.value}catch(e){return void n(e)}r.done?t(c):Promise.resolve(c).then(i,l)}function se(e){return function(){var t=this,n=arguments;return new Promise((function(i,l){var o=e.apply(t,n);function d(e){ce(o,i,l,d,r,"next",e)}function r(e){ce(o,i,l,d,r,"throw",e)}d(void 0)}))}}var ae,ue="",me="",ye="",ge="",fe=!1,pe="ERROR: Resource or citation ID not found in the selected WIKINDX.";function ke(){document.getElementById("wikindx-about").style.display="none",document.getElementById("wikindx-display-about").src="../../assets/lightbulb_off.png","references"==document.getElementById("wikindx-action").value?(r(["wikindx-messages","wikindx-display-results","wikindx-citation-order"]),document.getElementById("wikindx-search-completed").style.display="none",document.getElementById("wikindx-url-management").style.display="none",document.getElementById("wikindx-action-title-citations").style.display="none",document.getElementById("wikindx-action-title-finalize").style.display="none",document.getElementById("wikindx-finalize").style.display="none",document.getElementById("wikindx-search-completed").style.display="none",document.getElementById("wikindx-search-working").style.display="none",document.getElementById("wikindx-citations-help").style.display="none",document.getElementById("wikindx-finalize-help").style.display="none",document.getElementById("wikindx-display-citations-help").src="../../assets/lightbulb_off.png",document.getElementById("wikindx-display-finalize-help").src="../../assets/lightbulb_off.png",document.getElementById("wikindx-action-title-references").style.display="block",document.getElementById("wikindx-reference-order").style.display="block",document.getElementById("wikindx-search-parameters").style.display="block"):"citations"==document.getElementById("wikindx-action").value?(r(["wikindx-messages","wikindx-display-results","wikindx-reference-order"]),document.getElementById("wikindx-search-completed").style.display="none",document.getElementById("wikindx-url-management").style.display="none",document.getElementById("wikindx-action-title-references").style.display="none",document.getElementById("wikindx-action-title-finalize").style.display="none",document.getElementById("wikindx-finalize").style.display="none",document.getElementById("wikindx-search-completed").style.display="none",document.getElementById("wikindx-search-working").style.display="none",document.getElementById("wikindx-references-help").style.display="none",document.getElementById("wikindx-finalize-help").style.display="none",document.getElementById("wikindx-display-references-help").src="../../assets/lightbulb_off.png",document.getElementById("wikindx-display-finalize-help").src="../../assets/lightbulb_off.png",document.getElementById("wikindx-action-title-citations").style.display="block",document.getElementById("wikindx-citation-order").style.display="block",document.getElementById("wikindx-search-parameters").style.display="block"):"finalize"==document.getElementById("wikindx-action").value&&(r(["wikindx-messages","wikindx-display-results","wikindx-url-management","wikindx-search-parameters"]),document.getElementById("wikindx-finalize-completed").style.display="none",document.getElementById("wikindx-action-title-references").style.display="none",document.getElementById("wikindx-action-title-citations").style.display="none",document.getElementById("wikindx-citations-help").style.display="none",document.getElementById("wikindx-references-help").style.display="none",document.getElementById("wikindx-display-citations-help").src="../../assets/lightbulb_off.png",document.getElementById("wikindx-display-references-help").src="../../assets/lightbulb_off.png",document.getElementById("wikindx-action-title-finalize").style.display="block",ne())}function xe(){return we.apply(this,arguments)}function we(){return(we=se(regeneratorRuntime.mark((function e(){var t;return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if(t=(t=(t=document.getElementById("wikindx-search-text").value).trim()).replace(/[\u201C\u201D]/g,'"')){e.next=6;break}return i("ERROR: Missing search input."),e.abrupt("return",!1);case 6:if("references"!=document.getElementById("wikindx-action").value){e.next=11;break}return e.next=9,he("references",t);case 9:e.next=14;break;case 11:if("citations"!=document.getElementById("wikindx-action").value){e.next=14;break}return e.next=14,he("citations",t);case 14:case"end":return e.stop()}}),e)})))).apply(this,arguments)}function Ie(e){return new Promise((function(t){return setTimeout(t,e)}))}function he(e,t){return Ee.apply(this,arguments)}function Ee(){return(Ee=se(regeneratorRuntime.mark((function e(t,n){return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return document.getElementById("wikindx-search-completed").style.display="none",document.getElementById("wikindx-search-working").style.display="block",e.next=4,Ie(13);case 4:"references"==t?be(n):Be(n),document.getElementById("wikindx-search-working").style.display="none",document.getElementById("wikindx-search-completed").style.display="block";case 7:case"end":return e.stop()}}),e)})))).apply(this,arguments)}function Be(e){return function(e){var t=document.getElementById("wikindx-citation-params"),n=document.getElementById("wikindx-styleSelectBox"),i=document.getElementById("wikindx-url");a=i.value+"office.php?method=getCitations&searchWord="+encodeURI(e)+"&style="+encodeURI(n.value)+"&searchParams="+encodeURI(t.value),w()}(e),null==g?(i("No citations found matching your search."),!1):(function(){var e,t=document.getElementById("wikindx-citeSelectBox"),n=g.length,i="";for(e=0;e<n;e++)ye=g[e].citation,ge=g[e].id,i+='<option value="'+ge+'">'+ye+"</option>",0==e&&(fe=ge);Se(),t.innerHTML=i,document.getElementById("wikindx-messages").style.display="none",document.getElementById("wikindx-display-ref").style.display="none",document.getElementById("wikindx-insert-refSection").style.display="none",document.getElementById("wikindx-refSelectBox").style.display="none",document.getElementById("wikindx-display-cite").style.display="block",document.getElementById("wikindx-insert-citeSection").style.display="block",document.getElementById("wikindx-display-results").style.display="block",document.getElementById("wikindx-citeSelectBox").style.display="block"}(),!0)}function be(e){return function(e){var t=document.getElementById("wikindx-reference-params"),n=document.getElementById("wikindx-styleSelectBox"),i=document.getElementById("wikindx-url");a=i.value+"office.php?method=getReferences&searchWord="+encodeURI(e)+"&style="+encodeURI(n.value)+"&searchParams="+encodeURI(t.value),w()}(e),null==g?(i("No references found matching your search."),!1):(function(){var e,t=document.getElementById("wikindx-refSelectBox"),n=g.length,i="";for(e=0;e<n;e++)ue=g[e].bibEntry,ge=g[e].id,i+='<option value="'+ge+'">'+ue+"</option>",0==e&&(fe=ge);ve(),t.innerHTML=i,document.getElementById("wikindx-messages").style.display="none",document.getElementById("wikindx-display-cite").style.display="none",document.getElementById("wikindx-insert-citeSection").style.display="none",document.getElementById("wikindx-citeSelectBox").style.display="none",document.getElementById("wikindx-display-ref").style.display="block",document.getElementById("wikindx-insert-refSection").style.display="block",document.getElementById("wikindx-display-results").style.display="block",document.getElementById("wikindx-refSelectBox").style.display="block"}(),!0)}function ve(){return Re(),"Bad ID"==g?(i(pe),!1):(ue=g.bibEntry,document.getElementById("wikindx-display-ref").innerHTML="</br>"+ue,!0)}function Se(){return Oe(),"Bad ID"==g?(i(pe),!1):(ye=g.citation,ue=g.bibEntry,document.getElementById("wikindx-display-cite").innerHTML="</br>"+ye+"<br/><br/>"+ue,!0)}function Re(){if(fe){e=fe;fe=!1}else var e=document.getElementById("wikindx-refSelectBox").value;!function(e){var t=document.getElementById("wikindx-url"),n=document.getElementById("wikindx-styleSelectBox");a=t.value+"office.php?method=getReference&style="+encodeURI(n.value)+"&id="+encodeURI(e),w()}(e)}function Oe(){if(fe){e=fe;fe=!1}else var e=document.getElementById("wikindx-citeSelectBox").value;!function(e){var t=document.getElementById("wikindx-url"),n=document.getElementById("wikindx-styleSelectBox"),i="&withHtml=1";document.getElementById("wikindx-citation-html").checked&&(i="&withHtml=0"),a=t.value+"office.php?method=getCitation&style="+encodeURI(n.value)+"&id="+encodeURI(e)+i,w()}(e)}function Ne(){Word.run(function(){var e=se(regeneratorRuntime.mark((function e(t){var n;return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if(Re(),"Bad ID"!=g){e.next=4;break}return i(pe),e.abrupt("return");case 4:return me=g.inTextReference,ae=t.document.getSelection(),(n=ae.insertContentControl()).color="orange",n.tag="wikindx-id-"+btoa(JSON.stringify([u,g.id])),n.title=g.titleCC,console.log(g.titleCC),n.insertHtml(me,"Replace"),e.next=14,t.sync();case 14:case"end":return e.stop()}}),e)})));return function(t){return e.apply(this,arguments)}}()).catch((function(e){if(console.log("Error: "+e),e instanceof OfficeExtension.Error)return console.log("Debug info: "+JSON.stringify(e.debugInfo)),i(JSON.stringify(e)),!1}))}function De(){Word.run(function(){var e=se(regeneratorRuntime.mark((function e(t){var n,l;return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if(Oe(),"Bad ID"!=g){e.next=4;break}return i(pe),e.abrupt("return");case 4:return me=g.inTextReference,ye=g.citation,ae=t.document.getSelection(),t.load(ae,"font/size, font/name, font/color"),e.next=10,t.sync();case 10:return(n=ae.insertContentControl()).color="orange",n.tag="wikindx-id-"+btoa(JSON.stringify([u,g.id,g.metaId])),n.title=g.titleCC,n.insertHtml(me,"Replace"),l=n.getRange("Whole"),document.getElementById("wikindx-citation-html").checked?l.insertHtml(ye+"&nbsp;","Before").font.set({size:ae.font.size,color:ae.font.color,name:ae.font.name}):l.insertHtml(ye+"&nbsp;","Before"),e.next=19,t.sync();case 19:case"end":return e.stop()}}),e)})));return function(t){return e.apply(this,arguments)}}()).catch((function(e){if(console.log("Error: "+e),e instanceof OfficeExtension.Error)return console.log("Debug info: "+JSON.stringify(e.debugInfo)),i(JSON.stringify(e)),!1}))}Office.onReady((function(e){if(OfficeExtension.config.extendedErrorLogging=!0,e.host===Office.HostType.Word){if(Office.context.requirements.isSetSupported("WordApi","1.3")||console.log("Sorry. The WIKINDX citation tool uses Word.js APIs that are not available in your version of Office."),0==((o=new I).onreadystatechange=function(){if(4==o.readyState&&200==o.status)try{g=JSON.parse(o.responseText)}catch(e){return i("ERROR: Unspecified error. This could be any number of things from not being able to connect to the WIKINDX to no resources found matching your search."),!1}},!0))return i(y),!1;document.getElementById("wikindx-search").onclick=xe,document.getElementById("wikindx-action").onchange=ke,document.getElementById("wikindx-finalize-run").onclick=ie,document.getElementById("wikindx-url").onchange=E,document.getElementById("wikindx-styleSelectBox").onchange=s,document.getElementById("wikindx-reference-params").onchange=s,document.getElementById("wikindx-citation-params").onchange=s,document.getElementById("wikindx-refSelectBox").onchange=ve,document.getElementById("wikindx-citeSelectBox").onchange=Se,document.getElementById("wikindx-insert-reference").onclick=Ne,document.getElementById("wikindx-insert-citation").onclick=De,document.getElementById("wikindx-url-store").onclick=D,document.getElementById("wikindx-url-add").onclick=N,document.getElementById("wikindx-url-edit1").onclick=R,document.getElementById("wikindx-url-edit2").onclick=O,document.getElementById("wikindx-close-url-entry").onclick=W,document.getElementById("wikindx-close-url-edit").onclick=W,document.getElementById("wikindx-url-preferred").onclick=T,document.getElementById("wikindx-url-delete").onclick=L,document.getElementById("wikindx-display-about").onclick=j,document.getElementById("wikindx-display-references-help").onclick=A,document.getElementById("wikindx-display-citations-help").onclick=q,document.getElementById("wikindx-display-finalize-help").onclick=Y,document.getElementById("wikindx-url-heartbeat").onclick=k,_(!1),K&&(document.getElementById("wikindx-action").style.display="block")}}))}});
//# sourceMappingURL=taskpane.js.map