<?php

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

define('NO_MOODLE_COOKIES', false);

require('../../../../../config.php');
require_once($CFG->dirroot . '/filter/poodll/poodllresourcelib.php');
require_once($CFG->dirroot.'/repository/lib.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/lib/editor/tinymce/plugins/poodllrecording/poodllrecording.php');

if (isset($SESSION->lang)) {
    // Language is set via page url param.
    $lang = $SESSION->lang;
} else {
    $lang = 'en';
}

require_login();  // CONTEXT_SYSTEM level.
$editor = get_texteditor('tinymce');
$plugin = $editor->get_plugin('poodllrecording');
$itemid = optional_param('itemid', '', PARAM_TEXT);
@header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php print_string('title', 'tinymce_poodllrecording')?></title>
<script type="text/javascript" src="<?php echo $editor->get_tinymce_base_url(); ?>tiny_mce_popup.js"></script>
<script type="text/javascript" src="<?php echo $plugin->get_tinymce_file_url('js/poodllrecording.js'); ?>"></script>

</head>
<body>
<div style="text-align: center;">
<?php
echo "<script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script> ";
$usercontextid=get_context_instance(CONTEXT_USER, $USER->id)->id;
// Load the recorder.
echo fetchMP3RecorderForSubmission('myfilename', $usercontextid ,'user','draft',$itemid);

?>
</div>
<form>
   <div>
      <input id="myfilename" type="hidden" name="myfilename" value="">
      <input type="hidden" name="contextid" value= "<?php echo $usercontextid;?>" id="context_id">
      <input type="hidden" name= "wwwroot" value="<?php echo $CFG->wwwroot;?>" id="wwwroot">
      <p id="messageAlert">After you have finished recording, press Insert.</p>
      <input type="button" id="insert" name="insert" value="{#insert}" onclick="poodllrecordingDialog.insert(<?php echo $USER->id; ?>);" />
      <input type="button" id="cancel" name="cancel" value="{#cancel}" onclick="tinyMCEPopup.close();" />
      <input type="hidden" name="action" value="download">
   </div>
</form>
</body>
</html>