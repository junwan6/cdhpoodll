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
 * Library of functions for database manipulation.
 *
 * Other main libraries:
 * - weblib.php - functions that produce web output
 * - moodlelib.php - general-purpose Moodle functions
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The maximum courses in a category
 * MAX_COURSES_IN_CATEGORY * MAX_COURSE_CATEGORIES must not be more than max integer!
 */
define('MAX_COURSES_IN_CATEGORY', 10000);

/**
  * The maximum number of course categories
  * MAX_COURSES_IN_CATEGORY * MAX_COURSE_CATEGORIES must not be more than max integer!
  */
define('MAX_COURSE_CATEGORIES', 10000);

/**
 * Number of seconds to wait before updating lastaccess information in DB.
 */
define('LASTACCESS_UPDATE_SECS', 60);

/**
 * Returns $user object of the main admin user
 *
 * @static stdClass $mainadmin
 * @return stdClass {@link $USER} record from DB, false if not found
 */
function get_admin() {
    global $CFG, $DB;

    static $mainadmin = null;
    static $prevadmins = null;

    if (empty($CFG->siteadmins)) {
        // Should not happen on an ordinary site.
        // It does however happen during unit tests.
        return false;
    }

    if (isset($mainadmin) and $prevadmins === $CFG->siteadmins) {
        return clone($mainadmin);
    }

    $mainadmin = null;

    foreach (explode(',', $CFG->siteadmins) as $id) {
        if ($user = $DB->get_record('user', array('id'=>$id, 'deleted'=>0))) {
            $mainadmin = $user;
            break;
        }
    }

    if ($mainadmin) {
        $prevadmins = $CFG->siteadmins;
        return clone($mainadmin);
    } else {
        // this should not happen
        return false;
    }
}

/**
 * Returns list of all admins, using 1 DB query
 *
 * @return array
 */
function get_admins() {
    global $DB, $CFG;

    if (empty($CFG->siteadmins)) {  // Should not happen on an ordinary site
        return array();
    }

    $sql = "SELECT u.*
              FROM {user} u
             WHERE u.deleted = 0 AND u.id IN ($CFG->siteadmins)";

    // We want the same order as in $CFG->siteadmins.
    $records = $DB->get_records_sql($sql);
    $admins = array();
    foreach (explode(',', $CFG->siteadmins) as $id) {
        $id = (int)$id;
        if (!isset($records[$id])) {
            // User does not exist, this should not happen.
            continue;
        }
        $admins[$records[$id]->id] = $records[$id];
    }

    return $admins;
}

/**
 * Search through course users
 *
 * If $coursid specifies the site course then this function searches
 * through all undeleted and confirmed users
 *
 * @global object
 * @uses SITEID
 * @uses SQL_PARAMS_NAMED
 * @uses CONTEXT_COURSE
 * @param int $courseid The course in question.
 * @param int $groupid The group in question.
 * @param string $searchtext The string to search for
 * @param string $sort A field to sort by
 * @param array $exceptions A list of IDs to ignore, eg 2,4,5,8,9,10
 * @return array
 */
function search_users($courseid, $groupid, $searchtext, $sort='', array $exceptions=null) {
    global $DB;

    $fullname  = $DB->sql_fullname('u.firstname', 'u.lastname');

    if (!empty($exceptions)) {
        list($exceptions, $params) = $DB->get_in_or_equal($exceptions, SQL_PARAMS_NAMED, 'ex', false);
        $except = "AND u.id $exceptions";
    } else {
        $except = "";
        $params = array();
    }

    if (!empty($sort)) {
        $order = "ORDER BY $sort";
    } else {
        $order = "";
    }

    $select = "u.deleted = 0 AND u.confirmed = 1 AND (".$DB->sql_like($fullname, ':search1', false)." OR ".$DB->sql_like('u.email', ':search2', false).")";
    $params['search1'] = "%$searchtext%";
    $params['search2'] = "%$searchtext%";

    if (!$courseid or $courseid == SITEID) {
        $sql = "SELECT u.id, u.firstname, u.lastname, u.email
                  FROM {user} u
                 WHERE $select
                       $except
                $order";
        return $DB->get_records_sql($sql, $params);

    } else {
        if ($groupid) {
            $sql = "SELECT u.id, u.firstname, u.lastname, u.email
                      FROM {user} u
                      JOIN {groups_members} gm ON gm.userid = u.id
                     WHERE $select AND gm.groupid = :groupid
                           $except
                     $order";
            $params['groupid'] = $groupid;
            return $DB->get_records_sql($sql, $params);

        } else {
            $context = context_course::instance($courseid);
            $contextlists = get_related_contexts_string($context);

            $sql = "SELECT u.id, u.firstname, u.lastname, u.email
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                     WHERE $select AND ra.contextid $contextlists
                           $except
                    $order";
            return $DB->get_records_sql($sql, $params);
        }
    }
}

/**
 * Returns SQL used to search through user table to find users (in a query
 * which may also join and apply other conditions).
 *
 * You can combine this SQL with an existing query by adding 'AND $sql' to the
 * WHERE clause of your query (where $sql is the first element in the array
 * returned by this function), and merging in the $params array to the parameters
 * of your query (where $params is the second element). Your query should use
 * named parameters such as :param, rather than the question mark style.
 *
 * There are examples of basic usage in the unit test for this function.
 *
 * @param string $search the text to search for (empty string = find all)
 * @param string $u the table alias for the user table in the query being
 *     built. May be ''.
 * @param bool $searchanywhere If true (default), searches in the middle of
 *     names, otherwise only searches at start
 * @param array $extrafields Array of extra user fields to include in search
 * @param array $exclude Array of user ids to exclude (empty = don't exclude)
 * @param array $includeonly If specified, only returns users that have ids
 *     incldued in this array (empty = don't restrict)
 * @return array an array with two elements, a fragment of SQL to go in the
 *     where clause the query, and an associative array containing any required
 *     parameters (using named placeholders).
 */
function users_search_sql($search, $u = 'u', $searchanywhere = true, array $extrafields = array(),
        array $exclude = null, array $includeonly = null) {
    global $DB, $CFG;
    $params = array();
    $tests = array();

    if ($u) {
        $u .= '.';
    }

    // If we have a $search string, put a field LIKE '$search%' condition on each field.
    if ($search) {
        $conditions = array(
            $DB->sql_fullname($u . 'firstname', $u . 'lastname'),
            $conditions[] = $u . 'lastname'
        );
        foreach ($extrafields as $field) {
            $conditions[] = $u . $field;
        }
        if ($searchanywhere) {
            $searchparam = '%' . $search . '%';
        } else {
            $searchparam = $search . '%';
        }
        $i = 0;
        foreach ($conditions as $key => $condition) {
            $conditions[$key] = $DB->sql_like($condition, ":con{$i}00", false, false);
            $params["con{$i}00"] = $searchparam;
            $i++;
        }
        $tests[] = '(' . implode(' OR ', $conditions) . ')';
    }

    // Add some additional sensible conditions.
    $tests[] = $u . "id <> :guestid";
    $params['guestid'] = $CFG->siteguest;
    $tests[] = $u . 'deleted = 0';
    $tests[] = $u . 'confirmed = 1';

    // If we are being asked to exclude any users, do that.
    if (!empty($exclude)) {
        list($usertest, $userparams) = $DB->get_in_or_equal($exclude, SQL_PARAMS_NAMED, 'ex', false);
        $tests[] = $u . 'id ' . $usertest;
        $params = array_merge($params, $userparams);
    }

    // If we are validating a set list of userids, add an id IN (...) test.
    if (!empty($includeonly)) {
        list($usertest, $userparams) = $DB->get_in_or_equal($includeonly, SQL_PARAMS_NAMED, 'val');
        $tests[] = $u . 'id ' . $usertest;
        $params = array_merge($params, $userparams);
    }

    // In case there are no tests, add one result (this makes it easier to combine
    // this with an existing query as you can always add AND $sql).
    if (empty($tests)) {
        $tests[] = '1 = 1';
    }

    // Combing the conditions and return.
    return array(implode(' AND ', $tests), $params);
}


/**
 * This function generates the standard ORDER BY clause for use when generating
 * lists of users. If you don't have a reason to use a different order, then
 * you should use this method to generate the order when displaying lists of users.
 *
 * If the optional $search parameter is passed, then exact matches to the search
 * will be sorted first. For example, suppose you have two users 'Al Zebra' and
 * 'Alan Aardvark'. The default sort is Alan, then Al. If, however, you search for
 * 'Al', then Al will be listed first. (With two users, this is not a big deal,
 * but with thousands of users, it is essential.)
 *
 * The list of fields scanned for exact matches are:
 *  - firstname
 *  - lastname
 *  - $DB->sql_fullname
 *  - those returned by get_extra_user_fields
 *
 * If named parameters are used (which is the default, and highly recommended),
 * then the parameter names are like :usersortexactN, where N is an int.
 *
 * The simplest possible example use is:
 * list($sort, $params) = users_order_by_sql();
 * $sql = 'SELECT * FROM {users} ORDER BY ' . $sort;
 *
 * A more complex example, showing that this sort can be combined with other sorts:
 * list($sort, $sortparams) = users_order_by_sql('u');
 * $sql = "SELECT g.id AS groupid, gg.groupingid, u.id AS userid, u.firstname, u.lastname, u.idnumber, u.username
 *           FROM {groups} g
 *      LEFT JOIN {groupings_groups} gg ON g.id = gg.groupid
 *      LEFT JOIN {groups_members} gm ON g.id = gm.groupid
 *      LEFT JOIN {user} u ON gm.userid = u.id
 *          WHERE g.courseid = :courseid $groupwhere $groupingwhere
 *       ORDER BY g.name, $sort";
 * $params += $sortparams;
 *
 * An example showing the use of $search:
 * list($sort, $sortparams) = users_order_by_sql('u', $search, $this->get_context());
 * $order = ' ORDER BY ' . $sort;
 * $params += $sortparams;
 * $availableusers = $DB->get_records_sql($fields . $sql . $order, $params, $page*$perpage, $perpage);
 *
 * @param string $usertablealias (optional) any table prefix for the {users} table. E.g. 'u'.
 * @param string $search (optional) a current search string. If given,
 *      any exact matches to this string will be sorted first.
 * @param context $context the context we are in. Use by get_extra_user_fields.
 *      Defaults to $PAGE->context.
 * @return array with two elements:
 *      string SQL fragment to use in the ORDER BY clause. For example, "firstname, lastname".
 *      array of parameters used in the SQL fragment.
 */
function users_order_by_sql($usertablealias = '', $search = null, context $context = null) {
    global $DB, $PAGE;

    if ($usertablealias) {
        $tableprefix = $usertablealias . '.';
    } else {
        $tableprefix = '';
    }

    $sort = "{$tableprefix}lastname, {$tableprefix}firstname, {$tableprefix}id";
    $params = array();

    if (!$search) {
        return array($sort, $params);
    }

    if (!$context) {
        $context = $PAGE->context;
    }

    $exactconditions = array();
    $paramkey = 'usersortexact1';

    $exactconditions[] = $DB->sql_fullname($tableprefix . 'firstname', $tableprefix  . 'lastname') .
            ' = :' . $paramkey;
    $params[$paramkey] = $search;
    $paramkey++;

    $fieldstocheck = array_merge(array('firstname', 'lastname'), get_extra_user_fields($context));
    foreach ($fieldstocheck as $key => $field) {
        $exactconditions[] = 'LOWER(' . $tableprefix . $field . ') = LOWER(:' . $paramkey . ')';
        $params[$paramkey] = $search;
        $paramkey++;
    }

    $sort = 'CASE WHEN ' . implode(' OR ', $exactconditions) .
            ' THEN 0 ELSE 1 END, ' . $sort;

    return array($sort, $params);
}

/**
 * Returns a subset of users
 *
 * @global object
 * @uses DEBUG_DEVELOPER
 * @uses SQL_PARAMS_NAMED
 * @param bool $get If false then only a count of the records is returned
 * @param string $search A simple string to search for
 * @param bool $confirmed A switch to allow/disallow unconfirmed users
 * @param array $exceptions A list of IDs to ignore, eg 2,4,5,8,9,10
 * @param string $sort A SQL snippet for the sorting criteria to use
 * @param string $firstinitial Users whose first name starts with $firstinitial
 * @param string $lastinitial Users whose last name starts with $lastinitial
 * @param string $page The page or records to return
 * @param string $recordsperpage The number of records to return per page
 * @param string $fields A comma separated list of fields to be returned from the chosen table.
 * @return array|int|bool  {@link $USER} records unless get is false in which case the integer count of the records found is returned.
 *                        False is returned if an error is encountered.
 */
function get_users($get=true, $search='', $confirmed=false, array $exceptions=null, $sort='firstname ASC',
                   $firstinitial='', $lastinitial='', $page='', $recordsperpage='', $fields='*', $extraselect='', array $extraparams=null) {
    global $DB, $CFG;

    if ($get && !$recordsperpage) {
        debugging('Call to get_users with $get = true no $recordsperpage limit. ' .
                'On large installations, this will probably cause an out of memory error. ' .
                'Please think again and change your code so that it does not try to ' .
                'load so much data into memory.', DEBUG_DEVELOPER);
    }

    $fullname  = $DB->sql_fullname();

    $select = " id <> :guestid AND deleted = 0";
    $params = array('guestid'=>$CFG->siteguest);

    if (!empty($search)){
        $search = trim($search);
        $select .= " AND (".$DB->sql_like($fullname, ':search1', false)." OR ".$DB->sql_like('email', ':search2', false)." OR username = :search3)";
        $params['search1'] = "%$search%";
        $params['search2'] = "%$search%";
        $params['search3'] = "$search";
    }

    if ($confirmed) {
        $select .= " AND confirmed = 1";
    }

    if ($exceptions) {
        list($exceptions, $eparams) = $DB->get_in_or_equal($exceptions, SQL_PARAMS_NAMED, 'ex', false);
        $params = $params + $eparams;
        $select .= " AND id $exceptions";
    }

    if ($firstinitial) {
        $select .= " AND ".$DB->sql_like('firstname', ':fni', false, false);
        $params['fni'] = "$firstinitial%";
    }
    if ($lastinitial) {
        $select .= " AND ".$DB->sql_like('lastname', ':lni', false, false);
        $params['lni'] = "$lastinitial%";
    }

    if ($extraselect) {
        $select .= " AND $extraselect";
        $params = $params + (array)$extraparams;
    }

    if ($get) {
        return $DB->get_records_select('user', $select, $params, $sort, $fields, $page, $recordsperpage);
    } else {
        return $DB->count_records_select('user', $select, $params);
    }
}


/**
 * Return filtered (if provided) list of users in site, except guest and deleted users.
 *
 * @param string $sort An SQL field to sort by
 * @param string $dir The sort direction ASC|DESC
 * @param int $page The page or records to return
 * @param int $recordsperpage The number of records to return per page
 * @param string $search A simple string to search for
 * @param string $firstinitial Users whose first name starts with $firstinitial
 * @param string $lastinitial Users whose last name starts with $lastinitial
 * @param string $extraselect An additional SQL select statement to append to the query
 * @param array $extraparams Additional parameters to use for the above $extraselect
 * @param stdClass $extracontext If specified, will include user 'extra fields'
 *   as appropriate for current user and given context
 * @return array Array of {@link $USER} records
 */
function get_users_listing($sort='lastaccess', $dir='ASC', $page=0, $recordsperpage=0,
                           $search='', $firstinitial='', $lastinitial='', $extraselect='',
                           array $extraparams=null, $extracontext = null) {
    global $DB, $CFG;

    $fullname  = $DB->sql_fullname();

    $select = "deleted <> 1 AND id <> :guestid";
    $params = array('guestid' => $CFG->siteguest);

    if (!empty($search)) {
        $search = trim($search);
        $select .= " AND (". $DB->sql_like($fullname, ':search1', false, false).
                   " OR ". $DB->sql_like('email', ':search2', false, false).
                   " OR username = :search3)";
        $params['search1'] = "%$search%";
        $params['search2'] = "%$search%";
        $params['search3'] = "$search";
    }

    if ($firstinitial) {
        $select .= " AND ". $DB->sql_like('firstname', ':fni', false, false);
        $params['fni'] = "$firstinitial%";
    }
    if ($lastinitial) {
        $select .= " AND ". $DB->sql_like('lastname', ':lni', false, false);
        $params['lni'] = "$lastinitial%";
    }

    if ($extraselect) {
        $select .= " AND $extraselect";
        $params = $params + (array)$extraparams;
    }

    if ($sort) {
        $sort = " ORDER BY $sort $dir";
    }

    // If a context is specified, get extra user fields that the current user
    // is supposed to see.
    $extrafields = '';
    if ($extracontext) {
        $extrafields = get_extra_user_fields_sql($extracontext, '', '',
                array('id', 'username', 'email', 'firstname', 'lastname', 'city', 'country',
                'lastaccess', 'confirmed', 'mnethostid'));
    }

    // warning: will return UNCONFIRMED USERS
    return $DB->get_records_sql("SELECT id, username, email, firstname, lastname, city, country,
                                        lastaccess, confirmed, mnethostid, suspended $extrafields
                                   FROM {user}
                                  WHERE $select
                                  $sort", $params, $page, $recordsperpage);

}


/**
 * Full list of users that have confirmed their accounts.
 *
 * @global object
 * @return array of unconfirmed users
 */
function get_users_confirmed() {
    global $DB, $CFG;
    return $DB->get_records_sql("SELECT *
                                   FROM {user}
                                  WHERE confirmed = 1 AND deleted = 0 AND id <> ?", array($CFG->siteguest));
}


/// OTHER SITE AND COURSE FUNCTIONS /////////////////////////////////////////////


/**
 * Returns $course object of the top-level site.
 *
 * @return object A {@link $COURSE} object for the site, exception if not found
 */
function get_site() {
    global $SITE, $DB;

    if (!empty($SITE->id)) {   // We already have a global to use, so return that
        return $SITE;
    }

    if ($course = $DB->get_record('course', array('category'=>0))) {
        return $course;
    } else {
        // course table exists, but the site is not there,
        // unfortunately there is no automatic way to recover
        throw new moodle_exception('nosite', 'error');
    }
}

/**
 * Gets a course object from database. If the course id corresponds to an
 * already-loaded $COURSE or $SITE object, then the loaded object will be used,
 * saving a database query.
 *
 * If it reuses an existing object, by default the object will be cloned. This
 * means you can modify the object safely without affecting other code.
 *
 * @param int $courseid Course id
 * @param bool $clone If true (default), makes a clone of the record
 * @return stdClass A course object
 * @throws dml_exception If not found in database
 */
function get_course($courseid, $clone = true) {
    global $DB, $COURSE, $SITE;
    if (!empty($COURSE->id) && $COURSE->id == $courseid) {
        return $clone ? clone($COURSE) : $COURSE;
    } else if (!empty($SITE->id) && $SITE->id == $courseid) {
        return $clone ? clone($SITE) : $SITE;
    } else {
        return $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    }
}

/**
 * Returns list of courses, for whole site, or category
 *
 * Returns list of courses, for whole site, or category
 * Important: Using c.* for fields is extremely expensive because
 *            we are using distinct. You almost _NEVER_ need all the fields
 *            in such a large SELECT
 *
 * @global object
 * @global object
 * @global object
 * @uses CONTEXT_COURSE
 * @param string|int $categoryid Either a category id or 'all' for everything
 * @param string $sort A field and direction to sort by
 * @param string $fields The additional fields to return
 * @return array Array of courses
 */
function get_courses($categoryid="all", $sort="c.sortorder ASC", $fields="c.*") {

    global $USER, $CFG, $DB;

    $params = array();

    if ($categoryid !== "all" && is_numeric($categoryid)) {
        $categoryselect = "WHERE c.category = :catid";
        $params['catid'] = $categoryid;
    } else {
        $categoryselect = "";
    }

    if (empty($sort)) {
        $sortstatement = "";
    } else {
        $sortstatement = "ORDER BY $sort";
    }

    $visiblecourses = array();

    list($ccselect, $ccjoin) = context_instance_preload_sql('c.id', CONTEXT_COURSE, 'ctx');

    $sql = "SELECT $fields $ccselect
              FROM {course} c
           $ccjoin
              $categoryselect
              $sortstatement";

    // pull out all course matching the cat
    if ($courses = $DB->get_records_sql($sql, $params)) {

        // loop throught them
        foreach ($courses as $course) {
            context_instance_preload($course);
            if (isset($course->visible) && $course->visible <= 0) {
                // for hidden courses, require visibility check
                if (has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id))) {
                    $visiblecourses [$course->id] = $course;
                }
            } else {
                $visiblecourses [$course->id] = $course;
            }
        }
    }
    return $visiblecourses;
}


/**
 * Returns list of courses, for whole site, or category
 *
 * Similar to get_courses, but allows paging
 * Important: Using c.* for fields is extremely expensive because
 *            we are using distinct. You almost _NEVER_ need all the fields
 *            in such a large SELECT
 *
 * @global object
 * @global object
 * @global object
 * @uses CONTEXT_COURSE
 * @param string|int $categoryid Either a category id or 'all' for everything
 * @param string $sort A field and direction to sort by
 * @param string $fields The additional fields to return
 * @param int $totalcount Reference for the number of courses
 * @param string $limitfrom The course to start from
 * @param string $limitnum The number of courses to limit to
 * @return array Array of courses
 */
function get_courses_page($categoryid="all", $sort="c.sortorder ASC", $fields="c.*",
                          &$totalcount, $limitfrom="", $limitnum="") {
    global $USER, $CFG, $DB;

    $params = array();

    $categoryselect = "";
    if ($categoryid !== "all" && is_numeric($categoryid)) {
        $categoryselect = "WHERE c.category = :catid";
        $params['catid'] = $categoryid;
    } else {
        $categoryselect = "";
    }

    list($ccselect, $ccjoin) = context_instance_preload_sql('c.id', CONTEXT_COURSE, 'ctx');

    $totalcount = 0;
    if (!$limitfrom) {
        $limitfrom = 0;
    }
    $visiblecourses = array();

    $sql = "SELECT $fields $ccselect
              FROM {course} c
              $ccjoin
           $categoryselect
          ORDER BY $sort";

    // pull out all course matching the cat
    $rs = $DB->get_recordset_sql($sql, $params);
    // iteration will have to be done inside loop to keep track of the limitfrom and limitnum
    foreach($rs as $course) {
        context_instance_preload($course);
        if ($course->visible <= 0) {
            // for hidden courses, require visibility check
            if (has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id))) {
                $totalcount++;
                if ($totalcount > $limitfrom && (!$limitnum or count($visiblecourses) < $limitnum)) {
                    $visiblecourses [$course->id] = $course;
                }
            }
        } else {
            $totalcount++;
            if ($totalcount > $limitfrom && (!$limitnum or count($visiblecourses) < $limitnum)) {
                $visiblecourses [$course->id] = $course;
            }
        }
    }
    $rs->close();
    return $visiblecourses;
}

/**
 * A list of courses that match a search
 *
 * @global object
 * @global object
 * @param array $searchterms An array of search criteria
 * @param string $sort A field and direction to sort by
 * @param int $page The page number to get
 * @param int $recordsperpage The number of records per page
 * @param int $totalcount Passed in by reference.
 * @return object {@link $COURSE} records
 */
function get_courses_search($searchterms, $sort = 'fullname ASC', $page = 0, $recordsperpage = 50, &$totalcount = 0, $otherargs = null) {
    global $CFG, $DB;
    
    // START UCLA MOD CCLE-2309
    /// Collaboration join conditions:
    //      We have 4 cases to cover:
    //       
    //     1 [x] collab [x] course
    //     2 [x] collab [ ] course
    //     3 [ ] collab [x] course
    //     4 [ ] collab [ ] course
    //
    //      Case 1 & 4 are treated the same
    //
    // To start, assume case 1 & 4:
    
    $collab = null;
    $course = null;
    
    if(!empty($otherargs)) {
        $collab = $otherargs['collab'];
        $course = $otherargs['course'];
    }
    
    $collab_join = 'LEFT JOIN {ucla_siteindicator} AS si ON si.courseid = c.id ';
    $collab_and = "AND (si.type IS NULL OR (si.type NOT LIKE 'test' AND si.type NOT LIKE 'private'))";
    $collab_select = ', si.courseid as collabcheck';
    $rest_limit = '';
    // CCLE-3948: Add registrar summary/description to corse info.
    // In this case (1), we don't want to include the ucla_reg_classinfo 
    // because we cannot do a JOIN conditon on all tables and get back collaboration
    // site entries.  We can do a LEFT JOIN on this table, but this is painfully slow,
    // so we exclude the registrar table for now.
    $reg_join = '';
    $reg_select = '';
    
    // Limit sql query results 
    if(!empty($otherargs)) {
        $rest_limit = 'LIMIT ' . $recordsperpage;
    }

    // Handle case 2:
    if(empty($course) && !empty($collab)) {
        // Search only collab sites

        $collab_join = 'JOIN {ucla_siteindicator} si ON c.id = si.courseid';
        $collab_and = "AND si.type NOT LIKE 'test' AND si.type NOT LIKE 'private'";
        $collab_select = ', si.courseid as collabcheck';
        $reg_join = '';
        $reg_select = '';

    // Handle case 3:
    } else if (!empty($course) && empty($collab)) {
        // Search only course sites
        $collab_join = 'LEFT JOIN {ucla_siteindicator} AS si ON si.courseid = c.id ';
        $collab_and = 'AND si.id IS NULL';
        $collab_select = ', si.courseid as collabcheck';
        // Since we don't join on collab table, it's safe to join on 
        // registrar table.
        $reg_join = ' JOIN {ucla_reg_classinfo} AS urci ON c.shortname =
            CONCAT(urci.term, "-", urci.subj_area, urci.coursenum, "-", urci.sectnum) ';
        $reg_select = ', urci.crs_desc AS reg_desc, urci.crs_summary AS reg_summary';
    }
    // END UCLA MOD CCLE-2309

    if ($DB->sql_regex_supported()) {
        $REGEXP    = $DB->sql_regex(true);
        $NOTREGEXP = $DB->sql_regex(false);
    }

    $searchcond = array();
    $params     = array();
    $i = 0;

    // Thanks Oracle for your non-ansi concat and type limits in coalesce. MDL-29912

    // BEGIN UCLA MOD CCLE-3948
    // Adding the registrar info to the concat list of fields to be searched on.
    if ($DB->get_dbfamily() == 'oracle') {
        if (empty($reg_join)) {
            $concat = "(c.summary|| ' ' || c.fullname || ' ' || c.idnumber || ' ' || c.shortname)";
        } else {
            $concat = "(c.summary|| ' ' || c.fullname || ' ' || c.idnumber || ' ' || c.shortname || ' ' || urci.crs_desc || ' ' || urci.crs_summary)";
        }
    } else {
        if (empty($reg_join)) {
            $concat = $DB->sql_concat("COALESCE(c.summary, '')", "' '", 'c.fullname', "' '", 'c.idnumber', "' '", 'c.shortname');
        } else {
            $concat = $DB->sql_concat("COALESCE(c.summary, '')", "' '",
                                  'c.fullname', "' '",
                                  'c.idnumber', "' '",
                                  'c.shortname', "' '",
                                  "COALESCE(urci.crs_desc, '')", "' '",
                                  "COALESCE(urci.crs_summary, '')");
        }
    }
    // END UCLA MOD CCLE-3948

    foreach ($searchterms as $searchterm) {
        $i++;

        $NOT = false; /// Initially we aren't going to perform NOT LIKE searches, only MSSQL and Oracle
                   /// will use it to simulate the "-" operator with LIKE clause

    /// Under Oracle and MSSQL, trim the + and - operators and perform
    /// simpler LIKE (or NOT LIKE) queries
        if (!$DB->sql_regex_supported()) {
            if (substr($searchterm, 0, 1) == '-') {
                $NOT = true;
            }
            $searchterm = trim($searchterm, '+-');
        }

        // TODO: +- may not work for non latin languages

        if (substr($searchterm,0,1) == '+') {
            $searchterm = trim($searchterm, '+-');
            $searchterm = preg_quote($searchterm, '|');
            $searchcond[] = "$concat $REGEXP :ss$i";
            $params['ss'.$i] = "(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)";

        } else if (substr($searchterm,0,1) == "-") {
            $searchterm = trim($searchterm, '+-');
            $searchterm = preg_quote($searchterm, '|');
            $searchcond[] = "$concat $NOTREGEXP :ss$i";
            $params['ss'.$i] = "(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)";

        } else {
            $searchcond[] = $DB->sql_like($concat,":ss$i", false, true, $NOT);
            $params['ss'.$i] = "%$searchterm%";
        }
    }

    if (empty($searchcond)) {
        $totalcount = 0;
        return array();
    }

    $searchcond = implode(" AND ", $searchcond);

    $courses = array();
    $c = 0; // counts how many visible courses we've seen

    // Tiki pagination
    $limitfrom = $page * $recordsperpage;
    $limitto   = $limitfrom + $recordsperpage;

    list($ccselect, $ccjoin) = context_instance_preload_sql('c.id', CONTEXT_COURSE, 'ctx');
    $fields = array_diff(array_keys($DB->get_columns('course')), array('modinfo', 'sectioncache'));
    // START UCLA MOD CCLE-3948 - re-add advance search (to support collab).
    // Old query:
    // $sql = "SELECT c.".join(',c.',$fields)." $cselect
    //           FROM {course} c
    //        $ccjoin
    //          WHERE $searchcond AND c.id <> ".SITEID."
    //       ORDER BY $sort";
    $sql = "SELECT c.* $ccselect $collab_select $reg_select
              FROM {course} c
           $collab_join
           $reg_join
           $ccjoin
             WHERE $searchcond AND c.id <> " . SITEID . "
             $collab_and
          ORDER BY $sort
             $rest_limit";
    // END UCLA MOD CCLE-3948

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $course) {
        if (!$course->visible) {
            // preload contexts only for hidden courses or courses we need to return
            context_instance_preload($course);
            $coursecontext = context_course::instance($course->id);
            if (!has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
                continue;
            }
        }
        // Don't exit this loop till the end
        // we need to count all the visible courses
        // to update $totalcount
        if ($c >= $limitfrom && $c < $limitto) {
            $courses[$course->id] = $course;
        }
        $c++;
    }
    $rs->close();

    // our caller expects 2 bits of data - our return
    // array, and an updated $totalcount
    $totalcount = $c;
    return $courses;
}

/**
 * Fixes course category and course sortorder, also verifies category and course parents and paths.
 * (circular references are not fixed)
 *
 * @global object
 * @global object
 * @uses MAX_COURSES_IN_CATEGORY
 * @uses MAX_COURSE_CATEGORIES
 * @uses SITEID
 * @uses CONTEXT_COURSE
 * @return void
 */
function fix_course_sortorder() {
    global $DB, $SITE;

    //WARNING: this is PHP5 only code!

    // if there are any changes made to courses or categories we will trigger
    // the cache events to purge all cached courses/categories data
    $cacheevents = array();

    if ($unsorted = $DB->get_records('course_categories', array('sortorder'=>0))) {
        //move all categories that are not sorted yet to the end
        $DB->set_field('course_categories', 'sortorder', MAX_COURSES_IN_CATEGORY*MAX_COURSE_CATEGORIES, array('sortorder'=>0));
        $cacheevents['changesincoursecat'] = true;
    }

    $allcats = $DB->get_records('course_categories', null, 'sortorder, id', 'id, sortorder, parent, depth, path');
    $topcats    = array();
    $brokencats = array();
    foreach ($allcats as $cat) {
        $sortorder = (int)$cat->sortorder;
        if (!$cat->parent) {
            while(isset($topcats[$sortorder])) {
                $sortorder++;
            }
            $topcats[$sortorder] = $cat;
            continue;
        }
        if (!isset($allcats[$cat->parent])) {
            $brokencats[] = $cat;
            continue;
        }
        if (!isset($allcats[$cat->parent]->children)) {
            $allcats[$cat->parent]->children = array();
        }
        while(isset($allcats[$cat->parent]->children[$sortorder])) {
            $sortorder++;
        }
        $allcats[$cat->parent]->children[$sortorder] = $cat;
    }
    unset($allcats);

    // add broken cats to category tree
    if ($brokencats) {
        $defaultcat = reset($topcats);
        foreach ($brokencats as $cat) {
            $topcats[] = $cat;
        }
    }

    // now walk recursively the tree and fix any problems found
    $sortorder = 0;
    $fixcontexts = array();
    if (_fix_course_cats($topcats, $sortorder, 0, 0, '', $fixcontexts)) {
        $cacheevents['changesincoursecat'] = true;
    }

    // detect if there are "multiple" frontpage courses and fix them if needed
    $frontcourses = $DB->get_records('course', array('category'=>0), 'id');
    if (count($frontcourses) > 1) {
        if (isset($frontcourses[SITEID])) {
            $frontcourse = $frontcourses[SITEID];
            unset($frontcourses[SITEID]);
        } else {
            $frontcourse = array_shift($frontcourses);
        }
        $defaultcat = reset($topcats);
        foreach ($frontcourses as $course) {
            $DB->set_field('course', 'category', $defaultcat->id, array('id'=>$course->id));
            $context = context_course::instance($course->id);
            $fixcontexts[$context->id] = $context;
            $cacheevents['changesincourse'] = true;
        }
        unset($frontcourses);
    } else {
        $frontcourse = reset($frontcourses);
    }

    // now fix the paths and depths in context table if needed
    if ($fixcontexts) {
        foreach ($fixcontexts as $fixcontext) {
            $fixcontext->reset_paths(false);
        }
        context_helper::build_all_paths(false);
        unset($fixcontexts);
        $cacheevents['changesincourse'] = true;
        $cacheevents['changesincoursecat'] = true;
    }

    // release memory
    unset($topcats);
    unset($brokencats);
    unset($fixcontexts);

    // fix frontpage course sortorder
    if ($frontcourse->sortorder != 1) {
        $DB->set_field('course', 'sortorder', 1, array('id'=>$frontcourse->id));
        $cacheevents['changesincourse'] = true;
    }

    // now fix the course counts in category records if needed
    $sql = "SELECT cc.id, cc.coursecount, COUNT(c.id) AS newcount
              FROM {course_categories} cc
              LEFT JOIN {course} c ON c.category = cc.id
          GROUP BY cc.id, cc.coursecount
            HAVING cc.coursecount <> COUNT(c.id)";

    if ($updatecounts = $DB->get_records_sql($sql)) {
        // categories with more courses than MAX_COURSES_IN_CATEGORY
        $categories = array();
        foreach ($updatecounts as $cat) {
            $cat->coursecount = $cat->newcount;
            if ($cat->coursecount >= MAX_COURSES_IN_CATEGORY) {
                $categories[] = $cat->id;
            }
            unset($cat->newcount);
            $DB->update_record_raw('course_categories', $cat, true);
        }
        if (!empty($categories)) {
            $str = implode(', ', $categories);
            debugging("The number of courses (category id: $str) has reached MAX_COURSES_IN_CATEGORY (" . MAX_COURSES_IN_CATEGORY . "), it will cause a sorting performance issue, please increase the value of MAX_COURSES_IN_CATEGORY in lib/datalib.php file. See tracker issue: MDL-25669", DEBUG_DEVELOPER);
        }
        $cacheevents['changesincoursecat'] = true;
    }

    // now make sure that sortorders in course table are withing the category sortorder ranges
    $sql = "SELECT DISTINCT cc.id, cc.sortorder
              FROM {course_categories} cc
              JOIN {course} c ON c.category = cc.id
             WHERE c.sortorder < cc.sortorder OR c.sortorder > cc.sortorder + ".MAX_COURSES_IN_CATEGORY;

    if ($fixcategories = $DB->get_records_sql($sql)) {
        //fix the course sortorder ranges
        foreach ($fixcategories as $cat) {
            $sql = "UPDATE {course}
                       SET sortorder = ".$DB->sql_modulo('sortorder', MAX_COURSES_IN_CATEGORY)." + ?
                     WHERE category = ?";
            $DB->execute($sql, array($cat->sortorder, $cat->id));
        }
        $cacheevents['changesincoursecat'] = true;
    }
    unset($fixcategories);

    // categories having courses with sortorder duplicates or having gaps in sortorder
    $sql = "SELECT DISTINCT c1.category AS id , cc.sortorder
              FROM {course} c1
              JOIN {course} c2 ON c1.sortorder = c2.sortorder
              JOIN {course_categories} cc ON (c1.category = cc.id)
             WHERE c1.id <> c2.id";
    $fixcategories = $DB->get_records_sql($sql);

    $sql = "SELECT cc.id, cc.sortorder, cc.coursecount, MAX(c.sortorder) AS maxsort, MIN(c.sortorder) AS minsort
              FROM {course_categories} cc
              JOIN {course} c ON c.category = cc.id
          GROUP BY cc.id, cc.sortorder, cc.coursecount
            HAVING (MAX(c.sortorder) <>  cc.sortorder + cc.coursecount) OR (MIN(c.sortorder) <>  cc.sortorder + 1)";
    $gapcategories = $DB->get_records_sql($sql);

    foreach ($gapcategories as $cat) {
        if (isset($fixcategories[$cat->id])) {
            // duplicates detected already

        } else if ($cat->minsort == $cat->sortorder and $cat->maxsort == $cat->sortorder + $cat->coursecount - 1) {
            // easy - new course inserted with sortorder 0, the rest is ok
            $sql = "UPDATE {course}
                       SET sortorder = sortorder + 1
                     WHERE category = ?";
            $DB->execute($sql, array($cat->id));

        } else {
            // it needs full resorting
            $fixcategories[$cat->id] = $cat;
        }
        $cacheevents['changesincourse'] = true;
    }
    unset($gapcategories);

    // fix course sortorders in problematic categories only
    foreach ($fixcategories as $cat) {
        $i = 1;
        $courses = $DB->get_records('course', array('category'=>$cat->id), 'sortorder ASC, id DESC', 'id, sortorder');
        foreach ($courses as $course) {
            if ($course->sortorder != $cat->sortorder + $i) {
                $course->sortorder = $cat->sortorder + $i;
                $DB->update_record_raw('course', $course, true);
                $cacheevents['changesincourse'] = true;
            }
            $i++;
        }
    }

    // advise all caches that need to be rebuilt
    foreach (array_keys($cacheevents) as $event) {
        cache_helper::purge_by_event($event);
    }
}

/**
 * Internal recursive category verification function, do not use directly!
 *
 * @todo Document the arguments of this function better
 *
 * @global object
 * @uses MAX_COURSES_IN_CATEGORY
 * @uses CONTEXT_COURSECAT
 * @param array $children
 * @param int $sortorder
 * @param string $parent
 * @param int $depth
 * @param string $path
 * @param array $fixcontexts
 * @return bool if changes were made
 */
function _fix_course_cats($children, &$sortorder, $parent, $depth, $path, &$fixcontexts) {
    global $DB;

    $depth++;
    $changesmade = false;

    foreach ($children as $cat) {
        $sortorder = $sortorder + MAX_COURSES_IN_CATEGORY;
        $update = false;
        if ($parent != $cat->parent or $depth != $cat->depth or $path.'/'.$cat->id != $cat->path) {
            $cat->parent = $parent;
            $cat->depth  = $depth;
            $cat->path   = $path.'/'.$cat->id;
            $update = true;

            // make sure context caches are rebuild and dirty contexts marked
            $context = context_coursecat::instance($cat->id);
            $fixcontexts[$context->id] = $context;
        }
        if ($cat->sortorder != $sortorder) {
            $cat->sortorder = $sortorder;
            $update = true;
        }
        if ($update) {
            $DB->update_record('course_categories', $cat, true);
            $changesmade = true;
        }
        if (isset($cat->children)) {
            if (_fix_course_cats($cat->children, $sortorder, $cat->id, $cat->depth, $cat->path, $fixcontexts)) {
                $changesmade = true;
            }
        }
    }
    return $changesmade;
}

/**
 * List of remote courses that a user has access to via MNET.
 * Works only on the IDP
 *
 * @global object
 * @global object
 * @param int @userid The user id to get remote courses for
 * @return array Array of {@link $COURSE} of course objects
 */
function get_my_remotecourses($userid=0) {
    global $DB, $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    // we can not use SELECT DISTINCT + text field (summary) because of MS SQL and Oracle, subselect used therefore
    $sql = "SELECT c.id, c.remoteid, c.shortname, c.fullname,
                   c.hostid, c.summary, c.summaryformat, c.categoryname AS cat_name,
                   h.name AS hostname
              FROM {mnetservice_enrol_courses} c
              JOIN (SELECT DISTINCT hostid, remotecourseid
                      FROM {mnetservice_enrol_enrolments}
                     WHERE userid = ?
                   ) e ON (e.hostid = c.hostid AND e.remotecourseid = c.remoteid)
              JOIN {mnet_host} h ON h.id = c.hostid";

    return $DB->get_records_sql($sql, array($userid));
}

/**
 * List of remote hosts that a user has access to via MNET.
 * Works on the SP
 *
 * @global object
 * @global object
 * @return array|bool Array of host objects or false
 */
function get_my_remotehosts() {
    global $CFG, $USER;

    if ($USER->mnethostid == $CFG->mnet_localhost_id) {
        return false; // Return nothing on the IDP
    }
    if (!empty($USER->mnet_foreign_host_array) && is_array($USER->mnet_foreign_host_array)) {
        return $USER->mnet_foreign_host_array;
    }
    return false;
}

/**
 * This function creates a default separated/connected scale
 *
 * This function creates a default separated/connected scale
 * so there's something in the database.  The locations of
 * strings and files is a bit odd, but this is because we
 * need to maintain backward compatibility with many different
 * existing language translations and older sites.
 *
 * @global object
 * @return void
 */
function make_default_scale() {
    global $DB;

    $defaultscale = new stdClass();
    $defaultscale->courseid = 0;
    $defaultscale->userid = 0;
    $defaultscale->name  = get_string('separateandconnected');
    $defaultscale->description = get_string('separateandconnectedinfo');
    $defaultscale->scale = get_string('postrating1', 'forum').','.
                           get_string('postrating2', 'forum').','.
                           get_string('postrating3', 'forum');
    $defaultscale->timemodified = time();

    $defaultscale->id = $DB->insert_record('scale', $defaultscale);
    $DB->execute("UPDATE {forum} SET scale = ?", array($defaultscale->id));
}


/**
 * Returns a menu of all available scales from the site as well as the given course
 *
 * @global object
 * @param int $courseid The id of the course as found in the 'course' table.
 * @return array
 */
function get_scales_menu($courseid=0) {
    global $DB;

    $sql = "SELECT id, name
              FROM {scale}
             WHERE courseid = 0 or courseid = ?
          ORDER BY courseid ASC, name ASC";
    $params = array($courseid);

    if ($scales = $DB->get_records_sql_menu($sql, $params)) {
        return $scales;
    }

    make_default_scale();

    return $DB->get_records_sql_menu($sql, $params);
}



/**
 * Given a set of timezone records, put them in the database,  replacing what is there
 *
 * @global object
 * @param array $timezones An array of timezone records
 * @return void
 */
function update_timezone_records($timezones) {
    global $DB;

/// Clear out all the old stuff
    $DB->delete_records('timezone');

/// Insert all the new stuff
    foreach ($timezones as $timezone) {
        if (is_array($timezone)) {
            $timezone = (object)$timezone;
        }
        $DB->insert_record('timezone', $timezone);
    }
}


/// MODULE FUNCTIONS /////////////////////////////////////////////////

/**
 * Just gets a raw list of all modules in a course
 *
 * @global object
 * @param int $courseid The id of the course as found in the 'course' table.
 * @return array
 */
function get_course_mods($courseid) {
    global $DB;

    if (empty($courseid)) {
        return false; // avoid warnings
    }

    return $DB->get_records_sql("SELECT cm.*, m.name as modname
                                   FROM {modules} m, {course_modules} cm
                                  WHERE cm.course = ? AND cm.module = m.id AND m.visible = 1",
                                array($courseid)); // no disabled mods
}


/**
 * Given an id of a course module, finds the coursemodule description
 *
 * @global object
 * @param string $modulename name of module type, eg. resource, assignment,... (optional, slower and less safe if not specified)
 * @param int $cmid course module id (id in course_modules table)
 * @param int $courseid optional course id for extra validation
 * @param bool $sectionnum include relative section number (0,1,2 ...)
 * @param int $strictness IGNORE_MISSING means compatible mode, false returned if record not found, debug message if more found;
 *                        IGNORE_MULTIPLE means return first, ignore multiple records found(not recommended);
 *                        MUST_EXIST means throw exception if no record or multiple records found
 * @return stdClass
 */
function get_coursemodule_from_id($modulename, $cmid, $courseid=0, $sectionnum=false, $strictness=IGNORE_MISSING) {
    global $DB;

    $params = array('cmid'=>$cmid);

    if (!$modulename) {
        if (!$modulename = $DB->get_field_sql("SELECT md.name
                                                 FROM {modules} md
                                                 JOIN {course_modules} cm ON cm.module = md.id
                                                WHERE cm.id = :cmid", $params, $strictness)) {
            return false;
        }
    }

    $params['modulename'] = $modulename;

    $courseselect = "";
    $sectionfield = "";
    $sectionjoin  = "";

    if ($courseid) {
        $courseselect = "AND cm.course = :courseid";
        $params['courseid'] = $courseid;
    }

    if ($sectionnum) {
        $sectionfield = ", cw.section AS sectionnum";
        $sectionjoin  = "LEFT JOIN {course_sections} cw ON cw.id = cm.section";
    }

    $sql = "SELECT cm.*, m.name, md.name AS modname $sectionfield
              FROM {course_modules} cm
                   JOIN {modules} md ON md.id = cm.module
                   JOIN {".$modulename."} m ON m.id = cm.instance
                   $sectionjoin
             WHERE cm.id = :cmid AND md.name = :modulename
                   $courseselect";

    return $DB->get_record_sql($sql, $params, $strictness);
}

/**
 * Given an instance number of a module, finds the coursemodule description
 *
 * @global object
 * @param string $modulename name of module type, eg. resource, assignment,...
 * @param int $instance module instance number (id in resource, assignment etc. table)
 * @param int $courseid optional course id for extra validation
 * @param bool $sectionnum include relative section number (0,1,2 ...)
 * @param int $strictness IGNORE_MISSING means compatible mode, false returned if record not found, debug message if more found;
 *                        IGNORE_MULTIPLE means return first, ignore multiple records found(not recommended);
 *                        MUST_EXIST means throw exception if no record or multiple records found
 * @return stdClass
 */
function get_coursemodule_from_instance($modulename, $instance, $courseid=0, $sectionnum=false, $strictness=IGNORE_MISSING) {
    global $DB;

    $params = array('instance'=>$instance, 'modulename'=>$modulename);

    $courseselect = "";
    $sectionfield = "";
    $sectionjoin  = "";

    if ($courseid) {
        $courseselect = "AND cm.course = :courseid";
        $params['courseid'] = $courseid;
    }

    if ($sectionnum) {
        $sectionfield = ", cw.section AS sectionnum";
        $sectionjoin  = "LEFT JOIN {course_sections} cw ON cw.id = cm.section";
    }

    $sql = "SELECT cm.*, m.name, md.name AS modname $sectionfield
              FROM {course_modules} cm
                   JOIN {modules} md ON md.id = cm.module
                   JOIN {".$modulename."} m ON m.id = cm.instance
                   $sectionjoin
             WHERE m.id = :instance AND md.name = :modulename
                   $courseselect";

    return $DB->get_record_sql($sql, $params, $strictness);
}

/**
 * Returns all course modules of given activity in course
 *
 * @param string $modulename The module name (forum, quiz, etc.)
 * @param int $courseid The course id to get modules for
 * @param string $extrafields extra fields starting with m.
 * @return array Array of results
 */
function get_coursemodules_in_course($modulename, $courseid, $extrafields='') {
    global $DB;

    if (!empty($extrafields)) {
        $extrafields = ", $extrafields";
    }
    $params = array();
    $params['courseid'] = $courseid;
    $params['modulename'] = $modulename;


    return $DB->get_records_sql("SELECT cm.*, m.name, md.name as modname $extrafields
                                   FROM {course_modules} cm, {modules} md, {".$modulename."} m
                                  WHERE cm.course = :courseid AND
                                        cm.instance = m.id AND
                                        md.name = :modulename AND
                                        md.id = cm.module", $params);
}

/**
 * Returns an array of all the active instances of a particular module in given courses, sorted in the order they are defined
 *
 * Returns an array of all the active instances of a particular
 * module in given courses, sorted in the order they are defined
 * in the course. Returns an empty array on any errors.
 *
 * The returned objects includle the columns cw.section, cm.visible,
 * cm.groupmode and cm.groupingid, cm.groupmembersonly, and are indexed by cm.id.
 *
 * @global object
 * @global object
 * @param string $modulename The name of the module to get instances for
 * @param array $courses an array of course objects.
 * @param int $userid
 * @param int $includeinvisible
 * @return array of module instance objects, including some extra fields from the course_modules
 *          and course_sections tables, or an empty array if an error occurred.
 */
function get_all_instances_in_courses($modulename, $courses, $userid=NULL, $includeinvisible=false) {
    global $CFG, $DB;

    $outputarray = array();

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return $outputarray;
    }

    list($coursessql, $params) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED, 'c0');
    $params['modulename'] = $modulename;

    if (!$rawmods = $DB->get_records_sql("SELECT cm.id AS coursemodule, m.*, cw.section, cm.visible AS visible,
                                                 cm.groupmode, cm.groupingid, cm.groupmembersonly
                                            FROM {course_modules} cm, {course_sections} cw, {modules} md,
                                                 {".$modulename."} m
                                           WHERE cm.course $coursessql AND
                                                 cm.instance = m.id AND
                                                 cm.section = cw.id AND
                                                 md.name = :modulename AND
                                                 md.id = cm.module", $params)) {
        return $outputarray;
    }

    foreach ($courses as $course) {
        $modinfo = get_fast_modinfo($course, $userid);

        if (empty($modinfo->instances[$modulename])) {
            continue;
        }

        foreach ($modinfo->instances[$modulename] as $cm) {
            if (!$includeinvisible and !$cm->uservisible) {
                continue;
            }
            if (!isset($rawmods[$cm->id])) {
                continue;
            }
            $instance = $rawmods[$cm->id];
            if (!empty($cm->extra)) {
                $instance->extra = $cm->extra;
            }
            $outputarray[] = $instance;
        }
    }

    return $outputarray;
}

/**
 * Returns an array of all the active instances of a particular module in a given course,
 * sorted in the order they are defined.
 *
 * Returns an array of all the active instances of a particular
 * module in a given course, sorted in the order they are defined
 * in the course. Returns an empty array on any errors.
 *
 * The returned objects includle the columns cw.section, cm.visible,
 * cm.groupmode and cm.groupingid, cm.groupmembersonly, and are indexed by cm.id.
 *
 * Simply calls {@link all_instances_in_courses()} with a single provided course
 *
 * @param string $modulename The name of the module to get instances for
 * @param object $course The course obect.
 * @return array of module instance objects, including some extra fields from the course_modules
 *          and course_sections tables, or an empty array if an error occurred.
 * @param int $userid
 * @param int $includeinvisible
 */
function get_all_instances_in_course($modulename, $course, $userid=NULL, $includeinvisible=false) {
    return get_all_instances_in_courses($modulename, array($course->id => $course), $userid, $includeinvisible);
}


/**
 * Determine whether a module instance is visible within a course
 *
 * Given a valid module object with info about the id and course,
 * and the module's type (eg "forum") returns whether the object
 * is visible or not, groupmembersonly visibility not tested
 *
 * @global object

 * @param $moduletype Name of the module eg 'forum'
 * @param $module Object which is the instance of the module
 * @return bool Success
 */
function instance_is_visible($moduletype, $module) {
    global $DB;

    if (!empty($module->id)) {
        $params = array('courseid'=>$module->course, 'moduletype'=>$moduletype, 'moduleid'=>$module->id);
        if ($records = $DB->get_records_sql("SELECT cm.instance, cm.visible, cm.groupingid, cm.id, cm.groupmembersonly, cm.course
                                               FROM {course_modules} cm, {modules} m
                                              WHERE cm.course = :courseid AND
                                                    cm.module = m.id AND
                                                    m.name = :moduletype AND
                                                    cm.instance = :moduleid", $params)) {

            foreach ($records as $record) { // there should only be one - use the first one
                return $record->visible;
            }
        }
    }
    return true;  // visible by default!
}

/**
 * Determine whether a course module is visible within a course,
 * this is different from instance_is_visible() - faster and visibility for user
 *
 * @global object
 * @global object
 * @uses DEBUG_DEVELOPER
 * @uses CONTEXT_MODULE
 * @uses CONDITION_MISSING_EXTRATABLE
 * @param object $cm object
 * @param int $userid empty means current user
 * @return bool Success
 */
function coursemodule_visible_for_user($cm, $userid=0) {
    global $USER,$CFG;

    if (empty($cm->id)) {
        debugging("Incorrect course module parameter!", DEBUG_DEVELOPER);
        return false;
    }
    if (empty($userid)) {
        $userid = $USER->id;
    }
    if (!$cm->visible and !has_capability('moodle/course:viewhiddenactivities', context_module::instance($cm->id), $userid)) {
        return false;
    }
    if ($CFG->enableavailability) {
        require_once($CFG->libdir.'/conditionlib.php');
        $ci=new condition_info($cm,CONDITION_MISSING_EXTRATABLE);
        if(!$ci->is_available($cm->availableinfo,false,$userid) and
            !has_capability('moodle/course:viewhiddenactivities',
                context_module::instance($cm->id), $userid)) {
            return false;
        }
    }
    return groups_course_module_visible($cm, $userid);
}




/// LOG FUNCTIONS /////////////////////////////////////////////////////


/**
 * Add an entry to the log table.
 *
 * Add an entry to the log table.  These are "action" focussed rather
 * than web server hits, and provide a way to easily reconstruct what
 * any particular student has been doing.
 *
 * @package core
 * @category log
 * @global moodle_database $DB
 * @global stdClass $CFG
 * @global stdClass $USER
 * @uses SITEID
 * @uses DEBUG_DEVELOPER
 * @uses DEBUG_ALL
 * @param    int     $courseid  The course id
 * @param    string  $module  The module name  e.g. forum, journal, resource, course, user etc
 * @param    string  $action  'view', 'update', 'add' or 'delete', possibly followed by another word to clarify.
 * @param    string  $url     The file and parameters used to see the results of the action
 * @param    string  $info    Additional description information
 * @param    string  $cm      The course_module->id if there is one
 * @param    string  $user    If log regards $user other than $USER
 * @return void
 */
function add_to_log($courseid, $module, $action, $url='', $info='', $cm=0, $user=0) {
    // Note that this function intentionally does not follow the normal Moodle DB access idioms.
    // This is for a good reason: it is the most frequently used DB update function,
    // so it has been optimised for speed.
    global $DB, $CFG, $USER;

    if ($cm === '' || is_null($cm)) { // postgres won't translate empty string to its default
        $cm = 0;
    }

    if ($user) {
        $userid = $user;
    } else {
        if (session_is_loggedinas()) {  // Don't log
            return;
        }
        $userid = empty($USER->id) ? '0' : $USER->id;
    }

    if (isset($CFG->logguests) and !$CFG->logguests) {
        if (!$userid or isguestuser($userid)) {
            return;
        }
    }

    $REMOTE_ADDR = getremoteaddr();

    $timenow = time();
    $info = $info;
    if (!empty($url)) { // could break doing html_entity_decode on an empty var.
        $url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
    } else {
        $url = '';
    }

    // Restrict length of log lines to the space actually available in the
    // database so that it doesn't cause a DB error. Log a warning so that
    // developers can avoid doing things which are likely to cause this on a
    // routine basis.
    if(!empty($info) && textlib::strlen($info)>255) {
        $info = textlib::substr($info,0,252).'...';
        debugging('Warning: logged very long info',DEBUG_DEVELOPER);
    }

    // If the 100 field size is changed, also need to alter print_log in course/lib.php
    if(!empty($url) && textlib::strlen($url)>100) {
        $url = textlib::substr($url,0,97).'...';
        debugging('Warning: logged very long URL',DEBUG_DEVELOPER);
    }

    if (defined('MDL_PERFDB')) { global $PERF ; $PERF->logwrites++;};

    $log = array('time'=>$timenow, 'userid'=>$userid, 'course'=>$courseid, 'ip'=>$REMOTE_ADDR, 'module'=>$module,
                 'cmid'=>$cm, 'action'=>$action, 'url'=>$url, 'info'=>$info);

    try {
        $DB->insert_record_raw('log', $log, false);
    } catch (dml_exception $e) {
        debugging('Error: Could not insert a new entry to the Moodle log. '. $e->error, DEBUG_ALL);

        // MDL-11893, alert $CFG->supportemail if insert into log failed
        if ($CFG->supportemail and empty($CFG->noemailever)) {
            // email_to_user is not usable because email_to_user tries to write to the logs table,
            // and this will get caught in an infinite loop, if disk is full
            $site = get_site();
            $subject = 'Insert into log failed at your moodle site '.$site->fullname;
            $message = "Insert into log table failed at ". date('l dS \of F Y h:i:s A') .".\n It is possible that your disk is full.\n\n";
            $message .= "The failed query parameters are:\n\n" . var_export($log, true);

            $lasttime = get_config('admin', 'lastloginserterrormail');
            if(empty($lasttime) || time() - $lasttime > 60*60*24) { // limit to 1 email per day
                //using email directly rather than messaging as they may not be able to log in to access a message
                mail($CFG->supportemail, $subject, $message);
                set_config('lastloginserterrormail', time(), 'admin');
            }
        }
    }
}

/**
 * Store user last access times - called when use enters a course or site
 *
 * @package core
 * @category log
 * @global stdClass $USER
 * @global stdClass $CFG
 * @global moodle_database $DB
 * @uses LASTACCESS_UPDATE_SECS
 * @uses SITEID
 * @param int $courseid  empty courseid means site
 * @return void
 */
function user_accesstime_log($courseid=0) {
    global $USER, $CFG, $DB;

    if (!isloggedin() or session_is_loggedinas()) {
        // no access tracking
        return;
    }

    if (isguestuser()) {
        // Do not update guest access times/ips for performance.
        return;
    }

    if (empty($courseid)) {
        $courseid = SITEID;
    }

    $timenow = time();

/// Store site lastaccess time for the current user
    if ($timenow - $USER->lastaccess > LASTACCESS_UPDATE_SECS) {
    /// Update $USER->lastaccess for next checks
        $USER->lastaccess = $timenow;

        $last = new stdClass();
        $last->id         = $USER->id;
        $last->lastip     = getremoteaddr();
        $last->lastaccess = $timenow;

        $DB->update_record_raw('user', $last);
    }

    if ($courseid == SITEID) {
    ///  no user_lastaccess for frontpage
        return;
    }

/// Store course lastaccess times for the current user
    if (empty($USER->currentcourseaccess[$courseid]) or ($timenow - $USER->currentcourseaccess[$courseid] > LASTACCESS_UPDATE_SECS)) {

        $lastaccess = $DB->get_field('user_lastaccess', 'timeaccess', array('userid'=>$USER->id, 'courseid'=>$courseid));

        if ($lastaccess === false) {
            // Update course lastaccess for next checks
            $USER->currentcourseaccess[$courseid] = $timenow;

            $last = new stdClass();
            $last->userid     = $USER->id;
            $last->courseid   = $courseid;
            $last->timeaccess = $timenow;
            $DB->insert_record_raw('user_lastaccess', $last, false);

        } else if ($timenow - $lastaccess <  LASTACCESS_UPDATE_SECS) {
            // no need to update now, it was updated recently in concurrent login ;-)

        } else {
            // Update course lastaccess for next checks
            $USER->currentcourseaccess[$courseid] = $timenow;

            $DB->set_field('user_lastaccess', 'timeaccess', $timenow, array('userid'=>$USER->id, 'courseid'=>$courseid));
        }
    }
}

/**
 * Select all log records based on SQL criteria
 *
 * @package core
 * @category log
 * @global moodle_database $DB
 * @param string $select SQL select criteria
 * @param array $params named sql type params
 * @param string $order SQL order by clause to sort the records returned
 * @param string $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set)
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set)
 * @param int $totalcount Passed in by reference.
 * @return array
 */
function get_logs($select, array $params=null, $order='l.time DESC', $limitfrom='', $limitnum='', &$totalcount) {
    global $DB;

    if ($order) {
        $order = "ORDER BY $order";
    }

    $selectsql = "";
    $countsql  = "";

    if ($select) {
        $select = "WHERE $select";
    }

    $sql = "SELECT COUNT(*)
              FROM {log} l
           $select";

    $totalcount = $DB->count_records_sql($sql, $params);

    $sql = "SELECT l.*, u.firstname, u.lastname, u.picture
              FROM {log} l
              LEFT JOIN {user} u ON l.userid = u.id
           $select
            $order";

    return $DB->get_records_sql($sql, $params, $limitfrom, $limitnum) ;
}


/**
 * Select all log records for a given course and user
 *
 * @package core
 * @category log
 * @global moodle_database $DB
 * @uses DAYSECS
 * @param int $userid The id of the user as found in the 'user' table.
 * @param int $courseid The id of the course as found in the 'course' table.
 * @param string $coursestart unix timestamp representing course start date and time.
 * @return array
 */
function get_logs_usercourse($userid, $courseid, $coursestart) {
    global $DB;

    $params = array();

    $courseselect = '';
    if ($courseid) {
        $courseselect = "AND course = :courseid";
        $params['courseid'] = $courseid;
    }
    $params['userid'] = $userid;
    $$coursestart = (int)$coursestart; // note: unfortunately pg complains if you use name parameter or column alias in GROUP BY

    return $DB->get_records_sql("SELECT FLOOR((time - $coursestart)/". DAYSECS .") AS day, COUNT(*) AS num
                                   FROM {log}
                                  WHERE userid = :userid
                                        AND time > $coursestart $courseselect
                               GROUP BY FLOOR((time - $coursestart)/". DAYSECS .")", $params);
}

/**
 * Select all log records for a given course, user, and day
 *
 * @package core
 * @category log
 * @global moodle_database $DB
 * @uses HOURSECS
 * @param int $userid The id of the user as found in the 'user' table.
 * @param int $courseid The id of the course as found in the 'course' table.
 * @param string $daystart unix timestamp of the start of the day for which the logs needs to be retrived
 * @return array
 */
function get_logs_userday($userid, $courseid, $daystart) {
    global $DB;

    $params = array('userid'=>$userid);

    $courseselect = '';
    if ($courseid) {
        $courseselect = "AND course = :courseid";
        $params['courseid'] = $courseid;
    }
    $daystart = (int)$daystart; // note: unfortunately pg complains if you use name parameter or column alias in GROUP BY

    return $DB->get_records_sql("SELECT FLOOR((time - $daystart)/". HOURSECS .") AS hour, COUNT(*) AS num
                                   FROM {log}
                                  WHERE userid = :userid
                                        AND time > $daystart $courseselect
                               GROUP BY FLOOR((time - $daystart)/". HOURSECS .") ", $params);
}

/**
 * Returns an object with counts of failed login attempts
 *
 * Returns information about failed login attempts.  If the current user is
 * an admin, then two numbers are returned:  the number of attempts and the
 * number of accounts.  For non-admins, only the attempts on the given user
 * are shown.
 *
 * @global moodle_database $DB
 * @uses CONTEXT_SYSTEM
 * @param string $mode Either 'admin' or 'everybody'
 * @param string $username The username we are searching for
 * @param string $lastlogin The date from which we are searching
 * @return int
 */
function count_login_failures($mode, $username, $lastlogin) {
    global $DB;

    $params = array('mode'=>$mode, 'username'=>$username, 'lastlogin'=>$lastlogin);
    $select = "module='login' AND action='error' AND time > :lastlogin";

    $count = new stdClass();

    if (is_siteadmin()) {
        if ($count->attempts = $DB->count_records_select('log', $select, $params)) {
            $count->accounts = $DB->count_records_select('log', $select, $params, 'COUNT(DISTINCT info)');
            return $count;
        }
    } else if ($mode == 'everybody') {
        if ($count->attempts = $DB->count_records_select('log', "$select AND info = :username", $params)) {
            return $count;
        }
    }
    return NULL;
}


/// GENERAL HELPFUL THINGS  ///////////////////////////////////

/**
 * Dumps a given object's information for debugging purposes
 *
 * When used in a CLI script, the object's information is written to the standard
 * error output stream. When used in a web script, the object is dumped to a
 * pre-formatted block with the "notifytiny" CSS class.
 *
 * @param mixed $object The data to be printed
 * @return void output is echo'd
 */
function print_object($object) {

    // we may need a lot of memory here
    raise_memory_limit(MEMORY_EXTRA);

    if (CLI_SCRIPT) {
        fwrite(STDERR, print_r($object, true));
        fwrite(STDERR, PHP_EOL);
    } else {
        echo html_writer::tag('pre', s(print_r($object, true)), array('class' => 'notifytiny'));
    }
}

/**
 * This function is the official hook inside XMLDB stuff to delegate its debug to one
 * external function.
 *
 * Any script can avoid calls to this function by defining XMLDB_SKIP_DEBUG_HOOK before
 * using XMLDB classes. Obviously, also, if this function doesn't exist, it isn't invoked ;-)
 *
 * @uses DEBUG_DEVELOPER
 * @param string $message string contains the error message
 * @param object $object object XMLDB object that fired the debug
 */
function xmldb_debug($message, $object) {

    debugging($message, DEBUG_DEVELOPER);
}

/**
 * @global object
 * @uses CONTEXT_COURSECAT
 * @return boolean Whether the user can create courses in any category in the system.
 */
function user_can_create_courses() {
    global $DB;
    $catsrs = $DB->get_recordset('course_categories');
    foreach ($catsrs as $cat) {
        if (has_capability('moodle/course:create', context_coursecat::instance($cat->id))) {
            $catsrs->close();
            return true;
        }
    }
    $catsrs->close();
    return false;
}
