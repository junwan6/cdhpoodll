<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/export/lib.php';
require_once 'grade_export_xls.php';

$id = required_param('id', PARAM_INT); // course id

$PAGE->set_url('/grade/export/xls/index.php', array('id'=>$id));

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('nocourseid');
}

require_login($course);
$context = context_course::instance($id);

require_capability('moodle/grade:export', $context);
require_capability('gradeexport/xls:view', $context);

print_grade_page_head($COURSE->id, 'export', 'xls', get_string('exportto', 'grades') . ' ' . get_string('pluginname', 'gradeexport_xls'));
export_verify_grades($COURSE->id);

if (!empty($CFG->gradepublishing)) {
    $CFG->gradepublishing = has_capability('gradeexport/xls:publish', $context);
}

$mform = new grade_export_form(null, array('publishing' => true));

$groupmode    = groups_get_course_groupmode($course);   // Groups are being used
$currentgroup = groups_get_course_group($course, true);
if ($groupmode == SEPARATEGROUPS and !$currentgroup and !has_capability('moodle/site:accessallgroups', $context)) {
    echo $OUTPUT->heading(get_string("notingroup"));
    echo $OUTPUT->footer();
    die;
}

// process post information
if ($data = $mform->get_data()) {
    $onlyactive = $data->export_onlyactive || !has_capability('moodle/course:viewsuspendedusers', $context);
    $export = new grade_export_xls($course, $currentgroup, '', false, false, $data->display, $data->decimals, $onlyactive, true);

    // print the grades on screen for feedbacks
    $export->process_form($data);
    $export->print_continue();
    $export->display_preview();
    echo $OUTPUT->footer();
    exit;
}

groups_print_course_menu($course, 'index.php?id='.$id);
echo '<div class="clearer"></div>';

$mform->display();

echo $OUTPUT->footer();

// START UCLA MOD: CCLE-3980 - Add logging to Gradebook & Export to MyUCLA format pages
$url = '/export/xls/index.php?id=' . $course->id;
add_to_log($course->id, 'grade', 'view xls', $url);
// END UCLA MOD: CCLE-3980
