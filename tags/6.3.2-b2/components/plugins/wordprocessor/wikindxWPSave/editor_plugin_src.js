/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */
/**
* Save the word processor paper
* Author:  Mark Grimshaw-Aagaard
* Version:  1.1
* Date:  22/7/2012
*/
(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('wikindxWPSave');

	tinymce.create('tinymce.plugins.WikindxWPSavePlugin', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished its initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceWikindxWPSave');
			ed.addCommand('mceWikindxWPSave', function() {
				ed.windowManager.open({
					file : url + '/dialog.php',
					width : 400,
					height : 200,
					inline : 1,
					scrollbars : 'yes'
				}, {
					plugin_url : url, // Plugin absolute URL
					some_custom_arg : 'custom arg' // Custom argument
				});
			});

			// Register cite button
			ed.addButton('wikindxWPSave', {
				title : 'wikindxWPSave.desc',
				cmd : 'mceWikindxWPSave',
				image : url + '/img/wikindxWPSave.gif'
			});

			// Add a node change handler, selects the button in the UI when a image is selected
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('wikindxWPSave', n.nodeName == 'IMG');
			});
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'WikindxWPSave plugin',
				author : 'Mark Grimshaw-Aagaard',
				authorurl : 'https://wikindx.sourceforge.io',
				infourl : '',
				version : "1.1"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('wikindxWPSave', tinymce.plugins.WikindxWPSavePlugin);
})();