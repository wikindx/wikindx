(function(){tinymce.PluginManager.requireLangPack('wikindxImage');tinymce.create('tinymce.plugins.WikindxImagePlugin',{init:function(ed,url){ed.addCommand('mceWikindxImage',function(){ed.windowManager.open({file:url+'/dialog.php',width:615,height:400,inline:1,scrollbars:'yes'},{plugin_url:url,some_custom_arg:'custom arg'})});ed.addButton('wikindxImage',{title:'wikindxImage.desc',cmd:'mceWikindxImage',image:url+'/img/wikindxImage.png'});ed.onNodeChange.add(function(ed,cm,n){cm.setActive('wikindxImage',n.nodeName=='IMG')})},createControl:function(n,cm){return null},getInfo:function(){return{longname:'WikindxImage plugin',author:'Mark Grimshaw-Aagaard',authorurl:'https://wikindx.sourceforge.io',infourl:'',version:"1.1"}}});tinymce.PluginManager.add('wikindxImage',tinymce.plugins.WikindxImagePlugin)})();