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


/**
 * Add/remove members from group.
 *
 * @copyright 2006 The Open University and others, N.D.Freear AT open.ac.uk, J.White AT open.ac.uk and others
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   core_group
 */
require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once($CFG->dirroot . '/user/selector/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/filelib.php');

$groupid = required_param('group', PARAM_INT);
$cancel  = optional_param('cancel', false, PARAM_BOOL);

$group = $DB->get_record('groups', array('id'=>$groupid), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$group->courseid), '*', MUST_EXIST);

$PAGE->set_url('/group/members.php', array('group'=>$groupid));
$PAGE->set_pagelayout('admin');

require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/course:managegroups', $context);

$returnurl = $CFG->wwwroot.'/group/index.php?id='.$course->id.'&group='.$group->id;

if ($cancel) {
    redirect($returnurl);
}

$groupmembersselector = new group_members_selector('removeselect', array('groupid' => $groupid, 'courseid' => $course->id));
$potentialmembersselector = new group_non_members_selector('addselect', array('groupid' => $groupid, 'courseid' => $course->id));

/**
 * Require that group assignments are not made on the public/private group.
 *
 * @author ebollens
 * @version 20110719
 */

require_once($CFG->dirroot.'/local/publicprivate/lib/course.class.php');
$publicprivate_course = new PublicPrivate_Course($course);

if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey() && !$publicprivate_course->is_group($group)) {
    $userstoadd = $potentialmembersselector->get_selected_users();
    if (!empty($userstoadd)) {
        foreach ($userstoadd as $user) {
            if (!groups_add_member($groupid, $user->id)) {
                print_error('erroraddremoveuser', 'group', $returnurl);
            }
            $groupmembersselector->invalidate_selected_users();
            $potentialmembersselector->invalidate_selected_users();
        }
    }
}

if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey() && !$publicprivate_course->is_group($group)) {
    $userstoremove = $groupmembersselector->get_selected_users();
    if (!empty($userstoremove)) {
        foreach ($userstoremove as $user) {
            if (!groups_remove_member_allowed($groupid, $user->id)) {
                print_error('errorremovenotpermitted', 'group', $returnurl,
                        $user->fullname);
            }
            if (!groups_remove_member($groupid, $user->id)) {
                print_error('erroraddremoveuser', 'group', $returnurl);
            }
            $groupmembersselector->invalidate_selected_users();
            $potentialmembersselector->invalidate_selected_users();
        }
    }
}

// Print the page and form
$strgroups = get_string('groups');
$strparticipants = get_string('participants');
$stradduserstogroup = get_string('adduserstogroup', 'group');
$strusergroupmembership = get_string('usergroupmembership', 'group');

$groupname = format_string($group->name);

$PAGE->requires->js('/group/clientlib.js');
$PAGE->navbar->add($strparticipants, new moodle_url('/user/index.php', array('id'=>$course->id)));
$PAGE->navbar->add($strgroups, new moodle_url('/group/index.php', array('id'=>$course->id)));
$PAGE->navbar->add($stradduserstogroup);

/// Print header
$PAGE->set_title("$course->shortname: $strgroups");
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('adduserstogroup', 'group').": $groupname", 3);

/**
 * Alert that public/private group cannot be edited.
 *
 * @author ebollens
 * @version 20110719
 */
if($publicprivate_course->is_group($group)) {
    echo $OUTPUT->notification(get_string('publicprivatecannotremove','local_publicprivate'));
    echo $OUTPUT->continue_button('index.php?id='.$course->id);
    echo $OUTPUT->footer();
    die;
}

/// Print group info -  TODO: remove tables for layout here
$groupinfotable = new html_table();
$groupinfotable->attributes['class'] = 'groupinfobox';
$picturecell = new html_table_cell();
$picturecell->attributes['class'] = 'left side picture';
$picturecell->text = print_group_picture($group, $course->id, true, true, false);

$contentcell = new html_table_cell();
$contentcell->attributes['class'] = 'content';

$group->description = file_rewrite_pluginfile_urls($group->description, 'pluginfile.php', $context->id, 'group', 'description', $group->id);
if (!isset($group->descriptionformat)) {
    $group->descriptionformat = FORMAT_MOODLE;
}
$options = new stdClass;
$options->overflowdiv = true;
$contentcell->text = format_text($group->description, $group->descriptionformat, $options);
$groupinfotable->data[] = new html_table_row(array($picturecell, $contentcell));
echo html_writer::table($groupinfotable);

/// Print the editing form
?>

<div id="addmembersform">
    <form id="assignform" method="post" action="<?php echo $CFG->wwwroot; ?>/group/members.php?group=<?php echo $groupid; ?>">
    <div>
    <input type="hidden" name="sesskey" value="<?php p(sesskey()); ?>" />

    <table class="generaltable generalbox groupmanagementtable boxaligncenter" summary="">
    <tr>
      <td id='existingcell'>
          <p>
            <label for="removeselect"><?php print_string('groupmembers', 'group'); ?></label>
          </p>
          <?php $groupmembersselector->display(); ?>
          </td>
      <td id='buttonscell'>
        <p class="arrow_button">
            <input name="add" id="add" type="submit" value="<?php echo $OUTPUT->larrow().'&nbsp;'.get_string('add'); ?>" title="<?php print_string('add'); ?>" /><br />
            <input name="remove" id="remove" type="submit" value="<?php echo get_string('remove').'&nbsp;'.$OUTPUT->rarrow(); ?>" title="<?php print_string('remove'); ?>" />
        </p>
      </td>
      <td id='potentialcell'>
          <p>
            <label for="addselect"><?php print_string('potentialmembs', 'group'); ?></label>
          </p>
          <?php $potentialmembersselector->display(); ?>
      </td>
      <td>
        <p><?php echo($strusergroupmembership) ?></p>
        <div id="group-usersummary"></div>
      </td>
    </tr>
    <tr><td colspan="3" id='backcell'>
        <input type="submit" name="cancel" value="<?php print_string('backtogroups', 'group'); ?>" />
    </td></tr>
    </table>
    </div>
    </form>
</div>

<?php
    //outputs the JS array used to display the other groups users are in
    $potentialmembersselector->print_user_summaries($course->id);

    //this must be after calling display() on the selectors so their setup JS executes first
    $PAGE->requires->js_init_call('init_add_remove_members_page', null, false, $potentialmembersselector->get_js_module());

    echo $OUTPUT->footer();
