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
 *  This configuration file has been preconfigured to set certain variables
 *  such that launching and upgrades will run as smoothly as possible.
 *
 *  Currently, the plan is to symbolically link this file as such:
 *  moodle/config.php -> moodle/local/ucla/config/<this file>
 *
 *  The original configuration file should not be used, as it does not have
 *  any capability of saying that another configuration file can be 
 *  included before starting the Moodle session.
 *
 *  If you want configurations to be not within revisioning, then place
 *  your secrets @ moodle/config_private.php.
 *
 **/

unset($CFG);
global $CFG;

$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = '';
$CFG->dbuser    = '';
$CFG->dbpass    = '';
$CFG->prefix    = 'mdl_';
$CFG->dboptions['dbpersist'] = 0;
$CFG->dboptions['dbsocket']  = 0;

$CFG->wwwroot  = 'https://ccle.ucla.edu';
$CFG->dataroot = '/moodle/moodle_data'; 

// This determines what the admin folder is called.
$CFG->admin    = 'admin';

$CFG->directorypermissions = 0777;

// This should never change after the first install, or else any special
// logins using the Moodle login will not work.
$CFG->passwordsaltmain = '';

// Registrar
$CFG->registrar_dbtype = 'odbc_mssql';
$CFG->registrar_dbhost = '';
$CFG->registrar_dbuser = '';
$CFG->registrar_dbpass = '';
$CFG->registrar_dbname = 'srdb';
$CFG->registrar_dbencoding = 'ISO-8859-1';

// Format and browseby and anything else that requires instructors to be 
// displayed, we need to determine which roles should be displayed.
$CFG->instructor_levels_roles['Instructor'] = array('editinginstructor', 'ta_instructor');
$CFG->instructor_levels_roles['Teaching Assistant'] = array('ta', 'ta_admin');
$CFG->instructor_levels_roles['Student Facilitator'] = array('studentfacilitator');

// CCLE-2283: Friendly URLs
// CCLE-2283: Redirect to archive 
$CFG->forced_plugin_settings['local_ucla']['friendly_urls_enabled'] = true;
$CFG->forced_plugin_settings['local_ucla']['remotetermcutoff'] = '12S';
$CFG->forced_plugin_settings['local_ucla']['archiveserver'] = 'https://archive.ccle.ucla.edu';

// My Sites CCLE-2810
// Term limiting
$CFG->forced_plugin_settings['local_ucla']['student_access_ends_week'] = 3;
$CFG->forced_plugin_settings['local_ucla']['oldest_available_term'] = '08S';

// Browseby CCLE-2894
$CFG->forced_plugin_settings['block_ucla_browseby']['use_local_courses'] = 0;
$CFG->forced_plugin_settings['block_ucla_browseby']['ignore_coursenum'] = '194,295,296,375';
$CFG->forced_plugin_settings['block_ucla_browseby']['allow_acttypes'] = 'LEC,SEM';

// Course builder \\

// Course Requestor
$CFG->forced_plugin_settings['tool_uclacourserequestor']['mailinst_default'] = true; 
$CFG->forced_plugin_settings['tool_uclacourserequestor']['nourlupdate_default'] = false;
$CFG->forced_plugin_settings['tool_uclacourserequestor']['nourlupdate_hide'] = true;

// Course Creator
$CFG->forced_plugin_settings['tool_uclacoursecreator']['course_creator_email'] = 'ccle-operations@lists.ucla.edu';
$CFG->forced_plugin_settings['tool_uclacoursecreator']['email_template_dir'] = '/usr/local/moodle/config/ccle_email_templates/course_creator';
$CFG->forced_plugin_settings['tool_uclacoursecreator']['make_division_categories'] = true;

// MyUCLA url updater
$CFG->forced_plugin_settings['tool_myucla_url']['url_service'] = 'http://cis.ucla.edu/ieiWebMap/update.asp';
$CFG->forced_plugin_settings['tool_myucla_url']['user_name'] = 'CCLE Admin';
$CFG->forced_plugin_settings['tool_myucla_url']['user_email'] = 'ccle@ucla.edu';

// turn off messaging (CCLE-2318 - MESSAGING)
$CFG->messaging = false;

// CCLE-2590 - Implement Auto-detect Shibboleth Login
$CFG->shib_logged_in_cookie = '_ucla_sso';

// CCLE-2306 - HELP SYSTEM BLOCK
// if using JIRA, jira_user, jira_password, jira_pid should be defined in config_private.php
$CFG->forced_plugin_settings['block_ucla_help']['send_to'] = 'jira';
$CFG->forced_plugin_settings['block_ucla_help']['jira_endpoint'] = 'https://jira.ats.ucla.edu/CreateIssueDetails.jspa';
$CFG->forced_plugin_settings['block_ucla_help']['jira_default_assignee'] = 'dkearney';
//$block_ucla_help_support_contacts['System'] = 'dkearney';  // default

// CCLE-2301 - COURSE MENU BLOCK
$CFG->forced_plugin_settings['block_ucla_course_menu']['trimlength'] = 22;

// UCLA Theme settings
$CFG->forced_plugin_settings['theme_uclashared']['running_environment'] = 'prod';
$CFG->forced_plugin_settings['theme_uclashared']['footer_links'] = '';

// Newly created courses for ucla formats should only have the course menu block
$CFG->defaultblocks_ucla = 'ucla_course_menu';

// Enable conditional activities
$CFG->enableavailability = true;
$CFG->enablecompletion = true;  // needs to be enabled so that completion
                                // of tasks can be one of the conditions

// CCLE-2229 - Force public/private to be on
$CFG->enablegroupmembersonly = true; // needs to be on for public-private to work
$CFG->enablepublicprivate = true;

// CCLE-2792 - Enable multimedia filters
// NOTE: you still need to manually set the "Active?" value of the "Multimedia 
// plugins" filter at "Site administration > Plugins > Filters > Manage filters"
$CFG->filter_mediaplugin_enable_youtube = true;
$CFG->filter_mediaplugin_enable_vimeo = true;
$CFG->filter_mediaplugin_enable_mp3 = true;
$CFG->filter_mediaplugin_enable_flv = true;
$CFG->filter_mediaplugin_enable_swf = false;    // security risk if enabled
$CFG->filter_mediaplugin_enable_html5audio = true;
$CFG->filter_mediaplugin_enable_html5video = true;
$CFG->filter_mediaplugin_enable_qt = true;
$CFG->filter_mediaplugin_enable_wmp = true;
$CFG->filter_mediaplugin_enable_rm = true;

// CCLE-2362 - MyUCLA Gradebook Integration
$CFG->gradebook_webservice = 'https://cis.ucla.edu/ws/moodleitemgrade/service.svc?wsdl';
$CFG->gradebook_id = 3;
$CFG->gradebook_password = '123';
$CFG->gradebook_send_updates = 1;

/// CCLE-2810 - My Sites - disallow customized "My Moodle" page
$CFG->forcedefaultmymoodle = true;

// email address to notify in case of system problems
$CFG->forced_plugin_settings['local_ucla']['admin_email'] = 'ccle-operations@lists.ccle.ucla.edu';

// CCLE-3966 - Include self when messaging participants.
// Emails should still be sent to users that are logged in.
$CFG->forced_plugin_settings['message']['message_provider_moodle_instantmessage_loggedin'] = 'popup,email';

// Site administration > Advanced features
$CFG->usetags = 0;
$CFG->enablenotes = 0;
$CFG->bloglevel = 0; // Disable blog system completely
$CFG->enablebadges = 0; // Disable badges

// Site administration > Users > Permissions > User policies
$CFG->autologinguests = true;
$CFG->showuseridentity = 'idnumber,email';

// Site administration > Courses > Course default settings
$CFG->forced_plugin_settings['moodlecourse']['format'] = 'ucla';
$CFG->forced_plugin_settings['moodlecourse']['maxbytes'] = 2147483648;  // 2GB
// CCLE-2903 - Don't set completion tracking to be course default
$CFG->forced_plugin_settings['moodlecourse']['enablecompletion'] = 0;

// Site administration > Courses > Course request
$CFG->enablecourserequests = 1;

// Site administration > Courses > Backups > General backup defaults
// Commenting this out until following tracker issue is resolved:
// MDL-27886 - backup_general_users forbids all users to backup user data
//$CFG->forced_plugin_settings['backup']['backup_general_users'] = 0;

// Site administration > Grades > General settings
$CFG->recovergradesdefault = 1;

// Site administration > Grades > Grade category settings
$CFG->grade_overridecat = 0;

// Site administration > Plugins > Activity modules > Assignment
$CFG->assignment_maxbytes = 104857600;   // 100MB

// Site administration > Plugins > Activity modules > Book
$CFG->forced_plugin_settings['book']['requiremodintro'] = 0;

// Site administration > Plugins > Activity modules > Blackboard Collaborate Session
$CFG->elluminate_max_talkers = 2;

// Site administration > Plugins > Activity modules > Folder
$CFG->forced_plugin_settings['folder']['requiremodintro'] = 0;

// Site administration > Plugins > Activity modules > IMS content package
$CFG->forced_plugin_settings['imscp']['requiremodintro'] = 0;

// Site administration > Plugins > Activity modules > Page
$CFG->forced_plugin_settings['page']['requiremodintro'] = 0;
$CFG->forced_plugin_settings['page']['printheading'] = 1;

// Site administration > Plugins > Activity modules > File
$CFG->forced_plugin_settings['resource']['requiremodintro'] = 0;
$CFG->forced_plugin_settings['resource']['printheading'] = 1;
$CFG->forced_plugin_settings['resource']['display'] = 4;   // "Force Download"

// Site administration > Plugins > Activity modules > Turnitin Assignment
$CFG->turnitin_apiurl = 'https://api.turnitin.com/api.asp';
$CFG->turnitin_studentemail = 0;
$CFG->turnitin_tutoremail = 0;

// Site administration > Plugins > Activity modules > URL
$CFG->forced_plugin_settings['url']['requiremodintro'] = 0;
$CFG->forced_plugin_settings['url']['displayoptions'] = '0,1,2,3,4,5,6';    // allow every option
$CFG->forced_plugin_settings['url']['printheading'] = 1;
$CFG->forced_plugin_settings['url']['display'] = 3; // RESOURCELIB_DISPLAY_NEW

// Site administration > Plugins > Assignment plugins > Submission plugins > File submissions
$CFG->forced_plugin_settings['assignsubmission_file']['maxbytes'] = 104857600;   // 100MB

// Site administration > Plugins > Assignment plugins > Feedback plugins > Feedback PoodLL
$CFG->forced_plugin_settings['assignfeedback_poodll']['default'] = 0;

// Site administration > Plugins > Assignment plugins > Feedback plugins > Feedback comments
$CFG->forced_plugin_settings['assignfeedback_comments']['default'] = 1;

// Site administration > Plugins > Assignment plugins > Feedback plugins > File feedback
$CFG->forced_plugin_settings['assignfeedback_file']['default'] = 1;

// Site administration > Plugins > Enrollments > UCLA registrar
$CFG->forced_plugin_settings['local_ucla']['overrideenroldatabase'] = 1;

// Site administration > Plugins > Enrollments > Guest access
$CFG->forced_plugin_settings['enrol_guest']['defaultenrol'] = 1;
$CFG->forced_plugin_settings['enrol_guest']['status'] = 0;  // 0 is yes, 1 is no

// Site administration > Plugins > Enrollments > Site invitation
$CFG->forced_plugin_settings['enrol_invitation']['enabletempparticipant'] = 1;

// Site administration > Plugins > Enrollments > Self enrolment
$CFG->forced_plugin_settings['enrol_self']['defaultenrol'] = 0;
$CFG->forced_plugin_settings['enrol_self']['status'] = 1;  // 0 is yes, 1 is no
$CFG->forced_plugin_settings['enrol_self']['sendcoursewelcomemessage'] = 0;

// Site administration > Plugins > Blocks > UCLA bruincast
$CFG->forced_plugin_settings['block_ucla_bruincast']['source_url'] = 'http://www2.oid.ucla.edu/help/info/bcastlinks/';
$CFG->forced_plugin_settings['block_ucla_bruincast']['errornotify_email'] = 'ccle-operations@lists.ucla.edu';

// Site administration > Plugins > Blocks > UCLA library reserves
$CFG->forced_plugin_settings['block_ucla_library_reserves']['source_url'] = 'ftp://ftp.library.ucla.edu/incoming/eres/voyager_reserves_data.txt';

// Site administration > Plugins > Blocks > UCLA video furnace
$CFG->forced_plugin_settings['block_ucla_video_furnace']['source_url'] = 'ftp://guest:access270@164.67.141.31//Users/guest/Sites/VF_LINKS.txt';

// Data Source Sync (bruincast, video furnace, library reserves) contact Email
$CFG->forced_plugin_settings['tool_ucladatasourcesync']['contact_email']='ccle-operations@lists.ccle.ucla.edu';

// Site administration > Plugins > Blocks > i>clicker Moodle integrate
$CFG->forced_plugin_settings['block_iclicker']['block_iclicker_notify_emails'] = 'ccle-operations@lists.ucla.edu';
$CFG->block_iclicker_notify_emails = 'ccle-operations@lists.ucla.edu';  // due to bad coding, two variables exist to do the same thing
$CFG->forced_plugin_settings['block_iclicker']['block_iclicker_enable_shortname'] = 1;

// Site administration > Plugins > Licences > Manage licences
$CFG->sitedefaultlicense = 'tbd';

// Site administration > Plugins > Filters > PoodLL Filter
$CFG->filter_poodll_download_media_ok = '1';

// Site administration > Plugins > Repositories > Common repository settings
$CFG->legacyfilesinnewcourses = 1;  // enable new course to enable legacy course files

// Site administration > Plugins > Local plugins > Kaltura package libraries
$CFG->forced_plugin_settings['local_kaltura']['conn_server'] = 'ce';
$CFG->forced_plugin_settings['local_kaltura']['uri'] = 'https://www.kaltura.com';
$CFG->forced_plugin_settings['local_kaltura']['enable_reports'] = 1;
$CFG->forced_plugin_settings['local_kaltura']['player'] = 0;
$CFG->forced_plugin_settings['local_kaltura']['player_custom'] = 15205332;
$CFG->forced_plugin_settings['local_kaltura']['assign_uploader'] = 0;
$CFG->forced_plugin_settings['local_kaltura']['assign_uploader_custom'] = 15205342;
$CFG->forced_plugin_settings['local_kaltura']['player_resource'] = 0;
$CFG->forced_plugin_settings['local_kaltura']['player_resource_custom'] = 19816362;
$CFG->forced_plugin_settings['local_kaltura']['assign_uploader_custom'] = 15205332;
$CFG->forced_plugin_settings['local_kaltura']['player_resource_override'] = 1;
$CFG->forced_plugin_settings['local_kaltura']['res_uploader'] = 0;
$CFG->forced_plugin_settings['local_kaltura']['res_uploader_custom'] = 15205342;
$CFG->forced_plugin_settings['local_kaltura']['presentation'] = 0;
$CFG->forced_plugin_settings['local_kaltura']['presentation_custom'] = 15205352;
$CFG->forced_plugin_settings['local_kaltura']['pres_uploader'] = 0;
$CFG->forced_plugin_settings['local_kaltura']['pres_uploader_custom'] = 15205342;
$CFG->forced_plugin_settings['local_kaltura']['simple_uploader'] = 0;
$CFG->forced_plugin_settings['local_kaltura']['simple_uploader_custom'] = 15205362;
$CFG->forced_plugin_settings['local_kaltura']['mymedia_uploader'] = 0;
$CFG->forced_plugin_settings['local_kaltura']['mymedia_uploader_custom'] = 15205372;
$CFG->forced_plugin_settings['local_kaltura']['mymedia_screen_recorder'] = 0;
$CFG->forced_plugin_settings['local_kaltura']['mymedia_screen_recorder_custom'] = 15205382;
$CFG->forced_plugin_settings['local_kaltura']['player_filter'] = 0;
$CFG->forced_plugin_settings['local_kaltura']['player_filter_custom'] = 15205332;
$CFG->forced_plugin_settings['local_kaltura']['enable_html5'] = 1;
$CFG->forced_plugin_settings['local_kaltura']['mymedia_application_name'] = 'ccle-shared-prod';

// Site administration > Plugins > Local plugins > UCLA configurations
$CFG->forced_plugin_settings['local_ucla']['registrar_cache_ttl'] = 3600;   // 1 hour

// Site administration > Security > Site policies
$CFG->forceloginforprofiles = true; 
$CFG->forceloginforprofileimage = true; // temporary until "CCLE-2368 - PIX.PHP security fix" is done
$CFG->allowobjectembed = 1;
$CFG->maxeditingtime = 900; // 15 minutes
$CFG->fullnamedisplay = 'language'; // CCLE-2550 - Lastname, Firstname sorting
$CFG->cronclionly = true;

// Site administration > Security > HTTP security
$CFG->loginhttps = true;
$CFG->cookiesecure = true;
$CFG->allowframembedding = 1; // CCLE-3021 - enabled because some collab sites need to be embedded

// Site administration > Security > Anti-Virus
$CFG->runclamonupload = true;
$CFG->pathtoclam = '/usr/bin/clamscan';
$CFG->clamscan = '/usr/bin/clamscan';
$CFG->quarantinedir = '/usr/local/clamquarantine';
$CFG->clamfailureonupload = 'donothing';

// Site administration > Appearance > Themes
$CFG->theme = 'uclashared';

// Site administration > Appearance > Themes > Theme settings
$CFG->allowcoursethemes = 1;

// Site administration > Appearance > Navigation
$CFG->defaulthomepage = 1;    // user's home page should be "My Moodle" (HOMEPAGE_MY)
$CFG->navshowcategories = 0;

// Site administration > Appearance > Courses
$CFG->courselistshortnames = 1;

// Site administration > Server > System paths
$CFG->pathtodu = '/usr/bin/du';
$CFG->aspellpath = '/usr/bin/aspell';

// Site administration > Server > Session handling
$CFG->dbsessions = false;

// Site administration > Server > Performance
$CFG->extramemorylimit = '1024M';

// Site administration > Server > Update notifications
$CFG->updateautocheck = 0;

// Site administration > Development > Experimental > Experimental settings
$CFG->dndallowtextandlinks = 1;

// If you want to have un-revisioned configuration data, place in config_private
// $CFG->dirroot is overwritten later
$_dirroot_ = dirname(realpath(__FILE__)) . '/../../..';
$_config_private_ = $_dirroot_ . '/config_private.php';
if (file_exists($_config_private_)) {
    require_once($_config_private_);
}

/** 
 *  Automatic Shibboleth configurations.
 *  Disabling in favor for GUI configurations.
 *  Keeping in code for sake of quick re-enabling and reference.
 *  To re-enable, add a '/' at the end of the following line.
 **/
//$CFG->auth = 'shibboleth';
//$CFG->alternateloginurl = $CFG->wwwroot . '/login/ucla_login.php?shibboleth';

//$CFG->forced_plugin_settings['auth/shibboleth']['user_attribute'] = 'HTTP_SHIB_EDUPERSON_PRINCIPALNAME';
//$CFG->forced_plugin_settings['auth/shibboleth']['convert_data'] = $_dirroot_ . '/shib_transform.php';
//$CFG->forced_plugin_settings['auth/shibboleth']['logout_handler'] = $CFG->wwwroot . '/Shibboleth.sso/Logout';
//$CFG->forced_plugin_settings['auth/shibboleth']['logout_return_url'] = 'https://shb.ais.ucla.edu/shibboleth-idp/Logout';
//$CFG->forced_plugin_settings['auth/shibboleth']['login_name'] = 'Shibboleth Login';

//$CFG->forced_plugin_settings['auth/shibboleth']['field_map_firstname'] = 'HTTP_SHIB_GIVENNAME';
//$CFG->forced_plugin_settings['auth/shibboleth']['field_updatelocal_firstname'] = 'onlogin';
//$CFG->forced_plugin_settings['auth/shibboleth']['field_lock_firstname'] = 'locked';

//$CFG->forced_plugin_settings['auth/shibboleth']['field_map_lastname'] = 'HTTP_SHIB_PERSON_SURNAME';
//$CFG->forced_plugin_settings['auth/shibboleth']['field_updatelocal_lastname'] = 'onlogin';
//$CFG->forced_plugin_settings['auth/shibboleth']['field_lock_lastname'] = 'locked';

//$CFG->forced_plugin_settings['auth/shibboleth']['field_map_email'] = 'HTTP_SHIB_MAIL';
//$CFG->forced_plugin_settings['auth/shibboleth']['field_updatelocal_mail'] = 'onlogin';
//$CFG->forced_plugin_settings['auth/shibboleth']['field_lock_email'] = 'unlockedifempty';

//$CFG->forced_plugin_settings['auth/shibboleth']['field_map_idnumber'] = 'HTTP_SHIB_UID';
//$CFG->forced_plugin_settings['auth/shibboleth']['field_updatelocal_idnumber'] = 'onlogin';
//$CFG->forced_plugin_settings['auth/shibboleth']['field_lock_idnumber'] = 'locked';

//$CFG->forced_plugin_settings['auth/shibboleth']['field_map_institution'] = 'HTTP_SHIB_IDENTITY_PROVIDER';
//$CFG->forced_plugin_settings['auth/shibboleth']['field_updatelocal_institution'] = 'onlogin';
//$CFG->forced_plugin_settings['auth/shibboleth']['field_lock_institution'] = 'locked';
/**
 *  End shibboleth configurations.
 **/

// set external database connection settings after config_private.php has
// been read for the Registrar connection details
$CFG->forced_plugin_settings['enrol_database']['dbtype'] = $CFG->registrar_dbtype;
$CFG->forced_plugin_settings['enrol_database']['dbhost'] = $CFG->registrar_dbhost;
$CFG->forced_plugin_settings['enrol_database']['dbuser'] = $CFG->registrar_dbuser;
$CFG->forced_plugin_settings['enrol_database']['dbpass'] = $CFG->registrar_dbpass;
$CFG->forced_plugin_settings['enrol_database']['dbname'] = '';
$CFG->forced_plugin_settings['enrol_database']['remoteenroltable'] = 'enroll2_test';
$CFG->forced_plugin_settings['enrol_database']['remotecoursefield'] = 'termsrs';
$CFG->forced_plugin_settings['enrol_database']['remoteuserfield'] = 'uid';
$CFG->forced_plugin_settings['enrol_database']['remoterolefield'] = 'role';
$CFG->forced_plugin_settings['enrol_database']['localcoursefield'] = 'id';
$CFG->forced_plugin_settings['enrol_database']['localrolefield'] = 'id';
$CFG->forced_plugin_settings['enrol_database']['localuserfield'] = 'idnumber';
$CFG->forced_plugin_settings['enrol_database']['unenrolaction'] = 0;    // ENROL_EXT_REMOVED_UNENROL

// CCLE-2802 - Frontpage banner layout include
$CFG->customfrontpageinclude = $_dirroot_ . '/theme/uclashared/layout/frontpage.php';

// CCLE-2364 - SUPPORT CONSOLE (put after $_dirroot_, because needs $CFG->dataroot to be set)
$log_date = date('Y-m-d') . '-00_00';
$CFG->forced_plugin_settings['tool_uclasupportconsole']['log_apache_error'] = '/var/log/httpd/error_log.' . $log_date;
$CFG->forced_plugin_settings['tool_uclasupportconsole']['log_apache_access'] = '/var/log/httpd/access_log.' . $log_date;
$CFG->forced_plugin_settings['tool_uclasupportconsole']['log_apache_ssl_access'] = '/var/log/httpd/ssl_access_log.' . $log_date;
$CFG->forced_plugin_settings['tool_uclasupportconsole']['log_apache_ssl_error'] = '/var/log/httpd/ssl_error_log.' . $log_date;
$CFG->forced_plugin_settings['tool_uclasupportconsole']['log_apache_ssl_request'] = '/var/log/httpd/ssl_request_log.' . $log_date;
$CFG->forced_plugin_settings['tool_uclasupportconsole']['log_shibboleth_shibd'] = '/var/log/shibboleth/shibd.log';
$CFG->forced_plugin_settings['tool_uclasupportconsole']['log_shibboleth_trans'] = '/var/log/shibboleth/transaction.log';
$CFG->forced_plugin_settings['tool_uclasupportconsole']['log_moodle_cron'] = '/home/moodle/logs/moodlecron/moodlecron.out';
$CFG->forced_plugin_settings['tool_uclasupportconsole']['log_course_creator'] = $CFG->dataroot . '/course_creator/';
$CFG->forced_plugin_settings['tool_uclasupportconsole']['log_prepop'] = '/home/moodle/logs/prepop_logs/';

// This will bootstrap the moodle functions.
require_once($_dirroot_ . '/lib/setup.php');

// EOF
