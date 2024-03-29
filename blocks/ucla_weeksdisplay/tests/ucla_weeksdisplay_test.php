<?php
/**
 * Unit tests for ucla_weeksdisplay.
 */
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); //  It must be included from a Moodle page
}

// Make sure the code being tested is accessible.
global $CFG;
require_once($CFG->dirroot . '/blocks/ucla_weeksdisplay/block_ucla_weeksdisplay.php'); // Include the code to test

/**
 * Extends ucla_session to add some new powers. 
 */
class ucla_session_ext extends ucla_session {
    function __construct($session, $today) {
        parent::__construct($session);
        $this->_today = $today;
    }
    
    function update_today($today) {
        $this->_today = $today;
    }
}

class ucla_weeksdisplay_test extends advanced_testcase {

    protected function setUp() {
        $this->resetAfterTest(true);
    }

    /**
     * Tests the weeks display output for summer 2012 session and rollover
     * into Fall 2012
     * 
     */
    function test_summer_2012() {

        // Test summer 12 sessions A & C
        $today = '2012-06-25';
        
        /// Test ability to set term
        block_ucla_weeksdisplay::init_currentterm($today);
        $this->assertEquals('121', get_config('', 'currentterm'));
        
        // At start of session A
        $query = $this->registrar_query('121');
        $session = new ucla_session_ext($query, $today);
        $session->update();

        // For this particular summer, instruction starts at same time as session begins
        $this->assertEquals('Summer 2012 - Session A, Week 1', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('1', get_config('local_ucla', 'current_week'));
        $this->assertEquals('121,12F,13W,13S', get_config('local_ucla', 'active_terms'));
        
        // Start session C
        $today = date('Y-m-d', strtotime('+6 weeks', strtotime($today)));
        $session->update_today($today);
        $session->update();
        
        $this->assertEquals('Summer 2012 - Session A, Week 7 | Summer 2012 - Session C, Week 1', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('7', get_config('local_ucla', 'current_week'));
        $this->assertEquals('121,12F,13W,13S', get_config('local_ucla', 'active_terms'));
        
        // Next week
        $today = date('Y-m-d', strtotime('+1 weeks', strtotime($today)));
        $session->update_today($today);
        $session->update();
        
        $this->assertEquals('Summer 2012 - Session A, Week 8 | Summer 2012 - Session C, Week 2', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('8', get_config('local_ucla', 'current_week'));
        $this->assertEquals('121,12F,13W,13S', get_config('local_ucla', 'active_terms'));
        
        /// Session A end
        $today = date('Y-m-d', strtotime('+1 weeks', strtotime($today)));
        $session->update_today($today);
        $session->update();
        
        $this->assertEquals('Summer 2012 - Session C, Week 3', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('8', get_config('local_ucla', 'current_week'));
        $this->assertEquals('121,12F,13W,13S', get_config('local_ucla', 'active_terms'));
        $this->assertEquals('121', get_config('', 'currentterm'));
        
        /// Session C end 
        // this is between sessions, but the term should have been updated to Fall
        // The updated term will be used to make the next call to the registrar, 
        // Which will trigger the changes
        $today = date('Y-m-d', strtotime('+4 weeks', strtotime($today)));
        $session->update_today($today);
        $session->update();
        
        $this->assertEquals('Summer 2012', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('-1', get_config('local_ucla', 'current_week'));
        $this->assertEquals('121,12F,13W,13S', get_config('local_ucla', 'active_terms'));
        $this->assertEquals('12F', get_config('', 'currentterm'));
        
        // Check that we transition over to Fall correctly an hour later...
        // To do this, we need to make a fresh registrar call
        unset($session);
        
        $today = date('Y-m-d', strtotime('+1 hour', strtotime($today)));
        $query = $this->registrar_query('12F');
        $session = new ucla_session_ext($query, $today);
        $session->update();
        
        $this->assertEquals('Fall 2012', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('-1', get_config('local_ucla', 'current_week'));
        $this->assertEquals('12F,13W,13S,131', get_config('local_ucla', 'active_terms'));
        $this->assertEquals('12F', get_config('', 'currentterm'));
        

    }
    
    /**
     * Test Fall 2012 session and rolloever into winter break 
     */
    function test_fall_2012() {
        
        // Fall 2012 session start
        $today = '2012-09-24';
        
        /// Test ability to set term
        block_ucla_weeksdisplay::init_currentterm($today);
        $this->assertEquals('12F', get_config('', 'currentterm'));
        
        // At start of session
        $query = $this->registrar_query('12F');
        $session = new ucla_session_ext($query, $today);
        $session->update();

        // Session has started, but instructio hasn't
        $this->assertEquals('Fall 2012', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('-1', get_config('local_ucla', 'current_week'));
        $this->assertEquals('12F,13W,13S,131', get_config('local_ucla', 'active_terms'));

        // At the start of instruction, display week 0
        $today = date('Y-m-d', strtotime('+3 days', strtotime($today)));
        $session->update_today($today);
        $session->update();
        
        $this->assertEquals('Fall 2012 - Week 0', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('0', get_config('local_ucla', 'current_week'));
        $this->assertEquals('12F,13W,13S,131', get_config('local_ucla', 'active_terms'));
        
        // Check Week 1
        $today = date('Y-m-d', strtotime('+4 days', strtotime($today)));
        $session->update_today($today);
        $session->update();

        $this->assertEquals('Fall 2012 - Week 1', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('1', get_config('local_ucla', 'current_week'));
        $this->assertEquals('12F,13W,13S,131', get_config('local_ucla', 'active_terms'));
        
        // Check Week 10
        $today = date('Y-m-d', strtotime('+9 weeks', strtotime($today)));
        $session->update_today($today);
        $session->update();

        $this->assertEquals('Fall 2012 - Week 10', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('10', get_config('local_ucla', 'current_week'));
        $this->assertEquals('12F,13W,13S,131', get_config('local_ucla', 'active_terms'));
        
        // Check Finals Week
        $today = date('Y-m-d', strtotime('+1 week', strtotime($today)));
        $session->update_today($today);
        $session->update();

        // First make sure we didn't screw up $today
        $this->assertEquals('Monday December 10', date('l F j', strtotime($today)));
        $this->assertEquals('Fall 2012 - Finals week', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('11', get_config('local_ucla', 'current_week'));
        $this->assertEquals('12F,13W,13S,131', get_config('local_ucla', 'active_terms'));
        
        // Check the switch to Winter term.. this happens at session END
        $today = date('Y-m-d', strtotime('+5 days', strtotime($today)));
        $session->update_today($today);
        $session->update();

        // still final's week... haven't changed terms
        $this->assertEquals('Fall 2012 - Finals week', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('-1', get_config('local_ucla', 'current_week'));
        $this->assertEquals('12F,13W,13S,131', get_config('local_ucla', 'active_terms'));
        $this->assertEquals('13W', get_config('', 'currentterm'));
        
        unset($session);
        
        // Turnover to Winter with brand new registrar query, 
        // we should be displaying Winter break
        $today = date('Y-m-d', strtotime('+1 week', strtotime($today)));
        $query = $this->registrar_query('13W');
        $session = new ucla_session_ext($query, $today);
        $session->update();
        
        $this->assertEquals('Winter break', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('-1', get_config('local_ucla', 'current_week'));
        $this->assertEquals('13W,13S,131,13F', get_config('local_ucla', 'active_terms'));
        $this->assertEquals('13W', get_config('', 'currentterm'));
        
    }
    
    /**
     * Test Winter 2013 session and rolloever into Spring quarter
     */
    function test_winter_2013() {
        
        // Winter 2013 session start
        $today = '2013-01-02';
        
        /// Test ability to set term
        block_ucla_weeksdisplay::init_currentterm($today);
        $this->assertEquals('13W', get_config('', 'currentterm'));
        
        // At start of session
        $query = $this->registrar_query('13W');
        $session = new ucla_session_ext($query, $today);
        $session->update();

        // Session has started, but instruction hasn't yet..
        $this->assertEquals('Winter 2013', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('-1', get_config('local_ucla', 'current_week'));
        $this->assertEquals('13W,13S,131,13F', get_config('local_ucla', 'active_terms'));
        
        // Winter 2013 instruction start
        $today = '2013-01-07';
        $session->update_today($today);
        $session->update();
        
        $this->assertEquals('Winter 2013 - Week 1', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('1', get_config('local_ucla', 'current_week'));
        $this->assertEquals('13W,13S,131,13F', get_config('local_ucla', 'active_terms'));
        
        // Check Week 10
        $today = date('Y-m-d', strtotime('+9 weeks', strtotime($today)));
        $session->update_today($today);
        $session->update();

        $this->assertEquals('Winter 2013 - Week 10', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('10', get_config('local_ucla', 'current_week'));
        $this->assertEquals('13W,13S,131,13F', get_config('local_ucla', 'active_terms'));
        
        // Check Finals week
        $today = date('Y-m-d', strtotime('+1 week', strtotime($today)));
        $session->update_today($today);
        $session->update();

        // Sanity check
        $this->assertEquals('Monday March 18', date('l F j', strtotime($today)));
        $this->assertEquals('Winter 2013 - Finals week', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('11', get_config('local_ucla', 'current_week'));
        $this->assertEquals('13W,13S,131,13F', get_config('local_ucla', 'active_terms'));
        
        // Check Spring rollover
        $today = date('Y-m-d', strtotime('+1 week', strtotime($today)));
        $session->update_today($today);
        $session->update();
        
        $this->assertEquals('13W,13S,131,13F', get_config('local_ucla', 'active_terms'));
        $this->assertEquals('13S', get_config('', 'currentterm'));
        
        unset($session);
        
        // Make sure Spring happens
        $today = date('Y-m-d', strtotime('+1 hour', strtotime($today)));
        $query = $this->registrar_query('13S');
        $session = new ucla_session_ext($query, $today);
        $session->update();
        
        $this->assertEquals('Spring 2013', get_config('local_ucla', 'current_week_display'));
        $this->assertEquals('-1', get_config('local_ucla', 'current_week'));        
        $this->assertEquals('13S,131,13F,14W', get_config('local_ucla', 'active_terms'));
        $this->assertEquals('13S', get_config('', 'currentterm'));
    }
    
    /**
     * Fake registrar calls with real registrar data.  This is to avoid calling
     * registrar directly through tests.  The data was built from 
     * stored procedure: ucla_getterms.
     * 
     * To add more tests, update this data
     * 
     * @param type $term
     * @return array 
     */
    private function registrar_query($term) {
        // This list was retrieved from the registrar
        $terms = array(
            '121' => array(
                array(
                'term' => '121',
                'session' => '8A',
                'session_start' => '2012-06-25',
                'session_end' => '2012-08-17',
                'instruction_start' => '2012-06-25',
                ),
                array(
                'term' => '121',
                'session' => '6C',
                'session_start' => '2012-08-06',
                'session_end' => '2012-09-14',
                'instruction_start' => '2012-08-06',
                ),
            ),
            '12F' => array(
                array(
                'term' => '12F',
                'session' => 'RG',
                'session_start' => '2012-09-24',
                'session_end' => '2012-12-14',
                'instruction_start' => '2012-09-27',
                ),
            ),
            '13W' => array(
                array(
                'term' => '13W',
                'session' => 'RG',
                'session_start' => '2013-01-02',
                'session_end' => '2013-03-22',
                'instruction_start' => '2013-01-07',
                ),
            ),
            '13S' => array(
                array(
                'term' => '13S',
                'session' => 'RG',
                'session_start' => '2013-03-27',
                'session_end' => '2013-06-14',
                'instruction_start' => '2013-04-01',
                ),
            ),
        );
        
        return $terms[$term];
    }

}


//EOF
