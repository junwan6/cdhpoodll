<?php

/**
 * Class loader that will load in a PublicPrivate_Exception class defition for
 * either PHP 5.3+ or for compatibility before PHP 5.3.
 *
 * @author ebollens
 * @version 20110719
 *
 * @see exception.base.class.php
 * @see exception.ompat.class.php
 */

if(method_exists('Exception', 'getPrevious')) {
    include_once($CFG->dirroot.'/local/publicprivate/lib/exception.base.class.php');
} else {
    include_once($CFG->dirroot.'/local/publicprivate/lib/exception.compat.class.php');
}
