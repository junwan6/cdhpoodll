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
 * English strings for UCLA syllabus plugin
 *
 * @package    local_ucla_syllabus
 * @subpackage lang
 * @subpackage en
 * @copyright  2012 UC Regents
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginadministration'] = 'UCLA syllabus administration';
$string['pluginname'] = 'UCLA syllabus';

// Strings for uploading syllabus form.
$string['syllabus_manager'] = 'Syllabus manager';
$string['syllabus_choice'] = 'If you select both a file and URL, the URL will be displayed instead.';
$string['syllabus_url_file'] = 'Please provide a:';

$string['public_syllabus'] = 'Syllabus';
$string['public_syllabus_help'] = 'A syllabus can be available to the UCLA community (login required) or the general public (no login required).';
$string['private_syllabus'] = 'Restricted syllabus';
$string['private_syllabus_help'] = 'A restricted syllabus is viewable only by enrolled students in the course.';

$string['url'] = 'URL';
$string['file'] = 'File';
$string['upload_file'] = 'Please upload a PDF';
$string['access'] = 'Access';
$string['accesss_public_info'] = 'General public (no login required)';
$string['accesss_loggedin_info'] = 'UCLA community (login required)';
$string['access_none_selected'] = 'Please select an access type';
$string['access_invalid'] = 'Invalid access type selected';
$string['preview_info'] = 'This is not a complete version of the syllabus.';
$string['display_name'] = 'Display name';
$string['display_name_default'] = 'Syllabus';
$string['display_name_none_entered'] = 'Please enter a display name';
$string['cannnot_make_db_entry'] = 'Cannot insert entry into syllabus table';
$string['invalid_public_syllabus'] = 'Can only have one unrestricted syllabus for the course';
$string['public_syllabus_add'] = 'Add syllabus';
$string['private_syllabus_add'] = 'Add restricted syllabus';
$string['no_syllabus'] = 'No syllabus uploaded yet';
$string['make_private'] = 'Restrict';
$string['make_public'] = 'Unrestrict';
$string['confirm_deletion'] = 'Are you sure you want to delete this syllabus?';

// Strings for displaying syllabus.
$string['cannot_view_private_syllabus'] = 'This syllabus is available only to enrolled students in the course.';
$string['cannot_view_public_syllabus'] = 'This syllabus is available only to logged in users.';
$string['no_syllabus_uploaded'] = 'Syllabus is not available yet.';
$string['no_syllabus_uploaded_help'] = 'Please "Turn editing on" to upload a syllabus.';
$string['clicktodownload'] = 'Download: {$a}';
$string['syllabus_needs_setup'] = 'Syllabus (empty)';
$string['public_disclaimer'] = '';
$string['preview_disclaimer'] = 'May not reflect the complete contents of the final syllabus for this course.';
$string['private_disclaimer'] = 'This syllabus is available only to enrolled students in the course.';
$string['preview'] = 'preview';
$string['private'] = 'restricted';
$string['modified'] = 'Last modified: ';

// Success strings.
$string['successful_add'] = 'Successfully added syllabus';
$string['successful_delete'] = 'Successfully deleted syllabus';
$string['successful_update'] = 'Successfully updated syllabus';
$string['successful_restrict'] = 'Successfully restricted syllabus';
$string['successful_unrestrict'] = 'Successfully unrestricted syllabus';

// Error strings.
$string['err_file_not_uploaded'] = 'Please upload a PDF.';
$string['err_file_url_not_uploaded'] = 'Please upload a file or add a valid URL for your syllabus.';
$string['err_missing_courseid'] = 'Missing required courseid';
$string['err_syllabus_mismatch'] = 'Selected syllabus does not belong to course';
$string['err_syllabus_not_allowed'] = 'Sorry, you must be logged in or associated with the course to view this syllabus';
$string['err_syllabus_notexist'] = 'Sorry, but given syllabus does not exist';
$string['err_noembed'] = 'Unable to show embedded file. Please download file to view.';
$string['err_syllabus_convert'] = 'Cannot convert syllabus when both a restricted and unrestricted syllabi are uploaded';
$string['err_invalid_url'] = 'Please enter a valid URL.';
$string['err_cannot_manage'] = 'Sorry, but you do not have the capability to manage syllabi for course';

// Capability strings.
$string['ucla_syllabus:managesyllabus'] = 'Ability to add, edit, and delete syllabus for a course';
$string['ws_header'] = 'Add web service subscription';
$string['subject_area'] = 'Subject area';
$string['subject_area_help'] = 'Subject area to monitor';
$string['leading_srs'] = 'Leading SRS';
$string['leading_srs_rule'] = 'Numeric values only';
$string['post_url'] = 'POST URL';
$string['post_url_required'] = 'You must provide a POST url.';
$string['contact_email'] = 'Contact email';
$string['contact_email_help'] = 'This email will be used to report any problems
    encountered while attempting to access the provided url.';
$string['contact_email_required'] = 'You must provide a valid contact email.';
$string['token'] = 'Token';
$string['token_help'] = 'Use a token to verify the authenticity of the
    messages you receive on the POST url.';
$string['select_action'] = 'Service action';
$string['action_alert'] = 'Course alert';
$string['action_transfer'] = 'Syllabus transfer';

$string['heading'] = 'UCLA syllabus web service';
$string['status'] = 'Status';
$string['enable'] = 'Enable';
$string['disable'] = 'Disable';
$string['delete'] = 'Delete';

$string['email_subject'] = 'UCLA|CCLE web service error';
$string['email_msg'] = 'The subscribed URL did not respond, or returned the wrong response.

Make sure that your service is working.  The service will attempt to resend the message in 5 minutes.

data:
    SRS: {$a->srs}
    TERM: {$a->term}
    URL: {$a->service}
';

// Strings for alert notice.
$string['alert_msg'] = 'A syllabus has not been added to your site, would you like to add one now?';
$string['alert_yes'] = 'Yes';
$string['alert_no'] = 'No, don\'t ask again';
$string['alert_later'] = 'Ask me later';
$string['alert_no_redirect'] = 'You will no longer be prompted to add a syllabus. ' .
        'Use the Syllabus link in the Site menu block at the left or the tool in ' .
        'the Control Panel to add one later.';
$string['alert_later_redirect'] = 'Syllabus reminder set.';
$string['alert_msg_manual'] = 'We found a {$a->type} that might be a syllabus ' .
        'called "{$a->name}". Would you like to make this your official syllabus?';

// Strings for syllabus backup.
$string['backup_notice'] = 'Files uploaded via the syllabus tool will not
be included in your backup.  You will need to re-upload those files manually.';

// Strings for syllabus icons.
$string['icon_public_world_syllabus'] = 'Syllabus (no login required)';
$string['icon_public_ucla_syllabus'] = 'Syllabus (login required)';
$string['icon_private_syllabus'] = 'Restricted syllabus (only enrolled students)';

// Strings for handling manually uploaded syllabi.
$string['manualconvertsyllabus'] = 'Please select the type of syllabus to convert your existing "{$a->name}" {$a->modname}.';
$string['manualpublicsyllabusadd'] = 'Add "{$a->name}" as a syllabus';
$string['manualprivatesyllabusadd'] = 'Add "{$a->name}" as a restricted syllabus';
$string['manualconvertsyllabusreminder'] = 'Please complete the form below to ' .
        'convert your existing "{$a->name}" {$a->modname} into your official UCLA syllabus.';
$string['errmanualsyllabusmismatch'] = 'Cannot convert manual syllabus, because it does not belong to current course.';
$string['manualsuccessfulconversion'] = 'Successfully converted manual syllabus.';