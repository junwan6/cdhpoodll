<?php
/*
 * Upgrades block.
 */

defined('MOODLE_INTERNAL') || die();

/**
 *  Runs extra commands when upgrading.
 **/
function xmldb_local_ucla_upgrade($oldversion=0) {
    global $CFG, $DB, $OUTPUT;
    $dbman = $DB->get_manager();

    $result = true;
    if ($oldversion < 2012012700) {
        // Define table ucla_reg_subjectarea to be created
        $table = new xmldb_table('ucla_reg_subjectarea');

        // Adding fields to table ucla_reg_subjectarea
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('subjarea', XMLDB_TYPE_CHAR, '7', null, XMLDB_NOTNULL, null, null);
        $table->add_field('subj_area_full', XMLDB_TYPE_CHAR, '60', null, XMLDB_NOTNULL, null, null);
        $table->add_field('home', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('modified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);

        // Adding keys to table ucla_reg_subjectarea
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table ucla_reg_subjectarea
        $table->add_index('dexs', XMLDB_INDEX_NOTUNIQUE, array('subjarea'));

        // Conditionally launch create table for ucla_reg_subjectarea
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table ucla_rolemapping to be created
        $table = new xmldb_table('ucla_rolemapping');

        // Adding fields to table ucla_rolemapping
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('pseudo_role', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('moodle_roleid', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('subject_area', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, '*SYSTEM*');

        // Adding keys to table ucla_rolemapping
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for ucla_rolemapping
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // ucla savepoint reached
        upgrade_plugin_savepoint(true, 2012012700, 'local', 'ucla');
    }

    if ($oldversion < 2012012701) {
        // Define table ucla_reg_classinfo to be created
        $table = new xmldb_table('ucla_reg_classinfo');

        // Adding fields to table ucla_reg_classinfo
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('subj_area', XMLDB_TYPE_CHAR, '7', null, null, null, null);
        $table->add_field('coursenum', XMLDB_TYPE_CHAR, '8', null, null, null, null);
        $table->add_field('sectnum', XMLDB_TYPE_CHAR, '6', null, null, null, null);
        $table->add_field('crsidx', XMLDB_TYPE_CHAR, '8', null, null, null, null);
        $table->add_field('classidx', XMLDB_TYPE_CHAR, '6', null, null, null, null);
        $table->add_field('secidx', XMLDB_TYPE_CHAR, '6', null, null, null, null);
        $table->add_field('secttype', XMLDB_TYPE_CHAR, '1', null, null, null, null);
        $table->add_field('srs', XMLDB_TYPE_CHAR, '9', null, null, null, null);
        $table->add_field('term', XMLDB_TYPE_CHAR, '3', null, null, null, null);
        $table->add_field('division', XMLDB_TYPE_CHAR, '2', null, null, null, null);
        $table->add_field('acttype', XMLDB_TYPE_CHAR, '3', null, null, null, null);
        $table->add_field('coursetitle', XMLDB_TYPE_CHAR, '254', null, null, null, null);
        $table->add_field('sectiontitle', XMLDB_TYPE_CHAR, '240', null, null, null, null);
        $table->add_field('enrolstat', XMLDB_TYPE_CHAR, '1', null, null, null, null);
        $table->add_field('session_group', XMLDB_TYPE_CHAR, '1', null, null, null, null);
        $table->add_field('session', XMLDB_TYPE_CHAR, '2', null, null, null, null);
        $table->add_field('url', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('crs_desc', XMLDB_TYPE_TEXT, 'small', null, null, null, null);

        // Adding keys to table ucla_reg_classinfo
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('mdl_uclaregclas_tersrs_uix', XMLDB_KEY_UNIQUE, array('term', 'srs'));
        $table->add_key('mdl_uclaregclas_tersubcrss_uix', XMLDB_KEY_UNIQUE, array('term', 'subj_area', 'crsidx', 'secidx'));

        // Conditionally launch create table for ucla_reg_classinfo
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        } 

        // ucla savepoint reached
        upgrade_plugin_savepoint(true, 2012012701, 'local', 'ucla');
    }

    if ($oldversion < 2012020100) {
        // Define table ucla_reg_division to be created
        $table = new xmldb_table('ucla_reg_division');

        // Adding fields to table ucla_reg_division
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('code', XMLDB_TYPE_CHAR, '2', null, XMLDB_NOTNULL, null, null);
        $table->add_field('fullname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('home', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, null, null, '0');
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0');

        // Adding keys to table ucla_reg_division
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('code', XMLDB_KEY_UNIQUE, array('code'));

        // Conditionally launch create table for ucla_reg_division
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // ucla savepoint reached
        upgrade_plugin_savepoint(true, 2012020100, 'local', 'ucla');
    }
    
    // CCLE-2669 - Copyright Modifications - add licenses
    if ($oldversion < 2012032705) {
        require_once($CFG->libdir.'/licenselib.php');
        
       // Disable existing licenses
       license_manager::disable('allrightsreserved');
       license_manager::disable('cc');
       license_manager::disable('cc-nc');
       license_manager::disable('cc-nc-nd');
       license_manager::disable('cc-nc-sa');
       license_manager::disable('cc-nd');
       license_manager::disable('cc-sa');
       license_manager::disable('public');
       license_manager::disable('unknown');
       
        // Add new licenses
        $license = new stdClass();
        
        $license->shortname = 'iown';
        $license->fullname = 'I own the copyright';
        $license->source = null;
        $license->enabled = true;        
        $license->version = '2012032200';
        license_manager::add($license);        
        license_manager::enable($license->shortname);
        
        $license->shortname = 'ucown';
        $license->fullname = 'The UC Regents own the copyright';
        $license->source = null;
        $license->enabled = true;        
        $license->version = '2012032200';
        license_manager::add($license);        
        license_manager::enable($license->shortname);
        
        $license->shortname = 'lib';
        $license->fullname = 'Item is licensed by the UCLA Library';
        $license->source = null;
        $license->enabled = true;        
        $license->version = '2012032200';
        license_manager::add($license);        
        license_manager::enable($license->shortname);
        
        $license->shortname = 'public1';
        $license->fullname = 'Item is in the public domain';
        $license->source = 'http://creativecommons.org/licenses/publicdomain/';
        $license->enabled = true;        
        $license->version = '2012032200';
        license_manager::add($license);        
        license_manager::enable($license->shortname);
        
        $license->shortname = 'cc1';
        $license->fullname = 'Item is available for this use via Creative Commons license';
        $license->source = 'http://creativecommons.org/licenses/by/3.0/';
        $license->enabled = true;        
        $license->version = '2012032200';
        license_manager::add($license);        
        license_manager::enable($license->shortname);
        
        $license->shortname = 'obtained';
        $license->fullname = 'I have obtained written permission from the copyright holder';
        $license->source = NULL;
        $license->enabled = true;        
        $license->version = '2012032200';
        license_manager::add($license);        
        license_manager::enable($license->shortname);
        
        $license->shortname = 'fairuse';
        $license->fullname = 'I am using this item under fair use';
        $license->source = NULL;
        $license->enabled = true;        
        $license->version = '2012032200';
        license_manager::add($license);        
        license_manager::enable($license->shortname);
        
        $license->shortname = 'tbd';
        $license->fullname = 'Copyright status not yet identified';
        $license->source = NULL;
        $license->enabled = true;        
        $license->version = '2012032200';
        license_manager::add($license);        
        license_manager::enable($license->shortname);
        
        // ucla savepoint reached
        upgrade_plugin_savepoint(true, 2012032705, 'local', 'ucla');
    }
    
    // CCLE-2669 - Copyright Modifications - changed wording on tbd
    if ($oldversion < 2012060402) {
        require_once($CFG->libdir.'/licenselib.php');
        
        $license->shortname = 'tbd';
        $license->fullname = 'Copyright status not yet identified';
        $license->source = NULL;
        $license->enabled = true;        
        $license->version = '2012060400';
        license_manager::add($license);        
        license_manager::enable($license->shortname);
        
        // ucla savepoint reached
        upgrade_plugin_savepoint(true, 2012060402, 'local', 'ucla');
    }    
    
    if ($oldversion < 2012073000) {
        // Define table ccle_roster_class_cache to be created
        $table = new xmldb_table('ccle_roster_class_cache');

        // Adding fields to table ccle_roster_class_cache
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('param_term', XMLDB_TYPE_CHAR, '3', null, XMLDB_NOTNULL, null, null, 'id');
        $table->add_field('param_srs', XMLDB_TYPE_CHAR, '9', null, XMLDB_NOTNULL, null, null, 'param_term');
        $table->add_field('expires_on', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, 'param_srs');
        $table->add_field('term_cd', XMLDB_TYPE_CHAR, '3', null, null, null, null, 'expires_on');
        $table->add_field('stu_id', XMLDB_TYPE_CHAR, '9', null, null, null, null, 'term_cd');
        $table->add_field('full_name_person', XMLDB_TYPE_CHAR, '70', null, null, null, null, 'stu_id');
        $table->add_field('enrl_stat_cd', XMLDB_TYPE_CHAR, '1', null, null, null, null, 'full_name_person');
        $table->add_field('ss_email_addr', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'enrl_stat_cd');
        $table->add_field('bolid', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'ss_email_addr');

        // Adding keys to table ccle_roster_class_cache
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        
        // Adding indexes to table ccle_roster_class_cache
        $table->add_index('param_index', XMLDB_INDEX_NOTUNIQUE, array('param_term', 'param_srs', 'expires_on'));
        $table->add_index('student_uid_lookup', XMLDB_INDEX_NOTUNIQUE, array('param_term', 'param_srs', 'stu_id'));

        // Conditionally launch create table for ucla_reg_division
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // ucla savepoint reached
        upgrade_plugin_savepoint(true, 2012073000, 'local', 'ucla');
    }    
        
    if ($oldversion < 2012101500) {
        // Define table ccle_get_primary_srs_cache to be created
        $table = new xmldb_table('ccle_get_primary_srs_cache');

        // Adding fields to table ccle_get_primary_srs_cache
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('param_term', XMLDB_TYPE_CHAR, '3', null, XMLDB_NOTNULL, null, null);
        $table->add_field('param_srs', XMLDB_TYPE_CHAR, '9', null, XMLDB_NOTNULL, null, null);
        $table->add_field('expires_on', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('srs_crs_no', XMLDB_TYPE_CHAR, '9', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table ccle_get_primary_srs_cache
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table ccle_get_primary_srs_cache
        $table->add_index('param_index', XMLDB_INDEX_NOTUNIQUE, array('param_term', 'param_srs', 'expires_on'));

        // Conditionally launch create table for ccle_get_primary_srs_cache
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // ucla savepoint reached
        upgrade_plugin_savepoint(true, 2012101500, 'local', 'ucla');
    }

    if ($oldversion < 2013040900) {

        // Define index mdl_uclaregclas_subjarea_ix (not unique) to be added to ucla_reg_classinfo
        $table = new xmldb_table('ucla_reg_classinfo');
        $index = new xmldb_index('mdl_uclaregclas_subjarea_ix', XMLDB_INDEX_NOTUNIQUE, array('subj_area'));

        // Conditionally launch add index mdl_uclaregclas_subjarea_ix
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // ucla savepoint reached
        upgrade_plugin_savepoint(true, 2013040900, 'local', 'ucla');
    }

    if ($oldversion < 2013061000) {
        
        // Define field crs_summary to be added to ucla_reg_classinfo
        $table = new xmldb_table('ucla_reg_classinfo');
        $field = new xmldb_field('crs_summary', XMLDB_TYPE_TEXT, null, null, null, null, null, 'crs_desc');

        // Conditionally launch add field crs_summary
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // ucla savepoint reached
        upgrade_plugin_savepoint(true, 2013061000, 'local', 'ucla');
    }

    return $result;
}

