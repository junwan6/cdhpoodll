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

class ucla_cp_renderer {
    private $history = array();

    
    static function cmp($a, $b) {
        return( strcmp($a->get_key(), $b->get_key()) );
    }
    
    /**
     *  get_content_array()
     * @param int $size the size of the tables you want the data sorted into.
     * @param bool $sort whether or not you want to items sorted based on name.
     *
     *  @return Array This will return the data sorted into tables.
     *      Normally, this table will be 2 levels deep (Array => Array).
     *      Each key should be the identifier within the lang file
     *      that uses a language convention.
     *
     *      <item>_pre represents strings that are printed before the link.
     *      <item>_post represents the string that is printed after the link.
     **/
    static function get_content_array($contents, $size=null, $sort=true) {
        $all_stuff = array();

        // This is the number of groups to sort this into
        if ($size === null) {
            $size = floor(count($contents) / 2) + 1;

            if ($size == 0) {
                $size = 1;
            }
        }

        foreach ($contents as $content) {
            $action = $content;
            $title = $content->get_key();

            $all_stuff[] = $action;
        }

        usort($all_stuff,"ucla_cp_renderer::cmp");

        $disp_stuff = array();

        $disp_cat = array();
        foreach ($all_stuff as $title => $action) {
            if (count($disp_cat) == $size) {
                $disp_stuff[] = $disp_cat;
                $disp_cat = array();
            }

            $disp_cat[$title] = $action;
        }

        if (!empty($disp_cat)) {
            $disp_stuff[] = $disp_cat;
        }

        return $disp_stuff;
    }

    /**
     *  Builds the string with the string and the descriptions, pre and post.
     *  @param ucla_cp_module $item_obj - This is the identifier for the 
     *      current control panel item.
     *  @param link_attributes - Attributes associated with the object if the 
     *  object is a link.
     *  @return string The DOMs of the control panel description and link.
     **/
    static function general_descriptive_link($item_obj, $link_attributes = null) {
        $fitem = '';

        $bucp = $item_obj->associated_block(); 

        $item = $item_obj->item_name;
        $link = $item_obj->get_action();

        if ($item_obj->get_opt('pre')) {
            $fitem .= html_writer::tag('span', get_string($item . '_pre', 
                $bucp, $item_obj), array('class' => 'pre-link'));
        }

        //If the object is plain text, just include the object name in the string.
        if (get_class($item_obj) == 'ucla_cp_text_module') {
            $fitem.= $item;
        }
        //If the object is a tag 
        else if ($link === null) {
            $fitem .= html_writer::tag('span', get_string($item, $bucp,
                $item_obj), array('class' => 'disabled'));
        } else {
            $fitem .= html_writer::link($link, get_string($item, $bucp, 
                $item_obj), $link_attributes);
        }

        // One needs to explicitly hide the post description
        if ($item_obj->get_opt('post') !== false) {
            $fitem .= html_writer::tag('span', get_string($item . '_post', 
                $bucp, $item_obj), array('class' => 'post-link'));
        }

        return $fitem;
    }

    /**
     *  Adds an icon to the link and description.
     *
     *  @param ucla_cp_modules $item_obj - The item to display.
     *  @return string The DOMs of the control panel, with an image
     *      and whatever is returned by @see general_descriptive_link.
     **/
    static function general_icon_link($item_obj) {
        global $OUTPUT;

        $bucp = $item_obj->associated_block();

        $item = $item_obj->item_name;

        $item_string = get_string($item, $bucp, $item_obj);
        
        $fitem = '';       
        //BEGIN UCLA MOD: CCLE-2869-Add Empty Alt attribute for icons on Control Panel
        $fitem .= html_writer::start_tag('a', 
            array('href' => $item_obj->get_action()));

        $fitem .= html_writer::start_tag('img', 
                array('src' => $OUTPUT->pix_url('cp_' . $item, $bucp), 
                      'alt' => $item_string, 'class' => 'general_icon'));
        $fitem .= html_writer::end_tag('a');
        
        $fitem .= html_writer::start_tag('span', array('class' => 'general_icon_text'));
        $fitem .= self::general_descriptive_link($item_obj);
        
        $fitem .= html_writer::end_tag('span');
        
        //END UCLA MOD: CCLE-2869
        return $fitem;
    }
   
    /**
     *  This function will take the contents of a 2-layer deep
     *  array and generate the string that contains the contents
     *  in a div-split table. It can also generate the contents.
     *
     *  @param array $contents - The contents to diplay using the renderer.
     *  @param boolean $format - If this is true, then we will send the data
     *      through {@link get_content_array}.
     *  @param string $orient - Which orientation handler to use to render the
     *      display. Currently accepts two options (defaults to rows) if the
     *      option does not exist.
     *
     *      'col': This means that we expect an array containing 2 arrays of
     *          the elements we wish to render.
     *
     *      'row': This means taht we expect an array containing arrays each
     *          with 2 of the elements we wish to render.
     *
     *  @param string $handler - This is the callback function used to display
     *      each element. Defaults to general_descriptive_link, and will crash
     *      the script if you provide a non-existant function.
     **/
    static function control_panel_contents($contents, $format=false, 
            $orient='col', $handler='general_descriptive_link') {

        if ($format) {
            $contents = ucla_cp_renderer::get_content_array($contents, 2);
        }
        
        $full_table = '';
        
        $columns = ($orient == 'col');

        foreach ($contents as $content_row) {

            $row_contents = '';
            
            // This corresponds to bootstrap grid
            $responsive_class = 'col-sm-6 col-xs-12 item';

            foreach ($content_row as $content_item => $content_link) {

                $the_output = html_writer::start_tag('div',
                    array('class' => $responsive_class . ' ' . $content_link->item_name));
                
                $the_output .= ucla_cp_renderer::$handler(
                    $content_link);
                
                $the_output .= html_writer::end_tag('div');
                $row_contents .= $the_output;
            }
            
            $full_table .= html_writer::tag('div', $row_contents,
                array('class' => 'row'));
        }
        
        return $full_table;
    }
}
