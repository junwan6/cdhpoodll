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

/**
 * Unenrol a user who was enrolled through a invitation enrolment.
 *
 * Please note when unenrolling a user all of their grades are removed as well.
 *
 * @package    enrol
 * @subpackage invitation
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/enrol/locallib.php");
require_once("$CFG->dirroot/enrol/renderer.php");

$ueid   = required_param('ue', PARAM_INT); // user enrolment id
$filter = optional_param('ifilter', 0, PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);

// Get the user enrolment object
$ue     = $DB->get_record('user_enrolments', array('id' => $ueid), '*', MUST_EXIST);
// Get the user for whom the enrolment is
$user   = $DB->get_record('user', array('id'=>$ue->userid), '*', MUST_EXIST);
// Get the course the enrolment is to
list($ctxsql, $ctxjoin) = context_instance_preload_sql('c.id', CONTEXT_COURSE, 'ctx');
$sql = "SELECT c.* $ctxsql
          FROM {course} c
     LEFT JOIN {enrol} e ON e.courseid = c.id
               $ctxjoin
         WHERE e.id = :enrolid";
$params = array('enrolid' => $ue->enrolid);
$course = $DB->get_record_sql($sql, $params, MUST_EXIST);
context_instance_preload($course);

if ($course->id == SITEID) {
    redirect(new moodle_url('/'));
}

require_login($course);
require_capability("enrol/invitation:unenrol", context_course::instance($course->id, MUST_EXIST));

$manager = new course_enrolment_manager($PAGE, $course, $filter);
$table = new course_enrolment_users_table($manager, $PAGE);

// The URL of the enrolled users page for the course.
$usersurl = new moodle_url('/enrol/users.php', array('id' => $course->id));
// The URl to return the user too after this screen.
$returnurl = new moodle_url($usersurl, $manager->get_url_params()+$table->get_url_params());
// The URL of this page
$url = new moodle_url('/enrol/invitation/unenroluser.php', $returnurl->params());
$url->param('ue', $ueid);

$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
navigation_node::override_active_url($usersurl);

list($instance, $plugin) = $manager->get_user_enrolment_components($ue);

if (!$plugin->allow_unenrol($instance) || $instance->enrol != 'invitation' || !($plugin instanceof enrol_invitation_plugin)) {
    print_error('erroreditenrolment', 'enrol');
}

// If the unenrolment has been confirmed and the sesskey is valid unenrol the user.
if ($confirm && confirm_sesskey() && $manager->unenrol_user($ue)) {
    redirect($returnurl);
}

$yesurl = new moodle_url($PAGE->url, array('confirm'=>1, 'sesskey'=>sesskey()));
$message = get_string('unenroluser', 'enrol_invitation', array('user'=>fullname($user, true), 'course'=>format_string($course->fullname)));
$fullname = fullname($user);
$title = get_string('unenrol', 'enrol_invitation');

$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title);
$PAGE->navbar->add($fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($fullname);
echo $OUTPUT->confirm($message, $yesurl, $returnurl);
echo $OUTPUT->footer();