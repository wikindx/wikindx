/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

(function() {
	tinymce.create('tinymce.plugins.WikindxLinkPlugin', {
		init : function(ed, url) {
			this.editor = ed;

			// Register commands
			ed.addCommand('mceWikindxLink', function() {
				var se = ed.selection;

				// No selection and not in link
				if (se.isCollapsed() && !ed.dom.getParent(se.getNode(), 'A'))
					return;

				ed.windowManager.open({
					file : url + '/dialog.php',
					width : 480 + parseInt(ed.getLang('wikindxLink.delta_width', 0)),
					height : 200 + parseInt(ed.getLang('wikindxLink.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('wikindxLink', {
				title : 'Link',
				cmd : 'mceWikindxLink',
				image : url + '/img/wikindxLink.gif'
			});

			ed.addShortcut('ctrl+k', 'wikindxLink.advlink_desc', 'mceWikindxLink');

			ed.onNodeChange.add(function(ed, cm, n, co) {
				cm.setDisabled('wikindxLink', co && n.nodeName != 'A');
				cm.setActive('wikindxLink', n.nodeName == 'A' && !n.name);
			});
		},

		getInfo : function() {
			return {
				longname : 'Advanced link',
				author : 'Moxiecode Systems AB : edited by Mark Grimshaw-Aagaard',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/advlink',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('wikindxLink', tinymce.plugins.WikindxLinkPlugin);
})();