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
 * Viewing invitation history script.
 *
 * @package    enrol
 * @subpackage invitation
 * @copyright  2012 Rex Lorenzo <rex@oid.ucla.edu>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/invitation_form.php');

require_once($CFG->dirroot . '/enrol/locallib.php');
require_once($CFG->libdir . '/tablelib.php');

// for distance_of_time_in_words
require_once($CFG->dirroot . '/local/ucla/datetimehelpers.php');

require_login();
$courseid = required_param('courseid', PARAM_INT);
$inviteid = optional_param('inviteid', 0, PARAM_INT);
$actionid = optional_param('actionid', 0, PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$context = context_course::instance($courseid);
if (!has_capability('enrol/invitation:enrol', $context)) {
    $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));    
    throw new moodle_exception('nopermissiontosendinvitation' , 
            'enrol_invitation', $courseurl);
}

// set up page
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/enrol/invitation/history.php', 
        array('courseid' => $courseid)));
$PAGE->set_pagelayout('course');
$PAGE->set_course($course);
$page_title = get_string('invitehistory', 'enrol_invitation');
$PAGE->set_heading($page_title);
$PAGE->set_title($page_title);
$PAGE->navbar->add($page_title);

// Do not display the page if we are going to be redirecting the user
if ($actionid != invitation_manager::INVITE_RESEND) {
    // OUTPUT form
    echo $OUTPUT->header();

    // Print out a heading
    echo $OUTPUT->heading($page_title, 2, 'headingblock');
        
    // OUTPUT page tabs
    print_page_tabs('history');
}

$invitationmanager = new invitation_manager($courseid);
// course must have invitation plugin installed (will give error if not found)
$invite_instance = $invitationmanager->get_invitation_instance($courseid, true);

// get invites and display them
$invites = $invitationmanager->get_invites();

if (empty($invites)) {
    echo $OUTPUT->notification(
            get_string('noinvitehistory', 'enrol_invitation'), 'notifymessage');
} else {
    
    // BEGIN UCLA MOD: CCLE-2960-Viewing-history-of-invites-and-status
    // Update invitation if the user decided to revoke/extend/resend an invite
    if ($inviteid && $actionid) {
        if (!$curr_invite = $invites[$inviteid]) {
            print_error('invalidinviteid');
        }
        if ($actionid == invitation_manager::INVITE_REVOKE) {
            // Set the invite to be expired
            $DB->set_field('enrol_invitation', 'timeexpiration', time()-1, 
                    array('courseid' => $curr_invite->courseid, 'id' => $curr_invite->id) );
            
            add_to_log($course->id, 'course', 'invitation revoke', 
                            "../enrol/invitation/history.php?courseid=$course->id", $course->fullname);
            
            echo $OUTPUT->box_start('noticebox');
            echo html_writer::tag('span', get_string('revoke_invite_sucess', 'enrol_invitation'));
            echo $OUTPUT->box_end();
            
        } else if ($actionid == invitation_manager::INVITE_EXTEND) {
            // Resend the invite and email
            $invitationmanager->send_invitations($curr_invite, true);

            echo $OUTPUT->box_start('noticebox');
            echo html_writer::tag('span', get_string('extend_invite_sucess', 'enrol_invitation'));
            echo $OUTPUT->box_end();
            
        } else if ($actionid == invitation_manager::INVITE_RESEND) {
            // Send the user to the invite form with prefilled data
            $redirect = new moodle_url('/enrol/invitation/invitation.php', 
                    array('courseid' => $curr_invite->courseid, 'inviteid' => $curr_invite->id));
            redirect($redirect);
            
        } else {
            print_error('invalidactionid');
        }
        
        // Get the updated invites
        $invites = $invitationmanager->get_invites();
    }
    // END UCLA MOD: CCLE-2960
    
    // columns to display
    $columns = array(
            'invitee'           => get_string('historyinvitee', 'enrol_invitation'),
            'role'              => get_string('historyrole', 'enrol_invitation'),
            'status'            => get_string('historystatus', 'enrol_invitation'),
            'datesent'          => get_string('historydatesent', 'enrol_invitation'),
            'dateexpiration'    => get_string('historydateexpiration', 'enrol_invitation'),
            'actions'           => get_string('historyactions', 'enrol_invitation')
    );
    
    $table = new flexible_table('invitehistory');
    $table->define_columns(array_keys($columns));
    $table->define_headers(array_values($columns));
    $table->define_baseurl($PAGE->url);
    $table->set_attribute('class', 'generaltable');

    $table->setup();
    
    $role_cache = array();
    foreach ($invites as $invite) {
        /* build display row
         * [0] - invitee
         * [1] - role
         * [2] - status
         * [3] - dates sent
         * [4] - expiration date
         * [5] - actions (@todo)
         */
        
        // display invitee
        $row[0] = $invite->email;
        
        // figure out invited role
        if (empty($role_cache[$invite->roleid])) {
            $role = $DB->get_record('role', array('id' => $invite->roleid));
            if (empty($role)) {
                // cannot find role, give error
                $role_cache[$invite->roleid] = 
                        get_string('historyundefinedrole', 'enrol_invitation');
            } else {
                $role_cache[$invite->roleid] = $role->name;                
            }
        }
        $row[1] = $role_cache[$invite->roleid];
        
        // what is the status of the invite?
        $status = $invitationmanager->get_invite_status($invite);
        $row[2] = $status;
        
        // if status was used, figure out who used the invite
        $result = $invitationmanager->who_used_invite($invite);
        if (!empty($result)) {
            $row[2] .= get_string('used_by', 'enrol_invitation', $result);                
        }

        // If user's enrollment expired or will expire, let viewer know.
        $result = $invitationmanager->get_access_expiration($invite);
        if (!empty($result)) {
            $row[2] .= ' ' . $result;
        }

        // when was the invite sent?
        $row[3] = date('M j, Y g:ia', $invite->timesent);
        
        // when does the invite expire?
        $row[4] = date('M j, Y g:ia', $invite->timeexpiration);
        
        // if status is active, then state how many days/minutes left
        if ($status == get_string('status_invite_active', 'enrol_invitation')) {
            $expires_text = sprintf('%s %s', 
                    get_string('historyexpires_in', 'enrol_invitation'),
                    distance_of_time_in_words(time(), $invite->timeexpiration, true));   
            $row[4] .= ' ' . html_writer::tag('span', '(' . $expires_text . ')', array('expires-text'));
        }
        
        // BEGIN UCLA MOD: CCLE-2960-Viewing-history-of-invites-and-status
        // are there any actions user can do?
        $row[5] = '';
        $url = new moodle_url('/enrol/invitation/history.php', 
                array('courseid' => $courseid, 'inviteid' => $invite->id));
        // Same if statement as above, seperated for clarity
        if ($status == get_string('status_invite_active', 'enrol_invitation')) {
            // Create link to revoke an invite
            $url->param('actionid', invitation_manager::INVITE_REVOKE);
            $row[5] .= html_writer::link($url, get_string('action_revoke_invite', 'enrol_invitation'));
            $row[5] .= html_writer::start_tag('br');
            // Create link to extend an invite
            $url->param('actionid', invitation_manager::INVITE_EXTEND);
            $row[5] .= html_writer::link($url, get_string('action_extend_invite', 'enrol_invitation'));
        } else if ($status == get_string('status_invite_expired', 'enrol_invitation')) {
            // Create link to resend invite
            $url->param('actionid', invitation_manager::INVITE_RESEND);
            $row[5] .= html_writer::link($url, get_string('action_resend_invite', 'enrol_invitation'));
        }
        // END UCLA MOD: CCLE-2960
        
        $table->add_data($row);
    }
    
    $table->finish_output();
}

echo $OUTPUT->footer();