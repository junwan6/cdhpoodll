<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/course/reset_form.php');


class bulkcoursereset_form extends course_reset_form {
    
    function definition(){
        
        $mform = $this->_form;
        
        $course_list = $this->_customdata['course_list'];
        $course_selected = $this->_customdata['course_selected'];
        
        $mform->addElement('header', 'header_selectcourse', 
                get_string('header_selectcourse', 'tool_uclabulkcoursereset'));
        
        $mform->addElement('select', 'course_list', 
                get_string('course_select', 'tool_uclabulkcoursereset'), $course_list, array('multiple' => 'multiple'));
        $mform->addRule('course_list', get_string('missing_course_select', 'tool_uclabulkcoursereset'), 'required');
        
        if (!empty($course_selected)) {
            $mform->setDefault('course_list', $course_selected);
        }
        
        parent::definition();
    }
    
}
