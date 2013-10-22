<?php

// This file is not a part of Moodle - http://moodle.org/
// This is a none core contributed module.
//
// This is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License
// can be see at <http://www.gnu.org/licenses/>.

/*
 * __________________________________________________________________________
 *
 * PoodLL TinyMCE for Moodle 2.x
 *
 * This plugin need to use together with Poodll filter.
 *
 * @package    poodllrecording
 * @subpackage tinymce_poodllrecording
 * @copyright  2013 UC Regents
 * @copyright  2013 Justin Hunt  {@link http://www.poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * __________________________________________________________________________
 */

defined('MOODLE_INTERNAL') || die();

class tinymce_poodllrecording extends editor_tinymce_plugin {
    /**@var array list of buttons defined by this plugin. */
    protected $buttons = array('poodllrecording');

    protected function update_init_params(array &$params, context $context,
            array $options = null) {

        // Add button after 'unlink' in advancedbuttons3.
        $this->add_button_after($params, 3, 'poodllrecording', 'image');

        // Add JS file, which uses default name.
        $this->add_js_plugin($params);
    }
}