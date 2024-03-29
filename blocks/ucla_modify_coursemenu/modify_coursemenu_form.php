<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class ucla_modify_coursemenu_form extends moodleform {
    /**
     *  This is going to serve as a proxy for our custom UI.
     **/
    function definition() {
        $mform =& $this->_form;

        $courseid  = $this->_customdata['courseid'];
        $section = $this->_customdata['section'];
        $landing_page = $this->_customdata['landing_page'];
        $hide_autogenerated_content_default = $this->_customdata['hide_autogenerated_content'];

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);
        
        $mform->addElement('hidden', 'section', $section);
        $mform->setType('section', PARAM_INT);

        $mform->addElement('hidden', 'newsections', '',
            array('id' => block_ucla_modify_coursemenu::newnodes_domnode));
        $mform->setType('newsections', PARAM_TEXT);
        
        $mform->addElement('hidden', 'sectionsorder', '',
            array(
                'id' => block_ucla_modify_coursemenu::sectionsorder_domnode
            ));
        $mform->setType('sectionsorder', PARAM_TEXT);

        $mform->addElement('hidden', 'landingpage', $landing_page,
            array(
                'id' => block_ucla_modify_coursemenu::landingpage_domnode
            ));
        $mform->setType('landingpage', PARAM_TEXT);

        $mform->addElement('hidden', 'serialized', '',
            array(
                'id' => block_ucla_modify_coursemenu::serialized_domnode
            ));
        $mform->setType('serialized', PARAM_TEXT);

        $mform->addElement('html', html_writer::tag('div',
            get_string('javascriptrequired', 'group'), array('id' => 
                block_ucla_modify_coursemenu::primary_domnode)));
        
        $mform->addElement('button', 'addsectionbutton', 
            get_string('addnewsection', 'block_ucla_modify_coursemenu'),
            array('id' => block_ucla_modify_coursemenu::add_section_button));
        
        $mform->addElement('header', 'additional_options', get_string('additional_options',
            'block_ucla_modify_coursemenu'));        
        
        $mform->addElement('advcheckbox', 'hideautogeneratedcontent', '',
            get_string('hideautogeneratedcontent', 'block_ucla_modify_coursemenu') .
                ' ( ' . html_writer::tag('span', '', array('class' => 'glyphicon glyphicon-th-list')) . ' )');
        $mform->setDefault('hideautogeneratedcontent', $hide_autogenerated_content_default);
        
        $this->add_action_buttons();
    }
}   
