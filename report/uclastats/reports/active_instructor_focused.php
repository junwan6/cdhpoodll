<?php
/**
 * Report to get the number of active and inactive course sites by division.
 *
 * Criteria for an active course is defined in:
 *
 * CCLE-4029 - Course activity (Instructor focused)
 *
 * Has the course been modified in any way since the course shell was created?
 * Courses made visible with content beyond the default course shell should be
 * considered active.
 * 
 * @package    report
 * @subpackage uclastats
 * @copyright  UC Regents
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/local/ucla/lib.php');
require_once($CFG->dirroot . '/report/uclastats/locallib.php');

class active_instructor_focused extends uclastats_base {

    /**
     * Display number of inactive courses.
     *
     * @param array $results
     * @return string
     */
    public function format_cached_results($results) {
        if (isset($results['courselisting'])) {
            return count($results['courselisting']);
        }
        return '';
    }

    /**
     * For a given format, find the default blocks associated with it.
     * 
     * @param string $format
     *
     * @return array            Returns an array of default block names.
     */
    private function get_default_blocks($format) {
        global $CFG;
        
        static $defaultblockcache = array();
        if (!isset($defaultblockcache[$format])) {
            // Code to get default blocks adapted from
            // blocks_add_default_course_blocks().
            $defaultblocks = 'defaultblocks_' . $format;
            if (!empty($CFG->$defaultblocks)) {
                $blocknames = blocks_parse_default_blocks_list($CFG->$defaultblocks);

            } else {
                $formatconfig = $CFG->dirroot.'/course/format/'.$course->format.'/config.php';
                $format = array(); // initialize array in external file
                if (is_readable($formatconfig)) {
                    include($formatconfig);
                }
                if (!empty($format['defaultblocks'])) {
                    $blocknames = blocks_parse_default_blocks_list($format['defaultblocks']);

                } else if (!empty($CFG->defaultblocks)){
                    $blocknames = blocks_parse_default_blocks_list($CFG->defaultblocks);

                } else {
                    $blocknames = array(
                        BLOCK_POS_LEFT => array(),
                        BLOCK_POS_RIGHT => array('search_forums', 'news_items', 'calendar_upcoming', 'recent_activity')
                    );
                }
            }

            if (!isset($blocknames[BLOCK_POS_LEFT])) {
                $blocknames[BLOCK_POS_LEFT] = array();
            }
            if (!isset($blocknames[BLOCK_POS_RIGHT])) {
                $blocknames[BLOCK_POS_RIGHT] = array();
            }

            // Flatten $blocknames into a single array.
            $defaultblockcache[$format] = array_merge($blocknames[BLOCK_POS_LEFT],
                    $blocknames[BLOCK_POS_RIGHT]);
        }
        return $defaultblockcache[$format];
    }

    /**
     * Returns an array of form elements used to run report.
     */
    public function get_parameters() {
        return array('term');
    }

    /**
     * Display two results tables. One, for the inactive courses by division,
     * and, two, a list of the inactive courses for spot checking.
     *
     * @param uclastats_result $uclastats_result
     * @return string
     */
    protected function get_results_table(uclastats_result $uclastats_result) {
        $retval = '';

        $results = $uclastats_result->results;
        $courselisting = $results['courselisting'];
        unset($results['courselisting']);

        // Aggregated results.
        $resultstable = new html_table();
        $resultstable->id = 'uclastats-results-table';
        $resultstable->attributes = array('class' => 'results-table ' .
            get_class($this));

        $resultstable->head = $uclastats_result->get_header();
        $resultstable->data = $results;

        $retval = html_writer::table($resultstable);

        $retval .= html_writer::tag('h3', get_string('inactivecourselisting', 'report_uclastats'));

        // Course listing.
        $listingtable = new html_table();
        $listingtable->id = 'uclastats-courselisting-table';
        $listingtable->attributes = array('class' => 'results-table ' .
            get_class($this));

        $listingtable->head = array(get_string('division', 'report_uclastats'),
                get_string('course_shortname', 'report_uclastats'));
        foreach ($courselisting as $courseid => $course) {
            $courselisting[$courseid]['shortname'] = html_writer::link(
                    new moodle_url('/course/view.php',
                            array('id' => $courseid)), $course['shortname'],
                    array('target' => '_blank'));
        }
        $listingtable->data = $courselisting;
        
        $retval .= html_writer::table($listingtable);

        return $retval;
    }

    /**
     * Write out the aggregated results and the list of inactive courses.
     *
     * @param MoodleExcelWorksheet $worksheet
     * @param MoodleExcelFormat $boldformat
     * @param uclastats_result $uclastats_result
     * @param int $row      Row to start writing.
     *
     * @return int          Return row we stopped writing.
     */
    protected function get_results_xls(MoodleExcelWorksheet $worksheet,
            MoodleExcelFormat $boldformat, uclastats_result $uclastats_result, $row) {

        $results = $uclastats_result->results;
        $courselisting = $results['courselisting'];
        unset($results['courselisting']);

        // Display aggregated results.
        $col = 0;
        $header = $uclastats_result->get_header();
        foreach ($header as $name) {
            $worksheet->write_string($row, $col, $name, $boldformat);
            ++$col;
        }

        // now go through result set
        foreach ($results as $result) {
            ++$row; $col = 0;
            foreach ($result as $value) {
                // values might have HTML in them
                $value = clean_param($value, PARAM_NOTAGS);
                if (is_numeric($value)) {
                    $worksheet->write_number($row, $col, $value);
                } else {
                    $worksheet->write_string($row, $col, $value);
                }
                ++$col;
            }
        }

        $row += 2; $col = 0;
        $worksheet->write_string($row, $col,
                get_string('inactivecourselisting', 'report_uclastats'), $boldformat);
        $row++;

        // Display course listings table header
        $header = array(get_string('division', 'report_uclastats'),
                get_string('course_shortname', 'report_uclastats'));
        foreach ($header as $name) {
            $worksheet->write_string($row, $col, $name, $boldformat);
            ++$col;
        }

        // Now go through courselisting set.
        foreach ($courselisting as $course) {
            ++$row; $col = 0;
            foreach ($course as $value) {
                // values might have HTML in them
                $value = clean_param($value, PARAM_NOTAGS);
                if (is_numeric($value)) {
                    $worksheet->write_number($row, $col, $value);
                } else {
                    $worksheet->write_string($row, $col, $value);
                }
                ++$col;
            }
        }

        return $row;
    }

    /**
     * Query get the number of active/inactive course sites by division.
     *
     * @param array $params
     * @param return array
     */
    public function query($params) {
        global $DB;
        $retval = array();
        $courselisting = array();

        // For now, default threshold is hardcoded to be 1.
        $threshold = 1;

        // First get list of courseids for a given term by division.
        $sql = "SELECT  c.*,
                        urd.fullname AS division " .
                $this->from_filtered_courses(true) . "
                JOIN    {ucla_reg_division} urd ON (
                        urci.division=urd.code
                        )
                WHERE   1
                ORDER BY urd.fullname, urci.subj_area";
        $rs = $DB->get_recordset_sql($sql, $params);

        if ($rs->valid()) {
            foreach ($rs as $course) {
                $points = 0;
                $division = ucla_format_name($course->division, true);
                unset($course->division);

                // Initialize array for a given division.
                if (!isset($retval[$division])) {
                    // We want the result columns to display in a certain order.
                    $retval[$division] = array('division' => $division,
                        'numactive' => 0, 'numinactive' => 0,
                        'totalcourses' => 0);
                }

                /* Find how out many points this course "scored". Scoring is
                 * defined as:
                 *
                 * 1 point for each visible resource
                 * 1 point for each block added
                 * 1 points for each visible activity (not including default
                 * Announcement and Discussion forums)
                 * 1 point for each post by the Instructor/TA in either the
                 * default Announcement and Discussion forums.
                 */
                $points += $this->score_modules($course);
                $points += $this->score_blocks($course);
                $points += $this->score_default_forum_activity($course);

                // Update totals for divsion.
                if ($points >= $threshold) {
                    // Course is active if it is above a certain threshold.
                    ++$retval[$division]['numactive'];
                } else {
                    $courselisting[$course->id] = array('division' => $division,
                        'shortname' => $course->shortname);
                    ++$retval[$division]['numinactive'];
                }
                ++$retval[$division]['totalcourses'];
            }

            // Order result by division.
            ksort($retval);
            $retval['courselisting'] = $courselisting;
        }
        
        return $retval;
    }

    /**
     * Helper function to count the number of points a course has earned from
     * its course modules.
     *
     * @param object $course
     *
     * return int
     */
    private function score_blocks($course) {
        global $DB;

        $points = 0;

        // Count all blocks for a given course, excluding the default blocks
        // for the course format.
        $defautlblocks = $this->get_default_blocks($course->format);
        $context = context_course::instance($course->id);
        $records = $DB->get_records('block_instances',
                array('parentcontextid' => $context->id));
        foreach ($records as $record) {
            // Do not count the block if it is a default block.
            if (!in_array($record->blockname, $defautlblocks)) {
                ++$points;
            }
        }

        return $points;
    }

    /**
     * Returns the number of points earned for a course if an Instructor or TA
     * made at least one post in the default Announcements and/or Discussion
     * forums.
     *
     * @param object $course
     *
     * return int
     */
    private function score_default_forum_activity($course) {
        global $CFG, $DB;

        $instructingroles = array('editinginstructor', 'supervising_instructor',
            'ta_instructor', 'ta_admin', 'ta');
        list($insql, $params) = $DB->get_in_or_equal($instructingroles, SQL_PARAMS_NAMED);
        $params['courseid'] = $course->id;

        $sql = "SELECT  COUNT(fp.id)
                FROM    {forum} f
                JOIN    {context} ct ON (
                            ct.instanceid=f.course AND
                            ct.contextlevel=50
                        )
                JOIN    {role_assignments} ra ON (ct.id=ra.contextid)
                JOIN    {role} r ON (ra.roleid=r.id)
                JOIN    {forum_discussions} fd ON (fd.forum=f.id)
                JOIN    {forum_posts} fp ON (fp.discussion=fd.id)
                WHERE   f.course=:courseid AND
                        ((f.type='news' AND f.name='Announcements') OR
                        (f.type='general' AND f.name='Discussion forum')) AND
                        fp.userid=ra.userid AND
                        r.shortname " . $insql;
        $points = $DB->count_records_sql($sql, $params);

        return $points;
    }

    /**
     * Helper function to count the number of points a course has earned from
     * its course modules.
     *
     * @param object $course
     *
     * return int
     */
    private function score_modules($course) {
        global $DB;

        $points = 0;
        static $moduletypecache = array();

        // Get all visible course modules for course. Do not include the default
        // Announcement and Discussion forums.
        $sql = "SELECT  cm.id,
                        m.name
                FROM    {course_modules} cm
                JOIN    {modules} m ON (cm.module=m.id)
                WHERE   cm.course=:courseid AND
                        cm.visible=1 AND
                        cm.instance NOT IN (
                            SELECT  id
                            FROM    {forum}
                            WHERE   (type='news' AND name='Announcements') OR
                                    (type='general' AND name='Discussion forum')
                        )";
        $mods = $DB->get_records_sql($sql, array('courseid' => $course->id));

        if (!empty($mods)) {
            foreach ($mods as $mod) {
                // Check if module is a resource or activity.
                if (!isset($moduletypecache[$mod->name])) {
                    $moduletypecache[$mod->name] = plugin_supports('mod',
                            $mod->name, FEATURE_MOD_ARCHETYPE);
                }

                if ($moduletypecache[$mod->name] == MOD_ARCHETYPE_RESOURCE) {
                    // 1 point for each visible resource.
                    $points += 1;
                } else {
                    // 1 points for each visible activity. Note, that there is
                    // no constant for activities. Only resource modules return
                    // something for FEATURE_MOD_ARCHETYPE.
                    $points += 1;
                }
            }
        }

        return $points;
    }

}
