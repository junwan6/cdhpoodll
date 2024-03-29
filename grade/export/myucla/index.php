<?php  

require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/export/lib.php';
require_once 'grade_export_myucla.php';
require_once 'grade_export_form_myucla.php';

$id = required_param('id', PARAM_INT); // course id

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('nocourseid');
}

require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $id);

require_capability('moodle/grade:export', $context);
require_capability('gradeexport/myucla:view', $context);

$strgrades = get_string('grades', 'grades');
$actionstr = get_string('pluginname', 'gradeexport_myucla');
$navigation = grade_build_nav(__FILE__, $actionstr, array('courseid' => $course->id));

$PAGE->set_url('/grade/export/myucla/index.php', array('id'=>$id));
$PAGE->set_title($course->shortname.': '.get_string('grades'));
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
print_grade_page_head($course->id, 'export', 'myucla', 
        get_string('exportto', 'grades'). ' '.
        get_string('pluginname', 'gradeexport_myucla'));

if (!empty($CFG->gradepublishing)) {
    $CFG->gradepublishing = has_capability('gradeexport/myucla:publish', $context);
}

// START UCLA MODIFICATION
// Jeffrey Su - CCLE-1199
$mform = new grade_export_form_myucla(null, array('publishing' => true));
// END UCLA MODIFICATION

// process post information
if ($data = $mform->get_data()) {
    $export = new grade_export_myucla($course, groups_get_course_group($course), '', false, false, $data->display, $data->decimals);

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
$url = '/export/myucla/index.php?id=' . $course->id;
add_to_log($course->id, 'grade', 'view myucla', $url);
// END UCLA MOD: CCLE-3980