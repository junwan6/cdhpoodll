<?php

/**
 * Class that represents a UCLA session 
 */
class ucla_session {
    
    private $_session;          // An array of session objects from registrar
    protected $_quarter;          // Current quarter in single digit code
    protected $_year;             // Quarter year
    protected $_today;            // Today's timestamp
    private $_session_len;      // Array of sesion lengths to determine final's week
    private $_summer;           // boolean flag for summer
    protected $_current_week;     // Current week in session (max of summer sessions)
    protected $_lookahead;        // Number of terms to look ahead
    
    function __construct($session) {
        $this->_session = $this->key_session($session);

        // Summer contains more than 1 session
        $this->_summer = count($this->_session) > 1 ? true : false;
        
        // Session lengths -- to display finals week string
        $this->_session_len = array('8A' => 8, '6C' => 6, 'RG' => 10);

        // Other info
        $this->_quarter = substr($session[0]['term'], 2);
        $this->_year = substr($session[0]['term'], 0, -1);
        
        // All session dates are tested against today
        $this->_today = substr(date('c'), 0, 10);
        
        // Use current week
        $this->_current_week = (int)get_config('local_ucla', 'current_week');
        
        if(empty($this->_current_week)) {
            // Start with undetermined week
            $this->_current_week = -1;
        }
        
        // Number of active terms to retrieve (not including current term)
        $this->_lookahead = 3;
    }
    
    
    /**
     * Updates term information such as the current week/session, or sets 
     * the next term if that's needed
     */
    function update() {
        
        if(!$this->_summer) {   // Regular term in session
            
            // Check if quarter is in session
            if($this->in_session($this->_session['RG']->session_end)) {
            
                // Check if session started and instruction has NOT started
                if($this->session_started($this->_session['RG']->session_start)
                        && $this->instruction_started($this->_session['RG']->instruction_start)) {
                    $weeks_str = $this->get_week_str($this->_session['RG']);
                } else {
                    // Get current quarter and year 
                    if($this->_quarter == 'W' && strcmp(date('y', strtotime($this->_today)), $this->_year) < 0) {
                        $weeks_str = get_string('winter_break', 'block_ucla_weeksdisplay');
                    } else {
                        $weeks_str = $this->get_quarter_and_year();
                    }
                }
                
                $weeks_str = html_writer::div($weeks_str, 'weeks-display label-' . strtolower($this->quarter_name($this->_quarter)));
                // Update weeks display
                $this->update_week_display($weeks_str);
                
            } else { // Update term when quarter is not in session
                $next_term = $this->next_term();
                $this->update_term($next_term);
                $this->_current_week = -1;
                $this->update_week();
            }
            
        } else { // Summer
            // Hold concat of summer session strings
            $quarter_week = '';
            $separator = '';
            
            // Check if summer session A has started
            if($this->in_session_summer($this->_session['8A']->session_start,
                    $this->_session['8A']->session_end)) {
                $quarter_week .= $this->get_week_str($this->_session['8A']);
                $separator = ' | ';
            }
            
            // Check if summer session C has started
            if($this->in_session_summer($this->_session['6C']->session_start, 
                    $this->_session['6C']->session_end)) {
                $quarter_week .= $separator;
                $quarter_week .= $this->get_week_str($this->_session['6C']);
            }
            
            // Or we're in week prior to session start, so display quarter and year
            if(empty($quarter_week)) {
                $quarter_week = $this->get_quarter_and_year();
            }
            
            // Summer session ended, update term
            if(!$this->in_session($this->_session['6C']->session_end)) {
                $next_term = $this->next_term();
                $this->update_term($next_term);
                $this->_current_week = -1;
                $this->update_week();
            }
            
            // Update quarter string
            $this->update_week_display($quarter_week);
        }
        
        // Update the current week
        $this->update_week();
        
        // Update active terms
        $this->update_active_terms();
    }
    
    /**
     * Convert the session array into session objects for desired sessions
     * 
     * @param type $session
     * @return array 
     */
    private function key_session($session) {
        $k = array();
        
        foreach($session as $s) {
            if($s['session'] == 'RG' || $s['session'] == '8A' || $s['session'] == '6C') {
                $k[$s['session']] = (object)$s;
           }
        }
        
        return $k;
    }

    /**
     * Get the week string
     * 
     * @param obj $session
     * @return string 
     */
    private function get_week_str($session) {
        $quarter_year = $this->get_quarter_and_year();
        $summersession = '';
        
        // Append session for summer
        if($this->_summer) {
            $summersession = get_string('session', 'block_ucla_weeksdisplay') . ' ' . substr($session->session, -1) . ', ';
        }

        $weekstr = html_writer::span($quarter_year . ' - ' . $summersession, 'session ') .
                html_writer::span($this->get_week_for_session($session), 'week');
        
        return $weekstr;
    }
    
    /**
     * Get a string of <quarter> <year>
     * 
     * @return string 
     */
    private function get_quarter_and_year() {
        $yearstart = date('Y');
        $yearstart = substr($yearstart, 0, 2);
        return $this->quarter_name($this->_quarter) . ' ' . $yearstart . $this->_year;
    }
    
    /**
     * Gets the current week for a given session object
     * 
     * @param obj $session
     * @return string containing week display
     */
    private function get_week_for_session($session) {
        
        // Get a weeks count offset for instruction_star and today
        $weeks_start = date('W', strtotime($session->instruction_start));
        $weeks_today = date('W', strtotime($this->_today));
        
        // Offset week by +1 since week 1 will be week 0 if both values are equal
        $weeks = $weeks_today - $weeks_start + 1;
        
        // Fall has week 0
        if($this->_quarter == 'F') {
            $weeks--;
        }
         
        // Check if we need to display 'finals week'
        if($weeks > $this->_session_len[$session->session]) {
            $week_str = get_string('finals_week', 'block_ucla_weeksdisplay');
        } else {
            $week_str = get_string('week', 'block_ucla_weeksdisplay') . ' ' . $weeks;
        }
        
        // Update current week
        if($weeks > $this->_current_week) {
            $this->_current_week = $weeks;
            events_trigger('ucla_weeksdisplay_changed', $weeks);
        }
        
        return $week_str;
    }
    
    /**
     * Checks if term is in session given the session end date
     * 
     * @param type $end
     * @return bool true if term is in session
     */
    private function in_session($end) {
        $val = strcmp($this->_today, $end);
        return ($val < 0 ? true : false);
    }

    /** 
     * Checks if a summer session is active given session start and end dates
     * 
     * @param string $start of session
     * @param string $end of session
     * @return boolean true if session is active
     */
    private function in_session_summer($start, $end) {
        $start = substr($start, 0, 10);
        $end = substr($end, 0, 10);
        
        $sum_start = strcmp($start, $this->_today);
        $sum_end = strcmp($this->_today, $end);

        if($sum_start <= 0 && $sum_end <= 0) {
            return true;
        }
        
        return false;
    }
    
    private function session_started($start) {
        $start = substr($start, 0, 10);
        $val = strcmp($start, $this->_today);
        return ($val <= 0 ? true : false);
    }
    
    /**
     * Checks if instruction has started from a given start date
     * 
     * @param type $start
     * @return bool true if instruction has started
     */
    private function instruction_started($start) {
        $start = substr($start, 0, 10);
        $val = strcmp($start, $this->_today);
        return ($val <= 0 ? true : false);
    }
    
    /**
     * Gets next term from a term
     * 
     * @param type $term
     */
    protected function next_term($term = null) {
        
        if($term) {
            $year = substr($term, 0, 2);
            $quarter = substr($term, -1);
        } else {
            $year = intval($this->_year);
            $quarter = $this->_quarter;
        }

        switch($quarter) {
            case 'F':
                $next_year = ($year == 99) ? '00' : sprintf('%02d', $year + 1);
                return $next_year.'W';
            case 'W':   
                return $year.'S';
            case 'S':
                return $year.'1';
            case '1':
                return $year.'F';
        }
    }
        
    /**
     * Returns quarter name string given a single digit code
     * 
     * @param char $quarter code
     * @return string quarter name
     */
    private function quarter_name($quarter) {
        switch ($quarter) {
            case '1':
                $name = 'summer';
                break;
            case 'S':
                $name = 'spring';
                break;
            case 'W':
                $name = 'winter';
                break;
            case 'F':
                $name = 'fall';
                break;
        }
        
        return get_string($name, 'block_ucla_weeksdisplay');
    }
    
    /**
     * Update term config
     * 
     * @param type $term 
     */
    protected function update_term($term) {
        block_ucla_weeksdisplay::set_term($term);
    }
    
    /**
     * Update weeks display config
     * 
     * @param type $str 
     */
    protected function update_week_display($str) {
        block_ucla_weeksdisplay::set_week_display($str);

    }
    
    /**
     * Update current week config
     *  
     */
    protected function update_week() {
        block_ucla_weeksdisplay::set_current_week($this->_current_week);
    }
    
    /**
     * Update active terms
     *  
     */
    protected function update_active_terms() {
        $term = $this->_year . $this->_quarter;
        $ta = array($term);
        
        for($i = 0; $i < $this->_lookahead; $i++) {
            $term = $this->next_term($term);
            $ta[] = $term;           
        }
        
        block_ucla_weeksdisplay::set_active_terms($ta);
    }
}