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
 * Meta course enrolment plugin.
 *
 * @package    enrol_meta
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Meta course enrolment plugin.
 * @author Petr Skoda
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_meta_plugin extends enrol_plugin {

    /**
     * Returns localised name of enrol instance
     *
     * @param stdClass $instance (null is accepted too)
     * @return string
     */
    public function get_instance_name($instance) {
        global $DB;

        if (empty($instance)) {
            $enrol = $this->get_name();
            return get_string('pluginname', 'enrol_'.$enrol);
        } else if (empty($instance->name)) {
            $enrol = $this->get_name();
            return get_string('pluginname', 'enrol_'.$enrol) . ' (' . format_string($DB->get_field('course', 'fullname', array('id'=>$instance->customint1))) . ')';
        } else {
            return format_string($instance->name);
        }
    }

    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
        $context = context_course::instance($courseid, MUST_EXIST);
        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/meta:config', $context)) {
            return NULL;
        }
        // multiple instances supported - multiple parent courses linked
        return new moodle_url('/enrol/meta/addinstance.php', array('id'=>$courseid));
    }

    /**
     * Does this plugin allow manual unenrolment of a specific user?
     * Yes, but only if user suspended...
     *
     * @param stdClass $instance course enrol instance
     * @param stdClass $ue record from user_enrolments table
     *
     * @return bool - true means user with 'enrol/xxx:unenrol' may unenrol this user, false means nobody may touch this user enrolment
     */
    public function allow_unenrol_user(stdClass $instance, stdClass $ue) {
        if ($ue->status == ENROL_USER_SUSPENDED) {
            return true;
        }

        return false;
    }

    /**
     * Gets an array of the user enrolment actions
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue A user enrolment object
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol_user($instance, $ue) && has_capability('enrol/meta:unenrol', $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class'=>'unenrollink', 'rel'=>$ue->id));
        }
        return $actions;
    }

    /**
     * Called after updating/inserting course.
     *
     * @param bool $inserted true if course just inserted
     * @param stdClass $course
     * @param stdClass $data form data
     * @return void
     */
    public function course_updated($inserted, $course, $data) {
        global $CFG;

        if (!$inserted) {
            // sync cohort enrols
            require_once("$CFG->dirroot/enrol/meta/locallib.php");
            enrol_meta_sync($course->id);
        } else {
            // cohorts are never inserted automatically
        }
    }

    /**
     * Update instance status
     *
     * @param stdClass $instance
     * @param int $newstatus ENROL_INSTANCE_ENABLED, ENROL_INSTANCE_DISABLED
     * @return void
     */
    public function update_status($instance, $newstatus) {
        global $CFG;

        parent::update_status($instance, $newstatus);

        require_once("$CFG->dirroot/enrol/meta/locallib.php");
        enrol_meta_sync($instance->courseid);
    }

    /**
     * Called for all enabled enrol plugins that returned true from is_cron_required().
     * @return void
     */
    public function cron() {
        global $CFG;

        require_once("$CFG->dirroot/enrol/meta/locallib.php");
        enrol_meta_sync();
    }

    // START UCLA MOD: CCLE-2386 - TA Site Creator
    /**
     *  API call - lib/enrollib.php.
     *  UCLA MOD CCLE-2386 - TA sites
     **/
    function add_course_navigation($instancesnode, stdclass $instance) {
        global $PAGE;

        // This is technically a hack, $instancenode provides us
        // the node from settings_navigation, not global_navigation
        if (!empty($instance->customint1)) {
            $pcourseid = $instance->customint1;
            $courseid = $instance->courseid;

            $pcoursenode = $PAGE->navigation->find($pcourseid,
                    navigation_node::TYPE_COURSE);

            if (empty($pcoursenode)) {
                return;
            }

            $coursenode = $PAGE->navigation->find($courseid,
                    navigation_node::TYPE_COURSE);

            $pcoursenode->set_parent($coursenode->parent);
            $coursenode->set_parent($pcoursenode);
        }

    }

    /**
     *  Returns which role to assign a user based on promotion rules.
     *  UCLA MOD CCLE-2386 - TA sites
     **/
    static function get_target_role($roleid,
                                    $promoroleid, $promotoroleid,
                                    $userid=null, $promouserid=null) {
        $toroleid = $roleid;
        if ($roleid == $promoroleid) {
            // If there is a specific user only
            if ($promouserid) {
                if ($userid == $promouserid) {
                    $toroleid = $promotoroleid;
                }
            } else {
                $toroleid = $promotoroleid;
            }
        }

        return $toroleid;
    }

    /**
     *  Convenience function.
     *  UCLA MOD CCLE-2386
     **/
    static function get_role_promotion($ra) {
        if (!isset($ra->roleid)
                || !isset($ra->promoroleid)
                || !isset($ra->promotoroleid)) {
            return false;
        }

        $userid = null;
        $promouserid = null;

        if (isset($ra->userid)) {
            $userid = $ra->userid;
        }

        if (isset($ra->promouserid)) {
            $promouserid = $ra->promouserid;
        }

        return self::get_target_role($ra->roleid, $ra->promoroleid,
                $ra->promotoroleid, $userid, $promouserid);
    }

    /**
     *  Check to see if this is an automatically created connection.
     *  UCLA MOD CCLE-2386
     **/
    function instance_deleteable($instance) {
        global $DB;

        if (isset($instance->customint2) 
                && $DB->get_record('course', 
                    array('id' => $instance->customint1))) {

            return false;
        }

        return true;
    }
    // END UCLA MOD: CCLE-2386
}

