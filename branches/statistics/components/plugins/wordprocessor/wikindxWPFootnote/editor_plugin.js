(function(){tinymce.PluginManager.requireLangPack('wikindxWPFootnote');tinymce.create('tinymce.plugins.WikindxWPFootnotePlugin',{init:function(ed,url){ed.addCommand('mceWikindxWPFootnote',function(){ed.windowManager.open({file:url+'/dialog.php',width:480,height:200,inline:1,scrollbars:'yes'},{plugin_url:url,some_custom_arg:'custom arg'})});ed.addButton('wikindxWPFootnote',{title:'wikindxWPFootnote.desc',cmd:'mceWikindxWPFootnote',image:url+'/img/WPFootnote.gif'});ed.onNodeChange.add(function(ed,cm,n){cm.setActive('wikindxWPFootnote',n.nodeName=='IMG')})},createControl:function(n,cm){return null},getInfo:function(){return{longname:'WikindxWPFootnote plugin',author:'Mark Grimshaw-Aagaard',authorurl:'https://wikindx.sourceforge.io',infourl:'',version:"1.1"}}});tinymce.PluginManager.add('wikindxWPFootnote',tinymce.plugins.WikindxWPFootnotePlugin)})();