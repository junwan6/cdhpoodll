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

tinyMCEPopup.requireLangPack();

var poodllrecordingDialog = {
    init : function(ed) {
    },
    insert : function(userid) {
        var message = document.getElementById("messageAlert");
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
        var contextid = document.getElementById('context_id');
        var myfilename = document.getElementById('myfilename');
        var wwwroot = document.getElementById('wwwroot');
        if (itemidname) {
           itemid = itemid.value;
           contextid = contextid.value;
           myfilename = myfilename.value;
           wwwroot = wwwroot.value
           // It will store in mdl_question with the "@@PLUGINFILE@@/myfile.mp3" for the filepath.
           var h = '<a href="'+wwwroot+'/draftfile.php/'+contextid+'/user/draft/'+itemid+'/'+myfilename+'">'+myfilename+'</a>';
           // Insert the contents from the input into the document.
           tinyMCEPopup.execCommand('mceInsertContent', false,h);
        }
        tinyMCEPopup.restoreSelection();
        tinyMCEPopup.close();
    }
};

tinyMCEPopup.onInit.add(poodllrecordingDialog.init, poodllrecordingDialog);