// This file is not a part of Moodle - http://moodle.org/
// This is a none core contributed module.
//
// This is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License
// can be see at <http://www.gnu.org/licenses/>.

/*
 * __________________________________________________________________________
 *
 * PoodLL TinyMCE for Moodle 2.x
 *
 * This plugin need to use together with Poodll filter.
 *
 * @package    poodllrecording
 * @subpackage tinymce_poodllrecording
 * @copyright  2013 UC Regents
 * @copyright  2013 Justin Hunt  {@link http://www.poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * __________________________________________________________________________
 */

(function() {
    // Do not load language pack in moodle plugins.

    tinymce.create('tinymce.plugins.PoodllrecordingPlugin', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {
            /** added */
            var formtextareaid = tinyMCE.activeEditor.id.substr(3);
            var itemidname = '';
            var formtextareatmp = formtextareaid.split("_");
            if (formtextareatmp.length == 2 && !isNaN(formtextareatmp[1])) {
                itemidname = formtextareatmp[0] + '[' + formtextareatmp[1] + '][itemid]';
            }
            else {
                itemidname = formtextareaid + '[itemid]';
            }
            var itemid = window.top.document.getElementsByName(itemidname).item(0);
            if (itemid!=null)
            {
                itemid = itemid.value;
            }
            /** end **/

            // Register commands.
            ed.addCommand('mcePoodllrecording', function() {
                ed.windowManager.open({

                    file : ed.getParam("moodle_plugin_base") + 'poodllrecording/poodllrecording.php?itemid='+itemid,
                    width : 540,
                    height : 380,
                    inline : 1
                }, {
                    plugin_url : url, // Plugin absolute URL.
                    some_custom_arg : 'custom arg' // Custom argument.
                });
            });

            // Register poodllrecording button.
            ed.addButton('poodllrecording', {
                title : 'poodllrecording.desc',
                cmd : 'mcePoodllrecording',
                image : url + '/img/poodllrecording.png'
            });

            // Add a node change handler, selects the button in the UI when a image is selected.
            ed.onNodeChange.add(function(ed, cm, n) {
                var p, c;
                c = cm.get('poodllrecording');
                if (!c) {
                    // Button not used.
                    return;
                }
                p = ed.dom.getParent(n, 'SPAN');

                c.setActive(p && ed.dom.hasClass(p, 'poodllrecording'));

                if (p && ed.dom.hasClass(p, 'poodllrecording') || ed.selection.getContent()) {
                    c.setDisabled(false);
                } else {
                    c.setDisabled(true);
                }
                c.setDisabled(false);
            });
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */

        getInfo : function() {
            return {
                longname : 'poodllrecording tinymce plugin',
                author : 'Jun Wan',
                authorurl : 'http://ccle.ucla.edu',
                infourl : 'http://docs.moodle.org/en/TinyMCE',
                version : "1.0"
            };
        }
    });

    // Register plugin.
    tinymce.PluginManager.add('poodllrecording', tinymce.plugins.PoodllrecordingPlugin);
})();
